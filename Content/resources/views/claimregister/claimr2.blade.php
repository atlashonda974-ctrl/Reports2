<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.master_titles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.3.2/css/fixedHeader.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <x-datatable-styles />
    <style>
        .time-filter-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .time-tab {
            flex: 1;
            min-width: 80px;   
            max-width: 100px; 
            border-radius: 8px;
            padding: 8px 5px;
            text-align: center;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 14px;
        }
        .time-tab:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .time-tab.active {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <x-report-header title="Get O/S Surveyor Appointment " />
        <form method="GET" action="{{ url('/cr2') }}" class="mb-4">
            <div class="row g-3">
                <!-- From Date -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="start_date" class="form-label me-2" style="white-space: nowrap; width: 100px;">From Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" 
                        value="{{ request('start_date', $start_date) }}">
                </div>

                <!-- To Date -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="end_date" class="form-label me-2" style="white-space: nowrap; width: 100px;">To Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                        value="{{ request('end_date', $end_date) }}">
                </div>

                <!-- Department Filter -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="new_category" class="form-label me-2" style="white-space: nowrap; width: 100px;">Departments</label>
                    <select name="new_category" id="new_category" class="form-control">
                        <option value="" {{ $selected_category == '' ? 'selected' : '' }}>All Departments</option>
                        <option value="Fire" {{ $selected_category == 'Fire' ? 'selected' : '' }}>Fire</option>
                        <option value="Marine" {{ $selected_category == 'Marine' ? 'selected' : '' }}>Marine</option>
                        <option value="Motor" {{ $selected_category == 'Motor' ? 'selected' : '' }}>Motor</option>
                        <option value="Miscellaneous" {{ $selected_category == 'Miscellaneous' ? 'selected' : '' }}>Miscellaneous</option>
                        <option value="Health" {{ $selected_category == 'Health' ? 'selected' : '' }}>Health</option>
                    </select>
                </div>

                <!-- Branch -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="location_category" class="form-label me-2" style="white-space: nowrap;">Branches</label>
                    <select name="location_category" id="location_category" class="form-control select2">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option 
                                value="{{ $branch->fbracode }}" 
                                {{ request('location_category') == $branch->fbracode ? 'selected' : '' }}
                            >
                                {{ $branch->fbradsc }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Insurance Type Dropdown -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="insu" class="form-label me-2" style="white-space: nowrap;">Insurance Type</label>
                    <select name="insu[]" id="insu" class="form-control select2-insurance" multiple>
                        <option value="D" {{ in_array('D', request('insu', [])) ? 'selected' : '' }}>Direct</option>
                        <option value="I" {{ in_array('I', request('insu', [])) ? 'selected' : '' }}>Inward</option>
                        <option value="O" {{ in_array('O', request('insu', [])) ? 'selected' : '' }}>Outward</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-4 d-flex align-items-center">
                    <button type="submit" class="btn btn-outline-primary me-2" title="Filter">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <a href="{{ url('/cr2') }}" class="btn btn-outline-secondary me-2" title="Reset">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        <!-- Time Filter Tabs -->
        @php
            $insuValues = request('insu', []);
            $insuQuery = is_array($insuValues) ? implode(',', $insuValues) : $insuValues;
        @endphp

        <div class="d-flex justify-content-start mb-3">
            <div class="card time-filter-card">
                <div class="card-body py-2 px-3">
                    <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/cr2?new_category=' . urlencode($selected_category) . '&time_filter=all&start_date=' . $start_date . '&end_date=' . $end_date . '&location_category=' . request('location_category') . '&insu=' . $insuQuery) }}" 
                   class="time-tab {{ $selected_time_filter == 'all' ? 'active' : 'bg-light' }}">
                    <div class="fw-bold">All</div>
                    <div class="small">{{ $counts['all'] }}</div>
                </a>
                <a href="{{ url('/cr2?new_category=' . urlencode($selected_category) . '&time_filter=2days&start_date=' . $start_date . '&end_date=' . $end_date . '&location_category=' . request('location_category') . '&insu=' . $insuQuery) }}" 
                   class="time-tab {{ $selected_time_filter == '2days' ? 'active' : 'bg-light' }}">
                    <div class="fw-bold">2 Days</div>
                    <div class="small">{{ $counts['2days'] }}</div>
                </a>
                <a href="{{ url('/cr2?new_category=' . urlencode($selected_category) . '&time_filter=5days&start_date=' . $start_date . '&end_date=' . $end_date . '&location_category=' . request('location_category') . '&insu=' . $insuQuery) }}" 
                   class="time-tab {{ $selected_time_filter == '5days' ? 'active' : 'bg-light' }}">
                    <div class="fw-bold">5 Days</div>
                    <div class="small">{{ $counts['5days'] }}</div>
                </a>
                <a href="{{ url('/cr2?new_category=' . urlencode($selected_category) . '&time_filter=7days&start_date=' . $start_date . '&end_date=' . $end_date . '&location_category=' . request('location_category') . '&insu=' . $insuQuery) }}" 
                   class="time-tab {{ $selected_time_filter == '7days' ? 'active' : 'bg-light' }}">
                    <div class="fw-bold">7 Days</div>
                    <div class="small">{{ $counts['7days'] }}</div>
                </a>
                <a href="{{ url('/cr2?new_category=' . urlencode($selected_category) . '&time_filter=10days&start_date=' . $start_date . '&end_date=' . $end_date . '&location_category=' . request('location_category') . '&insu=' . $insuQuery) }}" 
                   class="time-tab {{ $selected_time_filter == '10days' ? 'active' : 'bg-light' }}">
                    <div class="fw-bold">10 Days</div>
                    <div class="small">{{ $counts['10days'] }}</div>
                </a>
                <a href="{{ url('/cr2?new_category=' . urlencode($selected_category) . '&time_filter=15days&start_date=' . $start_date . '&end_date=' . $end_date . '&location_category=' . request('location_category') . '&insu=' . $insuQuery) }}" 
                   class="time-tab {{ $selected_time_filter == '15days' ? 'active' : 'bg-light' }}">
                    <div class="fw-bold">15 Days</div>
                    <div class="small">{{ $counts['15days'] }}</div>
                </a>
                <a href="{{ url('/cr2?new_category=' . urlencode($selected_category) . '&time_filter=15plus&start_date=' . $start_date . '&end_date=' . $end_date . '&location_category=' . request('location_category') . '&insu=' . $insuQuery) }}" 
                   class="time-tab {{ $selected_time_filter == '15plus' ? 'active' : 'bg-light' }}">
                    <div class="fw-bold">15+ Days</div>
                    <div class="small">{{ $counts['15plus'] }}</div>
                </a>
            </div>
        </div>
    </div>
</div>

        @if(empty($data))
            <div class="alert alert-danger">No data available.</div>
        @else
            <table id="reportsTable" class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>Dept.</th>
                        <th>PDP DEPT CODE</th>
                        <th>PARTY CODE</th>
                        <th>DESC</th>
                        <th>MOBILE NO</th>
                        <th>EMAIL ADDRESS</th>
                        <th>INTIMATION DATE</th>
                        <th>REV#SUM</th>
                        <th>DOC REF NO</th>
                        <th>BASE DOCUMENT NO</th>
                        <th>GIH LOSS CLAIMED</th>
                        <th>Insurance Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $record)
                        <tr>
                            @php
                                $categoryMapping = [
                                    11 => 'Fire',
                                    12 => 'Marine',
                                    13 => 'Motor',
                                    14 => 'Miscellaneous',
                                    16 => 'Health',
                                ];
                                $code = $record->PDP_DEPT_CODE ?? null;
                                $department = $categoryMapping[$code] ?? 'N/A';
                            @endphp
                            <td>{{ $department }}</td>
                            <td>{{ $record->PDP_DEPT_CODE ?? 'N/A' }}</td>
                            <td>{{ $record->PPS_PARTY_CODE ?? 'N/A' }}</td>
                            <td>{{ $record->PPS_DESC ?? 'N/A' }}</td>
                            <td>{{ $record->PPS_MOBILE_NO ?? 'N/A' }}</td>
                            <td>{{ $record->PPS_EMAIL_ADDRESS ?? 'N/A' }}</td>
                            <td>{{ $record->GIH_INTIMATIONDATE ?? 'N/A' }}</td>
                            <td>{{ $record->{'MAX(CLM_INTHD.GIH_INTI_ENTRYNO)'} ?? 'N/A' }}</td>
                            <td>{{ $record->GIH_DOC_REF_NO ?? 'N/A' }}</td>
                            <td>{{ $record->GID_BASEDOCUMENTNO ?? 'N/A' }}</td>
                            <td style="text-align: right;">
                                {{ $record->GIH_LOSSCLAIMED ? number_format($record->GIH_LOSSCLAIMED) : 'N/A' }}
                            </td>
                            <td>
                                @php
                                    $insuranceTypeMapping = [
                                        'D' => 'Direct',
                                        'I' => 'Inward',
                                        'O' => 'Outward',
                                    ];
                                    $code = $record->PIY_INSUTYPE ?? null;
                                    $insuranceType = $insuranceTypeMapping[$code] ?? $code ?? 'N/A';
                                @endphp
                                {{ $insuranceType }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="10">Total GIH LOSS CLAIMED</th>
                        <th id="totalLossClaimed">0</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.3.2/js/dataTables.fixedHeader.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for branch dropdown
            $('#location_category').select2({
                placeholder: "Select a branch",
                allowClear: true,
                width: '69%'
            });

            // Initialize Select2 for insurance dropdown
            $('#insu').select2({
                placeholder: "Choose type",
                allowClear: true,
                width: '69%'
            });

            var table = $('#reportsTable').DataTable({
                "paging": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "scrollX": true,
                "scrollY": "500px",
                "scrollCollapse": false,
                "fixedHeader": {
                    header: true,
                    footer: true
                },
                "autoWidth": true,
                dom: '<"top"<"d-flex justify-content-between align-items-center"<"d-flex"f><"d-flex"B>>>rt<"bottom"ip><"clear">',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Outstanding Report',
                        footer: true,
                        exportOptions: {
                            columns: ':visible',
                            format: {
                                body: function(data, row, column, node) {
                                    return data;
                                }
                            },
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Outstanding Report',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        footer: true,
                        exportOptions: {
                            columns: ':visible',
                            format: {
                                body: function(data, row, column, node) {
                                    return data;
                                }
                            },
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api();

                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            parseFloat(i.replace(/[^\d.-]/g, '')) || 0 :
                            typeof i === 'number' ? i : 0;
                    };

                    // Calculate total for GIH_LOSSCLAIMED (column index 10)
                    var totalLossClaimed = api.column(10, { page: 'current' }).data().reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    // Update footer
                    $('#totalLossClaimed').html(
                        '<strong>' + totalLossClaimed.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + '</strong>'
                    );
                },
                "initComplete": function() {
                    $('#totalSum').html('0');
                    this.api().draw();
                    this.api().columns.adjust();
                    $('.dataTables_filter input').attr('placeholder', 'Search...');
                    $('.dataTables_filter').css('margin-left', '5px').css('margin-right', '5px');
                    $('.dt-buttons').css('margin-left', '5px');
                },
                "drawCallback": function() {
                    this.api().columns.adjust();
                }
            });

            $('a[title="Reset"]').on('click', function() {
                setTimeout(function() {
                    table.draw();
                }, 100);
            });
        });
    </script>
</body>
</html>
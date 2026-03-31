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
</head>

<body>
    <div class="container mt-5">
        <x-report-header title="Claim Report case" />
        <form method="GET" action="{{ url('/claimInt') }}" class="mb-4">
            <div class="row g-3">
                <!-- From Date -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="start_date" class="form-label me-2" style="white-space: nowrap; width: 100px;">From
                        Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                        value="{{ request('start_date', $start_date ?? date('Y-m-d', strtotime('-30 days'))) }}">
                </div>

                <!-- To Date -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="end_date" class="form-label me-2" style="white-space: nowrap; width: 100px;">To
                        Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                        value="{{ request('end_date', $end_date ?? date('Y-m-d')) }}">
                </div>

                <!-- Department Filter -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="new_category" class="form-label me-2"
                        style="white-space: nowrap; width: 100px;">Departments</label>
                    <select name="new_category" id="new_category" class="form-control">
                        <option value=""
                            {{ request('new_category', $selected_category ?? '') == '' ? 'selected' : '' }}>All
                            Departments</option>
                        <option value="Fire"
                            {{ request('new_category', $selected_category ?? '') == 'Fire' ? 'selected' : '' }}>Fire
                        </option>
                        <option value="Marine"
                            {{ request('new_category', $selected_category ?? '') == 'Marine' ? 'selected' : '' }}>Marine
                        </option>
                        <option value="Motor"
                            {{ request('new_category', $selected_category ?? '') == 'Motor' ? 'selected' : '' }}>Motor
                        </option>
                        <option value="Miscellaneous"
                            {{ request('new_category', $selected_category ?? '') == 'Miscellaneous' ? 'selected' : '' }}>
                            Miscellaneous</option>
                        <option value="Health"
                            {{ request('new_category', $selected_category ?? '') == 'Health' ? 'selected' : '' }}>Health
                        </option>
                    </select>
                </div>

                <!-- Branch -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="location_category" class="form-label me-2" style="white-space: nowrap;">Branches</label>
                    <select name="location_category" id="location_category" class="form-control select2">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->fbracode }}"
                                {{ request('location_category') == $branch->fbracode ? 'selected' : '' }}>
                                {{ $branch->fbradsc }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Insurance Type Filter -->
<div class="col-md-3 d-flex align-items-center">
    <label for="insurance_type" class="form-label me-2" style="white-space: nowrap; width: 100px;">Insurance Type</label>
    <select name="insurance_type[]" id="insurance_type" class="form-control select2-multi" multiple>
    <option value="D" {{ in_array('D', $selected_insurance_types ?? []) ? 'selected' : '' }}>Direct (D)</option>
    <option value="I" {{ in_array('I', $selected_insurance_types ?? []) ? 'selected' : '' }}>Indirect (I)</option>
    <option value="O" {{ in_array('O', $selected_insurance_types ?? []) ? 'selected' : '' }}>Outward (O)</option>
</select>
</div>

                <!-- Buttons -->
                <div class="col-md-4 d-flex align-items-center">
                    <button type="submit" class="btn btn-outline-primary me-2" title="Filter">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <a href="{{ url('/claimInt') }}" class="btn btn-outline-secondary me-2" title="Reset">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        @if (empty($data))
            <div class="alert alert-danger">No data available.</div>
        @else
            <table id="reportsTable" class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>CLAIM NO</th>
                        <th>INTIMATION DATE</th>
                        <th>INSURED</th>
                        <th>DEPT</th>
                        <th>POLICY NO</th>
                        <th>ENTRY NO</th>
                       <th>SUM INSURED</th>
                       <th>INSURANCE TYPE</th>
                        <th>LOSS CLAIMED</th>
                        <th>CLIENT CODE</th>
                        <th>MOBILE NO</th>
                        <th>EMAIL ADDRESS</th>
                       
                    
                      
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $record)
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
                                
                               $insuranceTypeMapping = [
    'D' => 'Direct',
    'I' => 'Indirect',
    'O' => 'Outward',
];
                                $insuranceType = $record->PIY_INSUTYPE ?? 'N/A';
                                $insuranceTypeDisplay = $insuranceTypeMapping[$insuranceType] ?? $insuranceType;
                            @endphp
                            <td>{{ $record->GIH_DOC_REF_NO ?? 'N/A' }}</td>
                            <td>{{ $record->GIH_INTIMATIONDATE ?? 'N/A' }}</td>
                            <td data-bs-toggle="tooltip" title="{{ $record->PPS_DESC }}">
                                {{ $record->PPS_DESC ?? 'N/A' }}
                            </td>
                            <td>{{ $department }}</td>
                            <td>{{ $record->GID_BASEDOCUMENTNO ?? 'N/A' }}</td>
                            <td>{{ $record->GIH_INTI_ENTRYNO ?? 'N/A' }}</td>
                              <td>{{ number_format((float)($record->GDH_TOTALSI ?? 0), 2) }}</td>
                               <td>{{ $insuranceTypeDisplay }}</td>
                                 <td>{{ number_format((float)($record->GIH_LOSSCLAIMED ?? 0), 2) }}</td>
                            <td>{{ $record->PPS_PARTY_CODE ?? 'N/A' }}</td>
                            <td>{{ $record->PPS_MOBILE_NO ?? 'N/A' }}</td>
                            <td>{{ $record->PPS_EMAIL_ADDRESS ?? 'N/A' }}</td>
                         
                          
                            
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="10">Total Loss Claimed</th>
                        <th id="totalLossClaimed">0</th>
                        <th id="totalSumInsured">0</th>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
    <style>
        #reportsTable td:nth-child(3) {
            max-width: 80px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
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
            $('.select2').select2({
                placeholder: "Select a branch",
                allowClear: true,
                width: '69%'
            });

           $('#insurance_type').select2({
    placeholder: "All Types ",
    allowClear: true,
    width: '100%'
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
                "autoWidth": false,
                dom: '<"top"<"d-flex justify-content-between align-items-center"<"d-flex"f><"d-flex"B>>>rt<"bottom"ip><"clear">',
                buttons: [{
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Claim Report',
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
                        title: 'Claim Report',
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
                "footerCallback": function(row, data, start, end, display) {
                    var api = this.api();

                    var intVal = function(i) {
                        return typeof i === 'string' ?
                            parseFloat(i.replace(/[^\d.-]/g, '')) || 0 :
                            typeof i === 'number' ? i : 0;
                    };

                    // Calculate total for GIH_LOSSCLAIMED (column index 10)
                    var totalLossClaimed = api.column(10, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    // Calculate total for GDH_TOTALSI (column index 11)
                    var totalSumInsured = api.column(11, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    // Update footers
                    $('#totalLossClaimed').html(
                        '<strong>' + totalLossClaimed.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + '</strong>'
                    );
                    
                    $('#totalSumInsured').html(
                        '<strong>' + totalSumInsured.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + '</strong>'
                    );
                },
                "initComplete": function() {
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
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
            $('a[title="Reset"]').on('click', function() {
                setTimeout(function() {
                    table.draw();
                }, 100);
            });
        });
    </script>
</body>

</html>
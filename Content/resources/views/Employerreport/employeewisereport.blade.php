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
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            flex-direction: column;
        }

        .loading-content {
            background: white;
            padding: 2.5rem 4rem;
            border-radius: 1rem;
            text-align: center;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            margin-bottom: 1rem;
        }

        .loading-text {
            font-size: 1.2rem;
            font-weight: 500;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 0.6rem 0.8rem;
            border-radius: 0.4rem;
            border-left: 3px solid #007bff;
            min-width: 110px;
            margin-left: 0.5rem;
            flex-shrink: 0;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #666;
            font-weight: 500;
        }

        .stat-value {
            font-size: 1.3rem;
            font-weight: bold;
            color: #007bff;
            margin-top: 0.1rem;
        }

        .stat-box.present {
            border-left-color: #28a745;
        }

        .stat-box.present .stat-value {
            color: #28a745;
        }

        .stat-box.absent {
            border-left-color: #dc3545;
        }

        .stat-box.absent .stat-value {
            color: #dc3545;
        }

        .stat-box.leave {
            border-left-color: #ffc107;
        }

        .stat-box.leave .stat-value {
            color: #ffc107;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-selection--multiple {
            min-height: 2.375rem !important;
        }

        .select2-container--default .select2-selection--multiple {
            min-height: 2.5rem;
            height: auto !important;
            white-space: normal;
            overflow-x: hidden;
            padding: 0.25rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .form-label {
            white-space: nowrap;
            margin-bottom: 0 !important;
        }

        table.dataTable thead th {
            padding-right: 1.875rem !important;
            position: relative;
        }

        table.dataTable thead .sorting:after,
        table.dataTable thead .sorting_asc:after,
        table.dataTable thead .sorting_desc:after {
            right: 0.5rem !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
        }

        #employeeReportTable_wrapper {
            margin-bottom: 4.375rem;
        }

        .flag-late {
            background-color: #fff3cd;
            color: #856404;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .flag-earlyout {
            background-color: #cfe2ff;
            color: #084298;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .flag-both {
            background-color: #f8d7da;
            color: #842029;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .flag-ok {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .flag-offday {
            background-color: #e7f3ff;
            color: #004085;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .flag-na {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .day-weekend {
            background-color: #ffe5e5;
            font-weight: 600;
            color: #d63384;
        }

        .stats-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .flag-absent {
            background-color: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 500;
        }
    </style>
</head>

<body>


    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="loading-text" id="loadingText">Fetching data, please wait...</div>
        </div>
    </div>

    <div class="container mt-5">
        <x-report-header title="Employee Wise Attendance Report" />


        <div class="mb-4">
            <form id="filterForm" method="GET" action="{{ route('employee.wise.report') }}">
                <div class="row g-3 align-items-center">

                    <!-- From Date -->
                    <div class="col-auto d-flex align-items-center">
                        <label for="from_date" class="form-label me-2 mb-0" style="width: 90px;">From Date:</label>
                        <input type="date" id="from_date" name="from_date" class="form-control"
                            value="{{ request('from_date') }}">
                    </div>

                    <!-- To Date -->
                    <div class="col-auto d-flex align-items-center">
                        <label for="to_date" class="form-label me-2 mb-0" style="width: 90px;">To Date:</label>
                        <input type="date" id="to_date" name="to_date" class="form-control"
                            value="{{ request('to_date') }}">
                    </div>

                    <!-- Employee Code -->
                    <div class="col-auto d-flex align-items-center" style="min-width: 250px;">
                        <label for="empcode" class="form-label me-2 mb-0" style="width: 120px;">Employee:</label>
                        <select id="empcode" name="empcode" class="form-control select2" style="width: 100%;">
                            <option value="">All Employees</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp['code'] }}"
                                    {{ request('empcode') == $emp['code'] ? 'selected' : '' }}>
                                    {{ $emp['code'] }} : {{ $emp['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-auto d-flex align-items-center mt-2">
                        <button type="submit" id="filterBtn" class="btn btn-outline-primary me-2">
                            <i class="bi bi-funnel-fill"></i> Filter
                        </button>
                        <button type="button" id="resetBtn" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>

                </div>
            </form>
        </div>



        <!-- Summary Statistics  -->
        <div class="stats-container">
            <div style="font-weight: 600; color: #333; font-size: 0.9rem;">
                {{ $empCode ? 'Emp: ' . $empCode : '' }}
            </div>
            <div class="stat-box present">
                <div class="stat-label">Present</div>
                <div class="stat-value" id="summaryPresent">{{ $summary['Present'] ?? 0 }}</div>
            </div>
            <div class="stat-box absent">
                <div class="stat-label">Absent</div>
                <div class="stat-value" id="summaryAbsent">{{ $summary['Absent'] ?? 0 }}</div>
            </div>
            <div class="stat-box leave">
                <div class="stat-label">Leave</div>
                <div class="stat-value" id="summaryLeave">{{ $summary['Leave'] ?? 0 }}</div>
            </div>
            <div class="stat-box late">
                <div class="stat-label">Late</div>
                <div class="stat-value" id="summaryLate">{{ $summary['Late'] ?? 0 }}</div>
            </div>
            <div class="stat-box earlyout">
                <div class="stat-label">Early Out</div>
                <div class="stat-value" id="summaryEarlyOut">{{ $summary['EarlyOut'] ?? 0 }}</div>
            </div>
        </div>


        @if (isset($mergedData) && $mergedData->isEmpty())
            <div class="alert alert-info">
                Please! Select Employee and Date Range to Filter Record!
            </div>
        @elseif(isset($mergedData))
            <table id="employeeReportTable" class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>Employee Code</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Branch</th>
                        <th>Management Type</th>
                        <th>Check Date</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Flag</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mergedData as $row)
                        <tr>
                            <td>{{ $row->HPI_EMP_CODE }}</td>
                            <td>{{ $row->HPI_EMP_NAME }}</td>
                            <td>{!! html_entity_decode($row->PDP_DESC) !!}</td>
                            <td>{{ $row->PLC_DESC }}</td>
                            <td>{{ $row->PHG_DESC }}</td>
                            <td>{{ $row->CHECKDATE != 'N/A' ? date('d M Y', strtotime($row->CHECKDATE)) : 'N/A' }}</td>
                            <td class="{{ in_array($row->DAY_NAME, ['Saturday', 'Sunday']) ? 'day-weekend' : '' }}">
                                {{ $row->DAY_NAME }}
                            </td>
                            <td>{{ $row->STATUS }}</td>
                            <td>{{ $row->CHECKIN != 'N/A' ? date('h:i A', strtotime($row->CHECKIN)) : 'N/A' }}</td>
                            <td>{{ $row->CHECKOUT != 'N/A' ? date('h:i A', strtotime($row->CHECKOUT)) : 'N/A' }}</td>
                            <td>
                                @if ($row->FLAG === 'Off Day')
                                    <span class="flag-offday">{{ $row->FLAG }}</span>
                                @elseif($row->FLAG === 'Late + EarlyOut')
                                    <span class="flag-both">{{ $row->FLAG }}</span>
                                @elseif($row->FLAG === 'Late')
                                    <span class="flag-late">{{ $row->FLAG }}</span>
                                @elseif($row->FLAG === 'EarlyOut')
                                    <span class="flag-earlyout">{{ $row->FLAG }}</span>
                                @elseif($row->FLAG === 'Ok')
                                    <span class="flag-ok">{{ $row->FLAG }}</span>
                                @elseif($row->FLAG === 'Absent')
                                    <span class="flag-absent">{{ $row->FLAG }}</span>
                                @else
                                    <span class="flag-na">{{ $row->FLAG }}</span>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif


    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.3.2/js/dataTables.fixedHeader.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {


            function setDefaultDates() {
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

                const formatDate = (date) => {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };

                if (!$('#from_date').val()) {
                    $('#from_date').val(formatDate(firstDay));
                }
                if (!$('#to_date').val()) {
                    $('#to_date').val(formatDate(today));
                }
            }

            setDefaultDates();


            $('.select2').select2({
                placeholder: "Select",
                closeOnSelect: false,
                width: '100%'
            });


            let employeeStats = {};
            let allRows = $('#employeeReportTable tbody tr');

            allRows.each(function(index) {
                let empCode = $(this).find('td:eq(0)').text().trim();
                if (!employeeStats[empCode]) {
                    employeeStats[empCode] = {
                        present: 0,
                        absent: 0,
                        leave: 0
                    };
                }
                let status = $(this).find('td:eq(7)').text().trim();
                if (status === 'Present') employeeStats[empCode].present++;
                else if (status === 'Absent') employeeStats[empCode].absent++;
                else if (status === 'Leave') employeeStats[empCode].leave++;
            });


            if ($('#employeeReportTable').length) {
                let table = $('#employeeReportTable').DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    scrollX: true,
                    fixedHeader: true,
                    dom: 'Bfrtip',
                    buttons: [{
                            extend: 'excel',
                            className: 'btn btn-success btn-sm',
                            title: 'Employee Wise Attendance Report - ' + ($('#empcode').val() || 'All')
                        },
                        {
                            extend: 'pdf',
                            className: 'btn btn-danger btn-sm',
                            title: 'Employee Wise Attendance Report - ' + ($('#empcode').val() ||
                                'All'),
                            orientation: 'landscape',
                            pageSize: 'A4'
                        }
                    ],
                    pageLength: 25,
                    order: [
                        [5, 'asc']
                    ]
                });


                let filteredEmpCode = $('#empcode').val();
                if (filteredEmpCode && employeeStats[filteredEmpCode]) {
                    $('#empCodeLabel').text('Emp: ' + filteredEmpCode);
                    $('#summaryPresent').text(employeeStats[filteredEmpCode].present);
                    $('#summaryAbsent').text(employeeStats[filteredEmpCode].absent);
                    $('#summaryLeave').text(employeeStats[filteredEmpCode].leave);
                }


                $(document).on('click', '#employeeReportTable tbody tr', function() {
                    let empCode = $(this).find('td:eq(0)').text().trim();
                    let stats = employeeStats[empCode];

                    if (stats) {
                        $('#empCodeLabel').text('Emp: ' + empCode);
                        $('#summaryPresent').text(stats.present);
                        $('#summaryAbsent').text(stats.absent);
                        $('#summaryLeave').text(stats.leave);
                    }
                });


                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#employeeReportTable tbody tr, .stats-container').length) {

                        if (!$('#empcode').val()) {
                            $('#empCodeLabel').text('');
                            $('#summaryPresent').text('0');
                            $('#summaryAbsent').text('0');
                            $('#summaryLeave').text('0');
                        }
                    }
                });
            }


            $('#filterForm').on('submit', function() {
                $('#loadingOverlay').show();
            });


            $(window).on('load', function() {
                setTimeout(() => {
                    $('#loadingOverlay').hide();
                }, 500);
            });


            setTimeout(() => {
                $('#loadingOverlay').hide();
            }, 30000);


            $('#resetBtn').on('click', function(e) {
                e.preventDefault();
                setDefaultDates();
                $('#empcode').val('');
                $('#branch, #department, #employee_type').val(null).trigger('change');
                $('#loadingOverlay').show();
                setTimeout(() => {
                    $('#filterForm').submit();
                }, 300);
            });


            setTimeout(() => {
                $('#loadingText').text('Processing your request, please wait...');
            }, 2000);

        });
    </script>

</body>

</html>

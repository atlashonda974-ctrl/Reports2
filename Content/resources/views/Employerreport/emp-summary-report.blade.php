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
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            flex-direction: column;
        }

        .loading-overlay.show {
            display: flex;
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

        .form-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .form-label {
            white-space: nowrap;
            flex-shrink: 0;
            min-width: 6.25rem;
            text-align: right;
            margin-bottom: 0 !important;
        }

        .select2-container {
            width: 100% !important;
        }

        .stats-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
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

        .stat-box.absent {
            border-left-color: #dc3545;
        }

        .stat-box.absent .stat-value {
            color: #dc3545;
        }

        .stat-box.late {
            border-left-color: #ffc107;
        }

        .stat-box.late .stat-value {
            color: #ffc107;
        }

        .stat-box.early {
            border-left-color: #0dcaf0;
        }

        .stat-box.early .stat-value {
            color: #0dcaf0;
        }

        .stat-box.present {
            border-left-color: #28a745;
        }

        .stat-box.present .stat-value {
            color: #28a745;
        }

        .no-data-message {
            text-align: center;
            padding: 3rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            border: 2px dashed #dee2e6;
        }

        .no-data-message i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .no-data-message h4 {
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .no-data-message p {
            color: #6c757d;
        }
    </style>
</head>

<body>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="loading-text" id="loadingText">Loading Employee Summary Data...</div>
        </div>
    </div>

    <div class="container mt-5">
        <x-report-header title="Employee's Summary Report" />


        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif


       <div class="mb-4">
    <form id="filterForm" method="GET" action="{{ route('summary.wise.report') }}">
        <div class="row g-3 align-items-center">

            <!-- From Date -->
            <div class="col-auto d-flex align-items-center">
                <label for="from_date" class="form-label me-2 mb-0" style="width: 100px;">From Date:</label>
                <input type="date" id="from_date" name="from_date" class="form-control"
                    value="{{ request('from_date', '2025-07-01') }}" required>
            </div>

            <!-- To Date -->
            <div class="col-auto d-flex align-items-center">
                <label for="to_date" class="form-label me-2 mb-0" style="width: 100px;">To Date:</label>
                <input type="date" id="to_date" name="to_date" class="form-control"
                    value="{{ request('to_date', now()->toDateString()) }}" required>
            </div>

            <!-- Branch -->
            <div class="col-auto d-flex align-items-center">
                <label for="branch" class="form-label me-2 mb-0" style="width: 100px;">Branch:</label>
                <select id="branch" name="branch[]" class="form-control select2" multiple>
                    @foreach ($branches->unique('PLC_LOC_CODE') as $b)
                        <option value="{{ $b->PLC_LOC_CODE }}"
                            {{ in_array($b->PLC_LOC_CODE, (array) request('branch', [])) ? 'selected' : '' }}>
                            {{ $b->PLC_LOC_CODE }} : {{ $b->PLC_DESC }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Department -->
            <div class="col-auto d-flex align-items-center">
                <label for="department" class="form-label me-2 mb-0" style="width: 100px;">Department:</label>
                <select id="department" name="department[]" class="form-control select2" multiple>
                    @foreach ($departments as $d)
                        <option value="{{ $d->PDP_DESC }}"
                            {{ in_array($d->PDP_DESC, (array) request('department', [])) ? 'selected' : '' }}>
                            {!! html_entity_decode($d->PDP_DESC) !!}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Employee Type -->
            <div class="col-auto d-flex align-items-center">
                <label for="employee_type" class="form-label me-2 mb-0" style="width: 150px;">Employee Type:</label>
                <select id="employee_type" name="employee_type[]" class="form-control select2" multiple>
                    <option value="">Select Employee</option>
                    @foreach ($employeeTypes as $e)
                        <option value="{{ $e->PHG_DESC }}"
                            {{ in_array($e->PHG_DESC, (array) request('employee_type', [])) ? 'selected' : '' }}>
                            {{ $e->PHG_DESC }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Employee Name/Code -->
            <div class="col-auto d-flex align-items-center">
                <label for="empcode" class="form-label me-2 mb-0" style="width: 100px;">Employee:</label>
                <select id="empcode" name="empcode" class="form-control select2-single">
                    <option value="">Select Employee</option>
                    @foreach ($normalizedEmployees as $emp)
                        <option value="{{ $emp->HPI_EMP_CODE }}"
                            {{ request('empcode') == $emp->HPI_EMP_CODE ? 'selected' : '' }}>
                            {{ $emp->HPI_EMP_CODE }} : {{ $emp->HPI_EMP_NAME }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-4 d-flex align-items-center mt-3">
                <button type="submit" id="filterBtn" class="btn btn-outline-primary me-3">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                <button type="button" id="resetBtn" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
            </div>

        </div>



        @if (isset($summaryData) && $summaryData->isNotEmpty())
            <div class="stats-container">
                <div class="stat-box present">
                    <div class="stat-label">Total Present</div>
                    <div class="stat-value">{{ $summary['Present'] ?? 0 }}</div>
                </div>
                <div class="stat-box absent">
                    <div class="stat-label">Total Absent</div>
                    <div class="stat-value">{{ $summary['Absent'] ?? 0 }}</div>
                </div>
                <div class="stat-box late">
                    <div class="stat-label">Late Count</div>
                    <div class="stat-value">{{ $summary['Late'] ?? 0 }}</div>
                </div>
                <div class="stat-box early">
                    <div class="stat-label">EarlyOut Count</div>
                    <div class="stat-value">{{ $summary['EarlyOut'] ?? 0 }}</div>
                </div>
            </div>
        @endif


        @if (!isset($summaryData) || $summaryData->isEmpty())
            <div class="alert alert-info">
                Please! Select Date Range to Filter Record!
            </div>
        @else
            <table id="summaryTable" class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>Employee Code</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Branch</th>
                        <th>Management Type</th>
                        <th>Total Working Days</th>

                        <th>Total Present</th>
                        <th>Total Absent</th>
                        <th>Late Count</th>
                        <th>EarlyOut Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summaryData as $row)
                        <tr>
                            <td>{{ $row->EMP_CODE }}</td>
                            <td>{{ $row->EMP_NAME }}</td>
                            <td>{!! html_entity_decode($row->DEPARTMENT) !!}</td>
                            <td>{{ $row->BRANCH }}</td>
                            <td>{{ $row->MANAGEMENT_TYPE }}</td>
                            <td>{{ $row->TOTAL_WORKING_DAYS }}</td>

                            <td>{{ $row->TOTAL_PRESENT }}</td>
                            <td>{{ $row->TOTAL_ABSENT }}</td>
                            <td>{{ $row->LATE_COUNT }}</td>
                            <td>{{ $row->EARLYOUT_COUNT }}</td>
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

            $('.select2').select2({
                placeholder: "Select",
                closeOnSelect: false,
                width: '100%',
                allowClear: true
            });

            @if (isset($summaryData) && $summaryData->isNotEmpty())
                let table = $('#summaryTable').DataTable({
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
                            title: 'Employee Summary Report'
                        },
                        {
                            extend: 'pdf',
                            className: 'btn btn-danger btn-sm',
                            title: 'Employee Summary Report',
                            orientation: 'landscape',
                            pageSize: 'A4'
                        }
                    ],
                    pageLength: 25
                });
            @endif


            $('#filterForm').on('submit', function(e) {

                if (!$('#from_date').val() || !$('#to_date').val()) {
                    e.preventDefault();
                    alert('Please select both From Date and To Date');
                    return false;
                }
                $('#loadingOverlay').addClass('show');
            });
            $('.select2-single').select2({
                placeholder: "Select Employee",
                allowClear: true,
                width: '100%'
            });


            $('#resetBtn').on('click', function(e) {
                e.preventDefault();

                $('#from_date, #to_date, #empcode').val('');
                $('#branch, #department, #employee_type').val(null).trigger('change');


                window.location.href = "{{ route('summary.wise.report') }}";
            });


            $(window).on('load', function() {
                setTimeout(() => $('#loadingOverlay').removeClass('show'), 500);
            });
        });
    </script>
</body>

</html>

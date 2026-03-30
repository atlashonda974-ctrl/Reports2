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
    text-align: center;
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

.loading-content {
    background: white;
    padding: 2.5rem 4rem;
    border-radius: 1rem;
    text-align: center;
    box-shadow: 0 0.5rem 2rem rgba(0,0,0,0.4);
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

.loading-subtext {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: #666;
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

.stat-box.present { border-left-color: #28a745; }
.stat-box.present .stat-value { color: #28a745; }

.stat-box.absent { border-left-color: #dc3545; }
.stat-box.absent .stat-value { color: #dc3545; }

.stat-box.leave { border-left-color: #ffc107; }
.stat-box.leave .stat-value { color: #ffc107; }


.time-filter-card {
    border: none;
    border-radius: 0.625rem;
    box-shadow: 0 0.25rem 0.375rem rgba(0,0,0,0.1); 
}


.time-tab {
    flex: 1;
    min-width: 5rem;   
    max-width: 6.25rem; 
    border-radius: 0.5rem; 
    padding: 0.5rem 0.3125rem; 
    text-align: center;
    font-size: 0.875rem; 
    transition: all 0.2s;
    text-decoration: none;
}

.time-tab:hover {
    transform: translateY(-0.125rem); 
    box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.15); 
}

.time-tab.active {
    background: linear-gradient(45deg, #007bff, #0056b3);
    color: white !important;
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


.form-group {
    display: flex;             
    align-items: center;       
    gap: 0.75rem;              
    margin-bottom: 0.75rem;    
}

.form-group .form-label {
    white-space: nowrap;       
    flex-shrink: 0;            
    min-width: 6.25rem;        
    text-align: right;         
}

.form-group .form-control,
.form-group .select2-container {
    flex: 1;                    
    min-width: 0;               
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

#reportsTable_wrapper {
    margin-bottom: 4.375rem; 
}


.form-label {
    white-space: nowrap;
    margin-bottom: 0 !important;
    min-width: 6.25rem; 
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
    <x-report-header title="Employee's Attendance Report" />

   
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <form id="filterForm" method="GET" action="{{ route('employer.report') }}">
            <div class="row g-3">

                <!-- From Date -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="from_date" class="form-label me-2">From Date</label>
                    <input type="date" id="from_date" name="from_date" class="form-control" 
                           value="{{ request('from_date') }}">
                </div>

                <!-- To Date -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="to_date" class="form-label me-2">To Date</label>
                    <input type="date" id="to_date" name="to_date" class="form-control" 
                           value="{{ request('to_date') }}">
                </div>

                <!-- Branch -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="branch" class="form-label me-2">Branch</label>
                    <select id="branch" name="branch[]" class="form-control select2" multiple>
                        @foreach($branches->unique('PLC_LOC_CODE') as $b)
                            <option value="{{ $b->PLC_LOC_CODE }}" 
                                {{ in_array($b->PLC_LOC_CODE, (array)request('branch', [])) ? 'selected' : '' }}>
                                {{ $b->PLC_LOC_CODE }} : {{ $b->PLC_DESC }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Department -->
                <div class="col-md-3 d-flex align-items-center">
                    <label for="department" class="form-label me-2">Department</label>
                    <select id="department" name="department[]" class="form-control select2" multiple>
                        @foreach($departments as $d)
                            <option value="{{ $d->PDP_DESC }}"
                                {{ in_array($d->PDP_DESC, (array)request('department', [])) ? 'selected' : '' }}>
                                {!! html_entity_decode($d->PDP_DESC) !!}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Employee Type -->
                <div class="col-md-3 d-flex align-items-center mt-3">
                    <label for="employee_type" class="form-label me-2">Employee Type</label>
                    <select id="employee_type" name="employee_type[]" class="form-control select2" multiple>
                        @foreach($employeeTypes as $e)
                            <option value="{{ $e->PHG_DESC }}"
                                {{ in_array($e->PHG_DESC, (array)request('employee_type', [])) ? 'selected' : '' }}>
                                {{ $e->PHG_DESC }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-4 d-flex align-items-center mt-3">
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

    {{-- <!-- Summary Statistics Above Search -->
    <div class="stats-container">
        <div style="font-weight: 600; color: #333; font-size: 0.9rem;" id="empCodeLabel"></div>
        <div class="stat-box present">
            <div class="stat-label">Present</div>
            <div class="stat-value" id="summaryPresent">0</div>
        </div>
        <div class="stat-box absent">
            <div class="stat-label">Absent</div>
            <div class="stat-value" id="summaryAbsent">0</div>
        </div>
        <div class="stat-box leave">
            <div class="stat-label">Leave</div>
            <div class="stat-value" id="summaryLeave">0</div>
        </div>
    </div> --}}

    <!-- Data Table -->
    @if(isset($mergedData) && $mergedData->isEmpty())
        <div class="alert alert-danger">No data available.</div>
    @elseif(isset($mergedData))
        <table id="reportsTable" class="table table-bordered table-responsive">
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
            <tbody id="tableBody">
            @foreach($mergedData as $row)
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
                    <td>
    @if(in_array($row->DAY_NAME, ['Saturday', 'Sunday']))
        Off Day
    @else
        {{ $row->STATUS }}
    @endif
</td>

                    <td>
                        @if($row->CHECKIN != 'N/A' && $row->CHECKIN)
                            {{ date('h:i A', strtotime($row->CHECKIN)) }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($row->CHECKOUT != 'N/A' && $row->CHECKOUT)
                            {{ date('h:i A', strtotime($row->CHECKOUT)) }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($row->FLAG === 'Off Day')
                            <span class="flag-offday">{{ $row->FLAG }}</span>
                        @elseif($row->FLAG === 'Late + EarlyOut')
                            <span class="flag-both">{{ $row->FLAG }}</span>
                        @elseif($row->FLAG === 'Late')
                            <span class="flag-late">{{ $row->FLAG }}</span>
                        @elseif($row->FLAG === 'EarlyOut')
                            <span class="flag-earlyout">{{ $row->FLAG }}</span>
                        @elseif($row->FLAG === 'Ok')
                            <span class="flag-ok">{{ $row->FLAG }}</span>
                            
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
    
    function decodeHtml(html) {
        var txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    }

    // Set default dates
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

  
    let table = $('#reportsTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        scrollX: true,
        fixedHeader: true,
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', className: 'btn btn-success btn-sm', title: 'Employer Attendance Report' },
            { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'Employer Attendance Report', orientation: 'landscape', pageSize: 'A4' }
        ],
        pageLength: 25
    });

    // // Function to calculate stats for a specific employee code
    // function calculateEmployeeStats(empCode) {
    //     let stats = { present: 0, absent: 0, leave: 0 };
        
    //     // IMPORTANT: Search in ALL rows, not just visible ones
    //     // Use table.rows() to get all rows including those on other pages
    //     table.rows().every(function() {
    //         let rowNode = this.node();
    //         let rowEmpCode = $(rowNode).find('td:eq(0)').text().trim();
            
    //         // Only count rows for this specific employee
    //         if (rowEmpCode === empCode) {
    //             let status = $(rowNode).find('td:eq(7)').text().trim();
                
    //             if (status === 'Present') {
    //                 stats.present++;
    //             } else if (status === 'Absent') {
    //                 stats.absent++;
    //             } else if (status === 'Leave') {
    //                 stats.leave++;
    //             }
    //         }
    //     });
        
    //     return stats;
    // }

    
    $('#reportsTable').on('click', 'tbody tr', function() {
        let empCode = $(this).find('td:eq(0)').text().trim();
        
     
        let stats = calculateEmployeeStats(empCode);
        
      
        $('#empCodeLabel').text('Emp: ' + empCode);
        $('#summaryPresent').text(stats.present);
        $('#summaryAbsent').text(stats.absent);
        $('#summaryLeave').text(stats.leave);
    });

   
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#reportsTable tbody tr, .stats-container').length) {
            $('#empCodeLabel').text('');
            $('#summaryPresent').text('0');
            $('#summaryAbsent').text('0');
            $('#summaryLeave').text('0');
        }
    });

   
    $('#filterForm').on('submit', function() {
        $('#loadingOverlay').addClass('show');
    });

   
    $('#resetBtn').on('click', function(e) {
        e.preventDefault();
        setDefaultDates();
        $('#branch, #department, #employee_type').val(null).trigger('change');
        $('#loadingOverlay').addClass('show');
        setTimeout(() => {
            $('#filterForm').submit();
        }, 300);
    });

 
    $(window).on('load', function() {
        setTimeout(() => {
            $('#loadingOverlay').removeClass('show');
        }, 500);
    });

    
    setTimeout(() => {
        $('#loadingOverlay').removeClass('show');
    }, 30000);
    
    window.addEventListener('load', function() {
        const loadingText = document.getElementById('loadingText');

      
        setTimeout(() => {
            loadingText.textContent = 'Processing your request, please wait...';
        }, 2000);

        
        setTimeout(() => {
            document.getElementById('loadingOverlay').style.display = 'none';
        }, 5000);
    });
});
</script>
</body>
</html>
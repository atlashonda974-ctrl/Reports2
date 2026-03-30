@extends('master')
@section('content')
<?php
use Illuminate\Support\Facades\Session; 
$userRole = Session::get('user')['role'] ?? 'user';
?>

<div class="content-body">
    <div class="container-fluid">
        <!-- Pre-loader -->
        <div id="preloader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.95); z-index: 9999; justify-content: center; align-items: center;">
            <div style="text-align: center; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); max-width: 450px; width: 90%; border: 1px solid #e0e0e0;">
                <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <div style="margin-top: 25px;">
                    <h5 style="color: #333; font-weight: 600; font-size: 1.25rem;">Generating Report...</h5>
                    <p style="color: #666; margin-bottom: 20px;">Fetching data... This may take a moment...</p>
                    <div class="progress mt-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 100%"></div>
                    </div>
                    <p class="text-muted mt-2" id="dateRangeInfo" style="font-size: 0.9rem;"></p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Dealer Wise Claims Report</h4>
                    </div>
                    
                    <div class="card-body">
                        <!-- Messages -->
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        @if(session('info') || isset($info))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fa fa-info-circle me-2"></i>
                            {{ session('info') ?? $info }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        @if(session('error') || isset($error))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            {{ session('error') ?? $error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        <!-- Filter Form -->
                        <form class="form-horizontal" role="form" method="POST" 
                              action="{{ route('dealer-claims.generate') }}" 
                              autocomplete="off" 
                              id="reportForm">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>From Date <span class="text-danger">*</span></label>
                                        <input name="fromDate" 
                                               class="form-control @error('fromDate') is-invalid @enderror" 
                                               id="from_date" 
                                               type="date" 
                                               value="{{ old('fromDate', $fromDate) }}" 
                                               required>
                                        @error('fromDate')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>To Date <span class="text-danger">*</span></label>
                                        <input name="toDate" 
                                               class="form-control @error('toDate') is-invalid @enderror" 
                                               id="to_date" 
                                               type="date" 
                                               value="{{ old('toDate', $toDate) }}" 
                                               required>
                                        @error('toDate')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>


                                    <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Branch <span class="text-danger">*</span></label>
                                       <select name="location_category" id="location_category" class="form-control select2">
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->fbracode }}" 
                                                    {{ request('location_category') == $branch->fbracode ? 'selected' : '' }}>
                                                {{ $branch->fbradsc }}
                                            </option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <button type="submit" 
                                                class="btn btn-primary btn-block" 
                                                id="generateBtn">
                                            <i class="fa fa-search me-2"></i> Generate Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Main Report Table -->
                        @if($showReport)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class="filter-controls">
                                            <div class="input-group" style="width: 350px;">
                                                <select class="form-control form-control-sm" id="liveDealerFilter">
                                                    <option value="">All Dealers</option>
                                                    @if(isset($dealers) && count($dealers) > 0)
                                                        @foreach($dealers as $dealer)
                                                            <option value="{{ $dealer['dealer_code'] ?? '' }}">
                                                                {{ $dealer['dealer_code'] ?? '' }} - {{ $dealer['contact_person'] ?? '' }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <button class="btn btn-outline-secondary" type="button" id="clearFilterBtn" title="Clear filter">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if(count($claims) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered" id="dealerReportTable" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Sr#</th>
                                                        <th>DEALER CODE</th>
                                                        <th>USERNAME</th>
                                                        <th>CNIC</th>
                                                        <th>COVER TYPE</th>
                                                        <th>APPLICATION DATE</th>
                                                        <th>GIS NUMBER</th>
                                                        <th>TOTAL CLAIMS</th>
                                                        <th>VEHICLE</th>
                                                        <th>PREMIUM</th>
                                                        <th>BRAND</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($claims as $index => $claim)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td class="dealer-code">{{ $claim['dealer_code'] ?? 'N/A' }}</td>
                                                        <td>{{ $claim['username'] ?? 'N/A' }}</td>
                                                        <td>{{ $claim['cnic'] ?? 'N/A' }}</td>
                                                        <td>{{ $claim['cover_type'] ?? 'N/A' }}</td>
                                                        <td>{{ date('d-M-Y H:i', strtotime($claim['application_date'] ?? '')) }}</td>
                                                        <td>{{ $claim['gis'] ?? 'N/A' }}</td>
                                                        <td class="text-right">{{ number_format($claim['tot_clm']) }}</td>
                                                        <td>{{ $claim['veh_name'] ?? 'N/A' }}</td>
                                                        <td class="text-right">{{ number_format($claim['premium'] ?? 0) }}</td>
                                                        <td>{{ $claim['brand_name'] ?? 'N/A' }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-dark">
                                                        <td colspan="7" class="text-end"><strong>Total Claims:</strong></td>
                                                        <td class="text-right"><strong id="totalClaims">{{ number_format(array_sum(array_column($claims, 'tot_clm'))) }}</strong></td>
                                                        <td class="text-end"><strong>Total Premium:</strong></td>
                                                        <td class="text-right"><strong id="totalPremium">{{ number_format(array_sum(array_column($claims, 'premium'))) }}</strong></td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        @else
                                        <div class="text-center py-5">
                                            <i class="fa fa-database fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No Data Available</h5>
                                            <p class="text-muted">No claims data found for the selected period</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif(!$showReport && !session('error') && !isset($error) && !session('info') && !isset($info))
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle me-2"></i> 
                                    Please select date range and click "Generate Report" to view data.
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>

<style>
    /* Preloader styles */
    #preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.95);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    /* DataTable custom styles */
    #dealerReportTable {
        border-collapse: collapse !important;
    }
    
    #dealerReportTable th {
        background-color: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
    }
    
    /* Hide extra "Show [25] entries" label */
    .dataTables_wrapper .dataTables_length label {
        display: none !important;
    }
    
    /* Style the entries dropdown */
    .dataTables_length select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 6px 30px 6px 12px;
        background-color: white;
        margin-left: 10px;
    }
    
    /* Style search box */
    .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 6px 12px;
        background-color: white;
    }
    
    /* Buttons styling */
    .dt-buttons .btn {
        margin-left: 5px;
        padding: 6px 12px;
        font-size: 14px;
    }
    
    /* Footer styling */
    #dealerReportTable tfoot tr {
        background-color: #343a40 !important;
        color: white;
    }
    
    #dealerReportTable tfoot td {
        border-top: 2px solid #dee2e6;
    }
    
    .table-responsive {
        min-height: 400px;
    }
    
    /* Adjust DataTables wrapper */
    .dataTables_wrapper {
        margin-top: 10px;
    }
    
    /* Card header styles */
    .filter-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    /* Clear filter button */
    #clearFilterBtn {
        border-left: 1px solid #dee2e6;
    }
</style>

<script>
// Global variable for DataTable
var dealerDataTable = null;
var originalTotalClaims = {{ array_sum(array_column($claims, 'tot_clm')) }};
var originalTotalPremium = {{ array_sum(array_column($claims, 'premium')) }};
var totalRecords = {{ count($claims) }};

// Preloader functions
function showPreloader(days) {
    document.getElementById('preloader').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    var dateRangeInfo = document.getElementById('dateRangeInfo');
    if (dateRangeInfo && days > 0) {
        if (days > 90) {
            dateRangeInfo.innerHTML = '<i class="fa fa-exclamation-triangle text-warning me-1"></i> Processing ' + days + ' days of data. This may take a while...';
            dateRangeInfo.style.color = '#ff9800';
        } else {
            dateRangeInfo.innerHTML = 'Processing ' + days + ' days of data...';
            dateRangeInfo.style.color = '#666';
        }
    }
}

function hidePreloader() {
    document.getElementById('preloader').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Form validation and submission
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates
    var today = new Date();
    var lastMonth = new Date(today);
    lastMonth.setDate(today.getDate() - 30);
    
    var fromDateInput = document.getElementById('from_date');
    var toDateInput = document.getElementById('to_date');
    
    if (fromDateInput && !fromDateInput.value) {
        fromDateInput.value = lastMonth.toISOString().split('T')[0];
    }
    if (toDateInput && !toDateInput.value) {
        toDateInput.value = today.toISOString().split('T')[0];
    }
    
    // Form submission
    var form = document.getElementById('reportForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            var fromDate = document.getElementById('from_date').value;
            var toDate = document.getElementById('to_date').value;
            
            if (!fromDate || !toDate) {
                alert('Please select both from and to dates');
                e.preventDefault();
                return false;
            }
            
            if (fromDate > toDate) {
                alert('To date cannot be earlier than From date');
                e.preventDefault();
                return false;
            }
            
            var date1 = new Date(fromDate);
            var date2 = new Date(toDate);
            var timeDiff = Math.abs(date2.getTime() - date1.getTime());
            var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            showPreloader(diffDays);
            
            var btn = document.getElementById('generateBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Processing...';
            }
            
            return true;
        });
    }
    
    // Initialize dealer filter dropdown
    var dealerFilter = document.getElementById('liveDealerFilter');
    if (dealerFilter) {
        dealerFilter.addEventListener('change', function() {
            applyDealerFilter(this.value);
        });
    }
    
    // Clear filter button
    var clearFilterBtn = document.getElementById('clearFilterBtn');
    if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', function() {
            clearDealerFilter();
        });
    }
});

// Initialize DataTable after all scripts are loaded
window.addEventListener('load', function() {
    hidePreloader();
    
    var btn = document.getElementById('generateBtn');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-search me-2"></i> Generate Report';
    }
    
    @if(session('error') || isset($error) || session('info') || isset($info))
        hidePreloader();
    @endif
    
    // Initialize DataTable after all scripts are loaded
    @if($showReport && count($claims) > 0)
    setTimeout(initializeDataTable, 500);
    @endif
});

// Apply dealer filter
function applyDealerFilter(dealerCode) {
    if (!dealerDataTable) return;
    
    if (!dealerCode) {
        // Clear filter - show all data
        dealerDataTable.search('').columns().search('').draw();
        console.log('Showing all records');
    } else {
        // Filter by dealer code (column index 1 is dealer code)
        dealerDataTable.columns().search(''); // Clear all column searches
        dealerDataTable.column(1).search('^' + dealerCode + '$', true, false).draw();
        
        console.log('Filtered by dealer:', dealerCode);
    }
}

// Clear dealer filter
function clearDealerFilter() {
    if (!dealerDataTable) return;
    
    // Clear all searches
    dealerDataTable.search('').columns().search('').draw();
    
    // Reset dealer dropdown
    document.getElementById('liveDealerFilter').value = '';
    
    console.log('All filters cleared');
}

// DataTable initialization
function initializeDataTable() {
    try {
        console.log('Initializing DataTable...');
        
        // Check if jQuery and DataTables are loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        
        if (typeof $.fn.DataTable === 'undefined') {
            console.error('DataTables is not loaded');
            return;
        }
        
        var table = $('#dealerReportTable');
        
        if (table.length === 0) {
            console.error('Table not found');
            return;
        }
        
        // Check if DataTable is already initialized
        if ($.fn.dataTable.isDataTable(table)) {
            console.log('DataTable already initialized, destroying...');
            table.DataTable().destroy();
        }
        
        var currentDate = new Date();
        var formattedDate = currentDate.getFullYear() + '-' + 
                          String(currentDate.getMonth() + 1).padStart(2, '0') + '-' + 
                          String(currentDate.getDate()).padStart(2, '0') + ' ' +
                          String(currentDate.getHours()).padStart(2, '0') + ':' + 
                          String(currentDate.getMinutes()).padStart(2, '0') + ':' + 
                          String(currentDate.getSeconds()).padStart(2, '0');
        
        // Create filename
        var fromDate = '{{ $fromDate }}';
        var toDate = '{{ $toDate }}';
        var fileName = 'Dealer_Claims_Report_' + fromDate + '_to_' + toDate + '.xlsx';
        
        // Initialize DataTable with proper DOM layout
        dealerDataTable = table.DataTable({
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "order": [[5, "desc"]], // Sort by Application Date (now column 5 after removing dealer name)
            "responsive": true,
            "dom": "<'row'<'col-sm-6'B><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
            "language": {
                "search": "",
                "searchPlaceholder": "Search in all columns...",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "zeroRecords": "No matching records found",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "buttons": [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel me-2"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    filename: fileName,
                    title: '',
                    messageTop: function() {
                        var dealerFilter = document.getElementById('liveDealerFilter');
                        var filterInfo = '';
                        if (dealerFilter && dealerFilter.value) {
                            var selectedOption = dealerFilter.options[dealerFilter.selectedIndex];
                            var dealerName = selectedOption.text.split(' - ')[1] || selectedOption.text;
                            filterInfo = '\nDealer: ' + dealerFilter.value + ' - ' + dealerName;
                        }
                        
                        return [
                            'DEALER WISE CLAIMS REPORT',
                            'Date Range: {{ $fromDate }} to {{ $toDate }}',
                            'Generated: ' + formattedDate + filterInfo,
                            '',
                            ''
                        ].join('\n');
                    },
                    exportOptions: {
                        columns: ':visible',
                        format: {
                            body: function(data, row, column, node) {
                                // Remove commas from numbers for Excel (columns 7 and 9 are zero-indexed after removing dealer name)
                                if (column === 7 || column === 9) {
                                    return data.replace(/,/g, '');
                                }
                                return data;
                            }
                        }
                    },
                    customize: function(xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        
                        // Find the last data row
                        var rows = $('row', sheet);
                        var lastRow = rows.last();
                        var lastRowNum = parseInt(lastRow.attr('r'));
                        
                        // Add totals row
                        $(lastRow).after(
                            '<row r="' + (lastRowNum + 1) + '">' +
                            '<c r="A' + (lastRowNum + 1) + '" t="inlineStr"><is><t>Total Claims:</t></is></c>' +
                            '<c r="H' + (lastRowNum + 1) + '" t="inlineStr"><is><t>' + originalTotalClaims.toLocaleString() + '</t></is></c>' +
                            '<c r="I' + (lastRowNum + 1) + '" t="inlineStr"><is><t>Total Premium:</t></is></c>' +
                            '<c r="J' + (lastRowNum + 1) + '" t="inlineStr"><is><t>' + originalTotalPremium.toLocaleString() + '</t></is></c>' +
                            '</row>'
                        );
                        
                        // Style the totals row (bold)
                        $(lastRow).next().find('c').each(function() {
                            $(this).attr('s', '2'); // Bold style
                        });
                    }
                },
                {
                    extend: 'copy',
                    text: '<i class="fa fa-copy me-2"></i> Copy',
                    className: 'btn btn-info btn-sm',
                    title: function() {
                        var dealerFilter = document.getElementById('liveDealerFilter');
                        var filterInfo = '';
                        if (dealerFilter && dealerFilter.value) {
                            var selectedOption = dealerFilter.options[dealerFilter.selectedIndex];
                            var dealerName = selectedOption.text.split(' - ')[1] || selectedOption.text;
                            filterInfo = '\nDealer: ' + dealerFilter.value + ' - ' + dealerName;
                        }
                        
                        return 'DEALER WISE CLAIMS REPORT\nDate Range: {{ $fromDate }} to {{ $toDate }}\nGenerated: ' + formattedDate + filterInfo + '\n\n';
                    },
                    exportOptions: {
                        columns: ':visible'
                    },
                    messageBottom: '\n\nTotal Claims: ' + originalTotalClaims.toLocaleString() + '\t\tTotal Premium: ' + originalTotalPremium.toLocaleString()
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print me-2"></i> Print',
                    className: 'btn btn-warning btn-sm',
                    title: '',
                    messageTop: function() {
                        var dealerFilter = document.getElementById('liveDealerFilter');
                        var filterInfo = '';
                        if (dealerFilter && dealerFilter.value) {
                            var selectedOption = dealerFilter.options[dealerFilter.selectedIndex];
                            var dealerName = selectedOption.text.split(' - ')[1] || selectedOption.text;
                            filterInfo = '<p><strong>Dealer:</strong> ' + dealerFilter.value + ' - ' + dealerName + '</p>';
                        }
                        
                        return '<h3>DEALER WISE CLAIMS REPORT</h3>' +
                               '<p><strong>Date Range:</strong> {{ $fromDate }} to {{ $toDate }}</p>' +
                               '<p><strong>Generated:</strong> ' + formattedDate + '</p>' +
                               filterInfo +
                               '<hr>';
                    },
                    messageBottom: function() {
                        return '<hr>' +
                               '<div style="text-align: center; margin-top: 20px; padding: 10px; background-color: #f8f9fa; border: 1px solid #dee2e6;">' +
                               '<strong>Total Claims:</strong> ' + originalTotalClaims.toLocaleString() + ' | ' +
                               '<strong>Total Premium:</strong> ' + originalTotalPremium.toLocaleString() +
                               '</div>';
                    },
                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function(win) {
                        $(win.document.body).find('table').addClass('table-bordered');
                        $(win.document.body).find('h1').css('text-align', 'center');
                    }
                }
            ],
            "drawCallback": function(settings) {
                // Get current filtered data
                var api = this.api();
                var filteredData = api.rows({ search: 'applied' }).data();
                
                // Calculate filtered totals
                var filteredClaims = 0;
                var filteredPremium = 0;
                
                filteredData.each(function(row) {
                    // Extract numeric values (remove commas)
                    var claims = parseFloat(row[7].toString().replace(/,/g, '')) || 0; // Column index 7 for total claims
                    var premium = parseFloat(row[9].toString().replace(/,/g, '')) || 0; // Column index 9 for premium
                    
                    filteredClaims += claims;
                    filteredPremium += premium;
                });
                
                // Update totals display ONLY
                $('#totalClaims').text(formatNumber(filteredClaims));
                $('#totalPremium').text(formatNumber(filteredPremium));
                
                // Check if filtered
                var dealerFilter = document.getElementById('liveDealerFilter');
                var isDealerFiltered = dealerFilter && dealerFilter.value;
                var isSearchFiltered = api.search() !== '';
                
                // Update footer labels correctly - FIXED: Select the correct cells
                if (isDealerFiltered || isSearchFiltered) {
                    // Update the label cells (cells 0 and 2 in the footer row)
                    $('tfoot tr td:eq(0)').html('<strong>Filtered Claims:</strong>');
                    $('tfoot tr td:eq(2)').html('<strong>Filtered Premium:</strong>');
                } else {
                    // Reset to original labels
                    $('tfoot tr td:eq(0)').html('<strong>Total Claims:</strong>');
                    $('tfoot tr td:eq(2)').html('<strong>Total Premium:</strong>');
                }
            },
            "initComplete": function() {
                // Customize the display
                this.api().buttons().container().appendTo('#dealerReportTable_wrapper .col-sm-6:eq(0)');
                
                // Add custom label for entries dropdown
                $('.dataTables_length').prepend('<span class="me-2" style="line-height: 34px;">Show:</span>');
                
                // Initialize FixedHeader for better UX
                new $.fn.dataTable.FixedHeader(this.api(), {
                    header: true,
                    footer: true
                });
                
                console.log('DataTable initialized successfully');
            }
        });
        
    } catch (error) {
        console.error('Error initializing DataTable:', error);
    }
}

// Helper function to format numbers
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>
@endsection
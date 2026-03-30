@extends('AutosecMaster.master')
@section('content')
<?php
use Illuminate\Support\Facades\Session; 
$userRole = Session::get('user')['role'] ?? 'user';
?>

<div class="content-body">
    <div class="container-fluid">
    
        <div id="preloader" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
        ">
            <div style="
                text-align: center;
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                max-width: 450px;
                width: 90%;
                border: 1px solid #e0e0e0;
            ">
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
                        <h4 class="card-title">Dealer Summary Report</h4>
                    </div>
                    
                    <div class="card-body">
                        <!-- Success Message -->
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        <!-- Info Message -->
                        @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fa fa-info-circle me-2"></i>
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        @if(isset($info) && !session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fa fa-info-circle me-2"></i>
                            {{ $info }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        <!-- Error Messages -->
                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        @if(isset($error) && !session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            {{ $error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        <!-- Filter Form -->
                        <form class="form-horizontal" role="form" method="GET" 
                              action="{{ route('dealer.summary.report') }}" 
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
                                               value="{{ old('fromDate', $fromDate ?? '') }}" 
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
                                               value="{{ old('toDate', $toDate ?? '') }}" 
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
                        @if($showReport ?? false)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="dealerSummaryTable">
                                        <thead>
                                            <tr class="bg-primary text-white">
                                                <th>Sr#</th>
                                                <th>Dealer Code</th>
                                                <th>Policies Count</th>
                                                <th>Total Insured Value</th>
                                                <th>Total Premium</th>
                                                <th>Total Claims</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($summaryData as $index => $dealer)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $dealer['dealer_code'] ?? 'Unknown' }}</strong></td>
                                                <td class="text-center">{{ number_format($dealer['count']) }}</td>
                                                <td class="text-right">{{ number_format($dealer['iev_sum']) }}</td>
                                                <td class="text-right">{{ number_format($dealer['premium_sum']) }}</td>
                                                <td class="text-right">{{ number_format($dealer['totclaims_sum']) }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    <i class="fa fa-database me-2 fa-2x"></i>
                                                    <h5 class="mt-2">No Data Available</h5>
                                                    <p>No dealer data found for the selected period {{ date('d-M-Y', strtotime($fromDate)) }} to {{ date('d-M-Y', strtotime($toDate)) }}</p>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        @if(count($summaryData) > 0)
                                        <tfoot>
                                            <tr class="table-dark">
                                                <td colspan="2" class="text-end"><strong>Grand Totals:</strong></td>
                                                <td class="text-center"><strong>{{ number_format(array_sum(array_column($summaryData, 'count'))) }}</strong></td>
                                                <td class="text-right"><strong>{{ number_format(array_sum(array_column($summaryData, 'iev_sum'))) }}</strong></td>
                                                <td class="text-right"><strong>{{ number_format(array_sum(array_column($summaryData, 'premium_sum'))) }}</strong></td>
                                                <td class="text-right"><strong>{{ number_format(array_sum(array_column($summaryData, 'totclaims_sum'))) }}</strong></td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        @elseif(!($showReport ?? false) && !session('error') && !isset($error) && !session('info') && !isset($info))
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle me-2"></i> 
                                    Please select date range and click "Generate Report" to view dealer summary data.
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

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Bootstrap JS  -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
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

document.addEventListener('DOMContentLoaded', function() {
    
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
    });
    
    var fromDateInput = document.getElementById('from_date');
    var toDateInput = document.getElementById('to_date');
    
    if (fromDateInput && toDateInput) {
        fromDateInput.addEventListener('change', function() {
            if (this.value) {
                toDateInput.min = this.value;
            }
        });
        
        toDateInput.addEventListener('change', function() {
            var fromDate = fromDateInput.value;
            var toDate = this.value;
            
            if (fromDate && toDate && fromDate > toDate) {
                alert('To date cannot be earlier than From date');
                this.value = '';
            }
        });
    }
    
    // Set default dates
    var today = new Date();
    var lastMonth = new Date(today);
    lastMonth.setDate(today.getDate() - 30);
    
    var fromDateDefault = lastMonth.toISOString().split('T')[0];
    var toDateDefault = today.toISOString().split('T')[0];
    
    if (fromDateInput && !fromDateInput.value) {
        fromDateInput.value = fromDateDefault;
    }
    if (toDateInput && !toDateInput.value) {
        toDateInput.value = toDateDefault;
    }
});

// Initialize DataTable
$(document).ready(function() {
    if ($('#dealerSummaryTable').length && $('#dealerSummaryTable tbody tr:not(.no-data)').length > 0) {
        var table = $('#dealerSummaryTable').DataTable({
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "order": [[2, "desc"]], /
            "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "responsive": true,
            "language": {
                "search": "Search dealers:",
                "lengthMenu": "Show _MENU_ dealers per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ dealers",
                "infoEmpty": "No dealers available",
                "infoFiltered": "(filtered from _MAX_ total dealers)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel me-2"></i> Export to Excel',
                    className: 'btn btn-success',
                    title: 'Dealer_Summary_Report_{{ $fromDate ?? date("Y-m-d") }}_{{ $toDate ?? date("Y-m-d") }}',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'copy',
                    text: '<i class="fa fa-copy me-2"></i> Copy',
                    className: 'btn btn-info',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print me-2"></i> Print',
                    className: 'btn btn-warning',
                    title: 'Dealer Summary Report',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],
            "initComplete": function() {
                
                var buttons = new $.fn.dataTable.Buttons(table, {
                    buttons: ['excelHtml5', 'copy', 'print']
                }).container().appendTo($('#dealerSummaryTable_wrapper .col-md-6:eq(0)'));
            }
        });
        
        
        table.on('draw', function() {
            var api = this.api();
            var totalCount = 0;
            var totalIev = 0;
            var totalPremium = 0;
            var totalClaims = 0;
            
        
            api.rows({ search: 'applied' }).every(function() {
                var data = this.data();
                var $row = $(this.node());
                
                var count = parseInt($row.find('td:eq(2)').text().replace(/,/g, '')) || 0;
                var iev = parseFloat($row.find('td:eq(3)').text().replace(/,/g, '')) || 0;
                var premium = parseFloat($row.find('td:eq(4)').text().replace(/,/g, '')) || 0;
                var claims = parseFloat($row.find('td:eq(5)').text().replace(/,/g, '')) || 0;
                
                totalCount += count;
                totalIev += iev;
                totalPremium += premium;
                totalClaims += claims;
            });
            
          
            if (api.rows({ search: 'applied' }).count() !== api.rows().count()) {
                $('#dealerSummaryTable tfoot').html('<tr class="table-dark">' +
                    '<td colspan="2" class="text-end"><strong>Filtered Totals:</strong></td>' +
                    '<td class="text-center"><strong>' + totalCount.toLocaleString() + '</strong></td>' +
                    '<td class="text-right"><strong>' + totalIev.toLocaleString() + '</strong></td>' +
                    '<td class="text-right"><strong>' + totalPremium.toLocaleString() + '</strong></td>' +
                    '<td class="text-right"><strong>' + totalClaims.toLocaleString() + '</strong></td>' +
                    '</tr>');
            }
        });
    }
});
</script>
@endsection
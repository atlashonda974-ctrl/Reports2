@extends('AutosecMaster.master')
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

        <!-- Toast Notification Container -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 11000;">
            <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fa fa-check-circle me-2"></i>
                        <span id="toastMessage"></span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Toast Notification Container - Error -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 11000;">
            <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fa fa-exclamation-circle me-2"></i>
                        <span id="errorToastMessage"></span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Email Confirmation Modal -->
        <div class="modal fade" id="emailConfirmModal" tabindex="-1" role="dialog" aria-labelledby="emailConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="emailConfirmModalLabel">
                            <i class="fa fa-envelope me-2"></i>Send Email Notification
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="background: transparent; border: none; color: white; font-size: 1.5rem; opacity: 0.8; padding: 0; margin: 0; line-height: 1;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-2"></i>
                            Email will be sent to the addresses below. Separate multiple emails with commas.
                        </div>
                        
                        <!-- Email Input Section -->
                        <div class="mb-4">
                            <label for="emailTo" class="form-label">To <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="emailTo" placeholder="Enter recipient email(s)" value="owais.zahid@ail.atlas.pk">
                            <small class="form-text text-muted">Example: user1@example.com, user2@example.com</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="emailCc" class="form-label">CC</label>
                            <input type="text" class="form-control" id="emailCc" placeholder="Enter CC email(s)" value="owais.zahid@ail.atlas.pk">
                            <small class="form-text text-muted">Separate multiple emails with commas</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="emailSubject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="emailSubject" placeholder="Enter email subject">
                        </div>
                        
                        <!-- Selected Documents Summary -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fa fa-list me-2"></i>Selected Documents (<span id="modalSelectedCount">0</span>)
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50"> Sr#</th>
                                                <th>Document No</th>
                                                <th>Insured Name</th>
                                                <th>Gross Premium</th>
                                                <th>Department</th>
                                            </tr>
                                        </thead>
                                        <tbody id="selectedDocsList">
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Email Preview -->
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="showPreview">
                            <label class="form-check-label" for="showPreview">Preview email content</label>
                        </div>
                        
                        <div id="emailPreview" class="border p-3 bg-light rounded" style="display: none; max-height: 300px; overflow-y: auto; font-size: 0.9rem;">
                            <!-- Preview will be shown here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="cancelEmailBtn">
                            <i class="fa fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary" id="confirmSendEmail">
                            <i class="fa fa-paper-plane me-1"></i>Send Email
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Unposted Documents Report</h4>
                    </div>
                    
                    <div class="card-body">
                        <!-- Messages -->
                        @if(session('success') || isset($success))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle me-2"></i>
                            {{ session('success') ?? $success }}
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
                              action="{{ route('unposted.report.generate') }}" 
                              autocomplete="off" 
                              id="reportForm">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-sm-2">
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
                                
                                <div class="col-sm-2">
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

                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Branch</label>
                                        <select name="branch" id="branch" class="form-control select2">
                                            <option value="All">All Branches</option>
                                            @foreach($branches as $branch)
                                                @php
                                                    $branchCode = $branch->fbracode ?? $branch['fbracode'];
                                                    $branchDesc = $branch->fbradsc ?? $branch['fbradsc'];
                                                @endphp
                                                <option value="{{ $branchCode }}" 
                                                        {{ ($selectedBranch ?? 'All') == $branchCode ? 'selected' : '' }}>
                                                    {{ $branchDesc }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select name="department" id="department" class="form-control select2">
                                            @foreach($departments as $code => $name)
                                                <option value="{{ $code }}" {{ ($selectedDept ?? 'All') == $code ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Takaful</label>
                                        <select name="takaful" id="takaful" class="form-control select2">
                                            @foreach($takafulOptions as $option)
                                                <option value="{{ $option }}" {{ ($selectedTakaful ?? 'All') == $option ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-sm-2">
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
                        <div id="reportContent">
                        @if($showReport ?? false)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-primary btn-sm" id="sendEmailBtn" style="display: none;">
                                                <i class="fa fa-envelope me-1"></i> Send Email (<span id="selectedCount">0</span>)
                                            </button>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="mb-0 me-2" style="white-space: nowrap;">Document Type:</label>
                                                <select id="docTypeFilter" class="form-control form-control-sm" style="width: 180px;">
                                                    <option value="All">All</option>
                                                    <option value="Covernote">Covernote</option>
                                                    <option value="Policy">Policy</option>
                                                    <option value="Endorsement">Endorsement</option>
                                                    <option value="Amendments">Amendments</option>
                                                    <option value="Certificate">Certificate</option>
                                                    <option value="Openpolicy">Openpolicy</option>
                                                </select>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="mb-0 me-2" style="white-space: nowrap;">Lock Status:</label>
                                                <select id="lockFilter" class="form-control form-control-sm" style="width: 120px;">
                                                    <option value="All">All</option>
                                                    <option value="Y">Yes</option>
                                                    <option value="N">No</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if(count($claims) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered" id="unpostedReportTable" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 50px; min-width: 50px; text-align: center;">SR#</th>
                                                        <th style="width: 100px; min-width: 100px; text-align: center; padding: 12px 8px; vertical-align: middle;">
                                                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;">
                                                                <div style="display: flex; align-items: center; gap: 5px;">
                                                                    <i class="fas fa-envelope" style="font-size: 0.8rem; color: #ffffff;"></i>
                                                                    <span style="font-size: 0.75rem; font-weight: 600; color: #ffffff;">SELECT</span>
                                                                </div>
                                                                <input type="checkbox" id="selectAll" class="form-check-input" style="margin: 0 auto; display: block; width: 20px; height: 20px; cursor: pointer;">
                                                            </div>
                                                        </th>
                                                        <th>LOCK</th>
                                                        <th>BROKER</th>
                                                        <th>DEV OFFICE</th>
                                                        <th>DOC TYPE</th>
                                                        <th>DOCUMENT No</th>
                                                        <th>DEPARTMENT</th>
                                                        <th>CLIENT NAME</th>
                                                        <th>ISSUE DATE</th>
                                                        <th>COMM DATE</th>
                                                        <th>EXPIRY DATE</th>
                                                        <th>SUM INSURED</th>
                                                        <th>GROSS PREMIUM</th>
                                                        <th>NET PREMIUM</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($claims as $index => $claim)
                                                    <tr data-lock="{{ ($claim['GDH_PROTECT_TAG'] ?? 'N') }}">
                                                        <td class="serial-number">{{ $index + 1 }}</td>
                                                        <td style="text-align: center; vertical-align: middle; padding: 12px 8px;">
                                                            <input type="checkbox" class="row-checkbox form-check-input" 
                                                                   data-doc-no="{{ $claim['GDH_BASEDOCUMENTNO'] ?? 'N/A' }}"
                                                                   data-party-name="{{ $claim['PPS_DESC'] ?? 'N/A' }}"
                                                                   data-dept="{{ $claim['DEPT_NAME'] ?? 'N/A' }}"
                                                                   data-gross-premium="{{ number_format($claim['GDH_GROSSPREMIUM'] ?? 0, 2) }}"
                                                                   style="margin: 0 auto; display: block; width: 20px; height: 20px; cursor: pointer;">
                                                        </td>
                                                        <td style="text-align: center;" class="lock-column">
                                                            @if(($claim['GDH_PROTECT_TAG'] ?? 'N') == 'Y')
                                                                <span class="badge bg-success">Yes</span>
                                                            @else
                                                                <span class="badge bg-secondary">No</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $claim['BROKER'] ?? 'N/A' }}</td>
                                                        <td>{{ $claim['PDO_DEVOFFDESC'] ?? 'N/A' }}</td>
                                                        <td>
                                                            @php
                                                                $docType = $claim['PDT_DOCTYPE'] ?? '';
                                                                $docTypeMap = [
                                                                    'T' => 'Covernote',
                                                                    'P' => 'Policy',
                                                                    'E' => 'Endorsement',
                                                                    'A' => 'Amendments',
                                                                    'C' => 'Certificate',
                                                                    'O' => 'Openpolicy'
                                                                ];
                                                                $displayDocType = $docTypeMap[$docType] ?? $docType;
                                                            @endphp
                                                            {{ $displayDocType }}
                                                        </td>
                                                        <td><strong>{{ $claim['GDH_DOC_REFERENCE_NO'] ?? 'N/A' }}</strong></td>
                                                        <td class="dept-code">{{ $claim['DEPT_NAME'] ?? ($claim['PDP_DEPT_CODE'] ?? 'N/A') }}</td>
                                                        <td>{{ $claim['PPS_DESC'] ?? 'N/A' }}</td>
                                                        <td>{{ $claim['GDH_ISSUEDATE'] ?? 'N/A' }}</td>
                                                        <td>{{ $claim['GDH_COMMDATE'] ?? 'N/A' }}</td>
                                                        <td>{{ $claim['GDH_EXPIRYDATE'] ?? 'N/A' }}</td>
                                                        <td class="text-right">{{ number_format($claim['GDH_TOTALSI'] ?? 0, 2) }}</td>
                                                        <td class="text-right">{{ number_format($claim['GDH_GROSSPREMIUM'] ?? 0, 2) }}</td>
                                                        <td class="text-right">{{ number_format($claim['GDH_NETPREMIUM'] ?? 0, 2) }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                               <tfoot>
    <tr class="font-weight-bold bg-light">
        <td style="background-color: #343a40; color: white;">Totals:</td>
        <td colspan="11" style="background-color: #343a40; color: white;"></td>
        <td style="background-color: #343a40; color: white;">{{ isset($totals['total_si']) ? number_format($totals['total_si'], 0) : '0' }}</td>
        <td style="background-color: #343a40; color: white;">{{ isset($totals['gross_premium']) ? number_format($totals['gross_premium'], 0) : '0' }}</td>
        <td style="background-color: #343a40; color: white;">{{ isset($totals['net_premium']) ? number_format($totals['net_premium'], 0) : '0' }}</td>
    </tr>
</tfoot>
</tfoot>
                                            </table>
                                        </div>
                                        @else
                                        <div class="text-center py-5">
                                            <i class="fa fa-database fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No Data Available</h5>
                                            <p class="text-muted">No unposted documents found for the selected criteria</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif(!($showReport ?? false) && !session('error') && !isset($error) && !session('info') && !isset($info))
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle me-2"></i> 
                                    Please select date range and click "Generate Report" to view unposted documents.
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
    
    #unpostedReportTable {
        border-collapse: collapse !important;
    }
    
    #unpostedReportTable th {
        background-color: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
        font-size: 0.85rem;
        text-align: center;
    }
    
    #unpostedReportTable td {
        font-size: 0.85rem;
    }

    #unpostedReportTable th:nth-child(2),
    #unpostedReportTable td:nth-child(2) {
        text-align: center !important;
        vertical-align: middle !important;
        padding: 12px 8px !important;
        width: 100px !important;
        min-width: 100px !important;
        max-width: 100px !important;
    }

    #unpostedReportTable td:nth-child(2) .form-check-input,
    #unpostedReportTable th:nth-child(2) .form-check-input {
        margin: 0 auto !important;
        display: block !important;
        width: 20px !important;
        height: 20px !important;
        cursor: pointer !important;
        position: relative;
        left: 0;
        right: 0;
    }
    
    #unpostedReportTable th:nth-child(1),
    #unpostedReportTable td:nth-child(1) {
        width: 50px !important;
        min-width: 50px !important;
        text-align: center !important;
    }

    #unpostedReportTable th:nth-child(3),
    #unpostedReportTable td:nth-child(3) {
        width: 80px !important;
        min-width: 80px !important;
        text-align: center !important;
    }
    
    .form-check-input {
        cursor: pointer;
        width: 18px;
        height: 18px;
    }
    
    .badge.bg-success {
        background-color: #28a745 !important;
        color: white;
        font-size: 0.75rem;
    }
    
    .badge.bg-secondary {
        background-color: #6c757d !important;
        color: white;
        font-size: 0.75rem;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000;
        font-size: 0.75rem;
    }
    
    .dataTables_wrapper .dataTables_length label {
        display: none !important;
    }
    
    .dataTables_length select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 6px 30px 6px 12px;
        background-color: white;
        margin-left: 10px;
    }
    
    .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 6px 12px;
        background-color: white;
    }
    
    .dt-buttons .btn {
        margin-left: 5px;
        padding: 6px 12px;
        font-size: 14px;
    }
    
    /* KEEP ORIGINAL FOOTER, HIDE DUPLICATE */
    #unpostedReportTable tfoot tr {
        background-color: #343a40 !important;
        color: white;
    }
    
    #unpostedReportTable tfoot td {
        border-top: 2px solid #dee2e6;
        font-size: 0.85rem;
    }
    
    /* Hide DataTable's built-in footer */
    .dataTables_wrapper tfoot {
        display: table-footer-group !important;
    }
    
    .table-responsive {
        min-height: 400px;
    }
    
    .dataTables_wrapper {
        margin-top: 10px;
    }
    
    .filter-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    #clearFilterBtn {
        border-left: 1px solid #dee2e6;
    }
    
    .text-right {
        text-align: right !important;
    }
    
    .text-end {
        text-align: right !important;
    }
    
    .dt-button-collection {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .toast {
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .toast-body {
        font-size: 0.95rem;
        padding: 12px;
    }

    .modal-header .close {
        background: transparent !important;
        border: none !important;
        color: white !important;
        font-size: 1.5rem !important;
        opacity: 0.8 !important;
        padding: 0 !important;
        margin: -1rem -1rem -1rem auto !important;
        line-height: 1 !important;
        cursor: pointer !important;
    }

    .modal-header .close:hover {
        opacity: 1 !important;
    }

    .modal-header .close span {
        font-weight: 300;
    }
    
    #selectedDocsList tr:hover {
        background-color: #f8f9fa !important;
    }
    
    #emailPreview {
        font-family: Arial, sans-serif;
        line-height: 1.6;
    }
    
    #emailPreview h4 {
        color: #dc3545;
        font-size: 1.1rem;
        margin-top: 0;
    }
    
    #emailPreview table {
        font-size: 0.85em;
    }
    
    #selectedDocsList td:nth-child(4) {
        text-align: right !important;
        font-weight: bold;
    }

    #lockFilter {
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    #lockFilter:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
</style>

<script>
var unpostedDataTable = null;
var originalTotals = {
    records: {{ $totalRecords ?? 0 }},
    gross_premium: {{ $totals['gross_premium'] ?? 0 }},
    total_si: {{ $totals['total_si'] ?? 0 }},
    net_premium: {{ $totals['net_premium'] ?? 0 }}
};

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

function stripHtmlTags(html) {
    var tmp = document.createElement("DIV");
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || "";
}

function applyFilters() {
    if (!unpostedDataTable) {
        // console.log('DataTable not initialized yet');
        return;
    }
    
    var lockValue = $('#lockFilter').val();
    var docTypeValue = $('#docTypeFilter').val();
    
    // console.log('=== FILTER DEBUG ===');
    // console.log('Lock Filter Value:', lockValue);
    // console.log('Doc Type Filter Value:', docTypeValue);
    
 
    $.fn.dataTable.ext.search = [];
    
    // Add filter function if any filter is active
    if (lockValue !== 'All' || docTypeValue !== 'All') {
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
          
            
         
            var row = unpostedDataTable.row(dataIndex).node();
            var rowLockValue = $(row).data('lock');
            
          
            var rowDocTypeValue = data[5] ? stripHtmlTags(data[5].toString()).trim() : '';
            
            // console.log('Row', dataIndex, '- Lock:', rowLockValue, 'DocType from data[5]:', rowDocTypeValue);
            
            var lockMatch = (lockValue === 'All') || (rowLockValue === lockValue);
            var docTypeMatch = (docTypeValue === 'All') || (rowDocTypeValue === docTypeValue);
            
            // console.log('Row', dataIndex, '- LockMatch:', lockMatch, 'DocTypeMatch:', docTypeMatch, 'Final:', lockMatch && docTypeMatch);
            
            return lockMatch && docTypeMatch;
        });
    }
    

    unpostedDataTable.draw();
    // console.log('=== FILTER APPLIED ===');
    // console.log('Filtered rows:', unpostedDataTable.rows({ search: 'applied' }).count());
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        $('.alert-success, .alert-info').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 4000);
    
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
    
    var form = document.getElementById('reportForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var fromDate = document.getElementById('from_date').value;
            var toDate = document.getElementById('to_date').value;
            if (!fromDate || !toDate) {
                alert('Please select both from and to dates');
                return false;
            }
            if (fromDate > toDate) {
                alert('To date cannot be earlier than From date');
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
            var formData = $(form).serialize();
            $.ajax({
                url: form.action,
                method: 'POST',
                data: formData,
                success: function(response) {
                    var tempDiv = document.createElement('div');
                    tempDiv.innerHTML = response;
                    var newReportContent = $(tempDiv).find('#reportContent').html();
                    if (newReportContent) {
                        $('#reportContent').html(newReportContent);
                        setTimeout(function() {
                            initializeDataTable();
                            hidePreloader();
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = '<i class="fa fa-search me-2"></i> Generate Report';
                            }
                        }, 500);
                    } else {
                        hidePreloader();
                        alert('Error loading report. Please try again.');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fa fa-search me-2"></i> Generate Report';
                        }
                    }
                },
                error: function(xhr) {
                    hidePreloader();
                    var errorMsg = 'Failed to generate report. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    $('#errorToastMessage').text(errorMsg);
                    var errorToast = new bootstrap.Toast(document.getElementById('errorToast'), {
                        autohide: true,
                        delay: 5000
                    });
                    errorToast.show();
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-search me-2"></i> Generate Report';
                    }
                }
            });
            return false;
        });
    }
    
    // Filter change events
    $(document).on('change', '#lockFilter, #docTypeFilter', function() {
        applyFilters();
    });
    
    $(document).on('change', '#selectAll', function() {
        var isChecked = $(this).prop('checked');
        $('.row-checkbox:visible').prop('checked', isChecked);
        updateSelectedCount();
    });
    
    $(document).on('change', '.row-checkbox', function() {
        updateSelectedCount();
        var totalVisible = $('.row-checkbox:visible').length;
        var totalChecked = $('.row-checkbox:visible:checked').length;
        $('#selectAll').prop('checked', totalVisible > 0 && totalVisible === totalChecked);
    });
    
    $('#cancelEmailBtn').on('click', function() {
        $('#emailConfirmModal').modal('hide');
    });
    $('.close[data-dismiss="modal"]').on('click', function() {
        $('#emailConfirmModal').modal('hide');
    });
    $('#emailConfirmModal').on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            $('#emailConfirmModal').modal('hide');
        }
    });
});

function updateSelectedCount() {
    var count = $('.row-checkbox:checked').length;
    $('#selectedCount').text(count);
    if (count > 0) {
        $('#sendEmailBtn').fadeIn();
    } else {
        $('#sendEmailBtn').fadeOut();
    }
}

$(document).on('click', '#sendEmailBtn', function() {
    var selectedRows = [];
    $('.row-checkbox:checked').each(function() {
        selectedRows.push({
            doc_no: $(this).data('doc-no'),
            party_name: $(this).data('party-name'),
            dept: $(this).data('dept'),
            GDH_GROSSPREMIUM: $(this).data('gross-premium')
        });
    });
    if (selectedRows.length === 0) {
        alert('Please select at least one document to send email.');
        return;
    }
    $('#emailConfirmModal').data('selectedRows', selectedRows);
    $('#modalSelectedCount').text(selectedRows.length);
    var docsList = $('#selectedDocsList');
    docsList.empty();
    selectedRows.forEach(function(doc, index) {
        docsList.append(`
            <tr>
                <td>${index + 1}</td>
                <td><strong>${doc.doc_no}</strong></td>
                <td>${doc.party_name}</td>
                <td class="text-right">${doc.GDH_GROSSPREMIUM}</td>
                <td>${doc.dept}</td>
            </tr>
        `);
    });
    var subject = `Unposted Documents Alert - ${selectedRows.length} Document(s) Pending`;
    $('#emailSubject').val(subject);
    generateEmailPreview(selectedRows, subject);
    $('#emailConfirmModal').modal('show');
});

$('#showPreview').on('change', function() {
    if ($(this).is(':checked')) {
        var selectedRows = $('#emailConfirmModal').data('selectedRows') || [];
        var subject = $('#emailSubject').val();
        generateEmailPreview(selectedRows, subject);
        $('#emailPreview').slideDown();
    } else {
        $('#emailPreview').slideUp();
    }
});

$('#emailSubject').on('input', function() {
    if ($('#showPreview').is(':checked')) {
        var selectedRows = $('#emailConfirmModal').data('selectedRows') || [];
        generateEmailPreview(selectedRows, $(this).val());
    }
});

function generateEmailPreview(selectedRows, subject) {
    var preview = $('#emailPreview');
    if (selectedRows.length === 0) {
        preview.html('<p class="text-muted">No documents selected</p>');
        return;
    }
    var portalLink = 'http://192.168.170.24/Reports2/login';
    var userName = 'System Administrator';
    var html = `<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <h4 style="color: #dc3545; margin-top: 0;">${subject}</h4>
            <hr style="margin: 10px 0;">
            <p><strong>Dear Team,</strong></p>
            <p>The following documents are currently unposted in the system. Kindly post them as soon as possible.</p>
            <table style="width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 0.9em;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="padding: 8px; border: 1px solid #ddd;"> SrNo#</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Document No</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Insured Name</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Gross Premium</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Department</th>
                    </tr>
                </thead>
                <tbody>`;
    selectedRows.forEach(function(doc, index) {
        html += `<tr>
                <td style="padding: 6px; border: 1px solid #ddd;">${index + 1}</td>
                <td style="padding: 6px; border: 1px solid #ddd;"><strong>${doc.doc_no}</strong></td>
                <td style="padding: 6px; border: 1px solid #ddd;">${doc.party_name}</td>
                <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">${doc.GDH_GROSSPREMIUM}</td>
                <td style="padding: 6px; border: 1px solid #ddd;">${doc.dept}</td>
            </tr>`;
    });
    html += `</tbody></table>
            <div style="background: #fff3cd; padding: 10px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 15px 0;">
                <p style="margin: 0; font-size: 0.9em;"><strong>Action Required:</strong> Please post these documents at your earliest convenience.</p>
            </div>
            <p style="font-size: 0.9em;"><strong>Portal Link:</strong> <a href="${portalLink}" style="color: #0062cc;">${portalLink}</a></p>
            <p style="margin-top: 20px; font-size: 0.9em;">Best regards,<br><strong>${userName}</strong></p>
        </div>`;
    preview.html(html);
}

$('#confirmSendEmail').on('click', function() {
    var selectedRows = $('#emailConfirmModal').data('selectedRows') || [];
    var emailTo = $('#emailTo').val().trim();
    var emailCc = $('#emailCc').val().trim();
    var emailSubject = $('#emailSubject').val().trim();
    if (!emailTo) {
        alert('Please enter at least one recipient email address.');
        return;
    }
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var toEmails = emailTo.split(',').map(email => email.trim()).filter(email => email);
    for (var i = 0; i < toEmails.length; i++) {
        if (!emailRegex.test(toEmails[i])) {
            alert('Invalid email address: ' + toEmails[i]);
            return;
        }
    }
    if (emailCc) {
        var ccEmails = emailCc.split(',').map(email => email.trim()).filter(email => email);
        for (var i = 0; i < ccEmails.length; i++) {
            if (!emailRegex.test(ccEmails[i])) {
                alert('Invalid CC email address: ' + ccEmails[i]);
                return;
            }
        }
    }
    $('#emailConfirmModal').modal('hide');
    sendEmailNotification(selectedRows, emailTo, emailCc, emailSubject);
});

function sendEmailNotification(selectedRows, emailTo, emailCc = '', emailSubject = '') {
    var btn = $('#confirmSendEmail');
    var originalHtml = btn.html();
    btn.prop('disabled', true);
    btn.html('<i class="fa fa-spinner fa-spin me-1"></i> Sending...');
    if (!emailSubject) {
        emailSubject = `Unposted Documents Alert - ${selectedRows.length} Document(s) Pending`;
    }
    $.ajax({
        url: '{{ route("unposted.report.send.email") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            documents: selectedRows,
            email_to: emailTo,
            email_cc: emailCc,
            email_subject: emailSubject
        },
        success: function(response) {
            if (response.success) {
                var recipientNames = emailTo.split(',').length > 3 ? 
                    emailTo.split(',').slice(0, 3).join(', ') + ' and others' : emailTo;
                $('#toastMessage').text(`Email sent successfully to ${recipientNames}`);
                var successToast = new bootstrap.Toast(document.getElementById('successToast'), {
                    autohide: true,
                    delay: 5000
                });
                successToast.show();
                $('.row-checkbox, #selectAll').prop('checked', false);
                updateSelectedCount();
                $('#emailSubject').val('');
                $('#showPreview').prop('checked', false);
                $('#emailPreview').hide();
            } else {
                $('#errorToastMessage').text(response.message || 'Failed to send email');
                var errorToast = new bootstrap.Toast(document.getElementById('errorToast'), {
                    autohide: true,
                    delay: 5000
                });
                errorToast.show();
            }
        },
        error: function(xhr) {
            var errorMsg = 'Failed to send email. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#errorToastMessage').text(errorMsg);
            var errorToast = new bootstrap.Toast(document.getElementById('errorToast'), {
                autohide: true,
                delay: 5000
            });
            errorToast.show();
        },
        complete: function() {
            btn.prop('disabled', false);
            btn.html(originalHtml);
        }
    });
}

@if(($showReport ?? false) && count($claims) > 0)
setTimeout(initializeDataTable, 500);
@endif

function initializeDataTable() {
    try {
        if (typeof jQuery === 'undefined' || typeof $.fn.DataTable === 'undefined') return;
        var table = $('#unpostedReportTable');
        if (table.length === 0) return;
        if ($.fn.dataTable.isDataTable(table)) {
            table.DataTable().destroy();
        }
        
        var currentDate = new Date();
        var formattedDate = currentDate.getFullYear() + '-' + 
                          String(currentDate.getMonth() + 1).padStart(2, '0') + '-' + 
                          String(currentDate.getDate()).padStart(2, '0') + ' ' +
                          String(currentDate.getHours()).padStart(2, '0') + ':' + 
                          String(currentDate.getMinutes()).padStart(2, '0') + ':' + 
                          String(currentDate.getSeconds()).padStart(2, '0');
        
        var fromDate = '{{ $fromDate }}';
        var toDate = '{{ $toDate }}';
        var fileName = 'Unposted_Documents_' + fromDate + '_to_' + toDate + '.xlsx';
        
        unpostedDataTable = table.DataTable({
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "order": [[9, "desc"]],
            "responsive": true,
            "dom": "<'row'<'col-sm-6'B><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api();
                var filteredGrossPremium = 0;
                var filteredSumInsured = 0;
                var filteredNetPremium = 0;
                
                api.rows({ search: 'applied' }).every(function() {
                    var rowData = this.data();
                    var sumInsured = parseFloat((rowData[11] || '0').replace(/,/g, '')) || 0;
                    var grossPremium = parseFloat((rowData[12] || '0').replace(/,/g, '')) || 0;
                    var netPremium = parseFloat((rowData[13] || '0').replace(/,/g, '')) || 0;
                    filteredSumInsured += sumInsured;
                    filteredGrossPremium += grossPremium;
                    filteredNetPremium += netPremium;
                });
                
                $(api.column(11).footer()).html('<strong>' + filteredSumInsured.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + '</strong>');
                $(api.column(12).footer()).html('<strong>' + filteredGrossPremium.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + '</strong>');
                $(api.column(13).footer()).html('<strong>' + filteredNetPremium.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + '</strong>');
            },
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
            "columnDefs": [
                {
                    "targets": [0],
                    "orderable": false,
                    "searchable": false,
                    "width": "50px",
                    "className": "text-center",
                    "visible": true
                },
                {
                    "targets": [1],
                    "orderable": false,
                    "searchable": false,
                    "width": "100px",
                    "className": "text-center"
                },
                {
                    "targets": [2],
                    "orderable": true,
                    "searchable": false,
                    "width": "80px",
                    "className": "text-center"
                },
                {
                    "targets": [11, 12, 13],
                    "className": "text-right",
                    "render": function(data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return data ? parseFloat(data.replace(/,/g, '')).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) : '0.00';
                        }
                        return data;
                    }
                }
            ],
            "buttons": [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel me-2"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    filename: fileName,
                    title: '',
                    messageTop: function() {
                        var branch = '{{ $selectedBranch ?? "All" }}';
                        var dept = '{{ $selectedDept ?? "All" }}';
                        var takaful = '{{ $selectedTakaful ?? "All" }}';
                        var criteria = [];
                        if (branch !== 'All') criteria.push('Branch: ' + branch);
                        if (dept !== 'All') criteria.push('Department: ' + dept);
                        if (takaful !== 'All') criteria.push('Takaful: ' + takaful);
                        var criteriaInfo = criteria.length > 0 ? '\nCriteria: ' + criteria.join(', ') : '';
                        return [
                            'UNPOSTED DOCUMENTS REPORT',
                            'Date Range: ' + fromDate + ' to ' + toDate,
                            criteriaInfo,
                            'Generated: ' + formattedDate,
                            '',
                            ''
                        ].join('\n');
                    },
                    exportOptions: {
                        columns: [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
                        format: {
                            body: function(data, row, column, node) {
                                var exportColumns = [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];
                                var actualColumnIndex = exportColumns[column];
                                if (actualColumnIndex === 0) {
                                    var api = unpostedDataTable;
                                    var filteredData = api.rows({ search: 'applied' }).data();
                                    var rowIndex = -1;
                                    var currentRowData = api.row(node).data();
                                    for (var i = 0; i < filteredData.length; i++) {
                                        if (filteredData[i] === currentRowData) {
                                            rowIndex = i;
                                            break;
                                        }
                                    }
                                    return rowIndex + 1;
                                }
                                var cleanData = stripHtmlTags(data.toString());
                                // Handle numeric columns
                                if (actualColumnIndex === 11 || actualColumnIndex === 12 || actualColumnIndex === 13) {
                                    return cleanData ? cleanData.replace(/,/g, '') : '0';
                                }
                                return cleanData;
                            }
                        }
                    },
                    customize: function(xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        var rows = $('row', sheet);
                        var lastRow = rows.last();
                        var lastRowNum = parseInt(lastRow.attr('r'));
                        var totalRow = '<row r="' + (lastRowNum + 1) + '">';
                       
                        for (var i = 0; i < 10; i++) {
                            totalRow += '<c r="' + String.fromCharCode(65 + i) + (lastRowNum + 1) + '" t="inlineStr" s="2"><is><t></t></is></c>';
                        }
                        
                        totalRow += '<c r="K' + (lastRowNum + 1) + '" t="inlineStr" s="2"><is><t>TOTALS:</t></is></c>';
                        totalRow += '<c r="L' + (lastRowNum + 1) + '" t="inlineStr" s="2"><is><t></t></is></c>';
                        // Column M - Sum Insured total
                        totalRow += '<c r="M' + (lastRowNum + 1) + '" t="n" s="2"><v>' + originalTotals.total_si + '</v></c>';
                        // Column N - Gross Premium total
                        totalRow += '<c r="N' + (lastRowNum + 1) + '" t="n" s="2"><v>' + originalTotals.gross_premium + '</v></c>';
                        // Column O - Net Premium total
                        totalRow += '<c r="O' + (lastRowNum + 1) + '" t="n" s="2"><v>' + originalTotals.net_premium + '</v></c>';
                        totalRow += '</row>';
                        $(lastRow).after(totalRow);
                    }
                },
                {
                    extend: 'copy',
                    text: '<i class="fa fa-copy me-2"></i> Copy',
                    className: 'btn btn-info btn-sm',
                    title: function() {
                        var branch = '{{ $selectedBranch ?? "All" }}';
                        var dept = '{{ $selectedDept ?? "All" }}';
                        var takaful = '{{ $selectedTakaful ?? "All" }}';
                        var criteria = [];
                        if (branch !== 'All') criteria.push('Branch: ' + branch);
                        if (dept !== 'All') criteria.push('Department: ' + dept);
                        if (takaful !== 'All') criteria.push('Takaful: ' + takaful);
                        var criteriaInfo = criteria.length > 0 ? '\nCriteria: ' + criteria.join(', ') : '';
                        return 'UNPOSTED DOCUMENTS REPORT\nDate Range: ' + fromDate + ' to ' + toDate + criteriaInfo + '\nGenerated: ' + formattedDate + '\n\n';
                    },
                    exportOptions: {
                        columns: [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
                    },
                    messageBottom: '\n\nTotal Records: ' + originalTotals.records + '\nTotal Gross Premium: ' + originalTotals.gross_premium.toFixed(2) + '\nTotal Sum Insured: ' + originalTotals.total_si.toFixed(2) + '\nTotal Net Premium: ' + originalTotals.net_premium.toFixed(2)
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print me-2"></i> Print',
                    className: 'btn btn-warning btn-sm',
                    title: '',
                    messageTop: function() {
                        var branch = '{{ $selectedBranch ?? "All" }}';
                        var dept = '{{ $selectedDept ?? "All" }}';
                        var takaful = '{{ $selectedTakaful ?? "All" }}';
                        var criteria = [];
                        if (branch !== 'All') criteria.push('Branch: ' + branch);
                        if (dept !== 'All') criteria.push('Department: ' + dept);
                        if (takaful !== 'All') criteria.push('Takaful: ' + takaful);
                        var criteriaInfo = criteria.length > 0 ? '<p><strong>Criteria:</strong> ' + criteria.join(', ') + '</p>' : '';
                        return '<h3>UNPOSTED DOCUMENTS REPORT</h3>' +
                               '<p><strong>Date Range:</strong> ' + fromDate + ' to ' + toDate + '</p>' +
                               criteriaInfo +
                               '<p><strong>Generated:</strong> ' + formattedDate + '</p><hr>';
                    },
                    messageBottom: function() {
                        return '<hr><div style="text-align: center; margin-top: 20px; padding: 10px; background-color: #f8f9fa; border: 1px solid #dee2e6;">' +
                               '<strong>Total Records:</strong> ' + originalTotals.records + ' | ' +
                               '<strong>Gross Premium:</strong> ' + originalTotals.gross_premium.toFixed(2) + ' | ' +
                               '<strong>Sum Insured:</strong> ' + originalTotals.total_si.toFixed(2) + ' | ' +
                               '<strong>Net Premium:</strong> ' + originalTotals.net_premium.toFixed(2) +
                               '</div>';
                    },
                    exportOptions: {
                        columns: [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
                    },
                    customize: function(win) {
                        $(win.document.body).find('table').addClass('table-bordered');
                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table').css('font-size', '10pt');
                    }
                }
            ],
            "drawCallback": function(settings) {
                var api = this.api();
                var pageInfo = api.page.info();
                var start = pageInfo.start;
                api.rows({ page: 'current' }).every(function(rowIdx, tableLoop, rowLoop) {
                    var row = this.node();
                    $(row).find('.serial-number').text(start + rowLoop + 1);
                });
            },
            "initComplete": function() {
                this.api().buttons().container().appendTo('#unpostedReportTable_wrapper .col-sm-6:eq(0)');
                $('.dataTables_length').prepend('<span class="me-2" style="line-height: 34px;">Show:</span>');
            }
        });
    } catch (error) {
        console.error('DataTable initialization error:', error);
    }
}
</script>
@endsection
@endsection
@extends('AutosecMaster.master')
@section('content')
    <?php
    use Illuminate\Support\Facades\Session;
    $userRole = Session::get('user')['role'] ?? 'user';
    ?>

    <div class="content-body">
        <div class="container-fluid">
            <!-- Pre-loader -->
            <div id="preloader"
                style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.95); z-index: 9999; justify-content: center; align-items: center;">
                <div
                    style="text-align: center; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); max-width: 450px; width: 90%; border: 1px solid #e0e0e0;">
                    <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <div style="margin-top: 25px;">
                        <h5 style="color: #333; font-weight: 600; font-size: 1.25rem;">Generating Report...</h5>
                        <p style="color: #666; margin-bottom: 20px;">Fetching data... This may take a moment...</p>
                        <div class="progress mt-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: 100%"></div>
                        </div>
                        <p class="text-muted mt-2" id="dateRangeInfo" style="font-size: 0.9rem;"></p>
                    </div>
                </div>
            </div>

            <!-- Success Toast -->
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 11000;">
                <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert"
                    aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body"><i class="fa fa-check-circle me-2"></i><span id="toastMessage"></span></div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            </div>

            <!-- Error Toast -->
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 11000;">
                <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert"
                    aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body"><i class="fa fa-exclamation-circle me-2"></i><span
                                id="errorToastMessage"></span></div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            </div>

            <!-- Email Modal -->
            <div class="modal fade" id="emailConfirmModal" tabindex="-1" role="dialog"
                aria-labelledby="emailConfirmModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-light text-dark">
                            <h5 class="modal-title" id="emailConfirmModalLabel">
                                <i class="fa fa-envelope me-2"></i>Send Email Notification
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                style="background:transparent;border:none;color:white;font-size:1.5rem;opacity:0.8;padding:0;margin:0;line-height:1;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle me-2"></i>
                                Email will be sent to the addresses below. Separate multiple emails with commas.
                            </div>
                            <div class="mb-3">
                                <label for="emailTo" class="form-label">To <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="emailTo" placeholder="Enter recipient email(s)">
                                <small class="form-text text-muted">Example: user1@ail.atlas.pk, user2@ail.atlas.pk</small>
                            </div>
                            <div class="mb-3">
                                <label for="emailCc" class="form-label">CC</label>
                                <input type="text" class="form-control" id="emailCc" placeholder="Enter CC email(s)">
                                <small class="form-text text-muted">Separate multiple emails with commas.</small>
                            </div>
                            <div class="mb-3">
                                <label for="emailSubject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="emailSubject" placeholder="Enter email subject">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message:</label>
                                <div id="emailBodyEditor" contenteditable="true" class="form-control"
                                    style="min-height:130px;max-height:260px;overflow-y:auto;font-family:Arial,sans-serif;font-size:0.9rem;line-height:1.7;color:#333;">
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="showPreview">
                                <label class="form-check-label" for="showPreview">Preview email content</label>
                            </div>
                            <div id="emailPreview" class="border p-3 bg-light rounded"
                                style="display:none;max-height:300px;overflow-y:auto;font-size:0.9rem;"></div>
                            <div class="card mt-3 mb-1">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fa fa-list me-2"></i>Selected Documents (<span
                                            id="modalSelectedCount">0</span>)</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height:200px;overflow-y:auto;">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="50">Sr#</th>
                                                    <th>Document No</th>
                                                    <th>Insured Name</th>
                                                    <th>Gross Premium</th>
                                                    <th>Department</th>
                                                </tr>
                                            </thead>
                                            <tbody id="selectedDocsList"></tbody>
                                        </table>
                                    </div>
                                </div>
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

            <!-- ================================================================
                 EMAIL LOGS MODAL
                 ================================================================ -->
            <div class="modal fade" id="emailLogsModal" tabindex="-1" role="dialog" aria-labelledby="emailLogsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                    <div class="modal-content" style="border:none;border-radius:10px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3);">

                      
                        <div class="modal-header" style="background:#ebeef5;color:#222;padding:16px 20px;border-bottom:1px solid #d8dce8;">
                            <h5 class="modal-title mb-0" id="emailLogsModalLabel"
                                style="font-weight:700;font-size:1rem;letter-spacing:0.3px;color:#0c0c0c;">
                                Email Notification Logs
                            </h5>
                            <button type="button" class="close" aria-label="Close"
                                style="background:#c8cfe8;border:none;color:#1a237e;font-size:1.2rem;border-radius:6px;width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;cursor:pointer;line-height:1;"
                                onclick="$('#emailLogsModal').modal('hide')">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <!-- Search & Filter Bar -->
                        <div style="background:#f0f2f5;padding:10px 16px;border-bottom:1px solid #dee2e6;">
                            <div class="row align-items-center g-2">

                                <!-- Search -->
                                <div class="col-sm-3">
                                    <div style="position:relative;">
                                        <i class="fa fa-search" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#aaa;font-size:0.78rem;pointer-events:none;"></i>
                                        <input type="text" id="logsSearchInput" class="form-control form-control-sm"
                                            placeholder="Search subject, recipient, sender…"
                                            style="padding-left:28px;border-radius:6px;font-size:0.82rem;">
                                    </div>
                                </div>

                                <!-- Sender -->
                                <div class="col-sm-2">
                                    <select id="logsSentByFilter" class="form-control form-control-sm" style="border-radius:6px;font-size:0.82rem;">
                                        <option value="">All Senders</option>
                                    </select>
                                </div>

                                <!-- Doc Count -->
                                <div class="col-sm-2">
                                    <select id="logsDocCountFilter" class="form-control form-control-sm" style="border-radius:6px;font-size:0.82rem;">
                                        <option value="">All Doc Counts</option>
                                        <option value="1">1 document</option>
                                        <option value="2-5">2–5 documents</option>
                                        <option value="6-20">6–20 documents</option>
                                        <option value="21+">21+ documents</option>
                                    </select>
                                </div>

                                <!-- Date From -->
                                <div class="col-sm-2">
                                    <div style="position:relative;">
                                        <span style="position:absolute;left:9px;top:50%;transform:translateY(-50%);font-size:0.7rem;color:#888;pointer-events:none;font-weight:600;letter-spacing:0.03em;">FROM</span>
                                        <input type="date" id="logsDateFrom" class="form-control form-control-sm"
                                            style="border-radius:6px;font-size:0.82rem;padding-left:46px;">
                                    </div>
                                </div>

                                <!-- Date To -->
                                <div class="col-sm-2">
                                    <div style="position:relative;">
                                        <span style="position:absolute;left:9px;top:50%;transform:translateY(-50%);font-size:0.7rem;color:#888;pointer-events:none;font-weight:600;letter-spacing:0.03em;">TO</span>
                                        <input type="date" id="logsDateTo" class="form-control form-control-sm"
                                            style="border-radius:6px;font-size:0.82rem;padding-left:28px;">
                                    </div>
                                </div>

                                <!-- Reset -->
                                <div class="col-sm-1 text-end">
                                    <button class="btn btn-outline-secondary btn-sm w-100" onclick="resetLogsFilters()"
                                        title="Reset all filters" style="font-size:0.78rem;border-radius:6px;">
                                        <i class="fa fa-undo"></i>
                                    </button>
                                </div>

                            </div>
                        </div>

                        <div class="modal-body" style="padding:0;max-height:60vh;overflow-y:auto;">
                            <div id="emailLogsLoading" class="text-center py-5">
                                <div class="spinner-border" role="status" style="width:2.5rem;height:2.5rem;color:#3949ab;">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="text-muted mt-3 mb-0" style="font-size:0.9rem;">Loading email logs...</p>
                            </div>
                            <div id="emailLogsContent" style="display:none;">
                                <table class="table table-hover mb-0" id="emailLogsTable"
                                    style="width:100%;font-size:0.83rem;border-collapse:collapse;">
                                    <thead style="position:sticky;top:0;z-index:5;">
                                        <tr style="background:#283593;color:white;">
                                            <th style="width:45px;text-align:center;padding:10px 8px;font-weight:600;font-size:0.78rem;letter-spacing:0.5px;">#</th>
                                            <th style="padding:10px 8px;font-weight:600;font-size:0.78rem;letter-spacing:0.5px;">SENT BY</th>
                                            <th style="padding:10px 8px;font-weight:600;font-size:0.78rem;letter-spacing:0.5px;">FROM</th>
                                            <th style="padding:10px 8px;font-weight:600;font-size:0.78rem;letter-spacing:0.5px;">TO</th>
                                            <th style="padding:10px 8px;font-weight:600;font-size:0.78rem;letter-spacing:0.5px;">CC</th>
                                            <th style="padding:10px 8px;font-weight:600;font-size:0.78rem;letter-spacing:0.5px;">SUBJECT</th>
                                            <th style="width:65px;text-align:center;padding:10px 8px;font-weight:600;font-size:0.78rem;letter-spacing:0.5px;">DOCS</th>
                                            <th style="width:165px;padding:10px 8px;font-weight:600;font-size:0.78rem;letter-spacing:0.5px;">DATE &amp; TIME </th>
                                            <th style="width:75px;text-align:center;padding:10px 8px;font-weight:600;font-size:0.78rem;letter-spacing:0.5px;">DETAIL</th>
                                        </tr>
                                    </thead>
                                    <tbody id="emailLogsTbody"></tbody>
                                </table>
                                <div id="logsNoResults" class="text-center py-4" style="display:none;">
                                    <i class="fa fa-filter fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-1" style="font-size:0.88rem;">No results match your search/filter.</p>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="resetLogsFilters()">Clear filters</button>
                                </div>
                            </div>
                            <div id="emailLogsEmpty" class="text-center py-5" style="display:none;">
                                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No email logs found</h6>
                                <p class="text-muted small mb-0">No emails have been sent yet.</p>
                            </div>
                            <div id="emailLogsError" class="alert alert-danger m-3" style="display:none;"></div>
                        </div>

                        <div class="modal-footer" style="background:#f8f9fa;border-top:1px solid #dee2e6;padding:10px 16px;">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <small class="text-muted" id="emailLogsTotalInfo" style="font-size:0.8rem;"></small>
                                <button type="button" class="btn btn-secondary btn-sm"
                                    onclick="$('#emailLogsModal').modal('hide')" style="border-radius:6px;">
                                    <i class="fa fa-times me-1"></i>Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Log Detail Modal -->
            <div class="modal fade" id="emailLogDetailModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index:12000;">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content" style="border:none;border-radius:10px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.35);">
                        
                        <div class="modal-header" style="background:#ebeef5;color:#222;padding:16px 20px;border-bottom:1px solid #d8dce8;">
                            <h6 class="modal-title mb-0"
                                style="font-weight:700;font-size:1rem;letter-spacing:0.3px;color:#0a0a0a;">
                                Email Log Detail
                            </h6>
                            <button type="button" class="close"
                                style="background:#c8cfe8;border:none;color:#1a237e;font-size:1.2rem;border-radius:6px;width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;cursor:pointer;line-height:1;"
                                onclick="$('#emailLogDetailModal').modal('hide')">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="emailLogDetailBody" style="padding:20px;background:#fafbfc;"></div>
                        <div class="modal-footer" style="background:#f0f2f5;border-top:1px solid #dee2e6;">
                            <button type="button" class="btn btn-secondary btn-sm"
                                onclick="$('#emailLogDetailModal').modal('hide')" style="border-radius:6px;">
                                <i class="fa fa-times me-1"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ================================================================ -->

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title">Unposted Documents Report</h4>
                        </div>
                        <div class="card-body">

                            @if (session('success') || isset($success))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fa fa-check-circle me-2"></i>{{ session('success') ?? $success }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            @if (session('info') || isset($info))
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <i class="fa fa-info-circle me-2"></i>{{ session('info') ?? $info }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            @if (session('error') || isset($error))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') ?? $error }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form class="form-horizontal" role="form" method="POST"
                                action="{{ route('unposted.report.generate') }}" autocomplete="off" id="reportForm">
                                @csrf
                                <div class="row align-items-end">
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label>From Date <span class="text-danger">*</span></label>
                                            <input name="fromDate"
                                                class="form-control @error('fromDate') is-invalid @enderror"
                                                id="from_date" type="date" value="{{ old('fromDate', $fromDate) }}"
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
                                                class="form-control @error('toDate') is-invalid @enderror" id="to_date"
                                                type="date" value="{{ old('toDate', $toDate) }}" required>
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
                                                @foreach ($branches as $branch)
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
                                                @foreach ($departments as $code => $name)
                                                    <option value="{{ $code }}"
                                                        {{ ($selectedDept ?? 'All') == $code ? 'selected' : '' }}>
                                                        {{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label>Takaful</label>
                                            <select name="takaful" id="takaful" class="form-control select2">
                                                @foreach ($takafulOptions as $option)
                                                    <option value="{{ $option }}"
                                                        {{ ($selectedTakaful ?? 'All') == $option ? 'selected' : '' }}>
                                                        {{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary btn-block" id="generateBtn">
                                                <i class="fa fa-search me-2"></i> Generate Report
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div id="reportContent">
                                @if ($showReport ?? false)
                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <button type="button" class="btn btn-primary btn-sm "
                                                            id="sendEmailBtn" style="display:none;">
                                                            <i class="fa fa-envelope me-1"></i> Send Email (<span
                                                                id="selectedCount">0</span>)
                                                        </button>
                                                        
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3">
                                                         <div class="d-flex align-items-center gap-2">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm "
                                                            id="emailLogsBtn" onclick="openEmailLogs()">
                                                            <i class="fa fa-history me-1"></i> Email Logs
                                                        </button>
                                                       </div>
                                                       <div style="width:1px; height:28px; background:#dee2e6; margin:0 8px;"></div>

                                                        <div class="d-flex align-items-center gap-2">
                                                            <label class="mb-0 me-2" style="white-space:nowrap;">Document Type:</label>
                                                            <select id="docTypeFilter" class="form-control form-control-sm" style="width:180px;">
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
                                                            <label class="mb-0 me-2" style="white-space:nowrap;">Lock Status:</label>
                                                            <select id="lockFilter" class="form-control form-control-sm" style="width:120px;">
                                                                <option value="All">All</option>
                                                                <option value="Y">Yes</option>
                                                                <option value="N">No</option>
                                                            </select>
                                                        </div>
                                                       
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    @if (count($claims) > 0)
                                                        <div class="table-responsive">
                                                            <table class="table table-striped table-bordered"
                                                                id="unpostedReportTable" style="width:100%">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width:50px;min-width:50px;text-align:center;">SR#</th>
                                                                        <th style="width:100px;min-width:100px;text-align:center;padding:12px 8px;vertical-align:middle;">
                                                                            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;">
                                                                                <div style="display:flex;align-items:center;gap:5px;">
                                                                                    <i class="fas fa-envelope" style="font-size:0.8rem;color:#ffffff;"></i>
                                                                                    <span style="font-size:0.75rem;font-weight:600;color:#ffffff;">SELECT</span>
                                                                                </div>
                                                                                <input type="checkbox" id="selectAll" class="form-check-input"
                                                                                    style="margin:0 auto;display:block;width:20px;height:20px;cursor:pointer;">
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
                                                                    @foreach ($claims as $index => $claim)
                                                                        <tr data-lock="{{ $claim['GDH_PROTECT_TAG'] ?? 'N' }}">
                                                                            <td class="serial-number">{{ $index + 1 }}</td>
                                                                            <td style="text-align:center;vertical-align:middle;padding:12px 8px;">
                                                                                <input type="checkbox"
                                                                                    class="row-checkbox form-check-input"
                                                                                    data-doc-no="{{ $claim['GDH_DOC_REFERENCE_NO'] ?? 'N/A' }}"
                                                                                    data-party-name="{{ $claim['PPS_DESC'] ?? 'N/A' }}"
                                                                                    data-dept="{{ $claim['DEPT_NAME'] ?? 'N/A' }}"
                                                                                    data-gross-premium="{{ number_format($claim['GDH_GROSSPREMIUM'] ?? 0, 2) }}"
                                                                                    style="margin:0 auto;display:block;width:20px;height:20px;cursor:pointer;">
                                                                            </td>
                                                                            <td style="text-align:center;" class="lock-column">
                                                                                @if (($claim['GDH_PROTECT_TAG'] ?? 'N') == 'Y')
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
                                                                                        'T' => 'Covernote', 'P' => 'Policy',
                                                                                        'E' => 'Endorsement', 'A' => 'Amendments',
                                                                                        'C' => 'Certificate', 'O' => 'Openpolicy',
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
                                                                    <tr>
                                                                        <td colspan="12"
                                                                            style="background-color:#343a40;color:white;font-weight:bold;text-align:right;">
                                                                            Totals:
                                                                        </td>
                                                                        <td id="footer-si"
                                                                            style="background-color:#343a40;color:white;font-weight:bold;text-align:right;">
                                                                            {{ isset($totals['total_si']) ? number_format($totals['total_si'], 2) : '0.00' }}
                                                                        </td>
                                                                        <td id="footer-gp"
                                                                            style="background-color:#343a40;color:white;font-weight:bold;text-align:right;">
                                                                            {{ isset($totals['gross_premium']) ? number_format($totals['gross_premium'], 2) : '0.00' }}
                                                                        </td>
                                                                        <td id="footer-np"
                                                                            style="background-color:#343a40;color:white;font-weight:bold;text-align:right;">
                                                                            {{ isset($totals['net_premium']) ? number_format($totals['net_premium'], 2) : '0.00' }}
                                                                        </td>
                                                                    </tr>
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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        #preloader{position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(255,255,255,0.95);z-index:9999;display:flex;justify-content:center;align-items:center;}
        #unpostedReportTable{border-collapse:collapse !important;}
        #unpostedReportTable th{background-color:#f8f9fa;font-weight:600;white-space:nowrap;font-size:0.85rem;text-align:center;}
        #unpostedReportTable td{font-size:0.85rem;}
        #unpostedReportTable th:nth-child(2),#unpostedReportTable td:nth-child(2){text-align:center !important;vertical-align:middle !important;padding:12px 8px !important;width:100px !important;min-width:100px !important;max-width:100px !important;}
        #unpostedReportTable td:nth-child(2) .form-check-input,#unpostedReportTable th:nth-child(2) .form-check-input{margin:0 auto !important;display:block !important;width:20px !important;height:20px !important;cursor:pointer !important;position:relative;left:0;right:0;}
        #unpostedReportTable th:nth-child(1),#unpostedReportTable td:nth-child(1){width:50px !important;min-width:50px !important;text-align:center !important;}
        #unpostedReportTable th:nth-child(3),#unpostedReportTable td:nth-child(3){width:80px !important;min-width:80px !important;text-align:center !important;}
        .form-check-input{cursor:pointer;width:18px;height:18px;}
        .badge.bg-success{background-color:#28a745 !important;color:white;font-size:0.75rem;}
        .badge.bg-secondary{background-color:#6c757d !important;color:white;font-size:0.75rem;}
        .badge.bg-warning{background-color:#ffc107 !important;color:#000;font-size:0.75rem;}
        .dataTables_wrapper .dataTables_length label{display:none !important;}
        .dataTables_length select{border:1px solid #ced4da;border-radius:4px;padding:6px 30px 6px 12px;background-color:white;margin-left:10px;}
        .dataTables_filter input{border:1px solid #ced4da;border-radius:4px;padding:6px 12px;background-color:white;}
        .dt-buttons .btn{margin-left:5px;padding:6px 12px;font-size:14px;}
        #unpostedReportTable tfoot tr{background-color:#343a40 !important;color:white;}
        #unpostedReportTable tfoot td{border-top:2px solid #dee2e6;font-size:0.85rem;}
        .dataTables_wrapper tfoot{display:table-footer-group !important;}
        .table-responsive{min-height:400px;}
        .dataTables_wrapper{margin-top:10px;}
        .text-right{text-align:right !important;}
        .text-end{text-align:right !important;}
        .dt-button-collection{max-height:400px;overflow-y:auto;}
        .toast{min-width:300px;box-shadow:0 4px 12px rgba(0,0,0,0.15);}
        .toast-body{font-size:0.95rem;padding:12px;}
        .modal-header .close:hover{opacity:1 !important;}
        #selectedDocsList tr:hover{background-color:#f8f9fa !important;}
        #emailPreview{font-family:Arial,sans-serif;line-height:1.6;}
        #emailPreview h4{color:#dc3545;font-size:1.1rem;margin-top:0;}
        #emailPreview table{font-size:0.85em;}
        #selectedDocsList td:nth-child(4){text-align:right !important;font-weight:bold;}
        #lockFilter{transition:border-color 0.15s ease-in-out,box-shadow 0.15s ease-in-out;}
        #lockFilter:focus{border-color:#80bdff;outline:0;box-shadow:0 0 0 0.2rem rgba(0,123,255,0.25);}
        #emailBodyEditor{cursor:text;}
        #emailBodyEditor:focus{background-color:#fefefe;box-shadow:0 0 0 0.2rem rgba(0,123,255,0.15);}
        #emailBodyEditor p{margin:0 0 8px 0;}
        /* Email Logs */
        #emailLogsTable thead th{color:white !important;font-size:0.78rem;white-space:nowrap;padding:10px 8px;}
        #emailLogsTable td{font-size:0.82rem;vertical-align:middle;padding:8px 10px;border-bottom:1px solid #f0f0f0;}
        #emailLogsTable tr:hover td{background-color:#eef1fb !important;}
        #emailLogsTable tr:nth-child(even) td{background-color:#f8f9ff;}
        .log-badge-docs{background:linear-gradient(135deg,#1a237e,#3949ab);color:white;padding:2px 9px;border-radius:10px;font-size:0.78rem;font-weight:700;letter-spacing:0.3px;}
        .btn-log-detail{padding:3px 10px;font-size:0.78rem;border-radius:5px;}
        #emailLogDetailBody .detail-section{background:white;border:1px solid #e8eaf6;border-radius:10px;padding:16px 18px;margin-bottom:14px;box-shadow:0 1px 4px rgba(26,35,126,0.06);}
        #emailLogDetailBody .detail-label{font-size:0.72rem;color:#5c6bc0;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;}
        #emailLogDetailBody .detail-value{font-size:0.9rem;color:#222;}
        #emailLogDetailBody .doc-pill{display:inline-block;background:#e8eaf6;border:1px solid #c5cae9;border-radius:12px;padding:3px 11px;font-size:0.78rem;margin:2px 3px;color:#1a237e;font-family:monospace;font-weight:600;}
        .log-truncate{max-width:170px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:middle;}
    </style>

    <script>
        var unpostedDataTable = null;

        var serverTotals = {
            records: {{ $totalRecords ?? 0 }},
            gross_premium: {{ $totals['gross_premium'] ?? 0 }},
            total_si: {{ $totals['total_si'] ?? 0 }},
            net_premium: {{ $totals['net_premium'] ?? 0 }}
        };

        function getDefaultEmailBody() {
            return '<p>Dear Team,</p><p>The following documents are currently unposted in the system. Kindly post them as soon as possible.</p>';
        }
        function stripHtmlTags(html) {
            var t = document.createElement('DIV'); t.innerHTML = html;
            return t.textContent || t.innerText || '';
        }
        function parseNum(str) {
            return parseFloat((str || '0').toString().replace(/,/g, '')) || 0;
        }
        function fmtNum(v) {
            return v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        function showPreloader(days) {
            document.getElementById('preloader').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            var info = document.getElementById('dateRangeInfo');
            if (info && days > 0) {
                info.innerHTML = days > 90
                    ? '<i class="fa fa-exclamation-triangle text-warning me-1"></i> Processing ' + days + ' days. This may take a while...'
                    : 'Processing ' + days + ' days of data...';
                info.style.color = days > 90 ? '#ff9800' : '#666';
            }
        }
        function hidePreloader() {
            document.getElementById('preloader').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        function applyFilters() {
            if (!unpostedDataTable) return;
            var lv = $('#lockFilter').val(), dv = $('#docTypeFilter').val();
            $.fn.dataTable.ext.search = [];
            if (lv !== 'All' || dv !== 'All') {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var row = unpostedDataTable.row(dataIndex).node();
                    var rowLock = $(row).data('lock');
                    var rowDoc  = data[5] ? stripHtmlTags(data[5].toString()).trim() : '';
                    return ((lv === 'All') || (rowLock === lv)) && ((dv === 'All') || (rowDoc === dv));
                });
            }
            unpostedDataTable.draw();
        }
        function generateEmailPreview(selectedRows, subject) {
            var preview = $('#emailPreview');
            if (!selectedRows || selectedRows.length === 0) { preview.html('<p class="text-muted">No documents selected</p>'); return; }
            var portal = 'http://192.168.170.24/Reports2/login', user = 'System Administrator';
            var bodyHtml = document.getElementById('emailBodyEditor').innerHTML || getDefaultEmailBody();
            var html = '<div style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">'
                + '<h4 style="color:#dc3545;margin-top:0;">' + subject + '</h4><hr style="margin:10px 0;">'
                + bodyHtml
                + '<table style="width:100%;border-collapse:collapse;margin:15px 0;font-size:0.9em;">'
                + '<thead><tr style="background:#f8f9fa;">'
                + '<th style="padding:8px;border:1px solid #ddd;">Sr#</th>'
                + '<th style="padding:8px;border:1px solid #ddd;">Document No</th>'
                + '<th style="padding:8px;border:1px solid #ddd;">Insured Name</th>'
                + '<th style="padding:8px;border:1px solid #ddd;">Gross Premium</th>'
                + '<th style="padding:8px;border:1px solid #ddd;">Department</th>'
                + '</tr></thead><tbody>';
            selectedRows.forEach(function(doc, i) {
                html += '<tr><td style="padding:6px;border:1px solid #ddd;">' + (i+1) + '</td>'
                    + '<td style="padding:6px;border:1px solid #ddd;"><strong>' + doc.doc_no + '</strong></td>'
                    + '<td style="padding:6px;border:1px solid #ddd;">' + doc.party_name + '</td>'
                    + '<td style="padding:6px;border:1px solid #ddd;text-align:right;">' + doc.GDH_GROSSPREMIUM + '</td>'
                    + '<td style="padding:6px;border:1px solid #ddd;">' + doc.dept + '</td></tr>';
            });
            html += '</tbody></table>'
                + '<div style="background:#fff3cd;padding:10px;border-radius:5px;border-left:4px solid #ffc107;margin:15px 0;">'
                + '<p style="margin:0;font-size:0.9em;"><strong>Action Required:</strong> Please post these documents at your earliest convenience.</p></div>'
                + '<p style="font-size:0.9em;"><strong>Portal Link:</strong> <a href="' + portal + '" style="color:#0062cc;">' + portal + '</a></p>'
                + '<p style="margin-top:20px;font-size:0.9em;">Best regards,<br><strong>' + user + '</strong></p></div>';
            preview.html(html);
        }

        /* ================================================================
           PKT TIME HELPERS  (UTC+5, 12-hour format)
           ================================================================
           DB stores UTC. We offset by +5 hours and display as 12-hr PKT.
           Example output: "26 Mar 2026, 02:08 PM"
        */
        function toPKT(rawStr) {
            if (!rawStr || rawStr === '-') return '-';
            var s = rawStr.trim();
            // Treat bare "YYYY-MM-DD HH:MM:SS" strings as UTC by appending Z
            if (!/[Zz+]/.test(s)) s = s.replace(' ', 'T') + 'Z';
            var d = new Date(s);
            if (isNaN(d.getTime())) return rawStr;           // fallback
            // Shift to PKT (UTC+5)
            var pkt  = new Date(d.getTime() + 5 * 3600000);
            var day  = String(pkt.getUTCDate()).padStart(2, '0');
            var mon  = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][pkt.getUTCMonth()];
            var yr   = pkt.getUTCFullYear();
            var hr   = pkt.getUTCHours();
            var min  = String(pkt.getUTCMinutes()).padStart(2, '0');
            var ampm = hr >= 12 ? 'PM' : 'AM';
            hr       = hr % 12 || 12;
            return day + ' ' + mon + ' ' + yr + ', ' + String(hr).padStart(2,'0') + ':' + min + ' ' + ampm;
        }

        // Returns YYYY-MM-DD in PKT for range-filter comparison
        function toPKTDateOnly(rawStr) {
            if (!rawStr || rawStr === '-') return '';
            var s = rawStr.trim();
            if (!/[Zz+]/.test(s)) s = s.replace(' ', 'T') + 'Z';
            var d = new Date(s);
            if (isNaN(d.getTime())) return '';
            var pkt = new Date(d.getTime() + 5 * 3600000);
            return pkt.getUTCFullYear()
                + '-' + String(pkt.getUTCMonth() + 1).padStart(2, '0')
                + '-' + String(pkt.getUTCDate()).padStart(2, '0');
        }

        /* ================================================================
           EMAIL LOGS
           ================================================================ */
        var _allLogs = [];

        function parseDocsArray(raw) {
            if (!raw) return [];
            if (Array.isArray(raw)) return raw;
            if (typeof raw === 'string') {
                try { var p = JSON.parse(raw); return Array.isArray(p) ? p : []; } catch(e) {}
            }
            return [];
        }

        function openEmailLogs() {
            $('#emailLogsModal').modal('show');
            $('#emailLogsLoading').show();
            $('#emailLogsContent,#emailLogsEmpty,#emailLogsError,#logsNoResults').hide();
            $('#emailLogsTotalInfo').text('');
            $('#logsSearchInput').val('');
            $('#logsSentByFilter,#logsDocCountFilter').val('');
            $('#logsDateFrom,#logsDateTo').val('');

            $.ajax({
                url: '{{ route("unposted.report.email.logs") }}',
                method: 'GET',
                success: function(res) {
                    $('#emailLogsLoading').hide();
                    if (!res.success) { $('#emailLogsError').text(res.message || 'Failed to load logs.').show(); return; }
                    if (!res.logs || res.logs.length === 0) { $('#emailLogsEmpty').show(); return; }
                    _allLogs = res.logs;
                    populateSenderFilter(_allLogs);
                    renderEmailLogs(_allLogs);
                    $('#emailLogsTotalInfo').text('Total: ' + _allLogs.length + ' log(s)');
                    $('#emailLogsContent').show();
                },
                error: function(xhr) {
                    $('#emailLogsLoading').hide();
                    var msg = 'Error loading logs. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    $('#emailLogsError').text(msg).show();
                }
            });
        }

        function populateSenderFilter(logs) {
            var senders = {};
            logs.forEach(function(l) { if (l.sent_by) senders[l.sent_by] = 1; });
            var sel = $('#logsSentByFilter').empty().append('<option value="">All Senders</option>');
            Object.keys(senders).sort().forEach(function(s) {
                sel.append('<option value="' + escHtml(s) + '">' + escHtml(s) + '</option>');
            });
        }

        function applyLogsFilters() {
            var search   = ($('#logsSearchInput').val()    || '').toLowerCase().trim();
            var sender   = ($('#logsSentByFilter').val()   || '').toLowerCase();
            var docRange =  $('#logsDocCountFilter').val() || '';
            var dateFrom =  $('#logsDateFrom').val()       || '';  // YYYY-MM-DD
            var dateTo   =  $('#logsDateTo').val()         || '';  // YYYY-MM-DD

            var filtered = _allLogs.filter(function(log) {
                if (search) {
                    var hay = [log.subject, log.recipient_to, log.recipient_cc,
                               log.sent_by, log.email_from, log.created_at].join(' ').toLowerCase();
                    if (hay.indexOf(search) === -1) return false;
                }
                if (sender && (log.sent_by || '').toLowerCase() !== sender) return false;
                if (docRange) {
                    var cnt = parseInt(log.document_count || 0);
                    if (docRange === '1'    && cnt !== 1)             return false;
                    if (docRange === '2-5'  && (cnt < 2 || cnt > 5))  return false;
                    if (docRange === '6-20' && (cnt < 6 || cnt > 20)) return false;
                    if (docRange === '21+'  && cnt < 21)              return false;
                }
                if (dateFrom || dateTo) {
                    var ld = toPKTDateOnly(log.created_at);
                    if (!ld) return false;
                    if (dateFrom && ld < dateFrom) return false;
                    if (dateTo   && ld > dateTo)   return false;
                }
                return true;
            });

            renderEmailLogs(filtered);
            var info = filtered.length + ' of ' + _allLogs.length + ' log(s)';
            if (filtered.length < _allLogs.length) info += ' (filtered)';
            $('#emailLogsTotalInfo').text(info);
            if (filtered.length === 0) {
                $('#emailLogsTable').hide(); $('#logsNoResults').show();
            } else {
                $('#emailLogsTable').show(); $('#logsNoResults').hide();
            }
        }

        function resetLogsFilters() {
            $('#logsSearchInput').val('');
            $('#logsSentByFilter,#logsDocCountFilter').val('');
            $('#logsDateFrom,#logsDateTo').val('');
            renderEmailLogs(_allLogs);
            $('#emailLogsTotalInfo').text('Total: ' + _allLogs.length + ' log(s)');
            $('#emailLogsTable').show(); $('#logsNoResults').hide();
        }

        $(document).on('input',  '#logsSearchInput',                       function() { applyLogsFilters(); });
        $(document).on('change', '#logsSentByFilter,#logsDocCountFilter',  function() { applyLogsFilters(); });
        $(document).on('change', '#logsDateFrom,#logsDateTo',              function() { applyLogsFilters(); });

        function renderEmailLogs(logs) {
            var tbody = $('#emailLogsTbody').empty();
            if (!logs || logs.length === 0) return;
            var map = {};
            logs.forEach(function(log) {
                map[log.id] = log;
                var docsArr     = parseDocsArray(log.documents);
                var docCount    = log.document_count || docsArr.length || 0;
                var pktDisplay  = toPKT(log.created_at);  // PKT 12-hr
                var row = '<tr>'
                    + '<td style="text-align:center;color:#888;font-size:0.78rem;">' + log.id + '</td>'
                    + '<td><strong style="color:#1a237e;">' + escHtml(log.sent_by || '-') + '</strong></td>'
                    + '<td><span class="log-truncate" title="' + escHtml(log.email_from   || '-') + '">' + escHtml(log.email_from   || '-') + '</span></td>'
                    + '<td><span class="log-truncate" title="' + escHtml(log.recipient_to || '-') + '">' + escHtml(log.recipient_to || '-') + '</span></td>'
                    + '<td><span class="log-truncate" title="' + escHtml(log.recipient_cc || '-') + '">' + escHtml(log.recipient_cc || '-') + '</span></td>'
                    + '<td><span class="log-truncate" title="' + escHtml(log.subject      || '-') + '">' + escHtml(log.subject      || '-') + '</span></td>'
                    + '<td style="text-align:center;"><span class="log-badge-docs">' + docCount + '</span></td>'
                    + '<td style="white-space:nowrap;color:#444;font-size:0.8rem;">' + escHtml(pktDisplay) + '</td>'
                    + '<td style="text-align:center;">'
                    +   '<button class="btn btn-sm btn-log-detail" data-log-id="' + log.id + '"'
                    +     ' style="background:#1a237e;color:white;border:none;border-radius:5px;padding:3px 10px;font-size:0.78rem;">'
                    +     '<i class="fa fa-eye me-1"></i>View'
                    +   '</button>'
                    + '</td>'
                    + '</tr>';
                tbody.append(row);
            });
            $('#emailLogsTbody').data('logsMap', map);
        }

        $(document).on('click', '.btn-log-detail', function() {
            var log = ($('#emailLogsTbody').data('logsMap') || {})[$(this).data('log-id')];
            if (log) showLogDetail(log);
        });

        function showLogDetail(log) {
            var docsArr    = parseDocsArray(log.documents);
            var docCount   = log.document_count || docsArr.length || 0;
            var pktDisplay = toPKT(log.created_at);

            var docPills = docsArr.length > 0
                ? docsArr.map(function(d){ return '<span class="doc-pill">' + escHtml(String(d)) + '</span>'; }).join('')
                : '<span class="text-muted small"><i class="fa fa-info-circle me-1"></i>No document list available</span>';

            var html =
                '<div class="detail-section">'
                + '<div class="row">'
                +   '<div class="col-sm-4 mb-3"><div class="detail-label"><i class="fa fa-user me-1" style="color:#3949ab;"></i>Sent By</div>'
                +     '<div class="detail-value"><strong style="color:#1a237e;">' + escHtml(log.sent_by || '-') + '</strong></div></div>'
                +   '<div class="col-sm-4 mb-3"><div class="detail-label"><i class="fa fa-at me-1" style="color:#3949ab;"></i>From</div>'
                +     '<div class="detail-value">' + escHtml(log.email_from || '-') + '</div></div>'
                +   '<div class="col-sm-4 mb-3"><div class="detail-label"><i class="fa fa-clock me-1" style="color:#3949ab;"></i>Date &amp; Time (PKT)</div>'
                +     '<div class="detail-value">' + escHtml(pktDisplay) + '</div></div>'
                + '</div>'
                + '<div class="row">'
                +   '<div class="col-sm-6 mb-3"><div class="detail-label"><i class="fa fa-envelope me-1" style="color:#3949ab;"></i>To</div>'
                +     '<div class="detail-value" style="word-break:break-all;">' + escHtml(log.recipient_to || '-') + '</div></div>'
                +   '<div class="col-sm-6 mb-3"><div class="detail-label"><i class="fa fa-copy me-1" style="color:#3949ab;"></i>CC</div>'
                +     '<div class="detail-value" style="word-break:break-all;">' + escHtml(log.recipient_cc || '-') + '</div></div>'
                + '</div>'
                + '<div><div class="detail-label"><i class="fa fa-tag me-1" style="color:#3949ab;"></i>Subject</div>'
                +   '<div class="detail-value"><strong>' + escHtml(log.subject || '-') + '</strong></div></div>'
                + '</div>'
                + '<div class="detail-section">'
                +   '<div class="detail-label mb-2"><i class="fa fa-align-left me-1" style="color:#3949ab;"></i>Message Body</div>'
                +   '<div style="background:white;border:1px solid #e0e4ef;border-radius:8px;padding:14px 16px;min-height:50px;font-size:0.88rem;color:#333;line-height:1.7;">'
                +     (log.email_body ? log.email_body : '<span class="text-muted">No message body</span>')
                +   '</div>'
                + '</div>'
                + '<div class="detail-section">'
                +   '<div class="detail-label mb-2"><i class="fa fa-file-alt me-1" style="color:#3949ab;"></i>Documents Included '
                +     '<span class="log-badge-docs ms-1">' + docCount + '</span></div>'
                +   '<div style="line-height:2;">' + docPills + '</div>'
                + '</div>';

            $('#emailLogDetailBody').html(html);
            $('#emailLogDetailModal').modal('show');
        }

        function escHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        /* ================================================================ */

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() { $('.alert-success,.alert-info').fadeOut('slow', function(){ $(this).remove(); }); }, 4000);
            var today = new Date(), lm = new Date(today);
            lm.setDate(today.getDate() - 30);
            var fi = document.getElementById('from_date'), ti = document.getElementById('to_date');
            if (fi && !fi.value) fi.value = lm.toISOString().split('T')[0];
            if (ti && !ti.value) ti.value = today.toISOString().split('T')[0];

            var form = document.getElementById('reportForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var fd = document.getElementById('from_date').value, td = document.getElementById('to_date').value;
                    if (!fd || !td) { alert('Please select both from and to dates'); return false; }
                    if (fd > td)    { alert('To date cannot be earlier than From date'); return false; }
                    var days = Math.ceil(Math.abs(new Date(td) - new Date(fd)) / 86400000);
                    showPreloader(days);
                    var btn = document.getElementById('generateBtn');
                    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Processing...'; }
                    $.ajax({
                        url: form.action, method: 'POST', data: $(form).serialize(),
                        success: function(resp) {
                            var tmp = document.createElement('div'); tmp.innerHTML = resp;
                            var nc = $(tmp).find('#reportContent').html();
                            if (nc) {
                                $('#reportContent').html(nc);
                                setTimeout(function() {
                                    initializeDataTable(); hidePreloader();
                                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-search me-2"></i> Generate Report'; }
                                }, 500);
                            } else {
                                hidePreloader(); alert('Error loading report. Please try again.');
                                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-search me-2"></i> Generate Report'; }
                            }
                        },
                        error: function(xhr) {
                            hidePreloader();
                            var msg = 'Failed to generate report. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            $('#errorToastMessage').text(msg);
                            new bootstrap.Toast(document.getElementById('errorToast'), { autohide:true, delay:5000 }).show();
                            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-search me-2"></i> Generate Report'; }
                        }
                    });
                    return false;
                });
            }
            $(document).on('change', '#lockFilter,#docTypeFilter', function() { applyFilters(); });
            $(document).on('change', '#selectAll', function() {
                $('.row-checkbox:visible').prop('checked', $(this).prop('checked')); updateSelectedCount();
            });
            $(document).on('change', '.row-checkbox', function() {
                updateSelectedCount();
                var tot = $('.row-checkbox:visible').length, chk = $('.row-checkbox:visible:checked').length;
                $('#selectAll').prop('checked', tot > 0 && tot === chk);
            });
            $('#cancelEmailBtn').on('click', function() { $('#emailConfirmModal').modal('hide'); });
            $('.close[data-dismiss="modal"]').on('click', function() { $('#emailConfirmModal').modal('hide'); });
            $('#emailConfirmModal').on('click', function(e) { if ($(e.target).hasClass('modal')) $('#emailConfirmModal').modal('hide'); });
            $(document).on('change', '#showPreview', function() {
                if ($(this).is(':checked')) {
                    generateEmailPreview($('#emailConfirmModal').data('selectedRows') || [], $('#emailSubject').val());
                    $('#emailPreview').slideDown();
                } else { $('#emailPreview').slideUp(); }
            });
            $(document).on('input', '#emailSubject', function() {
                if ($('#showPreview').is(':checked')) generateEmailPreview($('#emailConfirmModal').data('selectedRows') || [], $(this).val());
            });
            $(document).on('input', '#emailBodyEditor', function() {
                if ($('#showPreview').is(':checked')) generateEmailPreview($('#emailConfirmModal').data('selectedRows') || [], $('#emailSubject').val());
            });
        });

        function updateSelectedCount() {
            var c = $('.row-checkbox:checked').length;
            $('#selectedCount').text(c);
            c > 0 ? $('#sendEmailBtn').fadeIn() : $('#sendEmailBtn').fadeOut();
        }
        $(document).on('click', '#sendEmailBtn', function() {
            var rows = [];
            $('.row-checkbox:checked').each(function() {
                rows.push({ doc_no: $(this).data('doc-no'), party_name: $(this).data('party-name'),
                            dept: $(this).data('dept'), GDH_GROSSPREMIUM: $(this).data('gross-premium') });
            });
            if (!rows.length) { alert('Please select at least one document to send email.'); return; }
            $('#emailConfirmModal').data('selectedRows', rows);
            $('#modalSelectedCount').text(rows.length);
            $('#selectedDocsList').empty();
            rows.forEach(function(d, i) {
                $('#selectedDocsList').append('<tr><td>' + (i+1) + '</td><td><strong>' + d.doc_no + '</strong></td><td>'
                    + d.party_name + '</td><td class="text-right">' + d.GDH_GROSSPREMIUM + '</td><td>' + d.dept + '</td></tr>');
            });
            $('#emailSubject').val('Unposted Documents Alert - ' + rows.length + ' Document(s) Pending');
            document.getElementById('emailBodyEditor').innerHTML = getDefaultEmailBody();
            $('#showPreview').prop('checked', false); $('#emailPreview').hide();
            $('#emailConfirmModal').modal('show');
        });
        $('#confirmSendEmail').on('click', function() {
            var rows = $('#emailConfirmModal').data('selectedRows') || [];
            var to = $('#emailTo').val().trim(), cc = $('#emailCc').val().trim(), subj = $('#emailSubject').val().trim();
            if (!to) { alert('Please enter at least one recipient email address.'); return; }
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var toArr = to.split(',').map(function(e){ return e.trim(); }).filter(Boolean);
            for (var i = 0; i < toArr.length; i++) { if (!re.test(toArr[i])) { alert('Invalid email: ' + toArr[i]); return; } }
            if (cc) {
                var ccArr = cc.split(',').map(function(e){ return e.trim(); }).filter(Boolean);
                for (var j = 0; j < ccArr.length; j++) { if (!re.test(ccArr[j])) { alert('Invalid CC email: ' + ccArr[j]); return; } }
            }
            $('#emailConfirmModal').modal('hide');
            sendEmailNotification(rows, to, cc, subj);
        });
        function sendEmailNotification(rows, to, cc, subj) {
            var btn = $('#confirmSendEmail'), orig = btn.html();
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Sending...');
            if (!subj) subj = 'Unposted Documents Alert - ' + rows.length + ' Document(s) Pending';
            $.ajax({
                url: '{{ route('unposted.report.send.email') }}', method: 'POST',
                data: { _token: '{{ csrf_token() }}', documents: rows, email_to: to, email_cc: cc,
                        email_subject: subj, email_body: document.getElementById('emailBodyEditor').innerHTML },
                success: function(r) {
                    if (r.success) {
                        var parts = to.split(','), label = parts.length > 3 ? parts.slice(0,3).join(', ') + ' and others' : to;
                        $('#toastMessage').text('Email sent successfully to ' + label);
                        new bootstrap.Toast(document.getElementById('successToast'), { autohide:true, delay:5000 }).show();
                        $('.row-checkbox,#selectAll').prop('checked', false); updateSelectedCount();
                        $('#emailSubject').val(''); document.getElementById('emailBodyEditor').innerHTML = '';
                        $('#showPreview').prop('checked', false); $('#emailPreview').hide();
                    } else {
                        $('#errorToastMessage').text(r.message || 'Failed to send email');
                        new bootstrap.Toast(document.getElementById('errorToast'), { autohide:true, delay:5000 }).show();
                    }
                },
                error: function(xhr) {
                    var msg = 'Failed to send email. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    $('#errorToastMessage').text(msg);
                    new bootstrap.Toast(document.getElementById('errorToast'), { autohide:true, delay:5000 }).show();
                },
                complete: function() { btn.prop('disabled', false).html(orig); }
            });
        }

        @if (($showReport ?? false) && count($claims) > 0)
            setTimeout(initializeDataTable, 500);
        @endif

        function initializeDataTable() {
            try {
                if (typeof jQuery === 'undefined' || typeof $.fn.DataTable === 'undefined') return;
                var table = $('#unpostedReportTable');
                if (!table.length) return;
                if ($.fn.dataTable.isDataTable(table)) table.DataTable().destroy();

                var now = new Date();
                var ts = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0') + ' '
                    + String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0') + ':' + String(now.getSeconds()).padStart(2,'0');
                var fromDate = '{{ $fromDate }}', toDate = '{{ $toDate }}';
                var fileName = 'Unposted_Documents_' + fromDate + '_to_' + toDate + '.xlsx';

                unpostedDataTable = table.DataTable({
                    pageLength: 25,
                    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
                    order: [[9,'desc']],
                    responsive: true,
                    dom: "<'row'<'col-sm-6'B><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                    footerCallback: function(row, data, start, end, display) {
                        var api = this.api(), si = 0, gp = 0, np = 0;
                        api.rows({ search:'applied' }).every(function() {
                            var d = this.data(); si += parseNum(d[12]); gp += parseNum(d[13]); np += parseNum(d[14]);
                        });
                        $(api.column(12).footer()).html('<strong>' + fmtNum(si) + '</strong>');
                        $(api.column(13).footer()).html('<strong>' + fmtNum(gp) + '</strong>');
                        $(api.column(14).footer()).html('<strong>' + fmtNum(np) + '</strong>');
                    },
                    language: {
                        search:'', searchPlaceholder:'Search in all columns…',
                        lengthMenu:'Show _MENU_ entries', info:'Showing _START_ to _END_ of _TOTAL_ entries',
                        infoEmpty:'Showing 0 to 0 of 0 entries', infoFiltered:'(filtered from _MAX_ total entries)',
                        zeroRecords:'No matching records found',
                        paginate:{ first:'First', last:'Last', next:'Next', previous:'Previous' }
                    },
                    columnDefs: [
                        { targets:[0], orderable:false, searchable:false, width:'50px',  className:'text-center' },
                        { targets:[1], orderable:false, searchable:false, width:'100px', className:'text-center' },
                        { targets:[2], orderable:true,  searchable:false, width:'80px',  className:'text-center' },
                        { targets:[12,13,14], className:'text-right',
                          render: function(data,type) {
                              if (type==='display'||type==='filter') return data ? fmtNum(parseNum(data)) : '0.00';
                              return parseNum(data);
                          }
                        }
                    ],
                    buttons: [
                        {
                            extend:'excel', text:'<i class="fa fa-file-excel me-2"></i> Excel',
                            className:'btn btn-success btn-sm', filename:fileName, title:'',
                            messageTop: function() {
                                var b='{{ $selectedBranch ?? 'All' }}',d='{{ $selectedDept ?? 'All' }}',t='{{ $selectedTakaful ?? 'All' }}',c=[];
                                if(b!=='All')c.push('Branch: '+b); if(d!=='All')c.push('Department: '+d); if(t!=='All')c.push('Takaful: '+t);
                                return ['UNPOSTED DOCUMENTS REPORT','Date Range: '+fromDate+' to '+toDate,
                                    c.length?'Criteria: '+c.join(', '):'','Generated: '+ts,'',''].join('\n');
                            },
                            exportOptions:{ columns:[0,2,3,4,5,6,7,8,9,10,11,12,13,14],
                                format:{ body: function(data,row,column,node) {
                                    var ec=[0,2,3,4,5,6,7,8,9,10,11,12,13,14], ac=ec[column];
                                    if(ac===0){ var ad=unpostedDataTable.rows({search:'applied'}).data(),rd=unpostedDataTable.row(node).data(); for(var i=0;i<ad.length;i++){if(ad[i]===rd)return i+1;} return ''; }
                                    var cl=stripHtmlTags(data.toString()).trim();
                                    if(ac===12||ac===13||ac===14) return cl?parseFloat(cl.replace(/,/g,'')):0;
                                    return cl;
                                }}
                            },
                            customize: function(xlsx) {
                                var sh=xlsx.xl.worksheets['sheet1.xml'], st=xlsx.xl['styles.xml'];
                                var nf=$('numFmts',st);
                                if(!nf.length){$('styleSheet',st).prepend('<numFmts count="1"><numFmt numFmtId="200" formatCode="#,##0.00"/></numFmts>');nf=$('numFmts',st);}
                                else if(!$('numFmt[numFmtId="200"]',st).length){nf.append('<numFmt numFmtId="200" formatCode="#,##0.00"/>');nf.attr('count',parseInt(nf.attr('count')||0)+1);}
                                var cx=$('cellXfs',st),ec=parseInt(cx.attr('count')||0),CN=ec,TN=ec+1;
                                cx.append('<xf numFmtId="200" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment horizontal="right"/></xf>');
                                cx.append('<xf numFmtId="200" fontId="2" fillId="2" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="right"/></xf>');
                                cx.attr('count',ec+2);
                                ['L','M','N'].forEach(function(c){$('c[r^="'+c+'"]',sh).each(function(){var v=$(this).find('v').text();if(v!==''&&!isNaN(parseFloat(v))){$(this).attr('t','n').attr('s',CN);}});});
                                var ar=$('row',sh),lr=parseInt(ar.last().attr('r')),tr=lr+1;
                                var si=parseNum($('#footer-si').text()),gp=parseNum($('#footer-gp').text()),np=parseNum($('#footer-np').text());
                                var r='<row r="'+tr+'">';
                                ['A','B','C','D','E','F','G','H','I','J'].forEach(function(l){r+='<c r="'+l+tr+'" t="inlineStr" s="0"><is><t></t></is></c>';});
                                r+='<c r="K'+tr+'" t="inlineStr" s="2"><is><t>TOTALS:</t></is></c>';
                                r+='<c r="L'+tr+'" t="n" s="'+TN+'"><v>'+si+'</v></c>';
                                r+='<c r="M'+tr+'" t="n" s="'+TN+'"><v>'+gp+'</v></c>';
                                r+='<c r="N'+tr+'" t="n" s="'+TN+'"><v>'+np+'</v></c></row>';
                                ar.last().after(r);
                            }
                        },
                        {
                            extend:'copy', text:'<i class="fa fa-copy me-2"></i> Copy', className:'btn btn-info btn-sm',
                            title: function(){
                                var b='{{ $selectedBranch ?? 'All' }}',d='{{ $selectedDept ?? 'All' }}',t='{{ $selectedTakaful ?? 'All' }}',c=[];
                                if(b!=='All')c.push('Branch: '+b); if(d!=='All')c.push('Department: '+d); if(t!=='All')c.push('Takaful: '+t);
                                return 'UNPOSTED DOCUMENTS REPORT\nDate Range: '+fromDate+' to '+toDate+(c.length?'\nCriteria: '+c.join(', '):'')+'\nGenerated: '+ts+'\n\n';
                            },
                            exportOptions:{ columns:[0,2,3,4,5,6,7,8,9,10,11,12,13,14] },
                            messageBottom: function(){
                                var si=parseNum($('#footer-si').text()),gp=parseNum($('#footer-gp').text()),np=parseNum($('#footer-np').text());
                                return '\n\nTotal Records: '+serverTotals.records+'\nTotal Sum Insured: '+fmtNum(si)+'\nTotal Gross Premium: '+fmtNum(gp)+'\nTotal Net Premium: '+fmtNum(np);
                            }
                        },
                        {
                            extend:'print', text:'<i class="fa fa-print me-2"></i> Print', className:'btn btn-warning btn-sm', title:'',
                            messageTop: function(){
                                var b='{{ $selectedBranch ?? 'All' }}',d='{{ $selectedDept ?? 'All' }}',t='{{ $selectedTakaful ?? 'All' }}',c=[];
                                if(b!=='All')c.push('Branch: '+b); if(d!=='All')c.push('Department: '+d); if(t!=='All')c.push('Takaful: '+t);
                                return '<h3>UNPOSTED DOCUMENTS REPORT</h3><p><strong>Date Range:</strong> '+fromDate+' to '+toDate+'</p>'+(c.length?'<p><strong>Criteria:</strong> '+c.join(', ')+'</p>':'')+' <p><strong>Generated:</strong> '+ts+'</p><hr>';
                            },
                            messageBottom: function(){
                                var si=parseNum($('#footer-si').text()),gp=parseNum($('#footer-gp').text()),np=parseNum($('#footer-np').text());
                                return '<hr><div style="text-align:center;margin-top:20px;padding:10px;border:1px solid #dee2e6;"><strong>Total Records:</strong> '+serverTotals.records+' | <strong>Sum Insured:</strong> '+fmtNum(si)+' | <strong>Gross Premium:</strong> '+fmtNum(gp)+' | <strong>Net Premium:</strong> '+fmtNum(np)+'</div>';
                            },
                            exportOptions:{ columns:[0,2,3,4,5,6,7,8,9,10,11,12,13,14] },
                            customize: function(win){ $(win.document.body).find('table').addClass('table-bordered'); $(win.document.body).find('h1').css('text-align','center'); $(win.document.body).find('table').css('font-size','10pt'); }
                        }
                    ],
                    drawCallback: function(settings) {
                        var api=this.api(), start=api.page.info().start;
                        api.rows({page:'current'}).every(function(ri,tl,rl){ $(this.node()).find('.serial-number').text(start+rl+1); });
                    },
                    initComplete: function() {
                        this.api().buttons().container().appendTo('#unpostedReportTable_wrapper .col-sm-6:eq(0)');
                        $('.dataTables_length').prepend('<span class="me-2" style="line-height:34px;">Show:</span>');
                    }
                });
            } catch(e) { console.error('DataTable init error:', e); }
        }
    </script>
@endsection
@endsection
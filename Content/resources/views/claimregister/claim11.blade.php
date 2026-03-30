<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $title = 'Pending Settlement Approval';
    @endphp
    @include('layouts.master_titles')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.3.2/css/fixedHeader.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-datatable-styles />

    <style>
        .time-filter-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .time-tab.active {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white !important;
        }

        #docTable tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table td {
            vertical-align: middle;
        }

        .remarks-history {
            max-height: 100px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 5px;
            background-color: #f8f9fa;
        }

        .remark-item {
            padding: 3px;
            border-bottom: 1px solid #e9ecef;
        }

        .remark-item:last-child {
            border-bottom: none;
        }

        .modal-lg {
            max-width: 1200px;
        }

        .tab-pane {
            padding: 15px 0;
        }
        
        /* Side by side small buttons with fixed width */
        .action-buttons-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .action-buttons-row .btn {
            width: 200px !important; /* Fixed width for all buttons */
            min-width: 120px !important;
            max-width: 200px !important;
            flex: 0 0 200px !important; /* Don't grow or shrink, fixed at 200px */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        @media (max-width: 1200px) {
            .action-buttons-row .btn {
                width: 180px !important;
                flex: 0 0 180px !important;
            }
        }
        
        @media (max-width: 992px) {
            .action-buttons-row .btn {
                width: 150px !important;
                flex: 0 0 150px !important;
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 768px) {
            .action-buttons-row {
                flex-direction: column;
                gap: 5px;
            }
            
            .action-buttons-row .btn {
                width: 100% !important;
                min-width: auto !important;
                max-width: 100% !important;
                flex: 1 !important;
            }
        }
        
        /* File selection modal styles */
        .file-list-item {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .file-list-item:hover {
            background-color: #f8f9fa;
        }
        
        .file-icon {
            color: #dc3545;
        }
        
        .file-size {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <x-report-header title="Pending Settlement Approval" />

        <form method="GET" action="{{ url('/stlClmOs') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3 d-flex align-items-center">
                    <label for="start_date" class="form-label me-2" style="white-space: nowrap; width: 100px;">From
                        Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                        value="{{ request('start_date', $start_date) }}">
                </div>
                <div class="col-md-3 d-flex align-items-center">
                    <label for="end_date" class="form-label me-2" style="white-space: nowrap; width: 100px;">To
                        Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                        value="{{ request('end_date', $end_date) }}">
                </div>
                <div class="col-md-3 d-flex align-items-center">
                    <label for="new_category" class="form-label me-2"
                        style="white-space: nowrap; width: 100px;">Departments</label>
                    <select name="new_category" id="new_category" class="form-control">
                        <option value="" {{ $selected_category == '' ? 'selected' : '' }}>All Departments</option>
                        <option value="Fire" {{ $selected_category == 'Fire' ? 'selected' : '' }}>Fire</option>
                        <option value="Marine" {{ $selected_category == 'Marine' ? 'selected' : '' }}>Marine</option>
                        <option value="Motor" {{ $selected_category == 'Motor' ? 'selected' : '' }}>Motor</option>
                        <option value="Miscellaneous" {{ $selected_category == 'Miscellaneous' ? 'selected' : '' }}>
                            Miscellaneous</option>
                        <option value="Health" {{ $selected_category == 'Health' ? 'selected' : '' }}>Health</option>
                    </select>
                </div>
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
                <div class="col-md-3 d-flex align-items-center">
                    <label for="business_type" class="form-label me-2"
                        style="white-space: nowrap; width: 100px;">Business Type</label>
                    <select name="business_type" id="business_type" class="form-control">
                        <option value="all" {{ request('business_type', 'all') == 'all' ? 'selected' : '' }}>All
                        </option>
                        <option value="takaful" {{ request('business_type') == 'takaful' ? 'selected' : '' }}>Takaful
                        </option>
                        <option value="conventional"
                            {{ request('business_type') == 'conventional' ? 'selected' : '' }}>Conventional</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-center">
                    <label for="insu" class="form-label me-2" style="white-space: nowrap;">Insurance Type</label>
                    <select name="insu[]" id="insu" class="form-control select2-insurance" multiple>
                        <option value="D">Direct</option>
                        <option value="I">Inward</option>
                        <option value="O">Outward</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <button type="submit" class="btn btn-outline-primary me-2" title="Filter">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <a href="{{ url('/stlClmOs') }}" class="btn btn-outline-secondary me-2" title="Reset">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        @php
            $filters = [
                'all' => 'All',
                '2days' => '2 Days',
                '5days' => '5 Days',
                '7days' => '7 Days',
                '10days' => '10 Days',
                '15days' => '15 Days',
                '15plus' => '15+ Days',
            ];
        @endphp
        <div class="d-flex justify-content-start mb-3">
            <div class="card time-filter-card">
                <div class="card-body py-2 px-3">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($filters as $key => $label)
                            <a href="{{ request()->fullUrlWithQuery(['time_filter' => $key]) }}"
                                class="time-tab {{ $selected_time_filter == $key ? 'active' : 'bg-light' }}">
                                <div class="fw-bold">{{ $label }}</div>
                                <div class="small">{{ $counts[$key] }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if ($data->isEmpty())
            <div class="alert alert-danger">No data available.</div>
        @else
            <table id="reportsTable" class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Dept</th>
                        <th>Claim No.</th>
                        <th>Insured Name</th>
                        <th>Loss Amount</th>
                        <th>Survey Fee Amount</th>
                        <th>Advocate Amount</th>
                        <th>Salvage Amount</th>
                        <th>Cause of Loss</th>
                        <th>Settlement Date</th>
                    </tr>
                </thead>
                <tbody>
                      @php
                    $departmentMap = [
                    '11'  => 'FIRE',
                    '12' => 'MARINE',
                    '13'  => 'MOTOR',
                    '14' => 'MISCELLANEOUS', 
                    '16' => 'HEALTH', 
                ];
                @endphp
                    @foreach ($data as $record)
                    @php $departmentName = $departmentMap[$record->PDP_DEPT_CODE] ?? 'Other Department / Unlisted Code'; @endphp               
                        <tr>

                            <td>
                                <button class="btn btn-success btn-sm view-docs"
                                    
                                    data-doc="{{ $record->GSH_DOC_REF_NO ?? '' }}"
                                    data-loss-claimed="{{ $record->GSH_LOSSADJUSTED ?? 0 }}"
                                    data-button-type="{{ $record->button_type ?? '' }}"
                                    data-can-approve="{{ $record->can_approve ? '1' : '0' }}" data-bs-toggle="modal"
                                    data-bs-target="#docModal" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Save &Approve">

                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </td>

                            <!-- Claim No. Column -->
                            <td>{{ $departmentName ?? 'N/A' }}</td>
                            <td>{{ $record->GSH_DOC_REF_NO ?? 'N/A' }}</td>

                            <!-- Insured Name Column -->
                            <td>{{ $record->PPS_DESC ?? 'N/A' }}</td>

                            <!-- Loss Amount Column -->
                            <td style="text-align:right">{{ number_format($record->GSH_LOSSADJUSTED) ?? 'N/A' }}</td>

                            <!-- Survey Fee Amount Column -->
                            <td style="text-align:right">{{ number_format($record->SURV_AMT ?? 0) }}</td>

                            <!-- Advocate Amount Column -->
                            <td style="text-align:right">{{ number_format($record->ADV_AMT ?? 0) }}</td>

                            <!-- Salvage Amount Column -->
                            <td style="text-align:right">{{ number_format($record->SLVG_AMT ?? 0) }}</td>

                            <!-- Cause of Loss Column -->
                            <td>{{ $record->POC_LOSSDESC ?? 'N/A' }}</td>

                            <!-- Settlement Date Column -->
                            <td>{{ $record->GSH_SETTLEMENTDATE ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right"><strong>Total:</strong></td>
                        <td style="text-align:right"><strong></strong></td>
                        <td style="text-align:right"><strong></strong></td>
                        <td style="text-align:right"><strong></strong></td>
                        <td style="text-align:right"><strong></strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    <!-- Main Modal for Documents, Remarks and Approval -->
    <div class="modal fade" id="docModal" tabindex="-1" aria-labelledby="docModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="docModalLabel">Documents & Actions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="docModalTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="documents-tab" data-bs-toggle="tab"
                                data-bs-target="#documents" type="button" role="tab">Documents</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="remarks-tab" data-bs-toggle="tab" data-bs-target="#remarks"
                                type="button" role="tab">Actions</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="docModalTabsContent">
                        <!-- Documents Tab -->
                        <div class="tab-pane fade show active" id="documents" role="tabpanel">
                            <div class="mt-3">
                                <table class="table table-bordered" id="docTable">
                                    <thead>
                                        <tr>
                                            <th>File Name</th>
                                            <th>Document</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="2" class="text-center">Click "View" to load documents.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Actions Tab -->
                        <div class="tab-pane fade" id="remarks" role="tabpanel">
                            <div class="mt-3">
                                <!-- New Claim Settlement Performa Button -->
                                            <button class="btn btn-info btn-sm" id="claimSettlementBtn">
                                                <i class="bi bi-file-earmark-pdf"></i> Claim Settlement Performa
                                            </button>
                                <!-- Existing Remarks History -->
                                <div class="mb-4">
                                    <h6>Existing Remarks</h6>
                                    <div class="remarks-history" id="remarksHistory">
                                        <div class="text-center text-muted">Loading remarks...</div>
                                    </div>
                                </div>

                                <!-- Action Buttons Card -->
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Add New Remarks</h6>
                                        
                                        <!-- Add New Remarks Form -->
                                        <div class="mb-3">
                                            <textarea class="form-control" id="remarksText" rows="2"
                                                style="height: 60px; width: 100%; resize: none;"
                                                placeholder="Enter your remarks here..."></textarea>
                                        </div>
                                        
                                        <!-- Side by side small buttons with equal width -->
                                        <div class="action-buttons-row">
                                            <button type="button" class="btn btn-primary btn-sm" id="saveRemarksBtn">
                                                <i class="bi bi-save"></i> Save Remarks
                                            </button>
                                            <div id="approvalSection">
                                                <button class="btn btn-success btn-sm" id="approveButton">
                                                    <i class="bi bi-check-circle"></i> Approve
                                                </button>
                                            </div>
                                            
                                        </div>
                                        <div class="mt-1 text-muted small" id="approvalInfo"></div>
                                    </div>
                                </div>
                                
                                <!-- Hidden input for document reference -->
                                <input type="hidden" id="remarksDocRef" value="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
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
            // Initialize Select2
            $('#location_category').select2({
                placeholder: "Select a branch",
                allowClear: true
            });
            $('#insu').select2({
                placeholder: "Choose type",
                allowClear: true,
                width: '150%'
            });

            // DataTable initialization
            var table = $('#reportsTable').DataTable({
                paging: false,
                searching: true,
                ordering: true,
                info: true,
                scrollX: true,
                scrollY: "500px",
                scrollCollapse: false,
                fixedHeader: {
                    header: true,
                    footer: true
                },
                autoWidth: true,
                dom: '<"top"<"d-flex justify-content-between align-items-center"<"d-flex"f><"d-flex"B>>>rt<"bottom"ip><"clear">',
                buttons: [{
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        footer: true
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        footer: true,
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }
                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    // Loss Amount column (index 3 after removing columns)
                    var lossTotal = api.column(3, {
                        page: 'current'
                    }).data().reduce((a, b) => {
                        return a + (parseFloat(b.replace(/,/g, '')) || 0);
                    }, 0);

                    // Survey Fee column (index 4)
                    var surveyTotal = api.column(4, {
                        page: 'current'
                    }).data().reduce((a, b) => {
                        return a + (parseFloat(b.replace(/,/g, '')) || 0);
                    }, 0);

                    // Advocate Amount column (index 5)
                    var advocateTotal = api.column(5, {
                        page: 'current'
                    }).data().reduce((a, b) => {
                        return a + (parseFloat(b.replace(/,/g, '')) || 0);
                    }, 0);

                    // Salvage Amount column (index 6)
                    var salvageTotal = api.column(6, {
                        page: 'current'
                    }).data().reduce((a, b) => {
                        return a + (parseFloat(b.replace(/,/g, '')) || 0);
                    }, 0);

                    // Update footer cells
                    $(api.column(3).footer()).html('<strong>' + lossTotal.toLocaleString() +
                        '</strong>');
                    $(api.column(4).footer()).html('<strong>' + surveyTotal.toLocaleString() +
                        '</strong>');
                    $(api.column(5).footer()).html('<strong>' + advocateTotal.toLocaleString() +
                        '</strong>');
                    $(api.column(6).footer()).html('<strong>' + salvageTotal.toLocaleString() +
                        '</strong>');
                },
                initComplete: function() {
                    this.api().columns.adjust();
                    $('.dataTables_filter input').attr('placeholder');
                    $('.dataTables_filter').css('margin', '0 5px');
                    $('.dt-buttons').css('margin-left', '5px');
                },
                drawCallback: function() {
                    this.api().columns.adjust();
                }
            });
        });
    </script>

    <script>
        // Store current document reference globally
        let currentDocRef = '';
        let currentLossClaimed = 0;
        let currentButtonType = '';
        let currentCanApprove = false;

        $(document).on('click', '.view-docs', function() {
            currentDocRef = $(this).data('doc');
            currentLossClaimed = $(this).data('loss-claimed') || 0;
            currentButtonType = $(this).data('button-type') || '';
            currentCanApprove = $(this).data('can-approve') == '1';

            if (!currentDocRef) {
                alert('Document reference is missing.');
                return;
            }

            // Update title
            $('#docModalLabel').text('Documents & Actions for: ' + currentDocRef);

            // Load documents
            loadDocuments();

            // Load remarks
            loadRemarks();

            // Setup approval button
            setupApprovalButton();
        });

        function loadDocuments() {
            var tbody = $('#docTable tbody');
            tbody.html(
                '<tr><td colspan="2" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading documents...</td></tr>'
                );

            var apiUrl = 'http://192.168.170.24/dashboardApi/clm/getDiDocs.php?doc=' + encodeURIComponent(currentDocRef);

            $.ajax({
                url: apiUrl,
                method: 'GET',
                timeout: 30000,
                success: function(response) {
                    tbody.empty();
                    let files = [];

                    if (typeof response === 'string') {
                        var cleanedResponse = response
                            .replace(/<[^>]*>/g, '')
                            .replace(/File NameDocument/gi, '')
                            .trim();

                        console.log('Raw Cleaned Response:', cleanedResponse);

                        var fullPattern =
                            /[A-Z]+~\d{8}_.+?\.(pdf|msg|doc|docx|xls|xlsx|jpg|jpeg|png|gif)(?=\s+[A-Z]+~|\s*$)/gi;
                        var matches = cleanedResponse.match(fullPattern) || [];

                        console.log('Full filename matches:', matches);

                        if (matches.length === 0) {
                            console.log('Trying line-by-line parsing...');

                            var parts = cleanedResponse.split(/\s+(?=[A-Z]+~\d{8}_)/);

                            matches = parts
                                .map(part => part.trim())
                                .filter(part =>
                                    part.match(
                                        /[A-Z]+~\d{8}_.+\.(pdf|msg|doc|docx|xls|xlsx|jpg|jpeg|png|gif)$/i)
                                );

                            console.log('Line-by-line matches:', matches);
                        }

                        files = matches
                            .map(f => f.trim())
                            .filter(filename =>
                                filename &&
                                /\.(pdf|msg|doc|docx|xls|xlsx|jpg|jpeg|png|gif)$/i.test(filename) &&
                                filename.length > 20 &&
                                filename.match(/[A-Z]+~\d{8}_/)
                            );

                        console.log('Final Extracted Files:', files);
                    }

                    if (files.length === 0) {
                        tbody.html(`
                            <tr>
                                <td colspan="2" class="text-center text-muted">
                                    No documents found.                                     
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    files.forEach(function(filename) {
                        var fileUrl = 'http://192.168.170.24/dashboardApi/clm/openDiPdf.php?doc=' +
                            encodeURIComponent(currentDocRef) + '&filename=' + encodeURIComponent(
                                filename);

                        tbody.append(`
                            <tr>
                                <td><code>${filename}</code></td>
                                <td>
                                    <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-arrow-down"></i> View / Download
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary copy-filename" 
                                            data-filename="${filename}" title="Copy filename">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                },
                error: function(xhr, status, err) {
                    tbody.html(
                        '<tr><td colspan="2" class="text-center text-danger">Failed to load documents. Please try again.</td></tr>'
                        );
                    console.error('API Error:', err);
                }
            });
        }

        function loadRemarks() {
            var historyDiv = $('#remarksHistory');
            historyDiv.html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading remarks...</div>');

            $.ajax({
                url: '{{ route('getRemarks') }}',
                method: 'GET',
                data: {
                    doc: currentDocRef
                },
                success: function(response) {
                    if (response.remarks && response.remarks.length > 0) {
                        let html = '';
                        response.remarks.forEach(function(remark) {
                            html += `
                                <div class="remark-item">
                                    <strong>${remark.created_by || 'System'}</strong> 
                                    <small class="text-muted">(${remark.created_at})</small>
                                    <p class="mb-0">${remark.remarks}</p>
                                </div>
                            `;
                        });
                        historyDiv.html(html);
                    } else {
                        historyDiv.html('<div class="text-center text-muted">No remarks found.</div>');
                    }
                },
                error: function() {
                    historyDiv.html('<div class="text-center text-danger">Failed to load remarks.</div>');
                }
            });
        }

        function setupApprovalButton() {
            const approveButton = $('#approveButton');
            const approvalInfo = $('#approvalInfo');
            const claimSettlementBtn = $('#claimSettlementBtn');

            // Set the document reference in the form
            $('#remarksDocRef').val(currentDocRef);

            if (currentCanApprove) {
                if (currentButtonType === 'ok') {
                    approveButton.html('<i class="bi bi-check"></i> Mark as OK');
                } else if (currentButtonType === 'approve') {
                    approveButton.html('<i class="bi bi-check-circle"></i> Save & Approve');
                } else {
                    approveButton.hide();
                    approvalInfo.html('No approval action available.');
                }

                approveButton.show().prop('disabled', false);
                claimSettlementBtn.show().prop('disabled', false);
            } else {
                approveButton.hide();
                approvalInfo.html('This claim cannot be approved.');
            }
        }

        // Save Remarks Button
        $('#saveRemarksBtn').on('click', function() {
            const button = $(this);
            const remarks = $('#remarksText').val().trim();

            if (remarks.length === 0) {
                alert('Please enter remarks.');
                return;
            }

            button.prop('disabled', true).text('Saving...');

            $.post('{{ route('insertApproval') }}', {
                    doc: currentDocRef,
                    in_range: 0,
                    remakrs: remarks,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(res) {
                    if (res.status) {
                        alert('Remarks saved successfully!');
                        $('#remarksText').val('');
                        loadRemarks(); // Reload remarks
                    } else {
                        alert('Error saving remarks: ' + res.message);
                    }
                })
                .fail(function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Server error occurred while saving remarks.';
                    alert(msg);
                })
                .always(function() {
                    button.prop('disabled', false).html('<i class="bi bi-save"></i> Save Remarks');
                });
        });

        // Approve Button in Actions Tab
        $('#approveButton').on('click', function() {
            const button = $(this);
            const amount = currentLossClaimed || 0;

            if (!currentDocRef) {
                alert('Document reference missing');
                return;
            }

            if (!confirm(
                    `Are you sure you want to ${currentButtonType === 'ok' ? 'mark as OK' : 'approve'} this claim?`
                    )) {
                return;
            }

            button.prop('disabled', true)
                .html('<i class="bi bi-hourglass-split"></i> Processing...');

            $.post('{{ route('insertApproval') }}', {
                    doc: currentDocRef,
                    in_range: amount,
                    remakrs: '',
                    _token: '{{ csrf_token() }}'
                })
                .done(function(res) {
                    if (res.status) {
                         // Close modal FIRST (immediately)
                $('#docModal').modal('hide');
                        

                        // Update UI
                        button.html(
                                `<i class="bi bi-check-circle"></i> ${currentButtonType === 'ok' ? 'OK\'d' : 'Approved'}`
                                )
                            .prop('disabled', true);

                        // Hide the table row in the main table
                        const table = $('#reportsTable').DataTable();
                        table.row($(`button[data-doc="${currentDocRef}"]`).closest('tr')).remove().draw();
                         setTimeout(() => {
                    alert(res.message);
                }, 50); // 
                     
                

                    } else {
                        alert(res.message);
                        button.prop('disabled', false).html(
                            `<i class="bi bi-check-circle"></i> ${currentButtonType === 'ok' ? 'Mark as OK' : 'Approve'}`
                            );
                    }
                })
                .fail(function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Server error';
                    alert(msg);
                    button.prop('disabled', false).html(
                        `<i class="bi bi-check-circle"></i> ${currentButtonType === 'ok' ? 'Mark as OK' : 'Approve'}`
                        );
                });
        });

       
        // Claim Settlement Performa Button

$('#claimSettlementBtn').on('click', function() {
    const button = $(this);
    const originalHtml = button.html();
    
    if (!currentDocRef) {
        alert('Document reference is missing.');
        return;
    }

    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

    // Make AJAX request to get claim settlement performa files
    $.ajax({
        url: '{{ route("getClaimSettlementPerforma") }}',
        method: 'GET',
        data: {
            doc_num: currentDocRef
        },
        success: function(response) {
            if (response.success && response.files && response.files.length > 0) {
                // Download all files
                let downloadPromises = [];
                
                response.files.forEach((file, index) => {
                    // Add delay between downloads to prevent browser blocking
                    setTimeout(() => {
                        const link = document.createElement('a');
                        link.href = file.download_url;
                        link.target = '_blank';
                        link.download = file.file_name;
                        
                        // Add click event to track
                        link.onclick = function() {
                            console.log('Downloading:', file.file_name);
                        };
                        
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        
                        // Verify download started
                        setTimeout(() => {
                            $.ajax({
                                url: file.download_url,
                                method: 'HEAD',
                                error: function() {
                                    console.warn('File may not be accessible:', file.file_name);
                                    // Try alternative path
                                    tryAlternativePath(file.file_name, currentDocRef);
                                }
                            });
                        }, 500);
                        
                    }, index * 500); // 500ms delay between downloads
                });
                
                // Show success message
                setTimeout(() => {
                    if (response.files.length > 1) {
                        alert(`Initiated download for ${response.files.length} files`);
                    }
                }, 1000);
                
            } else {
                alert('No Claim Settlement Performa found for document: ' + currentDocRef);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching performa:', error);
            alert('Failed to load Claim Settlement Performa. Error: ' + error);
        },
        complete: function() {
            button.prop('disabled', false).html(originalHtml);
        }
    });
});

function tryAlternativePath(filename, docNum) {
    // Try direct download from known structure
    const alternativeUrl = '{{ url("/direct-download") }}' + 
        '?filename=' + encodeURIComponent(filename) + 
        '&doc_num=' + encodeURIComponent(docNum);
    
    window.open(alternativeUrl, '_blank');
}

        // Function to download file
        function downloadFile(url, filename) {
            // Create a download link
            const link = document.createElement('a');
            link.href = url;
            link.download = filename; // Use the original filename
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Copy filename functionality
        $(document).on('click', '.copy-filename', function() {
            const filename = $(this).data('filename');
            navigator.clipboard.writeText(filename).then(() => {
                $(this).html('<i class="bi bi-check"></i> Copied');
                setTimeout(() => {
                    $(this).html('<i class="bi bi-clipboard"></i>');
                }, 2000);
            });
        });
    </script>
</body>
</html>


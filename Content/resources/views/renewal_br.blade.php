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

    <x-datatable-styles />
    <style>
        .bullet {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        .card-body li:last-child {
            border-bottom: none !important;
        }
        .session-badge {
            background-color: #e9ecef;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .session-badge i {
            margin-right: 5px;
            color: #0d6efd;
        }
        .policy-info-box {
            background: #f8f9ff;
            border: 1px solid #e0e7ff;
            border-radius: 10px;
            padding: 14px 16px;
        }
        .policy-info-box .info-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6c757d;
            margin-bottom: 2px;
        }
        .policy-info-box .info-value {
            font-size: 0.92rem;
            font-weight: 600;
            color: #1a1a2e;
        }
        .renewal-option {
            flex: 1;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .renewal-option:hover {
            border-color: #0d6efd;
            background: #f0f5ff;
        }
        .renewal-option.selected-yes {
            border-color: #198754;
            background: #f0fff4;
        }
        .renewal-option.selected-no {
            border-color: #dc3545;
            background: #fff5f5;
        }
        .renewal-option input[type="radio"] {
            display: none;
        }
        .renewal-option .option-icon {
            font-size: 1.3rem;
            line-height: 1;
        }
        .renewal-option .option-label {
            font-weight: 600;
            font-size: 0.95rem;
        }
        .renewal-option .option-sub {
            display: none;
        }
        #remarks.is-invalid {
            border-color: #dc3545;
        }
        .remarks-required-note {
            font-size: 0.78rem;
            color: #dc3545;
            display: none;
            margin-top: 4px;
        }
        .remarks-required-note.show {
            display: block;
        }

        /* Success toast */
        #successToast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container mt-5">

        <div class="container mt-5">
            <x-report-header title="Renewal Report" />

            <!-- Success Toast -->
            <div id="successToast" class="alert alert-success alert-dismissible shadow" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <span id="successToastMsg"></span>
                <button type="button" class="btn-close" onclick="$('#successToast').fadeOut()"></button>
            </div>

            <!-- Success / Error Alerts  -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" id="successAlert" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <script>
                    setTimeout(function() {
                        var alert = document.getElementById('successAlert');
                        if (alert) {
                            alert.classList.remove('show');
                            setTimeout(function() { alert.remove(); }, 300);
                        }
                    }, 3000);
                </script>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Single Summary Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Renewal Summary</h5>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0 text-center">
                        <div class="col-md-3 border-end p-3">
                            <div class="text-muted small">Total Documents</div>
                            <div class="h4 fw-bold" id="summary-total-docs">{{ count($data) }}</div>
                        </div>
                        <div class="col-md-3 border-end p-3">
                            <div class="text-muted small">Total Sum Insured</div>
                            <div class="h4 fw-bold" id="summary-total-si">
                                {{ number_format($data->sum(function($item) { return (float)($item['GDH_TOTALSI'] ?? 0); })) }}
                            </div>
                        </div>
                        <div class="col-md-3 border-end p-3">
                            <div class="text-muted small">Gross Premium</div>
                            <div class="h4 fw-bold" id="summary-gross-premium">
                                {{ number_format($data->sum(function($item) { return (float)($item['GDH_GROSSPREMIUM'] ?? 0); })) }}
                            </div>
                        </div>
                        <div class="col-md-3 p-3">
                            <div class="text-muted small">Net Premium</div>
                            <div class="h4 fw-bold" id="summary-net-premium">
                                {{ number_format($data->sum(function($item) { return (float)($item['GDH_NETPREMIUM'] ?? 0); })) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department-wise Summary Card -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-primary text-white rounded-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Department Performance</h5>
                        <span class="badge bg-white text-primary">5 Departments</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive rounded-bottom">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 30%;">Department</th>
                                    <th class="text-end" style="width: 20%;">Documents</th>
                                    <th class="text-end" style="width: 25%;">Gross Premium</th>
                                    <th class="text-end pe-4" style="width: 25%;">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $departments = [
                                        'Fire'          => ['code' => 11, 'icon' => 'fire',     'color' => 'danger'],
                                        'Marine'        => ['code' => 12, 'icon' => 'ship',     'color' => 'info'],
                                        'Motor'         => ['code' => 13, 'icon' => 'car',      'color' => 'success'],
                                        'Miscellaneous' => ['code' => 14, 'icon' => 'shapes',   'color' => 'warning'],
                                        'Health'        => ['code' => 16, 'icon' => 'heartbeat','color' => 'primary']
                                    ];
                                    $totalGross = $data->sum('GDH_GROSSPREMIUM');
                                @endphp

                                @foreach($departments as $name => $dept)
                                    @php
                                        $deptData = $data->filter(function($item) use ($dept) {
                                            return ($item['PDP_DEPT_CODE'] ?? null) == $dept['code'];
                                        });
                                        $docCount     = $deptData->count();
                                        $grossPremium = $deptData->sum('GDH_GROSSPREMIUM');
                                        $percentage   = $totalGross > 0 ? ($grossPremium / $totalGross) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge bg-{{ $dept['color'] }} rounded-pill me-2">
                                                <i class="fas fa-{{ $dept['icon'] }}"></i>
                                            </span>
                                            <strong>{{ $name }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-muted">{{ number_format($docCount) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ number_format($grossPremium) }}</strong>
                                        </td>
                                        <td class="pe-4">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <span class="me-2">{{ number_format($percentage, 1) }}%</span>
                                                <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                    <div class="progress-bar bg-{{ $dept['color'] }}"
                                                         role="progressbar"
                                                         style="width: {{ $percentage }}%"
                                                         aria-valuenow="{{ $percentage }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="ps-4">Total</th>
                                    <th class="text-end">{{ number_format($data->count()) }}</th>
                                    <th class="text-end">{{ number_format($totalGross) }}</th>
                                    <th class="pe-4 text-end">100%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <form method="GET" action="{{ url('/uwRenewalBr') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4 d-flex align-items-center">
                        <label for="start_date" class="form-label me-2" style="white-space: nowrap;">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                            value="{{ request('start_date', \Carbon\Carbon::now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <label for="end_date" class="form-label me-2" style="white-space: nowrap;">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                            value="{{ request('end_date', \Carbon\Carbon::now()->addDays(30)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <label for="dept" class="form-label me-2" style="white-space: nowrap;">Department</label>
                        <select name="dept" id="dept" class="form-control">
                            <option value="">All Departments</option>
                            <option value="11" {{ request('dept') == '11' ? 'selected' : '' }}>Fire</option>
                            <option value="12" {{ request('dept') == '12' ? 'selected' : '' }}>Marine</option>
                            <option value="13" {{ request('dept') == '13' ? 'selected' : '' }}>Motor</option>
                            <option value="14" {{ request('dept') == '14' ? 'selected' : '' }}>Miscellaneous</option>
                            <option value="16" {{ request('dept') == '16' ? 'selected' : '' }}>Health</option>
                        </select>
                    </div>
                    <div class="col-md-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-outline-primary me-2" title="Filter">
                            <i class="bi bi-funnel-fill"></i> Apply Filter
                        </button>
                        <a href="{{ url('/uwRenewalBr') }}" class="btn btn-outline-secondary" title="Reset">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            @if(empty($data) || count($data) == 0)
                <div class="alert alert-info">No data available for your branch ({{ $user_branch ?? 'Unknown' }}) with the selected criteria.</div>
            @else
                @php
                    $categoryMapping = [
                        11 => 'Fire',
                        12 => 'Marine',
                        13 => 'Motor',
                        14 => 'Miscellaneous',
                        16 => 'Health',
                    ];
                @endphp

                <table id="reportsTable" class="table table-bordered table-responsive">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Base Document</th>
                            <th>Document No</th>
                            <th>Business Class</th>
                            <th>Issue Date</th>
                            <th>Branches</th>
                            <th>Department Code</th>
                            <th>Abbrevation</th>
                            <th>Insured</th>
                            <th>Client Code</th>
                            <th>Comm. Date</th>
                            <th>Expiry Date</th>
                            <th>Total Sum Insured</th>
                            <th>Gross Premium</th>
                            <th>Net Premium</th>
                            <th>Broker</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $record)
                            <tr>
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-primary btn-sm open-renewal-modal"
                                            data-doc-no="{{ $record['GDH_DOC_REFERENCE_NO'] ?? '' }}"
                                            data-base-doc="{{ $record['GDH_BASEDOCUMENTNO'] ?? '' }}"
                                            data-insured="{{ html_entity_decode(strip_tags($record['PPS_DESC'] ?? '')) }}"
                                            data-expiry="{{ isset($record['GDH_EXPIRYDATE']) ? \Carbon\Carbon::createFromFormat('d-M-y', $record['GDH_EXPIRYDATE'])->format('d-m-Y') : '' }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#renewalModal">
                                        <i class="bi bi-pencil-square"></i> Renew
                                    </button>
                                </td>
                                <td>{{ $record['GDH_BASEDOCUMENTNO'] ?? '' }}</td>
                                <td>{{ $record['GDH_DOC_REFERENCE_NO'] ?? '' }}</td>
                                <td>{{ $record['PBC_DESC'] ?? '' }}</td>
                                <td>{{ isset($record['GDH_ISSUEDATE']) ? \Carbon\Carbon::createFromFormat('d-M-y', $record['GDH_ISSUEDATE'])->format('d-m-Y') : '' }}</td>
                                <td>
                                    <span class="truncate-text" title="{{ $record['PLC_LOCADESC'] ?? '' }}">
                                        {{ \Illuminate\Support\Str::limit($record['PLC_LOCADESC'] ?? '', 8, '...') }}
                                    </span>
                                </td>
                                @php
                                    $deptCode = $record['PDP_DEPT_CODE'] ?? null;
                                @endphp
                                <td>{{ $categoryMapping[$deptCode] ?? 'N/A' }}</td>
                                <td>{{ $record['PLC_LOC_GIAS2'] ?? '' }}</td>
                                <td>
                                    <span class="truncate-text" title="{{ html_entity_decode(strip_tags($record['PPS_DESC'] ?? 'N/A')) }}">
                                        {{ \Illuminate\Support\Str::limit(html_entity_decode(strip_tags($record['PPS_DESC'] ?? 'N/A')), 8, '...') }}
                                    </span>
                                </td>
                                <td>{{ $record['PPS_PARTY_CODE'] ?? '' }}</td>
                                <td>{{ isset($record['GDH_COMMDATE']) ? \Carbon\Carbon::createFromFormat('d-M-y', $record['GDH_COMMDATE'])->format('d-m-Y') : '' }}</td>
                                <td>{{ isset($record['GDH_EXPIRYDATE']) ? \Carbon\Carbon::createFromFormat('d-M-y', $record['GDH_EXPIRYDATE'])->format('d-m-Y') : '' }}</td>
                                <td class="numeric" style="text-align: right;">
                                    {{ number_format((float) ($record['GDH_TOTALSI'] ?? 0)) }}
                                </td>
                                <td class="numeric" style="text-align: right;">
                                    {{ number_format((float) ($record['GDH_GROSSPREMIUM'] ?? 0)) }}
                                </td>
                                <td class="numeric" style="text-align: right;">
                                    {{ number_format((float) ($record['GDH_NETPREMIUM'] ?? 0)) }}
                                </td>
                                <td>
                                    <span class="truncate-text" title="{{ $record['BROKER'] ?? '' }}">
                                        {{ \Illuminate\Support\Str::limit($record['BROKER'] ?? '', 8, '...') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="12" style="text-align: right;">Totals:</th>
                            <th id="totalSumInsured"   style="text-align: right;"></th>
                            <th id="totalGrossPremium" style="text-align: right;"></th>
                            <th id="totalNetPremium"   style="text-align: right;"></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            @endif

            {{-- ═══════════════════
                 RENEWAL DECISION MODAL
            ════════════════════ --}}
            <div class="modal fade" id="renewalModal" tabindex="-1" aria-labelledby="renewalModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">

                        <!-- Header -->
                        <div class="modal-header bg-primary text-white py-3">
                            <h5 class="modal-title fw-semibold" id="renewalModalLabel">
                                <i class="bi bi-arrow-repeat me-2"></i>Policy Renewal Decision
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="renewalForm" method="POST" action="{{ url('/uwRenewalBr/decision') }}">
                            @csrf
                            <div class="modal-body p-4">

                                <!-- Policy Info Box -->
                                <div class="policy-info-box mb-4">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="info-label">Document No</div>
                                            <div class="info-value" id="modal-doc-no">—</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-label">Base Document</div>
                                            <div class="info-value" id="modal-base-doc">—</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-label">Insured</div>
                                            <div class="info-value" id="modal-insured">—</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-label">Expiry Date</div>
                                            <div class="info-value" id="modal-expiry">—</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden inputs -->
                                <input type="hidden" name="document_no"   id="input-doc-no">
                                <input type="hidden" name="base_document" id="input-base-doc">
                                <input type="hidden" name="insured_name"  id="input-insured">
                                <input type="hidden" name="expiry_date"   id="input-expiry">

                                <!-- Renewal Decision -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold mb-2">
                                        Policy Renewal <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex gap-3">

                                        <!-- YES option -->
                                        <label class="renewal-option" id="option-yes" for="renewalYes">
                                            <input type="radio" name="renewal_decision" id="renewalYes" value="1" required>
                                            <span class="option-icon text-success"><i class="bi bi-check-circle-fill"></i></span>
                                            <span class="option-label text-success">Yes</span>
                                        </label>

                                        <!-- NO option -->
                                        <label class="renewal-option" id="option-no" for="renewalNo">
                                            <input type="radio" name="renewal_decision" id="renewalNo" value="0">
                                            <span class="option-icon text-danger"><i class="bi bi-x-circle-fill"></i></span>
                                            <span class="option-label text-danger">No</span>
                                        </label>

                                    </div>
                                </div>

                                <!-- Remarks -->
                                <div class="mb-1" id="remarksContainer">
                                    <label for="remarks" class="form-label fw-semibold">
                                        Remarks
                                        <span id="requiredText" class="text-danger fw-normal small">* (Required if not Renewing)</span>
                                    </label>
                                    <textarea name="remarks" id="remarks" class="form-control"
                                              rows="3"
                                              placeholder="Enter any remarks or reason..."></textarea>
                                    <div class="remarks-required-note" id="remarksError">
                                        <i class="bi bi-exclamation-circle me-1"></i>Remarks are required when not Renewing
                                    </div>
                                </div>

                            </div>

                            <!-- Footer -->
                            <div class="modal-footer border-top px-4 pb-4 pt-3 d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                    <i class="bi bi-x me-1"></i>Cancel
                                </button>
                                <button type="submit" id="saveDecisionBtn" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i>Save Decision
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- JavaScript -->
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {

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
                        title: 'Renewal Report',
                        footer: true,
                        exportOptions: {
                            columns: ':not(:first-child)',
                            format: {
                                body: function(data, row, column, node) {
                                    if (node) {
                                        var $node = $(node);
                                        var titleText = $node.attr('title');
                                        if (titleText) return titleText;
                                        var $truncateElement = $node.find('.truncate-text');
                                        if ($truncateElement.length > 0) {
                                            var truncateTitle = $truncateElement.attr('title');
                                            if (truncateTitle) return truncateTitle;
                                        }
                                        return $node.text().trim();
                                    }
                                    if (typeof data === 'string') {
                                        return data.replace(/<[^>]*>/g, '').trim();
                                    }
                                    return data;
                                }
                            }
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Renewal Report',
                        orientation: 'landscape',
                        pageSize: 'A2',
                        exportOptions: {
                            columns: function(idx, data, node) {
                                return idx >= 1 && idx <= 15;
                            },
                            stripHtml: false,
                            format: {
                                body: function(data, row, column, node) {
                                    if (!data) return '';
                                    if (node) {
                                        var $node = $(node);
                                        var titleText = $node.attr('title');
                                        if (titleText) return titleText;
                                        var $truncateElement = $node.find('.truncate-text');
                                        if ($truncateElement.length > 0) {
                                            var truncateTitle = $truncateElement.attr('title');
                                            if (truncateTitle) return truncateTitle;
                                        }
                                    }
                                    return data.toString().replace(/<[^>]*>/g, '').trim();
                                }
                            }
                        },
                        customize: function(doc) {
                            if (doc.content[1] && doc.content[1].table) {
                                doc.content[1].table.widths = [
                                    '6%', '6%', '6%', '6%', '8%', '6%', '6%', '7%',
                                    '7%', '7%', '7%', '7%', '7%', '7%', '8%'
                                ];
                                if (doc.content[1].table.body.length > 0) {
                                    var actualColCount = doc.content[1].table.body[0].length;
                                    if (actualColCount !== 15) {
                                        doc.content[1].table.widths = Array(actualColCount).fill('*');
                                    }
                                }
                            }
                            doc.defaultStyle = { fontSize: 6, alignment: 'left' };
                            if (doc.content[1] && doc.content[1].table && doc.content[1].table.body[0]) {
                                doc.content[1].table.body[0].forEach(function(cell) {
                                    cell.fillColor = '#f2f2f2';
                                    cell.bold = true;
                                    cell.fontSize = 6;
                                });
                            }
                            doc.pageMargins = [10, 10, 10, 10];
                        }
                    }
                ],
                "columnDefs": [
                    {
                        "targets": [5, 8, 15],
                        "render": function(data, type, row) {
                            if (type === 'display') return data;
                            if (type === 'type' || type === 'sort' || type === 'export') {
                                var $temp = $('<div>').html(data);
                                var title = $temp.attr('title') || $temp.find('.truncate-text').attr('title');
                                return title || $temp.text() || data;
                            }
                            return data;
                        }
                    },
                    {
                        "targets": 0,
                        "orderable": false,
                        "searchable": false
                    }
                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();
                    var intVal = function(i) {
                        return typeof i === 'string' ? i.replace(/[^\d.-]/g, '') * 1 :
                               typeof i === 'number' ? i : 0;
                    };
                    var totalSumInsured   = api.column(12, {page: 'current'}).data().reduce(function(a, b) { return intVal(a) + intVal(b); }, 0);
                    var totalGrossPremium = api.column(13, {page: 'current'}).data().reduce(function(a, b) { return intVal(a) + intVal(b); }, 0);
                    var totalNetPremium   = api.column(14, {page: 'current'}).data().reduce(function(a, b) { return intVal(a) + intVal(b); }, 0);
                    $('#totalSumInsured').html(totalSumInsured.toLocaleString('en-US'));
                    $('#totalGrossPremium').html(totalGrossPremium.toLocaleString('en-US'));
                    $('#totalNetPremium').html(totalNetPremium.toLocaleString('en-US'));
                },
                initComplete: function() {
                    this.api().columns.adjust();
                    $('.dataTables_filter').css('margin-left', '5px').css('margin-right', '5px');
                    $('.dt-buttons').css('margin-left', '5px');
                },
                drawCallback: function() {
                    this.api().columns.adjust();
                }
            });

            
            var $clickedRow = null;

         
            $(document).on('click', '.open-renewal-modal', function() {
              
                $clickedRow = $(this).closest('tr');

                var docNo   = $(this).data('doc-no');
                var baseDoc = $(this).data('base-doc');
                var insured = $(this).data('insured');
                var expiry  = $(this).data('expiry');

                // Display values
                $('#modal-doc-no').text(docNo   || '—');
                $('#modal-base-doc').text(baseDoc || '—');
                $('#modal-insured').text(insured  || '—');
                $('#modal-expiry').text(expiry    || '—');

                // Hidden inputs
                $('#input-doc-no').val(docNo);
                $('#input-base-doc').val(baseDoc);
                $('#input-insured').val(insured);
                $('#input-expiry').val(expiry);

                // Reset form state
                $('input[name="renewal_decision"]').prop('checked', false);
                $('#option-yes, #option-no').removeClass('selected-yes selected-no');
                $('#remarks').val('').removeClass('is-invalid');
                $('#remarksError').removeClass('show');
                $('#requiredText').show();
            });

            /* ── Show/hide required text based on radio selection ── */
            $(document).on('change', 'input[name="renewal_decision"]', function() {
                var val = $(this).val();

                $('#option-yes').removeClass('selected-yes selected-no');
                $('#option-no').removeClass('selected-yes selected-no');

                if (val === '1') {
                    $('#option-yes').addClass('selected-yes');
                    $('#requiredText').hide();
                } else {
                    $('#option-no').addClass('selected-no');
                    $('#requiredText').show();
                }
            });

            /* ── AJAX form submission ── */
            $('#renewalForm').on('submit', function(e) {
                e.preventDefault(); 

                var decision = $('input[name="renewal_decision"]:checked').val();
                var remarks  = $('#remarks').val().trim();

                // Validation
                if (decision === undefined) {
                    alert('Please select a renewal decision (Yes or No).');
                    return false;
                }
                if (decision === '0' && !remarks) {
                    $('#remarks').addClass('is-invalid').focus();
                    $('#remarksError').addClass('show');
                    return false;
                }

                $('#remarks').removeClass('is-invalid');
                $('#remarksError').removeClass('show');

                var $submitBtn = $('#saveDecisionBtn');
                $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Saving...');

                $.ajax({
                    url: $('#renewalForm').attr('action'),
                    method: 'POST',
                    data: $('#renewalForm').serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                           
                            $('#renewalModal').modal('hide');

                          
                            if ($clickedRow) {
                                table.row($clickedRow).remove().draw(false);
                                $clickedRow = null;
                            }

                            
                            updateSummaryCard();

                           
                            showSuccessToast(response.message || 'Renewal decision saved successfully.');
                        } else {
                            alert('Something went wrong. Please try again.');
                        }
                    },
                    error: function(xhr) {
                        var msg = 'Error saving decision. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        alert(msg);
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Save Decision');
                    }
                });
            });

           
            function updateSummaryCard() {
                var intVal = function(i) {
                    return typeof i === 'string' ? parseFloat(i.replace(/[^\d.-]/g, '')) || 0 :
                           typeof i === 'number' ? i : 0;
                };

                var totalDocs  = table.rows().count();
                var totalSI    = table.column(12).data().reduce(function(a, b) { return intVal(a) + intVal(b); }, 0);
                var totalGross = table.column(13).data().reduce(function(a, b) { return intVal(a) + intVal(b); }, 0);
                var totalNet   = table.column(14).data().reduce(function(a, b) { return intVal(a) + intVal(b); }, 0);

                $('#summary-total-docs').text(totalDocs.toLocaleString('en-US'));
                $('#summary-total-si').text(totalSI.toLocaleString('en-US'));
                $('#summary-gross-premium').text(totalGross.toLocaleString('en-US'));
                $('#summary-net-premium').text(totalNet.toLocaleString('en-US'));
            }

           
            function showSuccessToast(message) {
                $('#successToastMsg').text(message);
                $('#successToast').fadeIn(300);
                setTimeout(function() {
                    $('#successToast').fadeOut(500);
                }, 3500);
            }

        });
    </script>
</body>
</html>
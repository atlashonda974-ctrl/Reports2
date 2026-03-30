@extends('upmaster.master')
@section('title', 'Attendance Record Management')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Main content wrapper */
    .main-content {
        margin-left: 80px; /* Adjust based on your sidebar width */
        margin-top: 100px; /* Adjust based on your header height */
        padding: 20px;
        transition: all 0.3s ease;
        min-height: calc(100vh - 70px);
        background-color: #f8f9fa;
    }
    
    /* When sidebar is collapsed */
    .sidebar-mini .main-content {
        margin-left: 70px; /* Smaller margin for collapsed sidebar */
    }
    
    /* Card styling */
    .content-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        height: calc(100vh - 90px);
        display: flex;
        flex-direction: column;
    }
    
    /* Card header */
    .card-header-custom {
        background: white;
        border-bottom: 1px solid #eef1f5;
        padding: 1.25rem 1.5rem;
        border-radius: 10px 10px 0 0;
    }
    
    /* Card body - takes remaining space */
    .card-body-custom {
        padding: 1.5rem;
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    /* DataTable container */
    .table-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    
    /* DataTable wrapper */
    #attendanceTable_wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    
    /* DataTable scroll area */
    .dataTables_scrollBody {
        flex: 1;
        min-height: 0 !important;
    }
    
    /* Header styling */
    .page-title {
        font-family: 'Reddit Sans', sans-serif;
        font-weight: 600;
        font-size: 1.5rem;
        color: #2c3e50;
        margin: 0;
    }
    
    /* Button styling */
    .btn-surveyor {
        background-color: #198754;
        border-color: #198754;
        color: white;
        margin-right: 10px;
    }
    
    .btn-surveyor:hover {
        background-color: #157347;
        border-color: #146c43;
    }
    
    .btn-add-record {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }
    
    .btn-add-record:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    
    /* Alert styling */
    .alert-success {
        background-color: #d1e7dd;
        border-color: #badbcc;
        color: #0f5132;
        border-radius: 8px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0 !important;
            padding: 15px;
        }
        
        .content-card {
            height: auto;
            min-height: calc(100vh - 70px);
        }
        
        .d-flex {
            flex-direction: column;
            gap: 10px;
        }
        
        .flex-grow-1 {
            display: none;
        }
        
        .btn-surveyor,
        .btn-add-record {
            width: 100%;
            margin-bottom: 5px;
            margin-right: 0;
        }
    }
    
    /* DataTable custom styles */
    #attendanceTable {
        width: 100% !important;
    }
    
    #attendanceTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Badge styling */
    .badge {
        padding: 0.4em 0.8em;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="main-content" id="mainContent">
    <div class="content-card">
        <!-- Card Header -->
        <div class="card-header-custom">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="page-title" style="font-size: 20px;">
                  Attendance Records
                </h5>
                
                <div class="d-flex align-items-center">
                    <!-- Surveyor Portal  Integration -->
               @php
    $ts = time();
    $secret = config('services.portal1.secret', 'ed2a55fb0653eb0bad1b6391cb907b4c3da9f8b8af40f8063910eff927e88c7d');
    if ($secret) {
        $sig = hash_hmac('sha256', (string)$ts, $secret);
        $portalUrl = config('services.portal1.url', 'http://192.168.170.24/Surveyor') . '/embedded/files';
    }
@endphp

@if(isset($portalUrl))
    <button onclick="openSurveyor({{ $ts }}, '{{ $sig }}')" class="btn btn-surveyor">
        <i class="fas fa-external-link-alt me-1"></i> Surveyor Portal
    </button>
    
    <script>
    function openSurveyor(ts, sig) {
        
        const url = '{{ $portalUrl }}?ts=' + ts + '&sig=' + sig + '&user_name=' + encodeURIComponent('{{ auth()->check() ? auth()->user()->name : "ReUserport " }}');
        window.open(url, '_blank');
    }
    </script>
@else
    <p>Configuration error</p>
@endif
                    
                    <!-- Add Record Button -->
                    <a href="{{ route('attreq.create') }}" class="btn btn-add-record">
                        <i class="fas fa-plus me-1"></i> Add Record
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Body -->
        <div class="card-body-custom">
            <!-- Success Alert -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" id="successAlert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Table Container -->
            <div class="table-container">
                <table id="attendanceTable" class="table table-striped table-bordered w-100">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Actions</th>
                            <th>Employee Code</th>
                            <th>Schedule Date</th>
                            <th>Attendance Type</th>
                            <th>Remarks</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded by DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    var baseUrl = "{{ url('att_reqs') }}";
    
    // Auto-hide success alert after 3 seconds
    setTimeout(function() {
        var successAlert = $('#successAlert');
        if (successAlert.length) {
            successAlert.fadeOut(500, function() {
                $(this).alert('close');
            });
        }
    }, 3000);
    
    // Calculate table height dynamically
    function calculateTableHeight() {
        var cardBody = $('.card-body-custom');
        var alertHeight = $('#successAlert').outerHeight() || 0;
        var buttonsHeight = $('#attendanceTable_wrapper .dt-buttons').outerHeight() || 50;
        var filterHeight = $('#attendanceTable_wrapper .dataTables_filter').outerHeight() || 40;
        var paginationHeight = $('#attendanceTable_wrapper .dataTables_paginate').outerHeight() || 40;
        var infoHeight = $('#attendanceTable_wrapper .dataTables_info').outerHeight() || 30;
        
        var availableHeight = cardBody.height() - alertHeight - buttonsHeight - filterHeight - 
                              paginationHeight - infoHeight - 40;
        
        return Math.max(availableHeight, 300); // Minimum height
    }
    
   
    var table = $('#attendanceTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('attreq.index') }}",
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'id',
                name: 'id'
            },
            { 
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<a href="${baseUrl}/${row.id}/edit" class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>`;
                }
            },
            { 
                data: 'empcode',
                render: function(data) {
                    return data;
                }
            },
            { 
                data: 'schddate',
                render: function(data) {
                    if (!data) return 'N/A';
                    const date = new Date(data);
                    return date.toLocaleDateString('en-GB', { 
                        day: '2-digit', 
                        month: 'short', 
                        year: 'numeric' 
                    });
                }
            },
            { 
                data: 'att',
                render: function(data) {
                    const typeConfig = {
                        'Manual': { color: 'primary' },
                        'Official Visit': { color: 'info' },
                        'Travel': { color: 'success' },
                        'Training': { color: 'warning' }
                    };
                    const config = typeConfig[data] || { color: 'secondary' };
                    return `<span class="badge bg-${config.color}">${data}</span>`;
                }
            },
            { 
                data: 'remarks',
                render: function(data) {
                    return data || '<span class="text-muted">No remarks</span>';
                }
            },
            { 
                data: 'created_at',
                render: function(data) {
                    if (!data) return 'N/A';
                    const date = new Date(data);
                    return date.toLocaleDateString('en-GB', { 
                        day: '2-digit', 
                        month: 'short', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                }
            }
        ],
        order: [[0, 'asc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        scrollY: calculateTableHeight(),
        scrollCollapse: true,
        paging: true,
        dom: '<"row mb-3"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-sm btn-secondary me-1',
                text: '<i class="fas fa-copy me-1"></i> Copy',
                exportOptions: {
                    columns: [0, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'excel',
                className: 'btn btn-sm btn-success me-1',
                text: '<i class="fas fa-file-excel me-1"></i> Excel',
                exportOptions: {
                    columns: [0, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'pdf',
                className: 'btn btn-sm btn-danger me-1',
                text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                exportOptions: {
                    columns: [0, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'print',
                className: 'btn btn-sm btn-info',
                text: '<i class="fas fa-print me-1"></i> Print',
                exportOptions: {
                    columns: [0, 2, 3, 4, 5, 6]
                }
            }
        ],
        language: {
            search: "Search records:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No matching records found",
            emptyTable: "No data available in table",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        initComplete: function() {
          
            this.api().columns.adjust();
        }
    });
    
    
    $(window).on('resize', function() {
        table.api().scrollY(calculateTableHeight());
        table.api().columns.adjust();
    });
    
   
    $('.sidebar-toggle').on('click', function() {
        setTimeout(function() {
            table.api().scrollY(calculateTableHeight());
            table.api().columns.adjust();
        }, 300);
    });
    
  
    $('[title]').tooltip({
        trigger: 'hover',
        placement: 'top'
    });
});
</script>
@endpush
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar - 60px width, white */
        .sidebar {
            width: 80px;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            background-color: white;
           
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            z-index: 1000;
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            margin-bottom: 30px;
        }

        .sidebar-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sidebar-menu {
            width: 100%;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 0;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
        }

        .sidebar-menu a:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }

        .sidebar-menu a:hover i {
            animation: bounceIcon 0.6s ease;
        }

        .sidebar-menu a.active {
           
            color: #0d6efd;
        }

        .sidebar-menu a.active i {
            animation: pulseIcon 1.5s infinite;
        }

        @keyframes bounceIcon {
            0%, 100% {
                transform: scale(1);
            }
            25% {
                transform: scale(1.2); 
            }
            50% {
                transform: scale(0.9);
            }
            75% {
                transform: scale(1.1);
            }
        }

        @keyframes pulseIcon {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .sidebar-menu i {
            font-size: 20px;
        }

        /* Top Header - White */
        .top-header {
            position: fixed;
            left: 60px;
            top: 0;
            right: 0;
            height: 60px;
            background-color: white;
            
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 20px;
            z-index: 999;
        }

        .fullscreen-icon {
            background: none;
            border: none;
            color: #6c757d;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            transition: all 0.3s;
        }

        .fullscreen-icon:hover {
            color: #0d6efd;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 60px;
            margin-top: 60px;
            padding: 30px;
            min-height: calc(100vh - 60px);
        }

        /* Card Styles */
        .card {
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-radius: 8px;
            margin-bottom: 20px;
            background: white;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 20px;
            font-weight: 600;
            font-size: 16px;
            color: #212529;
            border-radius: 8px 8px 0 0;
        }

        .card-body {
            padding: 20px;
        }

        /* Form Styles */
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control, .form-select {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.25);
        }

        /* Button Styles */
        .btn {
            border-radius: 4px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        /* Table Styles */
        .table {
            background: white;
            font-size: 14px;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: #212529;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            padding: 12px;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }

        /* DataTable Styles */
        .dataTables_wrapper .dataTables_length select {
            padding: 5px 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .dataTables_wrapper .dataTables_filter input {
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        /* Badge Styles */
        .badge {
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 500;
        }

        /* Alert Styles */
        .alert {
            border-radius: 4px;
            font-size: 14px;
        }
        
    </style>

    @yield('styles')
</head>
<body>
    <!-- Sidebar - 60px width, white -->
    <div class="sidebar">
        <!-- Logo -->
        <a href="{{ route('attreq.index') }}" class="sidebar-logo">
             <img src="{{ asset('images/atlas.png') }}" alt="Logo" style="width:40px; height:auto;">
        </a>
        
        <!-- Sidebar Menu - Only Home Icon -->
        <div class="sidebar-menu">
            <a href="{{ route('attreq.index') }}" class="{{ request()->routeIs('attreq.index') ? 'active' : '' }}" title="Home">
                <i class="fas fa-home"></i>
            </a>
        </div>
    </div>

    <!-- Top Header - White with fullscreen icon at right end -->
    <div class="top-header">
        <button class="fullscreen-icon" title="Fullscreen">
            <i class="fas fa-expand"></i>
        </button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>
   

   <!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Buttons extension -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- JSZip for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<!-- PDFMake for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>




    @yield('scripts')
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="keywords" content="" />
<meta name="author" content="" />
<meta name="robots" content="" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="MotaAdmin - Bootstrap Admin Dashboard" />
<meta property="og:title" content="MotaAdmin - Bootstrap Admin Dashboard" />
<meta property="og:description" content="MotaAdmin - Bootstrap Admin Dashboard" />
<meta property="og:image" content="social-image.png" />
<meta name="format-detection" content="telephone=no">

<title>Atlas Insurance - Branches</title>
<!-- Added DataTable RTD-->
        <style>
        /* Main table fixes */
        table.dataTable {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        /* Column styling */
        th, td {
            font-size: 14px !important;
            font-family: Calibri, sans-serif !important;
            padding: 2px !important;
            border-collapse: collapse !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Header styling */
        th {
            text-align: center !important;
            border: 1px #4682B4 !important;
            background-color: #4682B4 !important;
            color: white !important;
            font-weight: bold !important;
        }

        /* Cell styling */
        td {
            border: 1px solid black !important;
            vertical-align: top !important;
            text-align: left;
        }

        /* Footer styling */
        tfoot th, tfoot td {
            border: 1px solid black !important;
            background-color: blue !important;
            color: white !important;
            text-align: center !important;
            box-sizing: border-box; 
        }

        /* Ensure the footer spans the full width (for scrollable tables) */
        .dataTables_scrollFoot {
            width: 100% !important;
        }
        .dataTables_scrollFootInner {
            width: 100% !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            th, td {
                font-size: 12px !important;
            }
        }

        /* DataTables wrapper adjustments */
        .dataTables_wrapper {
            margin-top: 0px;
            margin-left: 0;
            width: 100%;
            padding: 0;
        }

        /* Search container */
        .dataTables_filter {
            margin-bottom: 0px;
            width: 100%;
            padding: 0;
        }

        /* Search input styling */
        .dataTables_filter input {
            width: 250px !important;
            font-size: 12px !important;
            border: 2px solid #007bff !important;
            border-radius: 5px !important;
            padding: 5px !important;
            margin-left: 0 !important;
        }

        /* Buttons container */
        .dt-buttons {
            margin-bottom: 2px !important;
            padding: 0 !important;
        }

        /* Button styling */
        .dt-buttons .btn {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            margin-right: 3px !important;
        }

        /* Top container */
        .dataTables_wrapper .top {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 10px;
            margin-bottom: -22px;
            padding: 0;
            flex-wrap: wrap;
        }

        /* Breadcrumb styling */
        .breadcrumb {
            background-color: #f8f9fa;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 14px;
        }

        /* Select2 styling */
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-container .select2-dropdown {
            border-color: #ced4da;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Custom icon color */
        .custom-icon {
            color: black !important;
        }
    </style>

<title>Atlas Insurance - Branches</title>

<!-- Added DataTable links css-->

    <link href="{{URL::asset('vendor/jqvmap/css/jqvmap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{URL::asset('vendor/chartist/css/chartist.min.css') }}">
    <link href="{{URL::asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{URL::asset('css/style.css') }}" rel="stylesheet">
    <link href="{{URL::asset('css/lineicon.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.3.2/css/fixedHeader.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">



<link href="{{URL::asset('vendor/jqvmap/css/jqvmap.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{URL::asset('vendor/chartist/css/chartist.min.css') }}">
<link href="{{URL::asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
<link href="{{URL::asset('css/style.css') }}" rel="stylesheet">
<link href="{{URL::asset('css/lineicon.css') }}" rel="stylesheet">


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.1.0"></script>
<script src="{{ asset('js/jquery/jquery.min.js')}}"></script>




</head>
<body>

<?php
use Illuminate\Support\Facades\Session;
function hasPassed30Daysmaster() {
  $userDate = Session::get('user')['updated_at']; 
  if (is_null($userDate)) {
    return true;
  }else{
    $givenDate = new DateTime($userDate);
    $currentDate = new DateTime();
    $difference = $currentDate->diff($givenDate);
    return $difference->days >= 30 && $difference->invert == 1; // invert == 1 means the given date is in the past
  }
  
}
?>


 {{ View::make('header') }}
    {{ View::make('navbar') }}
{{-- @if(!hasPassed30Daysmaster())
{{ View::make('navbar') }}
@endif --}}
@yield('content')



<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.1.0"></script>
<script src="{{ asset('cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js')}}"></script>
<script src="{{ asset('js/deznav-init.js')}}"></script>
<script src="{{asset('vendor/global/global.min.js') }}"></script>
<script src="{{asset('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
<script src="{{asset('vendor/chart.js/Chart.bundle.min.js') }}"></script>
<script src="{{asset('js/custom.min.js') }}"></script>
<script src="{{asset('vendor/apexchart/apexchart.js') }}"></script>
<script src="{{asset('vendor/peity/jquery.peity.min.js') }}"></script>
<script src="{{asset('vendor/chartist/js/chartist.min.js') }}"></script>
<script src="{{asset('js/dashboard/dashboard-1.js') }}"></script>
<script src="{{asset('vendor/svganimation/vivus.min.js') }}"></script>
<script src="{{asset('vendor/svganimation/svg.animation.js') }}"></script>
<script>

	
	function getUrlParams(dParam) {
		var dPageURL = window.location.search.substring(1),
			dURLVariables = dPageURL.split('&'),
			dParameterName,
			i;

		for (i = 0; i < dURLVariables.length; i++) {
			dParameterName = dURLVariables[i].split('=');

			if (dParameterName[0] === dParam) {
				return dParameterName[1] === undefined ? true : decodeURIComponent(dParameterName[1]);
			}
		}
	}
	
	(function($) {
		"use strict"

		var direction =  getUrlParams('dir');
		if(direction != 'rtl')
		{direction = 'ltr'; }
		
		var dezSettingsOptions = {
			typography: "roboto",
			version: "light",
			layout: "vertical",
			headerBg: "color_1",
			navheaderBg: "color_3",
			sidebarBg: "color_1",
			sidebarStyle: "mini",
			sidebarPosition: "fixed",
			headerPosition: "fixed",
			containerLayout: "wide",
			direction: direction
		};
		
		new dezSettings(dezSettingsOptions); 
		
		jQuery(window).on('resize',function(){
			
			var sidebar = 'mini';
			var screenWidth = jQuery(window).width();
			if(screenWidth < 600){
				sidebar = 'overlay';
			}
			dezSettingsOptions.sidebarStyle = sidebar;
			
			new dezSettings(dezSettingsOptions); 
		});

	})(jQuery);	
	</script>
		</script>
	<!-- Added DataTable scripts-->

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
<script>
	$(document).ready(function() {
    var table = $('#example').DataTable({
        "paging": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "scrollX": true,
        "scrollY": "500px",
        "scrollCollapse": false,
        "fixedHeader": {
            header: true
        },
        "autoWidth": false, // Try setting this to false
        dom: '<"top"<"d-flex justify-content-between align-items-center"<"d-flex"f><"d-flex"B>>>rt<"bottom"ip><"clear">',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel custom-icon"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Report',
                footer: true,
                exportOptions: {
                    modifier: {
                        page: 'current'
                    }
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf custom-icon"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Report',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {
                    columns: ':visible',
                    modifier: {
                        page: 'current'
                    }
                }
            }
        ],
        initComplete: function() {
            this.api().columns.adjust();
            $('.dataTables_filter').css('margin-left', '5px').css('margin-right', '5px');
            $('.dt-buttons').css('margin-left', '5px');
        },
        drawCallback: function() {
            this.api().columns.adjust();
        }
    });
});
	</script>
    
</body>

</html>
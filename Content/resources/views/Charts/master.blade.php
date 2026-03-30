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

<title>@yield('title', 'UnderWriting-Dashboard')</title>

<link href="{{URL::asset('vendor/jqvmap/css/jqvmap.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{URL::asset('vendor/chartist/css/chartist.min.css') }}">
<link href="{{URL::asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
<link href="{{URL::asset('css/style.css') }}" rel="stylesheet">
<link href="{{URL::asset('css/lineicon.css') }}" rel="stylesheet">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.1.0"></script>

</head>
<body>

<?php
use Illuminate\Support\Facades\Session;

// Keep this function for other purposes
function hasPassed30Daysmaster() {
    $userDate = Session::get('user')['updated_at']; 
    if (is_null($userDate)) {
        return true;
    } else {
        $givenDate = new DateTime($userDate);
        $currentDate = new DateTime();
        $difference = $currentDate->diff($givenDate);
        return $difference->days >= 30 && $difference->invert == 1; // invert == 1 means date is in past
    }
}
?>

{{-- Always include header and sidebar --}}
{{ View::make('header') }}
{{ View::make('navbar') }}

@yield('content')

<!-- JS Scripts -->
<script src="{{ asset('cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js')}}"></script>
<script src="{{ asset('js/deznav-init.js')}}"></script>

<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('js/plugins-init/datatables.init.js')}}"></script>

<script src="{{asset('vendor/global/global.min.js') }}"></script>
<script src="{{asset('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
<script src="{{asset('js/custom.min.js') }}"></script>
<script src="{{asset('vendor/apexchart/apexchart.js') }}"></script>
<script src="{{asset('vendor/peity/jquery.peity.min.js') }}"></script>
<script src="{{asset('vendor/chartist/js/chartist.min.js') }}"></script>
<script src="{{asset('js/dashboard/dashboard-1.js') }}"></script>
<script src="{{asset('vendor/svganimation/vivus.min.js') }}"></script>
<script src="{{asset('vendor/svganimation/svg.animation.js') }}"></script>

<script>
(function($) {
    "use strict";

    function getUrlParams(dParam) {
        var dPageURL = window.location.search.substring(1),
            dURLVariables = dPageURL.split('&'),
            dParameterName, i;

        for (i = 0; i < dURLVariables.length; i++) {
            dParameterName = dURLVariables[i].split('=');
            if (dParameterName[0] === dParam) {
                return dParameterName[1] === undefined ? true : decodeURIComponent(dParameterName[1]);
            }
        }
    }

    var direction = getUrlParams('dir');
    if(direction != 'rtl') { direction = 'ltr'; }

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

    jQuery(window).on('resize', function(){
        var sidebar = 'mini';
        var screenWidth = jQuery(window).width();
        if(screenWidth < 600) sidebar = 'overlay';
        dezSettingsOptions.sidebarStyle = sidebar;
        new dezSettings(dezSettingsOptions);
    });

})(jQuery);
</script>

@stack('scripts')

</body>
</html>

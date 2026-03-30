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
    
    <title>Atlas Insurance - @yield('title')</title>

    <!-- Master file CSS -->
    <link href="{{URL::asset('vendor/jqvmap/css/jqvmap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{URL::asset('vendor/chartist/css/chartist.min.css') }}">
    <link href="{{URL::asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{URL::asset('css/style.css') }}" rel="stylesheet">
    <link href="{{URL::asset('../../cdn.lineicons.com/2.0/LineIcons.css') }}" rel="stylesheet">
    
    <!-- Report-specific CSS -->
    @stack('styles')
</head>
<body>

    {{ View::make('header') }}
    {{ View::make('navbar') }}

    <div class="container mt-5">
        @yield('content')
    </div>

    <!-- Master file JS -->
    <script src="{{URL::asset('vendor/global/global.min.js') }}"></script>
    <script src="{{URL::asset('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
    <script src="{{URL::asset('vendor/chart.js/Chart.bundle.min.js') }}"></script>
    <script src="{{URL::asset('js/custom.min.js') }}"></script>
    <script src="{{URL::asset('vendor/apexchart/apexchart.js') }}"></script>
    <script src="{{URL::asset('vendor/peity/jquery.peity.min.js') }}"></script>
    <script src="{{URL::asset('vendor/chartist/js/chartist.min.js') }}"></script>
    <script src="{{URL::asset('js/dashboard/dashboard-1.js') }}"></script>
    <script src="{{URL::asset('vendor/svganimation/vivus.min.js') }}"></script>
    <script src="{{URL::asset('vendor/svganimation/svg.animation.js') }}"></script>
    
    @stack('scripts')
    
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
</body>
</html>
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

    <!-- CSS Dependencies -->
    <link href="{{ URL::asset('vendor/jqvmap/css/jqvmap.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('vendor/chartist/css/chartist.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('css/lineicon.css') }}" rel="stylesheet">

    <!-- Additional CSS Sections -->
    @stack('styles')
    
    <!-- Inline Styles Section -->
    @stack('inline-styles')

    <!-- PHP Function as JavaScript -->
    <script>
        // Convert PHP function to JavaScript for frontend use
        window.hasPassed30Daysmaster = function(userDate) {
            if (!userDate) {
                return true;
            }
            
            try {
                const givenDate = new Date(userDate);
                const currentDate = new Date();
                const timeDifference = currentDate.getTime() - givenDate.getTime();
                const daysDifference = timeDifference / (1000 * 3600 * 24);
                
                return daysDifference >= 30;
            } catch (error) {
                console.error('Error calculating date difference:', error);
                return true;
            }
        };

        // Make the function globally available
        window.checkIf30DaysPassed = function() {
            // This would need to be populated from your Laravel session
            // You can pass this data from your controller
            const userUpdatedAt = @json(session('user.updated_at', null));
            return window.hasPassed30Daysmaster(userUpdatedAt);
        };
    </script>

</head>
<body>

<?php
// PHP version of the function for server-side use
use Illuminate\Support\Facades\Session;

function hasPassed30Daysmaster() {
    $userDate = Session::get('user')['updated_at'] ?? null;
    
    if (is_null($userDate)) {
        return true;
    }
    
    try {
        $givenDate = new DateTime($userDate);
        $currentDate = new DateTime();
        $difference = $currentDate->diff($givenDate);
        
        // Return true if 30 or more days have passed
        return ($difference->days >= 30 && $difference->invert == 1);
    } catch (Exception $e) {
        // Log error if needed
        return true;
    }
}
?>

{{-- Conditionally show elements based on the function result --}}
@php
    $shouldShowAlert = hasPassed30Daysmaster();
@endphp

@if($shouldShowAlert)
    <!-- You can add a hidden input or data attribute to pass this to JavaScript -->
    <div id="date-check-data" data-days-passed="true" style="display: none;"></div>
@else
    <div id="date-check-data" data-days-passed="false" style="display: none;"></div>
@endif

{{-- Always include header and sidebar --}}
{{ View::make('header') }}
{{ View::make('navbar') }}

<main id="main-content">
    @yield('content')
</main>

<!-- Core JavaScript Dependencies -->
<script src="{{ asset('vendor/global/global.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<!-- Plugin Scripts -->
<script src="{{ asset('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('vendor/apexchart/apexchart.js') }}"></script>
<script src="{{ asset('vendor/peity/jquery.peity.min.js') }}"></script>
<script src="{{ asset('vendor/chartist/js/chartist.min.js') }}"></script>
<script src="{{ asset('vendor/svganimation/vivus.min.js') }}"></script>
<script src="{{ asset('vendor/svganimation/svg.animation.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.1.0"></script>

<!-- Custom Application Scripts -->
<script src="{{ asset('js/deznav-init.js') }}"></script>
<script src="{{ asset('js/plugins-init/datatables.init.js') }}"></script>
<script src="{{ asset('js/custom.min.js') }}"></script>
<script src="{{ asset('js/dashboard/dashboard-1.js') }}"></script>

<!-- DezSettings Configuration -->
<script>
(function($) {
    "use strict";
    
    // Prevent multiple initializations
    if (window.dezSettingsInitialized) {
        return;
    }
    window.dezSettingsInitialized = true;

    // Function to get URL parameters
    function getUrlParam(param) {
        var pageURL = window.location.search.substring(1),
            urlVariables = pageURL.split('&'),
            parameterName,
            i;

        for (i = 0; i < urlVariables.length; i++) {
            parameterName = urlVariables[i].split('=');
            if (parameterName[0] === param) {
                return parameterName[1] === undefined ? true : decodeURIComponent(parameterName[1]);
            }
        }
        return null;
    }

    // Check if 30 days have passed (using server-side calculation)
    function checkServerSideDateStatus() {
        const dateCheckElement = document.getElementById('date-check-data');
        if (dateCheckElement) {
            const daysPassed = dateCheckElement.getAttribute('data-days-passed') === 'true';
            if (daysPassed) {
                // 30 days have passed - you can trigger actions here
                console.log('30 days have passed since last update');
                
                // Example: Show a notification or redirect
                // if (typeof showNotification === 'function') {
                //     showNotification('Your password is over 30 days old. Please update it.');
                // }
            }
        }
    }

    // Initialize DezSettings
    function initializeDezSettings() {
        var direction = getUrlParam('dir');
        if (direction !== 'rtl') {
            direction = 'ltr';
        }

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

        // Check if dezSettings exists before calling
        if (typeof dezSettings === 'function') {
            new dezSettings(dezSettingsOptions);
        }
    }

    // Handle window resize
    function handleResize() {
        var sidebar = 'mini';
        var screenWidth = $(window).width();
        if (screenWidth < 600) {
            sidebar = 'overlay';
        }
        
        if (typeof dezSettings === 'function') {
            var direction = getUrlParam('dir') || 'ltr';
            var dezSettingsOptions = {
                typography: "roboto",
                version: "light",
                layout: "vertical",
                headerBg: "color_1",
                navheaderBg: "color_3",
                sidebarBg: "color_1",
                sidebarStyle: sidebar,
                sidebarPosition: "fixed",
                headerPosition: "fixed",
                containerLayout: "wide",
                direction: direction
            };
            new dezSettings(dezSettingsOptions);
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        // Check if jQuery is loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        
        // Check date status
        checkServerSideDateStatus();
        
        // Initialize layout settings
        initializeDezSettings();
        
        // Debounced resize handler
        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(handleResize, 250);
        });
    });

})(jQuery || window.jQuery);
</script>

<!-- Additional Scripts Section -->
@stack('scripts')

<!-- Inline Scripts Section -->
@stack('inline-scripts')

</body>
</html>
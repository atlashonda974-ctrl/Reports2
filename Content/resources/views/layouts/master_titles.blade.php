@if(isset($title))
    <title>{{ $title }}</title>
@elseif(request()->is('employees-attedence-report'))
    <title>Employee's Attendance Report</title>
@elseif(request()->is('employee-wise-report'))
    <title>Employee Wise Attendance Report</title>
@elseif(request()->is('employees-absent-late-attendance-report'))
    <title>Employee's Absent and Late Report</title>
@elseif(request()->is('employee-wise-summary-report'))
    <title>Employee's Summary Report</title>
@elseif(request()->is('uw-dashboard'))
    <title>Uw-Dashbaord</title>
@elseif(request()->is('att_reqs'))
    <title>Attendance Managment</title>
@elseif(request()->is('health-dashboard'))
    <title>Health-Dashbaord</title>
@elseif(request()->is('/stlClmOs'))
    <title>G/L Report</title>
@elseif(request()->is('uwRenewalBr'))
    <title>UW-Branches Renewel Report</title>
@else
    <title>@yield('title', 'Default Title')</title>
@endif
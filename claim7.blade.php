@section('title', 'Claims-Dashboard')
@extends('Charts.master')
@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
        }

        body {
            background: #fbf6f6 !important;
        }

        .content-body {
            background: #f3ecec !important;
            min-height: 100vh;
            margin-left: 260px;
            padding: 8px;
        }

        .card-body {
            padding: 5px !important;
            margin: 5px !important;
            transition: all 0.3s ease;
        }

        .card {
            transition: all 0.3s ease;
            border-radius: 8px;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1) !important;
        }

        td {
            margin: 0px !important;
            padding: 2px !important;
        }

        .dept-btn.active {
            background: #4361ee !important;
            color: white !important;
            border-color: #4361ee !important;
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(67, 97, 238, 0.3);
        }

        .toggle-btn {
            background: white;
            border: 1px solid #ccc;
            padding: 3px 10px;
            font-size: 11px;
            border-radius: 4px;
            cursor: pointer;
            color: #000;
            margin-left: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .toggle-btn:hover {
            background: #f8f9fa;
            border-color: #4361ee;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .toggle-btn i {
            transition: transform 0.3s ease;
        }

        .toggle-btn.active i {
            transform: rotate(180deg);
        }

        .dept-btn {
            background: white;
            border: 1px solid #ccc;
            padding: 3px 10px;
            font-size: 11px;
            border-radius: 4px;
            cursor: pointer;
            color: #000;
            transition: all 0.3s ease;
        }

        .dept-btn:hover {
            background: #f8f9fa;
            border-color: #4361ee;
            transform: translateY(-1px);
        }

        .chart-container {
            background: white;
            border-radius: 6px;
            padding: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .premium-main-value {
            font-size: 24px !important;
            font-weight: 700 !important;
            color: #000 !important;
            margin: 5px 0 !important;
        }

        .stats-row {
            display: flex;
            justify-content: space-between;
            margin: 15px 0 !important;
        }

        .stat-item {
            flex: 1;
            text-align: center;
            padding: 0 5px;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 14px;
            font-weight: 600;
            color: #000;
            margin-bottom: 3px;
        }

        .stat-percentage {
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
        }

        .percentage-up   { color: #28a745; }
        .percentage-down { color: #dc3545; }
        .percentage-arrow { font-size: 10px; }

        .table-card {
            border-radius: 8px;
            overflow: hidden;
        }

        .table-header {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            color: #000000;
            font-size: 12px;
        }

        .table-header i {
            margin-right: 8px;
            color: #4361ee;
        }

        .table-container {
            background: white;
            border-radius: 6px;
            padding: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .custom-table {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
        }

        .custom-table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        .custom-table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            color: #000;
            border-bottom: 1px solid #dee2e6;
        }

        .custom-table td {
            padding: 6px;
            border-bottom: 1px solid #f1f3f4;
        }

        .custom-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .text-right { text-align: right; }

        .card-header-icon {
            margin-right: 8px;
            font-size: 14px;
        }

        .claims-link {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .claims-link:hover {
            color: #4361ee;
            text-decoration: underline;
        }

        #myBarChart,
        #myDonutChart {
            max-height: 180px !important;
            width: 100% !important;
        }

        .small-table { font-size: 9px; }

        .small-table th,
        .small-table td {
            padding: 4px !important;
        }

        .os-table-container {
            max-height: 100px;
            overflow-y: auto;
        }

        .table-responsive { overflow-x: auto; }

        .data-card {
            background: white;
            border-radius: 6px;
            padding: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .card-intimation { border-left: 8px solid #FFCF9F; }
        .card-settled    { border-left: 8px solid #9AD0F5; }
        .card-survey     { border-left: 8px solid #9affc7; }
        .card-os         { border-left: 8px solid #ffb0c0; }

        .card-intimation { height: auto !important; }

        .status-badge {
            font-size: 10px;
            font-weight: 600;
            color: #4361ee;
            background: #e8ecff;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
        }

        .card-intimation .card-body,
        .card-settled .card-body,
        .card-survey .card-body,
        .card-os .card-body {
            padding: 8px 12px !important;
            margin: 0 !important;
        }

        .card-intimation .card-body p.mb-3,
        .card-settled .card-body p.mb-2,
        .card-survey .card-body p.mb-2,
        .card-os .card-body p.mb-2 {
            margin-bottom: 6px !important;
        }

        .card-intimation .row.mb-4 {
            margin: 0 0 8px 0 !important;
        }

        .row-gap { margin-top: 4px !important; }

        @media (min-width: 1200px) and (max-width: 1366px) {
            .premium-main-value { font-size: 20px !important; line-height: 1.2 !important; }
            .stats-row          { margin: 8px 0 !important; gap: 2px; }
            .stat-item          { padding: 0 2px !important; min-width: 33.333%; }
            .stat-label         { font-size: 9px !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px !important; }
            .stat-value         { font-size: 11px !important; font-weight: 600 !important; line-height: 1.2 !important; margin-bottom: 1px !important; }
            .stat-percentage    { font-size: 9px !important; white-space: nowrap; overflow: visible; gap: 2px; justify-content: center; }
        }

        @media (max-width: 1199px) and (min-width: 992px) {
            .premium-main-value { font-size: 18px !important; }
            .stat-label         { font-size: 8px !important; }
            .stat-value         { font-size: 10px !important; }
            .stat-percentage    { font-size: 8px !important; }
        }

        @media (max-width: 991px) and (min-width: 768px) {
            .premium-main-value { font-size: 16px !important; }
            .stat-label         { font-size: 7px !important; }
            .stat-value         { font-size: 9px !important; }
            .stat-percentage    { font-size: 7px !important; }
        }

        @media (max-width: 767px) and (min-width: 576px) {
            .premium-main-value { font-size: 15px !important; }
            .stat-label         { font-size: 6px !important; }
            .stat-value         { font-size: 8px !important; }
            .stat-percentage    { font-size: 6px !important; }
        }

        @media (max-width: 575px) {
            .premium-main-value { font-size: 14px !important; }
            .stat-label         { font-size: 5px !important; }
            .stat-value         { font-size: 7px !important; }
            .stat-percentage    { font-size: 5px !important; }
        }

        .card-charts { height: auto !important; }
    </style>

    <div class="content-body">
        <div style="margin: 8px;">
            @php
                $deptNames = [
                    '11' => 'Fire',
                    '12' => 'Marine',
                    '13' => 'Motor',
                    '14' => 'Misc',
                    '16' => 'Health',
                ];

                $currentYearTotal = $apiData['totals']['CURRENT_YEAR_COUNT'] ?? 0;
                $lastYearTotal    = $apiData['totals']['LAST_YEAR_COUNT']    ?? 0;
                $todayTotal       = $apiData['totals']['TODAY_COUNT']        ?? 0;

                $deptLookup = [];
                foreach ($apiData['departments'] ?? [] as $dept) {
                    $code = (string) ($dept['PDP_DEPT_CODE'] ?? '');
                    if ($code) {
                        $deptLookup[$code] = [
                            'current' => $dept['CURRENT_YEAR_COUNT'] ?? 0,
                            'last'    => $dept['LAST_YEAR_COUNT']    ?? 0,
                        ];
                    }
                }

                $monthwiseData          = $apiData['monthwise'] ?? [];
                $currentYearMonthCounts = array_column($monthwiseData, 'CURRENT_YEAR_COUNT', 'MONTH');
                $lastYearMonthCounts    = array_column($monthwiseData, 'LAST_YEAR_COUNT',    'MONTH');

                $totalsData              = $apiStatus['data']['totals'][0] ?? [];
                $currentYearTotalSettled = $totalsData['CURRENT_YEAR_COUNT'] ?? 0;
                $lastYearTotalSettled    = $totalsData['LAST_YEAR_COUNT']    ?? 0;

                $deptLookupSettled = [];
                foreach ($apiStatus['data']['departments'] ?? [] as $dept) {
                    $code = (string) ($dept['PDP_DEPT_CODE'] ?? '');
                    if ($code) {
                        $deptLookupSettled[$code] = [
                            'current' => $dept['CURRENT_YEAR_COUNT'] ?? 0,
                            'last'    => $dept['LAST_YEAR_COUNT']    ?? 0,
                        ];
                    }
                }

                $settledMonthwiseData          = $apiStatus['data']['monthwise'] ?? [];
                $settledCurrentYearMonthCounts = array_column($settledMonthwiseData, 'CURRENT_YEAR_COUNT', 'MONTH');
                $settledLastYearMonthCounts    = array_column($settledMonthwiseData, 'LAST_YEAR_COUNT',    'MONTH');

                $surveyData   = $apiData['Surv'][0]     ?? ['GPD_PAYEE_AMOUNT' => 0, 'GPD_PAYEE_COUNT' => 0];
                $workshopData = $apiData['Workshop'][0] ?? ['GPD_PAYEE_AMOUNT' => 0, 'GPD_PAYEE_COUNT' => 0];

                $intimationChangePercent = $lastYearTotal > 0 ? (($currentYearTotal - $lastYearTotal) / $lastYearTotal) * 100 : 0;
            @endphp

            <div class="row g-2">
                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                    <div class="card overflow-hidden card-intimation" style="height:auto !important;">
                        <div class="card-body" style="padding: 12px 15px !important; margin:0 !important;">
                            <p class="mb-2" style="font-size:14px; font-weight:600; color:#000;">
                                <i class="fas fa-bell card-header-icon" style="color:#FFCF9F;"></i>
                                Intimation
                            </p>

                            <div class="row" style="margin:0 0 10px 0;">
                                <div class="col-4" style="text-align:center;">
                                    <p style="margin:0 0 3px 0; font-size:11px; color:#666;">
                                        Current Year
                                        <span style="cursor:help;" title="{{ date('Y-01-01') }} to {{ date('Y-m-d') }}">
                                            <i class="fas fa-info-circle" style="font-size:9px; color:#999;"></i>
                                        </span>
                                    </p>
                                    <div style="display:inline-flex; align-items:baseline; justify-content:center; gap:4px; white-space:nowrap;">
                                        <a href="{{ url('/claimInt?start_date='.date('Y-01-01').'&end_date='.date('Y-m-d').'&insurance_type=D,I') }}"
                                           target="_blank" class="claims-link"
                                           style="font-size:22px; font-weight:700; color:#117863; text-decoration:underline; text-decoration-style:dotted; text-underline-offset:4px;">
                                            {{ number_format($currentYearTotal) }}
                                        </a>
                                        @if($currentYearTotal > $lastYearTotal)
                                            <span style="color:#28a745; font-size:11px; font-weight:600;">↑{{ number_format(abs($intimationChangePercent),1) }}%</span>
                                        @elseif($currentYearTotal < $lastYearTotal)
                                            <span style="color:#dc3545; font-size:11px; font-weight:600;">↓{{ number_format(abs($intimationChangePercent),1) }}%</span>
                                        @else
                                            <span style="color:#6c757d; font-size:11px; font-weight:600;">=</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-4" style="text-align:center;">
                                    <p style="margin:0 0 3px 0; font-size:11px; color:#666;">
                                        Last Year
                                        <span style="cursor:help;" title="{{ date('Y-01-01',strtotime('-1 year')) }} to {{ date('Y-m-d',strtotime('-1 year')) }}">
                                            <i class="fas fa-info-circle" style="font-size:9px; color:#999;"></i>
                                        </span>
                                    </p>
                                    <a href="{{ url('/claimInt?start_date='.date('Y-01-01',strtotime('-1 year')).'&end_date='.date('Y-m-d',strtotime('-1 year')).'&insurance_type=D,I') }}"
                                       target="_blank" class="claims-link"
                                       style="font-size:22px; font-weight:700; color:#4682B4; text-decoration:underline; text-decoration-style:dotted; text-underline-offset:4px;">
                                        {{ number_format($lastYearTotal) }}
                                    </a>
                                </div>
                                <div class="col-4" style="text-align:center;">
                                    <p style="margin:0 0 3px 0; font-size:11px; color:#666;">
                                        Today
                                        <span style="cursor:help;" title="{{ date('Y-m-d') }}">
                                            <i class="fas fa-info-circle" style="font-size:9px; color:#999;"></i>
                                        </span>
                                    </p>
                                    <a href="{{ url('/claimInt?start_date='.date('Y-m-d').'&end_date='.date('Y-m-d').'&insurance_type=D,I') }}"
                                       target="_blank" class="claims-link"
                                       style="font-size:22px; font-weight:700; color:#283747; text-decoration:underline; text-decoration-style:dotted; text-underline-offset:4px;">
                                        {{ number_format($todayTotal ?? 0) }}
                                    </a>
                                </div>
                            </div>

                            <div style="margin-top:6px;">
                                <table style="width:100%; font-size:10px; border-collapse:collapse; table-layout:fixed;">
                                    <colgroup>
                                        <col style="width:22px;">
                                        @foreach ($deptNames as $code => $name)
                                            <col style="width:13%;">
                                            <col style="width:6%;">
                                        @endforeach
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            @foreach ($deptNames as $code => $name)
                                                <th colspan="2" style="text-align:center; padding:3px 0; font-weight:600; font-size:10px;">{{ $name }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="font-weight:600; color:#117863; padding:3px 2px; white-space:nowrap;">CY:</td>
                                            @foreach (array_keys($deptNames) as $code)
                                                @php
                                                    $deptName      = $deptNames[$code];
                                                    $currentCount  = $deptLookup[$code]['current'] ?? 0;
                                                    $lastCount     = $deptLookup[$code]['last']    ?? 0;
                                                    $changePercent = $lastCount > 0 ? (($currentCount - $lastCount) / $lastCount) * 100 : 0;
                                                    $isHigher      = $currentCount > $lastCount;
                                                    $isLower       = $currentCount < $lastCount;
                                                    $arrow         = $isHigher ? '↑' : ($isLower ? '↓' : '=');
                                                    $arrowColor    = $isHigher ? '#28a745' : ($isLower ? '#dc3545' : '#6c757d');
                                                @endphp
                                                <td style="text-align:right; padding:3px 1px 3px 0; white-space:nowrap;">
                                                    @if($currentCount > 0)
                                                        <a href="{{ url('/claimInt?start_date='.date('Y-01-01').'&end_date='.date('Y-m-d').'&new_category='.urlencode($deptName).'&insurance_type=D,I') }}"
                                                           target="_blank" class="claims-link"
                                                           style="color:#117863; font-weight:600; font-size:10px; text-decoration:underline; text-decoration-style:dotted; text-underline-offset:2px;">{{ number_format($currentCount) }}</a>
                                                    @else
                                                        <span style="color:#117863; font-weight:600; font-size:10px;">0</span>
                                                    @endif
                                                </td>
                                                <td style="text-align:left; padding:3px 0 3px 1px; white-space:nowrap;">
                                                    @if($lastCount > 0 && $currentCount != 0)
                                                        <span style="color:{{ $arrowColor }}; font-size:8px; font-weight:600;">{{ $arrow }}{{ number_format(abs($changePercent),0) }}%</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td style="font-weight:600; color:#4682B4; padding:3px 2px; white-space:nowrap;">LY:</td>
                                            @foreach (array_keys($deptNames) as $code)
                                                @php
                                                    $deptName = $deptNames[$code];
                                                    $count    = $deptLookup[$code]['last'] ?? 0;
                                                @endphp
                                                <td style="text-align:right; padding:3px 1px 3px 0; white-space:nowrap;">
                                                    @if($count > 0)
                                                        <a href="{{ url('/claimInt?start_date='.date('Y-01-01',strtotime('-1 year')).'&end_date='.date('Y-m-d',strtotime('-1 year')).'&new_category='.urlencode($deptName).'&insurance_type=D,I') }}"
                                                           target="_blank" class="claims-link"
                                                           style="color:#4682B4; font-weight:500; font-size:10px; text-decoration:underline; text-decoration-style:dotted; text-underline-offset:2px;">{{ number_format($count) }}</a>
                                                    @else
                                                        <span style="color:#4682B4; font-size:10px;">{{ number_format($count) }}</span>
                                                    @endif
                                                </td>
                                                <td></td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                                <div style="text-align:right; margin-top:4px;">
                                    <span style="font-size:8px; color:#999;">
                                        <i class="fas fa-mouse-pointer"></i> Click on numbers to view department details
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                    <div class="card overflow-hidden card-settled" style="height:auto !important;">
                        <div class="card-body" style="padding: 12px 15px !important; margin:0 !important;">
                            <p class="mb-2" style="font-size:14px; font-weight:600; color:#000;">
                                <i class="fas fa-check-circle card-header-icon" style="color:#9AD0F5;"></i>
                                Settled
                            </p>

                            <div class="row" style="margin:0 0 10px 0;">
                                <div class="col-4" style="text-align:center;">
                                    <p style="margin:0 0 3px 0; font-size:11px; color:#666;">Current Year</p>
                                    <div style="display:inline-flex; align-items:baseline; justify-content:center; gap:4px; white-space:nowrap;">
                                        @php
                                            $settledChangePercent = $lastYearTotalSettled > 0
                                                ? (($currentYearTotalSettled - $lastYearTotalSettled) / $lastYearTotalSettled) * 100
                                                : 0;
                                        @endphp
                                        <a href="{{ url('#') }}" target="_blank" class="claims-link"
                                           style="font-size:22px; font-weight:700; color:#117863; text-decoration:underline; text-decoration-style:dotted; text-underline-offset:4px;">
                                            {{ number_format($currentYearTotalSettled) }}
                                        </a>
                                        @if($currentYearTotalSettled > $lastYearTotalSettled)
                                            <span style="color:#28a745; font-size:11px; font-weight:600;">↑{{ number_format(abs($settledChangePercent),1) }}%</span>
                                        @elseif($currentYearTotalSettled < $lastYearTotalSettled)
                                            <span style="color:#dc3545; font-size:11px; font-weight:600;">↓{{ number_format(abs($settledChangePercent),1) }}%</span>
                                        @else
                                            <span style="color:#6c757d; font-size:11px; font-weight:600;">=</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-4" style="text-align:center;">
                                    <p style="margin:0 0 3px 0; font-size:11px; color:#666;">Last Year</p>
                                    <span style="font-size:22px; font-weight:700; color:#4682B4;">
                                        {{ number_format($lastYearTotalSettled) }}
                                    </span>
                                </div>
                                <div class="col-4" style="text-align:center;">
                                    <p style="margin:0 0 3px 0; font-size:11px; color:#666;">Today</p>
                                    <span style="font-size:22px; font-weight:700; color:#283747;">
                                        {{ number_format($totalsData['TODAY_COUNT'] ?? 0) }}
                                    </span>
                                </div>
                            </div>

                            <div style="margin-top:6px;">
                                <table style="width:100%; font-size:10px; border-collapse:collapse; table-layout:fixed;">
                                    <colgroup>
                                        <col style="width:22px;">
                                        @foreach ($deptNames as $code => $name)
                                            <col style="width:13%;">
                                            <col style="width:6%;">
                                        @endforeach
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            @foreach ($deptNames as $code => $name)
                                                <th colspan="2" style="text-align:center; padding:3px 0; font-weight:600; font-size:10px;">{{ $name }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="font-weight:600; color:#117863; padding:3px 2px; white-space:nowrap;">CY:</td>
                                            @foreach (array_keys($deptNames) as $code)
                                                @php
                                                    $currentCount  = $deptLookupSettled[$code]['current'] ?? 0;
                                                    $lastCount     = $deptLookupSettled[$code]['last']    ?? 0;
                                                    $changePercent = $lastCount > 0 ? (($currentCount - $lastCount) / $lastCount) * 100 : 0;
                                                    $isHigher      = $currentCount > $lastCount;
                                                    $isLower       = $currentCount < $lastCount;
                                                    $arrow         = $isHigher ? '↑' : ($isLower ? '↓' : '=');
                                                    $arrowColor    = $isHigher ? '#28a745' : ($isLower ? '#dc3545' : '#6c757d');
                                                @endphp
                                                <td style="text-align:right; padding:3px 1px 3px 0; white-space:nowrap;">
                                                    <span style="color:#117863; font-weight:600; font-size:10px;">{{ number_format($currentCount) }}</span>
                                                </td>
                                                <td style="text-align:left; padding:3px 0 3px 1px; white-space:nowrap;">
                                                    @if($lastCount > 0 && $currentCount != 0)
                                                        <span style="color:{{ $arrowColor }}; font-size:8px; font-weight:600;">{{ $arrow }}{{ number_format(abs($changePercent),0) }}%</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td style="font-weight:600; color:#4682B4; padding:3px 2px; white-space:nowrap;">LY:</td>
                                            @foreach (array_keys($deptNames) as $code)
                                                <td style="text-align:right; padding:3px 1px 3px 0; white-space:nowrap;">
                                                    <span style="color:#4682B4; font-size:10px;">{{ number_format($deptLookupSettled[$code]['last'] ?? 0) }}</span>
                                                </td>
                                                <td></td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                                <div style="text-align:right; margin-top:4px;">
                                    <span style="font-size:8px; color:#999;">
                                        <i class="fas fa-mouse-pointer"></i> Dept-wise settled counts
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                    <div class="card overflow-hidden card-survey" style="height:auto !important;">
                        <div class="card-body" style="padding: 12px 15px !important; margin:0 !important;">
                            <p class="mb-2" style="font-size:14px; font-weight:600; color:#000;">
                                <i class="fas fa-clipboard-list card-header-icon" style="color:#9affc7;"></i>
                                Survey & Workshop
                            </p>

                            <div class="row" style="margin:0 0 10px 0;">
                                <div class="col-6" style="text-align:center;">
                                    <p style="margin:0 0 3px 0; font-size:11px; color:#666;">Surv Amt</p>
                                    <a href="{{ url('/getSurvReport') }}" target="_blank" class="claims-link"
                                       style="font-size:20px; font-weight:700; color:#117863; text-decoration:underline; text-decoration-style:dotted; text-underline-offset:4px; white-space:nowrap;">
                                        {{ number_format($surveyData['GPD_PAYEE_AMOUNT']) }}
                                    </a>
                                </div>
                                <div class="col-6" style="text-align:center;">
                                    <p style="margin:0 0 3px 0; font-size:11px; color:#666;">WS Amt</p>
                                    <a href="{{ url('/getWorkShopReport') }}" target="_blank" class="claims-link"
                                       style="font-size:20px; font-weight:700; color:#4682B4; text-decoration:underline; text-decoration-style:dotted; text-underline-offset:4px; white-space:nowrap;">
                                        {{ number_format($workshopData['GPD_PAYEE_AMOUNT']) }}
                                    </a>
                                </div>
                            </div>

                            <div style="margin-top:6px;">
                                <table style="width:100%; font-size:10px; border-collapse:collapse;">
                                    <thead>
                                        <tr>
                                            <th style="padding:3px 2px; font-weight:600; font-size:10px; text-align:left;"></th>
                                            <th style="text-align:right; padding:3px 4px; font-weight:600; font-size:10px;">Amount</th>
                                            <th style="text-align:right; padding:3px 4px; font-weight:600; font-size:10px;">Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="padding:3px 2px; white-space:nowrap;">
                                                <i class="fas fa-search" style="font-size:8px; margin-right:3px; color:#666;"></i>
                                                <span style="font-weight:600; color:#117863;">Surveyor</span>
                                            </td>
                                            <td style="text-align:right; padding:3px 4px; font-size:10px; font-weight:600; color:#117863;">
                                                {{ number_format($surveyData['GPD_PAYEE_AMOUNT']) }}
                                            </td>
                                            <td style="text-align:right; padding:3px 4px; font-size:10px;">
                                                {{ number_format($surveyData['GPD_PAYEE_COUNT']) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 2px; white-space:nowrap;">
                                                <i class="fas fa-tools" style="font-size:8px; margin-right:3px; color:#666;"></i>
                                                <span style="font-weight:600; color:#4682B4;">Workshop</span>
                                            </td>
                                            <td style="text-align:right; padding:3px 4px; font-size:10px; font-weight:600; color:#4682B4;">
                                                {{ number_format($workshopData['GPD_PAYEE_AMOUNT']) }}
                                            </td>
                                            <td style="text-align:right; padding:3px 4px; font-size:10px;">
                                                {{ number_format($workshopData['GPD_PAYEE_COUNT']) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div style="text-align:right; margin-top:4px;">
                                    <span style="font-size:8px; color:#999;">
                                        <i class="fas fa-mouse-pointer"></i> Click amounts to view reports
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                    <div class="card overflow-hidden card-os" style="height:auto !important;">
                        <div class="card-body" style="padding: 12px 15px !important; margin:0 !important;">
                            <p class="mb-2" style="font-size:14px; font-weight:600; color:#000;">
                                <i class="fas fa-clock card-header-icon" style="color:#ffb0c0;"></i>
                                Total OS
                            </p>

                            @php
                                $osDays = ['0-7 days','8-15 days','16-30 days','31-60 days','61-90 days','90+ days'];
                                $osTotals = [];
                                foreach (['Surveyor', 'Report', 'Stl'] as $type) {
                                    $t = 0;
                                    foreach ($osDays as $d) { $t += $combinedData[$type][$d] ?? 0; }
                                    $osTotals[$type] = $t;
                                }
                            @endphp

                            <div class="row" style="margin:0 0 10px 0;">
                                @foreach (['Surveyor' => '#117863', 'Report' => '#4682B4', 'Stl' => '#283747'] as $type => $color)
                                    <div class="col-4" style="text-align:center;">
                                        <p style="margin:0 0 3px 0; font-size:11px; color:#666;">{{ $type }}</p>
                                        <span style="font-size:22px; font-weight:700; color:{{ $color }}; white-space:nowrap;">
                                            {{ number_format($osTotals[$type]) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <div style="margin-top:4px;">
                                <table style="width:100%; font-size:8px; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#f8f9fa;">
                                            <th style="padding:1px 2px; font-weight:600; text-align:left;">Type</th>
                                            <th style="padding:1px 1px; font-weight:600; text-align:right;">0-7</th>
                                            <th style="padding:1px 1px; font-weight:600; text-align:right;">8-15</th>
                                            <th style="padding:1px 1px; font-weight:600; text-align:right;">16-30</th>
                                            <th style="padding:1px 1px; font-weight:600; text-align:right;">31-60</th>
                                            <th style="padding:1px 1px; font-weight:600; text-align:right;">61-90</th>
                                            <th style="padding:1px 1px; font-weight:600; text-align:right;">90+</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (['Surveyor', 'Report', 'Stl'] as $type)
                                            <tr>
                                                <td style="padding:1px 2px;">{{ $type }}</td>
                                                @foreach ($osDays as $d)
                                                    <td style="padding:2px; text-align:right;">{{ number_format($combinedData[$type][$d] ?? 0) }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div style="text-align:right; margin-top:2px;">
                                    <span style="font-size:8px; color:#999;">
                                        <i class="fas fa-clock"></i> Outstanding by aging bucket
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-2" style="margin-top:6px;">
                <div class="col-xl-3 col-lg-6 col-md-12">
                    <div class="card overflow-hidden card-charts">
                        <div class="card-body px-4 py-4">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <div class="table-header">
                                    <i class="fas fa-chart-bar"></i>
                                    <b>Month-wise Claims</b>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span id="monthwise-current-view" class="status-badge">Intimated</span>
                                    <button class="toggle-btn" id="toggle-monthwise-view" style="margin-left:0;">
                                        <i class="fas fa-exchange-alt"></i> Toggle
                                    </button>
                                </div>
                            </div>
                            <div class="chart-point chart-container" style="height:150px;">
                                <canvas id="myBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-12">
                    <div class="card overflow-hidden card-charts">
                        <div class="card-body px-4 py-4">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <div class="table-header">
                                    <i class="fas fa-chart-pie"></i>
                                    <b>Dept-wise Distribution</b>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span id="deptwise-current-view" class="status-badge">Intimated</span>
                                    <button class="toggle-btn" id="toggle-deptwise-view" style="margin-left:0;">
                                        <i class="fas fa-exchange-alt"></i> Toggle
                                    </button>
                                </div>
                            </div>
                            <div class="chart-point chart-container" style="height:150px;">
                                <canvas id="myDonutChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-12">
                    <div class="card overflow-hidden card-charts">
                        <div class="card-body px-4 py-4">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <div class="table-header">
                                    <i class="fas fa-chart-line"></i>
                                    <b>Chart 3</b>
                                </div>
                            </div>
                            <div class="chart-container" style="height:150px; display:flex; align-items:center; justify-content:center; background:#f8f9fa; border:2px dashed #dee2e6; border-radius:6px;">
                                <div style="text-align:center; color:#adb5bd;">
                                    <span style="font-size:11px;">Chart coming soon</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-12">
                    <div class="card overflow-hidden card-charts">
                        <div class="card-body px-4 py-4">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <div class="table-header">
                                    <i class="fas fa-chart-area"></i>
                                    <b>Chart 4</b>
                                </div>
                            </div>
                            <div class="chart-container" style="height:150px; display:flex; align-items:center; justify-content:center; background:#f8f9fa; border:2px dashed #dee2e6; border-radius:6px;">
                                <div style="text-align:center; color:#adb5bd;">
                                    <span style="font-size:11px;">Chart coming soon</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-2" style="margin-top:6px;">
                <div class="col-xl-4 col-lg-6 col-md-12 mb-2">
                    <div class="card table-card">
                        <div class="card-body">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <div class="table-header">
                                    <i class="fas fa-history"></i>
                                    <b>Last Ten Claims</b>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span id="lastten-current-view" class="status-badge">Settled</span>
                                    <button class="toggle-btn" id="toggle-lastten-view" style="margin-left:0;">
                                        <i class="fas fa-exchange-alt"></i> Toggle
                                    </button>
                                </div>
                            </div>

                            <div class="table-container" id="lastten-settled" style="display:block;">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>DATE</th><th>Claim No</th><th>Insured</th><th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($apiStatus['data']['LAST10'] ?? [] as $index => $claim)
                                            @if($index < 5)
                                                <tr>
                                                    <td>{{ $claim['GSH_SETTLEMENTDATE'] ?? '' }}</td>
                                                    <td>{{ $claim['GSH_DOC_REF_NO'] ?? '' }}</td>
                                                    <td style="max-width:80px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $claim['PPS_DESC'] ?? '' }}">
                                                        {{ \Illuminate\Support\Str::limit($claim['PPS_DESC'] ?? '', 10) }}
                                                    </td>
                                                    <td class="text-right">{{ isset($claim['GSH_LOSSCLAIMED']) ? number_format($claim['GSH_LOSSCLAIMED']) : '' }}</td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr><td colspan="4" class="text-center">No data available</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-container" id="lastten-intimated" style="display:none;">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>DATE</th><th>Claim No</th><th>Insured</th><th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($apiData['LAST10'] ?? [] as $index => $claim)
                                            @if($index < 5)
                                                <tr>
                                                    <td>{{ $claim['GIH_INTIMATIONDATE'] ?? '' }}</td>
                                                    <td>{{ $claim['GIH_DOC_REF_NO'] ?? '' }}</td>
                                                    <td style="max-width:80px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $claim['PPS_DESC'] ?? '' }}">
                                                        {{ \Illuminate\Support\Str::limit($claim['PPS_DESC'] ?? '', 10) }}
                                                    </td>
                                                    <td class="text-right">{{ isset($claim['GIH_LOSSCLAIMED']) ? number_format($claim['GIH_LOSSCLAIMED']) : '' }}</td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr><td colspan="4" class="text-center">No data available</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div style="text-align:right; margin-top:10px;">
                                <a href="{{ url('/cr4') }}" target="_blank" class="toggle-btn" style="text-decoration:none; display:inline-flex; align-items:center;">
                                    See More <i class="fas fa-arrow-right" style="margin-left:5px; font-size:10px;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-6 col-md-12 mb-2">
                    <div class="card table-card">
                        <div class="card-body">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <div class="table-header">
                                    <i class="fas fa-trophy"></i>
                                    <b>Top Ten Amt Wise</b>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span id="topten-current-view" class="status-badge">Settled</span>
                                    <button class="toggle-btn" id="toggle-topten-view" style="margin-left:0;">
                                        <i class="fas fa-exchange-alt"></i> Toggle
                                    </button>
                                </div>
                            </div>

                            <div class="table-container" id="topten-settled" style="display:block;">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>DATE</th><th>Claim No</th><th>Insured</th><th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($apiStatus['data']['AMT10'] ?? [] as $index => $claim)
                                            @if($index < 5)
                                                <tr>
                                                    <td>{{ $claim['GSH_SETTLEMENTDATE'] ?? '' }}</td>
                                                    <td>{{ $claim['GSH_DOC_REF_NO'] ?? '' }}</td>
                                                    <td style="max-width:80px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $claim['PPS_DESC'] ?? '' }}">
                                                        {{ \Illuminate\Support\Str::limit($claim['PPS_DESC'] ?? '', 10) }}
                                                    </td>
                                                    <td class="text-right">{{ isset($claim['GSH_LOSSCLAIMED']) ? number_format($claim['GSH_LOSSCLAIMED']) : '' }}</td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr><td colspan="4" class="text-center">No data available</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-container" id="topten-intimated" style="display:none;">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>DATE</th><th>Claim No</th><th>Insured</th><th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($apiData['AMT10'] ?? [] as $index => $claim)
                                            @if($index < 5)
                                                <tr>
                                                    <td>{{ $claim['GIH_INTIMATIONDATE'] ?? '' }}</td>
                                                    <td>{{ $claim['GIH_DOC_REF_NO'] ?? '' }}</td>
                                                    <td style="max-width:80px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $claim['PPS_DESC'] ?? '' }}">
                                                        {{ \Illuminate\Support\Str::limit($claim['PPS_DESC'] ?? '', 10) }}
                                                    </td>
                                                    <td class="text-right">{{ isset($claim['GIH_LOSSCLAIMED']) ? number_format($claim['GIH_LOSSCLAIMED']) : '' }}</td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr><td colspan="4" class="text-center">No data available</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div style="text-align:right; margin-top:10px;">
                                <a href="{{ url('/cr9') }}" target="_blank" class="toggle-btn" style="text-decoration:none; display:inline-flex; align-items:center;">
                                    See More <i class="fas fa-arrow-right" style="margin-left:5px; font-size:10px;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-6 col-md-12 mb-2">
                    <div class="card table-card">
                        <div class="card-body">
                            <div class="table-header">
                                <i class="fas fa-building"></i>
                                <b>Branch Wise Settled</b>
                            </div>
                            <div class="table-container" style="max-height:200px; overflow-y:auto;">
                                <table class="custom-table small-table">
                                    <thead>
                                        <tr>
                                            <th>Branch</th>
                                            <th class="text-right">Count</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($apiBIStatus['data']['branch'] ?? [] as $branch)
                                            <tr>
                                                <td title="{{ $branch['PLC_DESC'] ?? '' }}">{{ \Illuminate\Support\Str::limit($branch['PLC_DESC'] ?? 'N/A', 8) }}</td>
                                                <td class="text-right">{{ number_format($branch['INTI_DOCS'] ?? 0) }}</td>
                                                <td class="text-right">{{ number_format($branch['TOT'] ?? 0) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center">No data</td></tr>
                                        @endforelse
                                    </tbody>
                                    @php
                                        $totalInti  = collect($apiBIStatus['data']['branch'] ?? [])->sum('INTI_DOCS');
                                        $tot_amt_br = collect($apiBIStatus['data']['branch'] ?? [])->sum('TOT');
                                    @endphp
                                    <tfoot style="background:#f8f9fa; font-weight:600;">
                                        <tr>
                                            <td>Total</td>
                                            <td class="text-right">{{ number_format($totalInti) }}</td>
                                            <td class="text-right">{{ number_format($tot_amt_br) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-6 col-md-12 mb-2">
                    <div class="card table-card">
                        <div class="card-body">
                            <div class="table-header">
                                <i class="fas fa-user-shield"></i>
                                <b>Insured Wise Settled</b>
                            </div>
                            <div class="table-container" style="max-height:200px; overflow-y:auto;">
                                <table class="custom-table small-table">
                                    <thead>
                                        <tr>
                                            <th>Insured</th>
                                            <th class="text-right">Count</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($apiBIStatus['data']['insured'] ?? [] as $insured)
                                            <tr>
                                                <td title="{{ $insured['PPS_DESC'] ?? '' }}">{{ \Illuminate\Support\Str::limit($insured['PPS_DESC'] ?? '', 8) }}</td>
                                                <td class="text-right">{{ number_format($insured['CURRENT_YEAR_COUNT'] ?? 0) }}</td>
                                                <td class="text-right">{{ number_format($insured['TOT'] ?? 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @php
                                        $totalIntiInsured = collect($apiBIStatus['data']['insured'] ?? [])->sum('CURRENT_YEAR_COUNT');
                                        $tot_amt          = collect($apiBIStatus['data']['insured'] ?? [])->sum('TOT');
                                    @endphp
                                    <tfoot style="background:#f8f9fa; font-weight:600;">
                                        <tr>
                                            <td>Total</td>
                                            <td class="text-right">{{ number_format($totalIntiInsured) }}</td>
                                            <td class="text-right">{{ number_format($tot_amt) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function () {
            const barChartData = {
                intimation: {
                    current: [{{ $currentYearMonthCounts['JAN']??0 }},{{ $currentYearMonthCounts['FEB']??0 }},{{ $currentYearMonthCounts['MAR']??0 }},{{ $currentYearMonthCounts['APR']??0 }},{{ $currentYearMonthCounts['MAY']??0 }},{{ $currentYearMonthCounts['JUN']??0 }},{{ $currentYearMonthCounts['JUL']??0 }},{{ $currentYearMonthCounts['AUG']??0 }},{{ $currentYearMonthCounts['SEP']??0 }}],
                    last:    [{{ $lastYearMonthCounts['JAN']??0 }},{{ $lastYearMonthCounts['FEB']??0 }},{{ $lastYearMonthCounts['MAR']??0 }},{{ $lastYearMonthCounts['APR']??0 }},{{ $lastYearMonthCounts['MAY']??0 }},{{ $lastYearMonthCounts['JUN']??0 }},{{ $lastYearMonthCounts['JUL']??0 }},{{ $lastYearMonthCounts['AUG']??0 }},{{ $lastYearMonthCounts['SEP']??0 }}]
                },
                settled: {
                    current: [{{ $settledCurrentYearMonthCounts['JAN']??0 }},{{ $settledCurrentYearMonthCounts['FEB']??0 }},{{ $settledCurrentYearMonthCounts['MAR']??0 }},{{ $settledCurrentYearMonthCounts['APR']??0 }},{{ $settledCurrentYearMonthCounts['MAY']??0 }},{{ $settledCurrentYearMonthCounts['JUN']??0 }},{{ $settledCurrentYearMonthCounts['JUL']??0 }},{{ $settledCurrentYearMonthCounts['AUG']??0 }},{{ $settledCurrentYearMonthCounts['SEP']??0 }}],
                    last:    [{{ $settledLastYearMonthCounts['JAN']??0 }},{{ $settledLastYearMonthCounts['FEB']??0 }},{{ $settledLastYearMonthCounts['MAR']??0 }},{{ $settledLastYearMonthCounts['APR']??0 }},{{ $settledLastYearMonthCounts['MAY']??0 }},{{ $settledLastYearMonthCounts['JUN']??0 }},{{ $settledLastYearMonthCounts['JUL']??0 }},{{ $settledLastYearMonthCounts['AUG']??0 }},{{ $settledLastYearMonthCounts['SEP']??0 }}]
                }
            };

            const donutChartData = {
                intimation: [{{ $deptLookup['11']['current']??0 }},{{ $deptLookup['12']['current']??0 }},{{ $deptLookup['13']['current']??0 }},{{ $deptLookup['14']['current']??0 }},{{ $deptLookup['16']['current']??0 }}],
                settled:    [{{ $deptLookupSettled['11']['current']??0 }},{{ $deptLookupSettled['12']['current']??0 }},{{ $deptLookupSettled['13']['current']??0 }},{{ $deptLookupSettled['14']['current']??0 }},{{ $deptLookupSettled['16']['current']??0 }}]
            };

            const barCtx = document.getElementById('myBarChart').getContext('2d');
            let currentBarDataType = 'intimation';
            const myBarChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep'],
                    datasets: [
                        { label: 'Current Year', data: barChartData.intimation.current, backgroundColor: '#36A2EB', borderRadius: 4 },
                        { label: 'Last Year',    data: barChartData.intimation.last,    backgroundColor: '#FF6384', borderRadius: 4 }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    barPercentage: 0.7,
                    categoryPercentage: 0.9,
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { size: 9 }, maxTicksLimit: 5 } },
                        x: { ticks: { font: { size: 9 } } }
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 9 }, usePointStyle: true } }
                    }
                }
            });

            const donutCtx = document.getElementById('myDonutChart').getContext('2d');
            let currentDonutDataType = 'intimation';
            const myDonutChart = new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Fire','Marine','Motor','Misc','Health'],
                    datasets: [{
                        label: 'Current Year Claims',
                        data: donutChartData.intimation,
                        backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF'],
                        borderColor: '#fff',
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    layout: { padding: 5 },
                    plugins: {
                        legend: { display: true, position: 'bottom', labels: { font: { family: 'Inter', size: 9 }, usePointStyle: true, padding: 8 } },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct   = ((context.parsed / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.parsed} (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });

            $('#toggle-monthwise-view').click(function () {
                $(this).toggleClass('active');
                if (currentBarDataType === 'intimation') {
                    myBarChart.data.datasets[0].data = barChartData.settled.current;
                    myBarChart.data.datasets[1].data = barChartData.settled.last;
                    currentBarDataType = 'settled';
                    $('#monthwise-current-view').text('Settled');
                } else {
                    myBarChart.data.datasets[0].data = barChartData.intimation.current;
                    myBarChart.data.datasets[1].data = barChartData.intimation.last;
                    currentBarDataType = 'intimation';
                    $('#monthwise-current-view').text('Intimated');
                }
                myBarChart.update();
            });

            $('#toggle-deptwise-view').click(function () {
                $(this).toggleClass('active');
                if (currentDonutDataType === 'intimation') {
                    myDonutChart.data.datasets[0].data = donutChartData.settled;
                    currentDonutDataType = 'settled';
                    $('#deptwise-current-view').text('Settled');
                } else {
                    myDonutChart.data.datasets[0].data = donutChartData.intimation;
                    currentDonutDataType = 'intimation';
                    $('#deptwise-current-view').text('Intimated');
                }
                myDonutChart.update();
            });

            $('#toggle-lastten-view').click(function () {
                $(this).toggleClass('active');
                $('#lastten-settled, #lastten-intimated').toggle();
                $('#lastten-current-view').text($('#lastten-settled').is(':visible') ? 'Settled' : 'Intimated');
            });

            $('#toggle-topten-view').click(function () {
                $(this).toggleClass('active');
                $('#topten-settled, #topten-intimated').toggle();
                $('#topten-current-view').text($('#topten-settled').is(':visible') ? 'Settled' : 'Intimated');
            });

            let resizeTimeout;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function () {
                    myBarChart.update();
                    myDonutChart.update();
                }, 250);
            });
        });
    </script>
@endsection
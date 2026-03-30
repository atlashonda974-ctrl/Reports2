@section('title', 'UnderWriting-Dashboard') 
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
        }

        .card-body {
            padding: 5px !important;
            margin: 5px !important;
            transition: all 0.3s ease;
        }

        .card {
            transition: all 0.3s ease;
            border-radius: 8px;
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

        .percentage-up {
            color: #28a745;
        }

        .percentage-down {
            color: #dc3545;
        }

        .percentage-arrow {
            font-size: 10px;
        }

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

        .text-right {
            text-align: right;
        }

        .card-header-icon {
            margin-right: 8px;
            font-size: 14px;
        }


        /* *******************************************
         * RESPONSIVENESS SETTINGS START FROM HERE *
         */

     
        @media (min-width: 1200px) and (max-width: 1366px) {

          
            .premium-main-value {
                font-size: 20px !important;
                line-height: 1.2 !important;
            }

            .stats-row {
                margin: 8px 0 !important;
                gap: 2px;
            }

            .stat-item {
                padding: 0 2px !important;
                min-width: 33.333%;
            }

            .stat-label {
                font-size: 9px !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                margin-bottom: 2px !important;
            }

            .stat-value {
                font-size: 11px !important;
                font-weight: 600 !important;
                line-height: 1.2 !important;
                margin-bottom: 1px !important;
            }

            .stat-percentage {
                font-size: 9px !important;
                white-space: nowrap;
                overflow: visible;
                gap: 2px;
                justify-content: center;
            }

            .percentage-arrow {
                font-size: 8px !important;
            }
        }

    
        @media (max-width: 1199px) and (min-width: 992px) {
            .premium-main-value {
                font-size: 18px !important;
                line-height: 1.2 !important;
            }

            .stat-label {
                font-size: 8px !important;
                white-space: nowrap;
            }

            .stat-value {
                font-size: 10px !important;
            }

            .stat-percentage {
                font-size: 8px !important;
                white-space: nowrap;
            }

            .stats-row {
                margin: 6px 0 !important;
                gap: 1px;
            }
        }

     
        @media (max-width: 991px) and (min-width: 768px) {
            .premium-main-value {
                font-size: 16px !important;
                line-height: 1.2 !important;
            }

            .stats-row {
                margin: 6px 0 !important;
                gap: 0;
            }

            .stat-item {
                padding: 0 1px !important;
            }

            .stat-label {
                font-size: 7px !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .stat-value {
                font-size: 9px !important;
                line-height: 1.1 !important;
            }

            .stat-percentage {
                font-size: 7px !important;
                white-space: nowrap;
                overflow: visible;
                line-height: 1;
            }

            .percentage-arrow {
                font-size: 6px !important;
            }
        }

       
        @media (max-width: 767px) and (min-width: 576px) {
            .premium-main-value {
                font-size: 15px !important;
                line-height: 1.2 !important;
                margin: 3px 0 !important;
            }

            .stats-row {
                margin: 5px 0 !important;
                flex-wrap: wrap;
                gap: 3px;
            }

            .stat-item {
                flex: 1 0 32%;
                min-width: 32%;
                padding: 2px !important;
                background: #f8f9fa;
                border-radius: 4px;
                margin: 1px;
            }

            .stat-label {
                font-size: 6px !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                margin-bottom: 1px !important;
            }

            .stat-value {
                font-size: 8px !important;
                font-weight: 600 !important;
                margin-bottom: 1px !important;
            }

            .stat-percentage {
                font-size: 6px !important;
                white-space: nowrap;
                overflow: visible;
                line-height: 1;
            }

            .percentage-arrow {
                font-size: 5px !important;
            }
        }

  
        @media (max-width: 575px) {
            .premium-main-value {
                font-size: 14px !important;
                line-height: 1.2 !important;
                margin: 2px 0 !important;
            }

            .stats-row {
                margin: 4px 0 !important;
                flex-wrap: wrap;
                gap: 2px;
            }

            .stat-item {
                flex: 1 0 32%;
                min-width: 32%;
                padding: 1px !important;
                background: #f8f9fa;
                border-radius: 3px;
                margin: 1px;
            }

            .stat-label {
                font-size: 5px !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                margin-bottom: 1px !important;
            }

            .stat-value {
                font-size: 7px !important;
                font-weight: 600 !important;
                margin-bottom: 0 !important;
            }

            .stat-percentage {
                font-size: 5px !important;
                white-space: nowrap;
                overflow: visible;
                line-height: 1;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .percentage-arrow {
                font-size: 4px !important;
                margin-left: 1px;
            }
        }

        @media (max-width: 399px) {
            .premium-main-value {
                font-size: 12px !important;
            }

            .stat-label {
                font-size: 4px !important;
            }

            .stat-value {
                font-size: 6px !important;
            }

            .stat-percentage {
                font-size: 4px !important;
            }

            .stat-item {
                padding: 0px !important;
                margin: 0.5px;
            }
        }

   
        @media (width: 1280px) and (height: 1024px) {
            .premium-main-value {
                font-size: 20px !important;
            }

            .stats-row {
                margin: 10px 0 !important;
            }

            .stat-label {
                font-size: 10px !important;
            }

            .stat-value {
                font-size: 12px !important;
            }

            .stat-percentage {
                font-size: 10px !important;
            }
        }


        @media (orientation: landscape) and (max-height: 600px) {
            .stats-row {
                margin: 4px 0 !important;
            }

            .stat-label,
            .stat-value,
            .stat-percentage {
                font-size: 7px !important;
            }

            .premium-main-value {
                font-size: 14px !important;
            }
        }

        
        .stat-percentage {
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        
    </style>

    <div class="content-body">
        <div style="margin: 15px;">

            @if (isset($error))
                <div class="alert alert-danger">{{ $error }}</div>
            @else
                <!-- ROW 1 - MAIN CARDS -->
                <div class="row">

                    <!-- CARD 1 - Premium Summary -->
                    <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                        <div class="card overflow-hidden" id="uw_card"
                            style="border-left: 8px solid #FFCF9F; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <div class="card-body">

                                <div class="row">
                                    <div class="col-12">
                                        <p
                                            style="margin:0px !important;padding:0px !important;font-size: 14px; font-weight: 600; margin-left:10px !important; margin-bottom: 10px !important; color:#000;">
                                            <i class="fas fa-chart-line card-header-icon" style="color: #FFCF9F;"></i>
                                            Premium Summary
                                        </p>
                                    </div>
                                </div>

                                <!-- Gross Premium -->
                                <div class="row" style="margin-left:10px !important; text-align:center;">
                                    <div class="col-12">
                                        <p style="margin:0; font-size:14px; color:#000; text-align:left;">
                                            Gross Premium
                                        </p>
                                        <p class="premium-main-value">
                                            Rs. {{ number_format($current['TOT_PRE'] ?? 0) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Stats Row -  -->
                                <div class="stats-row"
                                    style="margin-left:5px !important; margin-right:5px !important; display: flex; flex-wrap: nowrap; gap: 3px; justify-content: space-between;">
                                    <!-- Last Year -->
                                    <div class="stat-item" style="flex: 1; min-width: 0;">
                                        <div class="stat-label"
                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Last
                                            Year</div>
                                        <div class="stat-value"
                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ number_format($previous['TOT_PRE'] ?? 0) }}</div>
                                        <div class="stat-percentage {{ (($current['TOT_PRE'] ?? 0) / ($previous['TOT_PRE'] ?? 1)) * 100 >= 100 ? 'percentage-up' : 'percentage-down' }}"
                                            style="white-space: nowrap; overflow: visible;">
                                            @php
                                                $lastYearPercentage =
                                                    ($previous['TOT_PRE'] ?? 0) > 0
                                                        ? (($current['TOT_PRE'] ?? 0) / $previous['TOT_PRE']) * 100
                                                        : 0;
                                            @endphp
                                            {{ number_format($lastYearPercentage, 1) }}%
                                            @if ($lastYearPercentage >= 100)
                                                <i class="fas fa-arrow-up percentage-arrow"></i>
                                            @else
                                                <i class="fas fa-arrow-down percentage-arrow"></i>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Budget -->
                                    <div class="stat-item" style="flex: 1; min-width: 0;">
                                        <div class="stat-label"
                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Budget
                                        </div>
                                        <div class="stat-value"
                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ number_format($upto_month->total_sum ?? 0) }}</div>
                                        <div class="stat-percentage {{ (($current['TOT_PRE'] ?? 0) / ($upto_month->total_sum ?? 1)) * 100 >= 100 ? 'percentage-up' : 'percentage-down' }}"
                                            style="white-space: nowrap; overflow: visible;">
                                            @php
                                                $uptoMonthPercentage =
                                                    ($upto_month->total_sum ?? 0) > 0
                                                        ? (($current['TOT_PRE'] ?? 0) / $upto_month->total_sum) * 100
                                                        : 0;
                                            @endphp
                                            {{ number_format($uptoMonthPercentage, 1) }}%
                                            @if ($uptoMonthPercentage >= 100)
                                                <i class="fas fa-arrow-up percentage-arrow"></i>
                                            @else
                                                <i class="fas fa-arrow-down percentage-arrow"></i>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Total Budget -->
                                    <div class="stat-item" style="flex: 1; min-width: 0;">
                                        <div class="stat-label"
                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Total
                                            Budget</div>
                                        <div class="stat-value"
                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ number_format($total_budget->total_sum ?? 0) }}</div>
                                        <div class="stat-percentage {{ (($current['TOT_PRE'] ?? 0) / ($total_budget->total_sum ?? 1)) * 100 >= 100 ? 'percentage-up' : 'percentage-down' }}"
                                            style="white-space: nowrap; overflow: visible;">
                                            @php
                                                $budgetPercentage =
                                                    ($total_budget->total_sum ?? 0) > 0
                                                        ? (($current['TOT_PRE'] ?? 0) / $total_budget->total_sum) * 100
                                                        : 0;
                                            @endphp
                                            {{ number_format($budgetPercentage, 1) }}%
                                            @if ($budgetPercentage >= 100)
                                                <i class="fas fa-arrow-up percentage-arrow"></i>
                                            @else
                                                <i class="fas fa-arrow-down percentage-arrow"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <hr style="margin: 8px 0;">

                                <!-- Department-wise Table Layout -->
                                <div style="margin-left:10px !important; margin-right:10px !important;">
                                    <table style="width:100%; font-size:9px; border-collapse: collapse;">
                                        <thead>
                                            <tr>
                                                <th style="text-align:left; padding:2px; font-weight:500;"></th>
                                                <th style="text-align:center; padding:2px; font-weight:600; color:#000;">
                                                    Fire</th>
                                                <th style="text-align:center; padding:2px; font-weight:600; color:#000;">
                                                    Marine</th>
                                                <th style="text-align:center; padding:2px; font-weight:600; color:#000;">
                                                    Motor</th>
                                                <th style="text-align:center; padding:2px; font-weight:600; color:#000;">
                                                    Misc</th>
                                                <th style="text-align:center; padding:2px; font-weight:600; color:#000;">
                                                    Health</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="padding:2px; font-weight:600; color:#000;">CY:</td>

                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format(($current['FIREPRE'] ?? 0) / 1000000, 0) }}M
                                                </td>

                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format(($current['MARINEPRE'] ?? 0) / 1000000, 0) }}M
                                                </td>

                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format(($current['MOTORPRE'] ?? 0) / 1000000, 0) }}M
                                                </td>

                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format(($current['MISCPRE'] ?? 0) / 1000000, 0) }}M
                                                </td>

                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format(($current['HEALTHPRE'] ?? 0) / 1000000, 0) }}M
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="padding:2px; font-weight:600; color:#000;">LY:</td>

                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format(($previous['FIREPRE'] ?? 0) / 1000000, 0) }}M
                                                </td>

                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format(($previous['MARINEPRE'] ?? 0) / 1000000, 0) }}M
                                                </td>

                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format(($previous['MOTORPRE'] ?? 0) / 1000000, 0) }}M
                                                </td>

                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format(($previous['MISCPRE'] ?? 0) / 1000000, 0) }}M
                                                </td>

                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format(($previous['HEALTHPRE'] ?? 0) / 1000000, 0) }}M
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- CARD 2 - Document's Summary -->
                    <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                        <div class="card overflow-hidden" id="uw_card"
                            style="border-left: 8px solid #9AD0F5; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <div class="card-body">

                                <div class="row">
                                    <div class="col-12">
                                        <p
                                            style="margin:0px !important;padding:0px !important;font-size: 14px; font-weight: 600; margin-left:10px !important; margin-bottom: 10px !important; color:#000;">
                                            <i class="fas fa-file-alt card-header-icon" style="color: #9AD0F5;"></i>
                                            Document's Summary
                                        </p>
                                    </div>
                                </div>

                                <!-- Total Documents - Large Font -->
                                <div class="row" style="margin-left:10px !important; text-align:center;">
                                    <div class="col-12">
                                        <p style="margin:0; font-size:14px; color:#000; text-align:left;">
                                            Total Documents
                                        </p>
                                        <p class="premium-main-value">
                                            {{ number_format($data['DOCS'][0]['TOT_DOC'] ?? 0) }}
                                        </p>
                                    </div>
                                </div>

                                <hr style="margin: 8px 0;">

                                <!-- Department-wise Documents Table -->
                                <div style="margin-left:10px !important; margin-right:10px !important;">
                                    <table style="width:100%; font-size:9px; border-collapse: collapse;">
                                        <thead>
                                            <tr>
                                                <th style="text-align:left; padding:2px; font-weight:500;"></th>
                                                <th style="text-align:center; padding:2px; font-weight:600; color:#000;">
                                                    Fire</th>
                                                <th style="text-align:center; padding:2px; font-weight:600; color:#000;">
                                                    Marine</th>
                                                <th style="text-align:center; padding:2px; font-weight:600; color:#000;">
                                                    Motor</th>
                                                <th style="text-align:center; padding:2px; font-weight:600; color:#000;">
                                                    Misc</th>
                                                <th style="text-align:center; padding:2px; font-weight:600; color:#000;">
                                                    Health</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="padding:2px; font-weight:600; color:#000;">Docs:</td>
                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format($data['DOCS'][0]['FIRE_DOC'] ?? 0) }}
                                                </td>
                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format($data['DOCS'][0]['MARINE_DOC'] ?? 0) }}
                                                </td>
                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format($data['DOCS'][0]['MOTOR_DOC'] ?? 0) }}
                                                </td>
                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format($data['DOCS'][0]['MISC_DOC'] ?? 0) }}
                                                </td>
                                                <td style="text-align:center; padding:2px;">
                                                    {{ number_format($data['DOCS'][0]['HLT_DOC'] ?? 0) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Unposted Documents -->
                                <div style="margin-left:10px !important; margin-right:10px !important; margin-top: 8px;">
                                    <p style="margin:0; font-size:12px; color:#000; font-weight:600;">
                                        Unposted Documents:
                                        <span style="font-weight:600; color:#dc3545;">
                                            {{ number_format($data['DOCS'][0]['UNPOSTED_DOC'] ?? 0) }}
                                        </span>
                                    </p>
                                </div>

                                <!-- Unposted Documents Breakdown -->
                                <div style="margin-left:10px !important; margin-right:10px !important; margin-top: 8px;">
                                    <table style="width:100%; font-size:8px; border-collapse: collapse;">
                                        <thead>
                                            <tr>
                                                <th style="text-align:center; padding:1px; font-weight:600; color:#000;">
                                                    > 7</th>
                                                <th style="text-align:center; padding:1px; font-weight:600; color:#000;">
                                                     7-9</th>
                                                <th style="text-align:center; padding:1px; font-weight:600; color:#000;">
                                                    10-14</th>
                                                <th style="text-align:center; padding:1px; font-weight:600; color:#000;">
                                                     15-19</th>
                                                <th style="text-align:center; padding:1px; font-weight:600; color:#000;">
                                                     20-29</th>
                                                <th style="text-align:center; padding:1px; font-weight:600; color:#000;">
                                                     30+</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="text-align:center; padding:1px;">
                                                    {{ number_format($data['DOCS'][0]['UNPOSTED_UPTO_7'] ?? 0) }}
                                                </td>
                                                <td style="text-align:center; padding:1px;">
                                                    {{ number_format($data['DOCS'][0]['UNPOSTED_7_TO_9'] ?? 0) }}
                                                </td>
                                                <td style="text-align:center; padding:1px;">
                                                    {{ number_format($data['DOCS'][0]['UNPOSTED_10_TO_14'] ?? 0) }}
                                                </td>
                                                <td style="text-align:center; padding:1px;">
                                                    {{ number_format($data['DOCS'][0]['UNPOSTED_15_TO_19'] ?? 0) }}
                                                </td>
                                                <td style="text-align:center; padding:1px;">
                                                    {{ number_format($data['DOCS'][0]['UNPOSTED_20_TO_29'] ?? 0) }}
                                                </td>
                                                <td style="text-align:center; padding:1px;">
                                                    {{ number_format($data['DOCS'][0]['UNPOSTED_OVER_30'] ?? 0) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- CARD 3 - RENEWAL -->
                    <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                        <div class="card overflow-hidden" id="uw_card"
                            style="border-left: 8px solid #9affc7; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <p
                                            style="margin:0px !important;padding:0px !important;font-size: 14px; font-weight: 600; margin-left:10px !important; margin-bottom: 10px !important; color:#000;">
                                            <i class="fas fa-sync-alt card-header-icon" style="color: #9affc7;"></i>
                                            Renewal Summary
                                        </p>
                                    </div>
                                    <p style="margin-left:5px; font-weight:600; margin-left:10px;">
                                        Next 30 Day's
                                    </p>
                                </div>

                                <div class="row" style="margin-left:10px !important; margin-bottom:5px !important;">
                                    <div class="col-12">
                                        <p style="margin:0; font-size:14px; color:#000;">
                                            Total Premium:
                                            <span style="margin-left:5px; font-weight:600;">
                                                {{ number_format($total_renew_premium ?? 0) }}
                                            </span>
                                        </p>
                                        <p style="margin:0; font-size:14px; color:#000;">
                                            Total Documents:
                                            <span style="margin-left:5px; font-weight:600;">
                                                {{ $total_renew_docs ?? 0 }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-12">
                                        <hr style="margin: 8px 0;">
                                        <p style="margin:0; font-size:14px; color:#000;">
                                            Broker Business:
                                            <span style="margin-left:5px; font-weight:600;">
                                                {{ number_format($current['BROKER_PRE'] ?? 0) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 4  -->
                    <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                        <div class="card overflow-hidden" id="uw_card"
                            style="border-left: 8px solid #ffb0c0; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <p
                                            style="margin:0px !important;padding:0px !important;font-size: 14px; font-weight: 600; margin-left:10px !important; margin-bottom: 10px !important; color:#000;">
                                            <i class="fas fa-clock card-header-icon" style="color: #ffb0c0;"></i>
                                            Coming Soon....
                                        </p>
                                    </div>
                                    <span style="margin-left:5px; font-weight:600; margin-left:10px;">
                                    </span>
                                </div>

                                <div class="row" style="margin-left:10px !important; margin-bottom:5px !important;">
                                    <!-- will erite dta here-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 2 - Charts -->
                <div class="row" style="margin-top: 15px;">
                    <!-- Gross Premium Comparison -->
                    <div class="col-xl-4 col-lg-4 col-md-12">
                        <div class="card overflow-hidden">
                            <div class="card-body px-4 py-4">
                                <div class="table-header">
                                    <i class="fas fa-chart-bar"></i>
                                    <b>Gross Premium Comparison - Upto Month</b>
                                </div>

                             
                                <div class="mb-2" style="display: flex; justify-content: flex-end; gap: 5px;">
                                    @foreach ($departments as $dept)
                                        <button class="dept-btn" data-dept="{{ $dept }}"
                                            style="
                                                background: white;
                                                border: 1px solid #ccc;
                                                padding: 3px 10px;
                                                font-size: 11px;
                                                border-radius: 4px;
                                                cursor: pointer;
                                                color: #000;
                                            ">
                                            {{ $dept }}
                                        </button>
                                    @endforeach

                                    <button class="dept-btn active" data-dept="Total"
                                        style="
                                            background: white;
                                            border: 1px solid #ccc;
                                            padding: 3px 10px;
                                            font-size: 11px;
                                            border-radius: 4px;
                                            cursor: pointer;
                                            color: #000;
                                        ">
                                        All
                                    </button>
                                </div>

                                <div class="chart-point chart-container" style="height: 150px;">
                                    <canvas id="gross-premium-comparison"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Department-wise Premium Distribution Chart -->
                    <div class="col-xl-4 col-lg-4 col-md-12">
                        <div class="card overflow-hidden">
                            <div class="card-body px-4 py-4">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <div class="table-header">
                                        <i class="fas fa-chart-pie"></i>
                                        <b>Department-wise Premium Distribution</b>
                                    </div>
                                    <button class="toggle-btn" id="toggle-dept-view">
                                        <i class="fas fa-exchange-alt"></i> Toggle View
                                    </button>
                                </div>

                                <div class="chart-point chart-container" style="height: 150px;">
                                    <canvas id="dept-premium-distribution"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Month-wise Premium Comparison -->
                    <div class="col-xl-4 col-lg-4 col-md-12">
                        <div class="card overflow-hidden">
                            <div class="card-body px-4 py-4">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <div class="table-header">
                                        <i class="fas fa-chart-line"></i>
                                        <b>Month-wise Premium Comparison</b>
                                    </div>
                                    <button class="toggle-btn" id="toggle-view">
                                        <i class="fas fa-exchange-alt"></i> Toggle View
                                    </button>
                                </div>
                                <div class="chart-point chart-container" style="height: 150px;">
                                    <canvas id="cy-monthwise-pre"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Top 5 Branches -->
                    <div class="col-xl-3 col-lg-6 col-md-12 mb-3">
                        <div class="card table-card">
                            <div class="card-body">
                                <div class="table-header">
                                    <i class="fas fa-building"></i>
                                    <b>Top 5 Branches</b>
                                </div>
                                <div class="table-container">
                                    <table class="custom-table">
                                        <thead>
                                            <tr>
                                                <th>Branch</th>
                                                <th class="text-right">TOT_PRE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topBranches as $branch)
                                                <tr>
                                                    <td>{{ $branch['name'] }}</td>
                                                    <td class="text-right">{{ number_format($branch['TOT_PRE'] ?? 0) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div style="text-align: right; margin-top: 10px;">
                                        <a href="{{ route('uw.dashboard') }}" target="_blank" class="toggle-btn"
                                            style="text-decoration: none; display: inline-flex; align-items: center;">
                                            See More <i class="fas fa-arrow-right"
                                                style="margin-left: 5px; font-size: 10px;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top 5 Brokers -->
                    <div class="col-xl-3 col-lg-6 col-md-12 mb-3">
                        <div class="card table-card">
                            <div class="card-body">
                                <div class="table-header">
                                    <i class="fas fa-handshake"></i>
                                    <b>Top 5 Brokers</b>
                                </div>
                                <div class="table-container">
                                    <table class="custom-table">
                                        <thead>
                                            <tr>
                                                <th>Broker</th>
                                                <th class="text-right">TOT_PRE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topBrokers as $broker)
                                                <tr>
                                                    <td>{{ $broker['name'] }}</td>
                                                    <td class="text-right">{{ number_format($broker['TOT_PRE'] ?? 0) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div style="text-align: right; margin-top: 10px;">
                                        <a href="{{ route('uw.dashboard') }}" target="_blank" class="toggle-btn"
                                            style="text-decoration: none; display: inline-flex; align-items: center;">
                                            See More <i class="fas fa-arrow-right"
                                                style="margin-left: 5px; font-size: 10px;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top 5 Insured -->
                    <div class="col-xl-3 col-lg-6 col-md-12 mb-3">
                        <div class="card table-card">
                            <div class="card-body">
                                <div class="table-header">
                                    <i class="fas fa-user-shield"></i>
                                    <b>Top 5 Insured</b>
                                </div>
                                <div class="table-container">
                                    <table class="custom-table">
                                        <thead>
                                            <tr>
                                                <th>Insured</th>
                                                <th class="text-right">TOT_PRE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topInsured as $insured)
                                                <tr>
                                                    <td>{{ $insured['name'] }}</td>
                                                    <td class="text-right">{{ number_format($insured['TOT_PRE'] ?? 0) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div style="text-align: right; margin-top: 10px;">
                                        <a href="{{ route('uw.dashboard') }}" target="_blank" class="toggle-btn"
                                            style="text-decoration: none; display: inline-flex; align-items: center;">
                                            See More <i class="fas fa-arrow-right"
                                                style="margin-left: 5px; font-size: 10px;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top 5 DOs -->
                    <div class="col-xl-3 col-lg-6 col-md-12 mb-3">
                        <div class="card table-card">
                            <div class="card-body">
                                <div class="table-header">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <b>Top 5 DO's</b>
                                </div>
                                <div class="table-container">
                                    <table class="custom-table">
                                        <thead>
                                            <tr>
                                                <th>DO</th>
                                                <th class="text-right">TOT_PRE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topDOs as $do)
                                                <tr>
                                                    <td>{{ $do['name'] }}</td>
                                                    <td class="text-right">{{ number_format($do['TOT_PRE'] ?? 0) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div style="text-align: right; margin-top: 10px;">
                                        <a href="{{ route('uw.dashboard') }}" target="_blank" class="toggle-btn"
                                            style="text-decoration: none; display: inline-flex; align-items: center;">
                                            See More <i class="fas fa-arrow-right"
                                                style="margin-left: 5px; font-size: 10px;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Loading Chart.js and Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        $(document).ready(function() {
         
            var deptLabels = ['Fire', 'Marine', 'Motor', 'Misc', 'Health'];
            var cyData = [
                {{ $current['FIREPRE'] ?? 0 }},
                {{ $current['MARINEPRE'] ?? 0 }},
                {{ $current['MOTORPRE'] ?? 0 }},
                {{ $current['MISCPRE'] ?? 0 }},
                {{ $current['HEALTHPRE'] ?? 0 }}
            ];

            var pyData = [
                {{ $previous['FIREPRE'] ?? 0 }},
                {{ $previous['MARINEPRE'] ?? 0 }},
                {{ $previous['MOTORPRE'] ?? 0 }},
                {{ $previous['MISCPRE'] ?? 0 }},
                {{ $previous['HEALTHPRE'] ?? 0 }}
            ];

            const ctxDept = document.getElementById('dept-premium-distribution').getContext('2d');

            var deptChart = new Chart(ctxDept, {
                type: 'doughnut',
                data: {
                    labels: deptLabels,
                    datasets: [{
                        data: cyData,
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: 10
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                font: {
                                    family: 'Inter',
                                    size: 10
                                },
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${(value / 1000000).toFixed(1)}M (${percentage}%)`;
                                }
                            },
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                family: 'Inter',
                                size: 12
                            },
                            bodyFont: {
                                family: 'Inter',
                                size: 11
                            },
                            padding: 10
                        }
                    },
                    cutout: '60%'
                }
            });

            
            $('#toggle-dept-view').click(function() {
                $(this).toggleClass('active');

                if (deptChart.config.type === 'doughnut') {
                    deptChart.destroy();

                    deptChart = new Chart(ctxDept, {
                        type: 'bar',
                        data: {
                            labels: deptLabels,
                            datasets: [{
                                    label: 'Current Year',
                                    data: cyData,
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.7)',
                                        'rgba(54, 162, 235, 0.7)',
                                        'rgba(255, 206, 86, 0.7)',
                                        'rgba(75, 192, 192, 0.7)',
                                        'rgba(153, 102, 255, 0.7)'
                                    ],
                                    borderColor: [
                                        '#FF6384',
                                        '#36A2EB',
                                        '#FFCE56',
                                        '#4BC0C0',
                                        '#9966FF'
                                    ],
                                    borderWidth: 1,
                                    barThickness: 20,
                                    borderRadius: 4
                                },
                                {
                                    label: 'Last Year',
                                    data: pyData,
                                    type: 'line',
                                    borderColor: '#666',
                                    borderWidth: 2,
                                    pointBackgroundColor: '#666',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    fill: false,
                                    tension: 0.1
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false,
                            layout: {
                                padding: 10
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return (value / 1000000) + 'M';
                                        },
                                        font: {
                                            family: 'Inter',
                                            size: 9
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            family: 'Inter',
                                            size: 9
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        },
                                        usePointStyle: true
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                const value = context.parsed.y;
                                                const formattedValue = (value / 1000000)
                                                    .toFixed(1) + 'M';
                                                label += formattedValue;
                                            }
                                            return label;
                                        }
                                    },
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleFont: {
                                        family: 'Inter',
                                        size: 12
                                    },
                                    bodyFont: {
                                        family: 'Inter',
                                        size: 11
                                    },
                                    padding: 10
                                }
                            },
                            interaction: {
                                mode: 'index',
                                intersect: false
                            }
                        }
                    });
                } else {
                    deptChart.destroy();

                    deptChart = new Chart(ctxDept, {
                        type: 'doughnut',
                        data: {
                            labels: deptLabels,
                            datasets: [{
                                data: cyData,
                                backgroundColor: [
                                    '#FF6384',
                                    '#36A2EB',
                                    '#FFCE56',
                                    '#4BC0C0',
                                    '#9966FF'
                                ],
                                borderColor: '#fff',
                                borderWidth: 2,
                                hoverOffset: 8
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            layout: {
                                padding: 10
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        },
                                        usePointStyle: true,
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed;
                                            const total = context.dataset.data.reduce((a, b) =>
                                                a + b, 0);
                                            const percentage = ((value / total) * 100).toFixed(
                                                1);
                                            return `${label}: ${(value / 1000000).toFixed(1)}M (${percentage}%)`;
                                        }
                                    },
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleFont: {
                                        family: 'Inter',
                                        size: 12
                                    },
                                    bodyFont: {
                                        family: 'Inter',
                                        size: 11
                                    },
                                    padding: 10
                                }
                            },
                            cutout: '60%'
                        }
                    });
                }
            });

            // =============================================
            // MONTH-WISE PREMIUM CHART                    = 
            // =============================================

            var monthwise_pre_arr = @json($monthwise_pre_arr);
            var monthwise_pre_prev_arr = @json($monthwise_pre_prev_arr);
            var monthwise_budget_arr = @json($monthwise_budget_arr);

            const ctxMonthwise = document.getElementById('cy-monthwise-pre').getContext('2d');


            function getResponsiveSettings() {
                const width = window.innerWidth;
                if (width < 576) {
                    return {
                        barPercentage: 0.5,
                        categoryPercentage: 0.7,
                        fontSize: 8,
                        legendSize: 8,
                        rotation: 45
                    };
                }
                if (width < 768) {
                    return {
                        barPercentage: 0.6,
                        categoryPercentage: 0.8,
                        fontSize: 9,
                        legendSize: 9,
                        rotation: 0
                    };
                }
                return {
                    barPercentage: 0.7,
                    categoryPercentage: 0.9,
                    fontSize: 10,
                    legendSize: 10,
                    rotation: 0
                };
            }

            const settings = getResponsiveSettings();

      
            var barChartData = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                        label: 'Monthly Budget',
                        data: monthwise_budget_arr,
                        backgroundColor: '#28a745',
                        borderColor: '#28a745',
                        borderWidth: 1,
                        borderRadius: 2
                    },
                    {
                        label: 'Current Year Premium',
                        data: monthwise_pre_arr,
                        backgroundColor: '#9AD0F5',
                        borderColor: '#9AD0F5',
                        borderWidth: 1,
                        borderRadius: 2
                    },
                    {
                        label: 'Last Year Premium',
                        data: monthwise_pre_prev_arr,
                        backgroundColor: '#FFB1C1',
                        borderColor: '#FFB1C1',
                        borderWidth: 1,
                        borderRadius: 2
                    }
                ]
            };

      
            var lineChartData = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                        label: 'Budget',
                        data: monthwise_budget_arr,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        borderDash: [5, 5],
                        fill: true,
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        type: 'line'
                    },
                    {
                        label: 'Current Year',
                        data: monthwise_pre_arr,
                        borderColor: '#9AD0F5',
                        backgroundColor: 'rgba(154, 208, 245, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        pointBackgroundColor: '#9AD0F5',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1,
                        pointRadius: 3,
                        type: 'line'
                    },
                    {
                        label: 'Last Year',
                        data: monthwise_pre_prev_arr,
                        borderColor: '#FFB1C1',
                        backgroundColor: 'rgba(255, 177, 193, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        pointBackgroundColor: '#FFB1C1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1,
                        pointRadius: 3,
                        type: 'line'
                    }
                ]
            };

            var monthwiseChart = new Chart(ctxMonthwise, {
                type: 'bar',
                data: barChartData,
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    barPercentage: settings.barPercentage,
                    categoryPercentage: settings.categoryPercentage,
                    layout: {
                        padding: {
                            left: 5,
                            right: 5,
                            top: 5,
                            bottom: 5
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return (value / 1000000) + 'M';
                                },
                                font: {
                                    family: 'Inter',
                                    size: settings.fontSize
                                },
                                maxTicksLimit: 6
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: settings.fontSize
                                },
                                maxRotation: settings.rotation,
                                minRotation: settings.rotation
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    family: 'Inter',
                                    size: settings.legendSize
                                },
                                usePointStyle: true,
                                padding: 10,
                                boxWidth: 12
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        const value = context.parsed.y;
                                        const formattedValue = (value / 1000000).toFixed(1) + 'M';
                                        label += formattedValue;
                                    }
                                    return label;
                                }
                            },
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                family: 'Inter',
                                size: 12
                            },
                            bodyFont: {
                                family: 'Inter',
                                size: 11
                            },
                            padding: 10,
                            displayColors: true,
                            usePointStyle: true
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    }
                }
            });

          
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function() {
                    const newSettings = getResponsiveSettings();

                    monthwiseChart.options.barPercentage = newSettings.barPercentage;
                    monthwiseChart.options.categoryPercentage = newSettings.categoryPercentage;
                    monthwiseChart.options.scales.x.ticks.font.size = newSettings.fontSize;
                    monthwiseChart.options.scales.y.ticks.font.size = newSettings.fontSize;
                    monthwiseChart.options.plugins.legend.labels.font.size = newSettings.legendSize;
                    monthwiseChart.options.scales.x.ticks.maxRotation = newSettings.rotation;
                    monthwiseChart.options.scales.x.ticks.minRotation = newSettings.rotation;

                    monthwiseChart.update();
                }, 250);
            });

      
            $('#toggle-view').click(function() {
                $(this).toggleClass('active');

                if (monthwiseChart.config.type === 'bar') {
                    monthwiseChart.config.type = 'line';
                    monthwiseChart.data = lineChartData;
                } else {
                    monthwiseChart.config.type = 'bar';
                    monthwiseChart.data = barChartData;

                    const newSettings = getResponsiveSettings();
                    monthwiseChart.options.barPercentage = newSettings.barPercentage;
                    monthwiseChart.options.categoryPercentage = newSettings.categoryPercentage;
                }

                monthwiseChart.update();
            });

            // =============================================
            // GROSS PREMIUM COMPARISON CHART              =
            // =============================================

            var ctxGross = document.getElementById('gross-premium-comparison').getContext('2d');
            var deptData = @json($deptData);

            var grossChart = new Chart(ctxGross, {
                type: 'bar',
                data: {
                    labels: ['Gross Premium', 'Last Year', 'Upto Month'],
                    datasets: [{
                        label: 'Amount',
                        data: [
                            {{ $current['TOT_PRE'] ?? 0 }},
                            {{ $previous['TOT_PRE'] ?? 0 }},
                            {{ $upto_month->total_sum ?? 0 }}
                        ],
                        backgroundColor: ['#FFCF9F', '#FFB1C1', '#9AD0F5'],
                        barThickness: 12,
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    layout: {
                        padding: 0
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return (value / 1000000) + 'M';
                                },
                                font: {
                                    family: 'Inter'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    family: 'Inter'
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

           
            $('.dept-btn').click(function() {
                $('.dept-btn').removeClass('active');
                $(this).addClass('active');

                var dept = $(this).data('dept');

                if (dept === 'Total') {
                    grossChart.data.datasets[0].data = [
                        {{ $current['TOT_PRE'] ?? 0 }},
                        {{ $previous['TOT_PRE'] ?? 0 }},
                        {{ $upto_month->total_sum ?? 0 }}
                    ];
                } else {
                    grossChart.data.datasets[0].data = [
                        deptData[dept]['CY'],
                        deptData[dept]['PY'],
                        deptData[dept]['Upto']
                    ];
                }

                grossChart.update();
            });
        });
    </script>
@endsection

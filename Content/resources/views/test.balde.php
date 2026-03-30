@extends('Charts.master')
@section('content')
    <style>
        /* Import Inter font */
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

        /* Added styles for active button and toggle */
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

        /* New styles for premium summary card */
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

        /* New styles for tables */
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
                                            Premium Summary</p>
                                    </div>
                                </div>

                                <!-- Gross Premium - Large Font -->
                                <div class="row" style="margin-left:10px !important; text-align:center;">
                                    <div class="col-12">
                                        <p style="margin:0; font-size:14px; color:#000;">
                                            Gross Premium
                                        </p>
                                        <p class="premium-main-value">
                                            {{ number_format($current['TOT_PRE'] ?? 0) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Stats Row -->
                                <div class="stats-row" style="margin-left:10px !important; margin-right:10px !important;">
                                    <!-- Last Year -->
                                    <div class="stat-item">
                                        <div class="stat-label">Last Year</div>
                                        <div class="stat-value">{{ number_format($previous['TOT_PRE'] ?? 0) }}</div>
                                        <div
                                            class="stat-percentage {{ (($current['TOT_PRE'] ?? 0) / ($previous['TOT_PRE'] ?? 1)) * 100 >= 100 ? 'percentage-up' : 'percentage-down' }}">
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

                                    <!-- Upto Month -->
                                    <div class="stat-item">
                                        <div class="stat-label">Budget</div>
                                        <div class="stat-value">{{ number_format($upto_month->total_sum ?? 0) }}</div>
                                        <div
                                            class="stat-percentage {{ (($upto_month->total_sum ?? 0) / ($current['TOT_PRE'] ?? 1)) * 100 >= 100 ? 'percentage-up' : 'percentage-down' }}">
                                            @php
                                                $uptoMonthPercentage =
                                                    ($current['TOT_PRE'] ?? 0) > 0
                                                        ? (($upto_month->total_sum ?? 0) / $current['TOT_PRE']) * 100
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
                                    <div class="stat-item">
                                        <div class="stat-label">Total Budget</div>
                                        <div class="stat-value">{{ number_format($total_budget->total_sum ?? 0) }}</div>
                                        <div
                                            class="stat-percentage {{ (($current['TOT_PRE'] ?? 0) / ($total_budget->total_sum ?? 1)) * 100 >= 100 ? 'percentage-up' : 'percentage-down' }}">
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

                    <!-- Rest of your cards remain the same -->
                    <!-- CARD 2 - Broker Business -->
                    <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                        <div class="card overflow-hidden" id="uw_card"
                            style="border-left: 8px solid #9AD0F5; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <div class="card-body">

                                <div class="row" style="margin-left:10px !important; margin-bottom:5px !important;">
                                    <div class="col-12">
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

                    <!-- CARD 3 - RENEWAL -->
                    <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                        <div class="card overflow-hidden" id="uw_card"
                            style="border-left: 8px solid #9effe2; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <p
                                            style="margin:0px !important;padding:0px !important;font-size: 14px; font-weight: 600; margin-left:10px !important; margin-bottom: 10px !important; color:#000;">
                                            Renewal Summary</p>

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
                                </div>

                            </div>
                        </div>
                    </div>


                    <!-- CARD 4 - COMING SOON -->
                    <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                        <div class="card overflow-hidden" id="uw_card"
                            style="border-left: 8px solid #ffb0c0; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <p
                                            style="margin:0px !important;padding:0px !important;font-size: 14px; font-weight: 600; margin-left:10px !important; margin-bottom: 10px !important; color:#000;">
                                            Coming Soon....</p>

                                    </div>
                                    <span style="margin-left:5px; font-weight:600; margin-left:10px;">

                                    </span>
                                </div>

                                <div class="row" style="margin-left:10px !important; margin-bottom:5px !important;">
                                    {{-- <div class="col-12">
                                        <p style="margin:0; font-size:14px; color:#000;">
                                            NA:
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
                                    </div> --}}
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <!-- ROW 2 - Charts -->
                <div class="row" style="margin-top: 15px;">
                    <!-- Gross Premium Comparison -->
                    <div class="col-xl-6 col-lg-6 col-md-12">
                        <div class="card overflow-hidden">
                            <div class="card-body px-4 py-4">
                                <div class="table-header">
                                    <i class="fas fa-chart-bar"></i>
                                    <b>Gross Premium Comparison - Upto Month</b>
                                </div>

                                <!-- Buttons aligned to the extreme right -->
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

                    <!-- Month-wise Premium Comparison -->
                    <div class="col-xl-6 col-lg-6 col-md-12">
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
                                                    <td class="text-right">{{ number_format($branch['TOT_PRE'] ?? 0) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
                                                    <td class="text-right">{{ number_format($broker['TOT_PRE'] ?? 0) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
                                                    <td class="text-right">{{ number_format($insured['TOT_PRE'] ?? 0) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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

            // Month-wise Premium Chart 
            var monthwise_pre_arr = @json($monthwise_pre_arr);
            var monthwise_pre_prev_arr = @json($monthwise_pre_prev_arr);
            var monthwise_budget_arr = @json($monthwise_budget_arr); // Add this line for budget data

            const ctxMonthwise = document.getElementById('cy-monthwise-pre').getContext('2d');

            var monthwiseChart = new Chart(ctxMonthwise, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                        'Dec'
                    ],
                    datasets: [{
                            label: 'Current Year Premium',
                            data: monthwise_pre_arr,
                            backgroundColor: '#9AD0F5',
                            barThickness: 12,
                            borderRadius: 4
                        },
                        {
                            label: 'Last Year Premium',
                            data: monthwise_pre_prev_arr,
                            backgroundColor: '#FFB1C1',
                            barThickness: 12,
                            borderRadius: 4
                        },
                        {
                            label: 'Monthly Budget',
                            data: monthwise_budget_arr,
                            backgroundColor: '#9effe2',
                            barThickness: 8,
                            borderRadius: 4,
                            type: 'line', // This will make budget appear as a line
                            borderColor: '#28a745',
                            borderWidth: 2,
                            pointBackgroundColor: '#28a745',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 1,
                            pointRadius: 3,
                            fill: false
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: 0
                    },
                    scales: {
                        y: {
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
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Inter'
                                }
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
                        }
                    }
                }
            });

            // Gross Premium Comparison Chart 
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


            $('#toggle-view').click(function() {
                $(this).toggleClass('active');

                if (monthwiseChart.config.type === 'bar') {
                    monthwiseChart.config.type = 'line';

                    monthwiseChart.data.datasets[0].borderColor = '#9AD0F5';
                    monthwiseChart.data.datasets[0].backgroundColor = 'rgba(154, 208, 245, 0.1)';
                    monthwiseChart.data.datasets[0].borderWidth = 2;
                    monthwiseChart.data.datasets[0].fill = true;
                    monthwiseChart.data.datasets[0].pointBackgroundColor = '#9AD0F5';
                    monthwiseChart.data.datasets[0].pointBorderColor = '#fff';
                    monthwiseChart.data.datasets[0].pointBorderWidth = 1;
                    monthwiseChart.data.datasets[0].pointRadius = 3;

                    monthwiseChart.data.datasets[1].borderColor = '#FFB1C1';
                    monthwiseChart.data.datasets[1].backgroundColor = 'rgba(255, 177, 193, 0.1)';
                    monthwiseChart.data.datasets[1].borderWidth = 2;
                    monthwiseChart.data.datasets[1].fill = true;
                    monthwiseChart.data.datasets[1].pointBackgroundColor = '#FFB1C1';
                    monthwiseChart.data.datasets[1].pointBorderColor = '#fff';
                    monthwiseChart.data.datasets[1].pointBorderWidth = 1;
                    monthwiseChart.data.datasets[1].pointRadius = 3;

                    // Keep budget as line but adjust styling
                    monthwiseChart.data.datasets[2].borderColor = '#28a745';
                    monthwiseChart.data.datasets[2].backgroundColor = 'rgba(40, 167, 69, 0.1)';
                    monthwiseChart.data.datasets[2].borderWidth = 3;
                    monthwiseChart.data.datasets[2].borderDash = [5, 5];
                    monthwiseChart.data.datasets[2].fill = true;
                    monthwiseChart.data.datasets[2].pointBackgroundColor = '#28a745';
                    monthwiseChart.data.datasets[2].pointBorderColor = '#fff';
                    monthwiseChart.data.datasets[2].pointBorderWidth = 2;
                    monthwiseChart.data.datasets[2].pointRadius = 4;
                } else {
                    monthwiseChart.config.type = 'bar';

                    monthwiseChart.data.datasets[0].backgroundColor = '#9AD0F5';
                    monthwiseChart.data.datasets[0].borderColor = undefined;
                    monthwiseChart.data.datasets[0].borderWidth = 0;
                    monthwiseChart.data.datasets[0].fill = false;
                    monthwiseChart.data.datasets[0].pointBackgroundColor = undefined;
                    monthwiseChart.data.datasets[0].pointBorderColor = undefined;
                    monthwiseChart.data.datasets[0].pointBorderWidth = 0;
                    monthwiseChart.data.datasets[0].pointRadius = 0;

                    monthwiseChart.data.datasets[1].backgroundColor = '#FFB1C1';
                    monthwiseChart.data.datasets[1].borderColor = undefined;
                    monthwiseChart.data.datasets[1].borderWidth = 0;
                    monthwiseChart.data.datasets[1].fill = false;
                    monthwiseChart.data.datasets[1].pointBackgroundColor = undefined;
                    monthwiseChart.data.datasets[1].pointBorderColor = undefined;
                    monthwiseChart.data.datasets[1].pointBorderWidth = 0;
                    monthwiseChart.data.datasets[1].pointRadius = 0;

                    // Reset budget to line over bars
                    monthwiseChart.data.datasets[2].type = 'line';
                    monthwiseChart.data.datasets[2].borderColor = '#28a745';
                    monthwiseChart.data.datasets[2].backgroundColor = undefined;
                    monthwiseChart.data.datasets[2].borderWidth = 2;
                    monthwiseChart.data.datasets[2].borderDash = [5, 5];
                    monthwiseChart.data.datasets[2].fill = false;
                    monthwiseChart.data.datasets[2].pointBackgroundColor = '#28a745';
                    monthwiseChart.data.datasets[2].pointBorderColor = '#fff';
                    monthwiseChart.data.datasets[2].pointBorderWidth = 1;
                    monthwiseChart.data.datasets[2].pointRadius = 3;
                }

                monthwiseChart.update();
            });

        });
    </script>
@endsection
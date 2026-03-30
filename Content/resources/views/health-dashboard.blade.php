
@section('title', 'Health-Dashboard')
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
            height: 100%;
        }

        .table-header {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            color: #000000;
            font-size: 12px;
        }

        .table-header i {
            margin-right: 8px;
            color: #4361ee;
        }

        .see-more-btn {
            background: transparent;
            border: none;
            color: #4361ee;
            font-size: 10px;
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 3px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            float: right;
            text-decoration: none;
            font-weight: 500;
        }

        .see-more-btn:hover {
            background: #f0f4ff;
            text-decoration: none;
        }

        .see-more-btn i {
            font-size: 8px;
            margin-left: 2px;
        }

        .table-container {
            background: white;
            border-radius: 6px;
            padding: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            height: 200px;
            display: flex;
            flex-direction: column;
        }

        .compact-table-wrapper {
            flex-grow: 1;
            overflow: hidden;
            margin-bottom: 0;
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
            padding: 6px 4px;
            text-align: left;
            font-weight: 600;
            color: #000;
            border-bottom: 1px solid #dee2e6;
            font-size: 10px;
        }

        .custom-table td {
            padding: 4px;
            border-bottom: 1px solid #f1f3f4;
            font-size: 10px;
            line-height: 1.2;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
        }

        .stat-label-grid {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-value-grid {
            font-size: 16px;
            font-weight: 700;
            color: #000;
        }

        /* Policy info styles for Card 1 */
        .policy-info-row {
            margin-bottom: 12px;
            padding-bottom: 10px;
        }

        .policy-info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .policy-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .policy-value {
            font-size: 14px;
            color: #000;
            font-weight: 600;
        }

        /* Compact table styles */
        .compact-table-container {
            background: white;
            border-radius: 6px;
            padding: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            height: 180px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .compact-table {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .compact-table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        .compact-table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            color: #000;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
            white-space: nowrap;
        }

        .compact-table td {
            padding: 6px;
            border-bottom: 1px solid #f1f3f4;
            font-size: 11px;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .compact-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .compact-table tfoot {
            background-color: #f8f9fa;
            font-weight: 600;
            border-top: 2px solid #e9ecef;
        }

        .compact-table tfoot td {
            padding: 3px 2px;
            font-size: 10px;
        }

        /* No data message */
        .no-data {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            font-size: 10px;
            padding: 5px;
            text-align: center;
        }

        .no-data i {
            font-size: 16px;
            margin-bottom: 5px;
            color: #999;
        }

        /* See More Button Container */
        .see-more-container {
            text-align: right;
            margin-top: 2px;
            padding-top: 2px;
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

            .stat-value-grid {
                font-size: 14px !important;
            }

            .table-container,
            .compact-table-container {
                height: 170px;
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

            .stat-value-grid {
                font-size: 14px !important;
            }

            .table-container,
            .compact-table-container {
                height: 160px;
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

            .stat-value-grid {
                font-size: 13px !important;
            }

            .table-container,
            .compact-table-container {
                height: 150px;
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

            .stat-value-grid {
                font-size: 12px !important;
            }

            .table-container,
            .compact-table-container {
                height: 140px;
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

            .stat-value-grid {
                font-size: 11px !important;
            }

            .table-container,
            .compact-table-container {
                height: 130px;
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

            .stat-value-grid {
                font-size: 10px !important;
            }

            .table-container,
            .compact-table-container {
                height: 120px;
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

            .stat-value-grid {
                font-size: 18px !important;
            }

            .table-container,
            .compact-table-container {
                height: 200px;
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

            .stat-value-grid {
                font-size: 12px !important;
            }

            .table-container,
            .compact-table-container {
                height: 110px;
            }
        }

        .stat-percentage {
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        /* Responsive table adjustments */
        @media (max-width: 768px) {
            .custom-table {
                font-size: 10px;
            }
            
            .custom-table th,
            .custom-table td {
                padding: 4px 3px;
            }

            .compact-table {
                font-size: 8px;
            }

            .compact-table th,
            .compact-table td {
                padding: 2px 1px;
            }
        }
    </style>

    <div class="content-body">
        <div style="margin: 15px;">

            <!-- ROW 1 - ALL 4 CARDS -->
            <div class="row">
                <!-- CARD 1 - Policy Information -->
                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                    <div class="card overflow-hidden" id="uw_card"
                        style="border-left: 8px solid #FFCF9F; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-12">
                                    <p
                                        style="margin:0px !important;padding:0px !important;font-size: 14px; font-weight: 600; margin-left:10px !important; margin-bottom: 10px !important; color:#000;">
                                        <i class="fas fa-file-contract card-header-icon" style="color: #FFCF9F;"></i>
                                        Policy Information
                                    </p>
                                </div>
                            </div>

                            <!-- NET PREMIUM FIRST (Large centered) -->
                            <div style="text-align: center; margin: 15px 0;">
                                <div style="font-size: 12px; color: #666; margin-bottom: 5px; text-align:left;">
                                    Net Premium
                                </div>
                                <div class="premium-main-value" style="font-size: 28px !important;">
                                    Rs. {{ $policy_info['net_premium'] ?? '0' }}
                                </div>
                            </div>

                            <hr style="margin: 15px 10px; border-color: #e0e0e0;">

                            <!-- Other Policy Information - COMPACT SIDE BY SIDE -->
                            <div style="margin: 0 10px;">

                                <!-- Insured -->
                                <div style="margin-bottom: 10px;">
                                    <div class="policy-label">Insured</div>
                                    <div class="policy-value" style="font-size: 13px !important;">
                                        {{ $policy_info['insured_name'] ?? 'N/A' }}
                                    </div>
                                </div>

                                <!-- Dates -->
                                <div class="row">
                                    <div class="col-6" style="padding-right: 5px;">
                                        <div class="policy-label">Issue Date</div>
                                        <div class="policy-value" style="font-size: 13px !important;">
                                            {{ $policy_info['issue_date'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="col-6" style="padding-left: 5px; border-left: 1px solid #e0e0e0;">
                                        <div class="policy-label">Expiry Date</div>
                                        <div class="policy-value" style="font-size: 13px !important;">
                                            {{ $policy_info['expiry_date'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <!-- CARD 2 - Lives Information -->
                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                    <div class="card overflow-hidden" id="uw_card"
                        style="border-left: 8px solid #9AD0F5; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-12">
                                    <p
                                        style="margin:0px !important;padding:0px !important;font-size: 14px; font-weight: 600; margin-left:10px !important; margin-bottom: 10px !important; color:#000;">
                                        <i class="fas fa-users card-header-icon" style="color: #9AD0F5;"></i>
                                        Lives Information
                                    </p>
                                </div>
                            </div>

                            <!-- Total Lives  -->
                            <div class="row" style="margin-left:10px !important; text-align:center;">
                                <div class="col-12">
                                    <div style="font-size: 12px; color: #666; margin-bottom: 5px; text-align:left;">
                                        Total Lives
                                    </div>
                                    <p class="premium-main-value">
                                        {{ $lives_info['total_lives'] ?? '0' }}
                                    </p>
                                </div>
                            </div>

                            <hr style="margin: 8px 0;">

                            <!-- Stats Grid -->
                            <div class="stats-grid">
                                <!-- Employees -->
                                <div class="stat-box">
                                    <div class="stat-label-grid">Employees</div>
                                    <div class="stat-value-grid">{{ $lives_info['employees'] ?? '0' }}</div>
                                </div>

                                <!-- Dependents -->
                                <div class="stat-box">
                                    <div class="stat-label-grid">Dependents</div>
                                    <div class="stat-value-grid">{{ $lives_info['dependents'] ?? '0' }}</div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- CARD 3 - O/S Collection -->
                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                    <div class="card overflow-hidden" id="uw_card"
                        style="border-left: 8px solid #9affc7; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <p style="margin:0px !important;padding:0px !important;font-size: 14px; font-weight: 600; margin-left:10px !important; margin-bottom: 10px !important; color:#000;">
                                        <i class="fas fa-chart-line card-header-icon" style="color: #9affc7;"></i>
                                        Outstanding (O/S) Collection
                                    </p>
                                </div>
                            </div>

                            <!-- Total Outstanding Collection -->
                            <div class="row" style="margin-left:10px !important; text-align:center;">
                                <div class="col-12">
                                    <div style="font-size: 12px; color: #666; margin-bottom: 5px; text-align:left;">
                                        Total Outstanding
                                    </div>
                                    <p class="premium-main-value">
                                        Rs. {{ $os_collection['total_collection'] ?? '0' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Calculation breakdown -->
                            <div style="margin-top: 10px; font-size: 11px; color: #666;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                    <span>Net Premium:</span>
                                    <span>Rs. {{ $os_collection['net_premium'] ?? '0' }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                    <span>Collected:</span>
                                    <span>Rs. {{ $os_collection['collected_amount'] ?? '0' }}</span>
                                </div>
                                <hr style="margin: 5px 0; border-color: #e0e0e0;">
                                <div style="display: flex; justify-content: space-between; font-weight: 600; color: #000;">
                                    <span>Outstanding:</span>
                                    <span>Rs. {{ $os_collection['total_collection'] ?? '0' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 4 - Claims -->
                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-6">
                    <div class="card overflow-hidden" id="uw_card"
                        style="border-left: 8px solid #ffb0c0; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <p
                                        style="margin:0px !important;padding:0px !important;font-size: 14px; font-weight: 600; margin-left:10px !important; margin-bottom: 10px !important; color:#000;">
                                        <i class="fas fa-file-invoice-dollar card-header-icon" style="color: #ffb0c0;"></i>
                                        Claims
                                    </p>
                                </div>
                            </div>

                            <!-- Total Claims -->
                            <div class="row" style="margin-left:10px !important; text-align:center;">
                                <div class="col-12">
                                    <div style="font-size: 12px; color: #666; margin-bottom: 5px; text-align:left;">
                                        Total Claims
                                    </div>
                                    <p class="premium-main-value">
                                        Rs. {{ $claims['total_claims'] ?? '0' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Additional stats can be added here if available -->
                            <div style="text-align: center; margin-top: 10px; color: #666;">
                                <p style="margin: 0; font-size: 12px; font-style: italic;">
                                    <!-- Additional details can be shown here -->
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 2 - Four Tables in Single Row -->
            <div class="row" style="margin-top: 15px;">
                
                <!-- Table 1: Loss Codes -->
                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-12">
                    <div class="card table-card">
                        <div class="card-body">
                            <div class="table-header">
                                <div style="display: flex; align-items: center;">
                                    <i class="fas fa-list-alt"></i>
                                    <b>Claims by Loss Code</b>
                                </div>
                            </div>
                            
                            <div class="compact-table-container">
                                <div class="compact-table-wrapper">
                                    @if(!empty($loss_codes))
                                    <table class="compact-table">
                                        <thead>
                                            <tr>
                                                <th>Loss Description</th>
                                                <th class="text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(array_slice($loss_codes, 0, 5) as $loss)
                                            <tr>
                                                <td>{{ $loss['POC_LOSSDESC'] ?? 'N/A' }}</td>
                                                <td class="text-right">
                                                    Rs. {{ isset($loss['TOT_CLM']) ? number_format((float) $loss['TOT_CLM']) : '0' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        @if(count($loss_codes) > 0)
                                        <tfoot>
                                            <tr>
                                                <td>Total</td>
                                                <td class="text-right">
                                                    Rs. {{ number_format(array_sum(array_column(array_slice($loss_codes, 0, 5), 'TOT_CLM'))) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                    @else
                                    <div class="no-data">
                                        <i class="fas fa-info-circle"></i>
                                        <p>No data available</p>
                                    </div>
                                    @endif
                                </div>
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
                
                <!-- Table 2: Top 5 Members -->
                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-12">
                    <div class="card table-card">
                        <div class="card-body">
                            <div class="table-header">
                                <div style="display: flex; align-items: center;">
                                    <i class="fas fa-user-friends"></i>
                                    <b>Top 5 Members</b>
                                </div>
                            </div>
                            
                            <div class="compact-table-container">
                                <div class="compact-table-wrapper">
                                    @if(!empty($top_members))
                                    <table class="compact-table">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Member</th>
                                                <th class="text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(array_slice($top_members, 0, 5) as $member)
                                            <tr>
                                                <td>{{ $member['GIH_MEMBER_CODE'] ?? 'N/A' }}</td>
                                                <td>{{ $member['GIH_MEMBER_NAME'] ?? 'N/A' }}</td>
                                                <td class="text-right">
                                                    Rs. {{ isset($member['TOT_CLM']) ? number_format((float) $member['TOT_CLM']) : '0' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        @if(count($top_members) > 0)
                                        <tfoot>
                                            <tr>
                                                <td colspan="2">Total</td>
                                                <td class="text-right">
                                                    Rs. {{ number_format(array_sum(array_column(array_slice($top_members, 0, 5), 'TOT_CLM'))) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                    @else
                                    <div class="no-data">
                                        <i class="fas fa-info-circle"></i>
                                        <p>No data available</p>
                                    </div>
                                    @endif
                                </div>
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
                
                <!-- Table 3: Department Claims -->
                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-12">
                    <div class="card table-card">
                        <div class="card-body">
                            <div class="table-header">
                                <div style="display: flex; align-items: center;">
                                    <i class="fas fa-building"></i>
                                    <b>Coming Soon</b>
                                </div>
                            </div>
                            
                            <div class="compact-table-container">
                                <div class="compact-table-wrapper">
                                    @if(!empty($department_claims))
                                    <table class="compact-table">
                                        <thead>
                                            <tr>
                                                <th>Department</th>
                                                <th class="text-right">Claims</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(array_slice($department_claims, 0, 5) as $dept)
                                            <tr>
                                                <td>{{ $dept['department'] ?? 'N/A' }}</td>
                                                <td class="text-right">
                                                    Rs. {{ isset($dept['claims']) ? number_format((float) $dept['claims']) : '0' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        @if(count($department_claims) > 0)
                                        <tfoot>
                                            <tr>
                                                <td>Total</td>
                                                <td class="text-right">
                                                    Rs. {{ number_format(array_sum(array_column(array_slice($department_claims, 0, 5), 'claims'))) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                    @else
                                    <div class="no-data">
                                        {{-- <i class="fas fa-info-circle"></i> --}}
                                        <p>No data available</p>
                                    </div>
                                    @endif
                                </div>
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
                
                <!-- Table 4: Monthly Trend -->
                <div class="col-xl-3 col-xxl-3 col-lg-6 col-sm-12">
                    <div class="card table-card">
                        <div class="card-body">
                            <div class="table-header">
                                <div style="display: flex; align-items: center;">
                                    <i class="fas fa-chart-line"></i>
                                    <b>Coming</b>
                                </div>
                            </div>
                            
                            <div class="compact-table-container">
                                <div class="compact-table-wrapper">
                                    @if(!empty($monthly_trend))
                                    <table class="compact-table">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th class="text-right">Claims</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(array_slice($monthly_trend, 0, 5) as $month)
                                            <tr>
                                                <td>{{ $month['month'] ?? 'N/A' }}</td>
                                                <td class="text-right">
                                                    Rs. {{ isset($month['claims']) ? number_format((float) $month['claims']) : '0' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        @if(count($monthly_trend) > 0)
                                        <tfoot>
                                            <tr>
                                                <td>Total</td>
                                                <td class="text-right">
                                                    Rs. {{ number_format(array_sum(array_column(array_slice($monthly_trend, 0, 5), 'claims'))) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                    @else
                                    <div class="no-data">
                                        {{-- <i class="fas fa-info-circle"></i> --}}
                                        <p>No data available</p>
                                    </div>
                                    @endif
                                </div>
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

        </div>

        <!-- Loading Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @endsection
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claims Summary</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
             background-color:#E6E9F1;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 400px; /* Fixed original size for all cards' parent */
            margin: 0; /* Remove auto-centering to align left */
        }
        .claims-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 8px; /* Further reduced padding */
            margin-bottom: 15px; /* Increased space between cards */
            width: 400px; /* Fixed width same as Claim Intimation for all cards (will cause horizontal scroll for side-by-side) */
            box-sizing: border-box;
            display: block; /* Ensure block-level for independence */
        }
        /* Independent chart sizing - fixed to fit within fixed card width without affecting other cards */
        #myPieChart, #myDonutChart, #myBarChart {
            max-width: 380px; /* Adjusted to fit within 400px card width independently */
            max-height: 140px; /* Further reduced max-height for charts */
            margin: 10px 0 0 0;
            width: 100% !important;
            height: auto !important;
        }
        .card-title {
            font-size: 16px; /* Further reduced font size */
            font-weight: 700;
            color: #333;
            text-align: left; 
            margin-bottom: 8px; /* Further reduced margin */
        }
        .current-year-figure {
            font-weight: bold;
        }
        .totals-box {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }
        .departments {
            display: flex;
            justify-content: space-between;
            flex-wrap: nowrap;
            margin-top: 6px; /* Further reduced margin */
            gap: 2px; /* Further reduced gap */
        }
        .dept-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }
        .dept-name {
            font-weight: normal; 
            color: #333;
            font-size: 10px; /* Further reduced font size */
        }
        .dept-counts {
            font-size: 9px; /* Further reduced font size */
            display: flex;
            flex-direction: column;
            align-items: center;
            font-weight: normal; 
        }
        /* Side-by-side row with fixed independent card sizing (each 400px, allowing horizontal scroll if needed) */
        .charts-row {
            display: flex;
            justify-content: flex-start; /* Align left, allow overflow for independence */
            width: auto; /* Allow wider than container for side-by-side without compression */
            margin: 0;
            gap: 15px; /* Space between the two chart cards */
        }
        .charts-row .claims-card {
            flex-shrink: 0; /* Prevent shrinking, maintain fixed 400px width independently */
            margin-bottom: 0; /* Remove bottom margin for row cards */
        }
        /* Ensure first card is completely independent */
        .container > .claims-card:first-of-type {
            width: 400px; /* Explicit fixed width */
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        @php
            $deptNames = [
                '11' => 'Fire',
                '12' => 'Marine',
                '13' => 'Motor',
                '14' => 'Misc',
                '16' => 'Health',
            ];

            $currentYearTotal = $apiData['totals']['CURRENT_YEAR_COUNT'] ?? 0;
            $lastYearTotal = $apiData['totals']['LAST_YEAR_COUNT'] ?? 0;

            $deptLookup = [];
            foreach ($apiData['departments'] ?? [] as $dept) {
                $code = (string) ($dept['PDP_DEPT_CODE'] ?? '');
                if ($code) {
                    $deptLookup[$code] = [
                        'current' => $dept['CURRENT_YEAR_COUNT'] ?? 0,
                        'last' => $dept['LAST_YEAR_COUNT'] ?? 0,
                    ];
                }
            }

            $monthwiseData = $apiData['monthwise'] ?? [];
            $currentYearMonthCounts = array_column($monthwiseData, 'CURRENT_YEAR_COUNT', 'MONTH');
            $lastYearMonthCounts = array_column($monthwiseData, 'LAST_YEAR_COUNT', 'MONTH');
        @endphp

        <!-- Summary Card (Fully independent, fixed 400px width) -->
        <div class="claims-card">
            <div class="card-title">Claim Intimation</div>
            
            <!-- Totals -->
            <div class="totals-box">
                <div>Current Year: <span class="current-year-figure">{{ number_format($currentYearTotal) }}</span></div>
                <div>Last Year: <span>{{ number_format($lastYearTotal) }}</span></div>
            </div>

            <!-- Department Breakdown -->
            <div class="departments">
                @foreach($deptNames as $code => $name)
                    @php
                        $currentCount = $deptLookup[$code]['current'] ?? 0;
                        $lastCount    = $deptLookup[$code]['last'] ?? 0;
                    @endphp
                    <div class="dept-container">
                        <span class="dept-name">{{ $name }}</span>
                        <div class="dept-counts">
                            <span>{{ number_format($currentCount) }}</span>
                            <span>{{ number_format($lastCount) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Side-by-side charts row (Independent from first card, each card fixed 400px width) -->
        <div class="charts-row">
            <!-- Bar Chart Card (Independent fixed size) -->
            <div class="claims-card">
                <div class="card-title">Month-wise Claims</div>
                <canvas id="myBarChart"></canvas>
            </div>

            <!-- Donut Chart Card (Independent fixed size) -->
            <div class="claims-card">
                <div class="card-title">Claim Distribution (Dept)</div>
                <canvas id="myDonutChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const donutCtx = document.getElementById('myDonutChart').getContext('2d');

        const donutData = {
            labels: ['Fire', 'Marine', 'Motor', 'Misc', 'Health'],
            datasets: [{
                label: 'Current Year Claims',
                data: [
                    {{ $deptLookup['11']['current'] ?? 0 }},
                    {{ $deptLookup['12']['current'] ?? 0 }},
                    {{ $deptLookup['13']['current'] ?? 0 }},
                    {{ $deptLookup['14']['current'] ?? 0 }},
                    {{ $deptLookup['16']['current'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                ],
            }]
        };

        const myDonutChart = new Chart(donutCtx, {
            type: 'doughnut',
            data: donutData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom', // Adjusted to bottom to save space
                        labels: {
                            padding: 5,
                            font: {
                                size: 9
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            },
        });

        const barCtx = document.getElementById('myBarChart').getContext('2d');
        const barData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
            datasets: [
                {
                    label: 'Current Year',
                    data: [
                        {{ $currentYearMonthCounts['JAN'] ?? 0 }},
                        {{ $currentYearMonthCounts['FEB'] ?? 0 }},
                        {{ $currentYearMonthCounts['MAR'] ?? 0 }},
                        {{ $currentYearMonthCounts['APR'] ?? 0 }},
                        {{ $currentYearMonthCounts['MAY'] ?? 0 }},
                        {{ $currentYearMonthCounts['JUN'] ?? 0 }},
                        {{ $currentYearMonthCounts['JUL'] ?? 0 }},
                        {{ $currentYearMonthCounts['AUG'] ?? 0 }},
                        {{ $currentYearMonthCounts['SEP'] ?? 0 }}
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                },
                {
                    label: 'Last Year',
                    data: [
                        {{ $lastYearMonthCounts['JAN'] ?? 0 }},
                        {{ $lastYearMonthCounts['FEB'] ?? 0 }},
                        {{ $lastYearMonthCounts['MAR'] ?? 0 }},
                        {{ $lastYearMonthCounts['APR'] ?? 0 }},
                        {{ $lastYearMonthCounts['MAY'] ?? 0 }},
                        {{ $lastYearMonthCounts['JUN'] ?? 0 }},
                        {{ $lastYearMonthCounts['JUL'] ?? 0 }},
                        {{ $lastYearMonthCounts['AUG'] ?? 0 }},
                        {{ $lastYearMonthCounts['SEP'] ?? 0 }}
                    ],
                    backgroundColor: 'rgba(255, 99, 132, 0.6)'
                }
            ]
        };

        const myBarChart = new Chart(barCtx, {
            type: 'bar',
            data: barData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 8
                            },
                            maxTicksLimit: 5
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 8
                            },
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 9
                            },
                            padding: 5
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
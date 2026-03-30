<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClaimDBHelper
{
    public static function getClaimS1(
        $startDate = null,
        $endDate = null,
        $dept = 'All',
        $branch = '0',
        $takaful = '60107'
    ) {
        // Set default dates
        if (!$startDate) {
            $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = Carbon::now()->format('Y-m-d');
        }

        // Format dates
        $dateFrom = Carbon::parse($startDate)->format('d-M-y');
        $dateTo = Carbon::parse($endDate)->format('d-M-y');

        // Build query parameters
        $params = [
            'datefrom' => $dateFrom,
            'dateto' => $dateTo,
            'dept' => $dept,
            'branch' => $branch,
            'takaful' => $takaful,
        ];

        // API URL
        $apiUrl = "http://172.16.22.204/dashboardApi/branch_portal/reports/clm/getClmStats.php?" . http_build_query($params);
        
        

        // Call API with cache-busting headers
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 120,
                'header' => "Cache-Control: no-cache\r\n" .
                           "Pragma: no-cache\r\n"
            ],
        ]);

        $response = @file_get_contents($apiUrl, false, $context);

        if ($response === false) {
            Log::error('API Request Failed');
            return [
                'status' => 'error',
                'message' => 'API Request Failed',
                'data' => null
            ];
        }

        $decodedResponse = json_decode($response, true);
        
        return [
            'status' => 'success',
            'data' => $decodedResponse,
        ];
    }
}
<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClaimHelper
{
    public static function getClaim(
        $startDate = null,
        $endDate = null,
        $dept = 'All',
        $branch = 'All',
        $takaful = 'All'
    ) {
        // Set default start and end dates if not provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
        }

        $dateFrom = Carbon::parse($startDate)->format('d-M-Y');
        $dateTo = Carbon::parse($endDate)->format('d-M-Y');

        // Log parameters
        Log::info('ClaimHelper Parameters:', [
            'datefrom' => $dateFrom,
            'dateto' => $dateTo,
            'dept' => $dept,
            'branch' => $branch,
            'takaful' => $takaful,
        ]);

        // Build query parameters dynamically
        $params = [
            'datefrom' => $dateFrom,
            'dateto' => $dateTo,
            'dept' => $dept,
            'branch' => $branch,
            'takaful' => $takaful,
        ];

        // Construct the API URL
        $apiUrl = "http://172.16.22.204/dashboardApi/clm/getIntiClm.php?" . http_build_query($params);
        Log::info('API URL: ' . $apiUrl);

        // Call the API
        $response = @file_get_contents($apiUrl, false, stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 120,
            ],
        ]));

        if ($response === false) {
            Log::error('API Request Failed for URL: ' . $apiUrl);
            return [
                'status' => 'error',
                'message' => 'API Request Failed',
            ];
        }

        Log::info('Raw API Response: ' . substr($response, 0, 1000));

        // Decode JSON
        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON Decode Error: ' . json_last_error_msg() . ' for response: ' . substr($response, 0, 500));
            return [
                'status' => 'error',
                'message' => 'Invalid JSON response from API',
                'raw_response' => substr($response, 0, 500),
            ];
        }

        return [
            'status' => 'success',
            'data' => $decodedResponse,
            'api_url' => $apiUrl,
            'datefrom' => $dateFrom,
            'dateto' => $dateTo,
        ];
    }

    public static function getClaimR2(
        $startDate = null,
        $endDate = null,
        $dept = 'All',
        $branch = 'All',
        $takaful = 'All',
        $insu = ['All']
    ) {
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
        }

        $dateFrom = Carbon::parse($startDate)->format('d-M-Y');
        $dateTo = Carbon::parse($endDate)->format('d-M-Y');

        if (is_array($insu)) {
            $insu = empty($insu) ? 'All' : implode(',', $insu);
        }

        $apiUrl = "http://172.16.22.204/dashboardApi/clm/getOsSurvClm.php?" .
            "datefrom={$dateFrom}&" .
            "dateto={$dateTo}&" .
            "dept={$dept}&" .
            "branch={$branch}&" .
            "takaful={$takaful}&" .
            "insu={$insu}";

        $response = @file_get_contents($apiUrl, false, stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 120,
            ],
        ]));

        if ($response === false) {
            return [
                'status' => 'error',
                'message' => 'API Request Failed',
            ];
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => 'error',
                'message' => 'Invalid JSON response from API',
                'raw_response' => substr($response, 0, 500),
            ];
        }

        return [
            'status' => 'success',
            'data' => $decodedResponse,
            'api_url' => $apiUrl,
            'datefrom' => $dateFrom,
            'dateto' => $dateTo,
        ];
    }

    public static function getClaimR3(
        $startDate = null,
        $endDate = null,
        $dept = 'All',
        $branch = 'All',
        $takaful = 'All',
        $insu = ['All']
    ) {
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
        }

        $dateFrom = Carbon::parse($startDate)->format('d-M-Y');
        $dateTo = Carbon::parse($endDate)->format('d-M-Y');

        if (is_array($insu)) {
            $insu = empty($insu) ? 'All' : implode(',', $insu);
        }

        $apiUrl = "http://172.16.22.204/dashboardApi/clm/getOsRepClm.php?" .
            "datefrom={$dateFrom}&" .
            "dateto={$dateTo}&" .
            "dept={$dept}&" .
            "branch={$branch}&" .
            "takaful={$takaful}&" .
            "insu={$insu}";

        $response = @file_get_contents($apiUrl, false, stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 120,
            ],
        ]));

        if ($response === false) {
            return [
                'status' => 'error',
                'message' => 'API Request Failed',
            ];
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => 'error',
                'message' => 'Invalid JSON response from API',
                'raw_response' => substr($response, 0, 500),
            ];
        }

        return [
            'status' => 'success',
            'data' => $decodedResponse,
            'api_url' => $apiUrl,
            'datefrom' => $dateFrom,
            'dateto' => $dateTo,
        ];
    }

    public static function getClaimR4(
        $startDate = null,
        $endDate = null,
        $dept = 'All',
        $branch = 'All',
        $takaful = 'All',
        $insu = ['All']
    ) {
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
        }

        $dateFrom = Carbon::parse($startDate)->format('d-M-Y');
        $dateTo = Carbon::parse($endDate)->format('d-M-Y');

        if (is_array($insu)) {
            $insu = empty($insu) ? 'All' : implode(',', $insu);
        }

        $apiUrl = "http://172.16.22.204/dashboardApi/clm/getOsStlClm.php?" .
            "datefrom={$dateFrom}&" .
            "dateto={$dateTo}&" .
            "dept={$dept}&" .
            "branch={$branch}&" .
            "takaful={$takaful}&" .
            "insu={$insu}";

        $response = @file_get_contents($apiUrl, false, stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 120,
            ],
        ]));

        if ($response === false) {
            return [
                'status' => 'error',
                'message' => 'API Request Failed',
            ];
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => 'error',
                'message' => 'Invalid JSON response from API',
                'raw_response' => substr($response, 0, 500),
            ];
        }

        return [
            'status' => 'success',
            'data' => $decodedResponse,
            'api_url' => $apiUrl,
            'datefrom' => $dateFrom,
            'dateto' => $dateTo,
        ];
    }

     public static function getClaimR5(
        $startDate = null,
        $endDate = null,
        $dept = 'All',
        $branch = 'All',
        $takaful = 'All',
        $insu = ['All']
    ) {
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
        }

        $dateFrom = Carbon::parse($startDate)->format('d-M-Y');
        $dateTo = Carbon::parse($endDate)->format('d-M-Y');

        if (is_array($insu)) {
            $insu = empty($insu) ? 'All' : implode(',', $insu);
        }

        $apiUrl = "http://172.16.22.204/dashboardApi/clm/getStlClm.php?" .
            "datefrom={$dateFrom}&" .
            "dateto={$dateTo}&" .
            "dept={$dept}&" .
            "branch={$branch}&" .
            "takaful={$takaful}&" .
            "insu={$insu}";
        //dd($apiUrl);
        $response = @file_get_contents($apiUrl, false, stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 120,
            ],
        ]));

        if ($response === false) {
            return [
                'status' => 'error',
                'message' => 'API Request Failed',
            ];
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => 'error',
                'message' => 'Invalid JSON response from API',
                'raw_response' => substr($response, 0, 500),
            ];
        }

        return [
            'status' => 'success',
            'data' => $decodedResponse,
            'api_url' => $apiUrl,
            'datefrom' => $dateFrom,
            'dateto' => $dateTo,
        ];
    }
    //      public static function getClaimR6(
    //     $startDate = null,
    //     $endDate = null,
    //     $dept = 'All',
    //     $branch = 'All',
    //     $takaful = 'All'
       
    // ) {
    //     if (!$startDate || !$endDate) {
    //         $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
    //         $endDate = Carbon::now()->format('Y-m-d');
    //     }

    //     $dateFrom = Carbon::parse($startDate)->format('d-M-Y');
    //     $dateTo = Carbon::parse($endDate)->format('d-M-Y');

       

    //     $apiUrl = "http://172.16.22.204/dashboardApi/branch_portal/reports/clm/getIntiClm.php?" .
    //         "datefrom={$dateFrom}&" .
    //         "dateto={$dateTo}&" .
    //         "dept={$dept}&" .
    //         "branch={$branch}&" .
    //         "takaful={$takaful}&" .
    //     //dd($apiUrl);
    //     $response = @file_get_contents($apiUrl, false, stream_context_create([
    //         'http' => [
    //             'ignore_errors' => true,
    //             'timeout' => 120,
    //         ],
    //     ]));

    //     if ($response === false) {
    //         return [
    //             'status' => 'error',
    //             'message' => 'API Request Failed',
    //         ];
    //     }

    //     $decodedResponse = json_decode($response, true);

    //     if (json_last_error() !== JSON_ERROR_NONE) {
    //         return [
    //             'status' => 'error',
    //             'message' => 'Invalid JSON response from API',
    //             'raw_response' => substr($response, 0, 500),
    //         ];
    //     }

    //     return [
    //         'status' => 'success',
    //         'data' => $decodedResponse,
    //         'api_url' => $apiUrl,
    //         'datefrom' => $dateFrom,
    //         'dateto' => $dateTo,
    //     ];
    // }
    // before addding all . takaful

    // public static function getClaimR6(
    //             $startDate = null,
    //             $endDate = null,
    //             $dept = 'All',
    //             $branch = 'All',
    //             $takaful = 'All'
    //         ) {
    //             // Set default start and end dates if not provided
    //             if (!$startDate || !$endDate) {
    //                 $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
    //                 $endDate = Carbon::now()->format('Y-m-d');
    //             }

    //             $dateFrom = Carbon::parse($startDate)->format('d-M-Y');
    //             $dateTo = Carbon::parse($endDate)->format('d-M-Y');

                

    //             // Build query parameters dynamically
    //             $params = [
    //                 'datefrom' => $dateFrom,
    //                 'dateto' => $dateTo,
    //                 'dept' => $dept,
    //                 'branch' => $branch,
    //                 'takaful' => $takaful,
    //             ];

    //             // Construct the API URL
    //             $apiUrl = "http://172.16.22.204/dashboardApi/branch_portal/reports/clm/getIntiClm.php?" . http_build_query($params);
                
    //             // Call the API
    //             $response = @file_get_contents($apiUrl, false, stream_context_create([
    //                 'http' => [
    //                     'ignore_errors' => true,
    //                     'timeout' => 120,
    //                 ],
    //             ]));

            
    //             // Decode JSON
    //             $decodedResponse = json_decode($response, true);
    //             //          echo "<pre>";
    //             // print_r($decodedResponse);
    //             // echo "</pre>";
    //             // exit;

            
    //             return [
    //                 'status' => 'success',
    //                 'data' => $decodedResponse,
    //                 'api_url' => $apiUrl,
    //                 'datefrom' => $dateFrom,
    //                 'dateto' => $dateTo,
    //             ];
    //         }

        public static function getClaimR6(
        $startDate = null,
        $endDate = null,
        $dept = 'All',
        $branch = 'All',
        $takaful = 'All',
        $insu = ['All']
    ) {
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
        }

        $dateFrom = Carbon::parse($startDate)->format('d-M-Y');
        $dateTo = Carbon::parse($endDate)->format('d-M-Y');

        if (is_array($insu)) {
            $insu = empty($insu) ? 'All' : implode(',', $insu);
        }

        $apiUrl = "http://172.16.22.204/dashboardApi/branch_portal/reports/clm/getIntiClm.php?" .
            "datefrom={$dateFrom}&" .
            "dateto={$dateTo}&" .
            "dept={$dept}&" .
            "branch={$branch}&" .
            "takaful={$takaful}&" .
            "insu={$insu}";
         //dd($apiUrl);

        $response = @file_get_contents($apiUrl, false, stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 120,
            ],
        ]));

        if ($response === false) {
            return [
                'status' => 'error',
                'message' => 'API Request Failed',
            ];
        }

        $decodedResponse = json_decode($response, true);
         
             
                //          echo "<pre>";
                // print_r($decodedResponse);
                // echo "</pre>";
                // exit;

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => 'error',
                'message' => 'Invalid JSON response from API',
                'raw_response' => substr($response, 0, 500),
            ];
        }

        return [
            'status' => 'success',
            'data' => $decodedResponse,
            'api_url' => $apiUrl,
            'datefrom' => $dateFrom,
            'dateto' => $dateTo,
        ];
    }
}
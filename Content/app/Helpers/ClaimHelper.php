<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClaimHelper
{
    
         public static function getClaimR11(
        $startDate = null,
        $endDate = null,
        $dept = 'All',
        $branch = 'All',
        $takaful = 'All',
        $insu = ['All'],
        $userZone = 'All'
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

        $apiUrl = "http://172.16.22.204/dashboardApi/clm/getOsStlClm_GL2.php?".
            "datefrom={$dateFrom}&" .
            "dateto={$dateTo}&" .
            "dept={$dept}&" .
            "branch={$branch}&" .
            "takaful={$takaful}&" .
            "insu={$insu}&" .
            "zone={$userZone}";
            
            //  return $apiUrl;
           // dd( $apiUrl);
           

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
                //  echo "<pre>";
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
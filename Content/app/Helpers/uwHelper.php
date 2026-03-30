<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class Helper
{
    public static function fetchRenewalData($startDate = null, $endDate = null, $dept = null) 
    {
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->format('Y-m-d');
            $endDate = Carbon::now()->addDays(30)->format('Y-m-d');
        }

        $user   = Session::get('user');
        $brConv = $user['loc_code'] ?? null;
        $brTak  = $user['loc_code_tak'] ?? null;

        $formattedStart = Carbon::parse($startDate)->format('d-M-Y');
        $formattedEnd   = Carbon::parse($endDate)->format('d-M-Y');

        $apiUrl = "http://172.16.22.204/dashboardApi/uw/renewal.php"
            . "?expiryfrom={$formattedStart}&expiryto={$formattedEnd}"
            . "&dept={$dept}&branch={$brConv}&takaful={$brTak}";

        $response = @file_get_contents($apiUrl, false, stream_context_create([
            'http' => ['ignore_errors' => true, 'timeout' => 120]
        ]));

        if ($response === false) {
            return ['error' => 'API request failed', 'data' => []];
        }

        $cleanResponse = preg_replace('/<br \/>\n<b>Notice<\/b>:.*?<br \/>\n/', '', $response);
        $apiResponse   = json_decode($cleanResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON: ' . json_last_error_msg(), 'data' => []];
        }

        return [
            'data'   => $apiResponse['data'] ?? [],
            'status' => $apiResponse['status'] ?? 'success',
        ];
    }
}
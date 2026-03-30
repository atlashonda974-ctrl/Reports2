<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ClaimDBHelper;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\BranchesList;
class ClaimDBController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        $allParams = $request->all();

       
        $result = ClaimDBHelper::getClaimS1($allParams);
        $result1 = ClaimDBHelper::getClaimS2($allParams);
        $result2 = ClaimDBHelper::getClaimS3($allParams);
        $result3 = ClaimDBHelper::getClaimS4($allParams);
        $result4 = ClaimDBHelper::getClaimS5($allParams);

        // dd($result4);
        $combinedData = [
            'Surveyor' => [],
            'Report' => [],
            'Stl' => [],
        ];

        foreach ($result3['data'] as $key => $data) {
            foreach ($data as $item) {
                $ageSegment = $item['AGE_SEGMENT'];
                $count = (int)$item['SEGMENT_COUNT'];
                $combinedData[ucfirst(str_replace('os_', '', $key))][$ageSegment] = $count;
            }
        }

     
        $monthwiseData = $result['data']['monthwise'] ?? [];
        $currentYearMonthCounts = array_column($monthwiseData, 'CURRENT_YEAR_COUNT', 'MONTH');
        $lastYearMonthCounts = array_column($monthwiseData, 'LAST_YEAR_COUNT', 'MONTH');

  
        $settledMonthwiseData = [];
        $settledCurrentYearMonthCounts = [];
        $settledLastYearMonthCounts = [];

        if (isset($result1['data']) && is_array($result1['data'])) {
           
            if (isset($result1['data']['monthwise'])) {
                $settledMonthwiseData = $result1['data']['monthwise'];
                $settledCurrentYearMonthCounts = array_column($settledMonthwiseData, 'CURRENT_YEAR_COUNT', 'MONTH');
                $settledLastYearMonthCounts = array_column($settledMonthwiseData, 'LAST_YEAR_COUNT', 'MONTH');
            }
           
            elseif (isset($result1['data']['monthwise_data'])) {
                $settledMonthwiseData = $result1['data']['monthwise_data'];
                $settledCurrentYearMonthCounts = array_column($settledMonthwiseData, 'CURRENT_YEAR_COUNT', 'MONTH');
                $settledLastYearMonthCounts = array_column($settledMonthwiseData, 'LAST_YEAR_COUNT', 'MONTH');
            }
        }

        return response()->view('claimregister.claim7', [
            'apiData' => $result['data'],
            'apiData2' => $result2['data'],
            'combinedData' => $combinedData,
            'apiStatus' => $result1,
            'apiBIStatus' => $result4,
            'apiUrlUsed' => $result['api_url_used'] ?? null,
          
            'currentYearMonthCounts' => $currentYearMonthCounts,
            'lastYearMonthCounts' => $lastYearMonthCounts,
           
            'settledCurrentYearMonthCounts' => $settledCurrentYearMonthCounts,
            'settledLastYearMonthCounts' => $settledLastYearMonthCounts,
        ]);
    }

     public function claimcase(Request $request)
{
    // Debug: Log all incoming request data
    Log::info('Request Inputs:', $request->all());

    // 1. Get dates from request - support both start_date and from_date
    $startDate = $request->input('start_date') ?? $request->input('from_date');
    $endDate = $request->input('end_date') ?? $request->input('to_date');

    // Set default dates if not provided
    if ($startDate && $endDate) {
        $formStartDate = Carbon::parse($startDate)->format('Y-m-d');
        $formEndDate = Carbon::parse($endDate)->format('Y-m-d');
    } else {
        $formStartDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $formEndDate = Carbon::now()->format('Y-m-d');
    }

    // 2. Define additional parameters from request or set defaults
    $branchCode = $request->input('location_category', '');
    
    // 3. Get branch code and takaful code based on selected branch
    $branch = $branchCode ?: 'All';
    $takaful = 'All';
    
    if ($branch !== 'All' && !empty($branchCode)) {
        $selectedBranch = BranchesList::where('fbracode', $branchCode)->first();
        if ($selectedBranch) {
            $takaful = $selectedBranch->fbratak;
        }
    }

    // 4. Map new_category to department code
    $dept = 'All';
    $categoryMapping = [
        'Fire' => 11,
        'Marine' => 12,
        'Motor' => 13,
        'Miscellaneous' => 14,
        'Health' => 16,
    ];
    
    $selectedCategory = $request->input('new_category', '');
    
    if (!empty($selectedCategory)) {
        Log::info('Selected Category: ' . $selectedCategory);
        if (isset($categoryMapping[$selectedCategory])) {
            $dept = $categoryMapping[$selectedCategory];
            Log::info('Mapped Department Code: ' . $dept);
        } else {
            Log::warning('Invalid Category Selected: ' . $selectedCategory);
        }
    }

    // 5. Get insurance type filter - only D or I
    $insuranceType = $request->input('insurance_type', 'All');

    // Format insurance type for API
    $insuParam = 'All';
    if ($insuranceType !== 'All') {
        $insuParam = $insuranceType;
    }

    // Debug: Log all parameters before API call
    Log::info('API Call Parameters:', [
        'start_date' => $formStartDate,
        'end_date' => $formEndDate,
        'dept' => $dept,
        'branch' => $branch,
        'takaful' => $takaful,
        'insu' => $insuParam,
    ]);

    // 6. Get data from API using helper
    $result = ClaimDBHelper::getClaim($formStartDate, $formEndDate, $dept, $branch, $takaful, $insuParam);
    
    // 7. Process the data for the view
    $data = [];
    
    if ($result['status'] === 'success' && isset($result['data'])) {
        $apiData = $result['data'];
        
        if (is_array($apiData)) {
            foreach ($apiData as $item) {
                if (empty($item) || $item === false) {
                    continue;
                }
                
                if (is_string($item)) {
                    $decodedItem = json_decode($item, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedItem)) {
                        $data[] = (object) $decodedItem;
                    }
                } elseif (is_array($item) || is_object($item)) {
                    $data[] = (object) $item;
                }
            }
        }
        
        Log::info('Processed ' . count($data) . ' records');
    } else {
        Log::error('Claim API Error: ' . ($result['message'] ?? 'Unknown error'));
    }
    
    // 8. Return the view with all required data
    return view('claimregister.claimcase', [
        'data' => $data,
        'start_date' => $formStartDate,
        'end_date' => $formEndDate,
        'selected_category' => $selectedCategory,
        'branches' => BranchesList::all(),
        'selected_insurance_type' => $insuranceType,
        'error_message' => $result['status'] === 'error' ? ($result['message'] ?? 'API Error') : null,
    ]);
}
}
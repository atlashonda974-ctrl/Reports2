<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
use Carbon\Carbon;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use App\Models\BranchesList;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ReinsLog;
use App\Models\EmailLog;
use App\Models\VerifyLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReinsuranceRequestEmail;

class RequestNoteController extends Controller

{

public function index(Request $request)
    {
        // Get user-submitted parameters
        $userStartDate = $request->query('start_date');
        $userEndDate = $request->query('end_date');
        $req_note = $request->query('req_note');
        $documentNumber = $request->query('uw_doc');

        // Set default dates from today to next 30 days
    if (!$userStartDate || !$userEndDate) {
        $userStartDate = Carbon::now()->format('Y-m-d');
        $userEndDate = Carbon::now()->addDays(30)->format('Y-m-d');
    }
          
    
        // Fetch data
        $result = Helper::fetchReinsuranceNotesData($userStartDate, $userEndDate, $req_note);
       //dd($result);
        // Handle API errors
        if ($result['status'] === 'error') {
            return response()->json($result, 500);
        }

        // Convert to collection
        $data = collect($result['data'] ?? []);
       // dd($data->values()->get());
        //dd($data->values()->get(93));

        //dd($request->all());
        // Format API date range for display
           // Format API date range
    $apiDateRangeFrom = Carbon::createFromFormat('d-M-Y', $result['api_expiry_from']);
    $apiDateRangeTo = Carbon::createFromFormat('d-M-Y', $result['api_expiry_to']);

    // Use either user dates or API fallback
    $defaultStartDate = $userStartDate ?? $apiDateRangeFrom->format('Y-m-d');
    $defaultEndDate = $userEndDate ?? $apiDateRangeTo->format('Y-m-d');



        // Filter by document number if provided
        if ($request->filled('uw_doc')) {
            $documentNumber = $request->query('uw_doc');
            $filteredNotes = Helper::getRequestNotesByDocument($documentNumber);
           // return $filteredNotes;
            
        
            
            if (!empty($filteredNotes)) {
                $data = $data->filter(function($item) use ($filteredNotes) {
                    return in_array(strtoupper($item['GRH_REFERENCE_NO'] ?? ''), 
                                 array_map('strtoupper', $filteredNotes));
                })->values();
            } else {
                $data = collect();
            }
        }

        // Filter by reins party if provided
        if ($request->filled('reins_party')) {
            $data = $data->filter(function($item) use ($request) {
                return ($item['RE_COMP_DESC'] ?? '') === $request->reins_party;
            });
        }

        // Filter by category if provided
        $categoryMapping = [
            'Fire' => 11,
            'Marine' => 12,
            'Motor' => 13,
            'Miscellaneous' => 14,
            'Health' => 16,
        ];

        if ($request->filled('new_category')) {
            $selectedCategory = $request->new_category;
            if ($deptCode = $categoryMapping[$selectedCategory] ?? null) {
                $data = $data->filter(function($item) use ($deptCode) {
                    return Str::startsWith($item['PDP_DEPT_CODE'] ?? '', (string)$deptCode);
                });
            }
        }
        // Filter by CP_STS if provided
        if ($request->filled('cp_sts')) {
            $cpStsValue = strtolower($request->cp_sts); // yes or no
            $data = $data->filter(function ($item) use ($cpStsValue) {
                $value = strtolower($item['CP_STS'] ?? '');
                return $cpStsValue === 'yes' ? $value === 'yes' : $value !== 'yes';
            });
        }
        


        return view('RequestNote.index', [
            'data' => $data,
            'start_date' => $defaultStartDate,
            'end_date' => $defaultEndDate,
            'reinsParties' => $data->pluck('RE_COMP_DESC')->unique()->values()->toArray(),
            'api_date_range' => [
                'from' => $apiDateRangeFrom->format('d-M-Y'),
                'to' => $apiDateRangeTo->format('d-M-Y')
            ],
            'total_records' => count($result['data']),
            'filtered_records' => $data->count()
        ]);
    }



public function getReinsuranceR1Data(Request $request)
    {
        // 1. Get dates from request or set defaults
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

       $formStartDate = $startDate ?? Carbon::now()->subDays(30)->format('Y-m-d');
       $formEndDate   = $endDate ?? Carbon::now()->format('Y-m-d');

        // 2. Get data from helper
        $result = Helper::getReinsuranceR1Report($formStartDate, $formEndDate);
        $data = collect($result['data'] ?? []);

       // dd(  $data);

        // Debug: Log sample data structure
        Log::info('Sample Data Structure:', $data->take(5)->toArray());

        // 3. Fetch reqnote values from emaillogs
        $insertedDocs = EmailLog::pluck('reqnote')->toArray();

        // Debug: Log insertedDocs
        Log::info('Inserted Docs (emaillogs reqnote):', ['insertedDocs' => $insertedDocs]);

        // 4. Filter out records that exist in emaillogs
        $data = $data->filter(function ($record) use ($insertedDocs) {
            $docReference = $record->GRH_REFERENCE_NO ?? '';
            $isExcluded = in_array($docReference, $insertedDocs);
            // Debug: Log filtering details
            Log::debug('Filtering record', [
                'GRH_REFERENCE_NO' => $docReference,
                'isExcluded' => $isExcluded
            ]);
            return !$isExcluded;
        });

        // Debug: Log filtered data count
        Log::info('Filtered Data Count:', ['count' => $data->count()]);

        // 5. Format dates for display
        $apiDateFrom = Carbon::parse($formStartDate)->format('d-M-Y');
        $apiDateTo = Carbon::parse($formEndDate)->format('d-M-Y');

        // 6. Category filtering
        $categoryMapping = [
            'Fire' => 11,
            'Marine' => 12,
            'Motor' => 13,
            'Miscellaneous' => 14,
            'Health' => 16,
        ];

        if ($request->filled('new_category')) {
            $selectedCategory = $request->new_category;
            if ($deptCode = $categoryMapping[$selectedCategory] ?? null) {
                $data = $data->filter(function ($item) use ($deptCode) {
                    return Str::startsWith($item['PDP_DEPT_CODE'] ?? '', (string) $deptCode);
                });
            }
        }

        // 7. Branch filtering
        if ($request->filled('location_category')) {
            $selectedLocation = $request->location_category;
            $data = $data->filter(function ($item) use ($selectedLocation) {
                return Str::contains($item['PLC_LOCADESC'], $selectedLocation);
            });
        }

        // 8. Extract unique categories
        $uniqueCategories = $data->pluck('PLC_LOCADESC')->filter()->unique()->sort()->values();

        // 9. Branch info
        $branchCode = $request->input('location_category');
        $matchedBranch = BranchesList::where('fbracode', $branchCode)->first();
        $branches = $matchedBranch ? collect([$matchedBranch]) : BranchesList::all();

        // 10. Filter not posted records AFTER filters
        $notPostedRecords = $data->where('GRH_POSTINGTAG', '!=', 'Y');

        // 11. Group aging buckets AFTER filters
        $now = Carbon::now();
        $agingBuckets = [
            '0-3 Days'     => [0, 3],
            '4-7 Days'     => [4, 7],
            '8-10 Days'    => [8, 10],
            '11-15 Days'   => [11, 15],
            '16-20 Days'   => [16, 20],
            '20+ Days'     => [21, PHP_INT_MAX],
        ];

        // $groupedByAging = [];
        // foreach ($agingBuckets as $label => [$min, $max]) {
        //     $groupedByAging[$label] = $notPostedRecords->filter(function ($item) use ($now, $min, $max) {
        //         $date = Carbon::parse($item->GRH_DOCUMENTDATE ?? null);
        //         $diff = $date->diffInDays($now);
        //         return $diff >= $min && $diff <= $max;
        //     });
        // }
        $groupedByAging = [];
    foreach ($agingBuckets as $label => [$min, $max]) {
        // Use $data (total records) instead of $notPostedRecords
        $groupedByAging[$label] = $data->filter(function ($item) use ($now, $min, $max) {
            $date = Carbon::parse($item->GRH_DOCUMENTDATE ?? null);
            $diff = $date->diffInDays($now);
            return $diff >= $min && $diff <= $max;
        });
    }

        // 12. Calculate sum insured for all records and not posted records
        $totalSumInsured = $data->sum(function($item) {
            return (float)($item->CED_SI ?? 0);
        });

        $notPostedSumInsured = $notPostedRecords->sum(function($item) {
            return (float)($item->CED_SI ?? 0);
        });

        // 13. Return view
        return view('GetRequestReports.r1', [
            'data' => $data,
            'start_date' => $formStartDate,
            'end_date' => $formEndDate,
            'uniqueCategories' => $uniqueCategories,
            'api_date_range' => [
                'from' => $apiDateFrom,
                'to' => $apiDateTo,
            ],
            'branches' => $branches,
            'notPostedCount' => $notPostedRecords->count(),
            'totalCount' => $data->count(),
            'groupedByAging' => $groupedByAging,
            'totalSumInsured' => $totalSumInsured,
            'notPostedSumInsured' => $notPostedSumInsured,
        ]);
    }


public function getReinsuranceR2Data(Request $request)
{
    // 1. Get dates and filters from request
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $timeFilter = $request->input('time_filter', 'all'); // Define timeFilter here
    $selectedCategory = $request->input('department');
    $selectedLocation = $request->input('location');
    $formStartDate = $startDate ?? Carbon::now()->startOfYear()->format('Y-m-d');
    $formEndDate = $endDate ?? Carbon::now()->format('Y-m-d');
   
    $formStartDate = $startDate ?? Carbon::now()->startOfYear()->format('Y-m-d');
    $formEndDate = $endDate ?? Carbon::now()->format('Y-m-d');

    // 2. Define category mapping
    $categoryMapping = [
        'Fire' => 11,
        'Marine' => 12,
        'Motor' => 13,
        'Miscellaneous' => 14,
        'Health' => 16,
    ];

    // 3. Initialize time filter counts
    $counts = [
        'all' => 0,
        '2days' => 0,
        '5days' => 0,
        '7days' => 0,
        '10days' => 0,
        '15days' => 0,
        '15plus' => 0,
    ];

    // 4. Get data from helper
    $result = Helper::getReinsuranceR2Report($formStartDate, $formEndDate);
    $data = collect($result['data'] ?? []);

    // 5. Fetch reqnote values from emaillogs
    $insertedDocs = EmailLog::pluck('reqnote')->toArray();

    // 6. Filter out records that exist in emaillogs
    $data = $data->filter(function ($record) use ($insertedDocs) {
        return !in_array($record->GRH_REFERENCE_NO ?? '', $insertedDocs);
    });

    // 7. Calculate time filter counts based on GRH_ACCEPTEDDATE
    $today = Carbon::today();
    $filterDate = fn($record, $condition) => ($date = ($record->GRH_ACCEPTEDDATE ?? null) ? Carbon::parse($record->GRH_ACCEPTEDDATE) : null) && $date->isValid() && $condition($date);

    $counts['all'] = $data->count();
    $counts['2days'] = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->gte($today->copy()->subDays(2))))->count();
    $counts['5days'] = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->between($today->copy()->subDays(5), $today->copy()->subDays(2), false)))->count();
    $counts['7days'] = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->between($today->copy()->subDays(7), $today->copy()->subDays(5), false)))->count();
    $counts['10days'] = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->between($today->copy()->subDays(10), $today->copy()->subDays(7), false)))->count();
    $counts['15days'] = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->between($today->copy()->subDays(15), $today->copy()->subDays(10), false)))->count();
    $counts['15plus'] = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->lt($today->copy()->subDays(15))))->count();

    // 8. Apply time filter based on GRH_ACCEPTEDDATE
    $filteredData = $data;
    switch ($timeFilter) {
        case '2days':
            $filteredData = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->gte($today->copy()->subDays(2))));
            break;
        case '5days':
            $filteredData = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->between($today->copy()->subDays(5), $today->copy()->subDays(2), false)));
            break;
        case '7days':
            $filteredData = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->between($today->copy()->subDays(7), $today->copy()->subDays(5), false)));
            break;
        case '10days':
            $filteredData = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->between($today->copy()->subDays(10), $today->copy()->subDays(7), false)));
            break;
        case '15days':
            $filteredData = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->between($today->copy()->subDays(15), $today->copy()->subDays(10), false)));
            break;
        case '15plus':
            $filteredData = $data->filter(fn($record) => $filterDate($record, fn($date) => $date->lt($today->copy()->subDays(15))));
            break;
        case 'all':
        default:
            break;
    }
    $data = $filteredData;

    // 9. Category filtering
    if ($selectedCategory && isset($categoryMapping[$selectedCategory])) {
        $deptCode = $categoryMapping[$selectedCategory];
        $data = $data->filter(fn($item) => isset($item->PDP_DEPT_CODE) && $item->PDP_DEPT_CODE == $deptCode);
    }

    // 10. Branch filtering by PLC_LOCADESC
    if ($selectedLocation) {
        $data = $data->filter(fn($item) => isset($item->PLC_LOCADESC) && Str::contains($item->PLC_LOCADESC, $selectedLocation));
    }

    // 11. Extract unique categories from filtered data
    $uniqueCategories = $data->pluck('PLC_LOCADESC')->filter()->unique()->sort()->values();

    // 12. Fetch branch info from branches_list table by fbracode
    $branchCode = $request->input('location_category');
    $matchedBranch = BranchesList::where('fbracode', $branchCode)->first();
    $branches = $matchedBranch ? collect([$matchedBranch]) : BranchesList::all();

    // 13. Filter not posted records
    $notPostedRecords = $data->where('GRH_POSTINGTAG', '!=', 'Y');

    // 14. Group aging buckets
    $now = Carbon::now();
    $agingBuckets = [
        '0-3 Days' => [0, 3],
        '4-7 Days' => [4, 7],
        '8-10 Days' => [8, 10],
        '11-15 Days' => [11, 15],
        '16-20 Days' => [16, 20],
        '20+ Days' => [21, PHP_INT_MAX],
    ];

    $groupedByAging = [];
    foreach ($agingBuckets as $label => [$min, $max]) {
        $groupedByAging[$label] = $data->filter(function ($item) use ($now, $min, $max) {
            $date = ($item->GRH_DOCUMENTDATE ?? null) ? Carbon::createFromFormat('d-M-y', $item->GRH_DOCUMENTDATE) : null;
            return $date && $date->isValid() && $date->diffInDays($now) >= $min && $date->diffInDays($now) <= $max;
        });
    }

    // 15. Calculate sum insured
    $totalSumInsured = $data->sum(fn($item) => (float)($item->CED_SI ?? 0));
    $notPostedSumInsured = $notPostedRecords->sum(fn($item) => (float)($item->CED_SI ?? 0));

    // 16. Add days_old for highlighting based on GRH_ACCEPTEDDATE
    $data = $data->map(function ($item) use ($today) {
        $item->days_old = null;
        if ($date = ($item->GRH_ACCEPTEDDATE ?? null) ? Carbon::parse($item->GRH_ACCEPTEDDATE) : null) {
            if ($date->isValid()) {
                $item->days_old = $date->diffInDays($today);
            }
        }
        return $item;
    });

    // 17. Format dates for display
    $apiDateFrom = Carbon::parse($formStartDate)->format('d-M-Y');
    $apiDateTo = Carbon::parse($formEndDate)->format('d-M-Y');

    // 18. Return view
    return view('GetRequestReports.r2', [
        'data' => $data,
        'start_date' => $formStartDate,
        'end_date' => $formEndDate,
        'uniqueCategories' => $uniqueCategories,
        'api_date_range' => [
            'from' => $apiDateFrom,
            'to' => $apiDateTo,
        ],
        'branches' => $branches,
        'notPostedCount' => $notPostedRecords->count(),
        'totalCount' => $data->count(),
        'groupedByAging' => $groupedByAging,
        'totalSumInsured' => $totalSumInsured,
        'notPostedSumInsured' => $notPostedSumInsured,
        'selected_time_filter' => $timeFilter,
        'selected_department' => $selectedCategory,
        'counts' => $counts,
    ]);
}


public function getReinsuranceCase(Request $request)
{
    // 1. Fetch business classes
    $businessClassResponse = Helper::fetchBusinessClasses(); 
    if (isset($businessClassResponse['error'])) {
        return back()->with('error', $businessClassResponse['error']);
    }

    // 2. Fetch brokers
    $brokerResponse = Helper::fetchBroker();
    if (isset($brokerResponse['error'])) {
        return back()->with('error', $brokerResponse['error']);
    }

    // Filter active brokers
    $brokers = collect($brokerResponse)->filter(function ($broker) {
        return isset($broker['PPS_STATUS']) && $broker['PPS_STATUS'] === 'A';
    });

    // 3. Fetch renewal info
    $renewalResponse = Helper::fetchRenewal();
    if (isset($renewalResponse['error'])) {
        return back()->with('error', $renewalResponse['error']);
    }
    $currentBrokerCode = $renewalResponse['current_broker'] ?? null;

    // 4. Handle filters with defaults
    $startDate = $request->filled('start_date') 
        ? Carbon::parse($request->start_date)->format('Y-m-d') 
        : Carbon::now()->startOfMonth()->format('Y-m-d');

    $endDate = $request->filled('end_date') 
        ? Carbon::parse($request->end_date)->format('Y-m-d') 
        : Carbon::now()->endOfMonth()->format('Y-m-d');

    $sum = is_numeric($request->sum) ? (float) $request->sum : 10000000;

    $businessClass = $request->input('business_class', 'All');
    $brokerCode = $request->input('broker_code', 'All');
    $clientType = $request->input('client_type', 'All');

    // 5. Map category to department
    $dept = 'All';
    $categoryMapping = [
        'Fire' => 11,
        'Marine' => 12,
        'Motor' => 13,
        'Miscellaneous' => 14,
        'Health' => 16,
    ];
    if ($request->filled('new_category')) {
        $selectedCategory = $request->new_category;
        if (isset($categoryMapping[$selectedCategory])) {
            $dept = $categoryMapping[$selectedCategory];
        }
    }

    // 6. Fetch reinsurance cases via helper
    $result = Helper::getReinsuranceCase($startDate, $endDate, $sum, $businessClass, $dept, $brokerCode, $clientType);
    $data = collect($result['data'] ?? []);

    // 7. Exclude already inserted documents
    $insertedDocs = ReinsLog::pluck('uw_doc')->toArray();
    $data = $data->filter(function ($record) use ($insertedDocs, $sum) {
        $docRef = $record->GDH_DOC_REFERENCE_NO ?? '';
        $sumInsured = (float) ($record->GDH_TOTALSI ?? 0);
        return !in_array($docRef, $insertedDocs) && $sumInsured <= $sum;
    });

    // 8. Format dates for display
    $apiDateFrom = Carbon::parse($startDate)->format('d-M-Y');
    $apiDateTo = Carbon::parse($endDate)->format('d-M-Y');

    // 9. Return view
    return view('GetRequestReports.case', [
        'data' => $data,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'api_date_range' => [
            'from' => $apiDateFrom,
            'to' => $apiDateTo,
        ],
        'brokers' => $brokers,
        'currentBrokerCode' => $currentBrokerCode,
        'businessClasses' => $businessClassResponse,
    ]);
}


 public function fetchReinsuranceData(Request $request)
{
    try {
        // Validate the incoming request
        $request->validate([
            'uw_doc' => 'required|string',
            'dept' => 'required|integer',
            'issue_date' => 'nullable|string',
            'comm_date' => 'nullable|string',
            'expiry_date' => 'nullable|string',
            'insured' => 'nullable|string',
            'location' => 'nullable|string',
            'business_class' => 'nullable|string',
            'sum_insured' => 'nullable|numeric',
            'gross_premium' => 'nullable|numeric',
            'net_premium' => 'nullable|numeric',
        ]);

        // Get the user's name from session
        $userid = Session::get('user')['name'] ?? 'Unknown';

        // Function to convert date format from "21-AUG-25" to "Y-m-d"
        $convertDate = function ($dateString) {
            if ($dateString === 'N/A' || empty($dateString) || $dateString === null) {
                return null;
            }
            try {
                return \Carbon\Carbon::createFromFormat('d-M-y', $dateString)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning("Date conversion failed for: {$dateString}. Error: " . $e->getMessage());
                return null;
            }
        };

        // Function to convert numeric values
        $convertNumeric = function ($value) {
            if ($value === 'N/A' || empty($value) || $value === null) {
                return null;
            }
            // Remove commas and convert to float
            $cleanValue = str_replace(',', '', $value);
            return is_numeric($cleanValue) ? (float) $cleanValue : null;
        };

        // Create a new record in the reinslogs table
        $record = ReinsLog::create([
            'uw_doc' => $request->uw_doc,
            'dept' => $request->dept,
            'issue_date' => $convertDate($request->issue_date),
            'comm_date' => $convertDate($request->comm_date),
            'expiry_date' => $convertDate($request->expiry_date),
            'insured' => $request->insured !== 'N/A' ? urldecode($request->insured) : null,
            'location' => $request->location !== 'N/A' ? urldecode($request->location) : null,
            'business_class' => $request->business_class !== 'N/A' ? urldecode($request->business_class) : null,
            'sum_insured' => $convertNumeric($request->sum_insured),
            'gross_premium' => $convertNumeric($request->gross_premium),
            'net_premium' => $convertNumeric($request->net_premium),
            'riskMarked' => 'Y',
            'noti_att' => 'N',
            'created_by' => $userid,
            'updated_by' => $userid,
        ]);

        return response()->json([
            'message' => 'Record added successfully',
            'record' => $record,
        ], 201);

    } catch (\Exception $e) {
        Log::error('Error adding reinsurance data: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'message' => 'Server error occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function show(Request $request)
{
    try {
        // Define category mapping
        $categoryMapping = [
            'Fire' => 11,
            'Marine' => 12,
            'Motor' => 13,
            'Miscellaneous' => 14,
            'Health' => 16,
        ];

        // Get the time filter and department from the request
        $timeFilter = $request->query('time_filter', 'all'); // Default to 'all'
        $selectedCategory = $request->filled('new_category') ? $request->new_category : '';

        // Initialize counts for each time filter
        $counts = [
            'all' => 0,
            '2days' => 0,
            '5days' => 0,
            '7days' => 0,
            '10days' => 0,
            '15days' => 0,
            '15plus' => 0,
        ];

        // Calculate counts for each time filter
        $today = Carbon::today();
        
        // All records
        $allRecords = ReinsLog::all();
        $counts['all'] = $allRecords->filter(function ($record) {
            return empty($this->getRequestNotesByDocument($record->uw_doc));
        })->count();

        // 2 Days (0-2 days old)
        $counts['2days'] = ReinsLog::where('created_at', '>=', $today->copy()->subDays(2))
            ->get()
            ->filter(function ($record) {
                return empty($this->getRequestNotesByDocument($record->uw_doc));
            })->count();

        // 5 Days (2-5 days old)
        $counts['5days'] = ReinsLog::where('created_at', '>=', $today->copy()->subDays(5))
            ->where('created_at', '<', $today->copy()->subDays(2))
            ->get()
            ->filter(function ($record) {
                return empty($this->getRequestNotesByDocument($record->uw_doc));
            })->count();

        // 7 Days (5-7 days old)
        $counts['7days'] = ReinsLog::where('created_at', '>=', $today->copy()->subDays(7))
            ->where('created_at', '<', $today->copy()->subDays(5))
            ->get()
            ->filter(function ($record) {
                return empty($this->getRequestNotesByDocument($record->uw_doc));
            })->count();

        // 10 Days (7-10 days old)
        $counts['10days'] = ReinsLog::where('created_at', '>=', $today->copy()->subDays(10))
            ->where('created_at', '<', $today->copy()->subDays(7))
            ->get()
            ->filter(function ($record) {
                return empty($this->getRequestNotesByDocument($record->uw_doc));
            })->count();

        // 15 Days (10-15 days old)
        $counts['15days'] = ReinsLog::where('created_at', '>=', $today->copy()->subDays(15))
            ->where('created_at', '<', $today->copy()->subDays(10))
            ->get()
            ->filter(function ($record) {
                return empty($this->getRequestNotesByDocument($record->uw_doc));
            })->count();

        // 15+ Days (more than 15 days old)
        $counts['15plus'] = ReinsLog::where('created_at', '<', $today->copy()->subDays(15))
            ->get()
            ->filter(function ($record) {
                return empty($this->getRequestNotesByDocument($record->uw_doc));
            })->count();

        // Reset today for record filtering
        $today = Carbon::today();

        // Apply time filter based on created_at
        $query = ReinsLog::query();
        switch ($timeFilter) {
            case '2days':
                $query->where('created_at', '>=', $today->copy()->subDays(2));
                break;
            case '5days':
                $query->where('created_at', '>=', $today->copy()->subDays(5))
                      ->where('created_at', '<', $today->copy()->subDays(2));
                break;
            case '7days':
                $query->where('created_at', '>=', $today->copy()->subDays(7))
                      ->where('created_at', '<', $today->copy()->subDays(5));
                break;
            case '10days':
                $query->where('created_at', '>=', $today->copy()->subDays(10))
                      ->where('created_at', '<', $today->copy()->subDays(7));
                break;
            case '15days':
                $query->where('created_at', '>=', $today->copy()->subDays(15))
                      ->where('created_at', '<', $today->copy()->subDays(10));
                break;
            case '15plus':
                $query->where('created_at', '<', $today->copy()->subDays(15));
                break;
            case 'all':
            default:
                // No time filter for 'all'
                break;
        }

        // Fetch all records based on time filter
        $records = $query->get();

        // Filter out records that exist in the API
        $records = $records->filter(function ($record) {
            if (empty($record->uw_doc)) {
                return true; // Keep records with no uw_doc
            }
            $apiNotes = $this->getRequestNotesByDocument($record->uw_doc);
            return empty($apiNotes); // Keep only if no notes found in API
        });

        // Apply department filter if provided
        if ($selectedCategory && isset($categoryMapping[$selectedCategory])) {
            $deptCode = $categoryMapping[$selectedCategory];
            $records = $records->filter(function ($item) use ($deptCode) {
                return Str::startsWith($item->dept ?? '', (string) $deptCode);
            });
        }

        // Calculate days_old for each record
        $records = $records->map(function ($record) use ($today) {
            $record->days_old = $record->created_at ? $record->created_at->diffInDays($today) : null;
            return $record;
        });

        // Return view with records, selected filters, and counts
        return view('GetRequestReports.show', [
            'records' => $records,
            'selected_time_filter' => $timeFilter,
            'selected_department' => $selectedCategory,
            'counts' => $counts,
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching reinsurance data: ' . $e->getMessage());
        return back()->with('error', 'An error occurred while fetching data.');
    }
}
 public static function getRequestNotesByDocument($documentNumber)
    {
        if (empty($documentNumber)) {
            return [];
        }

        $apiUrl = "http://172.16.22.204/dashboardApi/reins/rqn/get_notes_uw.php?uw_doc=" . urlencode($documentNumber);
        
        $response = @file_get_contents($apiUrl, false, stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 120
            ]
        ]));

        if ($response === false) {
            Log::error("Failed to fetch request notes for document: " . $documentNumber);
            return [];
        }

        $cleanResponse = preg_replace('/<br \/>\n<b>Notice<\/b>:.*?<br \/>\n/', '', $response);
        $data = json_decode($cleanResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            Log::error("Invalid JSON response for document: " . $documentNumber);
            return [];
        }

        $requestNotes = [];
        foreach ($data as $item) {
            try {
                $decoded = is_string($item) ? json_decode($item, true) : $item;
                if (isset($decoded['GRH_REFERENCE_NO'])) {
                    $requestNotes[] = $decoded['GRH_REFERENCE_NO'];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return array_unique(array_filter($requestNotes));
    }
 


public function getshow(Request $request)
{
    try {
        // Get the time filter from the request
        $timeFilter = $request->query('time_filter', 'all'); // Default to 'all'
        
        // Initialize counts for each time filter
        $counts = [
            'all' => 0,
            '2days' => 0,
            '5days' => 0,
            '7days' => 0,
            '10days' => 0,
            '15days' => 0,
            '15plus' => 0,
        ];

        // Calculate counts for each time filter
        $today = Carbon::today();
        
        // Fetch unique records based on reqnote, selecting the latest record
        $allRecords = EmailLog::select('*')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                      ->from('emaillogs')
                      ->groupBy('reqnote');
            })
            ->get();
        
        // All records
        $counts['all'] = $allRecords->count();

        // 2 Days (0-2 days old)
        $counts['2days'] = $allRecords->filter(function ($record) use ($today) {
            return $record->datetime && Carbon::parse($record->datetime) >= $today->copy()->subDays(2);
        })->count();
       // dd($allRecords);
        // 5 Days (2-5 days old)
        $counts['5days'] = $allRecords->filter(function ($record) use ($today) {
            return $record->datetime && 
                   Carbon::parse($record->datetime) >= $today->copy()->subDays(5) &&
                   Carbon::parse($record->datetime) < $today->copy()->subDays(2);
        })->count();

        // 7 Days (5-7 days old)
        $counts['7days'] = $allRecords->filter(function ($record) use ($today) {
            return $record->datetime && 
                   Carbon::parse($record->datetime) >= $today->copy()->subDays(7) &&
                   Carbon::parse($record->datetime) < $today->copy()->subDays(5);
        })->count();

        // 10 Days (7-10 days old)
        $counts['10days'] = $allRecords->filter(function ($record) use ($today) {
            return $record->datetime && 
                   Carbon::parse($record->datetime) >= $today->copy()->subDays(10) &&
                   Carbon::parse($record->datetime) < $today->copy()->subDays(7);
        })->count();

        // 15 Days (10-15 days old)
        $counts['15days'] = $allRecords->filter(function ($record) use ($today) {
            return $record->datetime && 
                   Carbon::parse($record->datetime) >= $today->copy()->subDays(15) &&
                   Carbon::parse($record->datetime) < $today->copy()->subDays(10);
        })->count();

        // 15+ Days (more than 15 days old)
        $counts['15plus'] = $allRecords->filter(function ($record) use ($today) {
            return $record->datetime && Carbon::parse($record->datetime) < $today->copy()->subDays(15);
        })->count();

        // Apply time filter based on datetime
        $query = EmailLog::select('*')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                      ->from('emaillogs')
                      ->groupBy('reqnote');
            });

        switch ($timeFilter) {
            case '2days':
                $query->where('datetime', '>=', $today->copy()->subDays(2));
                break;
            case '5days':
                $query->where('datetime', '>=', $today->copy()->subDays(5))
                      ->where('datetime', '<', $today->copy()->subDays(2));
                break;
            case '7days':
                $query->where('datetime', '>=', $today->copy()->subDays(7))
                      ->where('datetime', '<', $today->copy()->subDays(5));
                break;
            case '10days':
                $query->where('datetime', '>=', $today->copy()->subDays(10))
                      ->where('datetime', '<', $today->copy()->subDays(7));
                break;
            case '15days':
                $query->where('datetime', '>=', $today->copy()->subDays(15))
                      ->where('datetime', '<', $today->copy()->subDays(10));
                break;
            case '15plus':
                $query->where('datetime', '<', $today->copy()->subDays(15));
                break;
            case 'all':
            default:
                // No time filter for 'all'
                break;
        }

        $records = $query->get();
        
        // Add email count for each record
        foreach ($records as $record) {
            $record->email_count = EmailLog::where('reqnote', $record->reqnote)->count();
            
            // Calculate days_old for each record
            if ($record->datetime) {
                $record->days_old = Carbon::parse($record->datetime)->diffInDays($today);
            } else {
                $record->days_old = null;
            }
        }

        // Return view with records and counts
        return view('GetRequestReports.getshow', [
            'records' => $records,
            'selected_time_filter' => $timeFilter,
            'counts' => $counts,
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching email log data: ' . $e->getMessage());
        return back()->with('error', 'An error occurred while fetching data.');
    }
}
   
    public function getEmailLogs(Request $request)
    {
        $request->validate([
            'reqnote' => 'required|string',
        ]);

        try {
            // Fetch all email logs for the given reqnote, ordered by datetime
            $logs = EmailLog::where('reqnote', $request->reqnote)
                ->orderBy('datetime', 'desc') // Use datetime for ordering
                ->get();

            return response()->json([
                'success' => true,
                'logs' => $logs->map(function ($log) {
                    return [
                        'datetime' => $log->datetime ?? 'N/A', // Use datetime field
                        'sent_to' => $log->sent_to,
                        'sent_cc' => $log->sent_cc ?? 'N/A',
                        'subject' => $log->subject,
                    ];
                })->toArray()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching email logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch email logs: ' . $e->getMessage()
            ], 500);
        }
    }



public function getlast(Request $request)
{
    // Get dates from request or set defaults
    $startDate = $request->input('start_date') ?? Carbon::now()->startOfYear()->format('Y-m-d');
    $endDate = $request->input('end_date') ?? Carbon::now()->format('Y-m-d');

    // Get data from helper
    $result = Helper::getReinsuranceLastReport($startDate, $endDate);
    $data = collect($result['data'] ?? []);

    // Filter out verified records
    $verifiedReferenceNos = VerifyLog::pluck('GCP_DOC_REFERENCENO')->toArray();
    $data = $data->filter(function ($record) use ($verifiedReferenceNos) {
        return !in_array($record->GCP_DOC_REFERENCENO ?? '', $verifiedReferenceNos);
    });

    // 5. Category filtering
    $categoryMapping = [
        'Fire' => 11,
        'Marine' => 12,
        'Motor' => 13,
        'Miscellaneous' => 14,
        'Health' => 16,
    ];

    if ($request->filled('new_category')) {
        $selectedNewCategory = $request->new_category;
        $allowedNewCode = $categoryMapping[$selectedNewCategory] ?? null;
    
        if ($allowedNewCode !== null) {
            $data = $data->filter(function ($item) use ($allowedNewCode) {
    // Handle both arrays and objects
    $deptCode = is_array($item) ? ($item['PDP_DEPT_CODE'] ?? null) : ($item->PDP_DEPT_CODE ?? null);
    
    return $deptCode !== null && 
           Str::startsWith((string) $deptCode, (string) $allowedNewCode);
});
        }
    }

    // Return view
    return view('GetRequestReports.lastcase', [
        'data' => $data,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'api_date_range' => [
            'from' => Carbon::parse($startDate)->format('d-M-Y'),
            'to' => Carbon::parse($endDate)->format('d-M-Y'),
        ]
    ]);
}
  public function verifyRecord(Request $request)
    {
        // Decode record if it’s a JSON string
        $recordData = $request->input('record');
        if (is_string($recordData)) {
            $recordData = json_decode($recordData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid record format: JSON decode failed'
                ], 422);
            }
        }

        // Validate the request
        $request->merge(['record' => $recordData]); 
        $request->validate([
            'record' => 'required|array',
            'record.referenceNo' => 'required|string',
            'record.departmentCode' => 'nullable|string',
            'record.serialNo' => 'nullable|string',
            'record.issueDate' => 'nullable|string',
            'record.commencementDate' => 'nullable|string',
            'record.expiryDate' => 'nullable|string',
            'record.reinsurer' => 'nullable|string',
            'record.reissueDate' => 'nullable|string',
            'record.recommendedDate' => 'nullable|string',
            'record.reexpiryDate' => 'nullable|string',
            'record.totalSi' => 'nullable|string',
            'record.totalPremium' => 'nullable|string',
            'record.reinsuranceSi' => 'nullable|string',
            'record.reinsurancePremium' => 'nullable|string',
            'record.commissionAmount' => 'nullable|string',
            'record.postingTag' => 'nullable|string',
            'record.cancellationTag' => 'nullable|string',
            'record.postedBy' => 'nullable|string',
            'record.theirReferenceNo' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf|max:2048' // File is optional
        ]);


        //dd($request);
        try {
            $filePath = null;

            // Handle file upload if present
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $referenceNo = $recordData['referenceNo'] ?? 'unknown';
                $filename = 'reinsurance_' . $referenceNo . '_' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('uploads/reinsurance', $filename, 'public');
            }

            // Map data to VerifyLog model fields
            $verifyLog = VerifyLog::create([
                'GCP_DOC_REFERENCENO' => $recordData['referenceNo'] ?? null,
                'PDP_DEPT_CODE' => $recordData['departmentCode'] ?? null,
                'GCP_SERIALNO' => $recordData['serialNo'] ?? null,
                'GCP_ISSUEDATE' => $recordData['issueDate'] ?? null,
                'GCP_COMMDATE' => $recordData['commencementDate'] ?? null,
                'GCP_EXPIRYDATE' => $recordData['expiryDate'] ?? null,
                'GCP_REINSURER' => $recordData['reinsurer'] ?? null,
                'GCP_REISSUEDATE' => $recordData['reissueDate'] ?? null,
                'GCP_RECOMMDATE' => $recordData['recommendedDate'] ?? null,
                'GCP_REEXPIRYDATE' => $recordData['reexpiryDate'] ?? null,
                'GCP_COTOTALSI' => $recordData['totalSi'] ?? null,
                'GCP_COTOTALPREM' => $recordData['totalPremium'] ?? null,
                'GCP_REINSI' => $recordData['reinsuranceSi'] ?? null,
                'GCP_REINPREM' => $recordData['reinsurancePremium'] ?? null,
                'GCP_COMMAMOUNT' => $recordData['commissionAmount'] ?? null,
                'GCP_POSTINGTAG' => $recordData['postingTag'] ?? null,
                'GCP_CANCELLATIONTAG' => $recordData['cancellationTag'] ?? null,
                'GCP_POST_USER' => $recordData['postedBy'] ?? null,
                'GCT_THEIR_REF_NO' => $recordData['theirReferenceNo'] ?? null,
                'avatar' => $filePath, // Store file path if uploaded, else null
                'datetime' => Carbon::now()->format('Y-m-d H:i:s'),
                'sent_to' => null,
                'sent_cc' => null,
                'subject' => null,
                'body' => null,
                'rep_name' => null,
                'created_by' => auth()->user()->name ?? 'System',
                'updated_by' => auth()->user()->name ?? 'System'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Record verified and saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save record: ' . $e->getMessage()
            ], 500);
        }
}

}

     

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\BranchesList; // Add this use statement

class DealerWiseReportController extends Controller
{
    public function index()
    {
        // Get unique dealer codes from API for dropdown
        $dealers = "";
        
        // Get branches from BranchesList model
        $branches = $this->getBranchesList();
            $branches = BranchesList::all();
        
        return view('DealerReports.dealerwise_report', [
            'claims' => [],
            'fromDate' => date('Y-m-d', strtotime('-30 days')),
            'toDate' => date('Y-m-d'),
            'showReport' => false,
            'dealers' => $dealers,
            'branches' => $branches, 
            'selectedDealer' => null,
            'selectedBranch' => null 
        ]);
    }

    public function generate(Request $request)
    {
        $dealerCode = '';
        $request->validate([
            'fromDate' => 'required|date',
            'toDate' => 'required|date|after_or_equal:fromDate'
        ]);
        
        $fromDate = $request->get('fromDate');
        $toDate = $request->get('toDate');
        $brCode = $request->get('location_category');


        
        $selectedBranch = BranchesList::where('fbracode', $brCode)->first();
         $branchFilter = $selectedBranch->fbratak;
        
        
        try {
            // Fetch data from API with 10 minute timeout
            $apiUrl = "http://172.16.22.204/dashboardApi/autosecure/getDealerClaims.php";
            
            // Calculate days difference for logging
            $date1 = new \DateTime($fromDate);
            $date2 = new \DateTime($toDate);
            $interval = $date1->diff($date2);
            $diffDays = $interval->days;
            
            Log::info("DealerWiseReport: Fetching data for {$fromDate} to {$toDate} ({$diffDays} days)" . 
                   ($brCode ? " for dealer: {$brCode}" : " for all dealers") .
                   ($branchFilter ? " for branch: {$branchFilter}" : ""));
            
            // Prepare API parameters
            $apiParams = [
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'loccode' => $brCode,
                'loctak' => $branchFilter
            ];
            
            
       
            $response = Http::timeout(600)->get($apiUrl, $apiParams);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Check if API returned expected data structure
                if (!isset($data['autosecure'])) {
                    Log::error("DealerWiseReport: API returned invalid structure - 'autosecure' key missing", ['response' => $data]);
                    
                    return view('DealerReports.dealerwise_report', [
                        'claims' => [],
                        'error' => 'API Error: Invalid data structure received. The API did not return the expected format. Please contact support.',
                        'fromDate' => $fromDate,
                        'toDate' => $toDate,
                        'showReport' => false,
                        'dealers' => $this->getDealersList($apiParams),
                        'branches' => $this->getBranchesList(),
                        'selectedDealer' => $dealerCode,
                        'selectedBranch' => $branchFilter
                    ]);
                }
                
                if (!isset($data['autosecure']['data'])) {
                    Log::warning("DealerWiseReport: No data found in API response", ['response' => $data]);
                    
                    return view('DealerReports.dealerwise_report', [
                        'claims' => [],
                        'fromDate' => $fromDate,
                        'toDate' => $toDate,
                        'showReport' => true,
                        'info' => 'No claims data found for the selected period' . 
                                 ($dealerCode ? " and dealer code: {$dealerCode}" : '') .
                                 ($branchFilter ? " and branch: {$branchFilter}" : ''),
                        'dealers' => $this->getDealersList(),
                        'branches' => $this->getBranchesList(),
                        'selectedDealer' => $dealerCode,
                        'selectedBranch' => $branchFilter
                    ]);
                }
                
                // Process the data to match GIS numbers with claims
                $allProcessedData = $this->processClaimsData($data);
                
                
                // Get dealers list for dropdown
                $dealers = $this->getDealersList($apiParams);
                
                // Get branches list for dropdown
                    $branches = BranchesList::all();
                
                // Count total records before filtering
                $totalRecords = count($allProcessedData);
                
                // Filter by dealer code if selected (client-side filtering)
                $filteredData = $allProcessedData;
                
                if ($dealerCode) {
                    $filteredData = array_filter($allProcessedData, function($claim) use ($dealerCode) {
                        return isset($claim['dealer_code']) && $claim['dealer_code'] == $dealerCode;
                    });
                    $filteredData = array_values($filteredData); // Reset array keys
                }
                // return $filteredData;
                $recordCount = count($filteredData);
                Log::info("DealerWiseReport: Total records: {$totalRecords}, Filtered records: {$recordCount}" . 
                       ($dealerCode ? " for dealer: {$dealerCode}" : '') .
                       ($branchFilter ? " for branch: {$branchFilter}" : ''));
                
                // Prepare success message
                $message = "Report generated successfully! ";
                if ($dealerCode) {
                    $message .= "Found {$recordCount} records";
                    if ($dealerCode) {
                        $message .= " for dealer: {$dealerCode}";
                    }
                   
                    $message .= " (from {$totalRecords} total records)";
                } else {
                    $message .= "Found {$recordCount} records";
                }
                
                return view('DealerReports.dealerwise_report', [
                    'claims' => $filteredData,
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                    'showReport' => true,
                    'success' => $message,
                    'dealers' => $dealers,
                    'branches' => $branches,
                    'selectedDealer' => $dealerCode,
                    'selectedBranch' => $branchFilter,
                    'totalRecords' => $totalRecords,
                    'filteredRecords' => $recordCount
                ]);
            } else {
                $statusCode = $response->status();
                $errorMessage = "API Request Failed (Status: {$statusCode})";
                
                Log::error("DealerWiseReport: API request failed with status {$statusCode}");
                
                return view('DealerReports.dealerwise_report', [
                    'claims' => [],
                    'error' => $errorMessage,
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                    'showReport' => false,
                    'dealers' => $this->getDealersList(),
                    'branches' => $this->getBranchesList(),
                    'selectedDealer' => $dealerCode,
                    'selectedBranch' => $branchFilter
                ]);
            }
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("DealerWiseReport: Connection failed - " . $e->getMessage());
            
            return view('DealerReports.dealerwise_report', [
                'claims' => [],
                'error' => 'Connection Error: Unable to connect to the API server. Please check if the API server is running and accessible.',
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'showReport' => false,
                'dealers' => $this->getDealersList($apiParams),
                'branches' => $this->getBranchesList(),
                'selectedDealer' => $dealerCode,
                'selectedBranch' => $branchFilter
            ]);
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error("DealerWiseReport: Request exception - " . $e->getMessage());
            
            return view('DealerReports.dealerwise_report', [
                'claims' => [],
                'error' => 'Request Timeout: The API request timed out. This may be due to a large date range or server issues. Please try a smaller date range or try again later.',
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'showReport' => false,
                'dealers' => $this->getDealersList($apiParams),
                'branches' => $this->getBranchesList(),
                'selectedDealer' => $dealerCode,
                'selectedBranch' => $branchFilter
            ]);
            
        } catch (\Exception $e) {
            Log::error("DealerWiseReport: Unexpected error - " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('DealerReports.dealerwise_report', [
                'claims' => [],
                'error' => 'Unexpected Error: ' . $e->getMessage() . '. Please contact support.',
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'showReport' => false,
                'dealers' => $this->getDealersList($apiParams),
                'branches' => $this->getBranchesList(),
                'selectedDealer' => $dealerCode,
                'selectedBranch' => $branchFilter
            ]);
        }
    }

    private function getDealersList($apiParams)
    {
        $dealers = [];
        
        try {
            // Fetch last 30 days data to get dealer list
            $apiUrl = "http://172.16.22.204/dashboardApi/autosecure/getDealerClaims.php";
            $response = Http::timeout(60)->get($apiUrl, $apiParams);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['autosecure']['data'])) {
                    // Extract unique dealer codes with contact persons
                    $dealerMap = [];
                    foreach ($data['autosecure']['data'] as $claim) {
                        $dealerCode = $claim['dealer_code'] ?? null;
                        $contactPerson = $claim['con_per'] ?? null;
                        
                        if ($dealerCode && !isset($dealerMap[$dealerCode])) {
                            $dealerMap[$dealerCode] = [
                                'dealer_code' => $dealerCode,
                                'contact_person' => $contactPerson
                            ];
                        }
                    }
                    
                    $dealers = array_values($dealerMap);
                    // Sort dealers by dealer code
                    usort($dealers, function($a, $b) {
                        return strcmp($a['dealer_code'] ?? '', $b['dealer_code'] ?? '');
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error("DealerWiseReport: Failed to fetch dealers list - " . $e->getMessage());
        }
        
        return $dealers;
    }

    private function processClaimsData($data)
    {
        $autosecureData = $data['autosecure']['data'] ?? [];
        $gisData = $data['gis'] ?? [];
        
        // Create a lookup array for GIS claims
        $gisLookup = [];
        foreach ($gisData as $gis) {
            if (isset($gis['GID_BASEDOCUMENTNO'])) {
                $gisLookup[$gis['GID_BASEDOCUMENTNO']] = $gis['TOT_CLM'] ?? 0;
            }
        }
        
        // Process each claim and add the matching TOT_CLM
        $processedClaims = [];
        foreach ($autosecureData as $claim) {
            $gisNumber = $claim['gis'] ?? '';
            
            // Match GIS number and get TOT_CLM or default to 0
            $claim['tot_clm'] = isset($gisLookup[$gisNumber]) ? $gisLookup[$gisNumber] : '0';
            
            $processedClaims[] = $claim;
        }
        
        return $processedClaims;
    }

    /**
     * Get branches list from BranchesList model
     */
    private function getBranchesList()
    {
        try {
            // Fetch all active branches from BranchesList model
            $branches = BranchesList::select('fbracode', 'fbradsc', 'loctak')
                ->orderBy('fbradsc', 'asc')
                ->get()
                ->toArray();
            
            Log::info("DealerWiseReport: Retrieved " . count($branches) . " branches from database");
            
            return $branches;
            
        } catch (\Exception $e) {
            Log::error("DealerWiseReport: Failed to fetch branches list - " . $e->getMessage());
            
            // Return empty array if there's an error
            return [];
        }
    }

    /**
     * Dealer Summary Report
     */
    public function dealerSummaryReport(Request $request)
    {
        // Check if it's the initial page load (no form submission)
        if (!$request->has('fromDate') || !$request->has('toDate')) {
            return view('DealerReports.dealersummary_report', [
                'summaryData' => [],
                'fromDate' => null,
                'toDate' => null,
                'showReport' => false
            ]);
        }
        
        $request->validate([
            'fromDate' => 'required|date',
            'toDate' => 'required|date|after_or_equal:fromDate'
        ]);
        
        $fromDate = $request->get('fromDate');
        $toDate = $request->get('toDate');
        
        try {
            // Fetch data from API with 10 minute timeout
            $apiUrl = "http://172.16.22.204/dashboardApi/autosecure/getDealerClaims.php";
            
            $response = Http::timeout(600)->get($apiUrl, [
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Check if API returned expected data structure
                if (!isset($data['autosecure']) || !isset($data['autosecure']['data'])) {
                    return view('DealerReports.dealersummary_report', [
                        'summaryData' => [],
                        'fromDate' => $fromDate,
                        'toDate' => $toDate,
                        'showReport' => true,
                        'info' => 'No data found for the selected period.'
                    ]);
                }
                
                // Get autosecure data and process GIS data
                $autosecureData = $data['autosecure']['data'] ?? [];
                $gisData = $data['gis'] ?? [];
                
                // Create a lookup array for GIS claims
                $gisLookup = [];
                foreach ($gisData as $gis) {
                    if (isset($gis['GID_BASEDOCUMENTNO'])) {
                        $gisLookup[$gis['GID_BASEDOCUMENTNO']] = $gis['TOT_CLM'] ?? 0;
                    }
                }
                
                // Process and group data by dealer_code
                $dealerSummary = [];
                
                foreach ($autosecureData as $claim) {
                    $dealerCode = $claim['dealer_code'] ?? 'Unknown';
                    $gisNumber = $claim['gis'] ?? '';
                    
                    // Get TOT_CLM from GIS lookup or default to 0
                    $totClm = isset($gisLookup[$gisNumber]) ? (float)$gisLookup[$gisNumber] : 0;
                    
                    // Get premium and iev values
                    $premium = isset($claim['premium']) ? (float)$claim['premium'] : 0;
                    $iev = isset($claim['iev']) ? (float)$claim['iev'] : 0;
                    
                    // Initialize dealer summary if not exists
                    if (!isset($dealerSummary[$dealerCode])) {
                        $dealerSummary[$dealerCode] = [
                            'dealer_code' => $dealerCode,
                            'count' => 0,
                            'iev_sum' => 0,
                            'premium_sum' => 0,
                            'totclaims_sum' => 0
                        ];
                    }
                    
                    // Update dealer summary
                    $dealerSummary[$dealerCode]['count']++;
                    $dealerSummary[$dealerCode]['iev_sum'] += $iev;
                    $dealerSummary[$dealerCode]['premium_sum'] += $premium;
                    $dealerSummary[$dealerCode]['totclaims_sum'] += $totClm;
                }
                
                // Convert associative array to indexed array for easier use in view
                $summaryData = array_values($dealerSummary);
                
                // Sort by count (descending)
                usort($summaryData, function($a, $b) {
                    return $b['count'] - $a['count'];
                });
                
                $recordCount = count($summaryData);
                
                return view('DealerReports.dealersummary_report', [
                    'summaryData' => $summaryData,
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                    'showReport' => true,
                    'success' => "Dealer Summary Report generated successfully! Found {$recordCount} dealers."
                ]);
                
            } else {
                $statusCode = $response->status();
                
                return view('DealerReports.dealersummary_report', [
                    'summaryData' => [],
                    'error' => "API Request Failed (Status: {$statusCode})",
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                    'showReport' => false
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error("DealerSummaryReport: Error - " . $e->getMessage());
            
            return view('DealerReports.dealersummary_report', [
                'summaryData' => [],
                'error' => 'Error: ' . $e->getMessage(),
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'showReport' => false
            ]);
        }
    }
}
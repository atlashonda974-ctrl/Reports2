<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DoWiseReportController extends Controller
{
    public function index()
    {
        return view('DealerReports.DOWISE_report', [
            'claims' => [],
            'fromDate' => date('Y-m-d', strtotime('-30 days')),
            'toDate' => date('Y-m-d'),
            'showReport' => false
        ]);
    }
    
    public function generate(Request $request)
    {
        $request->validate([
            'fromDate' => 'required|date',
            'toDate' => 'required|date|after_or_equal:fromDate'
        ]);
        
        $fromDate = $request->get('fromDate');
        $toDate = $request->get('toDate');
        
        try {
      
            $apiUrl = "http://172.16.22.204/dashboardApi/autosecure/getDealerClaims.php";
            
    
            $date1 = new \DateTime($fromDate);
            $date2 = new \DateTime($toDate);
            $interval = $date1->diff($date2);
            $diffDays = $interval->days;
            
            \Log::info("DOWiseReport: Fetching data for {$fromDate} to {$toDate} ({$diffDays} days)");
            
            $response = Http::timeout(600)->get($apiUrl, [
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
              
                if (!isset($data['autosecure'])) {
                    \Log::error("DOWiseReport: API returned invalid structure - 'autosecure' key missing", ['response' => $data]);
                    
                    return view('DealerReports.DOWISE_report', [
                        'claims' => [],
                        'error' => 'API Error: Invalid data structure received. The API did not return the expected format. Please contact support.',
                        'fromDate' => $fromDate,
                        'toDate' => $toDate,
                        'showReport' => false
                    ]);
                }
                
                if (!isset($data['autosecure']['data'])) {
                    \Log::warning("DOWiseReport: No data found in API response", ['response' => $data]);
                    
                    return view('DealerReports.DOWISE_report', [
                        'claims' => [],
                        'fromDate' => $fromDate,
                        'toDate' => $toDate,
                        'showReport' => true,
                        'info' => 'No claims data found for the selected period'
                    ]);
                }
                dd(\App\Models\BranchesList::all()->toArray());
              
                $processedClaims = $this->processClaimsData($data);
                
              
                $doNames = $this->extractDONames($processedClaims);
                
                $recordCount = count($processedClaims);
                \Log::info("DOWiseReport: Found {$recordCount} records");
                
                return view('DealerReports.DOWISE_report', [
                    'claims' => $processedClaims,
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                    'showReport' => true,
                    'success' => "Report generated successfully! Found {$recordCount} records",
                    'doNames' => $doNames
                ]);
            } else {
                $statusCode = $response->status();
                $errorMessage = "API Request Failed (Status: {$statusCode})";
                
                \Log::error("DOWiseReport: API request failed with status {$statusCode}");
                
                return view('DealerReports.DOWISE_report', [
                    'claims' => [],
                    'error' => $errorMessage,
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                    'showReport' => false
                ]);
            }
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error("DOWiseReport: Connection failed - " . $e->getMessage());
            
            return view('DealerReports.DOWISE_report', [
                'claims' => [],
                'error' => 'Connection Error: Unable to connect to the API server. Please check if the API server is running and accessible.',
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'showReport' => false
            ]);
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            \Log::error("DOWiseReport: Request exception - " . $e->getMessage());
            
            return view('DealerReports.DOWISE_report', [
                'claims' => [],
                'error' => 'Request Timeout: The API request timed out. This may be due to a large date range or server issues. Please try a smaller date range or try again later.',
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'showReport' => false
            ]);
            
        } catch (\Exception $e) {
            \Log::error("DOWiseReport: Unexpected error - " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('DealerReports.DOWISE_report', [
                'claims' => [],
                'error' => 'Unexpected Error: ' . $e->getMessage() . '. Please contact support.',
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'showReport' => false
            ]);
        }
    }
    
    private function extractDONames($claims)
    {
        $doNames = [];
        $nameMap = [];
        
        foreach ($claims as $claim) {
            $doName = $claim['con_per'] ?? '';
            if ($doName && !in_array($doName, $nameMap)) {
                $nameMap[] = $doName;
                $doNames[] = [
                    'name' => $doName,
                    'dealer_code' => $claim['dealer_code'] ?? 'N/A'
                ];
            }
        }
        
     
        usort($doNames, function($a, $b) {
            return strcmp($a['name'] ?? '', $b['name'] ?? '');
        });
        
        return $doNames;
    }
    
    private function processClaimsData($data)
    {
        $autosecureData = $data['autosecure']['data'] ?? [];
        $gisData = $data['gis'] ?? [];
        
      
        $gisLookup = [];
        foreach ($gisData as $gis) {
            if (isset($gis['GID_BASEDOCUMENTNO'])) {
                $gisLookup[$gis['GID_BASEDOCUMENTNO']] = $gis['TOT_CLM'] ?? 0;
            }
        }

        $processedClaims = [];
        foreach ($autosecureData as $claim) {
            $gisNumber = $claim['gis'] ?? '';
      
            $claim['tot_clm'] = isset($gisLookup[$gisNumber]) ? $gisLookup[$gisNumber] : '0';
            
            $processedClaims[] = $claim;
        }
        
        return $processedClaims;
    }




   public function dosummaryReport(Request $request)
{
    
    if (!$request->has('fromDate') || !$request->has('toDate')) {
        return view('DealerReports.DoSummary-report', [
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
        
        $apiUrl = "http://172.16.22.204/dashboardApi/autosecure/getDealerClaims.php";
        
        $response = Http::timeout(600)->get($apiUrl, [
            'fromDate' => $fromDate,
            'toDate' => $toDate
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            
           
            if (!isset($data['autosecure']) || !isset($data['autosecure']['data'])) {
                return view('DealerReports.DoSummary-report', [
                    'summaryData' => [],
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                    'showReport' => true,
                    'info' => 'No data found for the selected period.'
                ]);
            }
            
           
            $autosecureData = $data['autosecure']['data'] ?? [];
            $gisData = $data['gis'] ?? [];
            
            $gisLookup = [];
            foreach ($gisData as $gis) {
                if (isset($gis['GID_BASEDOCUMENTNO'])) {
                    $gisLookup[$gis['GID_BASEDOCUMENTNO']] = $gis['TOT_CLM'] ?? 0;
                }
            }
         
            $doSummary = [];
            
            foreach ($autosecureData as $claim) {
                $doName = $claim['con_per'] ?? 'Unknown D/O';
                $gisNumber = $claim['gis'] ?? '';
                
        
                $totClm = isset($gisLookup[$gisNumber]) ? (float)$gisLookup[$gisNumber] : 0;
                
           
                $premium = isset($claim['premium']) ? (float)$claim['premium'] : 0;
                $iev = isset($claim['iev']) ? (float)$claim['iev'] : 0;
                
        
                if (!isset($doSummary[$doName])) {
                    $doSummary[$doName] = [
                        'do_name' => $doName,
                        'count' => 0,
                        'iev_sum' => 0,
                        'premium_sum' => 0,
                        'totclaims_sum' => 0
                    ];
                }
                
            
                $doSummary[$doName]['count']++;
                $doSummary[$doName]['iev_sum'] += $iev;
                $doSummary[$doName]['premium_sum'] += $premium;
                $doSummary[$doName]['totclaims_sum'] += $totClm;
            }
            
        
            $summaryData = array_values($doSummary);
            
        
            usort($summaryData, function($a, $b) {
                return $b['count'] - $a['count'];
            });
            
            $recordCount = count($summaryData);
            
            return view('DealerReports.DoSummary-report', [
                'summaryData' => $summaryData,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'showReport' => true,
                'success' => "D/O Summary Report generated successfully! Found {$recordCount} D/Os."
            ]);
            
        } else {
            $statusCode = $response->status();
            
            return view('DealerReports.DoSummary-report', [
                'summaryData' => [],
                'error' => "API Request Failed (Status: {$statusCode})",
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'showReport' => false
            ]);
        }
        
    } catch (\Exception $e) {
        \Log::error("DOSummaryReport: Error - " . $e->getMessage());
        
        return view('DealerReports.DoSummary-report', [
            'summaryData' => [],
            'error' => 'Error: ' . $e->getMessage(),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'showReport' => false
        ]);
    }
}
}
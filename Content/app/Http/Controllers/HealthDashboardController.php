<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HealthDashboardController extends Controller
{
    public function index()
    {
        // Fetch data from the actual API
        $apiResponse = $this->fetchHealthPolicyDataFromAPI();
        
        // Prepare data for the dashboard
        $dashboardData = $this->prepareDashboardData($apiResponse);
        
        return view('health-dashboard', $dashboardData);
    }

    private function fetchHealthPolicyDataFromAPI()
    {
        try {
            $response = Http::get('http://172.16.22.204/dashboardApi/health_portal/getPolicyStats.php', [
                'insured' => '1100100002'
            ]);
            
            if ($response->successful()) {
                return $response->json();
            } else {
                return $this->getEmptyDataStructure();
            }
            
        } catch (\Exception $e) {
            // Log error if needed
            // \Log::error('API Error: ' . $e->getMessage());
            return $this->getEmptyDataStructure();
        }
    }

    private function getEmptyDataStructure()
    {
        return [
            'POLICY' => [
                'STATUS' => 'Not Found',
                'PLC_LOC_CODE' => '',
                'PLC_LOCADESC' => '',
                'GDH_DOCUMENTNO' => '',
                'GDH_DOC_REFERENCE_NO' => '',
                'PPS_DESC' => 'No Data Available',
                'GDH_ISSUEDATE' => '',
                'GDH_COMMDATE' => '',
                'GDH_EXPIRYDATE' => '',
                'GDH_GROSSPREMIUM' => '0',
                'GDH_TOTALSI' => '0',
                'GDH_NETPREMIUM' => '0',
                'PAS_ADDRESS1' => ''
            ],
            'MEMBERS' => [
                'MEM_COUNT' => '0'
            ],
            'DEPENDENTS' => [
                'FAMILY_COUNT' => '0'
            ],
            'OS_COL' => [
                'TOT_COL' => '0'
            ],
            'CLM' => [
                'TOT_CLM' => '0'
            ],
            'LOSS_CODE' => [],
            'TOP_5_MEM' => []
        ];
    }

    private function prepareDashboardData($apiData)
    {
        // Check if we have the expected data structure
        if (!isset($apiData['POLICY']) || !isset($apiData['MEMBERS']) || !isset($apiData['DEPENDENTS'])) {
            $apiData = $this->getEmptyDataStructure();
        }
        
        $policy = $apiData['POLICY'];
        $members = $apiData['MEMBERS'];
        $dependents = $apiData['DEPENDENTS'];
        
        $totalLives = (int) ($members['MEM_COUNT'] ?? 0) + (int) ($dependents['FAMILY_COUNT'] ?? 0);
        
        // Calculate Outstanding Collection = GDH_NETPREMIUM - TOT_COL
        $netPremium = (float) ($policy['GDH_NETPREMIUM'] ?? 0);
        $totalCollected = (float) ($apiData['OS_COL']['TOT_COL'] ?? 0);
        $outstandingCollection = $netPremium - $totalCollected;
        
        // Ensure it's not negative
        if ($outstandingCollection < 0) {
            $outstandingCollection = 0;
        }

        return [
            'policy_info' => [
                'insured_name' => isset($policy['PPS_DESC']) ? trim(rtrim($policy['PPS_DESC'], ', ')) : 'N/A',
                'issue_date' => $policy['GDH_ISSUEDATE'] ?? 'N/A',
                'expiry_date' => $policy['GDH_EXPIRYDATE'] ?? 'N/A',
                'net_premium' => isset($policy['GDH_NETPREMIUM']) ? number_format((float) $policy['GDH_NETPREMIUM']) : '0',
                'branch_name' => $policy['PLC_LOCADESC'] ?? 'N/A',
                'policy_number' => $policy['GDH_DOC_REFERENCE_NO'] ?? 'N/A',
                'gross_premium' => isset($policy['GDH_GROSSPREMIUM']) ? number_format((float) $policy['GDH_GROSSPREMIUM']) : '0',
                'total_si' => isset($policy['GDH_TOTALSI']) ? number_format((float) $policy['GDH_TOTALSI']) : '0',
                'commencement_date' => $policy['GDH_COMMDATE'] ?? 'N/A',
                'net_premium_raw' => $netPremium,
                'total_collected_raw' => $totalCollected
            ],
            'lives_info' => [
                'employees' => isset($members['MEM_COUNT']) ? number_format((int) $members['MEM_COUNT']) : '0',
                'dependents' => isset($dependents['FAMILY_COUNT']) ? number_format((int) $dependents['FAMILY_COUNT']) : '0',
                'total_lives' => number_format($totalLives),
                'mem_count_raw' => (int) ($members['MEM_COUNT'] ?? 0),
                'family_count_raw' => (int) ($dependents['FAMILY_COUNT'] ?? 0)
            ],
            'os_collection' => [
                'total_collection' => number_format($outstandingCollection),
                'total_collection_raw' => $outstandingCollection,
                'net_premium' => number_format($netPremium),
                'collected_amount' => number_format($totalCollected)
            ],
            'claims' => [
                'total_claims' => isset($apiData['CLM']['TOT_CLM']) ? number_format((float) $apiData['CLM']['TOT_CLM']) : '0',
                'total_claims_raw' => (float) ($apiData['CLM']['TOT_CLM'] ?? 0)
            ],
            'loss_codes' => $apiData['LOSS_CODE'] ?? [],
            'top_members' => $apiData['TOP_5_MEM'] ?? [],
            'apiData' => $apiData
        ];
    }
}
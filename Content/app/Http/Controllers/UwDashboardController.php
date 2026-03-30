<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Budget; 
use Illuminate\Support\Facades\DB;

class UwDashboardController extends Controller
{
   public function index()
{
    $cacheKey = 'uw_dashboard_data';
    $cacheDurationMinutes = 30;
    $refreshBeforeMinutes = 28;

    $cachedData = Cache::get($cacheKey);

    // Refresh cache if needed
    if ($cachedData && isset($cachedData['last_refresh'])) {
        $lastRefresh = strtotime($cachedData['last_refresh']);
        $refreshTime = $lastRefresh + ($refreshBeforeMinutes * 60);
        if (time() >= $refreshTime) {
            $this->refreshCache($cacheKey, $cacheDurationMinutes);
        }
    }

    // Get cached data or refresh
    if ($cachedData && isset($cachedData['data'])) {
        $data = $cachedData['data'];
    } else {
        $data = $this->refreshCache($cacheKey, $cacheDurationMinutes);
        if (!$data) {
            return view('uw-dashboard')->with('error', 'Unable to fetch data from API');
        }
    }

    $current = $data['Current'][0] ?? [];
    $previous = $data['Previous'][0] ?? [];
    $monthwise = $data['Monthwise'] ?? [];
    $monthwisePrev = $data['Monthwise_prev'] ?? [];
    $broker = $data['Broker'] ?? [];

    $monthwise_pre_arr = [];
    $monthwise_pre_prev_arr = [];

    for ($i = 1; $i <= 12; $i++) {
        $month = str_pad($i, 2, '0', STR_PAD_LEFT);

        // Current year month
        $currentMonth = collect($monthwise)->first(function($item) use ($month) {
            return trim($item['YEAR_MONTH']) === date('Y') . "-{$month}";
        });
        $monthwise_pre_arr[] = $currentMonth['TOT_PRE'] ?? 0;

        // Previous year month
        $prevMonth = collect($monthwisePrev)->first(function($item) use ($month) {
            return trim($item['YEAR_MONTH']) === (date('Y') - 1) . "-{$month}";
        });
        $monthwise_pre_prev_arr[] = $prevMonth['TOT_PRE'] ?? 0;
    }

    $cur_month = date('m');
    $cur_year  = date('Y');

    // Upto current month (total) - SUM across all branches
    $upto_month = DB::table('budgets')
        ->where('month', '<=', $cur_month)
        ->where('year', $cur_year)
        ->selectRaw('SUM(CAST(uwfire AS DECIMAL(15,2)) + CAST(uwmar AS DECIMAL(15,2)) + CAST(uwmor AS DECIMAL(15,2)) + CAST(uwmisc AS DECIMAL(15,2)) + CAST(uwhlt AS DECIMAL(15,2))) as total_sum')
        ->first();

    // Total budget for current year - SUM across all branches
    $total_budget = DB::table('budgets')
        ->where('year', $cur_year)
        ->selectRaw('SUM(CAST(uwfire AS DECIMAL(15,2)) + CAST(uwmar AS DECIMAL(15,2)) + CAST(uwmor AS DECIMAL(15,2)) + CAST(uwmisc AS DECIMAL(15,2)) + CAST(uwhlt AS DECIMAL(15,2))) as total_sum')
        ->first();

    // Upto month per department - SUM across all branches
    $upto_month_department = DB::table('budgets')
        ->where('month', '<=', $cur_month)
        ->where('year', $cur_year)
        ->selectRaw('
            SUM(CAST(uwfire AS DECIMAL(15,2))) as Fire,
            SUM(CAST(uwmar AS DECIMAL(15,2))) as Marine,
            SUM(CAST(uwmor AS DECIMAL(15,2))) as Motor,
            SUM(CAST(uwmisc AS DECIMAL(15,2))) as Misc,
            SUM(CAST(uwhlt AS DECIMAL(15,2))) as Health
        ')
        ->first();

    // Monthly budget data for current year - GROUP BY month and SUM all branches
    $monthly_budgets = DB::table('budgets')
        ->where('year', $cur_year)
        ->selectRaw('
            month,
            SUM(CAST(uwfire AS DECIMAL(15,2))) as uwfire,
            SUM(CAST(uwmar AS DECIMAL(15,2))) as uwmar,
            SUM(CAST(uwmor AS DECIMAL(15,2))) as uwmor,
            SUM(CAST(uwmisc AS DECIMAL(15,2))) as uwmisc,
            SUM(CAST(uwhlt AS DECIMAL(15,2))) as uwhlt
        ')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

    // Build monthly budget array
    $monthwise_budget_arr = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthBudget = $monthly_budgets->firstWhere('month', $i);
        
        if ($monthBudget) {
            // Calculate the total for this month
            $total = ($monthBudget->uwfire ?? 0) + 
                     ($monthBudget->uwmar ?? 0) + 
                     ($monthBudget->uwmor ?? 0) + 
                     ($monthBudget->uwmisc ?? 0) + 
                     ($monthBudget->uwhlt ?? 0);
            $monthwise_budget_arr[] = $total;
        } else {
            $monthwise_budget_arr[] = 0;
        }
    }

    $topBranches = collect($data['Branch'] ?? [])->map(function($item) {
        return [
            'name' => $item['PLC_LOCADESC'],
            'TOT_PRE' => $item['TOT_PRE'],
        ];
    })->take(5);

    $topBrokers = collect($data['Broker'] ?? [])->map(function($item) {
        return [
            'name' => $item['PPS_DESC'],
            'TOT_PRE' => $item['TOT_PRE'],
        ];
    })->take(5);

    $topInsured = collect($data['Insured'] ?? [])->map(function($item) {
        return [
            'name' => $item['PPS_DESC'],
            'TOT_PRE' => $item['TOT_PRE'],
        ];
    })->take(5);

    $topDOs = collect($data['DO'] ?? [])->map(function($item) {
        return [
            'name' => $item['PDO_DEVOFFDESC'],
            'TOT_PRE' => $item['TOT_PRE'],
        ];
    })->take(5);

    // Renewal data
    $renew = $data['RENEW'][0] ?? ['TOTAL_DOCS' => 0, 'TOT_PRE' => 0];
    $total_renew_docs = $renew['TOTAL_DOCS'];
    $total_renew_premium = $renew['TOT_PRE'];

    $departments = ['Fire', 'Marine', 'Motor', 'Misc', 'Health'];
    $deptData = [];
    foreach ($departments as $dept) {
        $key = $dept; 
        $deptData[$dept] = [
            'CY' => $current[strtoupper($dept).'PRE'] ?? 0,
            'PY' => $previous[strtoupper($dept).'PRE'] ?? 0,
            'Upto' => $upto_month_department->$key ?? 0,
        ];
    }

    return view('uw-dashboard', compact(
        'data',
        'current',
        'previous',
        'monthwise_pre_arr',
        'monthwise_pre_prev_arr',
        'monthwise_budget_arr', 
        'broker',
        'upto_month',
        'total_budget',
        'total_renew_docs',
        'total_renew_premium',
        'upto_month_department',
        'deptData',
        'departments',
        'topBranches',
        'topBrokers',
        'topInsured',
        'topDOs'
    ));
}


   
    private function refreshCache($cacheKey, $cacheDurationMinutes)
    {
        $apiUrl = 'http://172.16.22.204/dashboardApi/branch_portal/uw_data.php?expiryfrom=01-Jan-2025&expiryto=01-Dec-2025';

        try {
            $response = Http::get($apiUrl);
            $mainData = $response->json();

            if (!empty($mainData)) {
                $cachePayload = [
                    'data' => $mainData,
                    'last_refresh' => now()->toDateTimeString()
                ];

                Cache::put($cacheKey, $cachePayload, $cacheDurationMinutes * 60);

                return $mainData;
            }
        } catch (\Exception $e) {
            
        }

       
        return $cachedData['data'] ?? null;
    }
}

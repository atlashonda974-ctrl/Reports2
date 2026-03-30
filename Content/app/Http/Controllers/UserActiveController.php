<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Helpers\Helper;
use Illuminate\Support\Str;



class UserActiveController extends Controller

{
    
    public function index(Request $request)
    {
        // Retrieve user data
        $data = Helper::fetchUser();

        // Check for errors in data fetching
        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], 500);
        }

        // Convert data to a collection
        $data = collect($data);
        // Today's date
        $today = now()->toDateString();

        // Filter logins and logouts for today
        $loginsToday = $data->filter(function ($user) use ($today) {
            return isset($user['SUL_LOGINDATE']) && Carbon::createFromFormat('d-M-y h.i.s.u A', $user['SUL_LOGINDATE'])->toDateString() === $today;
        })->unique('SUS_NAME'); // Ensure unique logins

        $logoutsToday = $data->filter(function ($user) use ($today) {
            return isset($user['SUS_LASTLOGIN']) 
                && Carbon::createFromFormat('d-M-y', $user['SUS_LASTLOGIN'])->toDateString() === $today;
        })->unique('SUS_NAME');


        // Ensure unique logouts

         //dd($logoutsToday );
        // Counts
        $loginsTodayCount = $loginsToday->count();
        $logoutsTodayCount = $logoutsToday->count();
        //dd($logoutsTodayCount);
        //dd($logoutsTodayCount);

        // Extract all unique branch names (locations) before filtering
        $locations = $data->pluck('PLC_DESC')->unique()->values();

        // Filter users based on selected user status
        if ($request->has('user_status')) {
            $userStatus = $request->input('user_status');

            if ($userStatus === 'A') {
                $data = $data->filter(fn($user) => isset($user['SUS_ACTIVE']) && $user['SUS_ACTIVE'] === 'A');
            } elseif ($userStatus === 'I') {
                $data = $data->filter(fn($user) => isset($user['SUS_ACTIVE']) && $user['SUS_ACTIVE'] === 'I');
            }
        }

        // Branch Filtering
        if ($request->filled('location')) {
            $location = $request->input('location');
            $data = $data->filter(fn($user) => isset($user['PLC_DESC']) && $user['PLC_DESC'] === $location);
        }
        
        // Default to today's login timeframe if not specified
        $timeframe = $request->input('login_timeframe', 'today');
        $now = now();

        // Login Date Filtering
        $data = $data->filter(function ($user) use ($timeframe, $now) {
            $lastLogin = isset($user['SUS_LASTLOGIN']) ? \Carbon\Carbon::parse($user['SUS_LASTLOGIN']) : null;

            if (!$lastLogin) {
                return false;
            }

            switch ($timeframe) {
                case 'today':
                    return $lastLogin->toDateString() === $now->toDateString();
                case 'yesterday':
                    return $lastLogin->toDateString() === $now->copy()->subDay()->toDateString();
                case '2days':
                    return $lastLogin->toDateString() === $now->copy()->subDays(2)->toDateString();
                case '3days':
                    return $lastLogin->toDateString() === $now->copy()->subDays(3)->toDateString();
                case '2w':
                    return $lastLogin->greaterThanOrEqualTo($now->copy()->subWeeks(2)->startOfDay());
                case '3w':
                    return $lastLogin->greaterThanOrEqualTo($now->copy()->subWeeks(3)->startOfDay());
                case '1m':
                    return $lastLogin->greaterThanOrEqualTo($now->copy()->subMonth()->startOfDay());
                case 'more1m':
                    return $lastLogin->lessThan($now->copy()->subMonth()->startOfDay());
                default:
                    return true;
            }
        });

        // Count active and inactive users
        $activeCount = $data->where('SUS_ACTIVE', 'A')->unique('SUS_NAME')->count();
        $inactiveCount = $data->where('SUS_ACTIVE', 'I')->unique('SUS_NAME')->count();

        // Ensure SUS_NAME is unique for the full user list
        $data = $data->unique('SUS_NAME');

        // Count the total number of unique users after filtering
        $totalCount = $data->count();

        // Calculate branch-wise statistics (only for displayed users)
        $branchStats = $data->groupBy('PLC_DESC')->map(function ($branchUsers) {
            return [
                'total' => $branchUsers->count(),
                'active' => $branchUsers->where('SUS_ACTIVE', 'A')->count(),
                'inactive' => $branchUsers->where('SUS_ACTIVE', 'I')->count(),
                'users' => $branchUsers->sortBy('SUS_NAME')->values()
            ];
        })->sortByDesc('total');

        // Pass the results to the view
        return view('usersactive.index', compact(
            'data', 
            'totalCount', 
            'activeCount', 
            'inactiveCount', 
            'locations',
            'branchStats',
            'loginsTodayCount', 
            'logoutsTodayCount', 
            'loginsToday', 
            'logoutsToday'
        ));
    }
    
//    public function index(Request $request)
// {
//     // Retrieve user data
//     $data = Helper::fetchUser();

//     // Check for errors
//     if (isset($data['error'])) {
//         return response()->json(['error' => $data['error']], 500);
//     }

//     $data = collect($data);

//     // Get all unique locations (for filtering)
//     $locations = $data->pluck('PLC_DESC')->unique()->values();

//     // Filter users based on selected status (if provided)
//     if ($request->has('user_status')) {
//         $userStatus = $request->input('user_status');
//         if ($userStatus === 'A') {
//             $data = $data->where('SUS_ACTIVE', 'A');
//         } elseif ($userStatus === 'I') {
//             $data = $data->where('SUS_ACTIVE', 'I');
//         }
//     }

//     // Filter by location (if provided)
//     if ($request->filled('location')) {
//         $location = $request->input('location');
//         $data = $data->where('PLC_DESC', $location);
//     }

//     // Count ACTIVE & INACTIVE users (without unique())
//     $activeCount = $data->where('SUS_ACTIVE', 'A')->count();
//     $inactiveCount = $data->where('SUS_ACTIVE', 'I')->count();
//     $totalCount = $data->count(); // Should be 15,848

//     // Branch-wise statistics
//     $branchStats = $data->groupBy('PLC_DESC')->map(function ($branchUsers) {
//         return [
//             'total' => $branchUsers->count(),
//             'active' => $branchUsers->where('SUS_ACTIVE', 'A')->count(),
//             'inactive' => $branchUsers->where('SUS_ACTIVE', 'I')->count(),
//             'users' => $branchUsers->sortBy('SUS_NAME')->values()
//         ];
//     })->sortByDesc('total');

//     return view('usersactive.index', compact(
//         'data', 
//         'totalCount', 
//         'activeCount', 
//         'inactiveCount', 
//         'locations',
//         'branchStats'
//     ));
// } with full only shows active / inactive and total

    // public function getLoginsByDate(Request $request)
    // {
    //     $date = $request->input('date');
    //     $search = $request->input('search');

    //     // Fetch all login data from API
    //     $loginData = Helper::fetchUser();
        
    //     // Convert to collection for easier manipulation
    //     $logins = collect($loginData);

    //     // Apply date filter if provided
    //     if ($date) {
    //         $filterDate = Carbon::createFromFormat('Y-m-d', $date);
    //         $logins = $logins->filter(function ($login) use ($filterDate) {
    //             try {
    //                 // Check if SUL_LOGINDATE exists, otherwise use SUS_LASTLOGIN
    //                 $dateField = $login['SUL_LOGINDATE'] ?? $login['SUS_LASTLOGIN'] ?? null;
    //                 if (!$dateField) return false;
                    
    //                 $cleanedDate = preg_replace('/\.\d+ /', ' ', $dateField);
    //                 $loginDate = Carbon::createFromFormat('d-M-y h.i.s A', $cleanedDate);
    //                 return $loginDate->isSameDay($filterDate);
    //             } catch (\Exception $e) {
    //                 return false;
    //             }
    //         });
    //     }

    //     // Apply search filter if provided
    //     if ($search) {
    //         $search = strtolower($search);
    //         $logins = $logins->filter(function ($login) use ($search) {
    //             return str_contains(strtolower($login['SUS_NAME'] ?? ''), $search) ||
    //                 str_contains(strtolower($login['SUS_USERCODE'] ?? ''), $search) ||
    //                 str_contains(strtolower($login['PLC_DESC'] ?? ''), $search);
    //         });
    //     }

    //     return response()->json(['data' => $logins->values()->all()]);
    // }
    public function getLoginsByDate(Request $request)
    {
        $date = $request->input('date') ?? now()->format('Y-m-d');
        $search = $request->input('search');

        // Fetch all login data from API
        $loginData = Helper::fetchUser();
        
        // Convert to collection for easier manipulation
        $logins = collect($loginData);

        // Apply date filter for today's logins
        $filterDate = Carbon::createFromFormat('Y-m-d', $date);
        $logins = $logins->filter(function ($login) use ($filterDate) {
            try {
                // Check if SUL_LOGINDATE exists, otherwise use SUS_LASTLOGIN
                $dateField = $login['SUL_LOGINDATE'] ?? $login['SUS_LASTLOGIN'] ?? null;
                if (!$dateField) return false;
                
                $cleanedDate = preg_replace('/\.\d+ /', ' ', $dateField);
                $loginDate = Carbon::createFromFormat('d-M-y h.i.s A', $cleanedDate);
                return $loginDate->isSameDay($filterDate);
            } catch (\Exception $e) {
                return false;
            }
        });

        // Apply search filter if provided
        if ($search) {
            $search = strtolower($search);
            $logins = $logins->filter(function ($login) use ($search) {
                return str_contains(strtolower($login['SUS_NAME'] ?? ''), $search) ||
                    str_contains(strtolower($login['SUS_USERCODE'] ?? ''), $search) ||
                    str_contains(strtolower($login['PLC_DESC'] ?? ''), $search);
            });
        }

        // Ensure unique users based on SUS_NAME
        $uniqueLogins = $logins->unique('SUS_NAME');

        return response()->json(['data' => $uniqueLogins->values()->all()]);
    }


public function getLogoutsByDate(Request $request)
{
    $date = $request->input('date') ?? now()->format('Y-m-d');
    $search = $request->input('search');

    // Fetch all user data (adjust this to your actual data source)
    $logoutData = Helper::fetchUser();
    $logouts = collect($logoutData);

    // Ensure unique users by SUS_NAME
    $logouts = $logouts->unique('SUS_NAME');

    // Apply date filter if provided
    $filterDate = Carbon::createFromFormat('Y-m-d', $date);
    $logouts = $logouts->filter(function ($logout) use ($filterDate) {
        try {
            $dateField = $logout['SUS_LASTLOGIN'] ?? null;
            if (!$dateField) return false;

            $logoutDate = Carbon::createFromFormat('d-M-y', $dateField);
            return $logoutDate->isSameDay($filterDate);
        } catch (\Exception $e) {
            return false;
        }
    });

    // Apply search filter if provided
    if ($search) {
        $search = strtolower($search);
        $logouts = $logouts->filter(function ($logout) use ($search) {
            return str_contains(strtolower($logout['SUS_NAME'] ?? ''), $search) ||
                str_contains(strtolower($logout['SUS_USERCODE'] ?? ''), $search) ||
                str_contains(strtolower($logout['PLC_DESC'] ?? ''), $search);
        });
    }

    // Sort by the most recent logout date
    $logouts = $logouts->sortByDesc(function ($logout) {
        return Carbon::createFromFormat('d-M-y', $logout['SUS_LASTLOGIN'])->timestamp;
    });

    return response()->json(['data' => $logouts->values()->all()]);
}




}

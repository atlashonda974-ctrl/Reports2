<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\BranchesList;
use App\Models\Feedback;
use Illuminate\Support\Facades\Session;


use App\Helpers\ClaimHelper;
use Illuminate\Support\Facades\Log;

class ClaimController extends Controller
{
    public function index(Request $request)
    {
        // Debug: Log all incoming request data
        Log::info('Request Inputs:', $request->all());

        // 1. Get dates from request or set defaults (last 30 days)
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $formStartDate = $startDate ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $formEndDate = $endDate ?? Carbon::now()->format('Y-m-d');

        // 2. Define additional parameters from request or set defaults
        $branchCode = $request->input('location_category', 'All');

        // 3. Get branch code and takaful code based on selected branch
        $branch = $branchCode ?: 'All'; // Ensure branch is 'All' if null or empty
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
        if ($request->filled('new_category')) {
            $selectedCategory = $request->input('new_category');
            Log::info('Selected Category: ' . $selectedCategory);
            if (isset($categoryMapping[$selectedCategory])) {
                $dept = $categoryMapping[$selectedCategory];
                Log::info('Mapped Department Code: ' . $dept);
            } else {
                Log::warning('Invalid Category Selected: ' . $selectedCategory);
            }
        } else {
            Log::info('No Category Selected, Defaulting to dept=All');
        }

        // Debug: Log all parameters before API call
        Log::info('API Call Parameters:', [
            'start_date' => $formStartDate,
            'end_date' => $formEndDate,
            'dept' => $dept,
            'branch' => $branch,
            'takaful' => $takaful,
        ]);

        // 5. Get data from API using helper
        $result = ClaimHelper::getClaim($formStartDate, $formEndDate, $dept, $branch, $takaful);

        // 6. Check API response status
        if ($result['status'] === 'error') {
            Log::error('Claim API Error: ' . $result['message']);
            return view('claimregister.claimcase', [
                'data' => [],
                'start_date' => $formStartDate,
                'end_date' => $formEndDate,
                'selected_category' => $request->input('new_category', ''),
                'branches' => BranchesList::all(),
                'error_message' => $result['message'],
            ]);
        }

        // 7. Handle API response data
        $data = collect($result['data'] ?? [])->map(function ($item) {
            if (is_string($item)) {
                $decoded = json_decode($item);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON decode error: ' . json_last_error_msg() . ' for item: ' . $item);
                    return (object) [];
                }
                return $decoded;
            }
            return (object) $item;
        });

        // Debug: Log the number of records returned
        Log::info('API Returned ' . $data->count() . ' Records');

        // 8. Get all branches for dropdown
        $branches = BranchesList::all();

        // 9. Return view with data
        return view('claimregister.claimcase', [
            'data' => $data,
            'start_date' => $formStartDate,
            'end_date' => $formEndDate,
            'selected_category' => $request->input('new_category', ''),
            'branches' => $branches,
            'error_message' => null,
        ]);
    }
   
    public function claim2(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $formStartDate = $startDate ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $formEndDate = $endDate ?? Carbon::now()->format('Y-m-d');
        $branchCode = $request->input('location_category', 'All');
        $selectedCategory = $request->input('new_category', '');
        $timeFilter = $request->input('time_filter', 'all');

        $insuInput = $request->input('insu', []);
        $insu = is_array($insuInput) && !empty($insuInput) ? implode(',', $insuInput) : 'All';

        $branch = $branchCode ?: 'All';
        $takaful = 'All';
        if ($branch !== 'All' && !empty($branchCode)) {
            $selectedBranch = BranchesList::where('fbracode', $branchCode)->first();
            if ($selectedBranch) {
                $takaful = $selectedBranch->fbratak;
            }
        }

        $dept = 'All';
        $categoryMapping = [
            'Fire' => 11,
            'Marine' => 12,
            'Motor' => 13,
            'Miscellaneous' => 14,
            'Health' => 16,
        ];

        if (isset($categoryMapping[$selectedCategory])) {
            $dept = $categoryMapping[$selectedCategory];
        }

        $result = ClaimHelper::getClaimR2($formStartDate, $formEndDate, $dept, $branch, $takaful, $insu);

        $data = collect($result['data'] ?? [])->map(function ($item) {
            return is_string($item) ? json_decode($item) : (object) $item;
        });

        $filteredData = $data;
        if ($timeFilter !== 'all') {
            $filteredData = $data->filter(function ($item) use ($timeFilter) {
                if (empty($item->GIH_INTIMATIONDATE)) {
                    return false;
                }

                $days = Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now());

                switch ($timeFilter) {
                    case '2days': return $days <= 2;
                    case '5days': return $days <= 5;
                    case '7days': return $days <= 7;
                    case '10days': return $days <= 10;
                    case '15days': return $days <= 15;
                    case '15plus': return $days > 15;
                    default: return true;
                }
            });
        }

        $counts = [
            'all' => $data->count(),
            '2days' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 2;
            })->count(),
            '5days' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 5;
            })->count(),
            '7days' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 7;
            })->count(),
            '10days' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 10;
            })->count(),
            '15days' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 15;
            })->count(),
            '15plus' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) > 15;
            })->count(),
        ];

        $branches = BranchesList::all();
        return view('claimregister.claimr2', [
            'data' => $filteredData,
            'start_date' => $formStartDate,
            'end_date' => $formEndDate,
            'selected_category' => $selectedCategory,
            'branches' => $branches,
            'error_message' => null,
            'selected_time_filter' => $timeFilter,
            'counts' => $counts,
        ]);
    }

   public function claim3(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $formStartDate = $startDate ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $formEndDate = $endDate ?? Carbon::now()->format('Y-m-d');
        $branchCode = $request->input('location_category', 'All');
        $selectedCategory = $request->input('new_category', '');
        $timeFilter = $request->input('time_filter', 'all');

        $insuInput = $request->input('insu', []);
        $insu = is_array($insuInput) && !empty($insuInput) ? $insuInput : ['All'];

        $branch = $branchCode ?: 'All';
        $takaful = 'All';
        
        if ($branch !== 'All' && !empty($branchCode)) {
            $selectedBranch = BranchesList::where('fbracode', $branchCode)->first();
            if ($selectedBranch) {
                $takaful = $selectedBranch->fbratak;
            }
        }

        $dept = 'All';
        $categoryMapping = [
            'Fire' => 11,
            'Marine' => 12,
            'Motor' => 13,
            'Miscellaneous' => 14,
            'Health' => 16,
        ];

        if (isset($categoryMapping[$selectedCategory])) {
            $dept = $categoryMapping[$selectedCategory];
        }

        $result = ClaimHelper::getClaimR3($formStartDate, $formEndDate, $dept, $branch, $takaful, $insu);

        $data = collect($result['data'] ?? [])->map(function ($item) {
            return is_string($item) ? json_decode($item) : (object) $item;
        });

        $filteredData = $data;
        if ($timeFilter !== 'all') {
            $filteredData = $data->filter(function ($item) use ($timeFilter) {
                $days = Carbon::parse($item->GUD_APPOINTMENTDATE)->diffInDays(Carbon::now());
                
                switch ($timeFilter) {
                    case '2days': return $days <= 2;
                    case '5days': return $days <= 5;
                    case '7days': return $days <= 7;
                    case '10days': return $days <= 10;
                    case '15days': return $days <= 15;
                    case '15plus': return $days > 15;
                    default: return true;
                }
            });
        }

        $counts = [
            'all' => $data->count(),
            '2days' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_APPOINTMENTDATE)->diffInDays(Carbon::now()) <= 2;
            })->count(),
            '5days' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_APPOINTMENTDATE)->diffInDays(Carbon::now()) <= 5;
            })->count(),
            '7days' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_APPOINTMENTDATE)->diffInDays(Carbon::now()) <= 7;
            })->count(),
            '10days' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_APPOINTMENTDATE)->diffInDays(Carbon::now()) <= 10;
            })->count(),
            '15days' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_APPOINTMENTDATE)->diffInDays(Carbon::now()) <= 15;
            })->count(),
            '15plus' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_APPOINTMENTDATE)->diffInDays(Carbon::now()) > 15;
            })->count(),
        ];

        $branches = BranchesList::all();

        return view('claimregister.claimr3', [
            'data' => $filteredData,
            'start_date' => $formStartDate,
            'end_date' => $formEndDate,
            'selected_category' => $selectedCategory,
            'branches' => $branches,
            'error_message' => null,
            'selected_time_filter' => $timeFilter,
            'counts' => $counts,
        ]);
    }
    
    public function claim4(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $formStartDate = $startDate ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $formEndDate = $endDate ?? Carbon::now()->format('Y-m-d');

        $branchCode = $request->input('location_category', 'All');
        $selectedCategory = $request->input('new_category', '');
        $timeFilter = $request->input('time_filter', 'all');
        
        $insuInput = $request->input('insu', []);
        $insu = is_array($insuInput) && !empty($insuInput) ? implode(',', $insuInput) : 'All';

        $branch = $branchCode ?: 'All';
        $takaful = 'All';
        
        if ($branch !== 'All' && !empty($branchCode)) {
            $selectedBranch = BranchesList::where('fbracode', $branchCode)->first();
            if ($selectedBranch) {
                $takaful = $selectedBranch->fbratak;
            }
        }

        $dept = 'All';
        $categoryMapping = [
            'Fire' => 11,
            'Marine' => 12,
            'Motor' => 13,
            'Miscellaneous' => 14,
            'Health' => 16,
        ];
        
        if (isset($categoryMapping[$selectedCategory])) {
            $dept = $categoryMapping[$selectedCategory];
        }

        $result = ClaimHelper::getClaimR4($formStartDate, $formEndDate, $dept, $branch, $takaful, $insu);

        $data = collect($result['data'] ?? [])->map(function ($item) {
        return is_string($item) ? json_decode($item) : (object) $item;
        });

        $filteredData = $data;
        if ($timeFilter !== 'all') {
            $filteredData = $data->filter(function ($item) use ($timeFilter) {
                $days = Carbon::parse($item->GUD_REPORT_DATE)->diffInDays(Carbon::now());
                
                switch ($timeFilter) {
                    case '2days': return $days <= 2;
                    case '5days': return $days <= 5;
                    case '7days': return $days <= 7;
                    case '10days': return $days <= 10;
                    case '15days': return $days <= 15;
                    case '15plus': return $days > 15;
                    default: return true;
                }
            });
        }

        $counts = [
            'all' => $data->count(),
            '2days' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_REPORT_DATE)->diffInDays(Carbon::now()) <= 2;
            })->count(),
            '5days' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_REPORT_DATE)->diffInDays(Carbon::now()) <= 5;
            })->count(),
            '7days' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_REPORT_DATE)->diffInDays(Carbon::now()) <= 7;
            })->count(),
            '10days' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_REPORT_DATE)->diffInDays(Carbon::now()) <= 10;
            })->count(),
            '15days' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_REPORT_DATE)->diffInDays(Carbon::now()) <= 15;
            })->count(),
            '15plus' => $data->filter(function ($item) {
                return Carbon::parse($item->GUD_REPORT_DATE)->diffInDays(Carbon::now()) > 15;
            })->count(),
        ];

        $branches = BranchesList::all();

        return view('claimregister.claim4', [
            'data' => $filteredData,
            'start_date' => $formStartDate,
            'end_date' => $formEndDate,
            'selected_category' => $selectedCategory,
            'branches' => $branches,
            'error_message' => null,
            'selected_time_filter' => $timeFilter,
            'counts' => $counts,
        ]);
    }

    //  public function claim5(Request $request)
    // {
    //     $startDate = $request->input('start_date');
    //     $endDate = $request->input('end_date');
    //     $formStartDate = $startDate ?? Carbon::now()->subDays(30)->format('Y-m-d');
    //     $formEndDate = $endDate ?? Carbon::now()->format('Y-m-d');

    //     $branchCode = $request->input('location_category', 'All');
    //     $selectedCategory = $request->input('new_category', '');
    //     $timeFilter = $request->input('time_filter', 'all');
        
    //     $insuInput = $request->input('insu', []);
    //     $insu = is_array($insuInput) && !empty($insuInput) ? implode(',', $insuInput) : 'All';

    //     $branch = $branchCode ?: 'All';
    //     $takaful = 'All';
        
    //     if ($branch !== 'All' && !empty($branchCode)) {
    //         $selectedBranch = BranchesList::where('fbracode', $branchCode)->first();
    //         if ($selectedBranch) {
    //             $takaful = $selectedBranch->fbratak;
    //         }
    //     }

    //     $dept = 'All';
    //     $categoryMapping = [
    //         'Fire' => 11,
    //         'Marine' => 12,
    //         'Motor' => 13,
    //         'Miscellaneous' => 14,
    //         'Health' => 16,
    //     ];
        
    //     if (isset($categoryMapping[$selectedCategory])) {
    //         $dept = $categoryMapping[$selectedCategory];
    //     }

    //     $result = ClaimHelper::getClaimR5($formStartDate, $formEndDate, $dept, $branch, $takaful, $insu);

    //     $data = collect($result['data'] ?? [])->map(function ($item) {
    //     return is_string($item) ? json_decode($item) : (object) $item;
    //     });

    //     $filteredData = $data;
    //     if ($timeFilter !== 'all') {
    //         $filteredData = $data->filter(function ($item) use ($timeFilter) {
    //             $days = Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now());
                
    //             switch ($timeFilter) {
    //                 case '2days': return $days <= 2;
    //                 case '5days': return $days <= 5;
    //                 case '7days': return $days <= 7;
    //                 case '10days': return $days <= 10;
    //                 case '15days': return $days <= 15;
    //                 case '15plus': return $days > 15;
    //                 default: return true;
    //             }
    //         });
    //     }

    //     $counts = [
    //         'all' => $data->count(),
    //         '2days' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 2;
    //         })->count(),
    //         '5days' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 5;
    //         })->count(),
    //         '7days' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 7;
    //         })->count(),
    //         '10days' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 10;
    //         })->count(),
    //         '15days' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 15;
    //         })->count(),
    //         '15plus' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) > 15;
    //         })->count(),
    //     ];

    //     $branches = BranchesList::all();

    //     return view('claimregister.claim5', [
    //         'data' => $filteredData,
    //         'start_date' => $formStartDate,
    //         'end_date' => $formEndDate,
    //         'selected_category' => $selectedCategory,
    //         'branches' => $branches,
    //         'error_message' => null,
    //         'selected_time_filter' => $timeFilter,
    //         'counts' => $counts,
    //     ]);
    // }

  public function claim5(Request $request)
    {
        // $data = Feedback::all();
        // dd($data);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $formStartDate = $startDate ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $formEndDate = $endDate ?? Carbon::now()->format('Y-m-d');

        $branchCode = $request->input('location_category', 'All');
        $selectedCategory = $request->input('new_category', '');
        $timeFilter = $request->input('time_filter', 'all');
        
        $insuInput = $request->input('insu', []);
        $insu = is_array($insuInput) && !empty($insuInput) ? implode(',', $insuInput) : 'All';

        $branch = $branchCode ?: 'All';
        $takaful = 'All';
        
        if ($branch !== 'All' && !empty($branchCode)) {
            $selectedBranch = BranchesList::where('fbracode', $branchCode)->first();
            if ($selectedBranch) {
                $takaful = $selectedBranch->fbratak;
            }
        }

        $dept = 'All';
        $categoryMapping = [
            'Fire' => 11,
            'Marine' => 12,
            'Motor' => 13,
            'Miscellaneous' => 14,
            'Health' => 16,
        ];
        
        if (isset($categoryMapping[$selectedCategory])) {
            $dept = $categoryMapping[$selectedCategory];
        }

        $result = ClaimHelper::getClaimR5($formStartDate, $formEndDate, $dept, $branch, $takaful, $insu);

        $data = collect($result['data'] ?? [])->map(function ($item) {
            return is_string($item) ? json_decode($item) : (object) $item;
        });

        $completedDocs = Feedback::pluck('uw_doc')->toArray();
        $data = $data->filter(function ($record) use ($completedDocs) {
            $docReference = $record->GIH_DOC_REF_NO ?? '';
            $isExcluded = in_array($docReference, $completedDocs);
            Log::debug('Filtering record', [
                'GIH_DOC_REF_NO' => $docReference,
                'isExcluded' => $isExcluded
            ]);
            return !$isExcluded;
        });

        $filteredData = $data;
        if ($timeFilter !== 'all') {
            $filteredData = $data->filter(function ($item) use ($timeFilter) {
                $days = Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now());
                
                switch ($timeFilter) {
                    case '2days': return $days <= 2;
                    case '5days': return $days <= 5;
                    case '7days': return $days <= 7;
                    case '10days': return $days <= 10;
                    case '15days': return $days <= 15;
                    case '15plus': return $days > 15;
                    default: return true;
                }
            });
        }

        $counts = [
            'all' => $data->count(),
            '2days' => $data->filter(function ($item) {
                return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 2;
            })->count(),
            '5days' => $data->filter(function ($item) {
                return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 5;
            })->count(),
            '7days' => $data->filter(function ($item) {
                return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 7;
            })->count(),
            '10days' => $data->filter(function ($item) {
                return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 10;
            })->count(),
            '15days' => $data->filter(function ($item) {
                return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 15;
            })->count(),
            '15plus' => $data->filter(function ($item) {
                return Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) > 15;
            })->count(),
        ];

        $branches = BranchesList::all();

        return view('claimregister.claim5', [
            'data' => $filteredData,
            'start_date' => $formStartDate,
            'end_date' => $formEndDate,
            'selected_category' => $selectedCategory,
            'branches' => $branches,
            'error_message' => null,
            'selected_time_filter' => $timeFilter,
            'counts' => $counts,
        ]);
    }

    public function storeFeedback(Request $request)
    {
        $request->validate([
            'uw_doc' => 'required|string',
            'surv_prof' => 'nullable|in:1,2,3,4,5',
            'surv_resp' => 'nullable|in:1,2,3,4,5',
            'surv_acc' => 'nullable|in:1,2,3,4,5',
            'surv_overall' => 'nullable|in:1,2,3,4,5',
            'clt_req' => 'nullable|in:1,2,3,4,5',
            'clt_info' => 'nullable|in:1,2,3,4,5',
            'clt_coop' => 'nullable|in:1,2,3,4,5',
            'clt_overall' => 'nullable|in:1,2,3,4,5',
        ]);

        try {
            Feedback::create([
                'uw_doc' => $request->uw_doc,
                'surv_prof' => $request->surv_prof,
                'surv_resp' => $request->surv_resp,
                'surv_acc' => $request->surv_acc,
                'surv_overall' => $request->surv_overall,
                'clt_req' => $request->clt_req,
                'clt_info' => $request->clt_info,
                'clt_coop' => $request->clt_coop,
                'clt_overall' => $request->clt_overall,
                'created_by' => auth()->user()->name ?? 'System',
                'updated_by' => auth()->user()->name ?? 'System',
            ]);

            return response()->json(['success' => true, 'message' => 'Feedback saved successfully.']);
        } catch (\Exception $e) {
            Log::error('Feedback submission failed: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['success' => false, 'message' => 'Failed to save feedback: ' . $e->getMessage()], 500);
        }
    }

    public function completeAction(Request $request, $id)
    {
        try {
            $hasFeedback = Feedback::where('uw_doc', $id)->exists();
            if (!$hasFeedback) {
                return response()->json(['success' => false, 'message' => 'Please submit remarks first.'], 403);
            }

            return response()->json(['success' => true, 'message' => 'Action completed successfully.']);
        } catch (\Exception $e) {
            Log::error('Action completion failed: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['success' => false, 'message' => 'Failed to complete action: ' . $e->getMessage()], 500);
        }
    }
    //
    
    // public function claim6(Request $request)
    // {
    //     $startDate = $request->input('start_date');
    //     $endDate = $request->input('end_date');
    //     $formStartDate = $startDate ?? Carbon::now()->subDays(30)->format('Y-m-d');
    //     $formEndDate = $endDate ?? Carbon::now()->format('Y-m-d');

    //     $branchCode = $request->input('location_category', 'All');
    //     $selectedCategory = $request->input('new_category', '');
    //     $timeFilter = $request->input('time_filter', 'all');
        
    //     $insuInput = $request->input('insu', []);
    //     $insu = is_array($insuInput) && !empty($insuInput) ? implode(',', $insuInput) : 'All';

    //     $branch = $branchCode ?: 'All';
    //     $takaful = 'All';
        
    //     if ($branch !== 'All' && !empty($branchCode)) {
    //         $selectedBranch = BranchesList::where('fbracode', $branchCode)->first();
    //         if ($selectedBranch) {
    //             $takaful = $selectedBranch->fbratak;
    //         }
    //     }

    //     $dept = 'All';
    //     $categoryMapping = [
    //         'Fire' => 11,
    //         'Marine' => 12,
    //         'Motor' => 13,
    //         'Miscellaneous' => 14,
    //         'Health' => 16,
    //     ];
        
    //     if (isset($categoryMapping[$selectedCategory])) {
    //         $dept = $categoryMapping[$selectedCategory];
    //     }

    //     $result = ClaimHelper::getClaimR6($formStartDate, $formEndDate, $dept, $branch, $takaful ,  $insu);
    //     //dd(  $result );

    //     $data = collect($result['data'] ?? [])->map(function ($item) {
    //     return is_string($item) ? json_decode($item) : (object) $item;
    //     });
    //     //dd($data );

    //     $filteredData = $data;
    //     if ($timeFilter !== 'all') {
    //         $filteredData = $data->filter(function ($item) use ($timeFilter) {
    //             $days = Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now());
                
    //             switch ($timeFilter) {
    //                 case '2days': return $days <= 2;
    //                 case '5days': return $days <= 5;
    //                 case '7days': return $days <= 7;
    //                 case '10days': return $days <= 10;
    //                 case '15days': return $days <= 15;
    //                 case '15plus': return $days > 15;
    //                 default: return true;
    //             }
    //         });
    //     }

    //     $counts = [
    //         'all' => $data->count(),
    //         '2days' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 2;
    //         })->count(),
    //         '5days' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 5;
    //         })->count(),
    //         '7days' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 7;
    //         })->count(),
    //         '10days' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 10;
    //         })->count(),
    //         '15days' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 15;
    //         })->count(),
    //         '15plus' => $data->filter(function ($item) {
    //             return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) > 15;
    //         })->count(),
    //     ];

    //     $branches = BranchesList::all();
    //     $userRole = Session::get('user')['role'] ?? null;
    //     //dd($userRole);

    //     return view('claimregister.claim6', [
    //         'data' => $filteredData,
    //         'start_date' => $formStartDate,
    //         'end_date' => $formEndDate,
    //         'selected_category' => $selectedCategory,
    //         'branches' => $branches,
    //         'error_message' => null,
    //         'selected_time_filter' => $timeFilter,
    //         'counts' => $counts,
    //         'userRole' => $userRole,
    //     ]);
    // }
    public function claim6(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $formStartDate = $startDate ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $formEndDate = $endDate ?? Carbon::now()->format('Y-m-d');

        $branchCode = $request->input('location_category', 'All');
        $selectedCategory = $request->input('new_category', '');
        $timeFilter = $request->input('time_filter', 'all');
        $businessType = $request->input('business_type', 'all'); 
        
        $insuInput = $request->input('insu', []);
        $insu = is_array($insuInput) && !empty($insuInput) ? implode(',', $insuInput) : 'All';

        // Initialize branch and takaful variables
        $branch = 'All';
        $takaful = 'All';
        
        // Handle branch and takaful logic based on business type
        if ($branchCode !== 'All' && !empty($branchCode)) {
            $selectedBranch = BranchesList::where('fbracode', $branchCode)->first();
            
            if ($selectedBranch) {
                switch ($businessType) {
                    case 'takaful':
                        // For Takaful: use takaful code as branch, set branch to 0
                        $branch = 0;
                        $takaful = $selectedBranch->fbratak;
                        break;
                        
                    case 'conventional':
                        // For Conventional: use branch code, set takaful to 0
                        $branch = $selectedBranch->fbracode;
                        $takaful = 0;
                        break;
                        
                    case 'all':
                    default:
                        // For All: use both branch and takaful codes
                        $branch = $selectedBranch->fbracode;
                        $takaful = $selectedBranch->fbratak;
                        break;
                }
            }
        } else {
            // If no specific branch is selected, handle business type for 'All'
            if ($businessType === 'takaful') {
                $branch = 0;
                $takaful = 'All';
            } elseif ($businessType === 'conventional') {
                $branch = 'All';
                $takaful = 0;
            }
            // For 'all', both remain 'All'
        }

        $dept = 'All';
        $categoryMapping = [
            'Fire' => 11,
            'Marine' => 12,
            'Motor' => 13,
            'Miscellaneous' => 14,
            'Health' => 16,
        ];
        
        if (isset($categoryMapping[$selectedCategory])) {
            $dept = $categoryMapping[$selectedCategory];
        }

        $result = ClaimHelper::getClaimR6($formStartDate, $formEndDate, $dept, $branch, $takaful, $insu);

        $data = collect($result['data'] ?? [])->map(function ($item) {
            return is_string($item) ? json_decode($item) : (object) $item;
        });

        $filteredData = $data;
        if ($timeFilter !== 'all') {
            $filteredData = $data->filter(function ($item) use ($timeFilter) {
                $days = Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now());
                
                switch ($timeFilter) {
                    case '2days': return $days <= 2;
                    case '5days': return $days <= 5;
                    case '7days': return $days <= 7;
                    case '10days': return $days <= 10;
                    case '15days': return $days <= 15;
                    case '15plus': return $days > 15;
                    default: return true;
                }
            });
        }

        $counts = [
            'all' => $data->count(),
            '2days' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 2;
            })->count(),
            '5days' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 5;
            })->count(),
            '7days' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 7;
            })->count(),
            '10days' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 10;
            })->count(),
            '15days' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) <= 15;
            })->count(),
            '15plus' => $data->filter(function ($item) {
                return Carbon::parse($item->GIH_INTIMATIONDATE)->diffInDays(Carbon::now()) > 15;
            })->count(),
        ];

        $branches = BranchesList::all();
        $userRole = Session::get('user')['role'] ?? null;

        return view('claimregister.claim6', [
            'data' => $filteredData,
            'start_date' => $formStartDate,
            'end_date' => $formEndDate,
            'selected_category' => $selectedCategory,
            'branches' => $branches,
            'error_message' => null,
            'selected_time_filter' => $timeFilter,
            'selected_business_type' => $businessType,
            'counts' => $counts,
            'userRole' => $userRole,
        ]);
    }
}
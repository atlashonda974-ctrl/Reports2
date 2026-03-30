<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session; // Ensure Session facade is imported
use App\Models\GlApproval; // Assuming GlApproval is a Model for gl_approval table
use App\Models\BranchesList; // Assuming BranchesList is a Model
use App\Helpers\ClaimHelper; // Assuming ClaimHelper is a Helper class
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;


class GlClmController extends Controller
{

    public function stlClmOS(Request $request)
    {
        $currentUser = Session::get('user')['name'];
        $dept = Session::get('user')['dept'];
        $zone = Session::get('user')['zone'];
        $userLvl = Session::get('user')['lvl'] ?? 0; // Get user level, default to 0 if not set
        $userLim = Session::get('user')['gl'];
        

        // Get docs that are fully approved by the final level (e.g., lvl 3) - permanently removed
        $fullyApprovedDocs = GlApproval::where('approve', 'approved')->pluck('doc')->toArray();
        
        // Get docs that the CURRENT USER has already OK'd/approved - user-specific removal
        $userActionedDocs = GlApproval::where('created_by', $currentUser)->pluck('doc')->toArray();

        // --- Filtering Parameters from Request ---
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $formStartDate = $startDate ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $formEndDate = $endDate ?? Carbon::now()->format('Y-m-d');

        $branchCode = $request->input('location_category', 'All');
        $selectedCategory = $request->input('new_category', '');
        $timeFilter = $request->input('time_filter', 'all');
        
        $insuInput = $request->input('insu', []);
        $insu = is_array($insuInput) && !empty($insuInput) ? implode(',', $insuInput) : 'All';

        $allBranches = BranchesList::all();
        $branch = $branchCode ?: 'All';
        $takaful = 'All';
        if ($branch !== 'All' && !empty($branchCode)) {
            $selectedBranch = BranchesList::where('fbracode', $branchCode)->first();
            if ($selectedBranch) {
                $takaful = $selectedBranch->fbratak;
            }
        }

        $categoryMapping = [
            'Fire' => 11, 'Marine' => 12, 'Motor' => 13,
            'Miscellaneous' => 14, 'Health' => 16,
        ];
        $deptFilter = $dept; // Start with user's dept
        if (isset($categoryMapping[$selectedCategory])) {
            $deptFilter = $categoryMapping[$selectedCategory]; // Override if department is filtered
        }


        // 1. Fetch all relevant data (This must be done first before filtering)
        $result = ClaimHelper::getClaimR11($formStartDate, $formEndDate, $deptFilter, $branch, $takaful, $insu, $zone);
        $data = collect($result['data'] ?? [])->map(function ($item) {
            return is_string($item) ? json_decode($item) : (object) $item;
        });
        

        // 2. Base Filtering: Exclude globally approved and user's own actioned docs
        $data = $data->filter(function ($item) use ($fullyApprovedDocs, $userActionedDocs) {
            $doc = $item->GSH_DOC_REF_NO ?? null;
            if (!$doc) return false;
            if (in_array($doc, $fullyApprovedDocs)) return false; // Fully approved
            if (in_array($doc, $userActionedDocs)) return false;    // User already actioned
            return true;
        });


        // 3. Sequential Approval Filtering based on User Level
        $filteredData = $data->filter(function ($item) use ($userLvl) {
            $doc = $item->GSH_DOC_REF_NO ?? null;
            if (!$doc) return false;

            if ($userLvl == 1) {
                // Lvl 1: Should see all records that have NO approval yet.
                $hasApproval = GlApproval::where('doc', $doc)
                                         ->whereNotNull('approve') // Exclude remarks-only entries
                                         ->exists();
                return !$hasApproval;
            }

            if ($userLvl > 1) {
                $prevLvl = $userLvl - 1;
                
                // Check for approval from the previous level
                $prevApproval = GlApproval::where('doc', $doc)
                                          ->where('lvl', $prevLvl)
                                          ->where('approve', 'OK') // Must be OK'd by previous level
                                          ->first();
                
                if ($prevApproval) {
                    // Check if current level has already approved this document
                    $currentLvlApproval = GlApproval::where('doc', $doc)
                                                    ->where('lvl', $userLvl)
                                                    ->whereNotNull('approve')
                                                    ->exists();
                    return !$currentLvlApproval;
                }
            }

            return false; // Does not meet approval criteria for this level
        });


        // 4. Set Button Type and Approval Eligibility on the final filtered data
        $filteredData = $filteredData->map(function ($item) use ($userLvl, $userLim) {
            $item->can_approve = false;
            $item->button_type = null;

            // Unlimited user (-1) can always approve, and their approval is 'approved'
            if ($userLim == '-1') {
                $item->button_type = 'approve'; 
                $item->can_approve = true;
                return $item;
            }
            
            // For limited users, the first step is 'OK', subsequent steps are 'approve' if limit is met.
            if ($userLvl == 1) {
                // Lvl 1: Always 'OK'
                $item->button_type = 'OK';
                $item->can_approve = true;
            } elseif ($userLvl > 1) {
                // Lvl 2 and higher: 'Approve' if amount is within limit
                $item->button_type = 'approve';
                $item->can_approve = ($item->GSH_LOSSADJUSTED ?? 0) <= $userLim;
            }

            return $item;
        });
        
        
        // 5. Apply Time Filter (applied after base and sequential filtering)
        if ($timeFilter !== 'all') {
            $filteredData = $filteredData->filter(function ($item) use ($timeFilter) {
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


        // 6. Counts for dashboard filtering (based on final $filteredData)
        $counts = [
            'all' => $filteredData->count(),
            '2days' => $filteredData->filter(fn($item) => Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 2)->count(),
            '5days' => $filteredData->filter(fn($item) => Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 5)->count(),
            '7days' => $filteredData->filter(fn($item) => Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 7)->count(),
            '10days' => $filteredData->filter(fn($item) => Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 10)->count(),
            '15days' => $filteredData->filter(fn($item) => Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) <= 15)->count(),
            '15plus' => $filteredData->filter(fn($item) => Carbon::parse($item->GSH_SETTLEMENTDATE)->diffInDays(Carbon::now()) > 15)->count(),
        ];
        
        return view('claimregister.claim11', [
            'data' => $filteredData,
            'start_date' => $formStartDate,
            'end_date' => $formEndDate,
            'selected_category' => $selectedCategory,
            'branch' => $branches ?? [],
            'branches' => $allBranches,
            'error_message' => null,
            'selected_time_filter' => $timeFilter,
            'counts' => $counts,
            'created_by' => $currentUser
        ]);
    }


    public function insertApproval(Request $request)
    {
        
        $doc       = $request->doc;
        $inRange   = $request->in_range;
        $remakrs   = $request->remakrs;
        $createdBy = Session::get('user')['name']; 
        $userLimit = Session::get('user')['gl'];
        $userLvl   = Session::get('user')['lvl'] ?? 0;
        
        // Use a generic status for sequential approval stages (OK is appropriate for intermediate steps)
        // Only final level approval is marked as 'approved' to permanently remove it from all queues.

        // Determine the approval status based on user level (or limit)
        $status = 'OK'; // Default for all intermediate approvals
        
        // Final Level Approval Check: Determine the highest level (e.g., 3) and mark their approval as 'approved'
        // Since we don't know the max level, we use the user's limit (-1) as the 'final' marker for now,
        // or you can check if $userLvl is the highest possible level in your system (e.g., 3)
        // I will use 'approved' for users with 'gl' = '-1' (unlimited/final approver)
        // if ($userLimit == '-1') {
        //     $status = 'approved';
        // }
        // TODO: If you have a strict max level (e.g., 3), you can check: if ($userLvl == 3) $status = 'approved';

        // CASE 1: Only Remarks (in_range = 0 means it's just a remark, not an approval)
        if ($inRange == 0 && !empty($remakrs)) {
            // Find existing record for this user and doc (including remarks-only)
            $existingApproval = GlApproval::where('doc', $doc)
                                          ->where('created_by', $createdBy)
                                          ->first();
            
            if ($existingApproval) {
                // Update existing record's remarks
                $existingApproval->remakrs = $remakrs;
                $existingApproval->save();
            } else {
                // Create new record with only remarks
                GlApproval::create([
                    'doc'        => $doc,
                    'approve'    => null, // Null for remarks only
                    'in_range'   => 0,
                    'remakrs'    => $remakrs,
                    'created_by' => $createdBy,
                    'updated_by' => null,
                    'lvl'        => $userLvl,
                ]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Remarks saved successfully.'
            ]);
        }

        // CASE 2: Actual Approval (OK or Approve button)

        // 2a. Check for duplicate approval by the same user
        $existingApproval = GlApproval::where('doc', $doc)
                                      ->where('created_by', $createdBy)
                                      ->whereNotNull('approve')
                                      ->first();
        if ($existingApproval) {
             return response()->json([
                 'status'  => false,
                 'message' => 'You have already approved this document.'
             ], 403);
        }

        // 2b. Check if the amount exceeds the limit for non-final approvers
        if ($userLimit != '-1' && $inRange > $userLimit) {
            return response()->json([
                'status'  => false,
                'message' => 'Amount exceeds your approval limit.'
            ], 403);
        }
        
        // 2c. Check for and update existing remarks-only record, or create a new approval record
        $remarksOnlyRecord = GlApproval::where('doc', $doc)
                                       ->where('created_by', $createdBy)
                                       ->whereNull('approve')
                                       ->first();
        
        if ($remarksOnlyRecord) {
            // Update existing remarks-only record with approval data
            $remarksOnlyRecord->approve = $status;
            $remarksOnlyRecord->in_range = $inRange;
            $remarksOnlyRecord->save();
        } else {
            // Create new approval record
            GlApproval::create([
                'doc'        => $doc,
                'approve'    => $status,
                'in_range'   => $inRange,
                'remakrs'    => $remakrs ?? '',
                'created_by' => $createdBy,
                'updated_by' => null,
                'lvl'        => $userLvl,
            ]);
        }

        return response()->json([
            'status'  => true,
            'approve' => $status,
            'message' => 'Approval recorded successfully.'
        ]);
    }




    
       public function getRemarks(Request $request)
    {
        $doc = $request->input('doc');
        
        if (empty($doc)) {
            return response()->json([
                'status' => false,
                'message' => 'Document reference is required',
                'remarks' => []
            ]);
        }

        // Fetch remarks from gl_approval table
        $remarks = GlApproval::where('doc', $doc)
            ->where(function($query) {
                // Get both remarks-only entries AND approval entries with remarks
                $query->whereNotNull('remakrs')
                      ->orWhere(function($q) {
                          $q->whereNotNull('approve')
                            ->where('remakrs', '<>', '');
                      });
            })
            ->where('remakrs', '<>', '') // Ensure remarks field is not empty
            ->orderBy('created_at', 'desc')
            ->get(['remakrs', 'created_by', 'created_at', 'approve', 'lvl'])
            ->map(function($remark) {
                return [
                    'remarks' => $remark->remakrs,
                    'created_by' => $remark->created_by,
                    'created_at' => $remark->created_at ? $remark->created_at->format('Y-m-d H:i:s') : null,
                    'approval_status' => $remark->approve,
                    'level' => $remark->lvl
                ];
            });

        return response()->json([
            'status' => true,
            'remarks' => $remarks
        ]);
    }



public function getClaimSettlementPerforma(Request $request)
    {
        $docNum = $request->query('doc_num');
        
        if (!$docNum) {
            return response()->json([
                'success' => false,
                'message' => 'Document number is required'
            ], 400);
        }

        $files = DB::table('claim_docs')
            ->where('doc_num', $docNum)
            ->get(['id', 'repname', 'doc_num', 'file_name', 'created_at']);

        if ($files->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No Claim Settlement Performa found for this document.',
                'files' => []
            ], 404);
        }

       
        $fileData = $files->map(function ($file) {
            return [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'repname' => $file->repname,
                'doc_num' => $file->doc_num,
                'created_at' => $file->created_at,
                'download_url' => route('downloadClaimDoc', [
                    'filename' => $file->file_name
                ]) . '?doc_num=' . urlencode($file->doc_num)
            ];
        });

        return response()->json([
            'success' => true,
            'files' => $fileData,
            'count' => $files->count(),
            'message' => 'Found ' . $files->count() . ' file(s)'
        ]);
    }

    public function downloadClaimDoc($filename, Request $request)
    {
        $docNum = $request->query('doc_num');
        
        if (!$docNum) {
            abort(400, 'Document number is required');
        }

        
        $fileRecord = DB::table('claim_docs')
            ->where('doc_num', $docNum)
            ->where('file_name', $filename)
            ->first();

        if (!$fileRecord) {
            abort(404, 'File not found in database');
        }

        

         $filePath = storage_path("app/public/claims/{$docNum}/{$filename}");

         if (!File::exists($filePath)) {
                abort(404, 'File not found on storage');
            }

            $mimeType = File::mimeType($filePath) ?? 'application/octet-stream';

            return response()->download(
                $filePath,
                $filename,
                [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'attachment; filename="'.$filename.'"'
                ]
            );
    }


}
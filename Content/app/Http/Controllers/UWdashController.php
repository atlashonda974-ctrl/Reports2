<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str; 
use App\Models\BranchesList; 
use App\Helpers\Helper; 
use App\Models\PolicyRenewal; 

class UWdashController extends Controller 
{
    public function getRenewalDataBr(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $dept = $request->input('dept') ?: 'All';

        $result = Helper::fetchRenewalData($startDate, $endDate, $dept);

        $user = Session::get('user');
        $brConv = (string)($user['loc_code'] ?? '');
        $brTak  = (string)($user['loc_code_tak'] ?? '');

        if (isset($result['error'])) {
            $data = collect([]);
            \Log::error('Renewal data fetch failed:', $result);
        } else {
            // Get DISTINCT document numbers that have ANY saved decision
            $decidedDocNos = PolicyRenewal::select('document_no')
                ->distinct()
                ->pluck('document_no')
                ->map(fn($v) => (string)$v)
                ->toArray();

            $data = collect($result['data'] ?? [])->filter(function($record) use ($brConv, $brTak, $decidedDocNos) {
                $recordBranch = (string)($record['PLC_LOC_CODE'] ?? '');
                $docNo        = (string)($record['GDH_DOC_REFERENCE_NO'] ?? '');

                // Must belong to user's branch AND not already have any saved decision
                return ($recordBranch === $brConv || $recordBranch === $brTak)
                    && !in_array($docNo, $decidedDocNos);
            })->values();
        }

        $defaultStartDate = $startDate
            ? Carbon::parse($startDate)->format('d-M-Y')
            : Carbon::now()->startOfYear()->format('d-M-Y');
        $defaultEndDate = $endDate
            ? Carbon::parse($endDate)->format('d-M-Y')
            : Carbon::now()->format('d-M-Y');

        $branches = BranchesList::all();

        return view('renewal_br', [
            'data'             => $data,
            'start_date'       => $defaultStartDate,
            'end_date'         => $defaultEndDate,
            'total_records'    => $data->count(),
            'branches'         => $branches,
            'filtered_records' => $data->count(),
            'user_branch'      => $brConv,
            'user_takaful'     => $brTak,
            'api_error'        => $result['error'] ?? null,
        ]);
    }

    public function saveRenewalDecision(Request $request)
    {
        $request->validate([
            'document_no'      => 'required',
            'renewal_decision' => 'required|boolean',
            'remarks'          => 'required_if:renewal_decision,0|nullable|string|max:1000',
        ]);

        
        PolicyRenewal::create([
            'document_no'      => $request->document_no,
            'base_document'    => $request->base_document,
            'insured_name'     => $request->insured_name,
            'expiry_date'      => Carbon::createFromFormat('d-m-Y', $request->expiry_date)->format('Y-m-d'),
            'renewal_decision' => $request->renewal_decision,
            'remarks'          => $request->remarks ?? '',
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Renewal decision saved successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Renewal decision saved successfully.');
    }
}
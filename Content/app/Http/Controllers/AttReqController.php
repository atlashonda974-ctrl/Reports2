<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttReq;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
class AttReqController extends Controller
{
    /**
     * Display datatable listing
     */
   public function index(Request $request)
{

    if ($request->ajax()) {
        $data = AttReq::orderBy('id', 'desc')->get();
        
        return response()->json([
            'data' => $data,
            'total' => $data->count()
        ]);
    }
    
    // For regular request, fetch and pass data to the view
    $data = AttReq::orderBy('id', 'Asc')->get();
    return view('att_reqs.index', compact('data'));
}

    /**
     * Show create form
     */
    public function create()
    {
        $attendanceTypes = ['Manual', 'Official Visit', 'Travel', 'Training'];
        return view('att_reqs.create', compact('attendanceTypes'));
    }

    /**
     * Store new request
     */
    public function store(Request $request)
    {
        $request->validate([
            'empcode' => 'required|string|max:20',
            'schddate' => 'required|date',
            'att' => 'required|string|max:50',
            'remarks' => 'nullable|string'
        ]);

        // Check for duplicate empcode + schddate
        $exists = AttReq::where('empcode', $request->empcode)
                        ->where('schddate', $request->schddate)
                        ->exists();
        
        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['empcode' => 'Attendance record already exists for this employee on this date.']);
        }

        AttReq::create([
            'empcode' => $request->empcode,
            'schddate' => $request->schddate,
            'att' => $request->att,
            'remarks' => $request->remarks,
            'created_by' => Auth::id()
        ]);

        return redirect()->route('attreq.index')
            ->with('success', 'Attendance record created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $data = AttReq::findOrFail($id);
        $attendanceTypes = ['Manual', 'Official Visit', 'Travel', 'Training'];
        return view('att_reqs.edit', compact('data', 'attendanceTypes'));
    }

    /**
     * Update existing request
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'empcode' => 'required|string|max:20',
            'schddate' => 'required|date',
            'att' => 'required|string|max:50',
            'remarks' => 'nullable|string'
        ]);

        $attReq = AttReq::findOrFail($id);
        
        // Check for duplicate empcode + schddate (excluding current record)
        $exists = AttReq::where('empcode', $request->empcode)
                        ->where('schddate', $request->schddate)
                        ->where('id', '!=', $id)
                        ->exists();
        
        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['empcode' => 'Attendance record already exists for this employee on this date.']);
        }

        $attReq->update([
            'empcode' => $request->empcode,
            'schddate' => $request->schddate,
            'att' => $request->att,
            'remarks' => $request->remarks,
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('attreq.index')
            ->with('success', 'Attendance record updated successfully.');
    }

    // delee willnot used
}
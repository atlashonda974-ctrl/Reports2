<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\AttReq;
 use Illuminate\Support\Facades\Schema;
class EmployerReportController extends Controller
{
    const OFFICE_START = '08:35'; // 08:35 AM in 24-hour format
    const OFFICE_END = '17:15';   // 05:15 PM in 24-hour format

    /**
     *  Employer report 
     */
    public function index(Request $request)
    {
        // $user = AttReq::all();
        // dd($user);
        // filters 

       

// if (Schema::hasTable('att_reqs')) {
//      return "Table exists!";
// } else {
//      return "Table does NOT exist!";
// }

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $branches = $request->input('branch', []);
        $departments = $request->input('department', []);
        $employeeTypes = $request->input('employee_type', []);

        try {
            // Step 1: Fetch Employee Data
            $employeeApiUrl = 'http://172.16.22.204/dashboardApi/temp/getEmployees.php';
            $employeeResponse = Http::timeout(60)->get($employeeApiUrl);

            if (!$employeeResponse->successful()) {
                return back()->with('error', 'Employee API returned an error: ' . $employeeResponse->status());
            }

            $employeeData = collect($employeeResponse->json())->map(fn($emp) => (object)$emp);

            //  Attendance Data
            $attendanceData = collect();
            if ($fromDate && $toDate) {
                $attendanceApiUrl = 'http://172.16.22.204/ATT/getDateWise.php';
                try {
                    $attendanceResponse = Http::timeout(60)->get($attendanceApiUrl, [
                        'fromDate' => $fromDate,
                        'toDate' => $toDate,
                    ]);

                    if ($attendanceResponse->successful()) {
                        $attendanceData = collect($attendanceResponse->json()['data'] ?? [])
                            ->map(fn($att) => (object)$att);
                    } else {
                        \Log::warning('Attendance API failed: ' . $attendanceResponse->status());
                    }
                } catch (\Exception $e) {
                    \Log::error('Attendance API exception: ' . $e->getMessage());
                }
            }

            // Step 2: Fetch att_reqs overrides from database
            $attReqsData = collect();
            if ($fromDate && $toDate) {
                try {
                    $attReqsData = DB::table('att_reqs')
                        ->whereDate('schddate', '>=', $fromDate)
                        ->whereDate('schddate', '<=', $toDate)
                        ->get()
                        ->map(function($row) {
                            return (object)[
                                'empcode' => trim($row->empcode ?? ''),
                                'schddate' => $row->schddate ?? '',
                                'att' => trim($row->att ?? ''),
                            ];
                        });
                    
                    // DEBUG: Uncomment to check att_reqs data
                    // dd([
                    //     'att_reqs_count' => $attReqsData->count(),
                    //     'att_reqs_sample' => $attReqsData->take(5)->toArray(),
                    //     'fromDate' => $fromDate,
                    //     'toDate' => $toDate
                    // ]);
                    
                } catch (\Exception $e) {
                    // Query failed - continue without overrides
                    // dd('att_reqs query failed: ' . $e->getMessage());
                }
            }

            
            $normalizedEmployees = $employeeData->map(function ($emp) {
                return (object)[
                    'HPI_EMP_CODE' => trim($emp->HPI_EMP_CODE ?? $emp->EMP_CODE ?? 'N/A'),
                    'HPI_EMP_NAME' => trim($emp->HPI_EMP_NAME ?? $emp->EMP_NAME ?? 'N/A') ?: 'N/A',
                    'PDP_DESC'     => trim($emp->PDP_DESC ?? $emp->DEPARTMENT ?? 'N/A') ?: 'N/A',
                    'PLC_DESC'     => trim($emp->PLC_DESC ?? $emp->BRANCH ?? 'N/A') ?: 'N/A',
                    'PLC_LOC_CODE' => trim($emp->PLC_LOC_CODE ?? 'N/A') ?: 'N/A',
                    'PHG_DESC'     => trim($emp->PHG_DESC ?? $emp->EMP_TYPE ?? 'N/A') ?: 'N/A',
                ];
            });

            // Filters
            $filteredEmployees = $normalizedEmployees;

            if (!empty($branches)) {
                $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PLC_LOC_CODE, $branches));
            }

            if (!empty($departments)) {
                $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PDP_DESC, $departments));
            }

            if (!empty($employeeTypes)) {
                $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PHG_DESC, $employeeTypes));
            }

            // Merge Employee + Attendance + att_reqs overrides
            $mergedData = $this->mergeEmployeeAttendance($filteredEmployees, $attendanceData, $attReqsData, $fromDate, $toDate);

            // Filter by Date Range
            if ($fromDate && $toDate) {
                $from = Carbon::parse($fromDate);
                $to = Carbon::parse($toDate);

                $mergedData = $mergedData->filter(function ($row) use ($from, $to) {
                    if ($row->CHECKDATE === 'N/A' || empty($row->CHECKDATE)) {
                        return true;
                    }

                    try {
                        $checkDate = Carbon::parse($row->CHECKDATE);
                        return $checkDate->between($from, $to);
                    } catch (\Exception $e) {
                        return false;
                    }
                });
            }

            // Summary Statistics
            $summary = [
                'Present' => $mergedData->where('STATUS', 'Present')->count(),
                'Absent' => $mergedData->where('STATUS', 'Absent')->count(),
                'Leave' => $mergedData->where('STATUS', 'Leave')->count(),
            ];

            $allBranches = $normalizedEmployees->map(fn($b) => (object)[
                'PLC_LOC_CODE' => $b->PLC_LOC_CODE,
                'PLC_DESC' => $b->PLC_DESC
            ])->unique('PLC_LOC_CODE')->values();

            $allDepartments = $normalizedEmployees->pluck('PDP_DESC')
                ->filter()
                ->unique()
                ->map(fn($d) => (object)['PDP_DESC' => $d])
                ->values();

            $allEmployeeTypes = $normalizedEmployees->pluck('PHG_DESC')
                ->filter()
                ->unique()
                ->map(fn($e) => (object)['PHG_DESC' => $e])
                ->values();

           
            return view('Employerreport.employerreport', [
                'mergedData' => $mergedData,
                'branches' => $allBranches,
                'departments' => $allDepartments,
                'employeeTypes' => $allEmployeeTypes,
                'summary' => $summary,
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Exception occurred: ' . $e->getMessage());
        }
    }

    /**
     * Check if check-in time is late (after 08:35 AM)
     */
    private function isLate($checkInTime)
    {
        if ($checkInTime === 'N/A' || !$checkInTime) {
            return false;
        }

        try {
            $checkinCarbon = Carbon::parse($checkInTime);
            $checkin = $checkinCarbon->format('H:i');
            return $checkin > self::OFFICE_START;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if check-out time is early (before 05:15 PM)
     */
    private function isEarlyOut($checkOutTime)
    {
        if ($checkOutTime === 'N/A' || !$checkOutTime) {
            return false;
        }

        try {
            $checkoutCarbon = Carbon::parse($checkOutTime);
            $checkout = $checkoutCarbon->format('H:i');
            return $checkout < self::OFFICE_END;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if the given date is a weekend (Saturday or Sunday)
     */
    private function isWeekend($date)
    {
        if ($date === 'N/A' || !$date) {
            return false;
        }

        try {
            $carbonDate = Carbon::parse($date);
            return $carbonDate->dayOfWeek === Carbon::SATURDAY || $carbonDate->dayOfWeek === Carbon::SUNDAY;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get day name from date
     */
    private function getDayName($date)
    {
        if ($date === 'N/A' || !$date) {
            return 'N/A';
        }

        try {
            return Carbon::parse($date)->format('l'); 
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Merge employee and attendance data by EMPCODE with att_reqs overrides
     */
    private function mergeEmployeeAttendance(
        Collection $employees, 
        Collection $attendance, 
        Collection $attReqs,
        $fromDate,
        $toDate
    ): Collection {
       
        $attendance = $attendance->map(function ($att) {
            $code = (string)($att->EMPCODE ?? $att->EmpCode ?? $att->EMP_CODE ?? '');
            $code = trim($code);
            return (object)[
                'EMPCODE'   => $code,
                'CHECKDATE' => $att->CHECKDATE ?? $att->CheckDate ?? 'N/A',
                'STATUS'    => $att->STATUS ?? $att->Status ?? 'N/A',
                'CHECKIN'   => $att->CHECKIN ?? $att->CheckIn ?? 'N/A',
                'CHECKOUT'  => $att->CHECKOUT ?? $att->CheckOut ?? 'N/A',
            ];
        });

        $merged = collect();

        // DEBUG:  check employee and att_reqs data before merging
        // dd([
        //     'employees_count' => $employees->count(),
        //     'employees_sample' => $employees->take(2)->toArray(),
        //     'att_reqs_count' => $attReqs->count(),
        //     'att_reqs_sample' => $attReqs->take(5)->toArray(),
        //     'date_range' => compact('fromDate', 'toDate')
        // ]);

        foreach ($employees as $employee) {
            $empCode = trim($employee->HPI_EMP_CODE ?? '');

            // Match attendance records
            $empAttendance = $attendance->filter(function ($att) use ($empCode) {
                $minLen = min(strlen($empCode), strlen($att->EMPCODE));
                return substr($empCode, -$minLen) === substr($att->EMPCODE, -$minLen);
            });

            // Get all dates in the range
            if ($fromDate && $toDate) {
                $from = Carbon::parse($fromDate);
                $to = Carbon::parse($toDate);
                $dateRange = collect();
                
                for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                    $dateRange->push($date->format('Y-m-d'));
                }

                // Process each date
                foreach ($dateRange as $date) {
                    // Check if there's an att_reqs override for this employee and date
                    $attReqOverride = $attReqs->first(function ($req) use ($empCode, $date) {
                        // Try exact match first
                        if (trim($req->empcode) === $empCode) {
                            return $req->schddate === $date;
                        }
                        
                        // Fall back to suffix matching
                        $minLen = min(strlen($empCode), strlen($req->empcode));
                        if ($minLen > 0) {
                            $empMatch = substr($empCode, -$minLen) === substr(trim($req->empcode), -$minLen);
                            return $empMatch && $req->schddate === $date;
                        }
                        
                        return false;
                    });
                    
                    // Debug logging
                    if ($attReqOverride) {
                        \Log::info("Override found for empcode: {$empCode}, date: {$date}, att: {$attReqOverride->att}");
                    }

                    // Check if there's API attendance for this date
                    $dayAttendance = $empAttendance->where('CHECKDATE', $date);

                    // check override matching for first employee
                    // static $debugCount = 0;
                    // if ($debugCount < 10) {
                    //     dd([
                    //         'empCode' => $empCode,
                    //         'date' => $date,
                    //         'attReqOverride' => $attReqOverride,
                    //         'attReqs_all' => $attReqs->where('schddate', $date)->toArray(),
                    //         'dayAttendance_count' => $dayAttendance->count()
                    //     ]);
                    //     $debugCount++;
                    // }

                    if ($attReqOverride) {
                        // Override exists - use att value for both STATUS and FLAG
                        $checkin = $dayAttendance->isNotEmpty() 
                            ? ($dayAttendance->pluck('CHECKIN')->filter(fn($v) => $v && $v !== 'N/A')->sort()->first() ?? 'N/A')
                            : 'N/A';
                        $checkout = $dayAttendance->isNotEmpty() 
                            ? ($dayAttendance->pluck('CHECKOUT')->filter(fn($v) => $v && $v !== 'N/A')->sort()->last() ?? 'N/A')
                            : 'N/A';

                        $merged->push((object)[
                            'HPI_EMP_CODE' => $empCode,
                            'HPI_EMP_NAME' => $employee->HPI_EMP_NAME ?? 'N/A',
                            'PDP_DESC'     => $employee->PDP_DESC ?? 'N/A',
                            'PLC_DESC'     => $employee->PLC_DESC ?? 'N/A',
                            'PLC_LOC_CODE' => $employee->PLC_LOC_CODE ?? 'N/A',
                            'PHG_DESC'     => $employee->PHG_DESC ?? 'N/A',
                            'CHECKDATE'    => $date,
                            'DAY_NAME'     => $this->getDayName($date),
                            'STATUS'       => $attReqOverride->att,
                            'CHECKIN'      => $checkin,
                            'CHECKOUT'     => $checkout,
                            'FLAG'         => $attReqOverride->att,
                        ]);
                    } elseif ($dayAttendance->isNotEmpty()) {
                        // No override but API has attendance - use normal logic
                        $status = $dayAttendance->contains(fn($r) => $r->STATUS === 'Present') ? 'Present' : 'Absent';
                        $checkin = $dayAttendance->pluck('CHECKIN')->filter(fn($v) => $v && $v !== 'N/A')->sort()->first() ?? 'N/A';
                        $checkout = $dayAttendance->pluck('CHECKOUT')->filter(fn($v) => $v && $v !== 'N/A')->sort()->last() ?? 'N/A';

                        $dayName = $this->getDayName($date);
                        $flag = 'N/A';
                        
                        if ($this->isWeekend($date)) {
                            $flag = 'Off Day';
                        } elseif ($checkin !== 'N/A' || $checkout !== 'N/A') {
                            $isLate = $this->isLate($checkin);
                            $isEarlyOut = $this->isEarlyOut($checkout);

                            if ($isLate && $isEarlyOut) {
                                $flag = 'Late + EarlyOut';
                            } elseif ($isLate) {
                                $flag = 'Late';
                            } elseif ($isEarlyOut) {
                                $flag = 'EarlyOut';
                            } else {
                                $flag = 'Ok';
                            }
                        }

                        $merged->push((object)[
                            'HPI_EMP_CODE' => $empCode,
                            'HPI_EMP_NAME' => $employee->HPI_EMP_NAME ?? 'N/A',
                            'PDP_DESC'     => $employee->PDP_DESC ?? 'N/A',
                            'PLC_DESC'     => $employee->PLC_DESC ?? 'N/A',
                            'PLC_LOC_CODE' => $employee->PLC_LOC_CODE ?? 'N/A',
                            'PHG_DESC'     => $employee->PHG_DESC ?? 'N/A',
                            'CHECKDATE'    => $date,
                            'DAY_NAME'     => $dayName,
                            'STATUS'       => $status,
                            'CHECKIN'      => $checkin,
                            'CHECKOUT'     => $checkout,
                            'FLAG'         => $flag,
                        ]);
                    } else {
                        // No API attendance and no override - mark as Absent
                        $merged->push((object)[
                            'HPI_EMP_CODE' => $empCode,
                            'HPI_EMP_NAME' => $employee->HPI_EMP_NAME ?? 'N/A',
                            'PDP_DESC'     => $employee->PDP_DESC ?? 'N/A',
                            'PLC_DESC'     => $employee->PLC_DESC ?? 'N/A',
                            'PLC_LOC_CODE' => $employee->PLC_LOC_CODE ?? 'N/A',
                            'PHG_DESC'     => $employee->PHG_DESC ?? 'N/A',
                            'CHECKDATE'    => $date,
                            'DAY_NAME'     => $this->getDayName($date),
                            'STATUS'       => 'Absent',
                            'CHECKIN'      => 'N/A',
                            'CHECKOUT'     => 'N/A',
                            'FLAG'         => 'Absent',
                        ]);
                    }
                }
            } else {
                // No date range specified - use original logic for backwards compatibility
                if ($empAttendance->isEmpty()) {
                    $merged->push((object)[
                        'HPI_EMP_CODE' => $empCode,
                        'HPI_EMP_NAME' => $employee->HPI_EMP_NAME ?? 'N/A',
                        'PDP_DESC'     => $employee->PDP_DESC ?? 'N/A',
                        'PLC_DESC'     => $employee->PLC_DESC ?? 'N/A',
                        'PLC_LOC_CODE' => $employee->PLC_LOC_CODE ?? 'N/A',
                        'PHG_DESC'     => $employee->PHG_DESC ?? 'N/A',
                        'CHECKDATE'    => 'N/A',
                        'DAY_NAME'     => 'N/A',
                        'STATUS'       => 'N/A',
                        'CHECKIN'      => 'N/A',
                        'CHECKOUT'     => 'N/A',
                        'FLAG'         => 'N/A',
                    ]);
                } else {
                    $groupedByDate = $empAttendance->groupBy('CHECKDATE');

                    foreach ($groupedByDate as $date => $records) {
                        $status = $records->contains(fn($r) => $r->STATUS === 'Present') ? 'Present' : 'Absent';
                        $checkin = $records->pluck('CHECKIN')->filter(fn($v) => $v && $v !== 'N/A')->sort()->first() ?? 'N/A';
                        $checkout = $records->pluck('CHECKOUT')->filter(fn($v) => $v && $v !== 'N/A')->sort()->last() ?? 'N/A';

                        $dayName = $this->getDayName($date);
                        $flag = 'N/A';
                        
                        if ($this->isWeekend($date)) {
                            $flag = 'Off Day';
                        } elseif ($checkin !== 'N/A' || $checkout !== 'N/A') {
                            $isLate = $this->isLate($checkin);
                            $isEarlyOut = $this->isEarlyOut($checkout);

                            if ($isLate && $isEarlyOut) {
                                $flag = 'Late + EarlyOut';
                            } elseif ($isLate) {
                                $flag = 'Late';
                            } elseif ($isEarlyOut) {
                                $flag = 'EarlyOut';
                            } else {
                                $flag = 'Ok';
                            }
                        }

                        $merged->push((object)[
                            'HPI_EMP_CODE' => $empCode,
                            'HPI_EMP_NAME' => $employee->HPI_EMP_NAME ?? 'N/A',
                            'PDP_DESC'     => $employee->PDP_DESC ?? 'N/A',
                            'PLC_DESC'     => $employee->PLC_DESC ?? 'N/A',
                            'PLC_LOC_CODE' => $employee->PLC_LOC_CODE ?? 'N/A',
                            'PHG_DESC'     => $employee->PHG_DESC ?? 'N/A',
                            'CHECKDATE'    => $date,
                            'DAY_NAME'     => $dayName,
                            'STATUS'       => $status,
                            'CHECKIN'      => $checkin,
                            'CHECKOUT'     => $checkout,
                            'FLAG'         => $flag,
                        ]);
                    }
                }
            }
        }

        return $merged;
    }



    /**
 * ****************************************************************************************** Absent and Late Report 
 */
// public function absentLateReport(Request $request)
// {
//     $fromDate = $request->input('from_date');
//     $toDate = $request->input('to_date');
//     $branches = $request->input('branch', []);
//     $departments = $request->input('department', []);
//     $employeeTypes = $request->input('employee_type', []);

//     try {
     
//         $employeeApiUrl = 'http://172.16.22.204/dashboardApi/temp/getEmployees.php';
//         $employeeResponse = Http::timeout(60)->get($employeeApiUrl);

//         if (!$employeeResponse->successful()) {
//             return back()->with('error', 'Employee API returned an error: ' . $employeeResponse->status());
//         }

//         $employeeData = collect($employeeResponse->json())->map(fn($emp) => (object)$emp);

      
//         $attendanceData = collect();
//         if ($fromDate && $toDate) {
//             $attendanceApiUrl = 'http://172.16.22.204/ATT/getDateWise.php';
//             try {
//                 $attendanceResponse = Http::timeout(60)->get($attendanceApiUrl, [
//                     'fromDate' => $fromDate,
//                     'toDate' => $toDate,
//                 ]);

//                 if ($attendanceResponse->successful()) {
//                     $attendanceData = collect($attendanceResponse->json()['data'] ?? [])
//                         ->map(fn($att) => (object)$att);
//                 } else {
//                     \Log::warning('Attendance API failed: ' . $attendanceResponse->status());
//                 }
//             } catch (\Exception $e) {
//                 \Log::error('Attendance API exception: ' . $e->getMessage());
//             }
//         }

       
//         $normalizedEmployees = $employeeData->map(function ($emp) {
//             return (object)[
//                 'HPI_EMP_CODE' => trim($emp->HPI_EMP_CODE ?? $emp->EMP_CODE ?? 'N/A'),
//                 'HPI_EMP_NAME' => trim($emp->HPI_EMP_NAME ?? $emp->EMP_NAME ?? 'N/A') ?: 'N/A',
//                 'PDP_DESC'     => trim($emp->PDP_DESC ?? $emp->DEPARTMENT ?? 'N/A') ?: 'N/A',
//                 'PLC_DESC'     => trim($emp->PLC_DESC ?? $emp->BRANCH ?? 'N/A') ?: 'N/A',
//                 'PLC_LOC_CODE' => trim($emp->PLC_LOC_CODE ?? 'N/A') ?: 'N/A',
//                 'PHG_DESC'     => trim($emp->PHG_DESC ?? $emp->EMP_TYPE ?? 'N/A') ?: 'N/A',
//             ];
//         });

       
//         $filteredEmployees = $normalizedEmployees;
//         if (!empty($branches)) {
//             $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PLC_LOC_CODE, $branches));
//         }
//         if (!empty($departments)) {
//             $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PDP_DESC, $departments));
//         }
//         if (!empty($employeeTypes)) {
//             $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PHG_DESC, $employeeTypes));
//         }

//         $mergedData = $this->mergeEmployeeAttendance($filteredEmployees, $attendanceData);

      
//         $mergedData = $mergedData->map(function ($row) {
//             if (in_array($row->DAY_NAME, ['Saturday', 'Sunday'])) {
//                 $row->STATUS = 'Off Day';
//                 $row->FLAG = 'Off Day'; 
//             }
//             return $row;
//         });

    
//         if ($fromDate && $toDate) {
//             $from = Carbon::parse($fromDate);
//             $to = Carbon::parse($toDate);

//             $mergedData = $mergedData->filter(function ($row) use ($from, $to) {
//                 if ($row->CHECKDATE === 'N/A' || empty($row->CHECKDATE)) {
//                     return true;
//                 }
//                 try {
//                     $checkDate = Carbon::parse($row->CHECKDATE);
//                     return $checkDate->between($from, $to);
//                 } catch (\Exception $e) {
//                     return false;
//                 }
//             });
//         }

    
//         $mergedData = $mergedData->filter(function ($row) {
//             $weekday = $row->DAY_NAME;
//             $flag = $row->FLAG;
//             $status = $row->STATUS;

//             if ($status === 'Absent') return true;         
//             if (in_array($flag, ['Late', 'Late + EarlyOut'])) return true;
//             if ($flag === 'EarlyOut' && !in_array($weekday, ['Saturday', 'Sunday'])) return true;

//             return false;
//         });

      
//         $summary = [
//             'Absent' => $mergedData->where('STATUS', 'Absent')->count(),
//             'Late' => $mergedData->where('FLAG', 'Late')->count(),
//             'LateEarlyOut' => $mergedData->where('FLAG', 'Late + EarlyOut')->count(),
//             'EarlyOut' => $mergedData->where('FLAG', 'EarlyOut')->count(),
//         ];

       
//         $allBranches = $normalizedEmployees->map(fn($b) => (object)[
//             'PLC_LOC_CODE' => $b->PLC_LOC_CODE,
//             'PLC_DESC' => $b->PLC_DESC
//         ])->unique('PLC_LOC_CODE')->values();

//         $allDepartments = $normalizedEmployees->pluck('PDP_DESC')
//             ->filter()
//             ->unique()
//             ->map(fn($d) => (object)['PDP_DESC' => $d])
//             ->values();

//         $allEmployeeTypes = $normalizedEmployees->pluck('PHG_DESC')
//             ->filter()
//             ->unique()
//             ->map(fn($e) => (object)['PHG_DESC' => $e])
//             ->values();

     
//         return view('Employerreport.absent-late-report', [
//             'mergedData' => $mergedData,
//             'branches' => $allBranches,
//             'departments' => $allDepartments,
//             'employeeTypes' => $allEmployeeTypes,
//             'summary' => $summary,
//         ]);

//     } catch (\Exception $e) {
//         return back()->with('error', 'Exception occurred: ' . $e->getMessage());
//     }
// }

/**
 * ****************************************************************************************** Absent and Late Report 
 */
public function absentLateReport(Request $request)
{
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $branches = $request->input('branch', []);
    $departments = $request->input('department', []);
    $employeeTypes = $request->input('employee_type', []);

    try {
        // Fetch employee data
        $employeeApiUrl = 'http://172.16.22.204/dashboardApi/temp/getEmployees.php';
        $employeeResponse = Http::timeout(60)->get($employeeApiUrl);

        if (!$employeeResponse->successful()) {
            return back()->with('error', 'Employee API returned an error: ' . $employeeResponse->status());
        }

        $employeeData = collect($employeeResponse->json())->map(fn($emp) => (object)$emp);

        // Fetch attendance data
        $attendanceData = collect();
        if ($fromDate && $toDate) {
            $attendanceApiUrl = 'http://172.16.22.204/ATT/getDateWise.php';
            try {
                $attendanceResponse = Http::timeout(60)->get($attendanceApiUrl, [
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                ]);

                if ($attendanceResponse->successful()) {
                    $attendanceData = collect($attendanceResponse->json()['data'] ?? [])
                        ->map(fn($att) => (object)$att);
                } else {
                    \Log::warning('Attendance API failed: ' . $attendanceResponse->status());
                }
            } catch (\Exception $e) {
                \Log::error('Attendance API exception: ' . $e->getMessage());
            }
        }

        // Fetch attendance requests data (attReqs) - Add this section
        $attReqsData = collect();
        if ($fromDate && $toDate) {
            try {
                $attReqsData = DB::table('att_reqs')
                    ->whereDate('schddate', '>=', $fromDate)
                    ->whereDate('schddate', '<=', $toDate)
                    ->get()
                    ->map(function($row) {
                        return (object)[
                            'empcode' => trim($row->empcode ?? ''),
                            'schddate' => $row->schddate ?? '',
                            'att' => trim($row->att ?? ''),
                        ];
                    });
            } catch (\Exception $e) {
                // Query failed - continue without overrides
                \Log::error('att_reqs query failed in absentLateReport: ' . $e->getMessage());
            }
        }

        // Normalize employee data
        $normalizedEmployees = $employeeData->map(function ($emp) {
            return (object)[
                'HPI_EMP_CODE' => trim($emp->HPI_EMP_CODE ?? $emp->EMP_CODE ?? 'N/A'),
                'HPI_EMP_NAME' => trim($emp->HPI_EMP_NAME ?? $emp->EMP_NAME ?? 'N/A') ?: 'N/A',
                'PDP_DESC'     => trim($emp->PDP_DESC ?? $emp->DEPARTMENT ?? 'N/A') ?: 'N/A',
                'PLC_DESC'     => trim($emp->PLC_DESC ?? $emp->BRANCH ?? 'N/A') ?: 'N/A',
                'PLC_LOC_CODE' => trim($emp->PLC_LOC_CODE ?? 'N/A') ?: 'N/A',
                'PHG_DESC'     => trim($emp->PHG_DESC ?? $emp->EMP_TYPE ?? 'N/A') ?: 'N/A',
            ];
        });

        // Apply filters
        $filteredEmployees = $normalizedEmployees;
        if (!empty($branches)) {
            $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PLC_LOC_CODE, $branches));
        }
        if (!empty($departments)) {
            $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PDP_DESC, $departments));
        }
        if (!empty($employeeTypes)) {
            $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PHG_DESC, $employeeTypes));
        }

        // Merge employee and attendance data with all required arguments - Fixed this line
        $mergedData = $this->mergeEmployeeAttendance(
            $filteredEmployees, 
            $attendanceData,
            $attReqsData,    // Added this argument
            $fromDate,       // Added this argument  
            $toDate          // Added this argument
        );

        // Mark weekends as Off Day
        $mergedData = $mergedData->map(function ($row) {
            if (in_array($row->DAY_NAME, ['Saturday', 'Sunday'])) {
                $row->STATUS = 'Off Day';
                $row->FLAG = 'Off Day'; 
            }
            return $row;
        });

        // Filter by date range
        if ($fromDate && $toDate) {
            $from = Carbon::parse($fromDate);
            $to = Carbon::parse($toDate);

            $mergedData = $mergedData->filter(function ($row) use ($from, $to) {
                if ($row->CHECKDATE === 'N/A' || empty($row->CHECKDATE)) {
                    return true;
                }
                try {
                    $checkDate = Carbon::parse($row->CHECKDATE);
                    return $checkDate->between($from, $to);
                } catch (\Exception $e) {
                    return false;
                }
            });
        }

        // Filter for Absent/Late/EarlyOut
        $mergedData = $mergedData->filter(function ($row) {
            $weekday = $row->DAY_NAME;
            $flag = $row->FLAG;
            $status = $row->STATUS;

            if ($status === 'Absent') return true;         
            if (in_array($flag, ['Late', 'Late + EarlyOut'])) return true;
            if ($flag === 'EarlyOut' && !in_array($weekday, ['Saturday', 'Sunday'])) return true;

            return false;
        });

        // Calculate summary
        $summary = [
            'Absent' => $mergedData->where('STATUS', 'Absent')->count(),
            'Late' => $mergedData->where('FLAG', 'Late')->count(),
            'LateEarlyOut' => $mergedData->where('FLAG', 'Late + EarlyOut')->count(),
            'EarlyOut' => $mergedData->where('FLAG', 'EarlyOut')->count(),
        ];

        // Get all branches, departments, employee types for filters
        $allBranches = $normalizedEmployees->map(fn($b) => (object)[
            'PLC_LOC_CODE' => $b->PLC_LOC_CODE,
            'PLC_DESC' => $b->PLC_DESC
        ])->unique('PLC_LOC_CODE')->values();

        $allDepartments = $normalizedEmployees->pluck('PDP_DESC')
            ->filter()
            ->unique()
            ->map(fn($d) => (object)['PDP_DESC' => $d])
            ->values();

        $allEmployeeTypes = $normalizedEmployees->pluck('PHG_DESC')
            ->filter()
            ->unique()
            ->map(fn($e) => (object)['PHG_DESC' => $e])
            ->values();

        return view('Employerreport.absent-late-report', [
            'mergedData' => $mergedData,
            'branches' => $allBranches,
            'departments' => $allDepartments,
            'employeeTypes' => $allEmployeeTypes,
            'summary' => $summary,
        ]);

    } catch (\Exception $e) {
        return back()->with('error', 'Exception occurred: ' . $e->getMessage());
    }
}

/*
Summary Report
*/ 
public function empSummaryReport(Request $request)
{
    $fromDate = $request->input('from_date', '2025-07-01');
$toDate = $request->input('to_date', now()->toDateString());
    $branches = $request->input('branch', []);
    $departments = $request->input('department', []);
    $employeeTypes = $request->input('employee_type', []);
    $empcode = $request->input('empcode');

    try {
        //  Fetch Employee Data for Dropdowns
        $employeeApiUrl = 'http://172.16.22.204/dashboardApi/temp/getEmployees.php';
        $employeeResponse = Http::timeout(60)->get($employeeApiUrl);

        if (!$employeeResponse->successful()) {
            return back()->with('error', 'Employee API returned an error: ' . $employeeResponse->status());
        }

        $employeeData = collect($employeeResponse->json())->map(fn($emp) => (object)$emp);

        // Normalize Employee Data for Dropdowns
        $normalizedEmployees = $employeeData->map(function ($emp) {
            return (object)[
                'HPI_EMP_CODE' => trim($emp->HPI_EMP_CODE ?? $emp->EMP_CODE ?? 'N/A'),
                'HPI_EMP_NAME' => trim($emp->HPI_EMP_NAME ?? $emp->EMP_NAME ?? 'N/A') ?: 'N/A',
                'PDP_DESC'     => trim($emp->PDP_DESC ?? $emp->DEPARTMENT ?? 'N/A') ?: 'N/A',
                'PLC_DESC'     => trim($emp->PLC_DESC ?? $emp->BRANCH ?? 'N/A') ?: 'N/A',
                'PLC_LOC_CODE' => trim($emp->PLC_LOC_CODE ?? 'N/A') ?: 'N/A',
                'PHG_DESC'     => trim($emp->PHG_DESC ?? $emp->EMP_TYPE ?? 'N/A') ?: 'N/A',
            ];
        });

    
        $allBranches = $normalizedEmployees->map(fn($b) => (object)[
            'PLC_LOC_CODE' => $b->PLC_LOC_CODE,
            'PLC_DESC' => $b->PLC_DESC
        ])->unique('PLC_LOC_CODE')->values();

        $allDepartments = $normalizedEmployees->pluck('PDP_DESC')
            ->filter()
            ->unique()
            ->map(fn($d) => (object)['PDP_DESC' => $d])
            ->values();

        $allEmployeeTypes = $normalizedEmployees->pluck('PHG_DESC')
            ->filter()
            ->unique()
            ->map(fn($e) => (object)['PHG_DESC' => $e])
            ->values();

       
        $isFiltered = $request->has('from_date') || $request->has('to_date') || 
                      !empty($branches) || !empty($departments) || 
                      !empty($employeeTypes) || !empty($empcode);

 
        if (!$isFiltered || !$fromDate || !$toDate) {
            return view('Employerreport.emp-summary-report', [
                'summaryData'   => collect(),
                'branches'      => $allBranches,
                'departments'   => $allDepartments,
                'employeeTypes' => $allEmployeeTypes,
                'summary'       => [
                    'Present'  => 0,
                    'Absent'   => 0,
                    'Late'     => 0,
                    'EarlyOut' => 0,
                ],
                'normalizedEmployees'=> $normalizedEmployees,
            ]);
        }

        //  Summary Attendance Data from API
        $summaryApiData = collect();
        if ($fromDate && $toDate) {
           
             $summaryApiUrl = 'http://172.16.22.204/ATT/getSummary.php';
            try {
                $summaryResponse = Http::timeout(60)->get($summaryApiUrl, [
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                ]);

                if ($summaryResponse->successful()) {
                    $responseData = $summaryResponse->json();
                    
                   
                    if (isset($responseData['summary']) && is_array($responseData['summary'])) {
                        $summaryApiData = collect($responseData['summary'])->map(function($att) {
                            return (object)[
                                'EMPCODE'  => trim($att['EMPCODE'] ?? ''),
                                'PRESENT'  => intval($att['Present Count'] ?? 0),
                                'ABSENT'   => intval($att['Absent Count'] ?? 0),
                                'LATE'     => intval($att['Late Count'] ?? 0),
                                'EARLYOUT' => intval($att['Early Out Count'] ?? 0),
                            ];
                        });
                    }
                } else {
                    \Log::warning('Summary API failed: ' . $summaryResponse->status());
                }
            } catch (\Exception $e) {
                \Log::error('Summary API exception: ' . $e->getMessage());
            }
        }

        //  Normalize Employee Data
        $normalizedEmployees = $employeeData->map(function ($emp) {
            return (object)[
                'HPI_EMP_CODE' => trim($emp->HPI_EMP_CODE ?? $emp->EMP_CODE ?? 'N/A'),
                'HPI_EMP_NAME' => trim($emp->HPI_EMP_NAME ?? $emp->EMP_NAME ?? 'N/A') ?: 'N/A',
                'PDP_DESC'     => trim($emp->PDP_DESC ?? $emp->DEPARTMENT ?? 'N/A') ?: 'N/A',
                'PLC_DESC'     => trim($emp->PLC_DESC ?? $emp->BRANCH ?? 'N/A') ?: 'N/A',
                'PLC_LOC_CODE' => trim($emp->PLC_LOC_CODE ?? 'N/A') ?: 'N/A',
                'PHG_DESC'     => trim($emp->PHG_DESC ?? $emp->EMP_TYPE ?? 'N/A') ?: 'N/A',
            ];
        });

        //  Filters
        $filteredEmployees = $normalizedEmployees;

        if (!empty($branches)) {
            $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PLC_LOC_CODE, $branches));
        }
        if (!empty($departments)) {
            $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PDP_DESC, $departments));
        }
        if (!empty($employeeTypes)) {
            $filteredEmployees = $filteredEmployees->filter(fn($emp) => in_array($emp->PHG_DESC, $employeeTypes));
        }
        if (!empty($empcode)) {
            $filteredEmployees = $filteredEmployees->filter(fn($emp) => 
                stripos($emp->HPI_EMP_CODE, trim($empcode)) !== false
            );
        }

        //Merge Employee + Summary Data
        $summaryData = collect();

        foreach ($filteredEmployees as $employee) {
            $empCode = trim($employee->HPI_EMP_CODE ?? '');
            
            // Find matching summary data by EMPCODE
            $empSummary = $summaryApiData->first(function ($sum) use ($empCode) {
                //  compare last N digits
                $sumCode = trim($sum->EMPCODE ?? '');
                if (empty($empCode) || empty($sumCode)) return false;
                
                $minLen = min(strlen($empCode), strlen($sumCode));
                return substr($empCode, -$minLen) === substr($sumCode, -$minLen);
            });

            $summaryData->push((object)[
                'EMP_CODE'       => $empCode,
                'EMP_NAME'       => $employee->HPI_EMP_NAME ?? 'N/A',
                'DEPARTMENT'     => $employee->PDP_DESC ?? 'N/A',
                'BRANCH'         => $employee->PLC_DESC ?? 'N/A',
                'MANAGEMENT_TYPE'=> $employee->PHG_DESC ?? 'N/A',
                'TOTAL_PRESENT'  => $empSummary->PRESENT ?? 0,
                'TOTAL_ABSENT'   => $empSummary->ABSENT ?? 0,
                'LATE_COUNT'     => $empSummary->LATE ?? 0,
                'EARLYOUT_COUNT' => $empSummary->EARLYOUT ?? 0,
            ]);
        }

        // Total Working Days (excluding Saturday & Sunday)
                $start = \Carbon\Carbon::parse($fromDate);
                $end = \Carbon\Carbon::parse($toDate);

                $totalWorkingDays = 0;
                while ($start->lte($end)) {
                    if (!in_array($start->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY])) {
                        $totalWorkingDays++;
                    }
                    $start->addDay();
                }

// Attach total working days to each employee record
$summaryData = $summaryData->map(function ($row) use ($totalWorkingDays) {
    $row->TOTAL_WORKING_DAYS = $totalWorkingDays;
    return $row;
});

        //  Calculate Totals for Stats Display
        $summary = [
            'Present'   => $summaryData->sum('TOTAL_PRESENT'),
            'Absent'    => $summaryData->sum('TOTAL_ABSENT'),
            'Late'      => $summaryData->sum('LATE_COUNT'),
            'EarlyOut'  => $summaryData->sum('EARLYOUT_COUNT'),
        ];

        //  Dropdown Data
        $allBranches = $normalizedEmployees->map(fn($b) => (object)[
            'PLC_LOC_CODE' => $b->PLC_LOC_CODE,
            'PLC_DESC' => $b->PLC_DESC
        ])->unique('PLC_LOC_CODE')->values();

        $allDepartments = $normalizedEmployees->pluck('PDP_DESC')
            ->filter()
            ->unique()
            ->map(fn($d) => (object)['PDP_DESC' => $d])
            ->values();

        $allEmployeeTypes = $normalizedEmployees->pluck('PHG_DESC')
            ->filter()
            ->unique()
            ->map(fn($e) => (object)['PHG_DESC' => $e])
            ->values();

       
        return view('Employerreport.emp-summary-report', [
            'summaryData'   => $summaryData,
            'branches'      => $allBranches,
            'departments'   => $allDepartments,
            'employeeTypes' => $allEmployeeTypes,
            'summary'       => $summary,
            'normalizedEmployees'=> $normalizedEmployees,
        ]);

    } catch (\Exception $e) {
        \Log::error('Employee Summary Exception: ' . $e->getMessage());
        return back()->with('error', 'Exception occurred: ' . $e->getMessage());
    }
}












/**
     * Test API endpoint - for debugging
     */
    public function testApi()
    {
        $apiUrl = 'http://172.16.22.204/ATT/getDateWise.php';
        $fromDate = '2025-07-01';
        $toDate = '2025-07-30';

        $response = Http::get($apiUrl, [
            'fromDate' => $fromDate,
            'toDate' => $toDate
        ]);

        $data = $response->json();
        return response()->json($data);
    }

    // public function testAllApis() 
    // {
    //     $employeeApiUrl = 'http://172.16.22.204/dashboardApi/temp/getEmployees.php';
    //     $attendanceApiUrl = 'http://172.16.22.204/ATT/getDateWise.php';

    //     $fromDate = '2025-07-01';
    //     $toDate = '2025-07-10';

    //     try {
    //         // Step 1: Fetch Employee API
    //         $employeeResponse = Http::timeout(60)->get($employeeApiUrl);
    //         if (!$employeeResponse->successful()) {
    //             return response()->json([
    //                 'error' => 'Employee API failed',
    //                 'status' => $employeeResponse->status()
    //             ], 500);
    //         }
    //         $employeeData = collect($employeeResponse->json());

    //         // Step 2: Fetch Attendance API
    //         $attendanceResponse = Http::timeout(60)->get($attendanceApiUrl, [
    //             'fromDate' => $fromDate,
    //             'toDate' => $toDate,
    //         ]);
    //         if (!$attendanceResponse->successful()) {
    //             return response()->json([
    //                 'error' => 'Attendance API failed',
    //                 'status' => $attendanceResponse->status()
    //             ], 500);
    //         }
    //         $attendanceData = collect($attendanceResponse->json()['data'] ?? []);

    //         // Step 3: Normalize codes
    //         $employeeData = $employeeData->map(fn($emp) => [
    //             'EMP_CODE' => ltrim($emp['HPI_EMP_CODE'], '0'),
    //             'EMP_NAME' => $emp['EMP_NAME'] ?? null,
    //             'DEPARTMENT' => $emp['DEPARTMENT'] ?? null
    //         ]);

    //         $attendanceData = $attendanceData->map(fn($att) => [
    //             'EMP_CODE' => ltrim($att['EMPCODE'], '0'),
    //             'CHECKIN' => $att['CHECKIN'] ?? null,
    //             'CHECKOUT' => $att['CHECKOUT'] ?? null,
    //             'STATUS' => $att['STATUS'] ?? 'Absent',
    //             'DATE' => $att['DATE'] ?? null
    //         ]);

    //         // Step 4: Find matched codes
    //         $employeeCodes = $employeeData->pluck('EMP_CODE');
    //         $attendanceCodes = $attendanceData->pluck('EMP_CODE');
    //         $matchedCodes = $employeeCodes->intersect($attendanceCodes)->values();

    //         // Step 5: Build matched_details array
    //         $matchedDetails = $matchedCodes->map(function($code) use ($employeeData, $attendanceData) {
    //             $empInfo = $employeeData->firstWhere('EMP_CODE', $code);
    //             $attRecords = $attendanceData->where('EMP_CODE', $code)->values();

    //             $summary = [
    //                 'Present' => $attRecords->where('STATUS', 'Present')->count(),
    //                 'Absent' => $attRecords->where('STATUS', 'Absent')->count(),
    //                 'Leave' => $attRecords->where('STATUS', 'Leave')->count(),
    //             ];

    //             return [
    //                 'EMP_CODE' => $code,
    //                 'EMP_NAME' => $empInfo['EMP_NAME'] ?? null,
    //                 'DEPARTMENT' => $empInfo['DEPARTMENT'] ?? null,
    //                 'ATTENDANCE' => $attRecords,
    //                 'SUMMARY' => $summary
    //             ];
    //         });

    //         // Step 6: Return structured JSON
    //         return response()->json([
    //             'status' => 'success',
    //             'fromDate' => $fromDate,
    //             'toDate' => $toDate,
    //             'matched_details' => $matchedDetails,
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

// public function testSummaryApi()
// {
//     try {
//         // API URL on the same server
//         $summaryApiUrl = 'http://127.0.0.1/ATT/getSummary.php';

//         // Set the from and to dates
//         $params = [
//             'fromDate' => '2025-01-01',
//             'toDate'   => '2025-07-30',
//         ];

//         // Make GET request with parameters
//         $response = Http::timeout(80)->get($summaryApiUrl, $params);

//         // Check if request was successful
//         $status = $response->successful() ? 'Success' : 'Failed: ' . $response->status();
//         $data = $response->successful() ? $response->json() : [];

//         // Return formatted JSON
//         return response()->json([
//             'summary_api' => [
//                 'status' => $status,
//                 'data_count' => isset($data['summary']) ? count($data['summary']) : 0,
//                 'data' => $data['summary'] ?? [],
//             ],
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'error' => 'Exception occurred',
//             'message' => $e->getMessage(),
//         ]);
//     }
// }







}


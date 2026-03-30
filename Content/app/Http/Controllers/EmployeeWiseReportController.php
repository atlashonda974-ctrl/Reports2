<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class EmployeeWiseReportController extends Controller
{
    const OFFICE_START = '08:35';
    const OFFICE_END = '17:15';

    public function index(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');
        $empCode  = $request->input('empcode');

        try {
           
            $employeeApiUrl = 'http://172.16.22.204/dashboardApi/temp/getEmployees.php';
            $employeeResponse = Http::timeout(60)->get($employeeApiUrl);
            $employeeData = collect($employeeResponse->successful() ? $employeeResponse->json() : []);

            // Normalize employees
            $employees = $employeeData->map(function ($emp) {
                return (object)[
                    'HPI_EMP_CODE' => trim($emp['HPI_EMP_CODE'] ?? $emp['EMP_CODE'] ?? 'N/A'),
                    'HPI_EMP_NAME' => trim($emp['HPI_EMP_NAME'] ?? $emp['EMP_NAME'] ?? 'N/A'),
                    'PDP_DESC'     => trim($emp['PDP_DESC'] ?? $emp['DEPARTMENT'] ?? 'N/A'),
                    'PLC_DESC'     => trim($emp['PLC_DESC'] ?? $emp['BRANCH'] ?? 'N/A'),
                    'PHG_DESC'     => trim($emp['PHG_DESC'] ?? $emp['EMP_TYPE'] ?? 'N/A'),
                ];
            });

            // Employee dropdown
            $employeesList = $employees->map(fn($emp) => ['code' => $emp->HPI_EMP_CODE, 'name' => $emp->HPI_EMP_NAME]);

            if (!$empCode || !$fromDate || !$toDate) {
                return view('Employerreport.EmployeeWiseReport', [
                    'mergedData' => collect(),
                    'summary' => ['Present'=>0,'Absent'=>0,'Leave'=>0,'Late'=>0,'EarlyOut'=>0],
                    'empCode' => $empCode,
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                    'employees' => $employeesList,
                ]);
            }

            // Filter employee by code
            $filteredEmployees = $employees->filter(fn($emp) => $emp->HPI_EMP_CODE == $empCode);

           
            $attendanceApiUrl = 'http://172.16.22.204/ATT/getEmpWise.php';
            $attendanceResponse = Http::timeout(60)->get($attendanceApiUrl, [
                'empcode' => $empCode,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
            ]);

            $attendanceData = collect($attendanceResponse->successful() ? ($attendanceResponse->json()['data'] ?? []) : []);

           
            $mergedData = $this->mergeEmployeeAttendance($filteredEmployees, $attendanceData, $fromDate, $toDate);

            //Summary stats (ignore Off Day for Late/EarlyOut)
            $empData = $mergedData->where('HPI_EMP_CODE', $empCode);

            $summary = [
                'Present' => $empData->where('STATUS', 'Present')->count(),
                'Absent'  => $empData->where('STATUS', 'Absent')->count(),
                'Leave'   => $empData->where('STATUS', 'Leave')->count(),
                'Late'    => $empData->where('STATUS', 'Present')
                                       ->whereIn('FLAG', ['Late','Late + EarlyOut'])
                                       ->count(),
                'EarlyOut'=> $empData->where('STATUS', 'Present')
                                       ->whereIn('FLAG', ['EarlyOut','Late + EarlyOut'])
                                       ->count(),
            ];

            return view('Employerreport.EmployeeWiseReport', [
                'mergedData' => $mergedData,
                'summary' => $summary,
                'empCode' => $empCode,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'employees' => $employeesList,
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Merge employee and attendance data
     */
    private function mergeEmployeeAttendance(Collection $employees, Collection $attendance, $fromDate, $toDate): Collection
    {
        // Map attendance by date
        $attendanceByDate = $attendance->mapWithKeys(function ($att) {
            $date = $att['CHECKDATE'] ?? 'N/A';
            return [$date => (object)[
                'CHECKIN'  => $att['CHECKIN'] ?? 'N/A',
                'CHECKOUT' => $att['CHECKOUT'] ?? 'N/A',
            ]];
        });

        $merged = collect();
        $period = CarbonPeriod::create($fromDate, $toDate);

        foreach ($employees as $emp) {
            foreach ($period as $date) {
                $checkDate = $date->format('Y-m-d');
                $dayName = $date->format('l');
                $isWeekend = in_array($dayName, ['Saturday', 'Sunday']);

                if ($isWeekend) {
                    // Weekend → Off Day
                    $status = 'Off Day';
                    $flag = 'Off Day';
                    $checkin = 'N/A';
                    $checkout = 'N/A';
                } else if ($attendanceByDate->has($checkDate)) {
                    // Weekday with attendance record
                    $att = $attendanceByDate[$checkDate];

                    if ($att->CHECKIN === 'N/A' && $att->CHECKOUT === 'N/A') {
                        $status = 'Absent';
                        $flag = 'N/A';
                        $checkin = 'N/A';
                        $checkout = 'N/A';
                    } else {
                        $status = 'Present';
                        $flag = $this->getFlag($checkDate, $att->CHECKIN, $att->CHECKOUT);
                        $checkin = $att->CHECKIN;
                        $checkout = $att->CHECKOUT;
                    }
                } else {
                                                    // Weekday with no record in API → Absent
                                                    $status = 'Absent';
                                                    $flag = 'Absent';
                                                    $checkin = 'N/A';
                                                    $checkout = 'N/A';
                                                }


                $merged->push((object)[
                    'HPI_EMP_CODE' => $emp->HPI_EMP_CODE,
                    'HPI_EMP_NAME' => $emp->HPI_EMP_NAME,
                    'PDP_DESC'     => $emp->PDP_DESC,
                    'PLC_DESC'     => $emp->PLC_DESC,
                    'PHG_DESC'     => $emp->PHG_DESC,
                    'CHECKDATE'    => $checkDate,
                    'DAY_NAME'     => $dayName,
                    'CHECKIN'      => $checkin,
                    'CHECKOUT'     => $checkout,
                    'STATUS'       => $status,
                    'FLAG'         => $flag,
                ]);
            }
        }

        return $merged->sortBy('CHECKDATE')->values();
    }

    /**
     * Attendance flags (Late, EarlyOut, etc.)
     */
    private function getFlag($date, $checkIn, $checkOut)
    {
        $dayName = Carbon::parse($date)->format('l');
        if (in_array($dayName, ['Saturday', 'Sunday'])) return 'Off Day';
        if ($checkIn === 'N/A' || $checkOut === 'N/A') return 'N/A';

        $isLate = Carbon::parse($checkIn)->format('H:i') > self::OFFICE_START;
        $isEarlyOut = Carbon::parse($checkOut)->format('H:i') < self::OFFICE_END;

        if ($isLate && $isEarlyOut) return 'Late + EarlyOut';
        if ($isLate) return 'Late';
        if ($isEarlyOut) return 'EarlyOut';
        return 'Ok';
    }
    public function testApis(Request $request)
{
    $empCode  = $request->input('empcode', '0001349'); 
    $fromDate = $request->input('from_date', now()->subDays(30)->format('Y-m-d'));
    $toDate   = $request->input('to_date', now()->format('Y-m-d'));

    try {
        
        $attendanceApiUrl = 'http://172.16.22.204/ATT/getEmpWise.php';
        $attendanceResponse = Http::timeout(60)->get($attendanceApiUrl, [
            'empcode' => $empCode,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);

        $attendanceData = collect($attendanceResponse->successful() ? ($attendanceResponse->json()['data'] ?? []) : []);

     
        $attendanceDates = $attendanceData->pluck('CHECKDATE')->all();

     
        $period = CarbonPeriod::create($fromDate, $toDate);
        $missingDates = [];
        foreach ($period as $date) {
            $checkDate = $date->format('Y-m-d');
            if (!in_array($checkDate, $attendanceDates) && !in_array($date->format('l'), ['Saturday','Sunday'])) {
                $missingDates[] = $checkDate;
            }
        }

       
        return response()->json([
            'empCode' => $empCode,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'attendanceRecordsCount' => $attendanceData->count(),
            'attendanceDatesReturned' => $attendanceDates,
            'missingWeekdays' => $missingDates,
            'rawApiData' => $attendanceData,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
}

}

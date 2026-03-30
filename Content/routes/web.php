<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReinsuranceController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\PremiumOutstandingController;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\UWController;
use App\Http\Controllers\UserActiveController; 
use App\Http\Controllers\RenewalController; 
use App\Http\Controllers\BrokerCodeController;
use App\Http\Controllers\RequestNoteController;
use App\Http\Controllers\OutstandingReportController;
use App\Http\Controllers\DashReport2Controller;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\POController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ClaimDBController;
use App\Http\Controllers\EmployerReportController;
use App\Http\Controllers\EmployeeWiseReportController;
use App\Http\Controllers\BrokerReportController;
use App\Http\Controllers\UwDashboardController;
use App\Http\Controllers\AttReqController;
use App\Http\Controllers\HealthDashboardController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\GlClmController;
use App\Http\Controllers\DealerWiseReportController;
use App\Http\Controllers\DoWiseReportController;
use Illuminate\Http\Request;
use App\Http\Controllers\PortalLinkController;
use App\Http\Controllers\UnpostedReportsController;
use App\Http\Controllers\UWdashController; 
use Carbon\Carbon;
/*

|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['middleware'=>"web"], function(){

    Route::get('/', function () {
        return view('main');
    });


        //logout
        Route::get('logout', function () {
            Session::forget('user');
            return redirect('/login');
        });



    Route::match(['get', 'post'], '/login', [UserController::class, 'login']);
    Route::match(['get', 'post'], '/changePassword', [UserController::class, 'changePassword']);
    Route::match(['get', 'post'], '/makeHash', [UserController::class, 'makeHash']);

    Route::match(['get', 'post'], 'bank_salary', [SalaryController::class, 'bankSalary']);
    Route::match(['get', 'post'], 'bank_salary_export', [SalaryController::class, 'exportBankSalaryPrint']);
    Route::match(['get', 'post'], 'emp_salary', [SalaryController::class, 'empSalary']);

    Route::match(['get', 'post'], 'gisulog', [LogController::class, 'gisUserLog']);


    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/dep', [ReportController::class, 'depreciation']);           
    Route::get('/transfer', [ReportController::class, 'transfer']);
    Route::get('/register', [ReportController::class, 'register']);
    Route::post('/dep/history', [ReportController::class, 'addHistory'])->name('addHistory');

    Route::get('/reinsurace', [ReinsuranceController::class, 'index']);
    Route::get('/broker', [BrokerController::class, 'index']);
    Route::get('/premium', [PremiumOutstandingController::class, 'index']);
    Route::get('/user', [UserReportController::class, 'index']);
    Route::get('/u_active', [UserActiveController::class, 'index']);
    Route::get('/renewal', [RenewalController::class, 'index']);
    Route::get('/code', [BrokerCodeController::class, 'index']);
Route::get('/logins-by-date', [UserActiveController::class, 'getLoginsByDate'])->name('logins.by.date');
Route::get('/logouts-by-date', [UserActiveController::class, 'getLogoutsByDate'])->name('logouts.by.date');
//
    Route::get('/getnote', [RequestNoteController::class, 'index']);





Route::get('/getnote/document-numbers', [RequestNoteController::class, 'getDocumentNumbers']);




Route::get('/uw', [UWController::class, 'getRenewalData']);
Route::get('/os', [OutstandingReportController::class, 'getOutstandingData']);
Route::get('/osg', [OutstandingReportController::class, 'getOutstandingGroupData']);
Route::get('/osgd', [OutstandingReportController::class, 'getOutstandingGroupBranchData']);
Route::get('/ost', [OutstandingReportController::class, 'getOutstandingTimelineData']);

//

Route::get('/do', [DashReport2Controller::class, 'getOutDoData']);

//

Route::get('/do-os', [DashReport2Controller::class, 'getOutstandingData']);
Route::get('/do-osg', [DashReport2Controller::class, 'getOutstandingGroupData']);
Route::get('/do-osgd', [DashReport2Controller::class, 'getOutstandingGroupBranchData']);
Route::get('/do-ost', [DashReport2Controller::class, 'getOutstandingTimelineData']);
Route::get('/do-uw', [DashReport2Controller::class, 'getRenewalData']);

//
Route::get('/do-gd', [DashReport2Controller::class, 'getBranchReport']);

Route::post('/generate-request-note-pdf', [RequestNoteController::class, 'generatePDF'])->name('generate.request.note.pdf');



//
Route::get('/reports/pdf/{requestNote}', [RequestNoteController::class, 'generatePdf'])->name('reports.pdf');
Route::get('/reports/preview/{requestNote}', [RequestNoteController::class, 'previewPdf'])->name('reports.preview');

Route::get('/generate-pdf/{recordId}', [RequestNoteController::class, 'generatePdf'])->name('generate.pdf');






Route::post('/r1/export-pdf', [RequestNoteController::class, 'exportR1RowPdf'])->name('r1.export.pdf');


Route::get('/generate-request-note-pdf/{reqNote}', [RequestNoteController::class, 'generatePdf'])->name('generate.request.note.pdf');


Route::get('/email', [RequestNoteController::class, ' EmailLog']);


// Reinsurance Cases project
Route::get('/r1', [RequestNoteController::class, 'getReinsuranceR1Data']);
Route::get('/r2', [RequestNoteController::class, 'getReinsuranceR2Data']);
Route::get('/c', [RequestNoteController::class, 'getReinsuranceCase']);
Route::get('/show', [RequestNoteController::class, 'show']);
Route::get('/getshow', [RequestNoteController::class, 'getshow']);
Route::get('/get-email-logs', [RequestNoteController::class, 'getEmailLogs'])->name('get.email.logs');
Route::get('/getlast', [RequestNoteController::class, 'getlast'])->name('reinsurance.getlast');
Route::post('/verify-record', [RequestNoteController::class, 'verifyRecord'])->name('verify.record');
Route::post('/send-email', [EmailController::class, 'sendEmail'])->name('send.email');
Route::post('/fetch-reinsurance-data', [RequestNoteController::class, 'fetchReinsuranceData'])->name('fetch.reinsurance.data');






//
// monday



//Route::get('/r1/emailed', [ReportController::class, 'getReinsuranceR1EmailedData'])->name('r1.emailed');
//Route::get('/getlast', [RequestNoteController::class, 'getlast']);

//

Route::post('/upload-file', [RequestNoteController::class, 'uploadFile'])->name('upload.file');

Route::post('/send-and-verify-email', [RequestNoteController::class, 'sendAndVerifyEmail'])->name('send-and-verify-email');
//Route::post('/verify-record', [YourController::class, 'verifyRecord'])->name('verify.record');

Route::get('/po', [POController::class, 'index']);
//
// Cliam 
Route::get('/claim', [ClaimController::class, 'index']);
Route::get('/cr2', [ClaimController::class, 'claim2']);
Route::get('/cr3', [ClaimController::class, 'Claim3']);
Route::get('/cr4', [ClaimController::class, 'Claim4']);
Route::get('/cr5', [ClaimController::class, 'claim5'])->name('claim5');
Route::post('/cr5/feedback', [ClaimController::class, 'storeFeedback'])->name('claim5.feedback');
Route::post('/cr5/complete/{id}', [ClaimController::class, 'completeAction'])->name('claim5.complete');

//
Route::get('/cr6', [ClaimController::class, 'Claim6']);
// dashboard

Route::get('/cr7', [ClaimDBController::class, 'index']);





//Route::get('/cr5', [ClaimController::class, 'Claim5']);
});
//Route::get('/logins-by-date', [UserActiveController::class, 'getLoginsByDate'])->name('logins.by.date');





/**
 * *****************************************************************************
 * EMPLOYER REPORT ROUTES (Total-4)                                            *
 * *****************************************************************************
 */




//  Employee's Attendance Report
Route::get('/employees-attedence-report', [EmployerReportController::class, 'index'])
    ->name('employer.report');

//  Employee Wise Attendance Report
Route::get('/employee-wise-report', [EmployeeWiseReportController::class, 'index'])
    ->name('employee.wise.report');  

//  Absent & Late Attendance Report 
Route::get('/employees-absent-late-attendance-report', [EmployerReportController::class, 'absentLateReport'])
    ->name('absent-late.wise.report');

//  Employee Summary Report
Route::get('/employee-wise-summary-report',[EmployerReportController::Class,'empSummaryReport'])->name('summary.wise.report');



/**
 * ************************************************************************************
 *                             Under Writing Dashboard                                *
 * ************************************************************************************
 */

Route::get('/uw-dashboard', [UwDashboardController::class, 'index'])->name('uw.dashboard');

Route::match(['GET','POST'], '/uw-dashboard-refersh', [UwDashboardController::class, 'refreshCache'])->name('uw.dashboard-referesh');

Route::get('/uw-dashboard/cache-check', function () {
    $cacheData = Cache::get('uw_dashboard_data');

    return response()->json([
        'cached_data' => $cacheData['data'] ?? null,
        'last_refresh' => $cacheData['last_refresh'] ?? null,
        'current_time' => now()->toDateTimeString(),
        'cache_age_minutes' => $cacheData ? now()->diffInMinutes($cacheData['last_refresh']) : 'N/A',
        'cache_age_seconds' => $cacheData ? now()->diffInSeconds($cacheData['last_refresh']) : 'N/A',
        'data_available' => $cacheData && isset($cacheData['data']) ? 'Yes' : 'No',
        'data_structure' => $cacheData ? array_keys($cacheData['data'] ?? []) : 'No data'
    ]);
});

Route::match(['GET','POST'], '/brokerWiseReport', [BrokerReportController::class, 'brokerWiseReport']);


/**
 * ************************************************************************************
 *                           CRUD Attdence                                            *
 * ************************************************************************************
 */




Route::get('att_reqs', [AttReqController::class, 'index'])->name('attreq.index');

Route::get('att_reqs/create', [AttReqController::class, 'create'])->name('attreq.create');

Route::post('att_reqs', [AttReqController::class, 'store'])->name('attreq.store');


Route::get('att_reqs/{att_req}/edit', [AttReqController::class, 'edit'])->name('attreq.edit');


Route::put('att_reqs/{att_req}', [AttReqController::class, 'update'])->name('attreq.update');


Route::delete('att_reqs/{att_req}', [AttReqController::class, 'destroy'])->name('attreq.destroy');



/***********************************************
 *           health_portal                      *
 ***********************************************/

Route::get('/health-dashboard', [HealthDashboardController ::class, 'index'])->name('health.dashboard');


/***********************************************
 *           O/S Report                        *
 ***********************************************/
Route::middleware('web')->group(function () {
    
    Route::get('/stlClmOs', [GlClmController::class, 'stlClmOS'])
        ->name('claim.os.settlement');
    
    Route::post('/insert-approval', [GlClmController::class, 'insertApproval'])
        ->name('insertApproval');
});


Route::get('/get-remarks', [GlClmController::class, 'getRemarks'])->name('getRemarks');
Route::get('/get-claim-settlement-performa', [GlClmController::class, 'getClaimSettlementPerforma'])
    ->name('getClaimSettlementPerforma');

Route::get('/download-claim-doc/{filename}', [GlClmController::class, 'downloadClaimDoc'])
    ->name('downloadClaimDoc');
Route::get('/debug-claim-files/{docNum}', [GlClmController::class, 'debugClaimFiles']);

/***********************************************
 *          AutoSecure Reports                    *
 ***********************************************/

// Dealer wiseReport 
Route::get('/dealer-claims', [DealerWiseReportController::class, 'index'])->name('dealer-claims.index');
Route::post('/dealer-claims/generate', [DealerWiseReportController::class, 'generate'])->name('dealer-claims.generate');
// Dealer Summary Report
Route::get('/dealer-summary-report', [DealerWiseReportController::class, 'dealerSummaryReport'])->name('dealer.summary.report');

// D/O wise Report
Route::get('/do-report', [DoWiseReportController::class, 'index'])->name('do.index');
Route::post('/do/generate', [DoWiseReportController::class, 'generate'])->name('do.generate');

// D/O Summary Report

Route::get('/do-summary-report', [DoWiseReportController::class, 'dosummaryReport'])->name('do.summary.report');

// Unposted Documents Report Routes
Route::get('/unposted-reports', [UnpostedReportsController::class, 'index'])->name('unposted.report');
Route::post('/unposted-reports/generate', [UnpostedReportsController::class, 'generate'])->name('unposted.report.generate');

Route::post('/unposted-reports/send-email', [UnpostedReportsController::class, 'sendEmail'])
    ->name('unposted.report.send.email');


Route::post('/unposted-reports/reject-invoice', [UnpostedReportsController::class, 'rejectInvoice'])
    ->name('unposted.report.reject.invoice');

Route::get('/unposted-report/email-logs', [UnpostedReportsController::class, 'emailLogs'])->name('unposted.report.email.logs');





Route::get('/uwRenewalBr',  [UWdashController::class, 'getRenewalDataBr']);
Route::post('/uwRenewalBr/decision', [UWdashController::class, 'saveRenewalDecision']);




Route::get('/claimInt', [ClaimDBController::class, 'claimcase']);











































































































// Route::get('/generate-portal-link', [PortalLinkController::class, 'generateLink']);







//********************************test******************************** */

Route::get('/check-db', function () {
    return DB::select("SELECT DATABASE() as db");
});
Route::get('/check-table', function () {
    $exists = DB::select("SHOW TABLES LIKE 'claim_docs'");
    return !empty($exists) 
        ? "YES — att_reqs table exists!"
        : "NO — att_reqs table does NOT exist!";
});
Route::get('/list-tables', function () {
    return DB::select("SHOW TABLES");
});


Route::get('/claims_docs', function () {
    // Get all column names
    $columns = Schema::getColumnListing('claim_docs');

    // Optionally, get first 5 records
    $records = DB::table('claim_docs')->get();

    // Return as JSON for easy viewing
    return response()->json([
        'columns' => $columns,
        'records' => $records,
    ]);
});



// Test route - check if data exists
Route::get('test-data', function() {
    $count = \App\Models\AttReq::count();
    $data = \App\Models\AttReq::all();
    
    return response()->json([
        'count' => $count,
        'data' => $data,
        
    ]);
});



// Route::get('/testempwisesummaryreport',[EmployerReportController::Class,'testSummaryApi']);

















Route::get('/test-portal-url', function () {
    $ts = time();
    $secret = config('services.portal1.secret');
    $sig = hash_hmac('sha256', $ts, $secret);
    
    $url = config('services.portal1.url') . '/embedded/files?' . http_build_query([
        'ts' => $ts,
        'sig' => $sig
    ]);
    
    return '<a href="' . $url . '" target="_blank">Test Portal Link</a><br><br>URL: ' . $url;
});

Route::get('/check-env', function () {
    return response()->json([
        'env_PORTAL1_URL' => env('PORTAL1_URL'),
        'config_portal_url' => config('services.portal1.url'),
        'env_SERVICES_PORTAL1_SECRET' => !empty(env('SERVICES_PORTAL1_SECRET')),
        'config_portal_secret' => !empty(config('services.portal1.secret')),
        'current_env_file' => base_path('.env'),
        'env_file_contents' => str_contains(
            file_get_contents(base_path('.env')), 
            'PORTAL1_URL=http://192.168.170.24/Surveyor'
        )
    ]);
});

//route to check session
Route::get('/check-session', function () {
    
    if (Session::has('user')) {
        $user = Session::get('user');
        return response()->json([
            'authenticated' => true,
            'user' => $user,
            'session_id' => session()->getId()
        ]);
    }
    return response()->json(['authenticated' => false]);
});

Route::get('/check-session', function () {
    
    
    $allSession = session()->all();
    
    $userSession = [
        'user.name' => session('user.name'),
        'user_id' => session('user_id'),
        'user_name' => session('user_name'),
        'username' => session('username'),
        'name' => session('name'),
        'email' => session('email'),
        'auth' => session('auth'),
        'logged_in' => session('logged_in'),
        'user' => session('user'),
    ];
    
  
    $authUser = null;
    if (auth()->check()) {
        $authUser = [
            'id' => auth()->id(),
            'name' => auth()->user()->name ?? null,
            'email' => auth()->user()->email ?? null,
            'username' => auth()->user()->username ?? null,
        ];
    }
    
    return response()->json([
        'all_session_data' => $allSession,
        'user_session_values' => $userSession,
        'auth_user' => $authUser,
        'session_id' => session()->getId(),
    ]);
});


Route::get('/policy_renewals', function () {
    // Get all column names
    $columns = Schema::getColumnListing('policy_renewals');

    // Optionally, get first 5 records
    $records = DB::table('policy_renewals')->get();

    // Return as JSON for easy viewing
    return response()->json([
        'columns' => $columns,
        'records' => $records,
    ]);
});




Route::get('/unpostedlog', function () {
    // Get all column names
    $columns = Schema::getColumnListing('unposted_log');

    // Optionally, get first 5 records
    $records = DB::table('unposted_log')->get();

    // Return as JSON for easy viewing
    return response()->json([
        'columns' => $columns,
        'records' => $records,
    ]);
});
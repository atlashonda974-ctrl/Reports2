<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Helpers\Helper;
use Illuminate\Support\Str;



class UserReportController extends Controller

{
    public function index(Request $request)
{
    $data = Helper::fetchUser();
    //dd()

    if (isset($data['error'])) {
        return response()->json(['error' => $data['error']], 500);
    }

    $data = collect($data);
   // dd($data); 



    return view('users.index', compact('data'));
}


     
}

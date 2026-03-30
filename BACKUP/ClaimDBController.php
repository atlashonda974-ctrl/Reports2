<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ClaimDBHelper;
use Illuminate\Support\Facades\Log;

class ClaimDBController extends Controller
{
    public function index(Request $request)
    {
        
        
        $result = ClaimDBHelper::getClaimS1();
        
        return response()
            ->view('claimregister.claim7', [
                'apiData' => $result['data'],
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                
            ]);
           
    }
}


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class BrokerReportController extends Controller
{
    public function brokerWiseReport(Request $request)
    {
        $fromDate = Carbon::now()->startOfYear()->format('d-M-Y');
        $toDate = Carbon::now()->lastOfMonth()->format('d-M-Y');

        if ($request->has('from_date')) {
            $data = $request->all();
            $toDate = date('d-M-Y', strtotime($data['to_date']));
            $fromDate = date('d-M-Y', strtotime($data['from_date']));
        }

        $url = "http://172.16.22.204/dashboardApi/broker/broker_wise_data.php?expiryfrom=$fromDate&expiryto=$toDate";
        $newsData = json_decode(file_get_contents($url));

        $urlBranch = "http://172.16.22.204/dashboardApi/broker/branch_broker_wise_data.php?expiryfrom=$fromDate&expiryto=$toDate";
        $newsDataB = json_decode(file_get_contents($urlBranch));

        $urlZone = "http://172.16.22.204/dashboardApi/broker/zone_broker_wise_data.php?expiryfrom=$fromDate&expiryto=$toDate";
        $newsDataZ = json_decode(file_get_contents($urlZone));

        $urlInsured = "http://172.16.22.204/dashboardApi/broker/insured_broker_wise_data.php?expiryfrom=$fromDate&expiryto=$toDate&broker=All";
        $newsDataI = json_decode(file_get_contents($urlInsured));

        return view('broker_report', compact(
            'newsData',
            'toDate',
            'fromDate',
            'newsDataB',
            'newsDataZ',
            'newsDataI'
        ));
    }
}

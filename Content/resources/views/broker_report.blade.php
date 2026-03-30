@extends('mymasters')
@section('content')

<?php
use Carbon\Carbon;
$formatedDate_from = Carbon::parse(Carbon::parse($fromDate)->format('Y-m-d'))->format('Y-m-d');
$formatedDate_to = Carbon::parse(Carbon::parse($toDate)->format('Y-m-d'))->format('Y-m-d');

$fromShow = Carbon::parse(Carbon::parse($fromDate)->format('Y-m-d'))->format('d-m-Y');
$toShow = Carbon::parse(Carbon::parse($toDate)->format('Y-m-d'))->format('d-m-Y');

$tot_fire = 0;
$tot_mar = 0;
$tot_mot = 0;
$tot_mis = 0;
$tot_health = 0;
$tot_all = 0;

$tot_fire_b = 0;
$tot_mar_b = 0;
$tot_mot_b = 0;
$tot_mis_b = 0;
$tot_health_b = 0;
$tot_all_b = 0;

$tot_fire_z = 0;
$tot_mar_z = 0;
$tot_mot_z = 0;
$tot_mis_z = 0;
$tot_health_z = 0;
$tot_all_z = 0;
?>

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<style>
    .total-row {
        background-color: #f8f9fa !important;
        font-weight: bold;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0c4879 !important;
        color: white !important;
    }
    
    
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px !important;
        margin-top: 10px !important;
    }
    
    .dataTables_wrapper .dataTables_length {
        margin-bottom: 15px !important;
        margin-top: 10px !important;
    }
    
   
    .content-body {
        margin-left: 0 !important;
    }
    
  
    .deznav ~ .content-body {
        margin-left: 0 !important;
        padding-left: 17rem !important;
    }
    
    
    .table-responsive {
        overflow-x: auto !important;
    }
    
   
    .table-bordered th:first-child,
    .table-bordered td:first-child {
        position: static !important;
        background-color: inherit !important;
    }
</style>
@endpush

<script>
$(window).on("load", function() {
    $('.hide').not('#1').hide();
    
       // THIS WILL REINITIALIZIE DATATBELE AFTER PAGE LOAD
    setTimeout(function() {
        initializeDataTables();
    }, 500);
});

function showOne(id) {
    $('.hide').hide();
    $('#' + id).show();
    
//   THIS WILL REINITIALIZIE DATATBELE WHILE SWTICHING THE TABS 
    setTimeout(function() {
        initializeDataTables();
    }, 300);
}

function initializeDataTables() {
    // THIS WILL DESTROY EXISTING DataTables INSTANCES
    if ($.fn.DataTable.isDataTable('#brokerTable')) {
        $('#brokerTable').DataTable().destroy();
    }
    if ($.fn.DataTable.isDataTable('#branchTable')) {
        $('#branchTable').DataTable().destroy();
    }
    if ($.fn.DataTable.isDataTable('#zoneTable')) {
        $('#zoneTable').DataTable().destroy();
    }
    if ($.fn.DataTable.isDataTable('#insuredTable')) {
        $('#insuredTable').DataTable().destroy();
    }
    
    // INITIALIZE DATATABLES FOR VISIBLE TABLES 
    $('.table-bordered').each(function() {
        if ($(this).is(':visible')) {
            $(this).DataTable({
                "pageLength": 10,
                "ordering": true,
                "searching": true,
                "info": true,
                "lengthChange": true,
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "language": {
                    "search": "Search:",
                   
                },
                "initComplete": function(settings, json) {
                    
                    $('.dataTables_wrapper .dataTables_filter').css('margin-bottom', '15px');
                    $('.dataTables_wrapper .dataTables_length').css('margin-bottom', '15px');
                }
            });
        }
    });
}
</script>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12 col-xxl-12 col-lg-24 col-sm-24">
                <div class="col-xl-12 col-xxl-12 col-lg-24 col-sm-24">
                    <div class="card">
                        <div class="card-header border-0 pb-0">
                            <center><h4 class="card-title">Broker Report</h4></center>
                        </div>

                        <form class="form-horizontal" role="form" method="POST" id="my-form" action="{{ url('brokerWiseReport') }}" autocomplete="off">
                            {!! csrf_field() !!}
                            <div class="row" style="margin-top:10px; margin-left:10px;">
                                <div class="col-sm-1">
                                    <label>From:</label>
                                </div>    
                                <div class="col-sm-3">
                                    <input name="from_date" class="form-control" id="from_date" type="date" value="{{ $formatedDate_from }}" required> &emsp;
                                </div>   
                                <div class="col-sm-1">
                                    <label>To:</label>
                                </div>    
                                <div class="col-sm-3">
                                    <input name="to_date" class="form-control" id="to_date" type="date" value="{{ $formatedDate_to }}" required> &emsp;
                                </div>
                                <div class="col-sm-3">
                                    <button type="submit" class="btn btn-primary" style="height:35px; width:150px; margin-left:10px;">Get Report</button>
                                </div>
                            </div>
                        </form>

                        <div class="m-2">
                            <button type="button" onclick="showOne('1')" class="btn btn-primary">Broker Wise</button>
                            <button type="button" onclick="showOne('2')" class="btn btn-primary">Branch Wise</button>
                            <button type="button" onclick="showOne('3')" class="btn btn-primary">Zone Wise</button>
                            <button type="button" onclick="showOne('4')" class="btn btn-primary">Insured Wise</button>
                        </div>

                        <!-- Broker Wise -->
                        <div class='hide' id='1'>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="brokerTable" class="table table-bordered">
                                        <thead> 
                                            <tr style="background: #4682B4 !important;">
                                                <th style="color: white !important;"><strong>Sr#</strong></th>
                                                <th style="color: white !important;"><strong>Broker Code</strong></th>
                                                <th style="color: white !important;"><strong>Broker Name</strong></th> 
                                                <th style="color: white !important;"><strong>Fire</strong></th>
                                                <th style="color: white !important;"><strong>Marine</strong></th>
                                                <th style="color: white !important;"><strong>Motor</strong></th>
                                                <th style="color: white !important;"><strong>Misc</strong></th>
                                                <th style="color: white !important;"><strong>Health</strong></th>
                                                <th style="color: white !important;"><strong>Total</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $count = 1; @endphp
                                            @foreach ($newsData as $row)
                                                <?php
                                                $tot_fire = $tot_fire + $row->FIREPRE;
                                                $tot_mar = $tot_mar + $row->MARINEPRE;
                                                $tot_mot = $tot_mot + $row->MOTORPRE;
                                                $tot_mis = $tot_mis + $row->MISCPRE;
                                                $tot_health = $tot_health + $row->HEALTHPRE;
                                                $tot_all = $tot_all + $row->TOT_PRE;
                                                ?>
                                                <tr>
                                                    <td>{{ $count }}</td>
                                                    <td>{{ $row->PPS_BROKERCODE }}</td>
                                                    <td><a href="{{ url('/brokerDetailReport/' . $fromDate . '/' . $toDate . '/' . $row->PPS_BROKERCODE . '/' . $row->PPS_DESC) }}">{{ $row->PPS_DESC }}</a></td>
                                                    <td align="right">{{ number_format(intval($row->FIREPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MARINEPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MOTORPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MISCPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->HEALTHPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->TOT_PRE),0) }}</td>
                                                </tr>
                                                @php $count++; @endphp
                                            @endforeach  
                                        </tbody>
                                        <tfoot>
                                            <tr class="total-row">
                                                <td colspan="3" align="right"><b>Total</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_fire),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mar),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mot),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mis),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_health),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_all),0) }}</b></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Branch Wise -->
                        <div class='hide' id='2'>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="branchTable" class="table table-bordered">
                                        <thead> 
                                            <tr style="background: #4682B4 !important;">
                                                <th style="color: white !important;"><strong>Sr#</strong></th>
                                                <th style="color: white !important;"><strong>Branch Code</strong></th>
                                                <th style="color: white !important;"><strong>Branch Name</strong></th> 
                                                <th style="color: white !important;"><strong>Fire</strong></th>
                                                <th style="color: white !important;"><strong>Marine</strong></th>
                                                <th style="color: white !important;"><strong>Motor</strong></th>
                                                <th style="color: white !important;"><strong>Misc</strong></th>
                                                <th style="color: white !important;"><strong>Health</strong></th>
                                                <th style="color: white !important;"><strong>Total</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $count = 1; @endphp
                                            @foreach ($newsDataB as $row)
                                                <?php
                                                $tot_fire_b = $tot_fire_b + $row->FIREPRE;
                                                $tot_mar_b = $tot_mar_b + $row->MARINEPRE;
                                                $tot_mot_b = $tot_mot_b + $row->MOTORPRE;
                                                $tot_mis_b = $tot_mis_b + $row->MISCPRE;
                                                $tot_health_b = $tot_health_b + $row->HEALTHPRE;
                                                $tot_all_b = $tot_all_b + $row->TOT_PRE;
                                                ?>
                                                <tr>
                                                    <td>{{ $count }}</td>
                                                    <td>{{ $row->PLC_LOC_CODE }}</td>
                                                    <td>{{ $row->PLC_LOCADESC }}</td>
                                                    <td align="right">{{ number_format(intval($row->FIREPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MARINEPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MOTORPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MISCPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->HEALTHPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->TOT_PRE),0) }}</td>
                                                </tr>
                                                @php $count++; @endphp
                                            @endforeach  
                                        </tbody>
                                        <tfoot>
                                            <tr class="total-row">
                                                <td colspan="3" align="right"><b>Total</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_fire_b),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mar_b),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mot_b),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mis_b),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_health_b),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_all_b),0) }}</b></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Zone Wise -->
                        <div class='hide' id='3'>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="zoneTable" class="table table-bordered">
                                        <thead> 
                                            <tr style="background: #4682B4 !important;">
                                                <th style="color: white !important;"><strong>Sr#</strong></th>
                                                <th style="color: white !important;"><strong>Zone</strong></th> 
                                                <th style="color: white !important;"><strong>Fire</strong></th>
                                                <th style="color: white !important;"><strong>Marine</strong></th>
                                                <th style="color: white !important;"><strong>Motor</strong></th>
                                                <th style="color: white !important;"><strong>Misc</strong></th>
                                                <th style="color: white !important;"><strong>Health</strong></th>
                                                <th style="color: white !important;"><strong>Total</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $count = 1; @endphp
                                            @foreach ($newsDataZ as $row)
                                                <?php
                                                $tot_fire_z = $tot_fire_z + $row->FIREPRE;
                                                $tot_mar_z = $tot_mar_z + $row->MARINEPRE;
                                                $tot_mot_z = $tot_mot_z + $row->MOTORPRE;
                                                $tot_mis_z = $tot_mis_z + $row->MISCPRE;
                                                $tot_health_z = $tot_health_z + $row->HEALTHPRE;
                                                $tot_all_z = $tot_all_z + $row->TOT_PRE;
                                                ?>
                                                <tr>
                                                    <td>{{ $count }}</td>
                                                    <td>{{ $row->PLC_LOC_SUB_SEG }}</td>
                                                    <td align="right">{{ number_format(intval($row->FIREPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MARINEPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MOTORPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MISCPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->HEALTHPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->TOT_PRE),0) }}</td>
                                                </tr>
                                                @php $count++; @endphp
                                            @endforeach  
                                        </tbody>
                                        <tfoot>
                                            <tr class="total-row">
                                                <td colspan="2" align="right"><b>Total</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_fire_z),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mar_z),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mot_z),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mis_z),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_health_z),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_all_z),0) }}</b></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Insured Wise -->
                        <div class='hide' id='4'>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="insuredTable" class="table table-bordered">
                                        <thead> 
                                            <tr style="background: #4682B4 !important;">
                                                <th style="color: white !important;"><strong>Sr#</strong></th>
                                                <th style="color: white !important;"><strong>Insured Code</strong></th>
                                                <th style="color: white !important;"><strong>Insured</strong></th> 
                                                <th style="color: white !important;"><strong>Fire</strong></th>
                                                <th style="color: white !important;"><strong>Marine</strong></th>
                                                <th style="color: white !important;"><strong>Motor</strong></th>
                                                <th style="color: white !important;"><strong>Misc</strong></th>
                                                <th style="color: white !important;"><strong>Health</strong></th>
                                                <th style="color: white !important;"><strong>Total</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $count = 1; @endphp
                                            @foreach ($newsDataI as $row)
                                                <?php
                                                $tot_fire_b = $tot_fire_b + $row->FIREPRE;
                                                $tot_mar_b = $tot_mar_b + $row->MARINEPRE;
                                                $tot_mot_b = $tot_mot_b + $row->MOTORPRE;
                                                $tot_mis_b = $tot_mis_b + $row->MISCPRE;
                                                $tot_health_b = $tot_health_b + $row->HEALTHPRE;
                                                $tot_all_b = $tot_all_b + $row->TOT_PRE;
                                                ?>
                                                <tr>
                                                    <td>{{ $count }}</td>
                                                    <td>{{ $row->PPS_PARTY_CODE }}</td>
                                                    <td>{{ $row->PPS_DESC }}</td>
                                                    <td align="right">{{ number_format(intval($row->FIREPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MARINEPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MOTORPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->MISCPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->HEALTHPRE),0) }}</td>
                                                    <td align="right">{{ number_format(intval($row->TOT_PRE),0) }}</td>
                                                </tr>
                                                @php $count++; @endphp
                                            @endforeach  
                                        </tbody>
                                        <tfoot>
                                            <tr class="total-row">
                                                <td colspan="3" align="right"><b>Total</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_fire_b),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mar_b),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mot_b),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_mis_b),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_health_b),0) }}</b></td>
                                                <td align="right"><b>{{ number_format(intval($tot_all_b),0) }}</b></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@endpush

@endsection
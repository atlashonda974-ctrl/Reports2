<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttReq extends Model
{
    use HasFactory;

    protected $table = 'att_reqs';
    
    protected $fillable = [
        'att',
        'empcode',
        'schddate',
        'remarks',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'schddate' => 'date'
    ];

   
}
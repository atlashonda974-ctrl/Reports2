<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyRenewal extends Model
{
    use HasFactory;

    protected $table = 'policy_renewals';

    protected $fillable = [
        'document_no',
        'base_document',
        'insured_name',
        'expiry_date',
        'renewal_decision',
        'remarks'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'renewal_decision' => 'boolean'
    ];
}
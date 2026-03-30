<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnpostedLog extends Model
{
    protected $table = 'unposted_log';
    
    protected $fillable = [
        'email_from',
        'recipient_to',
        'recipient_cc',
        'subject',
        'email_body',
        'document_count',
        'documents',
        'sent_by'
    ];
    
    protected $casts = [
        'documents' => 'array'
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class budget extends Model
{
	use HasFactory;

    protected $fillable = ['id', 'a_bracod', 'bracod', 'month', 'year', 'uwfire', 'uwmar', 'uwmor', 'uwmisc', '
    uwhlt', 'colfire', 'colmar', 'colmor', 'colmisc', 'colhlt', 'clmfire', 'clmmar', 'clmmor', 'clmmisc', 
    'clmhlt', 'comfire', 'commar', 'commor', 'commisc', 'comhlt'];
    
}

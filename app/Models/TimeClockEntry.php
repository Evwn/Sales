<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeClockEntry extends Model
{
    protected $fillable = ['user_id',
    'branch_id',
	'clock_in',
	'clock_out',
	'shift_id' ,];
    
}

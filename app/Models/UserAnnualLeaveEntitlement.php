<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnnualLeaveEntitlement extends Model
{
    protected $fillable = ['user_id', 'year', 'days'];
}

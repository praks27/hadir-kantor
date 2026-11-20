<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuAccessSetting extends Model
{
    protected $fillable = ['scope_type', 'scope_key', 'permissions'];

    protected function casts(): array
    {
        return ['permissions' => 'array'];
    }
}

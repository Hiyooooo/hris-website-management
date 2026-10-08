<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sites extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'secret' => 'encrypted',
        'previous_secret' => 'encrypted',
        'previous_secret_expired_at' => 'datetime',
        'last_used_at' => 'datetime'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurrentDuty extends Model
{
    protected $fillable = [
        'date',
        'hour',
    ];

    protected $casts = [
        'date' => 'datetime',
        'hour' => 'integer',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'current_duties_users', 'current_duty_id');
    }
}

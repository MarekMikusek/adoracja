<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Intention extends Model
{
    protected $table = 'intentions';

    protected $fillable = ['intention', 'user_id'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

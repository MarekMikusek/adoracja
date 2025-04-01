<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntentionUser extends Model
{
    protected $table = 'intentions_users';

protected $fillable = [
    'intention_id',
    'user_id'
];
}

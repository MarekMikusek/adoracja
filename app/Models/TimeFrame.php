<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TimeFrame extends Model
{
    protected $table = 'time_frames';
    protected $fillable = [
        'day',
        'hour',
    ];

    public static function getTimeFramesByDays(): EloquentCollection
    {
        return self::all()->groupBy('day');
    }
}

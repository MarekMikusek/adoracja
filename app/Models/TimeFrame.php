<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeFrame newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeFrame newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeFrame query()
 * @mixin \Eloquent
 */
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

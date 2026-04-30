<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $intention
 * @property int|null $user_id
 * @property int $is_confirmed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Intention newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Intention newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Intention query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Intention whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Intention whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Intention whereIntention($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Intention whereIsConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Intention whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Intention whereUserId($value)
 * @mixin \Eloquent
 */
class Intention extends Model
{
    protected $table = 'intentions';

    protected $fillable = ['intention', 'user_id', 'is_confirmed'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

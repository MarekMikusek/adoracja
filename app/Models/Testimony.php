<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nickname
 * @property int $is_confirmed
 * @property string $testimony
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimony newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimony newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimony query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimony whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimony whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimony whereIsConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimony whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimony whereTestimony($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimony whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Testimony extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'nickname',
        'is_confirmed',
        'testimony',
    ];
}

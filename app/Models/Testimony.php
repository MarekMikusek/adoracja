<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nickname
 * @property bool $is_confirmed
 * @property string $testimony
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Testimony extends Model
{
    use HasFactory;

    protected $fillable = [
        'nickname',
        'is_confirmed',
        'testimony',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope: Tylko zatwierdzone opinie.
     */
    public function scopeConfirmed(Builder $query): void
    {
        $query->where('is_confirmed', true);
    }

    /**
     * Scope: Sortowanie od najnowszych.
     */
    public function scopeRecent(Builder $query): void
    {
        $query->orderByDesc('created_at');
    }
}

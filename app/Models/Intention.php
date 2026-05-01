<?php

declare (strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Intention extends Model
{
    use HasFactory;
    protected $table = 'intentions';

    protected $fillable = ['intention', 'user_id', 'is_confirmed'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'intentions_users',
            'intention_id',
            'user_id'
        )->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, ?int $userId): void
    {
        $query->where(function (Builder $q) use ($userId) {
            $q->where('is_confirmed', true);

            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $intention_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntentionUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntentionUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntentionUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntentionUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntentionUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntentionUser whereIntentionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntentionUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IntentionUser whereUserId($value)
 * @mixin \Eloquent
 */
class IntentionUser extends Model
{
    protected $table = 'intentions_users';

protected $fillable = [
    'intention_id',
    'user_id'
];
}

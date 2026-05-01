<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaysOfContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaysOfContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaysOfContact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaysOfContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaysOfContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaysOfContact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaysOfContact whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class WaysOfContact extends Model
{
    protected $table = 'ways_of_contacts';

    protected $fillable = ['name'];
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $coordinator_responsible
 * @property int|null $day
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $coordinatorResponsible
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyCoordinatorPattern newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyCoordinatorPattern newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyCoordinatorPattern query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyCoordinatorPattern whereCoordinatorResponsible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyCoordinatorPattern whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyCoordinatorPattern whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyCoordinatorPattern whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyCoordinatorPattern whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class MonthlyCoordinatorPattern extends Model
{
    protected $table = 'monthly_coordinators_patterns';

    protected $primaryKey = 'day';
    public $incrementing  = false;
    protected $keyType    = 'int';

    protected $fillable = [
        'coordinator_responsible',
        'day',
        'updated_by',
    ];

    public function coordinatorResponsible()
    {
        return $this->belongsTo(User::class, 'coordinator_responsible');
    }

    public static function coordinatorsResponsible()
    {
        return self::leftJoin('users as u', 'monthly_coordinators_patterns.coordinator_responsible', 'u.id')
            ->selectRaw("monthly_coordinators_patterns.day, CONCAT(u.first_name, ' ', u.last_name) as full_name")
            ->orderBy('monthly_coordinators_patterns.day')
            ->pluck('full_name', 'day')
            ->toArray();
    }
}

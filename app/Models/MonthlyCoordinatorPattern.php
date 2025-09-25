<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

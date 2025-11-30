<?php
namespace App\Http\Controllers;

use App\Enums\DutyType;
use App\Http\Requests\AdminRemoveCurrentDutyRequest;
use App\Http\Requests\AdminStoreCurrentDutyRequest;
use App\Models\AdminDutyPattern;
use App\Models\CurrentDuty;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminCurrentDutyController extends Controller
{
    public function edit(CurrentDuty $duty)
    {
        $users = User::orderBy('first_name')
        ->orderBy('last_name')
        ->get()
        ->keyBy('id');

        $currentDuties = DB::table('current_duties as cd')
            ->selectRaw("
                cd.date,
                cd.hour,
                cdu.user_id,
                cd.id as duty_id,
                cdu.duty_type,
                u.first_name || ' ' || u.last_name as name,
                cdu.current_duty_id"
            )
            ->where('cd.id', $duty->id)
            ->leftJoin('current_duties_users as cdu', 'cdu.current_duty_id', 'cd.id')
            ->leftJoin('users as u', 'cdu.user_id', 'u.id')
            ->orderBy('cd.date')
            ->orderBy('cd.hour')
            ->get();
// dd($currentDuties);
        $duties                         = [];
        $duties[DutyType::DUTY->value]  = [];
        $duties[DutyType::READY->value] = [];
        $duties[DutyType::SUSPEND->value] = [];

        foreach ($currentDuties as $duty) {
            $duties[$duty->duty_type][] = $duty->user_id;
        }

        return view('admin.hours.duty', [
            'duties' => $duties,
            'duty'   => $duty,
            'admin'  => AdminDutyPattern::getAdmin($duty),
            'users'  => $users,
        ]);
    }

    public function addUser(AdminStoreCurrentDutyRequest $request)
    {
        $valiated = $request->validated();
        return DB::insert('insert into current_duties_users (user_id, current_duty_id, duty_type) values (?, ?, ?)', [
            $valiated['user_id'],
            $valiated['current_duty_id'],
            $valiated['duty_type'],
        ]);
    }

    public function removeCurrentDuty(AdminRemoveCurrentDutyRequest $request)
    {
        $validated = $request->validated();
        foreach ($validated['users'] as $user) {
            DB::table('current_duties_users')
                ->where('current_duty_id', $validated['current_duty_id'])
                ->where('user_id', $user)
                ->delete();
        }
        return true;
    }

}

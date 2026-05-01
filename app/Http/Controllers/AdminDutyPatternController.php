<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDutyPatternController extends Controller
{
    private $days = [
        'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela'
    ];

    public function index()
    {
        // Pobierz wszystkich adminów
        $admins = User::where('is_admin', 1)
            ->orderBy('last_name')
            ->get();

        // Pobierz istniejące wzorce i zorganizuj je w tablicę [dzień][godzina]
        $patterns = DB::table('admin_duty_patterns')
            ->get()
            ->groupBy('day')
            ->map(function ($dayGroup) {
                return $dayGroup->keyBy('hour');
            });

        return view('admin.admins.duty-patterns', [
            'admins' => $admins,
            'patterns' => $patterns,
            'days' => $this->days
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'day' => 'required|string',
            'hour' => 'required|integer|between:0,23',
            'admin_id' => 'nullable|exists:users,id'
        ]);

        // UpdateOrCreate zapobiega duplikatom dla tej samej godziny i dnia
        DB::table('admin_duty_patterns')->updateOrInsert(
            ['day' => $request->day, 'hour' => $request->hour],
            [
                'admin_id' => $request->admin_id,
                'updated_at' => now()
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Zaktualizowano koordynatora']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\MonthlyCoordinatorPattern;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MonthlyCoordinatorPatternController extends Controller
{
    public function index()
    {
        // Get all coordinators already assigned for this month
        $patterns = MonthlyCoordinatorPattern::coordinatorsResponsible();

        // Get all users (for dropdown list)
        $users = User::orderBy('first_name')->orderBy('last_name')->get();

        // Get number of days in current month
        $daysInMonth = 31;

        return view('admin.index', compact('patterns', 'users', 'daysInMonth'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'day' => 'required|integer|min:1|max:31',
            'coordinator_responsible' => 'nullable|exists:users,id',
        ]);

        MonthlyCoordinatorPattern::updateOrCreate(
            ['day' => $request->day],
            [
                'coordinator_responsible' => $request->coordinator_responsible,
                'updated_by' => Auth::id()
            ]
        );

        return redirect()->route('coordinators.index')->with('success', 'Coordinator updated successfully.');
    }
}

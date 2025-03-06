<?php

namespace App\Http\Controllers;

use App\Models\CurrentDuty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response as ResponseFacade;
use League\Csv\Writer;
use SplTempFileObject;

class DutyExportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Export duties to CSV.
     */
    public function exportCsv(Request $request): Response
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::now()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::now()->endOfMonth()));

        $duties = CurrentDuty::query()
            ->with(['user', 'admin'])
            ->whereBetween('duty_date', [$startDate, $endDate])
            ->orderBy('duty_date')
            ->orderBy('hour')
            ->get();

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        
        // Add CSV headers
        $csv->insertOne([
            'Data',
            'Godzina',
            'Użytkownik',
            'Administrator',
            'Status',
            'Utworzono',
            'Zaktualizowano'
        ]);

        // Add duty records
        foreach ($duties as $duty) {
            $csv->insertOne([
                $duty->duty_date,
                sprintf('%02d:00', $duty->hour),
                $duty->user->full_name,
                $duty->admin->full_name ?? 'N/A',
                $duty->completed ? 'Ukończony' : 'Nieukończony',
                $duty->created_at->format('Y-m-d H:i:s'),
                $duty->updated_at->format('Y-m-d H:i:s')
            ]);
        }

        $filename = sprintf(
            'dyzury_%s_%s.csv',
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        return ResponseFacade::make(
            $csv->getContent(),
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Encoding' => 'UTF-8',
            ]
        );
    }

    /**
     * Export user statistics to CSV.
     */
    public function exportUserStats(Request $request): Response
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::now()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::now()->endOfMonth()));

        $users = User::query()
            ->withCount([
                'duties' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('duty_date', [$startDate, $endDate]);
                },
                'completedDuties' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('duty_date', [$startDate, $endDate])
                        ->where('completed', true);
                }
            ])
            ->withSum([
                'dutyPoints' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            ], 'points')
            ->get();

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        
        // Add CSV headers
        $csv->insertOne([
            'Użytkownik',
            'Email',
            'Liczba dyżurów',
            'Ukończone dyżury',
            'Punkty',
            'Procent ukończonych'
        ]);

        // Add user statistics
        foreach ($users as $user) {
            $completionRate = $user->duties_count > 0
                ? round(($user->completed_duties_count / $user->duties_count) * 100, 2)
                : 0;

            $csv->insertOne([
                $user->full_name,
                $user->email,
                $user->duties_count,
                $user->completed_duties_count,
                $user->duty_points_sum_points ?? 0,
                $completionRate . '%'
            ]);
        }

        $filename = sprintf(
            'statystyki_uzytkownikow_%s_%s.csv',
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        return ResponseFacade::make(
            $csv->getContent(),
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Encoding' => 'UTF-8',
            ]
        );
    }
} 
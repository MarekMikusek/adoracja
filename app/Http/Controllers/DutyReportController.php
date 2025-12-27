<?php

namespace App\Http\Controllers;

use App\Models\CurrentDuty;
use App\Models\DutyReport;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DutyReportController extends Controller
{
    protected $notificationService;

    /**
     * Create a new controller instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Display duty reports.
     */
    public function index(): View
    {
        $query = DutyReport::query()->with(['duty.user', 'attachments']);

        if (!Auth::user()->is_admin) {
            $query->whereHas('duty', function ($query) {
                $query->where('user_id', Auth::id());
            });
        }

        $reports = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return ViewFacade::make('duty-reports.index', [
            'reports' => $reports
        ]);
    }

    /**
     * Store a new duty report.
     */
    public function store(Request $request, CurrentDuty $duty): RedirectResponse
    {
        if ($duty->user_id !== Auth::id()) {
            return Redirect::back()
                ->with('error', 'Nie możesz dodać raportu do tego dyżuru');
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'attachments.*' => ['nullable', 'file', 'max:10240'] // 10MB max
        ]);

        $report = DutyReport::create([
            'duty_id' => $duty->id,
            'content' => $validated['content']
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('duty-reports');
                $report->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName()
                ]);
            }
        }

        // Send notification to admin
        if ($duty->admin_id) {
            $this->notificationService->sendNotification(
                $duty->admin,
                "Nowy raport z dyżuru z dnia {$duty->duty_date} godz. {$duty->hour}:00"
            );
        }

        return Redirect::route('duty-reports.index')
            ->with('success', 'Raport został dodany');
    }

    /**
     * Update the specified duty report.
     */
    public function update(Request $request, DutyReport $report): RedirectResponse
    {
        if ($report->duty->user_id !== Auth::id()) {
            return Redirect::back()
                ->with('error', 'Nie możesz edytować tego raportu');
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'attachments.*' => ['nullable', 'file', 'max:10240'] // 10MB max
        ]);

        $report->update([
            'content' => $validated['content']
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('duty-reports');
                $report->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName()
                ]);
            }
        }

        return Redirect::route('duty-reports.index')
            ->with('success', 'Raport został zaktualizowany');
    }

    /**
     * Remove the specified duty report attachment.
     */
    public function removeAttachment(DutyReport $report, int $attachmentId): RedirectResponse
    {
        if ($report->duty->user_id !== Auth::id()) {
            return Redirect::back()
                ->with('error', 'Nie możesz usunąć tego załącznika');
        }

        $attachment = $report->attachments()->findOrFail($attachmentId);
        Storage::delete($attachment->path);
        $attachment->delete();

        return Redirect::back()
            ->with('success', 'Załącznik został usunięty');
    }
} 
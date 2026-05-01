<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Intentions\CreateIntentionAction;
use App\Actions\Intentions\TogglePrayerAction;
use App\Http\Requests\IsPrayerRequest;
use App\Http\Requests\SaveIntentionRequest;
use App\Models\Intention;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class IntentionController extends Controller
{
    // index() pozostaje bez zmian (obsługuje widoczność dla gości/zalogowanych)[cite: 1]
    public function index(): View
    {
        $userId = Auth::id();

        $intentions = Intention::query()
            ->visibleTo($userId)
            ->withCount('participants as users_count')
            ->withExists(['participants as is_user_joined' => function ($query) use ($userId) {
                $query->where('users.id', $userId);
            }])
            ->orderBy('id', 'desc')
            ->get();

        return view('intentions.index', [
            'intentions' => $intentions,
            'user_id' => $userId,
        ]);
    }

    /**
     * Zapisuje nową intencję. userId będzie nullem dla niezalogowanych[cite: 1, 4].
     */
    public function save(SaveIntentionRequest $request, CreateIntentionAction $action): Intention
    {
        return $action->execute(
            $request->validated('intention'),
            Auth::id() // Zwraca null, jeśli użytkownik nie jest zalogowany[cite: 1]
        );
    }

    /**
     * Dołączenie do modlitwy nadal wymaga bycia zalogowanym,
     * ponieważ tabela intentions_users wymaga user_id.
     */
    public function isPrayer(IsPrayerRequest $request, TogglePrayerAction $action): void
    {
        $action->execute(
            (int) $request->validated('intention_id'),
            Auth::id() ?? throw new \Illuminate\Auth\AuthenticationException(),
            (bool) $request->validated('is_prayer')
        );
    }
}

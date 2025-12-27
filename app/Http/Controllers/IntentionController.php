<?php
namespace App\Http\Controllers;

use App\Http\Requests\IsPrayerRequest;
use App\Http\Requests\SaveIntentionRequest;
use App\Models\Intention;
use App\Models\IntentionUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Log;

class IntentionController extends Controller
{
   public function index()
{
    $isAuthenticated = Auth::check();
    $userId = $isAuthenticated ? Auth::id() : null;

    $query = DB::table('intentions as i')
        ->select(
            'i.id',
            'i.intention',
            'i.user_id',
            'i.is_confirmed',
            'i.created_at',
            'i.updated_at'
        );

    // --- FILTROWANIE ---

    if ($isAuthenticated) {
        // Zalogowany widzi:
        // 1. swoje intencje
        // 2. intencje zatwierdzone
        $query->where(function ($q) use ($userId) {
            $q->where('i.user_id', $userId)
              ->orWhere('i.is_confirmed', 1);
        });
    } else {
        // Niezalogowany widzi tylko zatwierdzone
        $query->where('i.is_confirmed', 1);
    }

    // --- DODATKOWE POLA DLA ZALOGOWANEGO ---
    if ($isAuthenticated) {
        $query->selectRaw('
            CASE
                WHEN i.user_id = ? THEN TRUE
                ELSE FALSE
            END as is_creator', [$userId]
        );

        $query->selectRaw('
            EXISTS (
                SELECT 1
                FROM intentions_users as iu_check
                WHERE iu_check.intention_id = i.id
                AND iu_check.user_id = ?
            ) as is_user_joined', [$userId]
        );

    } else {
        // Dla niezalogowanych pola = false
        $query->selectRaw('FALSE as is_creator');
        $query->selectRaw('FALSE as is_user_joined');
    }

    // Liczba użytkowników powiązanych z intencją
    $query->selectRaw('
        (
            SELECT COUNT(*)
            FROM intentions_users as iu_count
            WHERE iu_count.intention_id = i.id
        ) as users_count'
    );

    return ViewFacade::make('intentions.index', [
        'intentions' => $query->orderBy('i.id', 'desc')->get(),
        'user_id'    => $userId,
    ]);
}

    public function save(SaveIntentionRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = Auth::user() ? Auth::user()->id : null;

        if ($user = Auth::user()) {
            $data['user_id'] = $user->id;
        }

        return Intention::create(['intention' => $data['intention'], 'user_id' => $data['user_id']]);
    }

    public function isPrayer(IsPrayerRequest $request)
    {
        $data   = $request->validated();
        $userId = Auth::user()->id;

        if ($data['is_prayer'] === "1") {

            return IntentionUser::create(['intention_id' => $data['intention_id'], 'user_id' => $userId]);
        } else {
                return IntentionUser::where('user_id', $userId)
                ->where('intention_id', intval($data['intention_id']))
                ->delete();
        }

    }
}

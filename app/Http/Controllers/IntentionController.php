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
        $userId          = $isAuthenticated ? Auth::id() : null;

        $query = DB::table('intentions as i')
            ->select(
                'i.id',
                'i.intention',
                'i.user_id',
                'i.created_at',
                'i.updated_at'
            );

        // Dynamiczne budowanie zapytania dla kolumn specyficznych dla zalogowanego użytkownika
        if ($isAuthenticated) {
            $query->selectRaw('
                -- I. Czy zalogowany użytkownik jest twórcą intencji
                CASE
                    WHEN i.user_id = ? THEN TRUE
                    ELSE FALSE
                END as is_creator',
                [$userId]
            );

            $query->selectRaw('
                -- II. Czy zalogowany użytkownik jest w intensions_users
                EXISTS (
                    SELECT 1
                    FROM intentions_users as iu_check
                    WHERE iu_check.intention_id = i.id
                    AND iu_check.user_id = ?
                ) as is_user_joined',
                [$userId]
            );

        } else {
            // Dla niezalogowanego użytkownika ustawiamy te kolumny na FALSE (0)
            $query->selectRaw('FALSE as is_creator');
            $query->selectRaw('FALSE as is_user_joined');
        }

        // To zliczenie jest niezależne od zalogowanego użytkownika, więc dodajemy je zawsze
        $query->selectRaw('
            -- III. Liczba użytkowników powiązanych z intencją
            (
                SELECT COUNT(*)
                FROM intentions_users as iu_count
                WHERE iu_count.intention_id = i.id
            ) as users_count'
        );
$intensions = $query->orderBy('i.id', 'desc')->get();
dd($intensions);
        return ViewFacade::make('intentions.index', [
            'intentions' => $query->orderBy('i.id', 'desc')->get(),
            'user_id'    => Auth::user()->id ?? null,
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
        Log::info('request', ['req'=> $request]);
        $data   = $request->validated();
        Log::info('data', ['data'=>$data]);
        $userId = Auth::user()->id;

        if ($data['is_prayer'] === "1") {
Log::info('if', ['data' => $data['is_prayer']]);
            return IntentionUser::create(['intention_id' => $data['intention_id'], 'user_id' => $userId]);
        } else {
                return IntentionUser::where('user_id', $userId)
                ->where('intention_id', intval($data['intention_id']))
                ->delete();
        }

    }
}

<?php
namespace App\Http\Controllers;

use App\Http\Requests\IsPrayerRequest;
use App\Http\Requests\SaveIntentionRequest;
use App\Models\Intention;
use App\Models\IntentionUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View as ViewFacade;

class IntentionController extends Controller
{
    public function index()
    {
        $user      = Auth::user();
        $firstPart = DB::table('intentions_users as iu')
            ->select('i.id as intention_id', 'i.intention', 'i.user_id as user_id', 'i.created_at as created_at_intention')
            ->leftJoin('intentions as i', 'iu.intention_id', '=', 'i.id');

        $secondPart = DB::table('intentions as i')
            ->select('i.id as intention_id', 'i.intention', 'i.user_id as user_id', 'i.created_at as created_at_intention')
            ->leftJoin('intentions_users as iu', 'i.id', '=', 'iu.intention_id')
            ->whereNull('iu.intention_id');

        $unionQuery = $firstPart->unionAll($secondPart);

        $intentions = DB::query()
            ->fromSub($unionQuery, 'combined_results')
            ->whereNotNull('user_id')
            ->orderBy('created_at_intention', 'DESC')
            ->get();

        $return = [];
        if ($intentions->count() > 0) {
            foreach ($intentions as $intention) {

                if (! isset($return[$intention->intention_id])) {
                    $return[$intention->intention_id]                  = [];
                    $return[$intention->intention_id]['intention']     = $intention->intention;
                    $return[$intention->intention_id]['users']         = 0;
                    $return[$intention->intention_id]['user_id']       = $intention->user_id;
                    $return[$intention->intention_id]['isMyIntention'] = false;
                }

                if ($intention->user_id) {
                    $return[$intention->intention_id]['users']++;
                }

                if ($user && $user->id == $intention->user_id) {
                    $return[$intention->intention_id]['isMyIntention'] = true;
                }
            }
        }

        return ViewFacade::make('intentions.index', [
            'intentions' => $return,
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
        $data   = $request->validated();
        $userId = Auth::user()->id;

        if ($data['is_prayer'] == 0) {
            return IntentionUser::where('user_id', $userId)
                ->where('intention_id', $data['intention_id'])
                ->delete();
        }

        return IntentionUser::create(['intention_id' => $data['intention_id'], 'user_id' => $userId]);
    }
}

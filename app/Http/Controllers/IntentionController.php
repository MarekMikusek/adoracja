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
        $user       = Auth::user();
        $intentions = DB::table('intentions_users as iu')
            ->select('i.id as intention_id', 'i.intention', 'iu.user_id')
            ->join('intentions as i', 'iu.intention_id', '=', 'i.id', 'full')
            ->orderBy('i.created_at', 'DESC')
            ->get();

        $return = [];

        foreach ($intentions as $intention) {

            if (! isset($return[$intention->intention_id])) {
                $return[$intention->intention_id]                  = [];
                $return[$intention->intention_id]['intention']     = $intention->intention;
                $return[$intention->intention_id]['users']         = 0;
                $return[$intention->intention_id]['isMyIntention'] = false;
            }

            if($intention->user_id){
                $return[$intention->intention_id]['users']++;
            }

            if ($user && $user->id == $intention->user_id) {
                $return[$intention->intention_id]['isMyIntention'] = true;
            }
        }
        return ViewFacade::make('intentions.index', [
            'intentions' => $return,
        ]);
    }

    public function save(SaveIntentionRequest $request)
    {
        return Intention::create(['intention' => $request->validated()['intention']]);
    }

    public function isPrayer(IsPrayerRequest $request)
    {
        $data = $request->validated();
        $userId = Auth::user()->id;

        if ($data['is_prayer'] == 0) {
            return IntentionUser::where('user_id', $userId)
                ->where('intention_id', $data['intention_id'])
                ->delete();
        }

        return IntentionUser::create(['intention_id' => $data['intention_id'], 'user_id' => $userId]);
    }
}

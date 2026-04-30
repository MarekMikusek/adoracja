<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmIntentionRequest;
use App\Http\Requests\RemoveIntentionRequest;
use App\Models\Intention;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminIntensionController extends Controller
{
    public function index()
    {
        return view('admin.intentions.index', ['intentions' => Intention::orderBy('id', "DESC")->get() ?? []]);
    }

    public function confirm(ConfirmIntentionRequest $request)
    {
        $intention = Intention::find($request->validated()['intention']);
        $intention->user_id = Auth::user()->id;
        $intention->is_confirmed = 1;

        return $intention->save();
    }

    public function remove(RemoveIntentionRequest $request)
    {
        $data = $request->validated();

        DB::table('intentions_users')->where('intention_id', $data['intention'])->delete();

        DB::table('intentions')->where('id', $data['intention'])->delete();
    }
}

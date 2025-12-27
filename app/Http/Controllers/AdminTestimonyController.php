<?php
namespace App\Http\Controllers;

use App\Http\Requests\ConfirmTestimonyRequest;
use App\Http\Requests\RemoveTestimonyRequest;
use App\Models\Testimony;

use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AdminTestimonyController extends Controller
{
    public function index()
    {
        $testimonies = Testimony::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.testimonies.index', compact('testimonies'));
    }

    public function create()
    {
        return view('admin.testimonies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nickname'  => 'required|string',
            'testimony' => 'required|string|max:1000',
        ]);

        Testimony::create($request->all());

        return redirect()->route('admin.testimonies.index')
            ->with('success', 'Opinia dodana pomyślnie.');
    }

    public function show(Testimony $testimony)
    {
        return view('admin.testimonies.show', compact('testimony'));
    }

    public function confirm(ConfirmTestimonyRequest $request)
    {
        $testimony = Testimony::find($request->validated()['testimony_id']);
        $testimony['is_confirmed'] = 1;

        return $testimony->save();
    }

    public function destroy(RemoveTestimonyRequest $request)
    {
        Testimony::findOrFail($request->validated()['testimony_id'])->delete();

        return redirect()->route('admin.testimonies.index');
    }
}

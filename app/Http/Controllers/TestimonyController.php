<?php
namespace App\Http\Controllers;

use App\Models\Testimony;

use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;

class TestimonyController extends Controller
{
    public function index()
    {
        $testimonies = Testimony::orderBy('created_at', 'desc')->where('is_confirmed', 1)->paginate(20);
        return view('testimonies.index', compact('testimonies'));
    }

    public function create()
    {
        return view('testimonies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nickname'  => 'required|string',
            'testimony' => 'required|string|max:1000',
        ]);

        Testimony::create($request->all());

        return redirect()->route('testimonies.index')
            ->with('success', 'Opinia dodana pomyślnie.');
    }

    public function show(Testimony $testimony)
    {
        return view('testimonies.show', compact('testimony'));
    }
}

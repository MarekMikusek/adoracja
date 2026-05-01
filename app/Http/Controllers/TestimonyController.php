<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Testimonies\CreateTestimonyAction;
use App\Http\Requests\StoreTestimonyRequest;
use App\Models\Testimony;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TestimonyController extends Controller
{
    public function index(): View
    {
        $testimonies = Testimony::query()
            ->confirmed()
            ->recent()
            ->paginate(20);

        return view('testimonies.index', compact('testimonies'));
    }

    public function create(): View
    {
        return view('testimonies.create');
    }

    public function store(
        StoreTestimonyRequest $request,
        CreateTestimonyAction $action
    ): RedirectResponse {
        $action->execute($request->validated());

        return redirect()
            ->route('testimonies.index')
            ->with('success', 'Opinia dodana pomyślnie i oczekuje na moderację.');
    }

    public function show(Testimony $testimony): View
    {
        return view('testimonies.show', compact('testimony'));
    }
}

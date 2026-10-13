<?php

namespace App\Http\Controllers;

use App\Models\LoveLanguage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama tidak boleh lebih dari :max karakter.',
        ]);

        $request->session()->put('name', $validated['name']);

        return redirect()->to('/assessment');
    }

    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('name')) {
            return redirect()->to('/');
        }

        $loveLanguages = LoveLanguage::with(['statements' => fn ($query) => $query->orderBy('id')])
            ->orderBy('id')
            ->get();

        return view('assessment', [
            'loveLanguages' => $loveLanguages,
            'name' => $request->session()->get('name'),
        ]);
    }
}

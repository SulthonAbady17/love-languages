<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\LoveLanguage;
use App\Models\Result;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function store(Request $request): RedirectResponse
    {
        if (! $request->session()->has('name')) {
            return redirect()->to('/');
        }

        $statements = Statement::orderBy('id')->get();

        $rules = [
            'answers' => ['required', 'array', 'size:'.$statements->count()],
        ];
        foreach ($statements as $statement) {
            $rules['answers.'.$statement->id] = ['required', 'integer', 'min:1', 'max:5'];
        }

        $validated = $request->validate($rules, [
            'answers.required' => 'Semua pernyataan wajib dijawab.',
            'answers.size' => 'Semua pernyataan wajib dijawab.',
            'answers.*.required' => 'Semua pernyataan wajib dijawab.',
            'answers.*.integer' => 'Jawaban harus berupa angka.',
            'answers.*.min' => 'Jawaban harus antara 1 dan 5.',
            'answers.*.max' => 'Jawaban harus antara 1 dan 5.',
        ]);

        $user = DB::transaction(function () use ($request, $statements, $validated): User {
            $user = User::create([
                'name' => $request->session()->get('name'),
                'token' => (string) Str::uuid(),
            ]);

            foreach ($statements as $statement) {
                Answer::create([
                    'user_id' => $user->id,
                    'statement_id' => $statement->id,
                    'value' => (int) $validated['answers'][$statement->id],
                ]);
            }

            foreach ($statements->groupBy('love_language_id') as $loveLanguageId => $languageStatements) {
                $sum = $languageStatements->sum(
                    fn (Statement $statement) => (int) $validated['answers'][$statement->id],
                );

                Result::create([
                    'user_id' => $user->id,
                    'love_language_id' => (int) $loveLanguageId,
                    'value' => $sum / $languageStatements->count(),
                ]);
            }

            return $user;
        });

        return redirect()->to('/results/'.$user->token);
    }
}

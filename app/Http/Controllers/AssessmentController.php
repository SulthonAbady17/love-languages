<?php

namespace App\Http\Controllers;

use App\Ai\Agents\AdviceAgent;
use App\Models\Answer;
use App\Models\LoveLanguage;
use App\Models\Result;
use App\Models\Statement;
use App\Models\User;
use App\Support\Token;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

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

        $user = $this->persistAssessment($request, $statements, $validated);

        $this->generateAdvice($user);

        return redirect()->to('/results/'.$user->token);
    }

    /**
     * Create the Participant with their Likert Responses and Results.
     *
     * The whole transaction is retried on the astronomically-unlikely Result
     * Token unique-index collision, up to 5 attempts, rethrowing the last miss.
     *
     * @param  array{answers: array<int, int>}  $validated
     */
    private function persistAssessment(Request $request, EloquentCollection $statements, array $validated): User
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return DB::transaction(function () use ($request, $statements, $validated): User {
                    $user = User::create([
                        'name' => $request->session()->get('name'),
                        'token' => Token::generate(),
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
            } catch (UniqueConstraintViolationException $exception) {
                if ($attempt >= 5) {
                    throw $exception;
                }
            }
        }
    }

    /**
     * Generate Advice for the top Love Language and store it on the Participant.
     *
     * A provider failure must never lose an Assessment, so exceptions are logged and swallowed.
     */
    private function generateAdvice(User $user): void
    {
        try {
            $results = $user->rankedResults()->get();

            if ($results->isEmpty()) {
                return;
            }

            $user->update(['advice' => (new AdviceAgent($results))->generate()]);
        } catch (Throwable $exception) {
            Log::error('Advice generation failed.', ['exception' => $exception]);
        }
    }
}

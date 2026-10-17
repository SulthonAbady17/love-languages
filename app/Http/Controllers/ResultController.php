<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function show(string $token): View
    {
        $user = User::where('token', $token)->firstOrFail();

        return view('results', [
            'user' => $user,
            'results' => $user->rankedResults()->get(),
            'feedback' => $user->feedback,
        ]);
    }

    public function storeFeedback(Request $request, string $token): RedirectResponse
    {
        $user = User::where('token', $token)->firstOrFail();

        if ($user->feedback()->exists()) {
            return redirect()->route('results.show', $user->token);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ], [
            'message.required' => 'Pesan wajib diisi.',
            'message.string' => 'Pesan harus berupa teks.',
            'message.max' => 'Pesan tidak boleh lebih dari :max karakter.',
        ]);

        try {
            $user->feedback()->create($validated);
        } catch (UniqueConstraintViolationException) {
            // A concurrent resubmit already created the row; the unique
            // user_id constraint is the one-per-Participant backstop.
        }

        return redirect()->route('results.show', $user->token);
    }
}

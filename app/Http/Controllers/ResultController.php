<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class ResultController extends Controller
{
    public function show(string $token): View
    {
        $user = User::where('token', $token)->firstOrFail();

        return view('results', [
            'user' => $user,
            'results' => $user->results()->with('loveLanguage')->orderByDesc('value')->get(),
        ]);
    }
}

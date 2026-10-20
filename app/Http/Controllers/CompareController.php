<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    private const UNKNOWN_TOKEN_ERROR = 'Result Token tidak ditemukan. Periksa kembali token pasanganmu.';

    private const SELF_COMPARE_ERROR = 'Kamu tidak bisa membandingkan dengan dirimu sendiri.';

    public function start(Request $request, string $tokenA): RedirectResponse
    {
        $tokenB = $request->query('tokenB');
        $tokenB = is_string($tokenB) ? trim($tokenB) : '';

        User::where('token', $tokenA)->firstOrFail();

        if ($response = $this->invalidPartnerResponse($tokenA, $tokenB)) {
            return $response;
        }

        return redirect()->route('compare.show', [$tokenA, $tokenB]);
    }

    public function show(string $tokenA, string $tokenB): View|RedirectResponse
    {
        $initiator = User::where('token', $tokenA)->firstOrFail();

        if ($response = $this->invalidPartnerResponse($tokenA, $tokenB)) {
            return $response;
        }

        $initiator->load('rankedResults');
        $partner = User::where('token', $tokenB)->firstOrFail();
        $partner->load('rankedResults');

        return view('compare', [
            'initiator' => $initiator,
            'partner' => $partner,
        ]);
    }

    private function invalidPartnerResponse(string $tokenA, string $tokenB): ?RedirectResponse
    {
        if ($tokenB === $tokenA) {
            return redirect()
                ->route('results.show', $tokenA)
                ->with('error', self::SELF_COMPARE_ERROR);
        }

        if (! User::where('token', $tokenB)->exists()) {
            return redirect()
                ->route('results.show', $tokenA)
                ->with('error', self::UNKNOWN_TOKEN_ERROR);
        }

        return null;
    }
}

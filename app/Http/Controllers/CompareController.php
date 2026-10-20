<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    private const UNKNOWN_PARTNER_ERROR = 'Tautan hasil pasangan tidak ditemukan. Periksa kembali tautannya.';

    private const SELF_COMPARE_ERROR = 'Kamu tidak bisa membandingkan dengan dirimu sendiri.';

    public function start(Request $request, string $tokenA): RedirectResponse
    {
        $partnerReference = $request->query('tokenB');
        $tokenB = is_string($partnerReference) ? $this->partnerTokenFrom($partnerReference) : '';

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
                ->with('error', self::UNKNOWN_PARTNER_ERROR);
        }

        return null;
    }

    /**
     * Extract the Result Token from a pasted Result Permalink, falling back to a bare token.
     */
    private function partnerTokenFrom(string $reference): string
    {
        $reference = trim($reference);
        $path = parse_url($reference, PHP_URL_PATH);

        if (is_string($path) && preg_match('~/results/([a-z0-9]{16})/?$~', $path, $matches) === 1) {
            return $matches[1];
        }

        return preg_match('/^[a-z0-9]{16}$/', $reference) === 1 ? $reference : '';
    }
}

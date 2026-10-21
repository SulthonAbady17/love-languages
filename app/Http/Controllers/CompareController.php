<?php

namespace App\Http\Controllers;

use App\Models\Comparison;
use App\Models\User;
use App\Support\Token;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    private const UNKNOWN_PARTNER_ERROR = 'Tautan hasil pasangan tidak ditemukan. Periksa kembali tautannya.';

    private const SELF_COMPARE_ERROR = 'Kamu tidak bisa membandingkan dengan dirimu sendiri.';

    public function start(Request $request, string $token): RedirectResponse
    {
        $initiator = User::where('token', $token)->firstOrFail();

        $partnerReference = $request->input('tokenB');
        $partnerToken = is_string($partnerReference) ? $this->partnerTokenFrom($partnerReference) : '';
        $partner = $partnerToken === '' ? null : User::where('token', $partnerToken)->first();

        if ($partner?->is($initiator)) {
            return $this->backWithError($initiator, self::SELF_COMPARE_ERROR);
        }

        if ($partner === null) {
            return $this->backWithError($initiator, self::UNKNOWN_PARTNER_ERROR);
        }

        $comparison = Comparison::firstOrCreate(
            ['initiator_id' => $initiator->id, 'partner_id' => $partner->id],
            ['token' => Token::generate()],
        );

        return redirect()->route('compare.show', $comparison->token);
    }

    public function show(string $token): View
    {
        $comparison = Comparison::where('token', $token)->firstOrFail();
        $comparison->load(['initiator.rankedResults', 'partner.rankedResults']);

        return view('compare', [
            'comparison' => $comparison,
            'initiator' => $comparison->initiator,
            'partner' => $comparison->partner,
        ]);
    }

    private function backWithError(User $initiator, string $error): RedirectResponse
    {
        return redirect()
            ->route('results.show', $initiator->token)
            ->with('error', $error);
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

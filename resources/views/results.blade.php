@extends('layouts.app')

@section('content')
@php($top = $results->first())
<main class="max-w-3xl mx-auto px-4 py-8 sm:py-12 space-y-8">
    <header class="border-b border-line pb-6 space-y-3">
        <p class="font-sans text-xs text-muted">
            Peserta: {{ $user->name }}
        </p>
        <h1 class="font-sans font-medium text-2xl sm:text-4xl md:text-5xl text-navy leading-tight tracking-[-0.01em]">
            Hasil Penilaian Bahasa Cinta Anda
        </h1>
    </header>

    {{-- Top Love Language, navy hero --}}
    <section id="top-love-language" class="bg-navy text-cream rounded-lg p-6 sm:p-10 space-y-3">
        <p class="font-sans font-medium text-xs uppercase tracking-[0.15em] text-terracotta">
            Bahasa Cinta Utama
        </p>
        <h2 class="font-editorial italic text-3xl sm:text-5xl md:text-[56px] leading-[1.15]">
            {{ $top?->loveLanguage?->name }}
        </h2>
        <p class="font-body text-base leading-relaxed text-cream/90">
            {{ $top?->loveLanguage?->description }}
        </p>
    </section>

    @if($user->advice)
        {{-- Advice for the highlighted top Love Language --}}
        <section id="advice" class="bg-white border border-line rounded-lg p-4 sm:p-6 space-y-3">
            <p class="font-sans font-medium text-xs uppercase tracking-[0.15em] text-terracotta">
                Saran
            </p>
            <p class="font-body text-base leading-relaxed text-navy">
                {!! nl2br(e($user->advice)) !!}
            </p>
        </section>
    @endif

    {{-- All 5 Love Languages, ranked --}}
    <section class="space-y-4">
        <h2 class="font-sans font-medium text-xl sm:text-2xl text-navy leading-tight">
            Peringkat Bahasa Cinta
        </h2>

        @foreach($results as $result)
            <article class="bg-white border border-line rounded-lg p-4 sm:p-6 space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center px-2 py-0.5 bg-navy text-cream font-sans font-medium text-xs rounded-sm shrink-0">
                            {{ $loop->iteration }}
                        </span>
                        <h3 class="font-sans font-medium text-lg sm:text-xl text-navy leading-tight">
                            {{ $result->loveLanguage->name }}
                        </h3>
                    </div>
                    <span class="font-body text-sm text-muted shrink-0">
                        {{ number_format($result->value, 1) }}
                    </span>
                </div>
                <p class="font-body text-sm sm:text-base text-muted leading-relaxed">
                    {{ $result->loveLanguage->description }}
                </p>
            </article>
        @endforeach
    </section>

    {{-- Permanent link note --}}
    <aside
        id="permanent-link"
        x-data="{
            copied: false,
            linkUrl: @js(route('results.show', $user->token)),
            async copyLink() {
                let success = false;
                try {
                    await navigator.clipboard.writeText(this.linkUrl);
                    success = true;
                } catch {
                    // navigator.clipboard needs a secure context; fall back for http://*.test
                    const textarea = document.createElement('textarea');
                    textarea.value = this.linkUrl;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();
                    success = document.execCommand('copy');
                    textarea.remove();
                }
                if (success) {
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2000);
                }
            }
        }"
        class="bg-cream border border-line rounded-lg p-4 sm:p-6 space-y-2"
    >
        <p class="font-body text-sm text-muted">
            Simpan tautan ini untuk melihat hasilmu kembali
        </p>

        {{-- Permalink --}}
        <div class="flex flex-wrap items-center gap-3">
            <p class="font-mono text-sm text-navy truncate flex-1 min-w-0">
                {{ route('results.show', $user->token) }}
            </p>
            <button
                type="button"
                @click="copyLink()"
                aria-label="Salin Tautan"
                title="Salin Tautan"
                class="min-h-[44px] min-w-[44px] p-2.5 shrink-0 bg-navy text-cream rounded-sm border border-line hover:opacity-90 transition-opacity cursor-pointer inline-flex items-center justify-center"
            >
                <svg x-show="!copied" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="9" y="9" width="12" height="12" />
                    <path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1" />
                </svg>
                <svg x-show="copied" x-cloak class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 12.5l5 5L20 6" />
                </svg>
            </button>
            <span class="sr-only" aria-live="polite" x-text="copied ? 'Tersalin' : ''"></span>
        </div>
    </aside>

    {{-- Compare with Partner --}}
    <section id="compare" class="bg-white border border-line rounded-lg p-4 sm:p-6 space-y-3">
        <p class="font-sans font-medium text-xs uppercase tracking-[0.15em] text-terracotta">
            Bandingkan dengan Pasangan
        </p>
        <p class="font-body text-base leading-relaxed text-muted">
            Masukkan Result Token pasanganmu untuk melihat profil kalian berdampingan.
        </p>
        @if (session('error'))
            <p class="font-sans text-xs text-error">
                {{ session('error') }}
            </p>
        @endif
        <form method="GET" action="{{ route('compare.start', $user->token) }}" class="space-y-3">
            <div class="space-y-1.5">
                <label for="partner-token" class="font-sans font-medium text-sm text-navy">
                    Result Token
                </label>
                <input
                    id="partner-token"
                    name="tokenB"
                    type="text"
                    autocomplete="off"
                    required
                    class="w-full min-h-[44px] px-3.5 py-3 bg-white text-navy font-sans text-base rounded-sm border border-line focus:border-navy focus:ring-navy focus:ring-1 focus:outline-none transition-colors"
                >
            </div>
            <button
                type="submit"
                class="w-full sm:w-auto min-h-[44px] px-8 py-3 bg-navy text-cream font-sans font-medium text-base rounded-sm border border-line hover:opacity-90 transition-opacity cursor-pointer flex sm:inline-flex items-center justify-center"
            >
                BANDINGKAN
            </button>
        </form>
    </section>

    @if ($feedback)
        <section id="feedback" class="bg-white border border-line rounded-lg p-4 sm:p-6 space-y-3">
            <p class="font-body text-base leading-relaxed text-navy">
                Terima kasih sudah mencoba aplikasi ini dan sudah memberikan feedback!
            </p>
        </section>
    @else
        <section id="feedback" class="bg-white border border-line rounded-lg p-4 sm:p-6 space-y-3">
            <p class="font-sans font-medium text-xs uppercase tracking-[0.15em] text-terracotta">
                Feedback
            </p>
            <p class="font-body text-base leading-relaxed text-muted">
                Sudah coba aplikasi ini? Ceritakan pengembang apa yang kamu pikirkan.
            </p>
            <form method="POST" action="{{ route('results.feedback', $user->token) }}" class="space-y-3">
                @csrf
                <label for="message" class="sr-only">Pesan Feedback</label>
                <textarea
                    id="message"
                    name="message"
                    rows="4"
                    class="w-full min-h-[44px] px-3.5 py-3 bg-white text-navy font-sans text-base rounded-sm border @error('message') border-error focus:border-error focus:ring-error @else border-line focus:border-navy focus:ring-navy @enderror focus:ring-1 focus:outline-none transition-colors"
                >{{ old('message') }}</textarea>
                @error('message')
                    <p class="font-sans text-xs text-error mt-1.5">
                        {{ $message }}
                    </p>
                @enderror
                <button
                    type="submit"
                    class="w-full sm:w-auto min-h-[44px] px-8 py-3 bg-navy text-cream font-sans font-medium text-base rounded-sm border border-line hover:opacity-90 transition-opacity cursor-pointer flex sm:inline-flex items-center justify-center"
                >
                    KIRIM FEEDBACK
                </button>
            </form>
        </section>
    @endif
</main>
@endsection

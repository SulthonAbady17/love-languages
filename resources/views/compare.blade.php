@extends('layouts.app')

@section('content')
<main class="max-w-3xl mx-auto px-4 py-8 sm:py-12 space-y-6">
    <a href="{{ route('results.show', $initiator->token) }}" class="inline-flex items-center gap-1.5 font-sans text-sm text-muted hover:text-navy transition-colors">
        <span aria-hidden="true">&larr;</span>
        Kembali ke Hasil
    </a>

    <header class="border-b border-line pb-6 space-y-3">
        <p class="font-sans font-medium text-xs uppercase tracking-[0.15em] text-terracotta">
            Perbandingan
        </p>
        <h1 class="font-sans font-medium text-2xl sm:text-4xl text-navy leading-tight tracking-[-0.01em]">
            Profil Bahasa Cinta Kalian
        </h1>
    </header>

    <x-copy-link
        id="comparison-permalink"
        :url="route('compare.show', $comparison->token)"
        message="Simpan tautan ini untuk melihat perbandingan kalian kembali"
    />

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6">
        @foreach([$initiator, $partner] as $participant)
            <article data-participant="{{ $participant->token }}" class="bg-white border border-line rounded-lg overflow-hidden">
                <header class="px-4 sm:px-6 py-4 border-b border-line space-y-1">
                    <p class="font-sans font-medium text-xs uppercase tracking-[0.15em] text-muted">
                        Peserta
                    </p>
                    <h2 class="font-sans font-medium text-xl sm:text-2xl text-navy leading-tight">
                        {{ $participant->name }}
                    </h2>
                </header>

                <ol class="px-4 sm:px-6 py-4 sm:py-5 space-y-4">
                    @foreach($participant->rankedResults as $result)
                        <li class="space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 bg-navy text-cream font-sans font-medium text-xs rounded-sm shrink-0">{{ $loop->iteration }}</span>
                                    <h3 class="font-sans font-medium text-base sm:text-lg text-navy leading-tight">
                                        {{ $result->loveLanguage->name }}
                                    </h3>
                                </div>
                                <span class="font-body text-sm text-muted shrink-0">
                                    {{ number_format($result->value, 1) }}/5.0
                                </span>
                            </div>

                            <div
                                class="h-2 w-full bg-line rounded-full overflow-hidden"
                                role="progressbar"
                                aria-valuenow="{{ number_format($result->value, 1) }}"
                                aria-valuemin="0"
                                aria-valuemax="5"
                                aria-label="{{ $result->loveLanguage->name }}"
                            >
                                <div class="h-full bg-terracotta rounded-full" style="width: {{ $result->value / 5 * 100 }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </article>
        @endforeach
    </section>

    @if($comparison->advice)
        {{-- Ringkasan & Saran: the Comparison Advice for both profiles --}}
        <section id="comparison-advice" class="bg-white border border-line rounded-lg p-4 sm:p-6 space-y-3">
            <p class="font-sans font-medium text-xs uppercase tracking-[0.15em] text-terracotta">
                Ringkasan &amp; Saran
            </p>
            <p class="font-body text-base leading-relaxed text-navy">
                {!! nl2br(e($comparison->advice)) !!}
            </p>
        </section>
    @endif
</main>
@endsection

@extends('layouts.app')

@section('content')
@php($top = $results->first())
<main class="min-h-screen p-6 md:p-12 max-w-4xl mx-auto space-y-8">
    <header class="border-b-[5px] border-black pb-6 space-y-3">
        <span class="font-mono text-xs uppercase tracking-wider text-black">
            Peserta: {{ $user->name }}
        </span>
        <h1 class="font-headline text-3xl md:text-5xl uppercase leading-none text-black">
            Hasil Penilaian Bahasa Cinta Anda
        </h1>
    </header>

    {{-- Top Love Language, inverted block --}}
    <section id="top-love-language" class="bg-black text-white border-[3px] border-black p-6 space-y-3">
        <p class="font-mono text-xs uppercase tracking-widest">
            Bahasa Cinta Utama
        </p>
        <h2 class="font-headline text-4xl md:text-6xl uppercase leading-none">
            {{ $top?->loveLanguage?->name }}
        </h2>
        <p class="font-body text-sm leading-relaxed">
            {{ $top?->loveLanguage?->description }}
        </p>
    </section>

    {{-- All 5 Love Languages, ranked --}}
    <section class="space-y-4">
        <h2 class="font-headline text-2xl uppercase leading-tight text-black">
            Peringkat Bahasa Cinta
        </h2>

        @foreach($results as $result)
            <article class="border-[3px] border-black bg-white p-4 space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-xs font-bold border-[2px] border-black px-2 py-0.5 bg-black text-white shrink-0">
                            {{ $loop->iteration }}
                        </span>
                        <h3 class="font-headline text-xl uppercase leading-none text-black">
                            {{ $result->loveLanguage->name }}
                        </h3>
                    </div>
                    <span class="font-mono text-sm border-[2px] border-black px-2 py-0.5 text-black shrink-0">
                        {{ number_format($result->value, 1) }}
                    </span>
                </div>
                <p class="font-body text-sm text-black leading-relaxed">
                    {{ $result->loveLanguage->description }}
                </p>
            </article>
        @endforeach
    </section>

    {{-- Permanent link note --}}
    <aside class="border-[3px] border-black bg-[#F0F0F0] p-4 space-y-2">
        <p class="font-body text-sm text-black">
            Simpan tautan ini untuk melihat hasilmu kembali
        </p>
        <p class="font-mono text-sm text-black break-all">
            {{ route('results.show', $user->token) }}
        </p>
    </aside>
</main>
@endsection

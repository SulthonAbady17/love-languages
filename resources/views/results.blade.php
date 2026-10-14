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

    @if($user->advice)
        {{-- Advice for the highlighted top Love Language --}}
        <section id="advice" class="border-[3px] border-black bg-white p-6 space-y-3">
            <p class="font-mono text-xs uppercase tracking-widest">
                Saran
            </p>
            <p class="font-body text-sm leading-relaxed text-black">
                {!! nl2br(e($user->advice)) !!}
            </p>
        </section>
    @endif

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
        class="flex flex-col gap-3"
    >
        <h3 class="font-body text-sm font-semibold text-black">
            Simpan tautan ini untuk melihat hasilmu kembali
        </h3>
        <div class="relative border-[3px] border-black bg-[#F0F0F0]">
            <div class="flex items-center justify-between">
                <code class="grow p-4 font-mono text-sm text-black break-all">
                    {{ route('results.show', $user->token) }}
                </code>
                <button
                    x-on:click="copyLink()"
                    type="button"
                    class="mx-2 flex flex-none items-center justify-center p-2 text-black hover:text-blue active:text-black transition-none cursor-pointer"
                >
                    <span class="relative inline-block size-5">
                        <svg
                            x-cloak
                            x-show="copied"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-200"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-200"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="absolute inset-0 inline-block size-5 text-success"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <svg
                            x-cloak
                            x-show="!copied"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-0"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-0"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="absolute inset-0 inline-block size-5"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M15.988 3.012A2.25 2.25 0 0 1 18 5.25v6.5A2.25 2.25 0 0 1 15.75 14H13.5V7A2.5 2.5 0 0 0 11 4.5H8.128a2.252 2.252 0 0 1-1.884-1.488A2.25 2.25 0 0 1 12.25 1h1.5a2.25 2.25 0 0 1 2.238 2.012ZM11.5 3.25a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75v.25h-3v-.25Z"
                                clip-rule="evenodd"
                            />
                            <path
                                fill-rule="evenodd"
                                d="M2 7a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7Zm2 3.25a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1-.75-.75Zm0 3.5a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1-.75-.75Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </span>
                    <span class="sr-only">Salin Tautan</span>
                </button>
            </div>
        </div>
    </aside>
</main>
@endsection

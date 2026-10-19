@extends('layouts.app')

@section('content')
<main class="max-w-3xl mx-auto px-4 py-8 sm:py-12 space-y-8">
    <header class="border-b border-line pb-8 space-y-3">
        <p class="font-sans font-medium text-xs uppercase tracking-[0.15em] text-terracotta">
            Penilaian Relasi
        </p>
        <h1 class="font-sans font-medium text-3xl sm:text-5xl md:text-[64px] text-navy leading-tight sm:leading-[1.05] tracking-[-0.02em]">
            Penilaian Bahasa Cinta
        </h1>
        <p class="font-body text-base leading-relaxed text-muted">
            Temukan bagaimana Anda mengekspresikan dan menerima kasih sayang. Penilaian ini terdiri dari 25 pernyataan yang terbagi ke dalam 5 bahasa cinta. Jawablah setiap pernyataan dengan jujur sesuai diri Anda untuk mendapatkan profil bahasa cinta pribadi Anda.
        </p>
    </header>

    <section class="bg-white border border-line rounded-lg p-6 sm:p-8">
        <form method="POST" action="{{ route('assessment.start') }}" class="space-y-6">
            @csrf

            <div class="space-y-1.5">
                <label for="name" class="block font-sans font-medium text-sm text-navy mb-1">
                    Nama Anda
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Masukkan nama Anda..."
                    maxlength="100"
                    class="w-full min-h-[44px] px-3.5 py-3 bg-cream text-navy font-sans text-base rounded-sm border @error('name') border-error focus:border-error focus:ring-error @else border-line focus:border-navy focus:ring-navy @enderror focus:ring-1 focus:outline-none transition-colors"
                    autofocus
                >
                @error('name')
                    <p class="font-sans text-xs text-error mt-1.5">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full sm:w-auto min-h-[44px] px-8 py-3 bg-navy text-cream font-sans font-medium text-base rounded-sm border border-line hover:opacity-90 transition-opacity cursor-pointer flex sm:inline-flex items-center justify-center"
            >
                Mulai Penilaian
            </button>
        </form>
    </section>
</main>
@endsection

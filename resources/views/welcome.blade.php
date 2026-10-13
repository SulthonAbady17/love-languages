@extends('layouts.app')

@section('content')
<main class="min-h-screen p-6 md:p-12 max-w-3xl mx-auto flex flex-col justify-center space-y-8">
    <header class="border-b-[5px] border-black pb-8 space-y-4">
        <h1 class="font-headline text-4xl md:text-[64px] uppercase leading-none text-black">
            Penilaian Bahasa Cinta
        </h1>
        <p class="font-body text-base leading-relaxed text-black">
            Temukan bagaimana Anda mengekspresikan dan menerima kasih sayang. Penilaian ini terdiri dari 25 pernyataan yang terbagi ke dalam 5 bahasa cinta. Jawablah setiap pernyataan dengan jujur sesuai diri Anda untuk mendapatkan profil bahasa cinta pribadi Anda.
        </p>
    </header>

    <section class="border-[3px] border-black p-6 bg-white">
        <form method="POST" action="{{ route('assessment.start') }}" class="space-y-6">
            @csrf

            <div class="space-y-1">
                <label for="name" class="block font-headline text-sm uppercase text-black mb-1">
                    Nama Anda
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Masukkan nama Anda..."
                    maxlength="100"
                    class="w-full bg-[#F0F0F0] text-black font-mono text-[15px] px-3 py-2.5 border-[3px] @error('name') border-error @else border-black hover:bg-[#E8E8E8] @enderror focus:border-[5px] focus:outline-none transition-none"
                    autofocus
                >
                @error('name')
                    <p class="font-body text-xs text-error mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button
                type="submit"
                class="inline-block px-10 py-4 bg-black text-white font-headline text-[18px] uppercase tracking-[2px] border-[3px] border-black hover:bg-white hover:text-black active:bg-black active:text-white active:border-[5px] transition-none cursor-pointer"
            >
                Mulai Penilaian
            </button>
        </form>
    </section>
</main>
@endsection

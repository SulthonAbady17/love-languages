@extends('layouts.app')

@section('content')
<main class="min-h-screen p-8 max-w-5xl mx-auto space-y-12">
    <header class="border-b-[5px] border-black pb-8">
        <h1 class="font-headline text-[64px] leading-none uppercase">RawBlock Design Shell</h1>
        <p class="font-body text-base mt-4 text-black">
            Anti-design brutalist foundation for the Love Language Assessment. No rounded corners, no shadows, pure structure.
        </p>
    </header>

    <section class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="border-[3px] border-black p-6 bg-white space-y-4">
            <h2 class="font-headline text-[32px] leading-[1.1] uppercase">Typography</h2>
            <p class="font-headline text-xl">Archivo Black</p>
            <p class="font-body text-base">Work Sans Body 16px</p>
            <p class="font-mono text-sm">Space Mono 15px</p>
        </div>

        <div class="border-[3px] border-black p-6 bg-white space-y-4">
            <h2 class="font-headline text-[32px] leading-[1.1] uppercase">Palette</h2>
            <div class="grid grid-cols-2 gap-2 font-mono text-xs uppercase">
                <div class="bg-black text-white p-2 text-center border border-black">Black</div>
                <div class="bg-white text-black p-2 text-center border border-black">White</div>
                <div class="bg-success text-white p-2 text-center border border-black">Success</div>
                <div class="bg-warning text-black p-2 text-center border border-black">Warning</div>
                <div class="bg-error text-white p-2 text-center border border-black">Error</div>
                <div class="border border-black p-2 text-center"><a href="#links" class="text-blue underline font-bold">Blue Link</a></div>
            </div>
        </div>

        <div class="border-[3px] border-black p-6 bg-white space-y-4" x-data="{ ready: true }">
            <h2 class="font-headline text-[32px] leading-[1.1] uppercase">Alpine.js</h2>
            <p class="font-body text-sm">Status verification:</p>
            <div class="border-[2px] border-black p-3 font-mono text-xs">
                <span x-show="ready" class="text-success font-bold uppercase">[Alpine Active]</span>
                <span x-show="!ready" class="text-error font-bold uppercase">[Alpine Offline]</span>
            </div>
            <button
                type="button"
                @click="ready = !ready"
                class="px-4 py-2 bg-black text-white font-mono text-xs uppercase tracking-wider hover:bg-white hover:text-black border-[3px] border-black transition-none cursor-pointer"
            >
                Toggle State
            </button>
        </div>
    </section>

    <section id="links" class="border-[3px] border-black p-6 bg-white space-y-4">
        <h2 class="font-headline text-[48px] leading-[1.05] uppercase">Hyperlinks</h2>
        <p class="font-body text-base">
            Blue (#0000FF) is reserved strictly for hyperlinks:
            <a href="https://github.com" target="_blank" rel="noreferrer" class="text-blue underline font-mono text-sm hover:text-black">
                https://github.com
            </a>
        </p>
    </section>
</main>
@endsection

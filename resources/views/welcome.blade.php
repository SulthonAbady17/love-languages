@extends('layouts.app')

@section('content')
<main class="min-h-screen p-8 max-w-5xl mx-auto space-y-10">
    <header class="border-b-[5px] border-black pb-6">
        <h1 class="font-headline text-5xl md:text-6xl tracking-tight uppercase">RawBlock Design Shell</h1>
        <p class="font-body text-base mt-3 text-black">
            Anti-design brutalist foundation for the Love Language Assessment. No rounded corners, no shadows, pure structure.
        </p>
    </header>

    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="border-[3px] border-black p-6 bg-white space-y-2">
            <h2 class="font-headline text-2xl uppercase">Typography</h2>
            <p class="font-headline text-xl">Archivo Black 20px</p>
            <p class="font-body text-base">Work Sans Body 16px</p>
            <p class="font-mono text-sm">Space Mono 14px</p>
        </div>

        <div class="border-[3px] border-black p-6 bg-white space-y-3">
            <h2 class="font-headline text-2xl uppercase">Palette</h2>
            <div class="grid grid-cols-2 gap-2 font-mono text-xs uppercase">
                <div class="bg-black text-white p-2 text-center border border-black">Black</div>
                <div class="bg-white text-black p-2 text-center border border-black">White</div>
                <div class="bg-blue text-white p-2 text-center border border-black">Blue</div>
                <div class="bg-success text-white p-2 text-center border border-black">Success</div>
                <div class="bg-warning text-black p-2 text-center border border-black">Warning</div>
                <div class="bg-error text-white p-2 text-center border border-black">Error</div>
            </div>
        </div>

        <div class="border-[3px] border-black p-6 bg-white space-y-4" x-data="{ ready: true }">
            <h2 class="font-headline text-2xl uppercase">Alpine.js</h2>
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

    <section class="border-[3px] border-black p-6 bg-white space-y-4">
        <h2 class="font-headline text-2xl uppercase">Hyperlink Sample</h2>
        <p class="font-body text-base">
            External link test:
            <a href="https://github.com" target="_blank" rel="noreferrer" class="text-blue underline font-mono text-sm hover:text-black">
                https://github.com
            </a>
        </p>
    </section>
</main>
@endsection

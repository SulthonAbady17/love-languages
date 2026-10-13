@extends('layouts.app')

@section('content')
<main class="min-h-screen p-6 md:p-12 max-w-4xl mx-auto flex flex-col justify-between">
    <div
        x-data="{
            currentStep: 1,
            totalSteps: {{ $loveLanguages->count() }},
            participantName: @js($name),
            stepNames: {
                @foreach($loveLanguages as $index => $lang)
                    {{ $index + 1 }}: @js($lang->name),
                @endforeach
            },
            stepStatements: {
                @foreach($loveLanguages as $index => $lang)
                    {{ $index + 1 }}: [{{ $lang->statements->pluck('id')->join(',') }}],
                @endforeach
            },
            answers: {},
            isCurrentStepComplete() {
                const currentIds = this.stepStatements[this.currentStep] || [];
                if (currentIds.length === 0) return false;
                return currentIds.every(id => this.answers[id] !== undefined && this.answers[id] !== null && this.answers[id] !== '');
            },
            nextStep() {
                if (this.isCurrentStepComplete() && this.currentStep < this.totalSteps) {
                    this.currentStep++;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },
            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        }"
        class="w-full space-y-8"
    >
        {{-- Progress Header --}}
        <header class="border-b-[5px] border-black pb-6 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <span class="font-mono text-xs uppercase tracking-wider text-black">
                    Langkah <span x-text="currentStep">1</span> dari 5
                </span>
                <span class="font-mono text-xs uppercase text-black" x-text="'Peserta: ' + participantName">
                    Peserta: {{ $name }}
                </span>
            </div>

            <h1
                class="font-headline text-3xl md:text-5xl uppercase leading-none text-black"
                x-text="stepNames[currentStep]"
            >
                {{ $loveLanguages->first()?->name }}
            </h1>
        </header>

        {{-- Form --}}
        <form method="POST" action="{{ url('/assessment') }}" id="assessment-form" class="space-y-8">
            @csrf

            @foreach($loveLanguages as $langIndex => $loveLanguage)
                <section
                    x-show="currentStep === {{ $loop->iteration }}"
                    x-cloak
                    class="space-y-6"
                >
                    <div class="border-[3px] border-black p-4 bg-[#F0F0F0]">
                        <p class="font-body text-sm text-black leading-relaxed">
                            {{ $loveLanguage->description }}
                        </p>
                    </div>

                    <div class="space-y-6">
                        @foreach($loveLanguage->statements as $statementIndex => $statement)
                            <fieldset class="border-[3px] border-black p-5 bg-white space-y-4">
                                <legend class="sr-only">Pernyataan {{ $statementIndex + 1 }}</legend>

                                <div class="flex items-start gap-3">
                                    <span class="font-mono text-xs font-bold border-[2px] border-black px-2 py-0.5 bg-black text-white shrink-0">
                                        #{{ ($loop->parent->index * 5) + $loop->iteration }}
                                    </span>
                                    <p class="font-body text-base text-black font-medium leading-snug">
                                        {{ $statement->body }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-5 gap-2 pt-2 border-t-[2px] border-black">
                                    @php
                                        $options = [
                                            1 => 'Sangat Tidak Setuju',
                                            2 => 'Tidak Setuju',
                                            3 => 'Netral',
                                            4 => 'Setuju',
                                            5 => 'Sangat Setuju',
                                        ];
                                    @endphp
                                    @foreach($options as $val => $label)
                                        <label
                                            class="flex sm:flex-col items-center justify-start sm:justify-center gap-2 p-2 border-[2px] border-black cursor-pointer hover:bg-[#F0F0F0] text-center transition-none"
                                            :class="answers[{{ $statement->id }}] == {{ $val }} ? 'bg-black text-white hover:bg-black' : 'bg-white text-black'"
                                        >
                                            <input
                                                type="radio"
                                                name="answers[{{ $statement->id }}]"
                                                value="{{ $val }}"
                                                x-model="answers[{{ $statement->id }}]"
                                                class="radio-circle shrink-0"
                                            >
                                            <span class="font-body text-xs leading-tight">
                                                {{ $label }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                </section>
            @endforeach

            {{-- Navigation Buttons --}}
            <nav class="flex items-center justify-between pt-6 border-t-[3px] border-black gap-4">
                <div>
                    <button
                        type="button"
                        x-show="currentStep > 1"
                        @click="prevStep()"
                        class="px-8 py-3.5 bg-white text-black font-headline text-base uppercase tracking-[2px] border-[3px] border-black hover:bg-black hover:text-white active:border-[5px] transition-none cursor-pointer"
                    >
                        Kembali
                    </button>
                </div>

                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        x-show="currentStep < totalSteps"
                        @click="nextStep()"
                        :disabled="!isCurrentStepComplete()"
                        class="px-8 py-3.5 bg-black text-white font-headline text-base uppercase tracking-[2px] border-[3px] border-black hover:bg-white hover:text-black active:bg-black active:text-white active:border-[5px] disabled:border-[#CCCCCC] disabled:bg-[#F5F5F5] disabled:text-[#888888] disabled:cursor-not-allowed transition-none cursor-pointer"
                    >
                        Lanjut
                    </button>

                    <button
                        type="submit"
                        x-show="currentStep === totalSteps"
                        :disabled="!isCurrentStepComplete()"
                        class="px-8 py-3.5 bg-black text-white font-headline text-base uppercase tracking-[2px] border-[3px] border-black hover:bg-white hover:text-black active:bg-black active:text-white active:border-[5px] disabled:border-[#CCCCCC] disabled:bg-[#F5F5F5] disabled:text-[#888888] disabled:cursor-not-allowed transition-none cursor-pointer"
                    >
                        Kirim
                    </button>
                </div>
            </nav>
        </form>
    </div>
</main>
@endsection

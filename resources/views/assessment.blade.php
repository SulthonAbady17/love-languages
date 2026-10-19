@extends('layouts.app')

@section('content')
<main class="max-w-3xl mx-auto px-4 py-8 sm:py-12 space-y-8">
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
        <header class="border-b border-line pb-6 space-y-4">
            <div class="flex items-center justify-between gap-2">
                <span class="font-sans font-medium text-xs uppercase tracking-[0.15em] text-terracotta">
                    Langkah <span x-text="currentStep">1</span> dari 5
                </span>
                <span class="font-sans text-xs text-muted" x-text="'Peserta: ' + participantName">
                    Peserta: {{ $name }}
                </span>
            </div>

            {{-- 5-dot step indicator --}}
            <div class="flex items-center justify-center gap-3 py-1" aria-label="Indikator langkah">
                @for($step = 1; $step <= $loveLanguages->count(); $step++)
                    <div
                        class="w-2.5 h-2.5 rounded-full transition-colors"
                        :class="{
                            'bg-navy': {{ $step }} < currentStep,
                            'bg-sage': {{ $step }} === currentStep,
                            'bg-transparent border border-line': {{ $step }} > currentStep
                        }"
                    ></div>
                @endfor
            </div>

            <h1
                class="font-sans font-medium text-2xl sm:text-4xl md:text-5xl text-navy leading-tight tracking-[-0.01em]"
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
                    <div class="bg-cream border border-line rounded-lg p-4 sm:p-5">
                        <p class="font-sans text-sm sm:text-base text-muted leading-relaxed">
                            {{ $loveLanguage->description }}
                        </p>
                    </div>

                    <div class="space-y-6">
                        @foreach($loveLanguage->statements as $statementIndex => $statement)
                            <fieldset class="bg-white border border-line rounded-lg p-4 sm:p-6 space-y-4">
                                <legend class="sr-only">Pernyataan {{ $statementIndex + 1 }}</legend>

                                <div class="flex items-start gap-3">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 bg-navy text-cream font-sans font-medium text-xs rounded-sm shrink-0">
                                        #{{ ($loop->parent->index * 5) + $loop->iteration }}
                                    </span>
                                    <p class="font-sans text-base text-navy font-medium leading-snug">
                                        {{ $statement->body }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-5 gap-2.5 sm:gap-2 pt-4 border-t border-line">
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
                                            class="min-h-[44px] flex sm:flex-col items-center justify-start sm:justify-center gap-2.5 sm:gap-1.5 p-3 sm:p-2.5 rounded-sm border cursor-pointer transition-colors text-center"
                                            :class="answers[{{ $statement->id }}] == {{ $val }} ? 'bg-navy text-cream border-navy' : 'bg-white text-navy border-line hover:border-navy/40'"
                                        >
                                            <input
                                                type="radio"
                                                name="answers[{{ $statement->id }}]"
                                                value="{{ $val }}"
                                                x-model="answers[{{ $statement->id }}]"
                                                class="radio-circle shrink-0"
                                            >
                                            <span class="font-sans text-xs font-medium leading-tight">
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
            <nav class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center pt-6 border-t border-line">
                <div>
                    <button
                        type="button"
                        x-show="currentStep > 1"
                        @click="prevStep()"
                        class="w-full sm:w-auto min-h-[44px] px-8 py-3 bg-white text-navy font-sans font-medium text-base rounded-sm border border-navy hover:bg-navy hover:text-cream transition-colors cursor-pointer flex items-center justify-center"
                    >
                        Kembali
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
                    <button
                        type="button"
                        x-show="currentStep < totalSteps"
                        @click="nextStep()"
                        :disabled="!isCurrentStepComplete()"
                        class="w-full sm:w-auto min-h-[44px] px-8 py-3 bg-navy text-cream font-sans font-medium text-base rounded-sm border border-line hover:opacity-90 disabled:border-line disabled:text-muted disabled:opacity-60 disabled:cursor-not-allowed transition-all cursor-pointer flex items-center justify-center"
                    >
                        Lanjut
                    </button>

                    <button
                        type="submit"
                        x-show="currentStep === totalSteps"
                        :disabled="!isCurrentStepComplete()"
                        class="w-full sm:w-auto min-h-[44px] px-8 py-3 bg-navy text-cream font-sans font-medium text-base rounded-sm border border-line hover:opacity-90 disabled:border-line disabled:text-muted disabled:opacity-60 disabled:cursor-not-allowed transition-all cursor-pointer flex items-center justify-center"
                    >
                        Kirim
                    </button>
                </div>
            </nav>
        </form>
    </div>
</main>
@endsection

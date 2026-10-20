@extends('layouts.app')

@section('content')
<main class="max-w-3xl mx-auto px-4 py-8 sm:py-12 space-y-4">
    <h1 class="font-sans font-medium text-2xl sm:text-4xl text-navy leading-tight">
        Perbandingan
    </h1>

    <p class="font-body text-base text-navy">{{ $initiator->name }}: {{ $initiator->token }}</p>
    <p class="font-body text-base text-navy">{{ $partner->name }}: {{ $partner->token }}</p>
</main>
@endsection

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Love Languages Assessment') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&family=Newsreader:ital,wght@0,400;1,400;1,500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-navy font-body antialiased pt-16 sm:pt-20">
    <header class="fixed top-0 inset-x-0 z-50 bg-cream/95 backdrop-blur-xs border-b border-line">
        <div class="max-w-3xl mx-auto px-4 py-3.5 flex items-center">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 text-navy hover:opacity-90 transition-opacity">
                <svg class="w-8 h-8 shrink-0" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="18" cy="32" r="7" fill="#1E293B"/>
                    <circle cx="46" cy="32" r="7" fill="#C97C5D"/>
                    <path d="M25 32C31 22, 35 42, 39 32" stroke="#A8C3A0" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                </svg>
                <span class="font-sans font-medium text-sm sm:text-base tracking-[0.08em] text-navy">
                    relationship project<span class="text-terracotta">.</span>
                </span>
            </a>
        </div>
    </header>

    @yield('content')
</body>
</html>

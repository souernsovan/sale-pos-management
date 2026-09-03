<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\Setting::get('site_title', config('app.name', 'Laravel')) }}</title>
        <meta name="description" content="{{ \App\Models\Setting::get('site_description', 'Point of sale management system') }}">
        @if ($siteIcon = \App\Models\Setting::get('site_icon'))
            <link rel="icon" href="{{ '/storage/'.ltrim($siteIcon, '/') }}">
        @endif

        @include('partials.theme-boot-script')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased dark:text-gray-100">
        <div class="relative min-h-screen flex flex-col items-center justify-center gap-8 px-4 py-10 bg-gradient-to-b from-gray-50 to-gray-100 dark:bg-none dark:bg-black">
            <div class="absolute right-4 top-4">
                <x-theme-toggle class="bg-white/70 shadow-sm dark:bg-white/5" />
            </div>

            <a href="/" class="flex flex-col items-center gap-3">
                <x-application-logo class="h-14 w-auto object-contain" />
            </a>

            <div class="w-full sm:max-w-md bg-white px-6 py-8 sm:px-10 sm:py-10 shadow-xl shadow-gray-200/60 rounded-2xl ring-1 ring-gray-900/5 dark:bg-gray-900 dark:shadow-none dark:ring-white/10">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

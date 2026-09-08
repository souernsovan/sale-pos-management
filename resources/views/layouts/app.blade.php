<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\Setting::get('site_title', config('app.name', 'Laravel')) }}</title>
        <meta name="description" content="{{ \App\Models\Setting::get('site_description', 'Point of sale management system') }}">
        @if ($siteIcon = \App\Models\Setting::get('site_icon'))
            <link rel="icon" href="{{ App\Support\Uploads::url($siteIcon) }}">
        @endif

        @include('partials.theme-boot-script')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|noto-sans-khmer:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-black">
            @include('layouts.navigation')

            <div class="flex-1 flex flex-col min-w-0">
                <!-- Top Bar -->
                <header class="bg-white border-b border-gray-200 dark:bg-black dark:border-gray-800">
                    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3 min-w-0">
                            <button
                                @click="$dispatch('open-sidebar')"
                                class="md:hidden shrink-0 p-2 -ms-2 rounded-md text-gray-500 hover:bg-gray-100 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-800"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            @isset($header)
                                <div class="min-w-0">{{ $header }}</div>
                            @endisset
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <x-theme-toggle />

                            <x-dropdown align="right" width="64" contentClasses="p-2 bg-white dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                                <x-slot name="trigger">
                                    <button type="button" class="group flex items-center gap-3 rounded-xl px-2 py-1.5 text-left transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:hover:bg-gray-800 dark:focus:ring-offset-black">
                                        <span class="hidden sm:block">
                                            <span class="block text-sm font-semibold text-slate-800 dark:text-gray-200">{{ Auth::user()->name }}</span>
                                            <span class="block text-xs text-slate-500 dark:text-gray-500">{{ Auth::user()->email }}</span>
                                        </span>
                                        @if (Auth::user()->profile_photo)
                                            <img src="{{ Auth::user()->profilePhotoUrl() }}" alt="{{ Auth::user()->name }}" class="h-10 w-10 rounded-full object-cover ring-2 ring-white shadow-sm dark:ring-gray-800">
                                        @else
                                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white ring-2 ring-white shadow-sm dark:ring-gray-800">
                                                {{ str(Auth::user()->name)->substr(0, 1)->upper() }}
                                            </span>
                                        @endif
                                        <svg class="hidden h-4 w-4 text-slate-400 transition group-hover:text-slate-600 sm:block dark:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="border-b border-slate-100 px-3 py-3 dark:border-gray-800">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-gray-500">{{ __('Account') }}</p>
                                        <p class="mt-1 truncate text-sm font-semibold text-slate-800 dark:text-gray-200">{{ Auth::user()->name }}</p>
                                        <p class="truncate text-xs text-slate-500 dark:text-gray-500">{{ Auth::user()->email }}</p>
                                    </div>

                                    <x-dropdown-link :href="route('profile.edit')">
                                        <span class="flex items-center gap-3">
                                            <svg class="h-5 w-5 text-slate-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                                <circle cx="12" cy="8" r="3.25" />
                                                <path d="M5.5 20c.5-3.4 2.8-5.25 6.5-5.25s6 1.85 6.5 5.25" />
                                            </svg>
                                            <span>{{ __('My Profile') }}</span>
                                        </span>
                                    </x-dropdown-link>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')" class="text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-950"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                            <span class="flex items-center gap-3">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
                                                </svg>
                                                <span>{{ __('Log Out') }}</span>
                                            </span>
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>

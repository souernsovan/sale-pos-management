<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('New Sale') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ __('New Sale') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Build a customer order, apply payment, and complete the checkout.') }}</p>
            </div>

            @livewire('pos.terminal')
        </div>
    </div>
</x-app-layout>

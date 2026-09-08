<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Settings') }}</h2>
    </x-slot>

    <div class="min-h-full bg-slate-100 py-12 dark:bg-black">
        <div class="mx-auto w-full max-w-6xl space-y-8 px-4 sm:px-6 lg:px-8">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-slate-900 dark:text-gray-100">{{ __('Settings') }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-gray-400">{{ __('Manage your profile and account settings.') }}</p>
            </div>

            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8 dark:bg-gray-900 dark:ring-gray-800">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8 dark:bg-gray-900 dark:ring-gray-800">
                        @include('profile.partials.update-password-form')
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8 dark:bg-gray-900 dark:ring-gray-800">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-gray-100">{{ __('Language') }}</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-gray-400">{{ __('Set the interface language.') }}</p>

                        <div class="mt-5">
                            <x-language-toggle class="bg-slate-50 ring-1 ring-slate-200 dark:bg-gray-800 dark:ring-gray-700" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8 dark:bg-gray-900 dark:ring-gray-800">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>

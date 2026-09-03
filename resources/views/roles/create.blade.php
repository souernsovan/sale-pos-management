<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Create Role') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <x-status-banner />

            <div class="bg-white p-6 shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <form method="POST" action="{{ route('roles.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Role Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full max-w-md" :value="old('name')" required autofocus />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Must be unique. Reserved names (:names) cannot be used.', ['names' => implode(', ', \Database\Seeders\RolesAndPermissionsSeeder::BUILT_IN_ROLES)]) }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    @include('roles._permissions-grid')

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('roles.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('roles._permissions-grid-script')
</x-app-layout>

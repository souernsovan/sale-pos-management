<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Settings') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ __('Settings') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Update your shop information and manage staff accounts and access.') }}</p>
            </div>

            <x-status-banner />

            <div class="bg-white p-6 shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('Shop Information') }}</h3>
                <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="shop_name" :value="__('Shop Name')" />
                        <x-text-input id="shop_name" name="shop_name" type="text" class="mt-1 block w-full" :value="old('shop_name', $settings['shop_name'])" required />
                        <x-input-error class="mt-2" :messages="$errors->get('shop_name')" />
                    </div>

                    <div>
                        <x-input-label for="shop_address" :value="__('Address')" />
                        <x-text-input id="shop_address" name="shop_address" type="text" class="mt-1 block w-full" :value="old('shop_address', $settings['shop_address'])" />
                        <x-input-error class="mt-2" :messages="$errors->get('shop_address')" />
                    </div>

                    <div>
                        <x-input-label for="shop_logo" :value="__('Logo')" />
                        @if ($settings['shop_logo'])
                            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($settings['shop_logo']) }}" class="h-12 mt-2 mb-2">
                        @endif
                        <input id="shop_logo" name="shop_logo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-500/10 dark:file:text-indigo-400 dark:hover:file:bg-indigo-500/20" />
                        <x-input-error class="mt-2" :messages="$errors->get('shop_logo')" />
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-800 pt-6">
                        <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Browser Metadata') }}</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Control the browser tab title, description, and favicon for your system.') }}</p>

                        <div class="mt-5 space-y-5">
                            <div>
                                <x-input-label for="site_title" :value="__('Browser Title')" />
                                <x-text-input id="site_title" name="site_title" type="text" class="mt-1 block w-full" :value="old('site_title', $settings['site_title'])" required />
                                <x-input-error class="mt-2" :messages="$errors->get('site_title')" />
                            </div>

                            <div>
                                <x-input-label for="site_description" :value="__('Meta Description')" />
                                <textarea id="site_description" name="site_description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100">{{ old('site_description', $settings['site_description']) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('site_description')" />
                            </div>

                            <div>
                                <x-input-label for="site_icon" :value="__('Browser Icon / Favicon')" />
                                @if ($settings['site_icon'])
                                    <img src="{{ '/storage/'.ltrim($settings['site_icon'], '/') }}" alt="{{ __('Current browser icon') }}" class="mt-2 h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-800">
                                @endif
                                <input id="site_icon" name="site_icon" type="file" accept=".png,.jpg,.jpeg,.webp,.ico,image/png,image/jpeg,image/webp,image/x-icon" class="mt-2 block w-full text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-500/10 dark:file:text-indigo-400 dark:hover:file:bg-indigo-500/20" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('PNG, JPG, WebP, or ICO. Maximum 512 KB.') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('site_icon')" />
                                @if ($settings['site_icon'])
                                    <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <input type="checkbox" name="remove_site_icon" value="1" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500">
                                        {{ __('Remove current icon') }}
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="currency_symbol" :value="__('Currency Symbol')" />
                            <x-text-input id="currency_symbol" name="currency_symbol" type="text" class="mt-1 block w-full" :value="old('currency_symbol', $settings['currency_symbol'])" required />
                            <x-input-error class="mt-2" :messages="$errors->get('currency_symbol')" />
                        </div>
                        <div>
                            <x-input-label for="tax_rate" :value="__('Tax Rate (%)')" />
                            <x-text-input id="tax_rate" name="tax_rate" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" :value="old('tax_rate', $settings['tax_rate'])" required />
                            <x-input-error class="mt-2" :messages="$errors->get('tax_rate')" />
                        </div>
                        <div>
                            <x-input-label for="low_stock_threshold" :value="__('Low Stock Threshold')" />
                            <x-text-input id="low_stock_threshold" name="low_stock_threshold" type="number" min="0" class="mt-1 block w-full" :value="old('low_stock_threshold', $settings['low_stock_threshold'])" required />
                            <x-input-error class="mt-2" :messages="$errors->get('low_stock_threshold')" />
                        </div>
                    </div>

                    <x-primary-button>{{ __('Save Settings') }}</x-primary-button>
                </form>
            </div>

            @can('view users')
            <div class="bg-white overflow-hidden shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <div class="flex items-center justify-between px-6 pt-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('User Accounts') }}</h3>
                    @can('manage users')
                        <x-link-button :href="route('settings.users.create')">{{ __('Add User') }}</x-link-button>
                    @endcan
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 mt-4">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Role') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $user->getRoleNames()->first() ?? '—' }}</td>
                                <td class="px-6 py-3 text-sm">
                                    <span @class([
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400' => $user->is_active,
                                        'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400' => ! $user->is_active,
                                    ])>{{ $user->is_active ? __('Active') : __('Deactivated') }}</span>
                                </td>
                                <td class="px-6 py-3 text-right text-sm space-x-3 whitespace-nowrap">
                                    @can('manage users')
                                        <a href="{{ route('settings.users.edit', $user) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('Edit') }}</a>
                                        @unless ($user->is(auth()->user()))
                                            <form method="POST" action="{{ route('settings.users.toggle-active', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ $user->is_active ? __('Deactivate') : __('Activate') }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('settings.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">{{ __('Delete') }}</button>
                                            </form>
                                        @endunless
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endcan
        </div>
    </div>
</x-app-layout>

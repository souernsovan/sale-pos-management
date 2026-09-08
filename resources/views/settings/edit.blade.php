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
                            <img src="{{ App\Support\Uploads::url($settings['shop_logo']) }}" class="h-12 mt-2 mb-2">
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
                                    <img src="{{ App\Support\Uploads::url($settings['site_icon']) }}" alt="{{ __('Current browser icon') }}" class="mt-2 h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-800">
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

                    <div class="border-t border-gray-200 dark:border-gray-800 pt-6">
                        <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Bakong / KHQR Payments') }}</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Set your Individual Bakong account details to show a scannable KHQR code at checkout for Bank Transfer sales. Leave the Account ID blank to hide it.') }}</p>

                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="bakong_account_id" :value="__('Bakong Account ID')" />
                                <x-text-input id="bakong_account_id" name="bakong_account_id" type="text" class="mt-1 block w-full" placeholder="name@bank" :value="old('bakong_account_id', $settings['bakong_account_id'])" />
                                <x-input-error class="mt-2" :messages="$errors->get('bakong_account_id')" />
                            </div>
                            <div>
                                <x-input-label for="bakong_account_name" :value="__('Account Name')" />
                                <x-text-input id="bakong_account_name" name="bakong_account_name" type="text" class="mt-1 block w-full" :value="old('bakong_account_name', $settings['bakong_account_name'])" />
                                <x-input-error class="mt-2" :messages="$errors->get('bakong_account_name')" />
                            </div>
                            <div>
                                <x-input-label for="bakong_merchant_city" :value="__('City')" />
                                <x-text-input id="bakong_merchant_city" name="bakong_merchant_city" type="text" class="mt-1 block w-full" :value="old('bakong_merchant_city', $settings['bakong_merchant_city'])" />
                                <x-input-error class="mt-2" :messages="$errors->get('bakong_merchant_city')" />
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

        </div>
    </div>
</x-app-layout>

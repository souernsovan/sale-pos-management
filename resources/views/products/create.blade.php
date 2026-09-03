<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Add Product') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <x-status-banner />

            <div class="bg-white p-6 shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="sku" :value="__('SKU')" />
                            <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full" :value="old('sku')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('sku')" />
                        </div>
                        <div>
                            <x-input-label for="barcode" :value="__('Barcode (optional — auto-generated if blank)')" />
                            <x-text-input id="barcode" name="barcode" type="text" class="mt-1 block w-full" :value="old('barcode')" />
                            <x-input-error class="mt-2" :messages="$errors->get('barcode')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="category_id" :value="__('Category')" />
                        <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100">
                            <option value="">{{ __('Uncategorized') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="price" :value="__('Price')" />
                            <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('price')" />
                        </div>
                        <div>
                            <x-input-label for="cost" :value="__('Cost')" />
                            <x-text-input id="cost" name="cost" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('cost', 0)" />
                            <x-input-error class="mt-2" :messages="$errors->get('cost')" />
                        </div>
                        <div>
                            <x-input-label for="stock_qty" :value="__('Initial Stock')" />
                            <x-text-input id="stock_qty" name="stock_qty" type="number" min="0" class="mt-1 block w-full" :value="old('stock_qty', 0)" />
                            <x-input-error class="mt-2" :messages="$errors->get('stock_qty')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="image" :value="__('Image')" />
                        <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-400" />
                        <x-input-error class="mt-2" :messages="$errors->get('image')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('products.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

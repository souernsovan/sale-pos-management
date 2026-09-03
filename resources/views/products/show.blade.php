<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $product->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex justify-end gap-4">
                <a href="{{ route('products.edit', $product) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('Edit') }}</a>
                <a href="{{ route('products.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Back to Products') }}</a>
            </div>

            <x-status-banner />

            <div class="bg-white p-6 shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 space-y-2">
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('SKU') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $product->sku }}</dd>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Category') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $product->category?->name ?? '—' }}</dd>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Price') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ number_format($product->price, 2) }}</dd>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Cost') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ number_format($product->cost, 2) }}</dd>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Stock on hand') }}</dt>
                        <dd @class(['font-semibold', 'text-red-600 dark:text-red-400' => $product->isLowStock($lowStockThreshold), 'text-green-700 dark:text-green-400' => ! $product->isLowStock($lowStockThreshold)])>{{ $product->stock_qty }}</dd>
                    </dl>
                </div>
                <div class="text-center">
                    @if ($product->image)
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="mx-auto h-24 w-24 object-cover rounded mb-3">
                    @endif
                    @if ($product->barcode)
                        <div class="mb-2 inline-block rounded bg-white p-2">{!! DNS1D::getBarcodeSVG($product->barcode, 'C128') !!}</div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product->barcode }}</p>
                        <a href="{{ route('products.barcode', $product) }}" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('Print label') }}</a>
                    @else
                        <p class="text-xs text-gray-400 dark:text-gray-600">{{ __('No barcode assigned.') }}</p>
                    @endif
                </div>
            </div>

            <div class="bg-white p-6 shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('Adjust Stock') }}</h3>
                <form method="POST" action="{{ route('products.stock-movements.store', $product) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <x-input-label for="type" :value="__('Type')" />
                        <select id="type" name="type" class="mt-1 block border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100">
                            <option value="restock">{{ __('Restock') }}</option>
                            <option value="adjustment">{{ __('Adjustment (correction)') }}</option>
                            <option value="damage">{{ __('Damage / Loss') }}</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="quantity" :value="__('Quantity')" />
                        <x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-28" required />
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <x-input-label for="note" :value="__('Note')" />
                        <x-text-input id="note" name="note" type="text" class="mt-1 block w-full" />
                    </div>
                    <x-primary-button>{{ __('Apply') }}</x-primary-button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 px-6 pt-6">{{ __('Stock Movement History') }}</h3>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 mt-4">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Date') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Type') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Qty') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Note') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('By') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($product->stockMovements as $movement)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100 capitalize">{{ $movement->type }}</td>
                                <td @class(['px-6 py-3 text-sm text-right', 'text-green-700 dark:text-green-400' => $movement->quantity > 0, 'text-red-700 dark:text-red-400' => $movement->quantity < 0])>{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $movement->note ?? '—' }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $movement->creator?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('No stock movements yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

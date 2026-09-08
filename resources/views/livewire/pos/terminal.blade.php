<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white p-4 shadow-sm rounded-lg space-y-3 dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
            <div>
                <x-input-label for="barcode" :value="__('Scan Barcode')" />
                <input
                    id="barcode"
                    type="text"
                    wire:model="barcode"
                    wire:keydown.enter.prevent="scan"
                    autofocus
                    autocomplete="off"
                    placeholder="{{ __('Scan or type a barcode, then press Enter') }}"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-lg dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                >
                @error('barcode') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="relative">
                <x-input-label for="search" :value="__('Or search by name / SKU')" />
                <input
                    id="search"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    autocomplete="off"
                    placeholder="{{ __('Start typing...') }}"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                >

                @if (trim($search) !== '')
                    <div class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-64 overflow-y-auto dark:bg-gray-900 dark:border-gray-800">
                        @forelse ($this->searchResults as $product)
                            <button
                                type="button"
                                wire:click="addFromSearch({{ $product->id }})"
                                class="w-full flex justify-between items-center px-4 py-2 text-sm hover:bg-gray-50 text-left dark:hover:bg-gray-800"
                            >
                                <span class="dark:text-gray-100">{{ $product->name }} <span class="text-gray-400 dark:text-gray-500">({{ $product->sku }})</span></span>
                                <span class="text-gray-600 dark:text-gray-400">{{ number_format($product->price, 2) }} · {{ __('stock') }}: {{ $product->stock_qty }}</span>
                            </button>
                        @empty
                            <p class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ __('No matching products.') }}</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
            @error('cart') <p class="px-6 pt-4 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Item') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Price') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Qty') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Subtotal') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($cart as $productId => $line)
                        <tr wire:key="cart-line-{{ $productId }}">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ $line['name'] }}
                                <div class="text-xs text-gray-400 dark:text-gray-500">{{ $line['sku'] }}</div>
                                @if ($line['quantity'] > $line['stock_qty'])
                                    <div class="text-xs text-red-600 dark:text-red-400 font-medium">{{ __('Exceeds available stock (:n on hand)', ['n' => $line['stock_qty']]) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ number_format($line['unit_price'], 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" wire:click="decrementQuantity({{ $productId }})" class="w-7 h-7 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300">-</button>
                                    <input
                                        type="number"
                                        min="1"
                                        value="{{ $line['quantity'] }}"
                                        wire:change="updateQuantity({{ $productId }}, $event.target.value)"
                                        class="w-16 text-center border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                    >
                                    <button type="button" wire:click="incrementQuantity({{ $productId }})" class="w-7 h-7 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300">+</button>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($line['unit_price'] * $line['quantity'], 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" wire:click="removeItem({{ $productId }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-sm">{{ __('Remove') }}</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('Cart is empty — scan a barcode or search for a product.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white p-6 shadow-sm rounded-lg h-fit space-y-4 dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
        <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Order Summary') }}</h3>

        <div class="relative">
            <x-input-label for="customerSearch" :value="__('Customer (optional)')" />

            @if ($this->selectedCustomer)
                <div class="mt-1 flex items-center justify-between border border-gray-300 rounded-md px-3 py-2 text-sm dark:border-gray-700 dark:text-gray-100">
                    <span>{{ $this->selectedCustomer->name }}</span>
                    <button type="button" wire:click="clearCustomer" class="text-gray-400 hover:text-red-600 dark:text-gray-500 dark:hover:text-red-400">&times;</button>
                </div>
            @else
                <input
                    id="customerSearch"
                    type="text"
                    wire:model.live.debounce.300ms="customerSearch"
                    autocomplete="off"
                    placeholder="{{ __('Search customer by name/phone...') }}"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                >
                @if (trim($customerSearch) !== '')
                    <div class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto dark:bg-gray-900 dark:border-gray-800">
                        @forelse ($this->customerResults as $customer)
                            <button type="button" wire:click="selectCustomer({{ $customer->id }})" class="w-full px-4 py-2 text-sm hover:bg-gray-50 text-left dark:text-gray-100 dark:hover:bg-gray-800">
                                {{ $customer->name }} <span class="text-gray-400 dark:text-gray-500">{{ $customer->phone }}</span>
                            </button>
                        @empty
                            <p class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ __('No matching customers.') }}</p>
                        @endforelse
                    </div>
                @endif
            @endif
        </div>

        <div class="flex justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">{{ __('Subtotal') }}</span>
            <span class="text-gray-900 dark:text-gray-100">{{ number_format($this->subtotal, 2) }}</span>
        </div>

        <div>
            <x-input-label for="discount" :value="__('Discount')" />
            <x-text-input id="discount" type="number" step="0.01" min="0" wire:model.live="discount" class="mt-1 block w-full" />
            @error('discount') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-between text-base font-semibold border-t pt-3 dark:border-gray-800 dark:text-gray-100">
            <span>{{ __('Total') }}</span>
            <span>{{ number_format($this->total, 2) }}</span>
        </div>

        <div>
            <x-input-label for="paymentMethod" :value="__('Payment Method')" />
            <select id="paymentMethod" wire:model.live="paymentMethod" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="cash">{{ __('Cash') }}</option>
                <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
            </select>
            @error('paymentMethod') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        @if ($paymentMethod === 'bank_transfer')
            <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 text-center" wire:loading.class="opacity-50" wire:target="discount,removeItem,incrementQuantity,decrementQuantity,updateQuantity,scan,addFromSearch">
                @if ($this->khqrSvg)
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Scan with a Bakong-linked banking app to pay :amount', ['amount' => number_format($this->total, 2)]) }}</p>
                    <div class="inline-block bg-white p-2 rounded">{!! $this->khqrSvg !!}</div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Confirm the payment has arrived before completing the sale.') }}</p>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Add a Bakong Account ID in Settings to show a KHQR code here.') }}</p>
                @endif
            </div>
        @endif

        <button
            type="button"
            wire:click="checkout"
            wire:loading.attr="disabled"
            class="w-full inline-flex justify-center items-center px-4 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50"
        >
            {{ __('Complete Sale') }}
        </button>
    </div>
</div>

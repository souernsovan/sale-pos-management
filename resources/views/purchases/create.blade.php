<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Record Purchase') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full max-w-4xl px-4 sm:px-6 lg:px-8">
            <x-status-banner />

            <div
                x-data="purchaseForm({
                    products: {{ $products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'cost' => (float) $p->cost])->values()->toJson() }},
                })"
                class="bg-white p-6 shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800"
            >
                <form method="POST" action="{{ route('purchases.store') }}" class="space-y-6" @submit="if (! items.length) $event.preventDefault()">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="supplier_id" :value="__('Supplier')" />
                            <select id="supplier_id" name="supplier_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100" required>
                                <option value="">{{ __('Select a supplier') }}</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('supplier_id')" />
                        </div>

                        <div>
                            <x-input-label for="purchased_at" :value="__('Purchase Date')" />
                            <x-text-input id="purchased_at" name="purchased_at" type="date" class="mt-1 block w-full" :value="old('purchased_at', now()->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('purchased_at')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" :value="__('Notes')" />
                        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Items') }}</h3>
                            <button type="button" @click="addItem()" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('+ Add Item') }}</button>
                        </div>
                        <x-input-error class="mb-2" :messages="$errors->get('items')" />

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Product') }}</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-28">{{ __('Quantity') }}</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">{{ __('Cost Price') }}</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-28">{{ __('Subtotal') }}</th>
                                        <th class="w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="px-3 py-2">
                                                <select :name="`items[${index}][product_id]`" x-model.number="item.product_id" @change="onProductChange(item)" class="block w-full border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100 text-sm" required>
                                                    <option value="">{{ __('Select a product') }}</option>
                                                    <template x-for="product in products" :key="product.id">
                                                        <option :value="product.id" x-text="`${product.name} (${product.sku})`"></option>
                                                    </template>
                                                </select>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" step="1" class="block w-full border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100 text-sm text-right" required>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" :name="`items[${index}][cost_price]`" x-model.number="item.cost_price" min="0" step="0.01" class="block w-full border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100 text-sm text-right" required>
                                            </td>
                                            <td class="px-3 py-2 text-right text-sm text-gray-700 dark:text-gray-300" x-text="lineSubtotal(item)"></td>
                                            <td class="px-3 py-2 text-right">
                                                <button type="button" @click="removeItem(index)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">&times;</button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="! items.length">
                                        <td colspan="5" class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('No items yet — click "Add Item" to begin.') }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="px-3 py-2 text-right text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Total') }}</td>
                                        <td class="px-3 py-2 text-right text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="total()"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save Purchase') }}</x-primary-button>
                        <a href="{{ route('purchases.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function purchaseForm({ products }) {
            return {
                products,
                items: [],
                addItem() {
                    this.items.push({ product_id: '', quantity: 1, cost_price: 0 });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                onProductChange(item) {
                    const product = this.products.find(p => p.id === item.product_id);
                    if (product) {
                        item.cost_price = product.cost;
                    }
                },
                lineSubtotal(item) {
                    const qty = Number(item.quantity) || 0;
                    const cost = Number(item.cost_price) || 0;
                    return (qty * cost).toFixed(2);
                },
                total() {
                    return this.items.reduce((sum, item) => sum + ((Number(item.quantity) || 0) * (Number(item.cost_price) || 0)), 0).toFixed(2);
                },
                init() {
                    this.addItem();
                },
            };
        }
    </script>
</x-app-layout>

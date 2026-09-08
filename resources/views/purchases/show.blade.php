<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Purchase') }} #{{ $purchase->id }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">
            <x-status-banner />

            <div class="bg-white p-6 shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Supplier') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $purchase->supplier?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Date') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $purchase->purchased_at->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Recorded By') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $purchase->user?->name ?? '—' }}</dd>
                    </div>
                    @if ($purchase->notes)
                        <div class="sm:col-span-3">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Notes') }}</dt>
                            <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $purchase->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Product') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Quantity') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Cost Price') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($purchase->items as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $item->product?->name ?? __('Deleted product') }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-600 dark:text-gray-400">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-600 dark:text-gray-400">{{ number_format($item->cost_price, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Total') }}</td>
                            <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($purchase->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <a href="{{ route('purchases.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('&larr; Back to Purchases') }}</a>
        </div>
    </div>
</x-app-layout>

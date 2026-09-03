<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Sale #:id', ['id' => $sale->id]) }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex justify-end gap-4">
                <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('View Receipt') }}</a>
                <a href="{{ route('sales.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Back to Sales') }}</a>
            </div>

            <x-status-banner />

            <div class="bg-white p-6 shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Date') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $sale->created_at->format('Y-m-d H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Cashier') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $sale->user?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Customer') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">
                            @if ($sale->customer)
                                <a href="{{ route('customers.show', $sale->customer) }}" class="hover:underline">{{ $sale->customer->name }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Payment Method') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100 capitalize">{{ str_replace('_', ' ', $sale->payment_method) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                        <dd>
                            <span @class([
                                'px-2 py-1 rounded-full text-xs font-medium',
                                'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400' => $sale->status === 'completed',
                                'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400' => $sale->status === 'voided',
                            ])>{{ ucfirst($sale->status) }}</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Product') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Unit Price') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Qty') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($sale->items as $item)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $item->product?->name ?? __('(deleted product)') }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ $item->quantity }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="px-6 py-2 text-sm text-right text-gray-500 dark:text-gray-400">{{ __('Subtotal') }}</td>
                            <td class="px-6 py-2 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($sale->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-6 py-2 text-sm text-right text-gray-500 dark:text-gray-400">{{ __('Discount') }}</td>
                            <td class="px-6 py-2 text-sm text-right text-gray-900 dark:text-gray-100">-{{ number_format($sale->discount, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-base text-right font-semibold text-gray-800 dark:text-gray-200">{{ __('Total') }}</td>
                            <td class="px-6 py-3 text-base text-right font-semibold text-gray-900 dark:text-gray-100">{{ number_format($sale->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if (! $sale->isVoided())
                <form method="POST" action="{{ route('sales.void', $sale) }}" onsubmit="return confirm('Void this sale? Stock will be restored.');">
                    @csrf
                    <x-danger-button>{{ __('Void Sale') }}</x-danger-button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>

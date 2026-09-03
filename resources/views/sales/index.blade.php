<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Sales History') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ __('Sales History') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Review completed and voided sales, payments, customers, and transaction details.') }}</p>
                </div>

                <x-link-button :href="route('pos.index')">{{ __('New Sale') }}</x-link-button>
            </div>

            <x-status-banner />

            <form method="GET" action="{{ route('sales.index') }}" class="mb-4 flex flex-wrap gap-3 items-end bg-white p-4 rounded-lg shadow-sm dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <div>
                    <x-input-label for="date_from" :value="__('From')" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block" :value="request('date_from')" />
                </div>
                <div>
                    <x-input-label for="date_to" :value="__('To')" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block" :value="request('date_to')" />
                </div>
                <div>
                    <x-input-label for="payment_method" :value="__('Payment Method')" />
                    <select id="payment_method" name="payment_method" class="mt-1 block border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100">
                        <option value="">{{ __('All') }}</option>
                        <option value="cash" @selected(request('payment_method') === 'cash')>{{ __('Cash') }}</option>
                        <option value="bank_transfer" @selected(request('payment_method') === 'bank_transfer')>{{ __('Bank Transfer') }}</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" class="mt-1 block border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100">
                        <option value="">{{ __('All') }}</option>
                        <option value="completed" @selected(request('status') === 'completed')>{{ __('Completed') }}</option>
                        <option value="voided" @selected(request('status') === 'voided')>{{ __('Voided') }}</option>
                    </select>
                </div>
                <x-primary-button>{{ __('Filter') }}</x-primary-button>
                @if (request()->anyFilled(['date_from', 'date_to', 'payment_method', 'status']))
                    <a href="{{ route('sales.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Reset') }}</a>
                @endif
            </form>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg overflow-x-auto dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('#') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Date') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Cashier') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Customer') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Payment') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Total') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">#{{ $sale->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $sale->user?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $sale->customer?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $sale->payment_method) }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($sale->total, 2) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span @class([
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400' => $sale->status === 'completed',
                                        'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400' => $sale->status === 'voided',
                                    ])>{{ ucfirst($sale->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('No sales found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $sales->links() }}</div>
        </div>
    </div>
</x-app-layout>

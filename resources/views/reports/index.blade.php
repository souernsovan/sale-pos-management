<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Reports') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ __('Reports') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Analyze sales, profit, payment activity, and your best-selling products.') }}</p>
            </div>

            <x-status-banner />

            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap gap-3 items-end bg-white p-4 rounded-lg shadow-sm dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <div>
                    <x-input-label for="date_from" :value="__('From')" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block" :value="$dateFrom" />
                </div>
                <div>
                    <x-input-label for="date_to" :value="__('To')" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block" :value="$dateTo" />
                </div>
                <div>
                    <x-input-label for="group_by" :value="__('Sales grouped by')" />
                    <select id="group_by" name="group_by" class="mt-1 block border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100">
                        <option value="daily" @selected($groupBy === 'daily')>{{ __('Daily') }}</option>
                        <option value="weekly" @selected($groupBy === 'weekly')>{{ __('Weekly') }}</option>
                        <option value="monthly" @selected($groupBy === 'monthly')>{{ __('Monthly') }}</option>
                    </select>
                </div>
                <x-primary-button>{{ __('Apply') }}</x-primary-button>
                @if ($dateFrom || $dateTo)
                    <a href="{{ route('reports.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Reset') }}</a>
                @endif
            </form>

            @php $qs = request()->only(['date_from', 'date_to', 'group_by']); @endphp

            <div class="bg-white overflow-hidden shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <div class="flex items-center justify-between px-6 pt-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Sales Report') }}</h3>
                    <div class="space-x-3 text-sm">
                        <a href="{{ route('reports.sales.pdf', $qs) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('PDF') }}</a>
                        <a href="{{ route('reports.sales.export', $qs) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('Excel') }}</a>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 mt-4">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Period') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Sales') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($salesReport as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $row->period }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ $row->sales_count }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($row->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('No sales in this range.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <div class="flex items-center justify-between px-6 pt-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Profit Report') }}</h3>
                    <div class="space-x-3 text-sm">
                        <a href="{{ route('reports.profit.pdf', $qs) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('PDF') }}</a>
                        <a href="{{ route('reports.profit.export', $qs) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('Excel') }}</a>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 mt-4">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Product') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Units') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Revenue') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Cost') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Profit') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($profitReport as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $row->product?->name ?? __('(deleted product)') }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ $row->quantity }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ number_format($row->revenue, 2) }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ number_format($row->cost, 2) }}</td>
                                <td class="px-6 py-3 text-sm text-right font-medium text-green-700 dark:text-green-400">{{ number_format($row->profit, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('No sales in this range.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <div class="flex items-center justify-between px-6 pt-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Best-Selling Products') }}</h3>
                    <div class="space-x-3 text-sm">
                        <a href="{{ route('reports.best-sellers.pdf', $qs) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('PDF') }}</a>
                        <a href="{{ route('reports.best-sellers.export', $qs) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('Excel') }}</a>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 mt-4">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Product') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Units Sold') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($bestSellers as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $row->product?->name ?? __('(deleted product)') }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ $row->total_qty }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('No sales in this range.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

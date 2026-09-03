<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $customer->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex justify-end gap-4">
                <a href="{{ route('customers.edit', $customer) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('Edit') }}</a>
                <a href="{{ route('customers.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Back to Customers') }}</a>
            </div>

            <x-status-banner />

            <div class="bg-white p-6 shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Phone') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $customer->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Address') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $customer->address ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Balance / Credit Owed') }}</dt>
                        <dd @class(['font-semibold', 'text-red-600 dark:text-red-400' => $customer->balance > 0, 'text-gray-900 dark:text-gray-100' => $customer->balance <= 0])>{{ number_format($customer->balance, 2) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 px-6 pt-6">{{ __('Purchase History') }}</h3>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 mt-4">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('#') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Date') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Payment') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Total') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">#{{ $sale->id }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $sale->payment_method) }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($sale->total, 2) }}</td>
                                <td class="px-6 py-3 text-sm">
                                    <span @class([
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400' => $sale->status === 'completed',
                                        'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400' => $sale->status === 'voided',
                                    ])>{{ ucfirst($sale->status) }}</span>
                                </td>
                                <td class="px-6 py-3 text-right text-sm">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('No purchases yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $sales->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>

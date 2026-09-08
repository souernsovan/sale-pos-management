<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Audit Log') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ __('Audit Log') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('A record of sensitive admin actions: users, roles, settings, and catalog deletions.') }}</p>
            </div>

            <x-status-banner />

            <form method="GET" action="{{ route('audit.index') }}" class="mb-4 flex flex-wrap gap-3 items-end bg-white p-4 rounded-lg shadow-sm dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <div>
                    <x-input-label for="log_name" :value="__('Category')" />
                    <select id="log_name" name="log_name" class="mt-1 block border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($logNames as $logName)
                            <option value="{{ $logName }}" @selected(request('log_name') === $logName)>{{ ucfirst($logName) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="causer_id" :value="__('User')" />
                    <select id="causer_id" name="causer_id" class="mt-1 block border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($causers as $causer)
                            <option value="{{ $causer->id }}" @selected(request('causer_id') == $causer->id)>{{ $causer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="event" :value="__('Action')" />
                    <select id="event" name="event" class="mt-1 block border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100">
                        <option value="">{{ __('All') }}</option>
                        <option value="created" @selected(request('event') === 'created')>{{ __('Created') }}</option>
                        <option value="updated" @selected(request('event') === 'updated')>{{ __('Updated') }}</option>
                        <option value="deleted" @selected(request('event') === 'deleted')>{{ __('Deleted') }}</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="date_from" :value="__('From')" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block" :value="request('date_from')" />
                </div>
                <div>
                    <x-input-label for="date_to" :value="__('To')" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block" :value="request('date_to')" />
                </div>
                <x-primary-button>{{ __('Filter') }}</x-primary-button>
                @if (request()->anyFilled(['log_name', 'causer_id', 'event', 'date_from', 'date_to']))
                    <a href="{{ route('audit.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Reset') }}</a>
                @endif
            </form>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Date') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('User') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Category') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Action') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Description') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($activities as $activity)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $activity->causer?->name ?? __('System') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ ucfirst($activity->log_name ?? '—') }}</td>
                                <td class="px-6 py-3 text-sm">
                                    @if ($activity->event)
                                        <span @class([
                                            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize',
                                            'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400' => $activity->event === 'created',
                                            'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400' => $activity->event === 'updated',
                                            'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400' => $activity->event === 'deleted',
                                        ])>{{ $activity->event }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $activity->description }}</td>
                                <td class="px-6 py-3 text-sm">
                                    @if ($activity->properties && $activity->properties->isNotEmpty())
                                        <details>
                                            <summary class="cursor-pointer text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('Details') }}</summary>
                                            <pre class="mt-2 max-w-md overflow-x-auto rounded bg-gray-50 dark:bg-gray-800 p-2 text-xs text-gray-700 dark:text-gray-300">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('No audit entries yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $activities->links() }}</div>
        </div>
    </div>
</x-app-layout>

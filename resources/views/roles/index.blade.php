<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Roles') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ __('Roles') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Control staff access by assigning permissions to each role.') }}</p>
                </div>

                @can('manage roles')
                    <x-link-button :href="route('roles.create')">{{ __('Create Role') }}</x-link-button>
                @endcan
            </div>

            <x-status-banner />

            <div class="mb-4 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400 items-center">
                <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400 font-medium">Full</span>
                <span>all actions</span>
                <span class="inline-flex items-center px-2 py-1 rounded-full bg-indigo-100 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 font-medium">Manage</span>
                <span>write access</span>
                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-medium">View</span>
                <span>read-only</span>
                <span>—</span>
                <span>no access</span>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg overflow-x-auto dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Role') }}</th>
                            @foreach ($modules as $moduleName => $permissions)
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $moduleName }}</th>
                            @endforeach
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($roles as $role)
                            <tr>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $role->name }}</span>
                                        @if (in_array($role->name, $builtIn))
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500 dark:text-gray-400 dark:bg-gray-800 dark:text-gray-400">{{ __('Built-in') }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-600">{{ trans_choice('{0} :count users|{1} :count user|[2,*] :count users', $role->users_count, ['count' => $role->users_count]) }}</div>
                                </td>
                                @foreach ($modules as $moduleName => $permissions)
                                    @php $badge = $matrix[$role->id][$moduleName]; @endphp
                                    <td class="px-4 py-4 text-sm">
                                        <span title="{{ implode(', ', $permissions) }}" @class([
                                            'px-2 py-1 rounded-full text-xs font-medium',
                                            'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400' => $badge['level'] === 'full',
                                            'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400' => $badge['level'] === 'manage',
                                            'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400' => $badge['level'] === 'view',
                                            'text-gray-300 dark:text-gray-700' => $badge['level'] === 'none',
                                        ])>{{ $badge['label'] }}</span>
                                    </td>
                                @endforeach
                                <td class="px-6 py-4 text-right text-sm space-x-3 whitespace-nowrap">
                                    @can('manage roles')
                                        @if ($role->name !== 'Super Admin')
                                            <a href="{{ route('roles.edit', $role) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('Edit') }}</a>
                                            <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Delete this role?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

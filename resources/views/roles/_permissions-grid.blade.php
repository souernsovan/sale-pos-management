@php $rolePermissions = old('permissions', $rolePermissions); @endphp

<div>
    <div class="flex items-center justify-between mb-3">
        <x-input-label :value="__('Permissions')" />
        <div class="flex items-center gap-3 text-sm">
            @isset($hasDefault)
                @if ($hasDefault)
                    <form method="POST" action="{{ route('roles.reset-default', $role) }}" onsubmit="return confirm('Reset this role to its default permissions?');">
                        @csrf
                        <button type="submit" class="px-3 py-1 border border-red-300 text-red-600 dark:text-red-400 rounded-md hover:bg-red-50 text-xs font-medium">{{ __('Reset to Default') }}</button>
                    </form>
                @endif
            @endisset
            <button type="button" id="select-all" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ __('Select all') }}</button>
            <button type="button" id="clear-all" class="text-gray-500 dark:text-gray-400 hover:text-gray-700">{{ __('Clear all') }}</button>
        </div>
    </div>
    <x-input-error class="mb-2" :messages="$errors->get('permissions')" />

    <div class="overflow-x-auto border border-gray-200 dark:border-gray-800 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Area') }}</th>
                    @foreach ($actions as $action)
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ ucfirst($action) }}</th>
                    @endforeach
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach ($modules as $moduleName => $moduleActions)
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">{{ $moduleName }}</td>
                        @foreach ($actions as $action)
                            <td class="px-4 py-2 text-center">
                                @if (isset($moduleActions[$action]))
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $moduleActions[$action] }}"
                                        class="perm-checkbox row-{{ $loop->parent->index }} rounded border-gray-300 dark:border-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm focus:ring-indigo-500"
                                        @checked(collect($rolePermissions)->contains($moduleActions[$action]))
                                    >
                                @else
                                    <span class="text-gray-300 dark:text-gray-700">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <button type="button" class="row-all text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300" data-row="{{ $loop->index }}">{{ __('all') }}</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

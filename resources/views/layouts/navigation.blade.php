@php
    $navSections = [
        [
            'label' => 'Overview',
            'items' => [
                ['permission' => null, 'route' => 'dashboard', 'pattern' => 'dashboard', 'label' => __('Dashboard'), 'icon' => 'dashboard'],
            ],
        ],
        [
            'label' => 'Catalog',
            'items' => [
                ['permission' => 'view products', 'route' => 'products.index', 'pattern' => 'products.*', 'label' => __('Products'), 'icon' => 'products'],
                ['permission' => 'view categories', 'route' => 'categories.index', 'pattern' => 'categories.*', 'label' => __('Categories'), 'icon' => 'categories'],
            ],
        ],
        [
            'label' => 'Sales',
            'items' => [
                ['permission' => 'access pos', 'route' => 'pos.index', 'pattern' => 'pos.*', 'label' => __('New Sale'), 'icon' => 'pos'],
                ['permission' => 'view sales', 'route' => 'sales.index', 'pattern' => 'sales.*', 'label' => __('Sales'), 'icon' => 'sales'],
                ['permission' => 'view customers', 'route' => 'customers.index', 'pattern' => 'customers.*', 'label' => __('Customers'), 'icon' => 'customers'],
            ],
        ],
        [
            'label' => 'Insights',
            'items' => [
                ['permission' => 'view reports', 'route' => 'reports.index', 'pattern' => 'reports.*', 'label' => __('Reports'), 'icon' => 'reports'],
            ],
        ],
        [
            'label' => 'Administration',
            'items' => [
                ['permission' => 'view roles', 'route' => 'roles.index', 'pattern' => 'roles.*', 'label' => __('Roles'), 'icon' => 'roles'],
                ['permission' => 'manage settings', 'route' => 'settings.edit', 'pattern' => 'settings.*', 'label' => __('Settings'), 'icon' => 'settings'],
            ],
        ],
    ];
@endphp

<div x-data="{ sidebarOpen: false }" @open-sidebar.window="sidebarOpen = true" class="h-full">
    <!-- Mobile overlay -->
    <div
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-gray-900/50 md:hidden"
        style="display: none;"
    ></div>

    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 flex h-full w-64 shrink-0 flex-col border-r border-gray-200 bg-white transition-transform duration-200 ease-in-out md:static md:translate-x-0 dark:border-gray-800 dark:bg-black"
    >
        <div class="flex items-center justify-between px-5 py-5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 min-w-0">
                <x-application-logo class="h-8 w-auto shrink-0 object-contain" />
            </a>
            <button @click="sidebarOpen = false" class="md:hidden p-1 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 pb-4 space-y-6">
            @foreach ($navSections as $section)
                @php
                    $visibleItems = collect($section['items'])->filter(fn ($item) => is_null($item['permission']) || auth()->user()->can($item['permission']));
                @endphp
                @if ($visibleItems->isNotEmpty())
                    <div>
                        <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $section['label'] }}</p>
                        <div class="space-y-1">
                            @foreach ($visibleItems as $item)
                                @php $active = request()->routeIs($item['pattern']); @endphp
                                <a
                                    href="{{ route($item['route']) }}"
                                    @class([
                                        'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition',
                                        'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' => $active,
                                        'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-100' => ! $active,
                                    ])
                                >
                                    <x-nav-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="border-t border-gray-200 p-3 dark:border-gray-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a
                    href="{{ route('logout') }}"
                    onclick="event.preventDefault(); this.closest('form').submit();"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950"
                >
                    <x-nav-icon name="logout" class="h-5 w-5 shrink-0" />
                    {{ __('Log Out') }}
                </a>
            </form>
        </div>
    </aside>
</div>

@props(['name'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    @switch($name)
        @case('dashboard')
            <rect x="3.5" y="3.5" width="7" height="7" rx="1.5" />
            <rect x="13.5" y="3.5" width="7" height="7" rx="1.5" />
            <rect x="3.5" y="13.5" width="7" height="7" rx="1.5" />
            <rect x="13.5" y="13.5" width="7" height="7" rx="1.5" />
            @break

        @case('products')
            <path d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
            @break

        @case('categories')
            <path d="M3.75 7A1.75 1.75 0 015.5 5.25h3.69c.4 0 .78.16 1.06.44l1.06 1.06c.28.28.66.44 1.06.44H18.5A1.75 1.75 0 0120.25 9v8A1.75 1.75 0 0118.5 18.75h-13A1.75 1.75 0 013.75 17V7z" />
            @break

        @case('pos')
            <circle cx="9.5" cy="19" r="1" />
            <circle cx="17" cy="19" r="1" />
            <path d="M3 4h2l2.2 11h9.8L19 8.5H6.4" />
            @break

        @case('sales')
            <path d="M4 5h16M4 10.5h16M4 16h10" />
            @break

        @case('customers')
            <circle cx="9" cy="8" r="3" />
            <path d="M3.5 19.5c.4-3 2.8-5 5.5-5s5.1 2 5.5 5" />
            <circle cx="17.25" cy="9" r="2.25" />
            <path d="M15.5 19.5c.2-2.2 1.6-3.8 3.3-4.4" />
            @break

        @case('reports')
            <path d="M4.5 20V10.5M11 20V4M17.5 20v-6.5M3.5 20h17" />
            @break

        @case('roles')
            <path d="M12 3.5l7 2.75v5c0 5-3.4 8-7 9.75-3.6-1.75-7-4.75-7-9.75v-5L12 3.5z" />
            @break

        @case('settings')
            <circle cx="12" cy="12" r="3" />
            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z" />
            @break

        @case('logout')
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
            @break

        @case('suppliers')
            <rect x="2.5" y="7" width="11" height="8" rx="1" />
            <path d="M13.5 10h3.5l3 3v2h-2" />
            <circle cx="7" cy="18" r="1.5" />
            <circle cx="16.5" cy="18" r="1.5" />
            @break

        @case('purchases')
            <path d="M4 8l3-4h10l3 4" />
            <path d="M4 8v10a1 1 0 001 1h14a1 1 0 001-1V8" />
            <path d="M4 8h5a3 3 0 006 0h5" />
            @break

        @case('users')
            <circle cx="9" cy="7.5" r="3" />
            <path d="M3.5 19c.4-3.2 2.8-5.5 5.5-5.5s5.1 2.3 5.5 5.5" />
            <path d="M15.5 8a2.5 2.5 0 010 5" />
            <path d="M18.5 19c-.2-2-1.3-3.7-2.8-4.6" />
            @break

        @case('audit')
            <path d="M6 3.5h9l3.5 3.5v13a1 1 0 01-1 1H6a1 1 0 01-1-1v-15a1 1 0 011-1z" />
            <path d="M14.5 3.5V7a1 1 0 001 1H19" />
            <path d="M8 13.5l2.5 2.5L16 10.5" />
            @break
    @endswitch
</svg>

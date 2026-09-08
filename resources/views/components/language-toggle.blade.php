@php
    $locales = ['en' => 'EN', 'km' => 'ខ្មែរ'];
    $current = app()->getLocale();
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 rounded-full p-1 bg-gray-100 dark:bg-gray-800']) }}>
    @foreach ($locales as $code => $label)
        <form method="POST" action="{{ route('locale.update') }}">
            @csrf
            <input type="hidden" name="locale" value="{{ $code }}">
            <button
                type="submit"
                aria-label="{{ __('Set the interface language.') }}"
                @class([
                    'px-2.5 py-1 rounded-full text-xs font-medium transition',
                    'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm' => $current === $code,
                    'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' => $current !== $code,
                ])
            >{{ $label }}</button>
        </form>
    @endforeach
</div>

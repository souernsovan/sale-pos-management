<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Best-Selling Products') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 16px; }
        .muted { color: #555; font-size: 11px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $shopName }} — {{ __('Best-Selling Products') }}</h1>
    <p class="muted">{{ $dateFrom ?: __('Beginning') }} &ndash; {{ $dateTo ?: __('Today') }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('Product') }}</th>
                <th class="right">{{ __('Units Sold') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bestSellers as $row)
                <tr>
                    <td>{{ $row->product?->name ?? __('(deleted product)') }}</td>
                    <td class="right">{{ $row->total_qty }}</td>
                </tr>
            @empty
                <tr><td colspan="2">{{ __('No sales in this range.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

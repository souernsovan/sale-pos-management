<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Profit Report') }}</title>
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
    <h1>{{ $shopName }} — {{ __('Profit Report') }}</h1>
    <p class="muted">{{ $dateFrom ?: __('Beginning') }} &ndash; {{ $dateTo ?: __('Today') }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('Product') }}</th>
                <th class="right">{{ __('Units') }}</th>
                <th class="right">{{ __('Revenue') }}</th>
                <th class="right">{{ __('Cost') }}</th>
                <th class="right">{{ __('Profit') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($profitReport as $row)
                <tr>
                    <td>{{ $row->product?->name ?? __('(deleted product)') }}</td>
                    <td class="right">{{ $row->quantity }}</td>
                    <td class="right">{{ number_format($row->revenue, 2) }}</td>
                    <td class="right">{{ number_format($row->cost, 2) }}</td>
                    <td class="right">{{ number_format($row->profit, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">{{ __('No sales in this range.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

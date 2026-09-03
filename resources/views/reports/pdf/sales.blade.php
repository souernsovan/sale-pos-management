<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Sales Report') }}</title>
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
    <h1>{{ $shopName }} — {{ __('Sales Report') }}</h1>
    <p class="muted">{{ $dateFrom ?: __('Beginning') }} &ndash; {{ $dateTo ?: __('Today') }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('Period') }}</th>
                <th class="right">{{ __('Sales') }}</th>
                <th class="right">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($salesReport as $row)
                <tr>
                    <td>{{ $row->period }}</td>
                    <td class="right">{{ $row->sales_count }}</td>
                    <td class="right">{{ number_format($row->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">{{ __('No sales in this range.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

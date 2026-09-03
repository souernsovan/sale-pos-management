<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Receipt') }} #{{ $sale->id }}</title>
    <style>
        body { font-family: monospace, sans-serif; font-size: 12px; width: 280px; margin: 20px auto; color: #111; }
        h1 { font-size: 14px; text-align: center; margin: 0 0 4px; }
        .center { text-align: center; }
        .muted { color: #555; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        hr { border: none; border-top: 1px dashed #999; margin: 8px 0; }
        .totals td { padding-top: 4px; }
        .grand { font-weight: bold; font-size: 13px; }
        .no-print { text-align: center; margin-bottom: 10px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0 auto; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">{{ __('Print') }}</button>
    </div>

    <h1>{{ $shop['name'] }}</h1>
    @if ($shop['address'])
        <p class="center muted">{{ $shop['address'] }}</p>
    @endif
    <p class="center muted">{{ __('Receipt') }} #{{ $sale->id }}<br>{{ $sale->created_at->format('Y-m-d H:i') }}</p>

    <hr>

    <table>
        @foreach ($sale->items as $item)
            <tr>
                <td colspan="3">{{ $item->product?->name ?? __('(deleted product)') }}</td>
            </tr>
            <tr>
                <td>{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</td>
                <td></td>
                <td class="right">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <hr>

    <table class="totals">
        <tr>
            <td>{{ __('Subtotal') }}</td>
            <td></td>
            <td class="right">{{ number_format($sale->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>{{ __('Discount') }}</td>
            <td></td>
            <td class="right">-{{ number_format($sale->discount, 2) }}</td>
        </tr>
        <tr class="grand">
            <td>{{ __('Total') }}</td>
            <td></td>
            <td class="right">{{ number_format($sale->total, 2) }}</td>
        </tr>
    </table>

    <hr>

    <p class="muted">
        {{ __('Payment') }}: {{ str_replace('_', ' ', $sale->payment_method) }}<br>
        {{ __('Cashier') }}: {{ $sale->user?->name ?? '—' }}
        @if ($sale->customer)
            <br>{{ __('Customer') }}: {{ $sale->customer->name }}
        @endif
    </p>

    <p class="center muted">{{ __('Thank you!') }}</p>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $product->name }} — {{ __('Barcode Label') }}</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 20px; }
        .label { display: inline-block; border: 1px dashed #ccc; padding: 12px 20px; }
        .name { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
        .price { font-size: 13px; margin-bottom: 6px; }
        .code { font-size: 12px; letter-spacing: 1px; margin-top: 4px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">{{ __('Print') }}</button>

    <div class="label">
        <div class="name">{{ $product->name }}</div>
        <div class="price">{{ number_format($product->price, 2) }}</div>
        {!! DNS1D::getBarcodeSVG($product->barcode, 'C128') !!}
        <div class="code">{{ $product->barcode }}</div>
    </div>
</body>
</html>

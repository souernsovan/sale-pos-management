@php
    $lineLeft = 46; $lineRight = 12; $lineTop = 16; $lineBottom = 28;
    $lineW = 700; $lineH = 260;
    $lineChartW = $lineW - $lineLeft - $lineRight;
    $lineChartH = $lineH - $lineTop - $lineBottom;
    $lineCount = count($salesTrend);

    $linePoints = collect($salesTrend)->values()->map(function ($point, $i) use ($lineLeft, $lineChartW, $lineTop, $lineChartH, $lineCount, $salesTrendScale) {
        $x = $lineCount > 1 ? $lineLeft + ($i / ($lineCount - 1)) * $lineChartW : $lineLeft;
        $y = $lineTop + $lineChartH - ($salesTrendScale['max'] > 0 ? ($point['total'] / $salesTrendScale['max']) * $lineChartH : 0);

        return ['x' => round($x, 2), 'y' => round($y, 2), 'label' => $point['label'], 'total' => $point['total']];
    });

    $linePolyline = $linePoints->map(fn ($p) => "{$p['x']},{$p['y']}")->implode(' ');
    $lineAreaPoints = $linePolyline.' '.($linePoints->last()['x'] ?? $lineLeft).','.($lineTop + $lineChartH).' '.$lineLeft.','.($lineTop + $lineChartH);
    $lineBaselineY = $lineTop + $lineChartH;

    $barLeft = 140; $barRight = 60; $barTop = 8; $barGap = 14; $barHeight = 24;
    $barW = 700;
    $barChartW = $barW - $barLeft - $barRight;
    $barItems = collect($topProducts)->values()->map(function ($row, $i) use ($barLeft, $barChartW, $barTop, $barGap, $barHeight, $topProductsScale) {
        $width = $topProductsScale['max'] > 0 ? ($row->total_qty / $topProductsScale['max']) * $barChartW : 0;

        return [
            'label' => $row->product?->name ?? __('(deleted product)'),
            'value' => $row->total_qty,
            'y' => $barTop + $i * ($barHeight + $barGap),
            'width' => round($width, 2),
        ];
    });
    $barH = $barItems->isEmpty() ? 80 : $barTop + $barItems->count() * ($barHeight + $barGap);
@endphp

<style>
    .viz-root {
        --series-1: #2a78d6;
        --text-secondary: #52514e;
        --text-muted: #898781;
        --grid: #e1e0d9;
        --baseline: #c3c2b7;
        --surface: #ffffff;
    }
    .dark .viz-root {
        --series-1: #3987e5;
        --text-secondary: #c3c2b7;
        --text-muted: #898781;
        --grid: #2c2c2a;
        --baseline: #383835;
        --surface: #111827;
    }
</style>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ __('Dashboard') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get a quick overview of sales performance, popular products, and stock levels.') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white shadow-sm rounded-lg p-6 dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __("Today's Sales") }}</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($todaySales, 2) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-6 dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __("This Month's Sales") }}</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($monthSales, 2) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-6 dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Low Stock Items') }}</p>
                    <p @class(['mt-1 text-3xl font-semibold', 'text-red-600 dark:text-red-400' => $lowStockCount > 0, 'text-gray-900 dark:text-gray-100' => $lowStockCount === 0])>{{ $lowStockCount }}</p>
                </div>
            </div>

            <!-- Sales trend (line chart) -->
            <div class="bg-white shadow-sm rounded-lg p-6 viz-root dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('Sales — Last 14 Days') }}</h3>
                <p class="text-xs text-gray-400 dark:text-gray-600 mb-3">{{ __('Completed sales, by day') }}</p>

                <div class="relative" data-chart="line">
                    <svg viewBox="0 0 {{ $lineW }} {{ $lineH }}" class="w-full h-auto" data-line-svg
                         data-left="{{ $lineLeft }}" data-right="{{ $lineW - $lineRight }}" data-top="{{ $lineTop }}" data-bottom="{{ $lineBaselineY }}">
                        @foreach ($salesTrendScale['ticks'] as $tick)
                            @php $ty = $lineTop + $lineChartH - ($salesTrendScale['max'] > 0 ? ($tick / $salesTrendScale['max']) * $lineChartH : 0); @endphp
                            <line x1="{{ $lineLeft }}" y1="{{ $ty }}" x2="{{ $lineW - $lineRight }}" y2="{{ $ty }}" stroke="var(--grid)" stroke-width="1" />
                            <text x="{{ $lineLeft - 8 }}" y="{{ $ty + 3 }}" text-anchor="end" font-size="10" fill="var(--text-muted)">{{ number_format($tick, 0) }}</text>
                        @endforeach

                        @foreach ($linePoints as $i => $p)
                            @if ($lineCount <= 7 || $i === 0 || $i === $lineCount - 1 || $i % 2 === 0)
                                <text x="{{ $p['x'] }}" y="{{ $lineH - 8 }}" text-anchor="middle" font-size="10" fill="var(--text-muted)">{{ $p['label'] }}</text>
                            @endif
                        @endforeach

                        <polygon points="{{ $lineAreaPoints }}" fill="var(--series-1)" fill-opacity="0.1" stroke="none" />
                        <polyline points="{{ $linePolyline }}" fill="none" stroke="var(--series-1)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                        @if ($linePoints->isNotEmpty())
                            @php $last = $linePoints->last(); @endphp
                            <circle cx="{{ $last['x'] }}" cy="{{ $last['y'] }}" r="6" fill="var(--surface)" />
                            <circle cx="{{ $last['x'] }}" cy="{{ $last['y'] }}" r="4" fill="var(--series-1)" />
                            <text x="{{ $last['x'] - 6 }}" y="{{ max($last['y'] - 10, 10) }}" text-anchor="end" font-size="11" font-weight="600" fill="var(--text-secondary)">{{ number_format($last['total'], 2) }}</text>
                        @endif

                        <line data-crosshair x1="0" y1="{{ $lineTop }}" x2="0" y2="{{ $lineBaselineY }}" stroke="var(--baseline)" stroke-width="1" style="display:none" />
                        <circle data-hover-dot r="4" fill="var(--series-1)" stroke="var(--surface)" stroke-width="2" style="display:none" />
                    </svg>
                    <div data-tooltip class="pointer-events-none absolute hidden rounded-md bg-gray-900 px-2.5 py-1.5 text-xs text-white shadow-lg z-10"></div>
                    <script type="application/json" data-line-points>@json($linePoints->values())</script>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top-selling products (bar chart) -->
                <div class="bg-white shadow-sm rounded-lg p-6 viz-root dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('Top 5 Best-Selling Products') }}</h3>

                    @if ($barItems->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">{{ __('No sales yet.') }}</p>
                    @else
                        <div class="relative" data-chart="bar">
                            <svg viewBox="0 0 {{ $barW }} {{ $barH }}" class="w-full h-auto">
                                @foreach ($barItems as $bar)
                                    <text x="{{ $barLeft - 10 }}" y="{{ $bar['y'] + $barHeight / 2 + 4 }}" text-anchor="end" font-size="12" fill="var(--text-secondary)">{{ \Illuminate\Support\Str::limit($bar['label'], 16) }}</text>
                                    <rect
                                        data-bar
                                        data-label="{{ $bar['label'] }}"
                                        data-value="{{ $bar['value'] }}"
                                        x="{{ $barLeft }}" y="{{ $bar['y'] }}" width="{{ max($bar['width'], 2) }}" height="{{ $barHeight }}" rx="4"
                                        fill="var(--series-1)"
                                    />
                                    <text x="{{ $barLeft + $bar['width'] + 8 }}" y="{{ $bar['y'] + $barHeight / 2 + 4 }}" font-size="12" font-weight="600" fill="var(--text-secondary)">{{ $bar['value'] }}</text>
                                @endforeach
                            </svg>
                            <div data-tooltip class="pointer-events-none absolute hidden rounded-md bg-gray-900 px-2.5 py-1.5 text-xs text-white shadow-lg z-10"></div>
                        </div>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 px-6 pt-6">{{ __('Low Stock Alert') }} <span class="text-xs text-gray-400 dark:text-gray-600 font-normal">({{ __('at or below :n', ['n' => $lowStockThreshold]) }})</span></h3>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 mt-4">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Product') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Stock') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($lowStockProducts as $product)
                                <tr>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        <a href="{{ route('products.show', $product) }}" class="hover:underline">{{ $product->name }}</a>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right font-medium text-red-600 dark:text-red-400">{{ $product->stock_qty }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('All products are well stocked.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function svgPoint(svg, clientX, clientY) {
            const pt = svg.createSVGPoint();
            pt.x = clientX;
            pt.y = clientY;
            return pt.matrixTransform(svg.getScreenCTM().inverse());
        }

        document.querySelectorAll('[data-chart="line"]').forEach((container) => {
            const svg = container.querySelector('[data-line-svg]');
            const tooltip = container.querySelector('[data-tooltip]');
            const crosshair = svg.querySelector('[data-crosshair]');
            const hoverDot = svg.querySelector('[data-hover-dot]');
            const points = JSON.parse(container.querySelector('[data-line-points]').textContent);
            if (!points.length) return;

            const showTooltip = (point, evt) => {
                const rect = container.getBoundingClientRect();
                tooltip.textContent = point.label + ': ' + point.total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                tooltip.classList.remove('hidden');
                tooltip.style.left = Math.min(evt.clientX - rect.left + 10, rect.width - tooltip.offsetWidth - 4) + 'px';
                tooltip.style.top = (evt.clientY - rect.top - 36) + 'px';
            };

            svg.addEventListener('pointermove', (evt) => {
                const p = svgPoint(svg, evt.clientX, evt.clientY);
                let nearest = points[0];
                let nearestDist = Math.abs(points[0].x - p.x);
                for (const candidate of points) {
                    const dist = Math.abs(candidate.x - p.x);
                    if (dist < nearestDist) {
                        nearest = candidate;
                        nearestDist = dist;
                    }
                }

                crosshair.setAttribute('x1', nearest.x);
                crosshair.setAttribute('x2', nearest.x);
                crosshair.style.display = '';
                hoverDot.setAttribute('cx', nearest.x);
                hoverDot.setAttribute('cy', nearest.y);
                hoverDot.style.display = '';
                showTooltip(nearest, evt);
            });

            svg.addEventListener('pointerleave', () => {
                crosshair.style.display = 'none';
                hoverDot.style.display = 'none';
                tooltip.classList.add('hidden');
            });
        });

        document.querySelectorAll('[data-chart="bar"]').forEach((container) => {
            const tooltip = container.querySelector('[data-tooltip]');

            container.querySelectorAll('[data-bar]').forEach((bar) => {
                bar.addEventListener('pointermove', (evt) => {
                    const rect = container.getBoundingClientRect();
                    tooltip.textContent = bar.dataset.label + ': ' + bar.dataset.value;
                    tooltip.classList.remove('hidden');
                    tooltip.style.left = Math.min(evt.clientX - rect.left + 10, rect.width - tooltip.offsetWidth - 4) + 'px';
                    tooltip.style.top = (evt.clientY - rect.top - 32) + 'px';
                    bar.setAttribute('fill-opacity', '0.85');
                });
                bar.addEventListener('pointerleave', () => {
                    tooltip.classList.add('hidden');
                    bar.removeAttribute('fill-opacity');
                });
            });
        });
    </script>
</x-app-layout>

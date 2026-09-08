<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;

class DashboardController extends Controller
{
    public function index()
    {
        $todaySales = Sale::query()
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereDate('created_at', today())
            ->sum('total');

        $monthSales = Sale::query()
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        $topProducts = SaleItem::bestSellers(limit: 5);

        $lowStockThreshold = (int) Setting::get('low_stock_threshold', '5');
        $lowStockCount = Product::query()->lowStock($lowStockThreshold)->count();
        $lowStockProducts = Product::query()
            ->lowStock($lowStockThreshold)
            ->orderBy('stock_qty')
            ->limit(10)
            ->get();

        $salesTrend = $this->salesTrend(14);
        $salesTrendScale = $this->niceScale((float) collect($salesTrend)->max('total'));
        $topProductsScale = $this->niceScale((float) $topProducts->max('total_qty'));

        return view('dashboard', compact(
            'todaySales', 'monthSales', 'topProducts', 'lowStockProducts', 'lowStockThreshold', 'lowStockCount',
            'salesTrend', 'salesTrendScale', 'topProductsScale'
        ));
    }

    /**
     * Completed-sales totals for each of the last $days days (including today),
     * with zero-filled gaps so the line chart has one continuous series.
     */
    private function salesTrend(int $days): array
    {
        $from = today()->subDays($days - 1);

        $totalsByDate = Sale::query()
            ->where('status', Sale::STATUS_COMPLETED)
            ->where('created_at', '>=', $from)
            ->get()
            ->groupBy(fn (Sale $sale) => $sale->created_at->toDateString())
            ->map(fn ($sales) => (float) $sales->sum('total'));

        return collect(range(0, $days - 1))
            ->map(function (int $i) use ($from, $totalsByDate) {
                $date = $from->copy()->addDays($i);

                return [
                    'label' => $date->format('M j'),
                    'total' => $totalsByDate->get($date->toDateString(), 0.0),
                ];
            })
            ->all();
    }

    /**
     * A "nice" rounded axis max/step/ticks for a chart, so gridlines land on
     * clean numbers (0, 25, 50...) instead of the raw data max.
     */
    private function niceScale(float $max, int $tickCount = 4): array
    {
        if ($max <= 0) {
            return ['max' => $tickCount, 'step' => 1, 'ticks' => range(0, $tickCount)];
        }

        $roughStep = $max / $tickCount;
        $magnitude = 10 ** floor(log10($roughStep));
        $residual = $roughStep / $magnitude;

        $niceResidual = match (true) {
            $residual <= 1 => 1,
            $residual <= 2 => 2,
            $residual <= 5 => 5,
            default => 10,
        };

        $step = $niceResidual * $magnitude;
        $niceMax = $step * $tickCount;

        while ($niceMax < $max) {
            $niceMax += $step;
        }

        $tickTotal = (int) round($niceMax / $step);

        return [
            'max' => $niceMax,
            'step' => $step,
            'ticks' => array_map(fn ($i) => $step * $i, range(0, $tickTotal)),
        ];
    }
}

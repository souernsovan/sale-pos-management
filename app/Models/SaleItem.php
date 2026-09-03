<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Units sold per product for completed sales, optionally within a date range.
     */
    public static function bestSellers(?string $dateFrom = null, ?string $dateTo = null, ?int $limit = null)
    {
        return static::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', Sale::STATUS_COMPLETED)
                    ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                    ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo));
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get();
    }

    /**
     * Profit (unit_price - product cost) per product for completed sales in a date range.
     */
    public static function profitByProduct(?string $dateFrom = null, ?string $dateTo = null)
    {
        return static::query()
            ->with('product')
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', Sale::STATUS_COMPLETED)
                    ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                    ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo));
            })
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;
                $quantity = $items->sum('quantity');
                $revenue = $items->sum('subtotal');
                $cost = $items->sum(fn (self $item) => (float) ($product?->cost ?? 0) * $item->quantity);

                return (object) [
                    'product' => $product,
                    'quantity' => $quantity,
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $revenue - $cost,
                ];
            })
            ->sortByDesc('profit')
            ->values();
    }
}

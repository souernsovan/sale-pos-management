<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'sku',
        'barcode',
        'price',
        'cost',
        'stock_qty',
        'reorder_point',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    /**
     * A product's own reorder point overrides the shop-wide low-stock
     * threshold when set.
     */
    public function isLowStock(int $threshold = 5): bool
    {
        return $this->stock_qty <= ($this->reorder_point ?? $threshold);
    }

    /**
     * Products at or below their effective reorder point (their own
     * `reorder_point`, falling back to the shop-wide $threshold). Portable
     * SQL (COALESCE) — works the same on MySQL and Postgres.
     */
    public function scopeLowStock(Builder $query, int $threshold): Builder
    {
        return $query->whereRaw('stock_qty <= COALESCE(reorder_point, ?)', [$threshold]);
    }
}

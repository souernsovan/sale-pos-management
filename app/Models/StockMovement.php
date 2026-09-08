<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const TYPE_RESTOCK = 'restock';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_DAMAGE = 'damage';
    public const TYPE_SALE = 'sale';
    public const TYPE_VOID = 'void';
    public const TYPE_PURCHASE = 'purchase';

    protected $fillable = [
        'product_id',
        'sale_id',
        'purchase_id',
        'type',
        'quantity',
        'note',
        'created_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * IN / OUT / ADJUSTMENT grouping for the audit-trail UI, derived from the
     * more specific `type` values the app actually stores.
     */
    public function direction(): string
    {
        return match ($this->type) {
            self::TYPE_RESTOCK, self::TYPE_PURCHASE, self::TYPE_VOID => 'in',
            self::TYPE_SALE => 'out',
            self::TYPE_ADJUSTMENT, self::TYPE_DAMAGE => 'adjustment',
            default => $this->quantity >= 0 ? 'in' : 'out',
        };
    }
}

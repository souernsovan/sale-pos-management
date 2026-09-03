<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Sale extends Model
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_VOIDED = 'voided';

    protected $fillable = [
        'customer_id',
        'user_id',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isVoided(): bool
    {
        return $this->status === self::STATUS_VOIDED;
    }

    /**
     * Completed sales totals grouped by day/week/month, done in PHP (not raw SQL date
     * functions) so it behaves the same on MySQL in production and SQLite in tests.
     */
    public static function salesGroupedBy(string $groupBy, ?string $dateFrom, ?string $dateTo): Collection
    {
        $sales = static::query()
            ->where('status', self::STATUS_COMPLETED)
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->get();

        return $sales
            ->groupBy(fn (self $sale) => match ($groupBy) {
                'weekly' => $sale->created_at->format('o-\WW'),
                'monthly' => $sale->created_at->format('Y-m'),
                default => $sale->created_at->format('Y-m-d'),
            })
            ->map(fn ($group, $period) => (object) [
                'period' => $period,
                'sales_count' => $group->count(),
                'total' => $group->sum('total'),
            ])
            ->sortKeys()
            ->values();
    }
}

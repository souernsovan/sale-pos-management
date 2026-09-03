<?php

namespace App\Exports;

use App\Models\SaleItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BestSellersExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected ?string $dateFrom,
        protected ?string $dateTo,
    ) {
    }

    public function collection(): Collection
    {
        return SaleItem::bestSellers($this->dateFrom, $this->dateTo)
            ->map(fn ($row) => [
                'product' => $row->product?->name ?? '(deleted product)',
                'units_sold' => $row->total_qty,
            ]);
    }

    public function headings(): array
    {
        return ['Product', 'Units Sold'];
    }
}

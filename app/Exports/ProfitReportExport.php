<?php

namespace App\Exports;

use App\Models\SaleItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProfitReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected ?string $dateFrom,
        protected ?string $dateTo,
    ) {
    }

    public function collection(): Collection
    {
        return SaleItem::profitByProduct($this->dateFrom, $this->dateTo)
            ->map(fn ($row) => [
                'product' => $row->product?->name ?? '(deleted product)',
                'quantity' => $row->quantity,
                'revenue' => (float) $row->revenue,
                'cost' => (float) $row->cost,
                'profit' => (float) $row->profit,
            ]);
    }

    public function headings(): array
    {
        return ['Product', 'Units Sold', 'Revenue', 'Cost', 'Profit'];
    }
}

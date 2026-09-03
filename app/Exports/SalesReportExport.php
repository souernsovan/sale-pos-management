<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected string $groupBy,
        protected ?string $dateFrom,
        protected ?string $dateTo,
    ) {
    }

    public function collection(): Collection
    {
        return Sale::salesGroupedBy($this->groupBy, $this->dateFrom, $this->dateTo)
            ->map(fn ($row) => [
                'period' => $row->period,
                'sales_count' => $row->sales_count,
                'total' => (float) $row->total,
            ]);
    }

    public function headings(): array
    {
        return ['Period', 'Number of Sales', 'Total'];
    }
}

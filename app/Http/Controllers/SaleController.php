<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Setting;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::query()
            ->with(['user', 'customer'])
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'user', 'customer']);

        return view('sales.show', compact('sale'));
    }

    public function void(Sale $sale)
    {
        abort_if($sale->isVoided(), 422, 'Sale is already voided.');

        DB::transaction(function () use ($sale) {
            $sale->load('items');

            foreach ($sale->items as $item) {
                $item->product?->increment('stock_qty', $item->quantity);
                $item->product?->stockMovements()->create([
                    'sale_id' => $sale->id,
                    'type' => StockMovement::TYPE_VOID,
                    'quantity' => $item->quantity,
                    'note' => "Void of sale #{$sale->id}",
                    'created_by' => auth()->id(),
                ]);
            }

            $sale->update(['status' => Sale::STATUS_VOIDED]);
        });

        return back()->with('status', 'Sale voided and stock restored.');
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['items.product', 'user', 'customer']);
        $shop = $this->shopInfo();

        return view('sales.receipt', compact('sale', 'shop'));
    }

    public function receiptPdf(Sale $sale)
    {
        $sale->load(['items.product', 'user', 'customer']);
        $shop = $this->shopInfo();

        return Pdf::loadView('sales.receipt', compact('sale', 'shop'))->stream("receipt-{$sale->id}.pdf");
    }

    private function shopInfo(): array
    {
        return [
            'name' => Setting::get('shop_name', config('app.name')),
            'address' => Setting::get('shop_address'),
        ];
    }
}

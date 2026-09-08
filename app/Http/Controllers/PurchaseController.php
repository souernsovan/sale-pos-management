<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::query()
            ->withCount('items')
            ->with(['supplier', 'user'])
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('purchased_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('purchased_at', '<=', $request->date_to))
            ->latest('purchased_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('create purchases'), 403);

        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'cost']);

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseRequest $request)
    {
        abort_unless($request->user()->can('create purchases'), 403);

        $data = $request->validated();

        $purchase = DB::transaction(function () use ($data) {
            $products = Product::whereIn('id', array_column($data['items'], 'product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = collect($data['items'])->sum(fn ($item) => $item['quantity'] * $item['cost_price']);

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => auth()->id(),
                'purchased_at' => $data['purchased_at'],
                'notes' => $data['notes'] ?? null,
                'total' => $total,
            ]);

            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']];
                $lineSubtotal = $item['quantity'] * $item['cost_price'];

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'subtotal' => $lineSubtotal,
                ]);

                $product->increment('stock_qty', $item['quantity']);
                $product->update(['cost' => $item['cost_price']]);

                $product->stockMovements()->create([
                    'purchase_id' => $purchase->id,
                    'type' => StockMovement::TYPE_PURCHASE,
                    'quantity' => $item['quantity'],
                    'note' => "Purchase #{$purchase->id}",
                    'created_by' => auth()->id(),
                ]);
            }

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)->with('status', 'Purchase recorded — stock and cost updated.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }
}

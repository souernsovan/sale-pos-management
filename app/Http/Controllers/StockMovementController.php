<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function store(Request $request, Product $product)
    {
        abort_unless($request->user()->can('manage products'), 403);

        $data = $request->validate([
            'type' => ['required', 'in:restock,adjustment,damage'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $signedQuantity = $data['type'] === 'restock' ? $data['quantity'] : -$data['quantity'];

        if ($signedQuantity < 0 && $product->stock_qty + $signedQuantity < 0) {
            return back()->withErrors(['quantity' => 'Quantity exceeds current stock on hand.']);
        }

        DB::transaction(function () use ($product, $data, $signedQuantity) {
            $product->increment('stock_qty', $signedQuantity);
            $product->stockMovements()->create([
                'type' => $data['type'],
                'quantity' => $signedQuantity,
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('status', 'Stock updated.');
    }
}

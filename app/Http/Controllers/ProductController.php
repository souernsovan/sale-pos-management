<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with('category')
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%")
                    ->orWhere('barcode', 'like', "%{$request->search}%");
            }))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $lowStockThreshold = (int) Setting::get('low_stock_threshold', '5');

        return view('products.index', compact('products', 'categories', 'lowStockThreshold'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('create products'), 403);

        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        abort_unless($request->user()->can('create products'), 403);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $initialStock = (int) ($data['stock_qty'] ?? 0);
        unset($data['stock_qty']);

        $product = DB::transaction(function () use ($data, $initialStock) {
            $product = Product::create($data + ['stock_qty' => 0]);

            $product->update(['barcode' => $data['barcode'] ?? sprintf('PRD%08d', $product->id)]);

            if ($initialStock > 0) {
                $product->increment('stock_qty', $initialStock);
                $product->stockMovements()->create([
                    'type' => StockMovement::TYPE_RESTOCK,
                    'quantity' => $initialStock,
                    'note' => 'Initial stock on product creation',
                    'created_by' => auth()->id(),
                ]);
            }

            return $product;
        });

        return redirect()->route('products.show', $product)->with('status', 'Product created.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'stockMovements.creator']);
        $lowStockThreshold = (int) Setting::get('low_stock_threshold', '5');

        return view('products.show', compact('product', 'lowStockThreshold'));
    }

    public function edit(Request $request, Product $product)
    {
        abort_unless($request->user()->can('edit products'), 403);

        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        abort_unless($request->user()->can('edit products'), 403);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.show', $product)->with('status', 'Product updated.');
    }

    public function destroy(Request $request, Product $product)
    {
        abort_unless($request->user()->can('delete products'), 403);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('status', 'Product deleted.');
    }

    public function barcode(Product $product)
    {
        abort_unless($product->barcode, 404);

        return view('products.barcode', compact('product'));
    }
}

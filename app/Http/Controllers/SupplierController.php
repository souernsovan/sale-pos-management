<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Support\Audit;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount(['products', 'purchases'])->orderBy('name')->paginate(15);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('create suppliers'), 403);

        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request)
    {
        abort_unless($request->user()->can('create suppliers'), 403);

        Supplier::create($request->validated());

        return redirect()->route('suppliers.index')->with('status', 'Supplier created.');
    }

    public function edit(Request $request, Supplier $supplier)
    {
        abort_unless($request->user()->can('edit suppliers'), 403);

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        abort_unless($request->user()->can('edit suppliers'), 403);

        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')->with('status', 'Supplier updated.');
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        abort_unless($request->user()->can('delete suppliers'), 403);

        Audit::log('suppliers', "Deleted supplier \"{$supplier->name}\"", $supplier, event: 'deleted');

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('status', 'Supplier deleted.');
    }
}

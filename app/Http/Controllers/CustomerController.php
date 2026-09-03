<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('create customers'), 403);

        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        abort_unless($request->user()->can('create customers'), 403);

        $customer = Customer::create($request->validated());

        return redirect()->route('customers.show', $customer)->with('status', 'Customer created.');
    }

    public function show(Customer $customer)
    {
        $sales = $customer->sales()->latest()->paginate(10);

        return view('customers.show', compact('customer', 'sales'));
    }

    public function edit(Request $request, Customer $customer)
    {
        abort_unless($request->user()->can('edit customers'), 403);

        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        abort_unless($request->user()->can('edit customers'), 403);

        $customer->update($request->validated());

        return redirect()->route('customers.show', $customer)->with('status', 'Customer updated.');
    }

    public function destroy(Request $request, Customer $customer)
    {
        abort_unless($request->user()->can('delete customers'), 403);

        $customer->delete();

        return redirect()->route('customers.index')->with('status', 'Customer deleted.');
    }
}

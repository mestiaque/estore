<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Customer;
use ME\Retail\Models\Membership;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_customer.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_customer.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_customer.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_customer.delete')->only('destroy');
    }

    public function index()
    {
        $customers = Customer::latest()->paginate(get_setting('pagination', 10));
        return view('retail::customers.index', compact('customers'));
    }

    public function create()
    {
        $memberships = Membership::where('status', true)->get();
        return view('retail::customers.create', compact('memberships'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            Customer::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.customers.index')->with('error', __('Failed to create customer'));
        }

        return redirect()->route('retail.customers.index')->with('success', __('Customer created successfully'));
    }

    public function show(Customer $customer)
    {
        $transactions = $customer->transactions()->latest('id')->paginate(get_setting('pagination', 10));
        $sales = $customer->sales()->latest('sale_date')->latest('id')->paginate(get_setting('pagination', 10), ['*'], 'sales_page');
        return view('retail::customers.show', compact('customer', 'transactions', 'sales'));
    }

    public function edit(Customer $customer)
    {
        $memberships = Membership::where('status', true)->get();
        return view('retail::customers.edit', compact('customer', 'memberships'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $this->validateData($request, $customer->id);

        DB::beginTransaction();
        try {
            $customer->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.customers.index')->with('error', __('Failed to update customer'));
        }

        return redirect()->route('retail.customers.index')->with('success', __('Customer updated successfully'));
    }

    public function destroy(Customer $customer)
    {
        DB::beginTransaction();
        try {
            $customer->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.customers.index')->with('error', __('Failed to delete customer'));
        }

        return redirect()->route('retail.customers.index')->with('success', __('Customer deleted successfully'));
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'customer_code'   => 'nullable|string|max:100|unique:retail_customers,customer_code,' . $id,
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
            'credit_limit'    => 'nullable|numeric|min:0',
            'membership_id'          => 'nullable|exists:retail_memberships,id',
            'membership_expires_at'  => 'nullable|date',
            'status'          => 'nullable|boolean',
            'notes'           => 'nullable|string',
        ]);

        return [
            'name'                   => $request->name,
            'customer_code'          => $request->customer_code,
            'phone'                  => $request->phone,
            'email'                  => $request->email,
            'address'                => $request->address,
            'opening_balance'        => $request->opening_balance ?: 0,
            'credit_limit'           => $request->credit_limit,
            'membership_id'          => $request->membership_id ?: null,
            'membership_expires_at'  => $request->membership_expires_at ?: null,
            'status'                 => $request->has('status') ? 1 : 0,
            'notes'                  => $request->notes,
        ];
    }
}

<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\CustomerLevel;

class CustomerLevelController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_customer_level.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_customer_level.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_customer_level.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_customer_level.delete')->only('destroy');
    }

    public function index()
    {
        $levels = CustomerLevel::orderBy('min_points')->paginate(get_setting('pagination', 10));
        return view('retail::customer-levels.index', compact('levels'));
    }

    public function create()
    {
        return view('retail::customer-levels.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            CustomerLevel::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.customer-levels.index')->with('error', __('Failed to create customer level'));
        }

        return redirect()->route('retail.customer-levels.index')->with('success', __('Customer level created successfully'));
    }

    public function show(CustomerLevel $customerLevel)
    {
        return view('retail::customer-levels.show', ['level' => $customerLevel]);
    }

    public function edit(CustomerLevel $customerLevel)
    {
        return view('retail::customer-levels.edit', ['level' => $customerLevel]);
    }

    public function update(Request $request, CustomerLevel $customerLevel)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            $customerLevel->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.customer-levels.index')->with('error', __('Failed to update customer level'));
        }

        return redirect()->route('retail.customer-levels.index')->with('success', __('Customer level updated successfully'));
    }

    public function destroy(CustomerLevel $customerLevel)
    {
        DB::beginTransaction();
        try {
            $customerLevel->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.customer-levels.index')->with('error', __('Failed to delete customer level'));
        }

        return redirect()->route('retail.customer-levels.index')->with('success', __('Customer level deleted successfully'));
    }

    private function validateData(Request $request): array
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'min_points'        => 'required|numeric|min:0',
            'discount_percent'  => 'nullable|numeric|min:0|max:100',
            'point_multiplier'  => 'nullable|numeric|min:0',
            'sl'                => 'nullable|integer|min:0',
            'status'            => 'nullable|boolean',
        ]);

        return [
            'name'             => $request->name,
            'min_points'       => $request->min_points,
            'discount_percent' => $request->discount_percent ?: 0,
            'point_multiplier' => $request->point_multiplier ?: 1,
            'sl'               => $request->sl ?: 0,
            'status'           => $request->has('status') ? 1 : 0,
        ];
    }
}

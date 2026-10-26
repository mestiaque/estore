<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Enums\DiscountRuleType;
use ME\Retail\Models\Brand;
use ME\Retail\Models\Category;
use ME\Retail\Models\DiscountRule;
use ME\Retail\Models\Product;

class DiscountRuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_discount_rule.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_discount_rule.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_discount_rule.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_discount_rule.delete')->only('destroy');
    }

    public function index()
    {
        $rules = DiscountRule::orderBy('sl')->paginate(get_setting('pagination', 10));
        return view('retail::discount-rules.index', compact('rules'));
    }

    public function create()
    {
        return view('retail::discount-rules.create', $this->targetOptions());
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            DiscountRule::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.discount-rules.index')->with('error', __('Failed to create discount rule'));
        }

        return redirect()->route('retail.discount-rules.index')->with('success', __('Discount rule created successfully'));
    }

    public function show(DiscountRule $discountRule)
    {
        return view('retail::discount-rules.show', ['rule' => $discountRule]);
    }

    public function edit(DiscountRule $discountRule)
    {
        return view('retail::discount-rules.edit', ['rule' => $discountRule] + $this->targetOptions());
    }

    public function update(Request $request, DiscountRule $discountRule)
    {
        $data = $this->validateData($request, $discountRule->id);

        DB::beginTransaction();
        try {
            $discountRule->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.discount-rules.index')->with('error', __('Failed to update discount rule'));
        }

        return redirect()->route('retail.discount-rules.index')->with('success', __('Discount rule updated successfully'));
    }

    public function destroy(DiscountRule $discountRule)
    {
        DB::beginTransaction();
        try {
            $discountRule->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.discount-rules.index')->with('error', __('Failed to delete discount rule'));
        }

        return redirect()->route('retail.discount-rules.index')->with('success', __('Discount rule deleted successfully'));
    }

    private function targetOptions(): array
    {
        return [
            'products'   => Product::where('status', true)->get(),
            'categories' => Category::where('status', true)->get(),
            'brands'     => Brand::where('status', true)->get(),
        ];
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:' . implode(',', array_column(DiscountRuleType::cases(), 'value')),
            'target_id'       => 'nullable|integer',
            'coupon_code'     => 'nullable|string|max:100|unique:retail_discount_rules,coupon_code,' . $id,
            'min_quantity'    => 'nullable|numeric|min:0',
            'discount_type'   => 'required|in:percent,flat',
            'discount_value'  => 'required|numeric|min:0',
            'starts_at'       => 'nullable|date',
            'ends_at'         => 'nullable|date|after_or_equal:starts_at',
            'max_uses'        => 'nullable|integer|min:1',
            'sl'              => 'nullable|integer|min:0',
            'status'          => 'nullable|boolean',
        ]);

        return [
            'name'           => $request->name,
            'type'           => $request->type,
            'target_id'      => in_array($request->type, ['product', 'category', 'brand']) ? $request->target_id : null,
            'coupon_code'    => $request->type === 'coupon' ? $request->coupon_code : null,
            'min_quantity'   => $request->type === 'quantity' ? $request->min_quantity : null,
            'discount_type'  => $request->discount_type,
            'discount_value' => $request->discount_value,
            'starts_at'      => $request->starts_at ?: null,
            'ends_at'        => $request->ends_at ?: null,
            'max_uses'       => $request->max_uses ?: null,
            'sl'             => $request->sl ?: 0,
            'status'         => $request->has('status') ? 1 : 0,
        ];
    }
}

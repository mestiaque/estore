<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\HeldSale;

/**
 * Parks an unfinished POS cart so the cashier can serve the next customer, and
 * discards parked carts. Resuming happens on the sale form itself
 * (retail.sales.create?resume={id}); the held cart is removed once that sale
 * is completed. Nothing here touches stock, ledgers or payments.
 */
class HeldSaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_sale.create');
    }

    public function store(Request $request)
    {
        // The sale form always carries a blank trailing line; only lines with a product are held.
        $items = collect($request->input('items', []))
            ->filter(fn ($item) => is_array($item) && !empty($item['product_id']))
            ->values()
            ->all();
        $request->merge(['items' => $items]);

        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:retail_products,id',
            'items.*.unit_id'    => 'nullable|exists:retail_units,id',
            'items.*.quantity'   => 'nullable|numeric|min:0',
            'items.*.sale_price' => 'nullable|numeric|min:0',
            'customer_id'         => 'nullable|exists:retail_customers,id',
            'warehouse_id'        => 'nullable|exists:retail_warehouses,id',
            'hold_reference'      => 'nullable|string|max:255',
            'held_sale_id'        => 'nullable|exists:retail_held_sales,id',
        ], [
            'items.required' => __('Add at least one item before holding the sale.'),
        ]);

        $estimatedTotal = collect($items)->sum(fn ($item) =>
            (float) ($item['quantity'] ?? 0) * (float) ($item['sale_price'] ?? 0)
            - (float) ($item['discount'] ?? 0)
            + (float) ($item['tax'] ?? 0)
        );

        $attributes = [
            'customer_id'     => $request->customer_id ?: null,
            'warehouse_id'    => $request->warehouse_id ?: null,
            'reference'       => $request->hold_reference,
            'payload'         => $request->only(['customer_id', 'store_id', 'warehouse_id', 'sale_date', 'discount', 'tax', 'coupon_code', 'notes', 'items']),
            'item_count'      => count($items),
            'estimated_total' => $estimatedTotal,
            'user_id'         => auth()->id(),
        ];

        // Re-holding a resumed cart updates it instead of parking a second copy.
        $heldSale = $request->held_sale_id ? HeldSale::find($request->held_sale_id) : null;
        $heldSale ? $heldSale->update($attributes) : HeldSale::create($attributes);

        return redirect()->route('retail.sales.create')->with('success', __('Sale held. Resume it from the held sales list.'));
    }

    public function destroy(HeldSale $heldSale)
    {
        $heldSale->delete();

        return redirect()->route('retail.sales.create')->with('success', __('Held sale discarded'));
    }
}

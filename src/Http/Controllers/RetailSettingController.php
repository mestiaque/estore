<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Models\Setting;
use ME\Retail\Support\RetailModules;

/**
 * Master-level loyalty settings — how much one point is worth in currency,
 * and the maximum share of an invoice's total that points may cover. Stored
 * in Metheme's own key/value `settings` table (same mechanism its own
 * SettingController uses) rather than a Retail-only table, so no migration
 * is needed and it survives cache-clears the same way every other setting does.
 */
class RetailSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_setting.view')->only(['edit', 'modules']);
        $this->middleware('authorization:retail_setting.edit')->only(['update', 'updateModules']);
    }

    public function edit()
    {
        $pointValue   = get_setting('retail_loyalty_point_value', config('retail.loyalty_point_value', 0.1));
        $maxRedeemPct = get_setting('retail_loyalty_max_redeem_percent', config('retail.loyalty_max_redeem_percent', 50));

        return view('retail::settings.loyalty', compact('pointValue', 'maxRedeemPct'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'point_value'        => 'required|numeric|min:0.0001',
            'max_redeem_percent' => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            Setting::set('retail_loyalty_point_value', (float) $request->point_value);
            Setting::set('retail_loyalty_max_redeem_percent', (float) $request->max_redeem_percent);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.settings.loyalty.edit')->with('error', __('Failed to update loyalty settings'));
        }

        return redirect()->route('retail.settings.loyalty.edit')->with('success', __('Loyalty settings updated successfully'));
    }

    public function modules()
    {
        return view('retail::settings.modules', [
            'discountEnabled' => RetailModules::discountEnabled(),
            'loyaltyEnabled'  => RetailModules::loyaltyEnabled(),
            'emiEnabled'      => RetailModules::emiEnabled(),
        ]);
    }

    public function updateModules(Request $request)
    {
        DB::beginTransaction();
        try {
            Setting::set('retail_enable_discount_module', $request->has('enable_discount'));
            Setting::set('retail_enable_loyalty_module', $request->has('enable_loyalty'));
            Setting::set('retail_enable_emi_module', $request->has('enable_emi'));
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.settings.modules.edit')->with('error', __('Failed to update module settings'));
        }

        return redirect()->route('retail.settings.modules.edit')->with('success', __('Module settings updated successfully'));
    }
}

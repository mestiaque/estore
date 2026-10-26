<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use ME\Http\Controllers\Controller;
use ME\Models\Setting;
use ME\Retail\Services\BusinessTypeService;
use ME\Retail\Support\RetailModules;
use ME\Retail\Support\RetailSettings;

/**
 * The Retail configuration screens — feature switches, general settings,
 * loyalty settings and business-type presets. Everything is stored in
 * Metheme's own key/value `settings` table (same mechanism its own
 * SettingController uses) rather than a Retail-only table, so no migration
 * is needed and it survives cache-clears the same way every other setting does.
 */
class RetailSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_setting.view')->only(['edit', 'modules', 'general', 'businessType']);
        $this->middleware('authorization:retail_setting.edit')->only(['update', 'updateModules', 'updateGeneral', 'updateBusinessType']);
    }

    public function businessType(BusinessTypeService $businessTypes)
    {
        return view('retail::settings.business-type', [
            'types'       => $businessTypes->types(),
            'unitCatalog' => $businessTypes->unitCatalog(),
            'current'     => $businessTypes->current(),
            'customised'  => $businessTypes->isCustomised(),
            'features'    => RetailModules::definitions(),
        ]);
    }

    public function updateBusinessType(Request $request, BusinessTypeService $businessTypes)
    {
        $request->validate([
            'business_type' => ['required', 'string', Rule::in(array_keys($businessTypes->types()))],
        ]);

        try {
            $createdUnits = $businessTypes->apply($request->business_type);
        } catch (\Throwable $e) {
            return redirect()->route('retail.settings.business-type.edit')->with('error', __('Failed to apply business type'));
        }

        return redirect()->route('retail.settings.business-type.edit')->with('success', __('Business type applied. :count unit(s) created.', ['count' => $createdUnits]));
    }

    public function edit()
    {
        $pointValue   = RetailSettings::get('loyalty_point_value');
        $maxRedeemPct = RetailSettings::get('loyalty_max_redeem_percent');

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
        $features = collect(RetailModules::definitions())->map(fn (array $definition, string $key) => $definition + [
            'key'      => $key,
            'on'       => RetailModules::switchedOn($key),
            'active'   => RetailModules::enabled($key),
            'requires' => $definition['requires'] ?? [],
        ])->groupBy('group');

        return view('retail::settings.modules', [
            'groups'      => RetailModules::groups(),
            'features'    => $features,
            'definitions' => RetailModules::definitions(),
        ]);
    }

    /**
     * Every feature switch is posted as features[key]; an unchecked switch is simply absent.
     */
    public function updateModules(Request $request)
    {
        $request->validate(['features' => 'nullable|array']);

        DB::beginTransaction();
        try {
            foreach (array_keys(RetailModules::definitions()) as $feature) {
                RetailModules::set($feature, $request->boolean("features.{$feature}"));
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.settings.modules.edit')->with('error', __('Failed to update module settings'));
        }

        return redirect()->route('retail.settings.modules.edit')->with('success', __('Module settings updated successfully'));
    }

    public function general()
    {
        $settings = collect(RetailSettings::visible())
            ->map(fn (array $definition, string $key) => $definition + [
                'key'   => $key,
                'field' => RetailSettings::fieldName($key),
                'value' => RetailSettings::get($key),
            ])
            ->groupBy('group');

        return view('retail::settings.general', [
            'groups'   => RetailSettings::groups(),
            'settings' => $settings,
        ]);
    }

    /**
     * Saves the settings shown on the form; settings of switched-off features keep their value.
     */
    public function updateGeneral(Request $request)
    {
        $visible = RetailSettings::visible();
        $request->validate(RetailSettings::rules($visible));

        DB::beginTransaction();
        try {
            foreach ($visible as $key => $definition) {
                RetailSettings::set($key, RetailSettings::fromInput($key, $request->input('settings.' . RetailSettings::fieldName($key))));
            }

            // The default invoice format must be one of the offered formats.
            if (isset($visible['invoice_types'], $visible['default_invoice_type'])
                && !in_array(RetailSettings::get('default_invoice_type'), RetailSettings::get('invoice_types'), true)) {
                RetailSettings::set('default_invoice_type', RetailSettings::get('invoice_types')[0]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.settings.general.edit')->with('error', __('Failed to update settings'));
        }

        return redirect()->route('retail.settings.general.edit')->with('success', __('Settings updated successfully'));
    }
}

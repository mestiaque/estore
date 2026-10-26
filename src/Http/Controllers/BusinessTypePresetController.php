<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use ME\Http\Controllers\Controller;
use ME\Retail\Services\BusinessTypeService;
use ME\Retail\Support\RetailModules;

/**
 * Add, edit, delete and reset the business-type presets offered on the
 * Business Type screen. Editing a preset changes only what applying it does
 * from now on — it does not re-apply it or touch any existing data.
 */
class BusinessTypePresetController extends Controller
{
    public function __construct(protected BusinessTypeService $businessTypes)
    {
        $this->middleware('authorization:retail_setting.edit');
    }

    public function create()
    {
        return $this->form(null, [
            'label' => '', 'description' => '', 'track_type' => 'normal', 'has_expiry' => false, 'disabled' => [], 'units' => ['piece'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->businessTypes->save($this->businessTypes->keyFor($data['label']), $data);

        return redirect()->route('retail.settings.business-type.edit')->with('success', __('Business type preset added'));
    }

    public function edit(string $type)
    {
        $preset = $this->businessTypes->types()[$type] ?? abort(404);

        return $this->form($type, $preset);
    }

    public function update(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, $this->businessTypes->types()), 404);

        $this->businessTypes->save($type, $this->validated($request));

        return redirect()->route('retail.settings.business-type.edit')->with('success', __('Business type preset updated'));
    }

    public function destroy(string $type)
    {
        abort_unless(array_key_exists($type, $this->businessTypes->types()), 404);

        if ($this->businessTypes->current() === $type) {
            return redirect()->route('retail.settings.business-type.edit')->with('error', __('The business type in use cannot be deleted'));
        }

        $this->businessTypes->delete($type);

        return redirect()->route('retail.settings.business-type.edit')->with('success', __('Business type preset deleted'));
    }

    public function reset()
    {
        $this->businessTypes->reset();

        return redirect()->route('retail.settings.business-type.edit')->with('success', __('Business type presets restored to the defaults'));
    }

    private function form(?string $type, array $preset)
    {
        return view('retail::settings.business-type-form', [
            'type'        => $type,
            'preset'      => $preset,
            'features'    => RetailModules::definitions(),
            'groups'      => RetailModules::groups(),
            'unitCatalog' => $this->businessTypes->unitCatalog(),
        ]);
    }

    /**
     * @return array{label: string, description: ?string, track_type: string, has_expiry: bool, disabled: string[], units: string[]}
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label'       => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'track_type'  => 'required|in:normal,batch,serial',
            'has_expiry'  => 'nullable|boolean',
            'disabled'    => 'nullable|array',
            'disabled.*'  => [Rule::in(array_keys(RetailModules::definitions()))],
            'units'       => 'nullable|array',
            'units.*'     => [Rule::in(array_keys($this->businessTypes->unitCatalog()))],
        ]);

        // A preset can't default new products to a tracking type it switches off.
        if ($data['track_type'] !== 'normal' && in_array($data['track_type'], $data['disabled'] ?? [], true)) {
            $data['track_type'] = 'normal';
        }

        return $data + ['has_expiry' => false, 'disabled' => [], 'units' => []];
    }
}

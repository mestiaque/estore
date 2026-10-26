<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Attribute;
use ME\Retail\Models\AttributeValue;

class AttributeValueController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_attribute.edit');
    }

    public function store(Request $request, Attribute $attribute)
    {
        $request->validate([
            'value' => 'required|string|max:255|unique:retail_attribute_values,value,NULL,id,attribute_id,' . $attribute->id,
        ]);

        DB::beginTransaction();
        try {
            $attribute->values()->create(['value' => $request->value, 'status' => true]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.attributes.show', $attribute->id)->with('error', __('Failed to add value'));
        }

        return redirect()->route('retail.attributes.show', $attribute->id)->with('success', __('Value added successfully'));
    }

    public function destroy(AttributeValue $attributeValue)
    {
        $attributeId = $attributeValue->attribute_id;

        DB::beginTransaction();
        try {
            $attributeValue->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.attributes.show', $attributeId)->with('error', __('Failed to delete value — it may be in use by a product variant.'));
        }

        return redirect()->route('retail.attributes.show', $attributeId)->with('success', __('Value deleted successfully'));
    }
}

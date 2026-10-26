<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Attribute;

class AttributeController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_attribute.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_attribute.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_attribute.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_attribute.delete')->only('destroy');
    }

    public function index()
    {
        $attributes = Attribute::withCount('values')->latest()->paginate(get_setting('pagination', 10));
        return view('retail::attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('retail::attributes.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            Attribute::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.attributes.index')->with('error', __('Failed to create attribute'));
        }

        return redirect()->route('retail.attributes.index')->with('success', __('Attribute created successfully'));
    }

    public function show(Attribute $attribute)
    {
        $attribute->load('values');
        return view('retail::attributes.show', compact('attribute'));
    }

    public function edit(Attribute $attribute)
    {
        return view('retail::attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            $attribute->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.attributes.index')->with('error', __('Failed to update attribute'));
        }

        return redirect()->route('retail.attributes.index')->with('success', __('Attribute updated successfully'));
    }

    public function destroy(Attribute $attribute)
    {
        DB::beginTransaction();
        try {
            $attribute->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.attributes.index')->with('error', __('Failed to delete attribute'));
        }

        return redirect()->route('retail.attributes.index')->with('success', __('Attribute deleted successfully'));
    }

    private function validateData(Request $request): array
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        return [
            'name'   => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ];
    }
}

<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Category;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_category.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_category.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_category.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_category.delete')->only('destroy');
    }

    public function index()
    {
        $categories = Category::with('parent')->latest()->paginate(get_setting('pagination', 10));
        return view('retail::categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        return view('retail::categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_id'   => 'nullable|exists:retail_categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            Category::create([
                'parent_id'   => $request->parent_id,
                'name'        => $request->name,
                'slug'        => Str::slug($request->name) . '-' . Str::random(6),
                'description' => $request->description,
                'status'      => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.categories.index')->with('error', __('Failed to create category'));
        }

        return redirect()->route('retail.categories.index')->with('success', __('Category created successfully'));
    }

    public function show(Category $category)
    {
        return view('retail::categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $categories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        return view('retail::categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'parent_id'   => 'nullable|exists:retail_categories,id|different:id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $category->update([
                'parent_id'   => $request->parent_id,
                'name'        => $request->name,
                'description' => $request->description,
                'status'      => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.categories.index')->with('error', __('Failed to update category'));
        }

        return redirect()->route('retail.categories.index')->with('success', __('Category updated successfully'));
    }

    public function destroy(Category $category)
    {
        DB::beginTransaction();
        try {
            $category->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.categories.index')->with('error', __('Failed to delete category'));
        }

        return redirect()->route('retail.categories.index')->with('success', __('Category deleted successfully'));
    }
}

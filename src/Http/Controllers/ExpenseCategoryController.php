<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\ExpenseCategory;

class ExpenseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_expense_category.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_expense_category.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_expense_category.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_expense_category.delete')->only('destroy');
    }

    public function index()
    {
        $categories = ExpenseCategory::latest()->paginate(get_setting('pagination', 10));
        return view('retail::expense-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('retail::expense-categories.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            ExpenseCategory::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.expense-categories.index')->with('error', __('Failed to create expense category'));
        }

        return redirect()->route('retail.expense-categories.index')->with('success', __('Expense category created successfully'));
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        return view('retail::expense-categories.show', ['category' => $expenseCategory]);
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('retail::expense-categories.edit', ['category' => $expenseCategory]);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            $expenseCategory->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.expense-categories.index')->with('error', __('Failed to update expense category'));
        }

        return redirect()->route('retail.expense-categories.index')->with('success', __('Expense category updated successfully'));
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        DB::beginTransaction();
        try {
            $expenseCategory->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.expense-categories.index')->with('error', __('Failed to delete expense category'));
        }

        return redirect()->route('retail.expense-categories.index')->with('success', __('Expense category deleted successfully'));
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

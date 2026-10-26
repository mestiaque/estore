<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Enums\CashRegisterSessionStatus;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Models\Expense;
use ME\Retail\Models\ExpenseCategory;
use ME\Retail\Models\Store;
use ME\Retail\Services\ExpenseService;

class ExpenseController extends Controller
{
    public function __construct(protected ExpenseService $expenseService)
    {
        $this->middleware('authorization:retail_expense.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_expense.create')->only(['create', 'store']);
    }

    public function index()
    {
        $expenses = Expense::with(['store', 'category'])->latest()->paginate(get_setting('pagination', 10));
        return view('retail::expenses.index', compact('expenses'));
    }

    public function create()
    {
        $stores            = Store::where('status', true)->get();
        $categories         = ExpenseCategory::where('status', true)->get();
        $openSessions       = CashRegisterSession::with('cashRegister')->where('status', CashRegisterSessionStatus::Open)->get();

        return view('retail::expenses.create', compact('stores', 'categories', 'openSessions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id'                  => 'required|exists:retail_stores,id',
            'expense_category_id'      => 'required|exists:retail_expense_categories,id',
            'amount'                    => 'required|numeric|min:0.01',
            'payment_method'            => 'required|string|max:50',
            'cash_register_session_id' => 'nullable|exists:retail_cash_register_sessions,id',
            'expense_date'              => 'required|date',
            'reference'                 => 'nullable|string|max:255',
            'notes'                     => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $expense = $this->expenseService->create($request->all(), auth()->id());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.expenses.create')->with('error', $e->getMessage() ?: __('Failed to record expense'));
        }

        return redirect()->route('retail.expenses.show', $expense->id)->with('success', __('Expense recorded successfully'));
    }

    public function show(Expense $expense)
    {
        $expense->load(['store', 'category', 'cashRegisterSession']);
        return view('retail::expenses.show', compact('expense'));
    }
}

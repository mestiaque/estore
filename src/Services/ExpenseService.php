<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use ME\Retail\Enums\CashRegisterTransactionType;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Models\Expense;

class ExpenseService
{
    public function __construct(protected CashRegisterService $cashRegisterService)
    {
    }

    /**
     * @param array{
     *   store_id:int, expense_category_id:int, amount:float, payment_method:string,
     *   expense_date:string, reference?:string, notes?:string, cash_register_session_id?:int
     * } $data
     */
    public function create(array $data, int $userId): Expense
    {
        return DB::transaction(function () use ($data, $userId) {
            $session = null;

            if ($data['payment_method'] === 'cash' && !empty($data['cash_register_session_id'])) {
                $session = CashRegisterSession::findOrFail($data['cash_register_session_id']);
            }

            $expense = Expense::create([
                'store_id'                 => $data['store_id'],
                'expense_category_id'      => $data['expense_category_id'],
                'cash_register_session_id' => $session?->id,
                'amount'                   => $data['amount'],
                'payment_method'           => $data['payment_method'],
                'expense_date'             => $data['expense_date'],
                'reference'                => $data['reference'] ?? null,
                'notes'                    => $data['notes'] ?? null,
                'user_id'                  => $userId,
            ]);

            if ($session) {
                $this->cashRegisterService->recordMovement(
                    session: $session,
                    type: CashRegisterTransactionType::Expense,
                    amount: -(float) $data['amount'],
                    referenceType: Expense::class,
                    referenceId: $expense->id,
                    userId: $userId,
                    notes: $expense->notes
                );
            }

            return $expense;
        });
    }
}

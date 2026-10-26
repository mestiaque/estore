<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'retail_expenses';

    protected $fillable = [
        'store_id',
        'expense_category_id',
        'cash_register_session_id',
        'amount',
        'payment_method',
        'expense_date',
        'reference',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function cashRegisterSession()
    {
        return $this->belongsTo(CashRegisterSession::class);
    }
}

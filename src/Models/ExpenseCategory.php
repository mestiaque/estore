<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $table = 'retail_expense_categories';

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}

<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $table = 'retail_cash_registers';

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function sessions()
    {
        return $this->hasMany(CashRegisterSession::class);
    }

    public function openSession()
    {
        return $this->hasOne(CashRegisterSession::class)->where('status', 'open');
    }
}

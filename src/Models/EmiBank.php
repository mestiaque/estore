<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class EmiBank extends Model
{
    protected $table = 'retail_emi_banks';

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function plans()
    {
        return $this->hasMany(EmiPlan::class);
    }
}

<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLevel extends Model
{
    protected $table = 'retail_customer_levels';

    protected $fillable = [
        'name',
        'min_points',
        'discount_percent',
        'point_multiplier',
        'sl',
        'status',
    ];

    protected $casts = [
        'min_points'       => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'point_multiplier' => 'decimal:2',
        'status'           => 'boolean',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_level_id');
    }
}

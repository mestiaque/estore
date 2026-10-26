<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    protected $table = 'retail_memberships';

    protected $fillable = [
        'name',
        'code',
        'fee',
        'duration_days',
        'discount_percent',
        'point_multiplier',
        'status',
    ];

    protected $casts = [
        'fee'              => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'point_multiplier' => 'decimal:2',
        'status'           => 'boolean',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'membership_id');
    }
}

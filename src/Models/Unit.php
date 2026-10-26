<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'retail_units';

    protected $fillable = [
        'base_unit_id',
        'name',
        'short_name',
        'conversion_factor',
        'status',
    ];

    protected $casts = [
        'status'            => 'boolean',
        'conversion_factor' => 'decimal:4',
    ];

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function derivedUnits()
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    /**
     * Convert a quantity expressed in this unit to the base unit.
     */
    public function toBaseQuantity(float $quantity): float
    {
        return $quantity * (float) $this->conversion_factor;
    }
}

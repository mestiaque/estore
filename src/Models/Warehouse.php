<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $table = 'retail_warehouses';

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }
}

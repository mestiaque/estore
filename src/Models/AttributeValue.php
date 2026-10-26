<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    protected $table = 'retail_attribute_values';

    protected $fillable = [
        'attribute_id',
        'value',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}

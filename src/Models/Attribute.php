<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $table = 'retail_attributes';

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}

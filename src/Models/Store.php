<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;

    protected $table = 'retail_stores';

    protected $fillable = [
        'name',
        'code',
        'phone',
        'email',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}

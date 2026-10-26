<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'retail_products';

    protected $fillable = [
        'parent_product_id',
        'category_id',
        'brand_id',
        'unit_id',
        'name',
        'sku',
        'barcode',
        'plu_code',
        'purchase_price',
        'sale_price',
        'wholesale_price',
        'stock_quantity',
        'track_type',
        'has_expiry',
        'earn_points',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_price'   => 'decimal:2',
        'sale_price'       => 'decimal:2',
        'wholesale_price'  => 'decimal:2',
        'stock_quantity'   => 'decimal:4',
        'has_expiry'       => 'boolean',
        'earn_points'      => 'decimal:2',
        'status'           => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function serials()
    {
        return $this->hasMany(Serial::class);
    }

    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    public function variants()
    {
        return $this->hasMany(Product::class, 'parent_product_id');
    }

    public function isVariant(): bool
    {
        return $this->parent_product_id !== null;
    }

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    /**
     * Attributes this (template) product varies by, e.g. Color, Size.
     */
    public function variantAttributes()
    {
        return $this->belongsToMany(Attribute::class, 'retail_product_attributes', 'product_id', 'attribute_id');
    }

    /**
     * The specific attribute values this variant row carries, e.g. Color=Red, Size=M.
     */
    public function variantValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'retail_product_variant_values', 'product_id', 'attribute_value_id');
    }

    public function variantLabel(): string
    {
        return $this->variantValues->pluck('value')->implode(' / ');
    }
}

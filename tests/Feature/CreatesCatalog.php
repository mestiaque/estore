<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Models\Brand;
use ME\Retail\Models\Category;
use ME\Retail\Models\Customer;
use ME\Retail\Models\Product;
use ME\Retail\Models\Store;
use ME\Retail\Models\Supplier;
use ME\Retail\Models\Unit;
use ME\Retail\Models\Warehouse;

/**
 * Shared catalog fixtures for feature tests — plain Eloquent creates rather
 * than factory classes, since these models have no complex generation needs.
 */
trait CreatesCatalog
{
    protected function createStore(): Store
    {
        return Store::create(['name' => 'Main Store', 'code' => 'ST-' . uniqid(), 'status' => true]);
    }

    protected function createWarehouse(Store $store): Warehouse
    {
        return Warehouse::create(['store_id' => $store->id, 'name' => 'Main Warehouse', 'code' => 'WH-' . uniqid(), 'status' => true]);
    }

    protected function createCategory(): Category
    {
        return Category::create(['name' => 'General', 'slug' => 'general-' . uniqid(), 'status' => true]);
    }

    protected function createBrand(): Brand
    {
        return Brand::create(['name' => 'Generic', 'slug' => 'generic-' . uniqid(), 'status' => true]);
    }

    protected function createUnit(float $conversionFactor = 1, ?int $baseUnitId = null): Unit
    {
        return Unit::create([
            'base_unit_id'      => $baseUnitId,
            'name'              => 'Piece',
            'short_name'        => 'pc',
            'conversion_factor' => $conversionFactor,
            'status'            => true,
        ]);
    }

    protected function createProduct(array $overrides = []): Product
    {
        $category = $overrides['category_id'] ?? $this->createCategory()->id;
        $unit     = $overrides['unit_id'] ?? $this->createUnit()->id;

        return Product::create(array_merge([
            'category_id'     => $category,
            'unit_id'         => $unit,
            'name'            => 'Test Product',
            'sku'             => 'SKU-' . uniqid(),
            'purchase_price'  => 50,
            'sale_price'      => 80,
            'stock_quantity'  => 0,
            'track_type'      => 'normal',
            'status'          => true,
        ], $overrides));
    }

    protected function createSupplier(): Supplier
    {
        return Supplier::create(['name' => 'Test Supplier', 'status' => true]);
    }

    protected function createCustomer(): Customer
    {
        return Customer::create(['name' => 'Test Customer', 'status' => true]);
    }
}

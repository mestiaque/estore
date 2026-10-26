<?php

namespace ME\Retail\Tests\Unit;

use ME\Retail\Models\Unit;
use ME\Retail\Tests\TestCase;

class UnitConversionTest extends TestCase
{
    public function test_base_unit_converts_one_to_one(): void
    {
        $piece = new Unit(['conversion_factor' => 1]);

        $this->assertSame(5.0, $piece->toBaseQuantity(5));
    }

    public function test_carton_converts_to_pieces(): void
    {
        // 1 Carton = 24 Pieces
        $carton = new Unit(['conversion_factor' => 24]);

        $this->assertSame(240.0, $carton->toBaseQuantity(10));
    }

    public function test_fractional_quantity_is_preserved(): void
    {
        // 1.5 KG in a Gram-based product (1 KG = 1000 Gram)
        $kg = new Unit(['conversion_factor' => 1000]);

        $this->assertEqualsWithDelta(1500.0, $kg->toBaseQuantity(1.5), 0.0001);
    }
}

<?php

namespace ME\Retail\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public static function forProduct(string $productName, string $warehouseName, float $requested, float $available): self
    {
        return new self(sprintf(
            'Insufficient stock for "%s" in warehouse "%s": requested %s, available %s.',
            $productName,
            $warehouseName,
            $requested,
            $available
        ));
    }
}

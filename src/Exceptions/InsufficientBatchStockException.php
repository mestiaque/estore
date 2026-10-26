<?php

namespace ME\Retail\Exceptions;

use Exception;

class InsufficientBatchStockException extends Exception
{
    public static function forBatch(string $productName, string $batchNumber, float $requested, float $available): self
    {
        return new self(sprintf(
            'Insufficient stock in batch "%s" for "%s": requested %s, available %s.',
            $batchNumber,
            $productName,
            $requested,
            $available
        ));
    }
}

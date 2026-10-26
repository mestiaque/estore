<?php

namespace ME\Retail\Exceptions;

use Exception;

class DuplicateSerialException extends Exception
{
    public static function forProduct(string $productName, string $serialNumber): self
    {
        return new self("Serial number \"{$serialNumber}\" already exists for product \"{$productName}\".");
    }
}

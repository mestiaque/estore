<?php

namespace ME\Retail\Exceptions;

use Exception;

class DuplicateBarcodeException extends Exception
{
    public static function forCode(string $barcode): self
    {
        return new self("Barcode \"{$barcode}\" is already assigned to another product.");
    }
}

<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly int $productId,
        public readonly string $productName,
        public readonly float $requiredQuantity,
        public readonly float $availableQuantity,
    ) {
        parent::__construct("Insufficient stock for '{$productName}': required {$requiredQuantity}, available {$availableQuantity}");
    }
}

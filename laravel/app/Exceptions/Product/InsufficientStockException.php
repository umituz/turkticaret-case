<?php

namespace App\Exceptions\Product;

use Exception;

class InsufficientStockException extends Exception
{
    protected $message = 'Insufficient stock for requested quantity';
    protected $code = 422;

    public function __construct(string $productName = null, int $requestedQuantity = null, int $availableStock = null)
    {
        if ($productName && $requestedQuantity !== null && $availableStock !== null) {
            $message = "Insufficient stock for product '{$productName}'. Requested: {$requestedQuantity}, Available: {$availableStock}";
        } else {
            $message = $this->message;
        }
        
        parent::__construct($message, $this->code);
    }
}
<?php

namespace App\Exceptions\Product;

use Exception;

class OutOfStockException extends Exception
{
    protected $message = 'Product is out of stock';
    protected $code = 422;

    public function __construct(string $productName = null)
    {
        $message = $productName 
            ? "Product '{$productName}' is out of stock" 
            : $this->message;
            
        parent::__construct($message, $this->code);
    }
}
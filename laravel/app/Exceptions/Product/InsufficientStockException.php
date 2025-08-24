<?php

namespace App\Exceptions\Product;

use Exception;

/**
 * Exception thrown when requested quantity exceeds available stock.
 * 
 * This exception is raised when attempting to add or purchase a quantity
 * of a product that exceeds the available inventory. It provides detailed
 * information about the requested vs available quantities.
 *
 * @package App\Exceptions\Product
 */
class InsufficientStockException extends Exception
{
    protected $message = 'Insufficient stock for requested quantity';
    protected $code = 422;

    /**
     * Create a new insufficient stock exception instance.
     *
     * @param string|null $productName Name of the product with insufficient stock
     * @param int|null $requestedQuantity Quantity that was requested
     * @param int|null $availableStock Quantity that is actually available
     */
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
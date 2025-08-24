<?php

namespace App\Exceptions\Product;

use Exception;

/**
 * Exception thrown when a product is completely out of stock.
 * 
 * This exception is raised when attempting to add or purchase a product
 * that has zero available stock. It provides clear messaging about
 * which product is unavailable and helps handle inventory constraints.
 *
 * @package App\Exceptions\Product
 */
class OutOfStockException extends Exception
{
    protected $message = 'Product is out of stock';
    protected $code = 422;

    /**
     * Create a new out of stock exception instance.
     *
     * @param string|null $productName Name of the product that is out of stock
     */
    public function __construct(string $productName = null)
    {
        $message = $productName 
            ? "Product '{$productName}' is out of stock" 
            : $this->message;
            
        parent::__construct($message, $this->code);
    }
}
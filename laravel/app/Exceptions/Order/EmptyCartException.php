<?php

namespace App\Exceptions\Order;

use Exception;

/**
 * Exception thrown when attempting to create an order from an empty cart.
 * 
 * This exception is raised when business logic attempts to process an order
 * but the cart contains no items. It helps prevent invalid order creation
 * and provides clear feedback about the empty cart state.
 *
 * @package App\Exceptions\Order
 */
class EmptyCartException extends Exception
{
    protected $message = 'Cannot create order from empty cart';
    protected $code = 400;

    /**
     * Create a new empty cart exception instance.
     *
     * @param string|null $message Custom error message (defaults to class message)
     */
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? $this->message, $this->code);
    }
}
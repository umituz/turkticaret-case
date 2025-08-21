<?php

namespace App\Exceptions\Order;

use Exception;

class EmptyCartException extends Exception
{
    protected $message = 'Cannot create order from empty cart';
    protected $code = 400;

    public function __construct(string $message = null)
    {
        parent::__construct($message ?? $this->message, $this->code);
    }
}
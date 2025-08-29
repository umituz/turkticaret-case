<?php

namespace App\Exceptions\Order;

use App\Helpers\MoneyHelper;
use Exception;

/**
 * Exception thrown when order total amount is below the minimum required amount.
 *
 * This exception is used to indicate that an order cannot be created
 * because the total amount does not meet the minimum order amount requirement.
 *
 * @package App\Exceptions\Order
 */
class MinimumOrderAmountException extends Exception
{
    protected $message = 'Order total amount is below the minimum required amount';
    protected $code = 400;

    /**
     * Create a new MinimumOrderAmountException instance.
     *
     * @param int $currentAmount Current order total amount in cents
     * @param int $minimumAmount Minimum required amount in cents
     * @param string|null $message Custom error message
     */
    public function __construct(int $currentAmount, int $minimumAmount, string $message = null)
    {
        $currentFormatted = MoneyHelper::getAmountInfo($currentAmount)['formatted'];
        $minimumFormatted = MoneyHelper::getAmountInfo($minimumAmount)['formatted'];
        
        $message = $message ?? "Order total ({$currentFormatted}) is below the minimum required amount ({$minimumFormatted})";
        
        parent::__construct($message, $this->code);
    }
}
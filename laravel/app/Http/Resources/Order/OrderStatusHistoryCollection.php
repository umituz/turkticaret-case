<?php

namespace App\Http\Resources\Order;


use App\Http\Resources\Base\BaseCollection;

class OrderStatusHistoryCollection extends BaseCollection
{
    public $collects = OrderStatusHistoryResource::class;
}

<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Base\BaseResourceCollection;

class OrderStatusHistoryCollection extends BaseResourceCollection
{
    public string $collects = OrderStatusHistoryResource::class;
}

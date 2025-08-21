<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Base\BaseCollection;

class OrderCollection extends BaseCollection
{
    public $collects = OrderResource::class;
}
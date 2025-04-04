<?php

namespace App\Services;

use App\Models\Order;
use App\Constants\OrderStatus;

class TypeCOrderProcessorService
{
    public function process(Order $order)
    {
        $order->status = $order->flag ? OrderStatus::COMPLETED : OrderStatus::IN_PROGRESS;
    }
}

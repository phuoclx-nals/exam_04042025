<?php

namespace App\Services;

use App\Models\Order;
use App\Constants\OrderStatus;
use App\Constants\OrderConfig;
use App\API\APIClient;
use App\Constants\APIResponseStatus;
use App\Constants\OrderPriority;

class TypeBOrderProcessorService
{
    private $apiClient;

    public function __construct(APIClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function process(Order $order)
    {
        try {
            $apiResponse = $this->apiClient->callAPI($order->id);

            if ($apiResponse === null) {
                $order->status = OrderStatus::API_NULL_RESPONSE;
                return;
            }

            if ($apiResponse->status === APIResponseStatus::SUCCESS) {
                if (!empty($apiResponse->data) && $apiResponse->data->amount >= OrderConfig::MIN_ORDER_AMOUNT && $order->amount < OrderConfig::MAX_ORDER_AMOUNT) {
                    $order->status = OrderStatus::PROCESSED;
                    $order->priority = OrderPriority::LOW;
                } elseif (!empty($apiResponse->data) && $apiResponse->data->amount < OrderConfig::MIN_ORDER_AMOUNT && $order->isPending) {
                    $order->status = OrderStatus::PENDING;
                } else {
                    $order->status = OrderStatus::ERROR;
                }
            } elseif ($apiResponse->status === APIResponseStatus::FAILURE) {
                $order->status = OrderStatus::API_ERROR;
            }
        } catch (\Exception $e) {
            $order->status = OrderStatus::API_FAILURE;
        }
    }
}

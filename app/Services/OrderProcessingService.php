<?php

namespace App\Services;

use App\API\APIClient;
use App\Constants\OrderStatus;
use App\Constants\OrderPriority;
use App\Constants\OrderType;
use App\Constants\OrderThreshold;
use App\Exceptions\DatabaseException;
use App\Models\Order;

class OrderProcessingService
{
    private $dbService;
    private $apiClient;

    public function __construct(DatabaseService $dbService, APIClient $apiClient)
    {
        $this->dbService = $dbService;
        $this->apiClient = $apiClient;
    }

    public function processOrders(int $userId)
    {
        try {
            $orders = $this->dbService->getOrdersByUser($userId);

            foreach ($orders as $order) {
                $this->processOrder($order, $userId);
            }

            return $orders;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function processOrder(Order $order, int $userId)
    {
        switch ($order->type) {
            case OrderType::TYPE_A:
                (new TypeAOrderProcessorService())->process($order, $userId);
                break;
            case OrderType::TYPE_B:
                (new TypeBOrderProcessorService($this->apiClient))->process($order);
                break;
            case OrderType::TYPE_C:
                (new TypeCOrderProcessorService())->process($order);
                break;
            default:
                $order->status = OrderStatus::UNKNOWN_TYPE;
                break;
        }

        $order->priority = $order->amount > OrderThreshold::HIGH_PRIORITY_THRESHOLD 
        ? OrderPriority::HIGH : OrderPriority::LOW;

        try {
            $this->dbService->updateOrderStatus(
                $order->id, $order->status, $order->priority);
        } catch (DatabaseException $e) {
            $order->status = OrderStatus::DB_ERROR;
        }
    }
}

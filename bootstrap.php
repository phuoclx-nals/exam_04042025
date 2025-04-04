<?php 
use App\OrderProcessingService;
use App\OrderHandlers\TypeAHandler;
use App\OrderHandlers\TypeBHandler;
use App\OrderHandlers\TypeCHandler;

$dbService = new DatabaseService();
$apiClient = new APIClient();
$orderHandlers = [
    new TypeAHandler(),
    new TypeBHandler(),
    new TypeCHandler(),
];

$orderProcessingService = new OrderProcessingService($dbService, $apiClient, $orderHandlers);

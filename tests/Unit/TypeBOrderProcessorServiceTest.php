<?php

use App\Services\TypeBOrderProcessorService;
use App\Models\Order;
use App\Constants\OrderStatus;
use App\Constants\OrderConfig;
use App\API\APIClient;
use App\API\APIResponse;
use App\Constants\APIResponseStatus;
use App\Constants\OrderPriority;

it('sets status to API_NULL_RESPONSE when API response is null', function () {
    $apiClient = mock(APIClient::class)
        ->shouldReceive('callAPI')
        ->andReturn(null)
        ->getMock();

    $service = new TypeBOrderProcessorService($apiClient);
    $order = new Order(1, 'typeB', 100.0, false);

    $service->process($order);

    expect($order->status)->toBe(OrderStatus::API_NULL_RESPONSE);
});

it('sets status to PROCESSED and priority to LOW for valid API response', function () {
    $apiResponseData = new Order(1, 'typeB', OrderConfig::MIN_ORDER_AMOUNT + 1, false); // Create Order instance
    $apiResponse = new APIResponse(APIResponseStatus::SUCCESS, $apiResponseData);

    $apiClient = mock(APIClient::class)
        ->shouldReceive('callAPI')
        ->andReturn($apiResponse)
        ->getMock();

    $service = new TypeBOrderProcessorService($apiClient);
    $order = new Order(2, 'typeB', OrderConfig::MAX_ORDER_AMOUNT - 1, false);

    $service->process($order);

    expect($order->status)->toBe(OrderStatus::PROCESSED)
        ->and($order->priority)->toBe(OrderPriority::LOW);
});

it('sets status to PENDING for low API response amount and pending order', function () {
    $apiResponseData = new Order(3, 'typeB', OrderConfig::MIN_ORDER_AMOUNT - 1, false); // Pass all required arguments
    $apiResponse = new APIResponse(APIResponseStatus::SUCCESS, $apiResponseData);

    $apiClient = mock(APIClient::class)
        ->shouldReceive('callAPI')
        ->andReturn($apiResponse)
        ->getMock();

    $service = new TypeBOrderProcessorService($apiClient);
    $order = new Order(3, 'typeB', 50.0, true);
    $order->isPending = true;

    $service->process($order);

    expect($order->status)->toBe(OrderStatus::PENDING);
});

it('sets status to ERROR for invalid conditions', function () {
    $apiResponseData = new Order(4, 'typeB', OrderConfig::MIN_ORDER_AMOUNT - 1, false); // Pass all required arguments
    $apiResponse = new APIResponse(APIResponseStatus::SUCCESS, $apiResponseData);

    $apiClient = mock(APIClient::class)
        ->shouldReceive('callAPI')
        ->andReturn($apiResponse)
        ->getMock();

    $service = new TypeBOrderProcessorService($apiClient);
    $order = new Order(4, 'typeB', 50.0, false);
    $order->isPending = false;

    $service->process($order);

    expect($order->status)->toBe(OrderStatus::ERROR);
});

it('sets status to API_ERROR for API failure response', function () {
    $apiResponseData = new Order(5, 'typeB', 0.0, false); // Pass a valid Order instance with default values
    $apiResponse = new APIResponse(APIResponseStatus::FAILURE, $apiResponseData);

    $apiClient = mock(APIClient::class)
        ->shouldReceive('callAPI')
        ->andReturn($apiResponse)
        ->getMock();

    $service = new TypeBOrderProcessorService($apiClient);
    $order = new Order(5, 'typeB', 100.0, false);

    $service->process($order);

    expect($order->status)->toBe(OrderStatus::API_ERROR);
});

it('sets status to API_FAILURE when an exception is thrown', function () {
    $apiClient = mock(APIClient::class)
        ->shouldReceive('callAPI')
        ->andThrow(new Exception('API error'))
        ->getMock();

    $service = new TypeBOrderProcessorService($apiClient);
    $order = new Order(6, 'typeB', 100.0, false);

    $service->process($order);

    expect($order->status)->toBe(OrderStatus::API_FAILURE);
});

<?php

use App\Services\TypeCOrderProcessorService;
use App\Models\Order;
use App\Constants\OrderStatus;

it('sets status to completed when flag is true', function () {
    // Arrange
    $order = new Order(1, 101, 202, 5); // Replace with actual valid arguments
    $order->flag = true;
    $service = new TypeCOrderProcessorService();

    // Act
    $service->process($order);

    // Assert
    expect($order->status)->toBe(OrderStatus::COMPLETED);
});

it('sets status to in progress when flag is false', function () {
    // Arrange
    $order = new Order(2, 102, 203, 10); // Replace with actual valid arguments
    $order->flag = false;
    $service = new TypeCOrderProcessorService();

    // Act
    $service->process($order);

    // Assert
    expect($order->status)->toBe(OrderStatus::IN_PROGRESS);
});

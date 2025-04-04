<?php 

use App\Services\TypeAOrderProcessorService;
use App\Models\Order;
use App\Constants\OrderStatus;
use App\Constants\OrderThreshold;
// use Mockery;

class TestableTypeAOrderProcessorService extends TypeAOrderProcessorService
{
    private int $mockedTime;
    private ?bool $mockedOpenCsvFileResult = null; // Add a property to control openCsvFile behavior

    public function __construct(int $mockedTime)
    {
        $this->mockedTime = $mockedTime;
    }

    protected function getCurrentTime(): int
    {
        return $this->mockedTime;
    }

    public function setMockedOpenCsvFileResult(?bool $result): void
    {
        $this->mockedOpenCsvFileResult = $result;
    }

    protected function openCsvFile(string $filePath)
    {
        if ($this->mockedOpenCsvFileResult !== null) {
            return $this->mockedOpenCsvFileResult; // Return the mocked result if set
        }
        return parent::openCsvFile($filePath); // Otherwise, call the parent method
    }
}

beforeEach(function () {
    $this->mockedTime = 1672531200; // Example fixed timestamp
    $this->service = new TestableTypeAOrderProcessorService($this->mockedTime);
});

afterEach(function () {
    Mockery::close();
});

it('exports order to CSV successfully', function () {
    $order = new Order(
        1, // id
        'TypeA', // type
        100, // amount
        true // flag
    );
    $order->status = OrderStatus::PENDING;
    $order->priority = 'High';
    $userId = 123;

    $mock = Mockery::mock('alias:fopen');
    $mock->shouldReceive('fopen')->andReturn(true);

    $service = $this->service; // Use the service initialized in beforeEach
    $service->process($order, $userId);

    $csvFile = sprintf('orders_type_A_%d_%d.csv', $userId, $this->mockedTime);

    expect(file_exists($csvFile))->toBeTrue();
    
    $csvContent = array_map('str_getcsv', file($csvFile));
    expect($csvContent[0])->toBe(['ID', 'Type', 'Amount', 'Flag', 'Status', 'Priority']);
    expect($csvContent[1])->toBe([
        (string) $order->id, // Cast to string to match CSV output
        $order->type,
        (string) $order->amount, // Cast to string to match CSV output
        'true',
        'pending', // Update to match the actual status in the CSV
        $order->priority,
    ]);

    unlink($csvFile);
    expect(file_exists($csvFile))->toBeFalse();
    expect($order->status)->toBe(OrderStatus::EXPORTED); // Ensure status is updated after processing
});

it('adds high value note for orders exceeding threshold', function () {
    $order = new Order(
        2, // id
        'TypeA', // type
        OrderThreshold::HIGH_VALUE_THRESHOLD + 1, // amount
        false // flag
    );
    $order->status = OrderStatus::PENDING;
    $order->priority = 'Medium';
    $userId = 456;

    $service = $this->service; // Use the service initialized in beforeEach
    $service->process($order, $userId);

    $csvFile = sprintf('orders_type_A_%d_%d.csv', $userId, $this->mockedTime);

    expect(file_exists($csvFile))->toBeTrue();
    $csvContent = array_map('str_getcsv', file($csvFile));
    expect($csvContent[1])->toBe([
        (string) $order->id,
        $order->type,
        (string) $order->amount,
        'false',
        'pending',
        $order->priority,
    ]);
    expect($csvContent[2])->toBe(['', '', '', '', 'Note', 'High value order']);

    unlink($csvFile);
    expect(file_exists($csvFile))->toBeFalse();
    expect($order->status)->toBe(OrderStatus::EXPORTED); // Ensure status is updated after processing
});

it('sets status to EXPORT_FAILED when file cannot be created', function () {
    $order = new Order(
        3, // id
        'TypeA', // type
        50, // amount
        false // flag
    );
    $order->status = OrderStatus::PENDING;
    $order->priority = 'Low';
    $userId = 789;

    // Use the TestableTypeAOrderProcessorService to control openCsvFile behavior
    $service = $this->service;
    $service->setMockedOpenCsvFileResult(false); // Simulate file creation failure

    $service->process($order, $userId);

    expect($order->status)->toBe(OrderStatus::EXPORT_FAILED);
});

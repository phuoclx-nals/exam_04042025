<?php

namespace App\Services;

use App\Models\Order;
use App\Constants\OrderStatus;
use App\Constants\OrderThreshold;

class TypeAOrderProcessorService
{
    private const CSV_FILE_NAME_FORMAT = 'orders_type_A_%d_%d.csv';
    private const CSV_HEADER_ROW = ['ID', 'Type', 'Amount', 'Flag', 'Status', 'Priority'];

    public function process(Order $order, int $userId): Order|null
    {
        $csvFile = sprintf(self::CSV_FILE_NAME_FORMAT, $userId, $this->getCurrentTime());
        $fileHandle = $this->openCsvFile($csvFile);

        if ($fileHandle === false) {
            $order->status = OrderStatus::EXPORT_FAILED;
            return null;
        }

        fputcsv($fileHandle, self::CSV_HEADER_ROW);
        fputcsv($fileHandle, [
            $order->id,
            $order->type,
            $order->amount,
            $order->flag ? 'true' : 'false',
            $order->status,
            $order->priority
        ]);

        if ($order->amount > OrderThreshold::HIGH_VALUE_THRESHOLD) {
            fputcsv($fileHandle, ['', '', '', '', 'Note', 'High value order']);
        }

        fclose($fileHandle);
        $order->status = OrderStatus::EXPORTED;

        return $order;
    }

    private function openCsvFile(string $csvFile)
    {
        return @fopen($csvFile, 'w');
    }

    protected function getCurrentTime(): int
    {
        return time();
    }
}

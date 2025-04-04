<?php 

namespace App\API;

use App\Models\Order;

class APIResponse
{
    public $status;
    public $data;

    public function __construct($status, Order|null $data)
    {
        $this->status = $status;
        $this->data = $data;
    }
}
<?php

namespace App\Models;

class Order
{
    public int $id;
    public string $type;
    public float $amount;
    public bool $flag;
    public string $status;
    public string $priority;
    public bool $isPending;

    public function __construct(int $id, string $type, float $amount, bool $flag)
    {
        $this->id = $id;
        $this->type = $type;
        $this->amount = $amount;
        $this->flag = $flag;
        $this->status = 'new';
        $this->priority = 'low';
        $this->isPending = false; // Default value
    }
}

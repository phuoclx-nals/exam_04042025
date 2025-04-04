<?php

namespace App\API;

interface APIClient
{
    public function callAPI($orderId): APIResponse|null;
}

<?php

namespace App\Constants;

class OrderStatus
{
    const PROCESSED = 'processed';
    const API_NULL_RESPONSE = 'api_null_response';
    const PENDING = 'pending';
    const ERROR = 'error';
    const API_ERROR = 'api_error';
    const API_FAILURE = 'api_failure';
    const COMPLETED = 'completed';
    const IN_PROGRESS = 'in_progress';
    const UNKNOWN_TYPE = 'unknown_type';
    const DB_ERROR = 'db_error';
    const EXPORTED = 'exported';
    const EXPORT_FAILED = 'export_failed';
}

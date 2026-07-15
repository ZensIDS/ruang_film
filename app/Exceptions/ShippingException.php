<?php

namespace App\Exceptions;

use RuntimeException;

class ShippingException extends RuntimeException
{
    protected $userMessage;

    public function __construct($message = '', $userMessage = null, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: 'Shipping service error.', $code, $previous);

        $this->userMessage = $userMessage ?: $message ?: 'Layanan pengiriman sedang bermasalah.';
    }

    public function userMessage()
    {
        return $this->userMessage;
    }
}

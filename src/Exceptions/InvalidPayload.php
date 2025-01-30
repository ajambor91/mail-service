<?php

namespace MailService\MailService\Exceptions;

use Exception;
use Throwable;

/**
 * Exception for invalid message payload
 */
class InvalidPayload extends Exception
{
    /**
     * @param $message
     * @param $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
<?php

namespace MailService\MailService\Exceptions;

use Exception;
use Throwable;

/**
 * Exception for invalid Secret from request's header
 */
class InvalidSecret extends Exception
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
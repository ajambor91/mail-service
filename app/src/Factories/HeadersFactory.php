<?php

namespace MailService\MailService\Factories;

use MailService\MailService\Core\Headers;

class HeadersFactory
{
    public function create(array $headers): Headers
    {
        return new Headers($headers);
    }
}
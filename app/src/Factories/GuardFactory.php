<?php

namespace MailService\MailService\Factories;

use MailService\MailService\Core\Env;
use MailService\MailService\Core\Guard;
use MailService\MailService\Core\IHeaders;
use MailService\MailService\Core\Response;

class GuardFactory
{
    public function create(IHeaders $headers, Env $env, Response $response): Guard
    {
        return new Guard($headers, $env, $response);
    }
}
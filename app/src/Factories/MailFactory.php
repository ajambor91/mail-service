<?php

namespace MailService\MailService\Factories;

use MailService\MailService\Core\Env;
use MailService\MailService\Core\Mail;
use MailService\MailService\Exceptions\InvalidPayload;

class MailFactory
{
    /**
     * @param array $payload
     * @param Env $env
     * @throws InvalidPayload
     */
    public function createMail(array $payload, Env $env): Mail
    {
        return new Mail($payload, $env);
    }
}
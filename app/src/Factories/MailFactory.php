<?php

namespace MailService\MailService\Factories;

use MailService\MailService\Core\Env;
use MailService\MailService\Core\Mail;

class MailFactory
{
    public function createMail(array $payload, Env $env): Mail
    {
        return new Mail($payload, $env);
    }
}
<?php
namespace MailService\MailService\Factories;

use MailService\MailService\Core\Env;
use MailService\MailService\Core\Mail;
use MailService\MailService\Core\Mailer;

class MailerFactory
{
    public function create(Mail $mail, Env $env)
    {
        return new Mailer($mail, $env);
    }
}
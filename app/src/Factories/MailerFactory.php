<?php
namespace MailService\MailService\Factories;

use MailService\MailService\Core\Env;
use MailService\MailService\Core\Mail;
use MailService\MailService\Core\Mailer;
use PHPMailer\PHPMailer\PHPMailer;

class MailerFactory
{
    /**
     * @throws \Exception
     */
    public function create(Mail $mail, Env $env, PHPMailer $mailer)
    {
        return new Mailer($mail, $env, $mailer);
    }
}
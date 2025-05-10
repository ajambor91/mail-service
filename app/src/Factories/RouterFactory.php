<?php
namespace MailService\MailService\Factories;

use MailService\MailService\Core\Router;

class RouterFactory
{
    public function createRouter(): Router
    {
        return new Router();
    }
}
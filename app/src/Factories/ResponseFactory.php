<?php

namespace MailService\MailService\Factories;

use MailService\MailService\Core\Env;
use MailService\MailService\Core\Response;

class ResponseFactory
{
    public function create(Env $env): Response
    {
        return new Response($env);
    }
}
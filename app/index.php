<?php

use MailService\MailService\App;
use MailService\MailService\Core\Env;
use MailService\MailService\Core\Logger;
use MailService\MailService\Factories\GuardFactory;
use MailService\MailService\Factories\HeadersFactory;
use MailService\MailService\Factories\MailerFactory;
use MailService\MailService\Factories\MailFactory;
use MailService\MailService\Factories\ResponseFactory;
use MailService\MailService\Factories\RouterFactory;

DEFINE("ROOT", __DIR__);
require_once "./vendor/autoload.php";
require_once "./src/App.php";
function getPayload(): array
{
    return json_decode(file_get_contents('php://input'), true);
}
$env  = Env::getInstance();
$logger = new Logger($env);
$app = new App(
    getPayload(),
    $_SERVER,
    $logger,
    $env,
    new HeadersFactory(),
    new GuardFactory(),
    new RouterFactory(),
    new MailFactory(),
    new MailerFactory(),
    new ResponseFactory()
);
$app->run();


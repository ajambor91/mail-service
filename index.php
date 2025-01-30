<?php

use MailService\MailService\App;

DEFINE("ROOT", __DIR__);
require_once "./vendor/autoload.php";
require_once "./src/App.php";

function getPayload(): array
{
    return json_decode(file_get_contents('php://input'), true);
}

$app = new App();
$app->setServerData($_SERVER)
    ->setRouter()
    ->setResponse()
    ->setPayload(getPayload())
    ->checkAccess()
    ->setupApp()
    ->sendMessage()
    ->handleResponse()
    ->sendResponse();


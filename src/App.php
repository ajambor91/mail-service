<?php

namespace MailService\MailService;

use MailService\MailService\Core\Env;
use MailService\MailService\Core\Guard;
use MailService\MailService\Core\Headers;
use MailService\MailService\Core\IHeaders;
use MailService\MailService\Core\Mail;
use MailService\MailService\Core\Mailer;
use Exception;
use MailService\MailService\Core\Response;
use MailService\MailService\Core\Router;
use MailService\MailService\Core\View;
use MailService\MailService\Exceptions\InvalidContentType;
use MailService\MailService\Exceptions\InvalidDomain;
use MailService\MailService\Exceptions\InvalidPayload;
use MailService\MailService\Exceptions\InvalidSecret;

/**
 * Main class
 */
class App
{

    private Router $router;

    /**
     * @var Guard
     */
    private Guard $guard;
    /**
     * @var IHeaders
     */
    private IHeaders $headers;
    /**
     * @var Env
     */
    private Env $env;

    /**
     * @var Mailer
     */
    private Mailer $mail;

    public function __construct()
    {
        $this->router = new Router();
    }

    /**
     * Run whole application and handle request, checking access and sending message
     * @return void
     */
    public function run(): void {
        if (!$this->router->checkPathForMail()) {
            $view = new View();
            $view->showView();
        }
        $this->env = Env::getInstance();
        try {
            $this->headers = new Headers();
            $this->guard = new Guard($this->headers);
            if ($this->guard->checkAccess()) {
                $mail = new Mail();
                $this->mail = new Mailer($mail);
                $this->mail->setup()->prepare()->sendMessage();
                $res = Response::getInstance();
                $res->setCode(200);
                $res->setMessage(["message" => 'Message was send']);
                $res->returnResponse();;
            }

        } catch (Exception $exception) {

            if ($exception instanceof InvalidSecret) {
                $this->sendInvalidSecret();;
            } elseif ($exception instanceof InvalidDomain) {
                $this->sendInvalidDomain();;
            } elseif ($exception instanceof InvalidPayload) {
                $this->sendInvalidPayload();
            } elseif ($exception instanceof InvalidContentType) {
                $this->sendInvalidContentTypen();
            } else {
                $this->sendInternalServerError();
            }

        }
    }

    /**
     * Return Bad Request when Content-Type is not application/json
     * @return void
     */
    private function sendInvalidContentTypen(): void
    {
        $res = Response::getInstance();
        $res->setCode(400);
        $res->setMessage(["message" => 'Content-Type should be application/json']);
        $res->returnResponse();;
    }

    /**
     * Send Forbidden when request's domain mismatch with domain from .env
     * @return void
     */
    private function sendInvalidDomain(): void
    {
        $res = Response::getInstance();
        $res->setCode(403);
        $res->setMessage(["message" => 'Domain not allowed']);
        $res->returnResponse();;
    }

    /**
     * Send Bad Request when payload is invalid
     * @return void
     */
    private function sendInvalidPayload(): void
    {
        $res = Response::getInstance();
        $res->setCode(400);
        $res->setMessage(["message" => 'Invalid payload']);
        $res->returnResponse();;
    }

    /**
     * Send Internal Server Error on Exception
     * @return void
     */
    private function sendInternalServerError(): void
    {
        $res = Response::getInstance();
        $res->setCode(500);
        $res->setMessage(["message" => 'Internal Server Error']);
        $res->returnResponse();;
    }


    /**
     * Send Unauthorized when Secret mismatch
     * @return void
     */
    private function sendInvalidSecret(): void
    {
        $res = Response::getInstance();
        $res->setCode(401);
        $res->setMessage(["message" => 'Invalid secret']);
        $res->returnResponse();;
    }

}

$app = new App();
$app->run();
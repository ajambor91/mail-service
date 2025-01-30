<?php

namespace MailService\MailService;

use Exception;
use MailService\MailService\Core\Env;
use MailService\MailService\Core\Guard;
use MailService\MailService\Core\Headers;
use MailService\MailService\Core\IHeaders;
use MailService\MailService\Core\Logger;
use MailService\MailService\Core\Mail;
use MailService\MailService\Core\Mailer;
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

    private Logger $logger;
    private string $uuid;
    /**
     * @var bool
     */
    private bool $isError = false;
    /**
     * @var string|null
     */
    private ?string $errorMessage = null;
    /**
     * @var string|null
     */
    private ?string $responseToReturn = null;
    /**
     * @var array|null
     */
    private ?array $payload = null;

    /**
     * @var array|null
     */
    private ?array $server = null;

    /**
     * @var Router|null
     */
    private ?Router $router = null;

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
    private Mailer $mailer;


    /**
     * @var Response
     */
    private Response $response;

    public function __construct()
    {
        $uuid = bin2hex(random_bytes(16));
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($uuid, 4));
        $this->uuid = $uuid;
        $this->logger = Logger::getInstance();
    }


    /**
     * Method for set request payload
     * @param array $payload
     * @return $this
     */
    public function setPayload(array $payload): self
    {
        if (!$this->router) {
            $this->sendInternalServerError();
            $this->isError = true;
            return $this;
        }
        $this->payload = $payload;
       $this->logger->log($this->uuid, 'Payload set: ' . json_encode($this->payload));

        return $this;
    }

    /**
     * Method for set server data
     * @param array $server
     * @return $this
     */
    public function setServerData(array $server): self
    {
        $this->server = $server;
        $this->logger->log($this->uuid, 'Server data set: ' . json_encode($this->server));

        return $this;
    }

    /**
     * Method for create router
     * @return $this
     */
    public function setRouter(): self
    {
        $this->logger->log($this->uuid, 'Router set: ' . json_encode($this->payload));

        if (!$this->server) {
            $this->sendInternalServerError();
            $this->isError = true;
            return $this;
        }
        $this->router = new Router();
        if (!$this->router->checkPathForMail($this->server)) {
            $this->logger->log($this->uuid, 'Redirected into main view');

            $view = new View();
            $view->showView();
        }
        return $this;
    }

    /**
     * Method for initialize response
     * @return $this
     */
    public function setResponse(): self
    {
        if (!$this->server) {
            $this->sendInternalServerError();
            $this->isError = true;
            return $this;
        }
        $this->logger->log($this->uuid, 'Response set');

        $this->response = Response::getInstance();
        return $this;
    }

    /**
     * Method for sending message
     * @return $this
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendMessage(): self
    {
        if ($this->isError) {
            return $this;
        }

        if (!$this->mailer) {
            $this->sendInternalServerError();
            $this->sendResponse();
        }
        $this->logger->log($this->uuid, 'Message send: ' . json_encode($this->payload));

        $this->mailer->setup()->prepare()->sendMessage();
        return $this;

    }

    /**
     * Method for create setup App, creating Mail and Mailer
     * @return $this
     */
    public function setupApp(): self
    {
        try {
            $this->logger->log($this->uuid, 'Created mail: ' . json_encode($this->payload));

            $mail = new Mail($this->payload);
            $this->mailer = $this->createMailer($mail);

        } catch (InvalidPayload $invalidPayload) {
            $this->sendInvalidPayload();
            $this->isError = true;
            $this->errorMessage = $invalidPayload->getMessage();
            return $this;
        }
        return $this;
    }

    /**
     * Method for returning response
     * @return void
     */
    public function sendResponse()
    {
        if (!$this->response) {
            $this->sendInternalServerError();
        }
        $this->logger->log($this->uuid, 'Returned response: ' . $this->responseToReturn);

        echo $this->responseToReturn;
        exit;

    }

    /**
     * Method for handling response on the last stage
     * @return $this
     */
    public function handleResponse(): self
    {
        if ($this->isError) {
            $this->logger->log($this->uuid, $this->errorMessage);
            $this->response->setDebugMessage($this->errorMessage);
            return $this;
        }
        $this->logger->log($this->uuid, 'Handled response: ' . $this->responseToReturn . ' code: ' . 200);

        $this->response->setCode(200);
        $this->response->setMessage(["message" => 'Message was send']);
        $this->responseToReturn = $this->response->returnResponse();
        return $this;
    }

    /**
     * Run whole application and handle request, checking access and sending message
     * @return void
     */
    public function checkAccess(): self
    {

        $this->env = Env::getInstance();
        try {
            $this->headers = new Headers($this->server);
            $this->guard = new Guard($this->headers);
            if ($this->guard->checkAccess()) {

                return $this;
            } else {
                $this->sendInternalServerError();
            }

        } catch (Exception $exception) {
            if ($exception instanceof InvalidSecret) {
                $this->sendInvalidSecret();
            } elseif ($exception instanceof InvalidDomain) {

                $this->sendInvalidDomain();
            } elseif ($exception instanceof InvalidContentType) {
                $this->sendInvalidContentTypen();
            } else {
                $this->sendInternalServerError();
            }
            $this->errorMessage = $exception->getMessage();
            $this->isError = true;
            return $this;

        }
    }

    /**
     * Send Unauthorized when Secret mismatch
     * @return void
     */
    private function sendInvalidSecret(): void
    {
        $this->logger->log($this->uuid, 'Send invalid secret: ' . json_encode($this->payload) . ' code: ' . 401);

        $this->response->setCode(401);
        $this->response->setMessage(["message" => 'Invalid secret']);
        $this->responseToReturn = $this->response->returnResponse();
    }

    /**
     * Send Forbidden when request's domain mismatch with domain from .env
     * @return void
     */
    private function sendInvalidDomain(): void
    {
        $this->logger->log($this->uuid, 'Send invalid domain: ' . json_encode($this->payload) . ' code: ' . 403);

        $this->response->setCode(403);
        $this->response->setMessage(["message" => 'Domain not allowed']);
        $this->responseToReturn = $this->response->returnResponse();
    }

    /**
     * Send Bad Request when payload is invalid
     * @return void
     */
    private function sendInvalidPayload(): void
    {
        $this->logger->log($this->uuid, 'Send invalid payload: ' . json_encode($this->payload) . ' code: ' . 400);

        $this->response->setCode(400);
        $this->response->setMessage(["message" => 'Invalid payload']);
        $this->responseToReturn = $this->response->returnResponse();

    }

    /**
     * Return Bad Request when Content-Type is not application/json
     * @return void
     */
    private function sendInvalidContentTypen(): void
    {
        $this->logger->log($this->uuid, 'Send invalid content type: ' . json_encode($this->payload) . ' code: ' . 400);

        $this->response->setCode(400);
        $this->response->setMessage(["message" => 'Content-Type should be application/json']);
        $this->responseToReturn = $this->response->returnResponse();
    }

    /**
     * Send Internal Server Error on Exception
     * @return void
     */
    private function sendInternalServerError(): void
    {
        $this->logger->log($this->uuid, 'Send inernal server error: ' . json_encode($this->payload) . ' code: ' . 500);

        $this->response->setCode(500);
        $this->response->setMessage(["message" => 'Internal Server Error']);
        $this->responseToReturn = $this->response->returnResponse();
    }

    /**
     * Method for create mailer
     * @param Mail $mail
     * @return Mailer
     * @throws Exception
     */
    private function createMailer(Mail $mail): Mailer
    {
        return new Mailer($mail);
    }

}


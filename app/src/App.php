<?php

namespace MailService\MailService;

use MailService\MailService\Core\Env;

use MailService\MailService\Core\IHeaders;
use MailService\MailService\Core\Logger;
use MailService\MailService\Core\Response;
use MailService\MailService\Core\View;
use MailService\MailService\Exceptions\InvalidContentType;
use MailService\MailService\Exceptions\InvalidDomain;
use MailService\MailService\Exceptions\InvalidPayload;
use MailService\MailService\Exceptions\InvalidSecret;
use MailService\MailService\Factories\GuardFactory;
use MailService\MailService\Factories\HeadersFactory;
use MailService\MailService\Factories\MailerFactory;
use MailService\MailService\Factories\MailFactory;
use MailService\MailService\Factories\ResponseFactory;
use MailService\MailService\Factories\RouterFactory;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 *
 */
class App
{

    /**
     * @var array
     */
    private array $payloadArr;
    /**
     * @var array
     */
    private array $serverArr;
    /**
     * @var Env
     */
    private Env $env;

    /**
     * @var Logger
     */
    private Logger $logger;
    /**
     * @var RouterFactory
     */
    private RouterFactory $routerFactory;
    /**
     * @var MailFactory
     */
    private MailFactory $mailFactory;
    /**
     * @var MailerFactory
     */
    private MailerFactory $mailerFactory;

    /**
     * @var GuardFactory
     */
    private GuardFactory $guardFactory;
    /**
     * @var HeadersFactory
     */
    private HeadersFactory $headersFactory;
    /**
     * @var IHeaders
     */
    private IHeaders $headers;
    /**
     * @var string
     */
    private string $uuid;
    /**
     * @var Response
     */
    private Response $response;
    /**
     * @var PHPMailer
     */
    private PHPMailer $phpMailer;


    /**
     * @param array $payloadArr
     * @param array $serverArr
     * @param Logger $logger
     * @param Env $env
     * @param HeadersFactory $headersFactory
     * @param GuardFactory $guardFactory
     * @param RouterFactory $routerFactory
     * @param MailFactory $mailFactory
     * @param MailerFactory $mailerFactory
     * @param ResponseFactory $responseFactory
     * @throws \Random\RandomException
     */
    public function __construct(
        array $payloadArr,
        array $serverArr,
        Logger $logger,
        Env $env,
        HeadersFactory $headersFactory,
        GuardFactory $guardFactory,
        RouterFactory $routerFactory,
        MailFactory $mailFactory,
        MailerFactory $mailerFactory,
        ResponseFactory $responseFactory,
        PHPMailer $phpMailer
    )
    {
        $this->env = $env;
        $this->response = $responseFactory->create($env);
        $uuid = bin2hex(random_bytes(16));
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($uuid, 4));
        $this->uuid = $uuid;
        $this->logger = $logger;
        $this->logger->info($uuid, 'Application starting.');
        $this->payloadArr = $payloadArr;
        $this->serverArr = $serverArr;
        $this->routerFactory = $routerFactory;
        $this->mailFactory = $mailFactory;
        $this->mailerFactory = $mailerFactory;
        $this->guardFactory = $guardFactory;
        $this->headersFactory = $headersFactory;
        $this->phpMailer = $phpMailer;
    }

    /**
     * @return void
     */
    public function run(): void
    {

        $this->logger->info($this->uuid, 'Request handling started.', [
            'method' => $this->serverArr['REQUEST_METHOD'] ?? 'N/A',
            'uri' => $this->serverArr['REQUEST_URI'] ?? 'N/A'
        ]);

        try {
            $router = $this->routerFactory->createRouter();
            if (!$router->checkPathForMail($this->serverArr)) {
                $this->logger->notice($this->uuid, 'Request path does not match mail endpoint. Displaying default view.');
                $view = new View();
                $view->showView();
                $this->logger->info($this->uuid, 'Request handling finished (view displayed).');
                exit;
                }
            $this->logger->info($this->uuid, 'Request path matches mail endpoint.');
            $this->headers = $this->headersFactory->create($this->serverArr);
            $this->logger->debug($this->uuid, 'Headers object created and parsed.');

            $guard = $this->guardFactory->create($this->headers, $this->env, $this->response);
            $this->logger->info($this->uuid, 'Starting access check.');

            $guard->checkAccess();
            $this->logger->notice($this->uuid, 'Access check successful.');


            $mail = $this->mailFactory->createMail($this->payloadArr, $this->env);
            $this->logger->notice($this->uuid, 'Mail object created from payload.');


            $mailer = $this->mailerFactory->create($mail, $this->env, $this->phpMailer);
            $this->logger->notice($this->uuid, 'Mailer instance created and configured.');
            $this->logger->info($this->uuid, 'Attempting to send email.');
            $mailer->setup()->prepare()->sendMessage();
            $this->logger->notice($this->uuid, 'Email sent successfully.');
            $this->response->setCode(200);
            $this->response->setMessage(["message" => "Message was send"]);
        } catch (InvalidSecret $e) {
            $this->logger->warning($this->uuid, "Access denied: Invalid Secret.", [
                'error_message' => $e->getMessage()
            ]);
            $this->response->setCode(403);
            $this->response->setMessage(["message" => "Invalid credential"]);
            $this->response->setDebugMessage($this->env->getIsDebug() ? $e->getMessage() : null);

        } catch (InvalidDomain $e) {
            $this->logger->warning($this->uuid, "Access denied: Invalid Domain.", [
                'error_message' => $e->getMessage()
            ]);
            $this->response->setCode(403);
            $this->response->setMessage(["message" => "Invalid credential"]);
            $this->response->setDebugMessage($this->env->getIsDebug() ? $e->getMessage() : null);

        } catch (InvalidContentType $e) {
            $this->logger->warning($this->uuid, "Invalid Content Type.", [
                'error_message' => $e->getMessage()
            ]);
            $this->response->setCode(415);
            $this->response->setMessage(["message" => "Unsupported Media Type"]);
            $this->response->setDebugMessage($this->env->getIsDebug() ? $e->getMessage() : null);

        } catch (InvalidPayload $e) {
            $this->logger->warning($this->uuid, "Invalid Payload Exception.", [
                'error_message' => $e->getMessage(),
                'payload_preview' => substr(json_encode($this->payloadArr), 0, 200)
            ]);
            $this->response->setCode(400);
            $this->response->setMessage(["message" => "Invalid payload"]);
            $this->response->setDebugMessage($this->env->getIsDebug() ? $e->getMessage() : null);

        } catch (PHPMailerException $e) {
            $this->logger->error($this->uuid, "PHPMailer Exception during email sending.", [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->response->setCode(500);
            $this->response->setMessage(["message" => "Failed to send email"]);
            $this->response->setDebugMessage($this->env->getIsDebug() ? $e->getMessage() : null);

        } catch (\Exception $e) {
            $this->logger->critical($this->uuid, "Unexpected Application Exception.", [
                'error_message' => $e->getMessage(),
                'exception_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->response->setCode(500);
            $this->response->setMessage(["message" => "An internal error occurred"]);
            $this->response->setDebugMessage($this->env->getIsDebug() ? $e->getMessage() : null);

        } catch (\Throwable $e) {
            $this->logger->emergency($this->uuid, "EMERGENCY: Uncaught Throwable.", [
                'error_message' => $e->getMessage(),
                'exception_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->response->setCode(500);
            $this->response->setMessage(["message" => "A fatal error occurred"]);
            $this->response->setDebugMessage($this->env->getIsDebug() ? $e->getMessage() : null);
        } finally {
            $this->response->sendHeaders();

            echo $this->response->returnResponse();

            $this->logger->info($this->uuid, 'Request handling finished.', ['http_status' => $this->response->getCode()]);
        }




    }




}
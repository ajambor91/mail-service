<?php
declare(strict_types=1);

namespace MailService\MailService\Tests;

use Error;
use Exception;
use MailService\MailService\App;
use MailService\MailService\Core\Env;
use MailService\MailService\Core\Guard;
use MailService\MailService\Core\Headers;
use MailService\MailService\Core\Logger;
use MailService\MailService\Core\Mail;
use MailService\MailService\Core\Mailer;
use MailService\MailService\Core\Response;
use MailService\MailService\Core\Router;
use MailService\MailService\Enums\SSLEnum;
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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/data/message.php';
require_once __DIR__ . '/data/server.php';
require_once __DIR__ . '/data/response.php';

/**
 * Unit tests for the App class.
 */
#[CoversClass(App::class)]
final class AppTest extends TestCase
{
    /**
     * @var HeadersFactory&MockObject
     */
    private HeadersFactory $headersFactory;

    /**
     * @var Logger&MockObject
     */
    private Logger $logger;

    /**
     * @var Env&MockObject
     */
    private Env $env;

    /**
     * @var GuardFactory&MockObject
     */
    private GuardFactory $guardFactory;

    /**
     * @var RouterFactory&MockObject
     */
    private RouterFactory $routerFactory;

    /**
     * @var MailFactory&MockObject
     */
    private MailFactory $mailFactory;

    /**
     * @var MailerFactory&MockObject
     */
    private MailerFactory $mailerFactory;

    /**
     * @var ResponseFactory&MockObject
     */
    private ResponseFactory $responseFactory;

    /**
     * @var Router&MockObject
     */
    private Router $routerMock;

    /**
     * @var Mailer&MockObject
     */
    private Mailer $mailerMock;

    /**
     * @var Mail&MockObject
     */
    private Mail $mailMock;

    /**
     * @var Response&MockObject
     */
    private Response $responseMock;

    /**
     * @var Headers&MockObject
     */
    private Headers $headersMock;

    /**
     * @var Guard&MockObject
     */
    private Guard $guardMock;

    /**
     * @var PHPMailer
     */
    private PHPMailer $phpMailerMock;

    /**
     * @var App
     */
    private App $app;


    /**
     * Tests the success scenario: correct path, access granted, email sent.
     */
    #[Test]
    #[TestDox('Handles valid request: sets 200, sends email, logs success')]
    public function testRunSuccess(): void
    {
        // Arrange
        $this->routerMock->method('checkPathForMail')->willReturn(true);
        $this->guardMock->method('checkAccess')->willReturn(true);

        $this->mailerMock->expects($this->once())->method('setup')->willReturn($this->mailerMock);
        $this->mailerMock->expects($this->once())->method('prepare')->willReturn($this->mailerMock);
        $this->mailerMock->expects($this->once())->method('sendMessage');

        $this->responseMock->expects($this->once())->method('setCode')->with($this->equalTo(200));
        $this->responseMock->expects($this->once())->method('setMessage')->with($this->equalTo(["message" => "Message was send"]));
        $this->responseMock->expects($this->never())->method('setDebugMessage');
        $this->responseMock->expects($this->once())->method('sendHeaders');
        $expectedOutput = json_encode(["message" => "Message was send"]);
        $this->responseMock->method('returnResponse')->willReturn($expectedOutput);
        $this->expectOutputString($expectedOutput);

        $this->logger->expects($this->atLeastOnce())->method('info');
        $this->logger->expects($this->atLeastOnce())->method('notice');
        $this->logger->expects($this->atLeastOnce())->method('debug');
        $this->logger->expects($this->never())->method('warning');
        $this->logger->expects($this->never())->method('error');
        $this->logger->expects($this->never())->method('critical');
        $this->logger->expects($this->never())->method('emergency');

        // Act
        $this->app->run();
    }


    /**
     * Tests the access denied scenario: Guard throws InvalidSecret.
     */
    #[Test]
    #[TestDox('When Guard throws InvalidSecret, App sets 403, logs warning, and returns error')]
    public function testRunForbiddenInvalidSecret(): void
    {
        // Arrange
        $this->routerMock->method('checkPathForMail')->willReturn(true);
        $invalidSecretException = new InvalidSecret("Simulated Invalid Secret Error");
        $this->guardMock->method('checkAccess')->willThrowException($invalidSecretException);

        $this->responseMock->expects($this->once())->method('setCode')->with($this->equalTo(403));
        $this->responseMock->expects($this->once())->method('setMessage')->with($this->equalTo(["message" => "Invalid credential"]));
        $this->responseMock->expects($this->once())->method('setDebugMessage')->with($this->stringContains("Simulated Invalid Secret Error"));
        $this->responseMock->expects($this->once())->method('sendHeaders');
        $expectedOutput = json_encode(["message" => "Invalid credential", "debug_message" => "Simulated Invalid Secret Error"]);
        $this->responseMock->method('returnResponse')->willReturn($expectedOutput);
        $this->expectOutputString($expectedOutput);

        $this->mailerMock->expects($this->never())->method('setup');
        $this->mailerMock->expects($this->never())->method('prepare');
        $this->mailerMock->expects($this->never())->method('sendMessage');

        $this->logger->expects($this->once())->method('warning');
        $this->logger->expects($this->atLeastOnce())->method('info');
        $this->logger->expects($this->atLeastOnce())->method('debug');
        $this->logger->expects($this->never())->method('error');
        $this->logger->expects($this->never())->method('critical');
        $this->logger->expects($this->never())->method('emergency');

        // Act
        $this->app->run();

    }

    /**
     * Tests the access denied scenario: Guard throws InvalidDomain.
     */
    #[Test]
    #[TestDox('When Guard throws InvalidDomain, App sets 403, logs warning, and returns error')]
    public function testRunForbiddenInvalidDomain(): void
    {
        // Arrange
        $this->routerMock->method('checkPathForMail')->willReturn(true);
        $invalidDomainException = new InvalidDomain("Simulated Invalid Domain Error");
        $this->guardMock->method('checkAccess')->willThrowException($invalidDomainException);

        $this->responseMock->expects($this->once())->method('setCode')->with($this->equalTo(403));
        $this->responseMock->expects($this->once())->method('setMessage')->with($this->equalTo(["message" => "Invalid credential"]));
        $this->responseMock->expects($this->once())->method('setDebugMessage')->with($this->stringContains("Simulated Invalid Domain Error"));
        $this->responseMock->expects($this->once())->method('sendHeaders');
        $expectedOutput = json_encode(["message" => "Invalid credential", "debug_message" => "Simulated Invalid Domain Error"]);
        $this->responseMock->method('returnResponse')->willReturn($expectedOutput);
        $this->expectOutputString($expectedOutput);

        $this->mailerMock->expects($this->never())->method('setup');
        $this->mailerMock->expects($this->never())->method('prepare');
        $this->mailerMock->expects($this->never())->method('sendMessage');

        $this->logger->expects($this->once())->method('warning');
        $this->logger->expects($this->atLeastOnce())->method('info');
        $this->logger->expects($this->atLeastOnce())->method('debug');
        $this->logger->expects($this->never())->method('error');
        $this->logger->expects($this->never())->method('critical');
        $this->logger->expects($this->never())->method('emergency');

        // Act
        $this->app->run();

    }

    /**
     * Tests the validation error scenario: Guard throws InvalidContentType.
     */
    #[Test]
    #[TestDox('When Guard throws InvalidContentType, App sets 415, logs warning, and returns error')]
    public function testRunInvalidContentType(): void
    {
        // Arrange
        $this->routerMock->method('checkPathForMail')->willReturn(true);
        $invalidContentTypeException = new InvalidContentType("Simulated Invalid Content Type Error");
        $this->guardMock->method('checkAccess')->willThrowException($invalidContentTypeException);

        $this->responseMock->expects($this->once())->method('setCode')->with($this->equalTo(415));
        $this->responseMock->expects($this->once())->method('setMessage')->with($this->equalTo(["message" => "Unsupported Media Type"]));
        $this->responseMock->expects($this->once())->method('setDebugMessage')->with($this->stringContains("Simulated Invalid Content Type Error"));
        $this->responseMock->expects($this->once())->method('sendHeaders');
        $expectedOutput = json_encode(["message" => "Unsupported Media Type", "debug_message" => "Simulated Invalid Content Type Error"]);
        $this->responseMock->method('returnResponse')->willReturn($expectedOutput);
        $this->expectOutputString($expectedOutput);

        $this->mailerMock->expects($this->never())->method('setup');
        $this->mailerMock->expects($this->never())->method('prepare');
        $this->mailerMock->expects($this->never())->method('sendMessage');


        $this->logger->expects($this->atLeastOnce())->method('info');
        $this->logger->expects($this->atLeastOnce())->method('debug');
        $this->logger->expects($this->never())->method('error');
        $this->logger->expects($this->never())->method('critical');
        $this->logger->expects($this->never())->method('emergency');

        // Act
        $this->app->run();

    }

    /**
     * Tests the validation error scenario: MailFactory throws InvalidPayload.
     */
    #[Test]
    #[TestDox('When MailFactory throws InvalidPayload, App sets 400, logs warning, and returns error')]
    public function testRunInvalidPayload(): void
    {
        // Arrange
        $this->routerMock->method('checkPathForMail')->willReturn(true);
        $this->guardMock->method('checkAccess')->willReturn(true);

        $invalidPayloadException = new InvalidPayload("Simulated Invalid Payload Error");
        $this->mailFactory->method('createMail')->willThrowException($invalidPayloadException);

        $this->responseMock->expects($this->once())->method('setCode')->with($this->equalTo(400));
        $this->responseMock->expects($this->once())->method('setMessage')->with($this->equalTo(["message" => "Invalid payload"]));
        $this->responseMock->expects($this->once())->method('setDebugMessage')->with($this->stringContains("Simulated Invalid Payload Error"));
        $this->responseMock->expects($this->once())->method('sendHeaders');
        $expectedOutput = json_encode(["message" => "Invalid payload", "debug_message" => "Simulated Invalid Payload Error"]);
        $this->responseMock->method('returnResponse')->willReturn($expectedOutput);
        $this->expectOutputString($expectedOutput);

        $this->mailerMock->expects($this->never())->method('setup');
        $this->mailerMock->expects($this->never())->method('prepare');
        $this->mailerMock->expects($this->never())->method('sendMessage');

        $this->logger->expects($this->once())->method('warning');
        $this->logger->expects($this->atLeastOnce())->method('info');
        $this->logger->expects($this->atLeastOnce())->method('debug');
        $this->logger->expects($this->atLeastOnce())->method('notice');
        $this->logger->expects($this->never())->method('error');
        $this->logger->expects($this->never())->method('critical');
        $this->logger->expects($this->never())->method('emergency');

        // Act
        $this->app->run();

    }

    /**
     * Tests the email sending error scenario: Mailer throws PHPMailerException.
     */
    #[Test]
    #[TestDox('When mailer throws PHPMailerException, App sets 500, logs error, and returns error')]
    public function testRunPHPMailerException(): void
    {
        // Arrange
        $this->routerMock->method('checkPathForMail')->willReturn(true);
        $this->guardMock->method('checkAccess')->willReturn(true);

        $this->mailerMock->expects($this->once())->method('setup')->willReturn($this->mailerMock);
        $this->mailerMock->expects($this->once())->method('prepare')->willReturn($this->mailerMock);
        $phpMailerException = new PHPMailerException("Simulated PHPMailer Send Error");
        $this->mailerMock->expects($this->once())->method('sendMessage')->willThrowException($phpMailerException);

        $this->responseMock->expects($this->once())->method('setCode')->with($this->equalTo(500));
        $this->responseMock->expects($this->once())->method('setMessage')->with($this->equalTo(["message" => "Failed to send email"]));
        $this->responseMock->expects($this->once())->method('setDebugMessage')->with($this->stringContains("Simulated PHPMailer Send Error"));
        $this->responseMock->expects($this->once())->method('sendHeaders');
        $expectedOutput = json_encode(["message" => "Failed to send email", "debug_message" => "Simulated PHPMailer Send Error"]);
        $this->responseMock->method('returnResponse')->willReturn($expectedOutput);
        $this->expectOutputString($expectedOutput);
        $this->logger->expects($this->once())->method('error');
        $this->logger->expects($this->atLeastOnce())->method('info');
        $this->logger->expects($this->atLeastOnce())->method('debug');
        $this->logger->expects($this->atLeastOnce())->method('notice');
        $this->logger->expects($this->never())->method('warning');
        $this->logger->expects($this->never())->method('critical');
        $this->logger->expects($this->never())->method('emergency');

        // Act
        $this->app->run();

    }

    /**
     * Tests the unexpected error scenario: a generic Exception is thrown.
     * This simulates errors not specifically caught by other blocks.
     */
    #[Test]
    #[TestDox('When a generic Exception is thrown, App sets 500, logs critical, and returns error')]
    public function testRunGenericException(): void
    {
        // Arrange
        $genericException = new Exception("Simulated Generic Application Error");
        $this->routerMock->method('checkPathForMail')->willThrowException($genericException);

        $this->guardFactory->expects($this->never())->method('create');
        $this->mailFactory->expects($this->never())->method('createMail');
        $this->mailerFactory->expects($this->never())->method('create');
        $this->responseMock->expects($this->once())->method('setCode')->with($this->equalTo(500));
        $this->responseMock->expects($this->once())->method('setMessage')->with($this->equalTo(["message" => "An internal error occurred"]));
        $this->responseMock->expects($this->once())->method('setDebugMessage')->with($this->stringContains("Simulated Generic Application Error"));
        $this->responseMock->expects($this->once())->method('sendHeaders');
        $expectedOutput = json_encode(["message" => "An internal error occurred", "debug_message" => "Simulated Generic Application Error"]);
        $this->responseMock->method('returnResponse')->willReturn($expectedOutput);
        $this->expectOutputString($expectedOutput);

        $this->logger->expects($this->once())->method('critical')->with(
            $this->isType('string'),
            $this->stringContains('Unexpected Application Exception.'),
            $this->anything()
        );
        $this->logger->expects($this->atLeastOnce())->method('info');
        $this->logger->expects($this->never())->method('warning');
        $this->logger->expects($this->never())->method('error');
        $this->logger->expects($this->never())->method('emergency');

        // Act
        $this->app->run();

    }

    /**
     * Tests the fatal error scenario: a Throwable (e.g., Error) is thrown.
     */
    #[Test]
    #[TestDox('When a Throwable is thrown, App sets 500, logs emergency, and returns error')]
    public function testRunThrowable(): void
    {
        // Arrange
        $throwableError = new Error("Simulated Throwable Error");
        $this->routerMock->method('checkPathForMail')->willThrowException($throwableError);

        $this->guardFactory->expects($this->never())->method('create');
        $this->mailFactory->expects($this->never())->method('createMail');
        $this->mailerFactory->expects($this->never())->method('create');


        $this->responseMock->expects($this->once())->method('setCode')->with($this->equalTo(500));
        $this->responseMock->expects($this->once())->method('setMessage')->with($this->equalTo(["message" => "A fatal error occurred"]));
        $this->responseMock->expects($this->once())->method('setDebugMessage')->with($this->stringContains("Simulated Throwable Error"));
        $this->responseMock->expects($this->once())->method('sendHeaders');
        $expectedOutput = json_encode(["message" => "A fatal error occurred", "debug_message" => "Simulated Throwable Error"]);
        $this->responseMock->method('returnResponse')->willReturn($expectedOutput);
        $this->expectOutputString($expectedOutput);
        $this->logger->expects($this->once())->method('emergency')->with(
            $this->isType('string'),
            $this->stringContains('EMERGENCY: Uncaught Throwable.'),
            $this->anything()
        );
        $this->logger->expects($this->atLeastOnce())->method('info');
        $this->logger->expects($this->never())->method('warning');
        $this->logger->expects($this->never())->method('error');
        $this->logger->expects($this->never())->method('critical');


        // Act
        $this->app->run();

    }


    /**
     * Set up mocks and configure factories before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for factories and core dependencies
        $this->createMocksAndFactories();
        $this->createEnv();
        $this->app = new App(
            VALID_RAW_MESSAGE,
            VALID_SERVER_DATA,
            $this->logger,
            $this->env,
            $this->headersFactory,
            $this->guardFactory,
            $this->routerFactory,
            $this->mailFactory,
            $this->mailerFactory,
            $this->responseFactory,
            $this->phpMailerMock
        );
    }

    /**
     * Create mocks for factories and core dependencies, and configure factories
     * to return mocks of objects they create.
     */
    private function createMocksAndFactories(): void
    {
        // Create mocks for factories
        $this->env = $this->createMock(Env::class);
        $this->guardFactory = $this->createMock(GuardFactory::class);
        $this->headersFactory = $this->createMock(HeadersFactory::class);
        $this->logger = $this->createMock(Logger::class);
        $this->routerFactory = $this->createMock(RouterFactory::class);
        $this->mailFactory = $this->createMock(MailFactory::class);
        $this->mailerFactory = $this->createMock(MailerFactory::class);
        $this->responseFactory = $this->createMock(ResponseFactory::class);

        // Create mocks for objects created by factories
        $this->guardMock = $this->createMock(Guard::class);
        $this->headersMock = $this->createMock(Headers::class);
        $this->routerMock = $this->createMock(Router::class);
        $this->mailMock = $this->createMock(Mail::class);
        $this->mailerMock = $this->createMock(Mailer::class);
        $this->responseMock = $this->createMock(Response::class);
        $this->phpMailerMock = $this->createMock(PHPMailer::class);
        // Configure factories to return these mocks
        $this->guardFactory->method('create')->willReturn($this->guardMock);
        $this->headersFactory->method('create')->willReturn($this->headersMock);
        $this->routerFactory->method('createRouter')->willReturn($this->routerMock);
        $this->mailFactory->method('createMail')->willReturn($this->mailMock);
        $this->mailerFactory->method('create')->willReturn($this->mailerMock);
        $this->responseFactory->method('create')->willReturn($this->responseMock);
    }


    /**
     * Helper method to configure the Env mock.
     */
    private function createEnv(string $secret = VALID_SECRET, array $allowedDomains = VALID_ALLOWED_DOMAIN, bool $isDebug = true): void
    {
        $this->env->method('getIsDebug')->willReturn($isDebug);
        $this->env->method('getSecret')->willReturn($secret);
        $this->env->method('getAllowedDomains')->willReturn($allowedDomains);
        $this->env->method('getIsSMTP')->willReturn(true);
        $this->env->method('getHost')->willReturn('test.example.com');
        $this->env->method('getPort')->willReturn(25);
        $this->env->method('getSSL')->willReturn(SSLEnum::STARTTLS);
        $this->env->method('getSenderEmail')->willReturn('sender@test.com');
        $this->env->method('getRecipientEmail')->willReturn('recipient@test.com');
        $this->env->method('getDefaultTile')->willReturn('Default Subject');
        $this->env->method('getIsHTML')->willReturn(false);
        $this->env->method('isPHPMailerDebugMode')->willReturn(false);
        $this->env->method('getDebugLevel')->willReturn('emergency');
        $this->env->method('getSSLVerifyServerCert')->willReturn(false);
        $this->env->method('getSSLVerifyServerName')->willReturn(false);
        $this->env->method('getAllowingSelfSignCert')->willReturn(true);
        $this->env->method('getPassword')->willReturn(null);
        $this->env->method('getUsername')->willReturn(null);
        $this->env->method('getBCCMail')->willReturn(null);
        $this->env->method('getCCMail')->willReturn(null);
    }
}
<?php
declare(strict_types=1);

namespace MailService\MailService\Tests;

use Exception;
use MailService\MailService\App;
use MailService\MailService\Core\Mail;
use MailService\MailService\Core\Mailer;
use MailService\MailService\Core\Router;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

require_once __DIR__ . '/stubs/message.php';
require_once __DIR__ . '/stubs/server.php';
require_once __DIR__ . '/stubs/response.php';

/**
 *
 */
#[CoversClass(App::class)]
final class AppTest extends TestCase
{
    /**
     * @var App
     */
    private App $app;

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox('Setting serverData, expecting same value as passed')]
    public function testServer()
    {
        $this->app->setServerData(VALID_SERVER_DATA);
        $serverDataValue = TestHelper::getPrivatePropValue($this->app, 'server');
        self::assertEquals(VALID_SERVER_DATA, $serverDataValue);
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox('Setting router, expecting router instance')]
    public function testRoute()
    {

        $this->app->setServerData(VALID_SERVER_DATA)->setRouter();
        $serverDataValue = TestHelper::getPrivatePropValue($this->app, 'server');
        $routerValue = TestHelper::getPrivatePropValue($this->app, 'router');
        self::assertEquals(VALID_SERVER_DATA, $serverDataValue);
        self::assertInstanceOf(Router::class, $routerValue);
    }

    /**
     * @throws Exception
     */
    #[Test]
    #[TestDox('Setting payload, expecting same value as passed')]
    public function testPayload()
    {
        $this->app->setServerData(VALID_SERVER_DATA)->setRouter()->setPayload(VALID_RAW_MESSAGE);
        $serverDataValue = TestHelper::getPrivatePropValue($this->app, 'server');
        $routerValue = TestHelper::getPrivatePropValue($this->app, 'router');
        $payloadValue = TestHelper::getPrivatePropValue($this->app, 'payload');
        self::assertEquals(VALID_SERVER_DATA, $serverDataValue);
        self::assertInstanceOf(Router::class, $routerValue);
        self::assertEquals(VALID_RAW_MESSAGE, $payloadValue);
    }

    /**
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception|ReflectionException
     */
    #[Test]
    #[TestDox('Send request with valid payload, expecting same value as passed')]
    public function testRun()
    {

        $this->app->setServerData(VALID_SERVER_DATA)
            ->setRouter()
            ->setResponse()
            ->setPayload(VALID_RAW_MESSAGE)
            ->checkAccess()
            ->setupApp();
        $this->mockMailer();
        $this->app->sendMessage()
            ->handleResponse()
            ->setResponse();

        $responseValue = $this->getResponse();
        self::assertEquals(VALID_OUTPUT_MESSAGE, json_decode($responseValue, true));
    }

    /**
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception|ReflectionException
     */
    #[Test]
    #[TestDox('Send request with invalid payload, expecting same value as passed')]
    public function testRunInvalidRecipientEmail()
    {
        $this->app->setServerData(VALID_SERVER_DATA)
            ->setRouter()
            ->setResponse()
            ->setPayload(INVALID_RAW_MESSAGE)
            ->checkAccess()
            ->setupApp();
        $this->mockMailer();
        $this->app->sendMessage()
            ->handleResponse()
            ->setResponse();
        $response = $this->getResponse();
        self::assertEquals(INVALID_OUTPUT_BAD_REQUEST, json_decode($response, true));
    }

    /**
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception|ReflectionException
     */
    #[Test]
    #[TestDox('Send request with invalid secret, expecting invalid message unauthorized')]
    public function testRunInvalidSecret()
    {
        $this->app->setServerData(INVALID_SERVER_DATA_SECRET)
            ->setRouter()
            ->setResponse()
            ->setPayload(VALID_RAW_MESSAGE)
            ->checkAccess()
            ->setupApp();
        $this->mockMailer();
        $this->app->sendMessage()
            ->handleResponse()
            ->setResponse();
        $response = $this->getResponse();
        self::assertEquals(INVALID_OUTPUT_UNAUTHORIZED, json_decode($response, true));
    }

    /**
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception|ReflectionException
     */
    #[Test]
    #[TestDox('Send request with invalid secret, expecting invalid Content-Type message')]
    public function testRunInvalidContentType()
    {
        $this->app->setServerData(INVALID_SERVER_DATA_CONTENT_TYPE)
            ->setRouter()
            ->setResponse()
            ->setPayload(VALID_RAW_MESSAGE)
            ->checkAccess()
            ->setupApp();
        $this->mockMailer();
        $this->app->sendMessage()
            ->handleResponse()
            ->setResponse();
        $response = $this->getResponse();
        self::assertEquals(INVALID_OUTPUT_BAD_REQUEST_INVALID_CONTENT_TYPE, json_decode($response, true));
    }

    /**
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception|ReflectionException
     */
    #[Test]
    #[TestDox('Send request with invalid secret, expecting invalid Content-Type message')]
    public function testRunInvalidDomain()
    {
        $this->app->setServerData(INVALID_SERVER_DATA_DOMAIN)
            ->setRouter()
            ->setResponse()
            ->setPayload(VALID_RAW_MESSAGE)
            ->checkAccess()
            ->setupApp();
        $this->mockMailer();
        $this->app->sendMessage()
            ->handleResponse()
            ->setResponse();
        $response = $this->getResponse();
        self::assertEquals(INVALID_OUTPUT_BAD_REQUEST_INVALID_DOMAIN, json_decode($response, true));
    }

    /**
     * @return void
     */
    protected function setup(): void
    {
        $this->app = new App();
    }

    /**
     * Mocking mailer
     * @return void
     * @throws ReflectionException
     */
    private function mockMailer(): void
    {
        $mail = new Mail(VALID_RAW_MESSAGE);
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs([$mail])
            ->onlyMethods(['sendMessage'])
            ->getMock();
        $reflector = new ReflectionProperty($this->app, 'mailer');
        $reflector->setAccessible(true);
        $reflector->setValue($this->app, $mailer);
    }

    /**
     * Get response from App
     * @return string
     */
    private function getResponse(): string
    {
        $appReflector = new ReflectionClass($this->app);
        $appReflectorProp = $appReflector->getProperty('responseToReturn');
        return $appReflectorProp->getValue($this->app);
    }

}


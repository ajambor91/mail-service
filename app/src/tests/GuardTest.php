<?php

declare(strict_types=1);

namespace MailService\MailService\Tests;

use MailService\MailService\Core\Env;
use MailService\MailService\Core\Guard;
use MailService\MailService\Core\IHeaders;
use MailService\MailService\Core\Response;
use MailService\MailService\Enums\SSLEnum;
use MailService\MailService\Exceptions\InvalidContentType;
use MailService\MailService\Exceptions\InvalidDomain;
use MailService\MailService\Exceptions\InvalidSecret;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/data/message.php';
require_once __DIR__ . '/data/server.php';
require_once __DIR__ . '/data/response.php';
require_once __DIR__ . '/data/env_data.php';

/**
 * Unit tests for the Guard class.
 */
#[CoversClass(Guard::class)]
final class GuardTest extends TestCase
{
    /**
     * Mock object for IHeaders interface.
     * @var IHeaders&MockObject
     */
    private IHeaders $headersMock;

    /**
     * Mock object for Env class.
     * @var Env&MockObject
     */
    private Env $envMock;

    /**
     * Mock object for Response class.
     * @var Response&MockObject;
     */
    private Response $responseMock;

    /**
     * The Guard instance under test.
     * @var Guard
     */
    private Guard $guard;


    /**
     * Tests successful access check with valid data.
     */
    #[Test]
    #[TestDox('Allows access with valid domain, secret, and Content-Type')]
    public function testCheckAccess(): void
    {
        $this->headersMock->method('getHost')->willReturn(TEST_DOMAIN);
        $this->headersMock->method('getSecret')->willReturn(VALID_SECRET);
        $this->headersMock->method('getContentType')->willReturn('application/json');

        $this->responseMock->expects($this->once())->method('setAllowedDomain')->with(TEST_DOMAIN);

        $access = $this->guard->checkAccess();

        $this->assertTrue($access, 'Access should be allowed with valid credentials.');
    }

    /**
     * Tests that checkAccess throws InvalidDomain exception for an invalid domain.
     * @throws InvalidContentType|InvalidSecret
     */
    #[Test]
    #[TestDox('Throws InvalidDomain exception for an invalid domain')]
    public function testCheckAccessInvalidDomain(): void
    {
        $this->headersMock->method('getHost')->willReturn("invaliddomain");
        $this->headersMock->method('getSecret')->willReturn(VALID_SECRET);
        $this->headersMock->method('getContentType')->willReturn('application/json');

        $this->expectException(InvalidDomain::class);
        $this->expectExceptionMessage("Invalid domain");

        $this->responseMock->expects($this->never())->method('setAllowedDomain');

        $this->guard->checkAccess();
    }

    /**
     * Tests that checkAccess throws InvalidSecret exception for an invalid secret.
     * @throws InvalidDomain | InvalidContentType
     */
    #[Test]
    #[TestDox('Throws InvalidSecret exception for an invalid secret')]
    public function testCheckAccessInvalidSecret(): void
    {
        $this->headersMock->method('getHost')->willReturn(TEST_DOMAIN);
        $this->headersMock->method('getSecret')->willReturn("INVALID SECRET");
        $this->headersMock->method('getContentType')->willReturn('application/json');

        $this->expectException(InvalidSecret::class);
        $this->expectExceptionMessage("Invalid secret");

        $this->responseMock->expects($this->once())->method('setAllowedDomain')->with(TEST_DOMAIN);

        $this->guard->checkAccess();
    }

    /**
     * Tests that checkAccess throws InvalidContentType exception for an invalid Content-Type header.
     * @throws InvalidDomain | InvalidSecret
     */
    #[Test]
    #[TestDox('Throws InvalidContentType exception for invalid Content-Type')]
    public function testCheckAccessInvalidContentType(): void
    {
        $this->headersMock->method('getHost')->willReturn(TEST_DOMAIN);
        $this->headersMock->method('getSecret')->willReturn(VALID_SECRET);
        $this->headersMock->method('getContentType')->willReturn('invalid/json');

        $this->expectException(InvalidContentType::class);
        $this->expectExceptionMessage("Invalid content type");

        $this->responseMock->expects($this->once())->method('setAllowedDomain')->with(TEST_DOMAIN);

        $this->guard->checkAccess();
    }

    /**
     * Sets up the test environment before each test method.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createMocks();
        $this->configureEnv();
        $this->guard = new Guard($this->headersMock, $this->envMock, $this->responseMock);
    }

    /**
     * Creates mock objects for the Guard class dependencies.
     */
    private function createMocks(): void
    {
        $this->envMock = $this->createMock(Env::class);
        $this->responseMock = $this->createMock(Response::class);
        $this->headersMock = $this->createMock(IHeaders::class);
    }

    /**
     * Configures the Env mock object with standard test data.
     * Note: This helper method includes configuration for Env properties
     * that might not be directly used by the Guard class, but it maintains
     * consistency with typical Env mock setup across the test suite.
     */
    private function configureEnv(): void
    {
        $this->envMock->method('getIsDebug')->willReturn(true);
        $this->envMock->method('getSecret')->willReturn(VALID_SECRET);
        $this->envMock->method('getAllowedDomains')->willReturn(VALID_ALLOWED_DOMAIN);
        $this->envMock->method('getIsSMTP')->willReturn(true);
        $this->envMock->method('getHost')->willReturn(ENV_SMTP_HOST);
        $this->envMock->method('getPort')->willReturn(ENV_SMTP_PORT);
        $this->envMock->method('getSSL')->willReturn(SSLEnum::NONE);
        $this->envMock->method('getSenderEmail')->willReturn(ENV_SENDER_EMAIL);
        $this->envMock->method('getRecipientEmail')->willReturn(ENV_RECIPIENT_EMAIL);
        $this->envMock->method('getDefaultTile')->willReturn(ENV_DEFAULT_TITLE);
        $this->envMock->method('getIsHTML')->willReturn(false);
        $this->envMock->method('isPHPMailerDebugMode')->willReturn(false);
        $this->envMock->method('getDebugLevel')->willReturn('emergency');
        $this->envMock->method('getSSLVerifyServerCert')->willReturn(false);
        $this->envMock->method('getSSLVerifyServerName')->willReturn(false);
        $this->envMock->method('getAllowingSelfSignCert')->willReturn(true);
        $this->envMock->method('getPassword')->willReturn(null);
        $this->envMock->method('getUsername')->willReturn(null);
        $this->envMock->method('getBCCMail')->willReturn(null);
        $this->envMock->method('getCCMail')->willReturn(null);
    }
}
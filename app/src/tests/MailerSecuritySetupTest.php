<?php

declare(strict_types=1);

namespace MailService\MailService\Tests;

use MailService\MailService\Core\Env;
use MailService\MailService\Core\MailerSecuritySetup;
use MailService\MailService\Enums\SSLEnum;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the MailerSecuritySetup class.
 */
#[CoversClass(MailerSecuritySetup::class)]
final class MailerSecuritySetupTest extends TestCase
{
    /**
     * Mock object for PHPMailer.
     * @var PHPMailer&MockObject
     */
    private PHPMailer $phpMailerMock;

    /**
     * Mock object for Env class.
     * @var Env&MockObject
     */
    private Env $envMock;

    /**
     * The MailerSecuritySetup instance under test.
     * @var MailerSecuritySetup
     */
    private MailerSecuritySetup $mailerSecuritySetup;


    /**
     * Tests that setupSSL sets SMTPSecure to empty for no SSL.
     */
    #[Test]
    #[TestDox('Setup e-mail server security, with no SSL')]
    public function testSetupSSLNoSecure(): void
    {
        $this->configureEnv(SSLEnum::NONE);
        $this->mailerSecuritySetup->setupSSL();
        $this->assertEmpty($this->phpMailerMock->SMTPSecure, 'SMTPSecure should be empty for no SSL.');
    }

    /**
     * Helper method to configure the Env mock with specific values for testing MailerSecuritySetup.
     */
    private function configureEnv(
        SSLEnum     $sslOption = SSLEnum::NONE,
        string|null $testUserName = "Test user",
        string|null $testUserPassword = "Testpassword",
        bool        $getSSLVerifyServerCert = true,
        bool        $getSSLVerifyServerName = true,
        bool        $getAllowingSelfSignCert = false
    ): void
    {
        $this->envMock->method('getSSL')->willReturn($sslOption);
        $this->envMock->method('getSSLVerifyServerCert')->willReturn($getSSLVerifyServerCert);
        $this->envMock->method('getSSLVerifyServerName')->willReturn($getSSLVerifyServerName);
        $this->envMock->method('getAllowingSelfSignCert')->willReturn($getAllowingSelfSignCert);
        $this->envMock->method('getPassword')->willReturn($testUserPassword);
        $this->envMock->method('getUsername')->willReturn($testUserName);

    }

    /**
     * Tests that setupSSL sets SMTPSecure to STARTTLS.
     */
    #[Test]
    #[TestDox('Setup e-mail server security, STARTTLS')]
    public function testSetupSSLSTARTTLS(): void
    {
        $this->configureEnv(SSLEnum::STARTTLS);
        $this->mailerSecuritySetup->setupSSL();
        $this->assertEquals(PHPMailer::ENCRYPTION_STARTTLS, $this->phpMailerMock->SMTPSecure, 'SMTPSecure should be set to STARTTLS.');
    }

    /**
     * Tests that setupSSL sets SMTPSecure to SMTPS.
     */
    #[Test]
    #[TestDox('Setup e-mail server security, SMTPS')]
    public function testSetupSSLSMTPS(): void
    {
        $this->configureEnv(SSLEnum::SMTPS);
        $this->mailerSecuritySetup->setupSSL();
        $this->assertEquals(PHPMailer::ENCRYPTION_SMTPS, $this->phpMailerMock->SMTPSecure, 'SMTPSecure should be set to SMTPS.');
    }

    /**
     * Tests that SMTPAuth is false and Username/Password are empty when no user data is provided.
     */
    #[Test]
    #[TestDox('Disables SMTPAuth and clears user data when none provided')]
    public function testSetupNoUserData(): void
    {
        $this->configureEnv(SSLEnum::NONE, null, null);
        $this->mailerSecuritySetup->setupSSL();
        $this->assertFalse($this->phpMailerMock->SMTPAuth, 'SMTPAuth should be disabled.');
        $this->assertEmpty($this->phpMailerMock->Username, 'Username should be empty.');
        $this->assertEmpty($this->phpMailerMock->Password, 'Password should be empty.');
    }

    /**
     * Tests that SMTPAuth is true and Username/Password are set when user data is provided.
     */
    #[Test]
    #[TestDox('Enables SMTPAuth and sets user data when provided')]
    public function testSetupUserData(): void
    {
        $this->configureEnv(SSLEnum::NONE, 'Test user', 'Testpassword');
        $this->mailerSecuritySetup->setupSSL();
        $this->assertTrue($this->phpMailerMock->SMTPAuth, 'SMTPAuth should be enabled.');
        $this->assertEquals('Test user', $this->phpMailerMock->Username, 'Username should be set.');
        $this->assertEquals('Testpassword', $this->phpMailerMock->Password, 'Password should be set.');
    }

    /**
     * Tests that SMTPOptions are configured correctly for cert verification.
     */
    #[Test]
    #[TestDox('Configures SMTPOptions for certificate verification')]
    public function testSetupCertsVerification(): void
    {
        $this->configureEnv(SSLEnum::STARTTLS, null, null, true, true, false);
        $this->mailerSecuritySetup->setupSSL();
        $this->assertTrue($this->phpMailerMock->SMTPOptions['ssl']['verify_peer'], 'verify_peer should be true.');
        $this->assertTrue($this->phpMailerMock->SMTPOptions['ssl']['verify_peer_name'], 'verify_peer_name should be true.');
        $this->assertFalse($this->phpMailerMock->SMTPOptions['ssl']['allow_self_signed'], 'allow_self_signed should be false.');
    }

    /**
     * Tests that SMTPOptions are configured correctly to disable cert verification.
     */
    #[Test]
    #[TestDox('Configures SMTPOptions to disable certificate verification')]
    public function testSetupNoCertsVerification(): void
    {
        $this->configureEnv(SSLEnum::STARTTLS, null, null, false, false, true);
        $this->mailerSecuritySetup->setupSSL();
        $this->assertFalse($this->phpMailerMock->SMTPOptions['ssl']['verify_peer'], 'verify_peer should be false.');
        $this->assertFalse($this->phpMailerMock->SMTPOptions['ssl']['verify_peer_name'], 'verify_peer_name should be false.');
        $this->assertTrue($this->phpMailerMock->SMTPOptions['ssl']['allow_self_signed'], 'allow_self_signed should be true.');
    }

    /**
     * Sets up the test environment before each test method.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->phpMailerMock = $this->createMock(PHPMailer::class);
        $this->envMock = $this->createMock(Env::class);
        $this->mailerSecuritySetup = new MailerSecuritySetup($this->phpMailerMock, $this->envMock);
    }
}
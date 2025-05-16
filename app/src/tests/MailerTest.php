<?php

declare(strict_types=1);

namespace MailService\MailService\Tests;

use Exception;
use MailService\MailService\Core\Env;
use MailService\MailService\Core\IMail;
use MailService\MailService\Core\Mail;
use MailService\MailService\Core\Mailer;
use MailService\MailService\Enums\SSLEnum;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
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
 * Unit tests for the Mailer class.
 */
#[CoversClass(Mailer::class)]
final class MailerTest extends TestCase
{
    /**
     * @var IMail&MockObject
     */
    private IMail $mailMock;

    /**
     * @var Env&MockObject
     */
    private Env $envMock;

    /**
     * @var PHPMailer&MockObject
     */
    private PHPMailer $phpMailerMock;

    /**
     * @var Mailer
     */
    private Mailer $mailer;


    /**
     * Tests SMTP setup without SSL.
     */
    #[Test]
    #[TestDox('Sets up PHPMailer for SMTP with default settings (no SSL/TLS)')]
    public function testSetupSMTP(): void
    {
        $this->createEnv(VALID_SECRET, VALID_ALLOWED_DOMAIN);
        $this->mailer = new Mailer($this->mailMock, $this->envMock, $this->phpMailerMock);

        $this->phpMailerMock->expects($this->once())
            ->method('isSMTP');
        $this->phpMailerMock->expects($this->never())
            ->method('isMail');

        $mailer = $this->mailer->setup();

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertEquals(ENV_SMTP_HOST, $this->phpMailerMock->Host);
        $this->assertEquals(ENV_SMTP_PORT, $this->phpMailerMock->Port);
        $this->assertEquals('', $this->phpMailerMock->SMTPSecure);
    }

    /**
     * Helper method to configure the Env mock.
     * This method is NOT changed, as per user request.
     */
    private function createEnv(
        string  $secret,
        array   $allowedDomains,
        bool    $isDebug = true,
        bool    $isPHPMailerDebug = false,
        bool    $isSMTP = true,
        SSLEnum $isSSL = SSLEnum::NONE,
        string  $smtpHost = ENV_SMTP_HOST,
        int     $smtpPort = ENV_SMTP_PORT,
        string  $defaultTitle = ENV_DEFAULT_TITLE,
        string  $defaultSenderMail = ENV_SENDER_EMAIL,
        string  $defaultRecipientMail = ENV_RECIPIENT_EMAIL
    ): void
    {
        $this->envMock->method('getIsDebug')->willReturn($isDebug);
        $this->envMock->method('getSecret')->willReturn($secret);
        $this->envMock->method('getAllowedDomains')->willReturn($allowedDomains);
        $this->envMock->method('getIsSMTP')->willReturn($isSMTP);
        $this->envMock->method('getHost')->willReturn($smtpHost);
        $this->envMock->method('getPort')->willReturn($smtpPort);
        $this->envMock->method('getSSL')->willReturn($isSSL);
        $this->envMock->method('getSenderEmail')->willReturn($defaultSenderMail);
        $this->envMock->method('getRecipientEmail')->willReturn($defaultRecipientMail);
        $this->envMock->method('getDefaultTile')->willReturn($defaultTitle);
        $this->envMock->method('getIsHTML')->willReturn(false); // Default for Env mock
        $this->envMock->method('isPHPMailerDebugMode')->willReturn($isPHPMailerDebug);
        $this->envMock->method('getDebugLevel')->willReturn('emergency');
        $this->envMock->method('getSSLVerifyServerCert')->willReturn(false);
        $this->envMock->method('getSSLVerifyServerName')->willReturn(false);
        $this->envMock->method('getAllowingSelfSignCert')->willReturn(true);
        $this->envMock->method('getPassword')->willReturn(null);
        $this->envMock->method('getUsername')->willReturn(null);
        $this->envMock->method('getBCCMail')->willReturn(null); // Default for Env mock for BCC from Env
        $this->envMock->method('getCCMail')->willReturn(null);  // Default for Env mock for CC from Env
    }

    /**
     * Set up mocks before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createMocks();
    }

    private function createMocks(): void
    {
        $this->envMock = $this->createMock(Env::class);
        $this->mailMock = $this->createMock(Mail::class);
        $this->phpMailerMock = $this->createMock(PHPMailer::class);
    }

    /**
     * Tests setup for using PHP mail() function.
     */
    #[Test]
    #[TestDox('Sets up PHPMailer to use PHP mail() function')]
    public function testSetupMAIL(): void
    {
        $this->createEnv(
            VALID_SECRET,
            VALID_ALLOWED_DOMAIN,
            false,
            false,
            false
        );
        $this->mailer = new Mailer($this->mailMock, $this->envMock, $this->phpMailerMock);

        $this->phpMailerMock->expects($this->once())
            ->method('isMail');
        $this->phpMailerMock->expects($this->never())
            ->method('isSMTP');

        $mailer = $this->mailer->setup();

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertFalse($this->phpMailerMock->SMTPAuth);
        $this->assertEquals('localhost', $this->phpMailerMock->Host);
        $this->assertEquals(25, $this->phpMailerMock->Port);
    }

    /**
     * Tests SMTP setup with PHPMailer debug mode enabled.
     */
    #[Test]
    #[TestDox('Sets up PHPMailer SMTP with debug connection enabled')]
    public function testSetupSMTPDebugOn(): void
    {
        $this->createEnv(
            VALID_SECRET,
            VALID_ALLOWED_DOMAIN,
            true,
            true
        );
        $this->mailer = new Mailer($this->mailMock, $this->envMock, $this->phpMailerMock);
        $mailer = $this->mailer->setup();

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertEquals(SMTP::DEBUG_CONNECTION, $this->phpMailerMock->SMTPDebug);
    }

    /**
     * Tests SMTP setup with PHPMailer debug mode disabled.
     */
    #[Test]
    #[TestDox('Sets up PHPMailer SMTP with debug output disabled')]
    public function testSetupSMTPDebugOff(): void
    {
        $this->createEnv(
            VALID_SECRET,
            VALID_ALLOWED_DOMAIN,
            false,
            false
        );
        $this->mailer = new Mailer($this->mailMock, $this->envMock, $this->phpMailerMock);
        $mailer = $this->mailer->setup();

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertEquals(SMTP::DEBUG_OFF, $this->phpMailerMock->SMTPDebug);
    }

    /**
     * Tests SMTP setup with SMTPS (SSL) encryption.
     */
    #[Test]
    #[TestDox('Sets up PHPMailer for SMTP with SMTPS (SSL) encryption')]
    public function testSetupSMTPWithSSL(): void
    {
        $this->createEnv(
            VALID_SECRET,
            VALID_ALLOWED_DOMAIN,
            true,
            false,
            true,
            SSLEnum::SMTPS
        );
        $this->mailer = new Mailer($this->mailMock, $this->envMock, $this->phpMailerMock);

        $this->phpMailerMock->expects($this->once())
            ->method('isSMTP');

        $mailer = $this->mailer->setup();
        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertEquals(PHPMailer::ENCRYPTION_SMTPS, $this->phpMailerMock->SMTPSecure);
        $this->assertEquals(ENV_SMTP_HOST, $this->phpMailerMock->Host);
        $this->assertEquals(ENV_SMTP_PORT, $this->phpMailerMock->Port);
    }

    /**
     * Tests SMTP setup with STARTTLS encryption.
     */
    #[Test]
    #[TestDox('Sets up PHPMailer for SMTP with STARTTLS encryption')]
    public function testSetupSMTPWithTLS(): void
    {
        $this->createEnv(
            VALID_SECRET,
            VALID_ALLOWED_DOMAIN,
            true,
            false,
            true,
            SSLEnum::STARTTLS
        );
        $this->mailer = new Mailer($this->mailMock, $this->envMock, $this->phpMailerMock);

        $this->phpMailerMock->expects($this->once())
            ->method('isSMTP');

        $mailer = $this->mailer->setup();
        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertEquals(PHPMailer::ENCRYPTION_STARTTLS, $this->phpMailerMock->SMTPSecure);
        $this->assertEquals(ENV_SMTP_HOST, $this->phpMailerMock->Host);
        $this->assertEquals(ENV_SMTP_PORT, $this->phpMailerMock->Port);
    }

    /**
     * Tests prepare method with CC and BCC.
     * @throws Exception
     */
    #[Test]
    #[TestDox('Prepare email message with CC and BCC')]
    public function testPrepare(): void
    {
        $this->createEnv(VALID_SECRET, VALID_ALLOWED_DOMAIN);
        $this->createMailData(); // Defaults: isCC=true, isBCC=true

        $this->phpMailerMock->expects($this->once())->method('addAddress')->with(TEST_RECIPIENT_EMAIL);
        $this->phpMailerMock->expects($this->once())->method('addCC')->with(CC_TEST_EMAIL);
        $this->phpMailerMock->expects($this->once())->method('addBCC')->with(BCC_TEST_EMAIL);
        $this->phpMailerMock->expects($this->once())->method('isHTML')->with(false);
        $this->phpMailerMock->expects($this->once())->method('setFrom')->with(ENV_SENDER_EMAIL);

        $this->mailer = new Mailer($this->mailMock, $this->envMock, $this->phpMailerMock);
        $mailer = $this->mailer->prepare();

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertEquals(TEST_TITLE, $this->phpMailerMock->Subject);
        $this->assertEquals(TEST_MESSAGE, $this->phpMailerMock->Body);
    }

    /**
     * Helper method to set message data mock.
     * This method is NOT changed, as per user request.
     */
    private function createMailData(
        bool $isCC = true,
        bool $isBCC = true,
        bool $isTitle = true,
        bool $isArrays = false
    ): void
    {
        if ($isCC && !$isArrays) {
            $this->mailMock->method('getCCMail')->willReturn(CC_TEST_EMAIL);
        }

        if ($isCC && $isArrays) {
            $this->mailMock->method('getCCMail')->willReturn([CC_TEST_EMAIL, ANOTHER_CC_TEST_EMAIL]);
        }

        if ($isBCC && !$isArrays) {
            $this->mailMock->method('getBCCMail')->willReturn(BCC_TEST_EMAIL);
        }

        if ($isBCC && $isArrays) {
            $this->mailMock->method('getBCCMail')->willReturn([BCC_TEST_EMAIL, ANOTHER_BCC_TEST_EMAIL]);
        }


        if ($isTitle) {
            $this->mailMock->method('getTitle')->willReturn(TEST_TITLE);
        } else {
            $this->mailMock->method('getTitle')->willReturn(null); // Explicitly null if no title
        }


        $this->mailMock->method('getIsHTML')->willReturn(false);
        $this->mailMock->method('getRecipientMail')->willReturn(TEST_RECIPIENT_EMAIL);
        $this->mailMock->method('getMessage')->willReturn(TEST_MESSAGE); // Assuming string message for non-HTML
    }

    /**
     * Tests prepare method with CC but no BCC.
     * @throws Exception
     */
    #[Test]
    #[TestDox('Prepare email message with CC and no BCC')]
    public function testPrepareNoBCC(): void
    {
        $this->createEnv(VALID_SECRET, VALID_ALLOWED_DOMAIN);
        $this->createMailData(true, false); // isCC=true, isBCC=false

        $this->phpMailerMock->expects($this->once())->method('addAddress')->with(TEST_RECIPIENT_EMAIL);
        $this->phpMailerMock->expects($this->once())->method('addCC')->with(CC_TEST_EMAIL);
        $this->phpMailerMock->expects($this->never())->method('addBCC');
        $this->phpMailerMock->expects($this->once())->method('isHTML')->with(false);
        $this->phpMailerMock->expects($this->once())->method('setFrom')->with(ENV_SENDER_EMAIL);

        $this->mailer = new Mailer($this->mailMock, $this->envMock, $this->phpMailerMock);
        $mailer = $this->mailer->prepare();

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertEquals(TEST_TITLE, $this->phpMailerMock->Subject);
        $this->assertEquals(TEST_MESSAGE, $this->phpMailerMock->Body);
    }

    /**
     * Tests prepare method with BCC but no CC.
     * @throws Exception
     */
    #[Test]
    #[TestDox('Prepare email message with BCC and no CC')]
    public function testPrepareNoCC(): void
    {
        $this->createEnv(VALID_SECRET, VALID_ALLOWED_DOMAIN);
        $this->createMailData(false, true); // isCC=false, isBCC=true

        $this->phpMailerMock->expects($this->once())->method('addAddress')->with(TEST_RECIPIENT_EMAIL);
        $this->phpMailerMock->expects($this->never())->method('addCC');
        $this->phpMailerMock->expects($this->once())->method('addBCC')->with(BCC_TEST_EMAIL);
        $this->phpMailerMock->expects($this->once())->method('isHTML')->with(false);
        $this->phpMailerMock->expects($this->once())->method('setFrom')->with(ENV_SENDER_EMAIL);

        $this->mailer = new Mailer($this->mailMock, $this->envMock, $this->phpMailerMock);
        $mailer = $this->mailer->prepare();

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertEquals(TEST_TITLE, $this->phpMailerMock->Subject);
        $this->assertEquals(TEST_MESSAGE, $this->phpMailerMock->Body);
    }

    /**
     * Tests prepare method with arrays for CC and BCC.
     * @throws Exception
     */
    #[Test]
    #[TestDox('Prepare email message with multiple CC and BCC addresses')]
    public function testPrepareArrayCopies(): void
    {
        $this->createEnv(VALID_SECRET, VALID_ALLOWED_DOMAIN);
        $this->createMailData(true, true, true, true, true); // isArrays=true

        $this->phpMailerMock->expects($this->once())->method('addAddress')->with(TEST_RECIPIENT_EMAIL);
        $this->phpMailerMock->expects($this->exactly(2))
            ->method('addCC');
        $this->phpMailerMock->expects($this->exactly(2))
            ->method('addBCC');
        $this->phpMailerMock->expects($this->once())->method('isHTML')->with(false);
        $this->phpMailerMock->expects($this->once())->method('setFrom')->with(ENV_SENDER_EMAIL);

        $this->mailer = new Mailer($this->mailMock, $this->envMock, $this->phpMailerMock);
        $mailer = $this->mailer->prepare();

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertEquals(TEST_TITLE, $this->phpMailerMock->Subject);
        $this->assertEquals(TEST_MESSAGE, $this->phpMailerMock->Body);
    }

    /**
     * Tests the sendMessage method.
     */
    #[Test]
    #[TestDox('Sends the email message')]
    public function testSendMessage(): void
    {
        $this->createEnv(VALID_SECRET, VALID_ALLOWED_DOMAIN);
        $this->mailer = new Mailer($this->mailMock, $this->envMock, $this->phpMailerMock);

        $this->phpMailerMock->expects($this->once())->method('send');
        $this->mailer->sendMessage();
    }
}
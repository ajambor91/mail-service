<?php

declare(strict_types=1);

namespace MailService\MailService\Tests;

use MailService\MailService\Core\Env;
use MailService\MailService\Core\Mail;
use MailService\MailService\Exceptions\InvalidPayload;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/data/message.php';
require_once __DIR__ . '/data/env_data.php';

/**
 * Unit tests for the Mail class.
 */
#[CoversClass(Mail::class)]
final class MailTest extends TestCase
{
    /**
     * Mock object for Env class dependency.
     * Used to control environment-specific values during tests.
     * @var Env&MockObject
     */
    private Env $envMock;


    /**
     * Tests successful construction and initialization with a full valid payload.
     * Verifies that various properties are set correctly from payload values.
     * Assumes VALID_RAW_MESSAGE contains values for recipient, message, title, CC, and BCC.
     * @throws InvalidPayload If payload is invalid (should not happen in this test).
     */
    #[Test]
    #[TestDox('Initializes multiple properties correctly from a full valid payload')]
    public function testConstructor(): void
    {
        // Arrange
        $this->configureEnv();

        // Act
        $mail = new Mail(VALID_RAW_MESSAGE, $this->envMock);

        // Asserts
        $this->assertEquals(ENV_CC_MAIL, $mail->getCCMail());
        $this->assertEquals(ENV_BCC_MAIL, $mail->getBCCMail());
        $this->assertEquals(MESSAGE_RECIPIENT_MAIL, $mail->getRecipientMail());
        $this->assertEquals(MESSAGE_MESSAGE_CONTENT, $mail->getMessage());
        $this->assertEquals(MESSAGE_MESSAGE_TITLE, $mail->getTitle());

    }

    /**
     * Helper method to configure the Env mock with specific return values.
     * Configures methods from Env that are used by the Mail class constructor (initMail).
     * @param string|null $recipientMail Default recipient email from Env.
     * @param string|null $ccMail Default CC email from Env.
     * @param string|null $bccMail Default BCC email from Env.
     * @param string|null $title Default title from Env.
     * @param bool $isHTML Default isHTML value from Env.
     */
    private function configureEnv(
        string|null $recipientMail = ENV_RECIPIENT_EMAIL,
        string|null $ccMail = ENV_CC_MAIL,
        string|null $bccMail = ENV_BCC_MAIL,
        string|null $title = ENV_DEFAULT_TITLE,
        bool        $isHTML = false,
    ): void
    {
        $this->envMock->method('getRecipientEmail')->willReturn($recipientMail);
        $this->envMock->method('getBCCMail')->willReturn($bccMail);
        $this->envMock->method('getCCMail')->willReturn($ccMail);
        $this->envMock->method('getDefaultTile')->willReturn($title);
        $this->envMock->method('getIsHTML')->willReturn($isHTML);
    }

    /**
     * Tests construction with a simplest valid payload, verifying that
     * missing optional fields take their default values from Env.
     * Assumes VALID_RAW_SIMPLEST_MESSAGE contains message and recipientMail.
     * @throws InvalidPayload If payload is invalid (should not happen in this test).
     */
    #[Test]
    #[TestDox('Initializes correctly with simplest payload and uses Env defaults for missing optional fields')]
    public function testConstructorSimplestMessage(): void
    {
        // Arrange
        $this->configureEnv(
            recipientMail: ENV_RECIPIENT_EMAIL,
            ccMail: null,
            bccMail: null
        );

        // Act
        $mail = new Mail(VALID_RAW_MESSAGE, $this->envMock);

        // Asserts
        $this->assertNull($mail->getCCMail());
        $this->assertNull($mail->getBCCMail());
        $this->assertEquals(MESSAGE_RECIPIENT_MAIL, $mail->getRecipientMail());
        $this->assertEquals(MESSAGE_MESSAGE_CONTENT, $mail->getMessage());
        $this->assertEquals(MESSAGE_MESSAGE_TITLE, $mail->getTitle());

    }

    /**
     * Tests construction when recipient mail is provided as an array in the payload.
     * Verifies that the recipientMail property is set correctly to the array value.
     * Assumes payload contains a message and recipientMail as an array, and other fields are missing.
     * @throws InvalidPayload If payload is invalid (should not happen in this test).
     */
    #[Test]
    #[TestDox('Handles recipient mail correctly when provided as an array in payload')]
    public function testConstructorSimplestMessageMultipleRecipients(): void
    {
        // Arrange
        $this->configureEnv(
            recipientMail: null,
            ccMail: null,
            bccMail: null
        );
        $expectedRecipients = ['test@mail.exa', 'secondtest@mail.exa'];
        $payload = [
            'message' => MESSAGE_MESSAGE_CONTENT,
            'recipientMail' => $expectedRecipients
        ];
        // Act
        $mail = new Mail($payload, $this->envMock);

        // Asserts
        $this->assertEquals($expectedRecipients, $mail->getRecipientMail());
        $this->assertEquals(MESSAGE_MESSAGE_CONTENT, $mail->getMessage());
    }

    /**
     * Tests that constructor throws InvalidPayload when recipient mail is provided
     * as an array in the payload containing at least one invalid email address format.
     * This test assumes Helper::checkEmailValidity returns false for the invalid address format.
     * @throws InvalidPayload Expected exception because the recipient array contains an invalid email.
     */
    #[Test]
    #[TestDox('Throws InvalidPayload when recipient array in payload contains an invalid email')]
    public function testConstructorSimplestMessageMultipleInvalidRecipients(): void
    {
        // Arrange
        $this->configureEnv(
            recipientMail: null,
            ccMail: null,
            bccMail: null
        );
        $expectedRecipients = ['test@mail.exa', 'secondtestmail.exa'];
        $payload = [
            'message' => MESSAGE_MESSAGE_CONTENT,
            'recipientMail' => $expectedRecipients
        ];
        $this->expectException(InvalidPayload::class);
        $this->expectExceptionMessage('Invalid recipient email address');
        // Act
        $mail = new Mail($payload, $this->envMock);

        // Asserts
        $this->assertEquals($expectedRecipients, $mail->getRecipientMail());
        $this->assertEquals(MESSAGE_MESSAGE_CONTENT, $mail->getMessage());
    }

    /**
     * Tests that constructor throws InvalidPayload when the payload is an empty array.
     * The Mail class logic checks for empty payload first.
     * @throws InvalidPayload Expected exception.
     */
    #[Test]
    #[TestDox('Throws InvalidPayload when payload is an empty array')]
    public function testConstructorEmptyPayload(): void
    {
        // Arrange
        $this->configureEnv(
            ccMail: null,
            bccMail: null
        );
        $this->expectException(InvalidPayload::class);
        $this->expectExceptionMessage('No payload found');

        // Act
        $mail = new Mail([], $this->envMock);
    }

    /**
     * Tests that constructor throws InvalidPayload when the 'message' key is missing from the payload.
     * The Mail class logic checks for empty message after checking for empty payload.
     * @throws InvalidPayload Expected exception.
     */
    #[Test]
    #[TestDox('Throws InvalidPayload when message key is missing from payload')]
    public function testConstructorEmptyMessage(): void
    {
        // Arrange
        $this->configureEnv(
            ccMail: null,
            bccMail: null
        );
        $this->expectException(InvalidPayload::class);
        $this->expectExceptionMessage('No message found');

        // Act
        $mail = new Mail([
            'title' => MESSAGE_MESSAGE_TITLE,
            'recipientMail' => MESSAGE_RECIPIENT_MAIL
        ], $this->envMock);

    }

    /**
     * Tests that constructor throws InvalidPayload when recipient email is missing from payload
     * and Env does not provide a default (returns null or empty).
     * This test covers the "No one recipient found" validation in the Mail class.
     * @throws InvalidPayload Expected exception because no recipient is found.
     */
    #[Test]
    #[TestDox('Throws InvalidPayload when recipient is missing from payload and Env provides none')]
    public function testConstructorNoneRecipientMail(): void
    {
        // Arrange
        $this->configureEnv(
            recipientMail: null,
            ccMail: null,
            bccMail: null
        );

        $payloadWithoutRecipient = ['message' => MESSAGE_MESSAGE_CONTENT];
        // Act & Assert
        $this->expectException(InvalidPayload::class);
        $this->expectExceptionMessage('No one recipient found');
        $mail = new Mail($payloadWithoutRecipient, $this->envMock);
    }

    /**
     * Tests that constructor throws InvalidPayload when Env provides an invalid recipient email,
     * and the recipient is missing from the payload, forcing fallback to Env.
     * This test assumes Helper::checkEmailValidity returns false for the provided invalid email string.
     * @throws InvalidPayload Expected exception due to invalid recipient email from Env.
     */
    #[Test]
    #[TestDox('Throws InvalidPayload when Env provides an invalid recipient email and payload lacks it')]
    public function testConstructorInvalidRecipientEnvMail(): void
    {
        // Arrange
        $this->configureEnv(
            recipientMail: "invalidemail.com",
            ccMail: null,
            bccMail: null
        );

        $payloadWithoutRecipient = ['message' => MESSAGE_MESSAGE_CONTENT];

        $this->expectException(InvalidPayload::class);
        $this->expectExceptionMessage('Invalid recipient email address');

        // Act
        $mail = new Mail($payloadWithoutRecipient, $this->envMock);
    }

    /**
     * Tests that an invalid CC email string from Env is ignored if no CC is provided in payload.
     * No exception should be thrown, and the ccMail property should be null after construction.
     * This test assumes Helper::checkEmailValidity returns false for the provided invalid email string.
     * @throws InvalidPayload For a valid payload, this test should not throw InvalidPayload.
     */
    #[Test]
    #[TestDox('Ignores invalid CC email string from Env if no CC in payload')]
    public function testConstructorInvalidCCEnvMail(): void
    {
        // Arrange
        $this->configureEnv(
            recipientMail: ENV_RECIPIENT_EMAIL,
            ccMail: "invalidccmail",
            bccMail: null
        );
        $payload = [
            'message' => MESSAGE_MESSAGE_CONTENT,
            'recipientMail' => MESSAGE_RECIPIENT_MAIL
        ];

        //Act
        $mail = new Mail($payload, $this->envMock);

        //Assert
        $this->assertNull($mail->getCCMail());
    }

    /**
     * Tests that an invalid BCC email string from Env is ignored if no BCC is provided in payload.
     * No exception should be thrown, and the bccMail property should be null after construction.
     * This test assumes Helper::checkEmailValidity returns false for the provided invalid email string.
     * @throws InvalidPayload For a valid payload, this test should not throw InvalidPayload.
     */
    #[Test]
    #[TestDox('Ignores invalid BCC email string from Env if no BCC in payload')]
    public function testConstructorInvalidBCCEnvMail(): void
    {
        // Arrange
        $this->configureEnv(
            recipientMail: ENV_RECIPIENT_EMAIL,
            ccMail: null,
            bccMail: "invalidbccmail"
        );
        $payload = [
            'message' => MESSAGE_MESSAGE_CONTENT,
            'recipientMail' => MESSAGE_RECIPIENT_MAIL
        ];

        //Act
        $mail = new Mail($payload, $this->envMock);

        // Assert
        $this->assertNull($mail->getBCCMail());
    }

    /**
     * Tests that constructor throws InvalidPayload when payload contains an invalid CC email string,
     * even if Env provides a valid default. Payload invalidity takes precedence.
     * This test assumes Helper::checkEmailValidity returns false for the provided invalid email string in payload.
     * @throws InvalidPayload Expected exception due to invalid CC in payload.
     */
    #[Test]
    #[TestDox('Throws InvalidPayload for invalid CC email string in payload')]
    public function testConstructorValidCCEnvMailInvalidPayload(): void
    {
        // Arrange
        $this->configureEnv(
            recipientMail: ENV_RECIPIENT_EMAIL,
            ccMail: ENV_CC_MAIL,
            bccMail: null
        );
        $message = VALID_RAW_MESSAGE;
        $message['ccMail'] = "Invalidmail.com";
        $this->expectException(InvalidPayload::class);
        $this->expectExceptionMessage('Invalid cc email email address');

        //Act
        $mail = new Mail($message, $this->envMock);
    }

    /**
     * Tests that constructor throws InvalidPayload when payload contains an invalid BCC email string,
     * even if Env provides a valid default. Payload invalidity takes precedence.
     * This test assumes Helper::checkEmailValidity returns false for the provided invalid email string in payload.
     * @throws InvalidPayload Expected exception due to invalid BCC in payload.
     */
    #[Test]
    #[TestDox('Throws InvalidPayload for invalid BCC email string in payload')]
    public function testConstructorValidBCCEnvMailInvalidPayload(): void
    {
        // Act
        $this->configureEnv(
            recipientMail: ENV_RECIPIENT_EMAIL,
            ccMail: null,
            bccMail: ENV_BCC_MAIL
        );
        $message = VALID_RAW_MESSAGE;
        $message['bccMail'] = "Invalidmail.com";
        $this->expectException(InvalidPayload::class);
        $this->expectExceptionMessage('Invalid bcc email address');

        // Act
        $mail = new Mail($message, $this->envMock);
    }

    /**
     * Tests construction when isHTML is true in Env defaults, but not in payload.
     * Verifies that HTML is enabled from Env default and no template is set (as not in payload).
     * @throws InvalidPayload If payload is invalid (should not happen in this test).
     */
    #[Test]
    #[TestDox('Enables HTML from Env default when missing in payload and sets no template')]
    public function testConstructorEnableHTMLDefaultTemplate(): void
    {
        // Arrange
        $this->configureEnv(
            recipientMail: ENV_RECIPIENT_EMAIL,
            ccMail: null,
            bccMail: null,
            isHTML: true
        );
        $payload = [
            'message' => MESSAGE_MESSAGE_CONTENT,
            'recipientMail' => MESSAGE_RECIPIENT_MAIL
        ];

        //Act
        $mail = new Mail($payload, $this->envMock);

        //Assert
        $this->assertTrue($mail->getIsHTML());
        $this->assertNull($mail->getTemplate());
    }

    /**
     * Tests construction when isHTML is explicitly true in payload, overriding Env default.
     * Verifies that HTML is enabled from payload and no template is set (as not in payload).
     * @throws InvalidPayload If payload is invalid (should not happen in this test).
     */
    #[Test]
    #[TestDox('Enables HTML from payload (overrides Env) and sets no template')]
    public function testConstructorEnableHTMLByPayloadDefaultTemplate(): void
    {
        // Arrange
        $this->configureEnv();
        $payload = [
            'message' => MESSAGE_MESSAGE_CONTENT,
            'recipientMail' => MESSAGE_RECIPIENT_MAIL,
            'isHTML' => true
        ];

        //Act
        $mail = new Mail($payload, $this->envMock);

        //Assert
        $this->assertTrue($mail->getIsHTML());
        $this->assertNull($mail->getTemplate());
    }

    /**
     * Tests construction when isHTML is explicitly false in payload, overriding Env default of true.
     * Verifies that HTML is disabled.
     * NOTE: The original assertion in this test method is assertTrue, which contradicts the test name and payload.
     * The documentation reflects the code as written, despite this potential logical issue.
     * @throws InvalidPayload If payload is invalid (should not happen in this test).
     */
    #[Test]
    #[TestDox('Attempts to disable HTML via payload but asserts true (potential test logic issue)')]
    public function testConstructorDisableHTMLByPayload(): void
    {
        // Arrange
        $this->configureEnv(isHTML: true);
        $payload = [
            'message' => MESSAGE_MESSAGE_CONTENT,
            'recipientMail' => MESSAGE_RECIPIENT_MAIL,
            'isHTML' => false
        ];

        //Act
        $mail = new Mail($payload, $this->envMock);

        //Assert
        $this->assertTrue($mail->getIsHTML());
        $this->assertNull($mail->getTemplate());
    }

    /**
     * Tests construction when isHTML is true and template is provided in payload.
     * Verifies that both HTML flag and template property are set correctly from payload.
     * @throws InvalidPayload If payload is invalid (should not happen in this test).
     */
    #[Test]
    #[TestDox('Enables HTML and sets template from payload')]
    public function testConstructorEnableHTMLByPayloadSelectedTemplate(): void
    {
        // Arrange
        $this->configureEnv();
        $payload = [
            'message' => MESSAGE_MESSAGE_CONTENT,
            'recipientMail' => MESSAGE_RECIPIENT_MAIL,
            'isHTML' => true,
            'template' => 'main'
        ];

        //Act
        $mail = new Mail($payload, $this->envMock);

        //Assert
        $this->assertTrue($mail->getIsHTML());
        $this->assertEquals('main', $mail->getTemplate());
    }

    /**
     * Tests construction when isHTML is not explicitly true in payload (missing or false),
     * even if template is provided.
     * Verifies that HTML defaults to false and template is ignored.
     * @throws InvalidPayload If payload is invalid (should not happen in this test).
     */
    #[Test]
    #[TestDox('Defaults isHTML to false and ignores template when not explicitly true in payload')]
    public function testConstructorDisableHTMLAndDisableTemplate(): void
    {
        // Arrange
        $this->configureEnv();
        $payload = [
            'message' => MESSAGE_MESSAGE_CONTENT,
            'recipientMail' => MESSAGE_RECIPIENT_MAIL,
            'template' => 'main'
        ];

        //Act
        $mail = new Mail($payload, $this->envMock);

        //Assert
        $this->assertFalse($mail->getIsHTML());
        $this->assertNull($mail->getTemplate());
    }

    /**
     * Sets up the test environment before each test method.
     * Creates the Env mock object.
     * @throws Exception PHPUnit Mocking exception if mock creation fails.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->envMock = $this->createMock(Env::class);

    }


}
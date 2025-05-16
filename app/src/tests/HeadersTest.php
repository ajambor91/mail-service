<?php

declare(strict_types=1);

namespace MailService\MailService\Tests;


use MailService\MailService\Core\Headers;
use MailService\MailService\Exceptions\InvalidContentType;
use MailService\MailService\Exceptions\InvalidHost;
use MailService\MailService\Exceptions\InvalidSecret;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/data/message.php';
require_once __DIR__ . '/data/env_data.php';
require_once __DIR__ . '/data/server.php';

/**
 * Unit tests for the Headers class.
 */
#[CoversClass(Headers::class)] //
final class HeadersTest extends TestCase
{


    /**
     * Tests the constructor and verifies correct header parsing for valid input.
     *
     * @return void
     */
    #[Test]
    #[TestDox('Tests constructor with valid headers')]
    public function testConstructor(): void
    {
        //Act
        $headers = new Headers(VALID_SERVER_DATA);
        //Asserts
        $this->assertEquals('application/json', $headers->getContentType());
        $this->assertEquals(VALID_SECRET, $headers->getSecret());
        $this->assertEquals(TEST_DOMAIN, $headers->getHost());
    }

    /**
     * Tests that InvalidContentType exception is thrown when Content-Type header is missing.
     *
     * @return void
     */
    #[Test]
    #[TestDox('Tests that InvalidContentType exception is thrown when Content-Type header is missing')]
    public function testConstructorInvalidContentType(): void
    {
        //Assert
        $this->expectException(InvalidContentType::class);
        $this->expectExceptionMessage("No content type found");
        //Act
        new Headers(INVALID_SERVER_DATA_EMPTY_CONTENT_TYPE);

    }

    /**
     * Tests that InvalidHost exception is thrown when Host header is missing.
     *
     * @return void
     */
    #[Test]
    #[TestDox('Tests that InvalidHost exception is thrown when Host header is missing')]
    public function testConstructorInvalidHost(): void
    {
        //Assert
        $this->expectException(InvalidHost::class);
        $this->expectExceptionMessage("No host found");
        //Act
        new Headers(INVALID_SERVER_DATA_EMPTY_HOST);
    }

    /**
     * Tests that InvalidSecret exception is thrown when Secret header is missing.
     * @return void
     */
    #[Test]
    #[TestDox('Tests that InvalidSecret exception is thrown when Secret header is missing')]
    public function testConstructorInvalidSecret(): void
    {
        //Assert
        $this->expectException(InvalidSecret::class);
        $this->expectExceptionMessage("No secret found");
        //Act
        new Headers(INVALID_SERVER_DATA_EMPTY_SECRET);
    }

    /**
     * Sets up the test environment before each test method.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
}
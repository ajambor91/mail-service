<?php

declare(strict_types=1);

namespace MailService\MailService\Tests;

use MailService\MailService\Core\Env;
use MailService\MailService\Core\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for the Response class.
 */
#[CoversClass(Response::class)]
final class ResponseTest extends TestCase
{
    /**
     * Mock object for Env class.
     * @var Env&MockObject
     */
    private Env $envMock;

    /**
     * The Response instance under test.
     * @var Response
     */
    private Response $response;

    /**
     * Tests setting the allowed domain header.
     */
    #[Test]
    #[TestDox('Sets the Access-Control-Allow-Origin header')]
    public function testSetAllowedDomain(): void
    {
        $domain = 'https://przyklad.com';
        $this->response->setAllowedDomain($domain);

        $headers = $this->getPrivatePropertyValue($this->response, 'headers');

        $this->assertArrayHasKey('Access-Control-Allow-Origin', $headers);
        $this->assertEquals($domain, $headers['Access-Control-Allow-Origin']);
    }

    /**
     * Helper method to get the value of a private property using Reflection.
     * Useful for checking the internal state modified by setter methods.
     * @param object $object The object from which to get the property.
     * @param string $propertyName The name of the private property.
     * @return mixed The value of the private property.
     */
    private function getPrivatePropertyValue(object $object, string $propertyName): mixed
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * Tests setting the main response message.
     */
    #[Test]
    #[TestDox('Sets the main response message')]
    public function testSetMessage(): void
    {
        $message = ['status' => 'success'];
        $this->response->setMessage($message);

        $actualMessage = $this->getPrivatePropertyValue($this->response, 'message');

        $this->assertEquals($message, $actualMessage);
    }

    /**
     * Tests setting the debug message.
     */
    #[Test]
    #[TestDox('Sets the debug message')]
    public function testSetDebugMessage(): void
    {
        $debugMessage = 'Debug message';
        $this->response->setDebugMessage($debugMessage);

        $actualDebugMessage = $this->getPrivatePropertyValue($this->response, 'debugMessage');

        $this->assertEquals($debugMessage, $actualDebugMessage);
    }

    /**
     * Tests setting and getting the HTTP response code.
     */
    #[Test]
    #[TestDox('Sets and gets the HTTP response code')]
    public function testSetCodeAndGetCode(): void
    {
        $code = 201;
        $this->response->setCode($code);
        $actualCodeSet = $this->getPrivatePropertyValue($this->response, 'code');

        $this->assertEquals($code, $actualCodeSet);
        $this->assertEquals($code, $this->response->getCode());
    }

    /**
     * Tests the returnResponse method when debug mode is enabled.
     * Asserts that the returned JSON includes both the main message and the debug message.
     */
    #[Test]
    #[TestDox('Returns JSON response including debug message when debug is enabled')]
    public function testReturnResponseDebugOn(): void
    {
        $this->envMock->method('getIsDebug')->willReturn(true);

        $message = ['status' => 'success'];
        $debugMessage = 'Debug message';
        $this->response->setMessage($message);
        $this->response->setDebugMessage($debugMessage);

        $expectedJson = json_encode(array_merge($message, ['debugMessage' => $debugMessage]));
        $actualJson = $this->response->returnResponse();

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }

    /**
     * Tests the returnResponse method when debug mode is disabled.
     * Asserts that the returned JSON includes only the main message.
     */
    #[Test]
    #[TestDox('Returns JSON response excluding debug message when debug is disabled')]
    public function testReturnResponseDebugOff(): void
    {
        $this->envMock->method('getIsDebug')->willReturn(false);

        $message = ['status' => 'success'];
        $debugMessage = 'Debug message';
        $this->response->setMessage($message);
        $this->response->setDebugMessage($debugMessage);
        $expectedJson = json_encode($message);
        $actualJson = $this->response->returnResponse();

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }

    /**
     * Tests the sendHeaders method's return value when the main message is set.
     * Note: Direct testing of global header() and http_response_code() calls is complex
     * and typically requires mocking global functions or running tests in a separate process.
     * This test focuses on the method's internal logic regarding message presence and its return value.
     */
    #[Test]
    #[TestDox('Returns 1 when message is set, indicating readiness to send headers/code')]
    public function testSendHeadersWithMessage(): void
    {
        $this->response->setMessage(['status' => 'ok']);
        $this->response->setCode(200);
        $result = $this->response->sendHeaders();

        $this->assertEquals(1, $result);
    }

    /**
     * Sets up the test environment before each test method.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->envMock = $this->createMock(Env::class);
        $this->response = new Response($this->envMock);
    }
}
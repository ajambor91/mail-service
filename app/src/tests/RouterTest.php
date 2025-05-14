<?php

declare(strict_types=1);

namespace MailService\MailService\Tests;

use MailService\MailService\Core\Router;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/data/server.php';

/**
 * Unit tests for the Router class.
 */
#[CoversClass(Router::class)]
final class RouterTest extends TestCase
{
    /**
     * The Router instance under test.
     * @var Router
     */
    private Router $router;

    /**
     * Sets up the test environment before each test method.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();
    }

    /**
     * Tests checkPathForMail method with a valid '/send' path.
     */
    #[Test]
    #[TestDox('Identifies "/send" as a valid mail path')]
    public function testCheckPathForMailValidSendPath(): void
    {
        $isValidSendPath = $this->router->checkPathForMail(VALID_SERVER_DATA);
        $this->assertTrue($isValidSendPath);
    }


    /**
     * Tests checkPathForMail method with the homepage path '/'.
     */
    #[Test]
    #[TestDox('Identifies "/send?..." as a valid mail path')]
    public function testCheckPathForMailValidSendPathWithQuery(): void
    {
        $isValidSendPath = $this->router->checkPathForMail(VALID_SERVER_DATA_HOMEPAGE);
        $this->assertTrue($isValidSendPath);
    }

}
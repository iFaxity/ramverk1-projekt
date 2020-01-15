<?php

namespace Faxity\Auth;

use Anax\DI\DI;
use PHPUnit\Framework\TestCase;
use Faxity\DI\DISorcery;
use Faxity\Models\User;
use Faxity\Test\DITestCase;

/**
 * Test the Auth DI Service.
 */
class AuthTest extends DITestCase
{
    /** @var Auth $auth */
    private $auth;

    protected function createDI(): DI
    {
        // Create dependency injector with services
        $di = new DISorcery(TEST_INSTALL_PATH, ANAX_INSTALL_PATH . "/vendor");
        $di->initialize("config/sorcery.php");
        return $di;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->auth = new Auth();
        $this->auth->setDI($this->di);
        createTestDatabase();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->auth = null;
    }


    private function initDb(): void
    {
        $user = new User($this->di->dbqb);
        $user->alias = "foo";
        $user->email = "foo@example.com";
        $user->setPassword("shh_secret");
        $user->save();
    }


    public function testInitialize(): void
    {
        $this->initDb();

        // Test without user in session
        $this->auth->initialize();
        $this->assertFalse($this->auth->loggedIn());

        // Test with undefined user in session
        $this->di->session->set("uid", 0);
        $this->auth->initialize();
        $this->assertFalse($this->auth->loggedIn());

        // Test with defined user in session
        $this->di->session->set("uid", 1);
        $this->auth->initialize();
        $this->assertTrue($this->auth->loggedIn());
    }


    public function testLoggedIn(): void
    {
        $this->assertFalse($this->auth->loggedIn());
        $this->auth->setUser(new User());
        $this->assertTrue($this->auth->loggedIn());
    }


    public function testLogin(): void
    {
        $this->initDb();
        $this->assertFalse($this->auth->loggedIn());
        $this->auth->login("foo", "shh_secret");
        $this->assertTrue($this->auth->loggedIn());

        // Just to trigger the if statement
        $this->auth->login("", "");
    }

    public function testLoginFail(): void
    {
        $this->expectException(Exception::class);
        $this->auth->login("letmein", "please?");
    }


    public function testRegister(): void
    {
        $this->assertFalse($this->auth->loggedIn());
        $this->auth->register("hugs", "hugs@example.com", "hugsandkisses");
        $this->assertTrue($this->auth->loggedIn());
    }


    public function testLogout(): void
    {
        $this->auth->setUser(new User());
        $this->assertTrue($this->auth->loggedIn());
        $this->auth->logout();
        $this->assertFalse($this->auth->loggedIn());
    }
}

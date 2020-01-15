<?php

namespace Faxity\Site;

use Anax\DI\DI;
use Anax\Route\Exception\ForbiddenException;
use Anax\Route\Exception\NotFoundException;
use Faxity\Test\ControllerTestCase;
use Faxity\DI\DISorcery;
use Faxity\Models\User;

/**
 * Test Answer Controller.
 */
class ControllerTest extends ControllerTestCase
{
    protected $className = Controller::class;
    /** @var Controller $controller */
    protected $controller;
    /** @var User $user */
    private $user;


    protected function createDI(): DI
    {
        $di = new DISorcery(TEST_INSTALL_PATH, ANAX_INSTALL_PATH . "/vendor");
        $di->initialize("config/sorcery.php");

        return $di;
    }


    public function setUp(): void
    {
        parent::setUp();
        createTestDatabase();
        populateTestDatabase();

        // Create user for these tests
        $this->user = new User($this->di->dbqb);
        $this->user->alias = "site";
        $this->user->email = "site@example.com";
        $this->user->setPassword("site");
        $this->user->save();
    }

    public function tearDown(): void
    {
        $this->di->auth->logout();
        parent::tearDown();
    }


    public function testCatchAll(): void
    {
        $res = $this->controller->catchAll();
        $this->assertFalse($res);
    }


    public function testIndexActionGet(): void
    {
        $res = $this->controller->indexActionGet();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Welcome to CodeCommunity</h1>', $body);
        $this->assertContains('<ul class="questions">', $body);
        $this->assertContains('<ul class="tags">', $body);
        $this->assertContains('<ul class="users">', $body);
    }


    public function testAboutActionGet(): void
    {
        $res = $this->controller->aboutActionGet();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>About</h1>', $body);
    }


    public function testUsersActionGet(): void
    {
        $res = $this->controller->usersActionGet();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Users</h1>', $body);
        $this->assertContains('<div class="users">', $body);

        // Now try with user alias
        $res = $this->controller->usersActionGet("site");
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>site</h1>', $body);
        $this->assertContains('<ul class="questions">', $body);
        $this->assertContains('<ul class="answers">', $body);
        $this->assertContains('<ul class="comments">', $body);
    }


    public function testUsersActionGetFail(): void
    {
        $this->expectException(NotFoundException::class);
        $this->controller->usersActionGet("notarealuser");
    }


    public function testProfileActionGet(): void
    {
        $this->di->auth->setUser($this->user);
        $res = $this->controller->profileAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Profile: site</h1>', $body);
    }


    public function testProfileActionPost(): void
    {
        $this->di->request->setPost("id", $this->user->id);
        $this->di->request->setPost("alias", "etis");
        $this->di->request->setPost("email", "etis@example.com");
        $this->di->request->setPost("password", "etis");
        $this->di->request->setPost("password-verify", "etis");
        $this->di->request->setPost("submit", "Update profile");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Site\HTMLForm\ProfileForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $this->di->auth->setUser($this->user);
        $res = $this->controller->profileAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create($this->di->request->getCurrentUrl());
        $this->assertContains("Location: $url", $headers);

        // Check if user alias was changed
        $user = new User($this->di->dbqb);
        $user->findWhere("alias = ?", "site");
        $this->assertNull($user->id);

        // Check if user profile was updated
        $user = new User($this->di->dbqb);
        $user->findWhere("alias = ?", "etis");
        $this->assertIsInt($user->id);
        $this->assertEquals($user->alias, "etis");
        $this->assertEquals($user->email, "etis@example.com");
        $this->assertTrue($user->verifyPassword("etis"));
    }


    public function testProfileActionFail(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->controller->profileAction();
    }


    public function testLoginActionGet(): void
    {
        $res = $this->controller->loginAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Login</h1>', $body);
    }


    public function testLoginActionPostUsername(): void
    {
        $this->di->request->setPost("user", "site");
        $this->di->request->setPost("password", "site");
        $this->di->request->setPost("submit", "Login");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Site\HTMLForm\LoginForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $res = $this->controller->loginAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("");
        $this->assertContains("Location: $url", $headers);

        // Check so the user logged in
        $this->assertTrue($this->di->auth->loggedIn());
    }


    public function testLoginActionPostEmail(): void
    {
        $this->di->request->setPost("user", "site@example.com");
        $this->di->request->setPost("password", "site");
        $this->di->request->setPost("submit", "Login");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Site\HTMLForm\LoginForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $res = $this->controller->loginAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("");
        $this->assertContains("Location: $url", $headers);

        // Check so the user logged in
        $this->assertTrue($this->di->auth->loggedIn());
    }


    public function testLoginActionRedirect(): void
    {
        $this->di->auth->setUser($this->user);
        $res = $this->controller->loginAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("");
        $this->assertContains("Location: $url", $headers);
    }


    public function testRegisterActionGet(): void
    {
        $res = $this->controller->registerAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Register</h1>', $body);
    }


    public function testRegisterActionPost(): void
    {
        $this->di->request->setPost("username", "madmax");
        $this->di->request->setPost("email", "madmax@example.com");
        $this->di->request->setPost("password", "madmax_pwd");
        $this->di->request->setPost("password-verify", "madmax_pwd");
        $this->di->request->setPost("submit", "Register");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Site\HTMLForm\RegisterForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $res = $this->controller->registerAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("");
        $this->assertContains("Location: $url", $headers);

        // Check that the user was created and logged in
        $this->assertTrue($this->di->auth->loggedIn());

        $user = new User($this->di->dbqb);
        $user->findWhere("alias = ?", "madmax");
        $this->assertIsInt($user->id);
        $this->assertEquals($user->email, "madmax@example.com");
    }


    public function testRegisterActionPostFail(): void
    {
        $this->di->request->setPost("username", "fury");
        $this->di->request->setPost("email", "fury@example.com");
        $this->di->request->setPost("password", "password123");
        $this->di->request->setPost("password-verify", "somepassword");
        $this->di->request->setPost("submit", "Register");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Site\HTMLForm\RegisterForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $res = $this->controller->registerAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create($this->di->request->getCurrentUrl());
        $this->assertContains("Location: $url", $headers);

        // Check that the user wasn't created
        $this->assertFalse($this->di->auth->loggedIn());

        $user = new User($this->di->dbqb);
        $user->findWhere("alias = ?", "fury");
        $this->assertNull($user->id);
    }


    public function testRegisterActionRedirect(): void
    {
        $this->di->auth->setUser($this->user);
        $res = $this->controller->registerAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("");
        $this->assertContains("Location: $url", $headers);
    }


    public function testLogoutActionGet(): void
    {
        $this->di->auth->setUser($this->user);
        $this->assertTrue($this->di->auth->loggedIn());
        $res = $this->controller->logoutActionGet();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("");
        $this->assertContains("Location: $url", $headers);
        $this->assertFalse($this->di->auth->loggedIn());
    }
}

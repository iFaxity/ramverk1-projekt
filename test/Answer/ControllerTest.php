<?php

namespace Faxity\Answer;

use Anax\DI\DI;
use Anax\Route\Exception\ForbiddenException;
use Faxity\Test\ControllerTestCase;
use Faxity\DI\DISorcery;
use Faxity\Models\Answer;
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
        $this->user->alias = "answer";
        $this->user->email = "answer@example.com";
        $this->user->setPassword("answer");
        $this->user->save();
    }

    public function tearDown(): void
    {
        $this->di->auth->logout();
        parent::tearDown();
    }


    public function testUpdateActionGet(): void
    {
        $this->di->auth->login("const", "const");
        $res = $this->controller->updateAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Update answer</h1>', $body);
    }


    public function testUpdateActionPost(): void
    {
        $this->di->request->setPost("id", 1);
        $this->di->request->setPost("content", "Updated content");
        $this->di->request->setPost("submit", "Update answer");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Answer\HTMLForm\UpdateForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $this->di->auth->login("const", "const");
        $res = $this->controller->updateAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("question/1");
        $this->assertContains("Location: $url", $headers);

        // Check that the answer updated
        $answer = new Answer($this->di->dbqb);
        $answer = $answer->findById(1);
        $this->assertInstanceOf(Answer::class, $answer);
        $this->assertEquals($answer->content, "Updated content");
    }


    public function testUpdateActionFail(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->controller->updateAction(1);
    }


    public function testUpdateActionFail2(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->di->auth->setUser($this->user);
        $this->controller->updateAction("notanid");
    }


    public function testUpdateActionFail3(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->di->auth->setUser($this->user);
        $this->controller->updateAction(1);
    }


    public function testDeleteActionGet(): void
    {
        $this->di->auth->login("const", "const");
        $res = $this->controller->deleteAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Delete answer</h1>', $body);
    }


    public function testDeleteActionPost(): void
    {
        $this->di->request->setPost("id", 1);
        $this->di->request->setPost("submit", "Delete answer");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Answer\HTMLForm\DeleteForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $this->di->auth->login("const", "const");
        $res = $this->controller->deleteAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("question/1");
        $this->assertContains("Location: $url", $headers);

        // Check that the answer was deleted
        $answer = new Answer($this->di->dbqb);
        $answer = $answer->findById(1);
        $this->assertInstanceOf(Answer::class, $answer);
        $this->assertNull($answer->id);
    }


    public function testDeleteActionFail(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->controller->deleteAction(1);
    }


    public function testDeleteActionFail2(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->di->auth->setUser($this->user);
        $this->controller->deleteAction("notanid");
    }


    public function testDeleteActionFail3(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->di->auth->setUser($this->user);
        $this->controller->deleteAction(1);
    }


    public function testUpvoteActionPost(): void
    {
        $this->di->auth->setUser($this->user);
        $res = $this->controller->upvoteActionPost(3);

        $this->assertIsArray($res);
        list($json, $status) = $res;

        $this->assertEquals($status, 200);
        $this->assertIsArray($json);
        $this->assertIsString($json["message"]);
        $this->assertIsInt($json["votes"]);
    }


    public function testUpvoteActionPostFail(): void
    {
        
        // Test without user
        $res = $this->controller->upvoteActionPost(1);

        $this->assertIsArray($res);
        list($json, $status) = $res;

        $this->assertEquals($status, 400);
        $this->assertIsArray($json);
        $this->assertIsString($json["message"]);

        // Test with user but invalid answerId
        $this->di->auth->setUser($this->user);
        $res = $this->controller->upvoteActionPost(900);

        $this->assertIsArray($res);
        list($json, $status) = $res;

        $this->assertEquals($status, 400);
        $this->assertIsArray($json);
        $this->assertIsString($json["message"]);
    }


    public function testDownvoteActionPost(): void
    {
        $this->di->auth->setUser($this->user);
        $res = $this->controller->downvoteActionPost(1);

        $this->assertIsArray($res);
        list($json, $status) = $res;

        $this->assertEquals($status, 200);
        $this->assertIsArray($json);
        $this->assertIsString($json["message"]);
        $this->assertIsInt($json["votes"]);
    }


    public function testDownvoteActionPostError(): void
    {
        // Test without user
        $res = $this->controller->downvoteActionPost(3);

        $this->assertIsArray($res);
        list($json, $status) = $res;

        $this->assertEquals($status, 400);
        $this->assertIsArray($json);
        $this->assertIsString($json["message"]);

        // Test with user but invalid answerId
        $this->di->auth->setUser($this->user);
        $res = $this->controller->downvoteActionPost(-1);

        $this->assertIsArray($res);
        list($json, $status) = $res;

        $this->assertEquals($status, 400);
        $this->assertIsArray($json);
        $this->assertIsString($json["message"]);
    }


    public function testVoteAsAuthor(): void
    {
        // Test as author of the comment (should fail)
        $this->di->auth->login("const", "const");
        $res = $this->controller->upvoteActionPost(3);

        $this->assertIsArray($res);
        list($json, $status) = $res;

        $this->assertEquals($status, 400);
        $this->assertIsArray($json);
        $this->assertIsString($json["message"]);
    }
}

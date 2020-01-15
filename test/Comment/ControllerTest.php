<?php

namespace Faxity\Comment;

use Anax\DI\DI;
use Anax\Route\Exception\ForbiddenException;
use Faxity\Test\ControllerTestCase;
use Faxity\DI\DISorcery;
use Faxity\Models\Comment;
use Faxity\Models\User;

/**
 * Test Comment Controller.
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

        // Create imaginary user for these tests
        $this->user = new User($this->di->dbqb);
        $this->user->alias = "comment";
        $this->user->email = "comment@example.com";
        $this->user->setPassword("comment");
        $this->user->save();
    }

    public function tearDown(): void
    {
        $this->di->auth->logout();
        parent::tearDown();
    }


    public function testQuestionActionGet(): void
    {
        $this->di->auth->setUser($this->user);
        $res = $this->controller->questionAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Comment on question</h1>', $body);
    }


    public function testQuestionActionPost(): void
    {
        $comment = new Comment($this->di->dbqb);
        $comments = count($comment->findAll());

        $this->di->request->setPost("id", 1);
        $this->di->request->setPost("content", "New comment");
        $this->di->request->setPost("submit", "Post comment");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Comment\HTMLForm\QuestionForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $this->di->auth->setUser($this->user);
        $res = $this->controller->questionAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("question/1");
        $this->assertContains("Location: $url", $headers);

        // Check that the comment was created
        $this->assertCount($comments + 1, $comment->findAll());
    }


    public function testQuestionActionFail(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->controller->questionAction(1);
    }


    public function testQuestionActionFail2(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->di->auth->setUser($this->user);
        $this->controller->questionAction("notanid");
    }


    public function testAnswerActionGet(): void
    {
        $this->di->auth->setUser($this->user);
        $res = $this->controller->answerAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Comment on answer</h1>', $body);
    }


    public function testAnswerActionPost(): void
    {
        $comment = new Comment($this->di->dbqb);
        $comments = count($comment->findAll());

        $this->di->request->setPost("id", 3);
        $this->di->request->setPost("content", "New comment");
        $this->di->request->setPost("submit", "Post comment");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Comment\HTMLForm\AnswerForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $this->di->auth->setUser($this->user);
        $res = $this->controller->answerAction(3);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("question/2");
        $this->assertContains("Location: $url", $headers);

        // Check that the comment was created
        $this->assertCount($comments + 1, $comment->findAll());
    }


    public function testAnswerActionFail(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->controller->answerAction(1);
    }


    public function testAnswerActionFail2(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->di->auth->setUser($this->user);
        $this->controller->answerAction("notanid");
    }


    public function testUpdateActionGet(): void
    {
        $this->di->auth->login("miim", "miim");
        $res = $this->controller->updateAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Update comment</h1>', $body);
    }


    public function testUpdateActionPost(): void
    {
        $this->di->request->setPost("id", 1);
        $this->di->request->setPost("content", "Updated content");
        $this->di->request->setPost("submit", "Delete comment");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Comment\HTMLForm\UpdateForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $this->di->auth->login("miim", "miim");
        $res = $this->controller->updateAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("question/3");
        $this->assertContains("Location: $url", $headers);

        // Check that the answer updated
        $comment = new Comment($this->di->dbqb);
        $comment = $comment->findById(1);
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals($comment->content, "Updated content");
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
        $this->di->auth->login("miim", "miim");
        $res = $this->controller->deleteAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Delete comment</h1>', $body);
    }


    public function testDeleteActionPost(): void
    {
        $this->di->request->setPost("id", 1);
        $this->di->request->setPost("submit", "Delete comment");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Comment\HTMLForm\DeleteForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $this->di->auth->login("miim", "miim");
        $res = $this->controller->deleteAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("question/3");
        $this->assertContains("Location: $url", $headers);

        // Check that the comment was deleted
        $comment = new Comment($this->di->dbqb);
        $comment = $comment->findById(1);
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertNull($comment->id);
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

        // Test with user but invalid commentId
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

        // Test with user but invalid commentId
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
        // Test as author of the answer (should fail)
        $this->di->auth->login("miim", "miim");
        $res = $this->controller->upvoteActionPost(3);

        $this->assertIsArray($res);
        list($json, $status) = $res;

        $this->assertEquals($status, 400);
        $this->assertIsArray($json);
        $this->assertIsString($json["message"]);
    }
}

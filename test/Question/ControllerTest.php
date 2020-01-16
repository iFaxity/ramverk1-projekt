<?php

namespace Faxity\Question;

use Anax\DI\DI;
use Anax\Route\Exception\ForbiddenException;
use Anax\Route\Exception\NotFoundException;
use Faxity\Test\ControllerTestCase;
use Faxity\DI\DISorcery;
use Faxity\Models\Answer;
use Faxity\Models\Question;
use Faxity\Models\User;

/**
 * Test Question Controller.
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
        $this->user->alias = "question";
        $this->user->email = "question@example.com";
        $this->user->setPassword("question");
        $this->user->save();
    }

    public function tearDown(): void
    {
        $this->di->auth->logout();
        parent::tearDown();
    }


    public function testCatchAll(): void
    {
        $res = $this->controller->catchAll("1");
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1 class="title">How do i write a loop in javascript?</h1>', $body);
        $this->assertContains('<div class="question ">', $body);
        $this->assertContains('<h4>Accepted answer</h4>', $body);
    }


    public function testCatchAllFail(): void
    {
        $this->expectException(NotFoundException::class);
        $this->controller->catchAll("notanID");
    }


    public function testCatchAllFail2(): void
    {
        $this->expectException(NotFoundException::class);
        $this->controller->catchAll("99999");
    }


    public function testIndexActionGet(): void
    {
        $res = $this->controller->indexActionGet();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Questions</h1>', $body);
    }


    public function testCreateActionGet(): void
    {
        $this->di->auth->setUser($this->user);
        $res = $this->controller->createAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Create question</h1>', $body);
    }


    public function testCreateActionPost(): void
    {
        $this->di->request->setPost("title", "New question");
        $this->di->request->setPost("content", "This is just a test");
        $this->di->request->setPost("tags", "webdev testing test");
        $this->di->request->setPost("submit", "Create question");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Question\HTMLForm\CreateForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $this->di->auth->setUser($this->user);
        $res = $this->controller->createAction();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("question/7");
        $this->assertContains("Location: $url", $headers);

        // Check that the question was created
        $question = new Question($this->di->dbqb);
        $question->findById(7);
        $this->assertIsInt($question->id);
        $this->assertEquals($question->title, "New question");
        $this->assertEquals($question->content, "This is just a test");
        $this->assertEquals($question->userId, $this->user->id);
    }


    public function testCreateActionFail(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->controller->createAction();
    }


    public function testUpdateActionGet(): void
    {
        $this->di->auth->login("ratz", "ratz");
        $res = $this->controller->updateAction(6);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Update question</h1>', $body);
    }


    public function testUpdateActionPost(): void
    {
        $this->di->request->setPost("id", 5);
        $this->di->request->setPost("title", "Updated question");
        $this->di->request->setPost("content", "This is the new content");
        $this->di->request->setPost("tags", "test update webdev");
        $this->di->request->setPost("submit", "Update question");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Question\HTMLForm\UpdateForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $this->di->auth->login("super", "super");
        $res = $this->controller->updateAction(5);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("question/5");
        $this->assertContains("Location: $url", $headers);

        // Check that the question was updated
        $comment = new Question($this->di->dbqb);
        $comment = $comment->findById(5);
        $this->assertEquals($comment->title, "Updated question");
        $this->assertEquals($comment->content, "This is the new content");
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
        $this->controller->updateAction("fakeid");
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
        $this->assertContains('<h1>Delete question</h1>', $body);
    }


    public function testDeleteActionPost(): void
    {
        $this->di->request->setPost("id", 1);
        $this->di->request->setPost("submit", "Delete question");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Question\HTMLForm\DeleteForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $this->di->auth->login("miim", "miim");
        $res = $this->controller->deleteAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("question");
        $this->assertContains("Location: $url", $headers);

        // Check that the comment was deleted
        $comment = new Question($this->di->dbqb);
        $comment = $comment->findById(1);
        $this->assertInstanceOf(Question::class, $comment);
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


    public function testAnswerActionGet(): void
    {
        $this->di->auth->setUser($this->user);
        $res = $this->controller->answerAction(1);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if the template gets rendered
        $body = $res->getBody();
        $this->assertContains('<h1>Answer question</h1>', $body);
    }


    public function testAnswerActionPost(): void
    {
        $this->di->request->setPost("id", 3);
        $this->di->request->setPost("content", "New answer");
        $this->di->request->setPost("submit", "Post answer");
        $this->di->request->setPost("anax/htmlform-id", "Faxity\Question\HTMLForm\AnswerForm");
        $this->di->request->setServer("REQUEST_METHOD", "POST");

        $this->di->auth->setUser($this->user);
        $res = $this->controller->answerAction(3);
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        // Check if redirect header is set
        $nextId = 12;
        $headers = $this->di->response->getHeaders();
        $url = $this->di->url->create("question/3#answer$nextId");
        $this->assertContains("Location: $url", $headers);

        // Check that the answer was created
        $answer = new Answer($this->di->dbqb);
        $answer->findById($nextId);
        $this->assertIsInt($answer->id);
        $this->assertEquals($answer->questionId, 3);
        $this->assertEquals($answer->content, "New answer");
        $this->assertEquals($answer->userId, $this->user->id);
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


    public function testAnswerActionFail3(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->di->auth->login("ratz", "ratz");
        $this->controller->answerAction(3);
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
        $this->di->auth->login("ratz", "ratz");
        $res = $this->controller->upvoteActionPost(3);

        $this->assertIsArray($res);
        list($json, $status) = $res;

        $this->assertEquals($status, 400);
        $this->assertIsArray($json);
        $this->assertIsString($json["message"]);
    }
}

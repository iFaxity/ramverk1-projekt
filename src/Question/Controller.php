<?php

namespace Faxity\Question;

use Anax\Commons\ContainerInjectableInterface;
use Anax\Commons\ContainerInjectableTrait;
use Anax\Route\Exception\NotFoundException;
use Anax\Route\Exception\ForbiddenException;
use Faxity\Models\Question;
use Faxity\Models\User;
use Faxity\Question\HTMLForm\CreateForm;
use Faxity\Question\HTMLForm\UpdateForm;
use Faxity\Question\HTMLForm\DeleteForm;
use Faxity\Question\HTMLForm\AnswerForm;

/**
 * A controller for flat file markdown content.
 */
class Controller implements ContainerInjectableInterface
{
    use ContainerInjectableTrait;


    public function catchAll(...$args)
    {
        if (count($args) != 1 || !ctype_digit($args[0])) {
            throw new NotFoundException("Question doesn't exist");
        }

        $id = $args[0]; // question id
        $question = new Question($this->di->dbqb);
        $question->findById($id);
        $sort = $this->di->request->getGet("sort", "");

        // Question doesn't exist
        if (is_null($question->id)) {
            throw new NotFoundException("Question doesn't exist");
        }

        $answers = $question->answers($sort);

        // Hoist the selected answer to the top
        if (!is_null($question->answerId)) {
            $answer = null;
            foreach ($answers as $key => $a) {
                if ($a->id == $question->answerId) {
                    array_splice($answers, $key, 1);
                    $answer = $a;
                    break;
                }
            }

            if ($answer) {
                array_unshift($answers, $answer);
            }
        }

        $this->di->page->add("site/question/question", [
            "user"     => $this->di->auth->user ?? null,
            "question" => $question,
            "answers"  => $answers,
            "sort"     => $sort,
        ]);

        return $this->di->page->render([
            "title" => $question->title,
        ]);
    }


    public function indexActionGet()
    {
        $question = new Question($this->di->dbqb);
        $questions = $question->findAllTop("created DESC");

        // Show top questions
        $this->di->page->add("site/question/list", [
            "questions" => $questions,
        ]);

        return $this->di->page->render([
            "title" => "Questions",
        ]);
    }


    public function createAction()
    {
        // Route guard for already logged in
        if (!$this->di->auth->loggedIn()) {
            throw new ForbiddenException("This action requires login");
        }

        $form = new CreateForm($this->di);
        $form->check();

        $this->di->page->add("site/question/create", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Create question",
        ]);
    }


    public function updateAction($id)
    {
        // Route guard for already logged in
        if (!$this->di->auth->loggedIn()) {
            throw new ForbiddenException("This action requires login");
        }

        $question = new Question($this->di->dbqb);
        $question->findById($id);

        if (is_null($question->id)) {
            throw new ForbiddenException("Question doesn't exist");
        } else if ($this->di->auth->user->id != $question->userId) {
            throw new ForbiddenException("Only the author of this question can view this page");
        }

        $form = new UpdateForm($this->di, $question);
        $form->check();

        $this->di->page->add("site/question/update", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Update question",
        ]);
    }


    public function deleteAction($id)
    {
        // Route guard for already logged in
        if (!$this->di->auth->loggedIn()) {
            throw new ForbiddenException("This action requires login");
        }

        $question = new Question($this->di->dbqb);
        $question->findById($id);

        if (is_null($question->id)) {
            throw new ForbiddenException("Question doesn't exist");
        } else if ($this->di->auth->user->id != $question->userId) {
            throw new ForbiddenException("Only the author of this question can view this page");
        }

        $form = new DeleteForm($this->di, $question);
        $form->check();

        $this->di->page->add("site/question/delete", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Delete question",
        ]);
    }


    public function answerAction($id)
    {
        // Route guard for already logged in
        if (!$this->di->auth->loggedIn()) {
            throw new ForbiddenException("This action requires login");
        }

        $question = new Question($this->di->dbqb);
        $question->findById($id);

        if (is_null($question->id)) {
            throw new ForbiddenException("Question doesn't exist");
        } else if ($this->di->auth->user->id == $question->userId) {
            throw new ForbiddenException("The author of this question can't view this page");
        }

        $form = new AnswerForm($this->di, $question);
        $form->check();

        $this->di->page->add("site/question/answer", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Answer question",
        ]);
    }


    public function upvoteActionPost($id)
    {
        $json = [];

        try {
            if (!$this->di->auth->loggedIn()) {
                throw new \Exception("Not logged in, user can't vote.");
            }

            $this->di->auth->user->voteQuestion($id, User::UPVOTE);

            // Get the new amount of votes
            $question = new Question($this->di->dbqb);
            $question->id = $id;

            $status = 200;
            $json["message"] = "Upvoting question successfull.";
            $json["votes"] = $question->votes();
        } catch (\Exception $ex) {
            $status = 400;
            $json["message"] = $ex->getMessage();
        }


        $json["status"] = $status;
        return [ $json, $status ];
    }


    public function downvoteActionPost($id)
    {
        $json = [];

        try {
            if (!$this->di->auth->loggedIn()) {
                throw new \Exception("Not logged in, user can't vote.");
            }

            $this->di->auth->user->voteQuestion($id, User::DOWNVOTE);

            // Get the new amount of votes
            $question = new Question($this->di->dbqb);
            $question->id = $id;

            $status = 200;
            $json["message"] = "Downvoting question successfull.";
            $json["votes"] = $question->votes();
        } catch (\Exception $ex) {
            $status = 400;
            $json["message"] = $ex->getMessage();
        }


        $json["status"] = $status;
        return [ $json, $status ];
    }
}

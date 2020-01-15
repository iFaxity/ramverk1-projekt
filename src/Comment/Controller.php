<?php

namespace Faxity\Comment;

use Anax\Commons\ContainerInjectableInterface;
use Anax\Commons\ContainerInjectableTrait;
use Anax\Route\Exception\ForbiddenException;
use Faxity\Models\User;
use Faxity\Comment\HTMLForm\QuestionForm;
use Faxity\Comment\HTMLForm\AnswerForm;
use Faxity\Comment\HTMLForm\UpdateForm;
use Faxity\Comment\HTMLForm\DeleteForm;
use Faxity\Models\Answer;
use Faxity\Models\Comment;
use Faxity\Models\Question;

/**
 * A controller for flat file markdown content.
 */
class Controller implements ContainerInjectableInterface
{
    use ContainerInjectableTrait;


    public function questionAction($id)
    {
        // Route guard for already logged in
        if (!$this->di->auth->loggedIn()) {
            throw new ForbiddenException("This action requires login");
        }

        $question = new Question($this->di->dbqb);
        $question->findById($id);

        if (is_null($question->id)) {
            throw new ForbiddenException("Question doesn't exist");
            return false;
        }

        $form = new QuestionForm($this->di, $id);
        $form->check();

        $this->di->page->add("site/comment/question", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Post comment",
        ]);
    }


    public function answerAction($id)
    {
        // Route guard for already logged in
        if (!$this->di->auth->loggedIn()) {
            throw new ForbiddenException("This action requires login");
        }

        $answer = new Answer($this->di->dbqb);
        $answer->findById($id);

        if (is_null($answer->id)) {
            throw new ForbiddenException("Answer doesn't exist");
        }

        $form = new AnswerForm($this->di, $answer);
        $form->check();

        $this->di->page->add("site/comment/answer", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Post comment",
        ]);
    }


    public function updateAction($id)
    {
        // Route guard for already logged in
        if (!$this->di->auth->loggedIn()) {
            throw new ForbiddenException("This action requires login");
        }

        $comment = new Comment($this->di->dbqb);
        $comment->findById($id);

        if (is_null($comment->id)) {
            throw new ForbiddenException("Comment doesn't exist");
        } else if ($this->di->auth->user->id != $comment->userId) {
            throw new ForbiddenException("Only the author of this comment can view this page");
        }

        $form = new UpdateForm($this->di, $comment);
        $form->check();

        $this->di->page->add("site/comment/update", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Update comment",
        ]);
    }


    public function deleteAction($id)
    {
        // Route guard for already logged in
        if (!$this->di->auth->loggedIn()) {
            throw new ForbiddenException("This action requires login");
        }

        $comment = new Comment($this->di->dbqb);
        $comment->findById($id);

        if (is_null($comment->id)) {
            throw new ForbiddenException("Comment doesn't exist");
        } else if ($this->di->auth->user->id != $comment->userId) {
            throw new ForbiddenException("Only the author of this comment can view this page");
        }

        $form = new DeleteForm($this->di, $comment);
        $form->check();

        $this->di->page->add("site/comment/delete", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Delete comment",
        ]);
    }


    public function upvoteActionPost($id)
    {
        $json = [];

        try {
            if (!$this->di->auth->loggedIn()) {
                throw new \Exception("Not logged in, user can't vote.");
            }

            $this->di->auth->user->voteComment($id, User::UPVOTE);

            // Get the new amount of votes
            $comment = new Comment($this->di->dbqb);
            $comment->id = $id;

            $status = 200;
            $json["message"] = "Upvoting comment successfull.";
            $json["votes"] = $comment->votes();
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

            $this->di->auth->user->voteComment($id, User::DOWNVOTE);

            // Get the new amount of votes
            $comment = new Comment($this->di->dbqb);
            $comment->id = $id;

            $status = 200;
            $json["message"] = "Downvoting comment successfull.";
            $json["votes"] = $comment->votes();
        } catch (\Exception $ex) {
            $status = 400;
            $json["message"] = $ex->getMessage();
        }


        $json["status"] = $status;
        return [ $json, $status ];
    }
}

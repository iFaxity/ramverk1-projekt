<?php

namespace Faxity\Answer;

use Anax\Commons\ContainerInjectableInterface;
use Anax\Commons\ContainerInjectableTrait;
use Anax\Route\Exception\ForbiddenException;
use Faxity\Models\User;
use Faxity\Answer\HTMLForm\UpdateForm;
use Faxity\Answer\HTMLForm\DeleteForm;
use Faxity\Models\Answer;

/**
 * A controller to create, modify and delete answers
 */
class Controller implements ContainerInjectableInterface
{
    use ContainerInjectableTrait;

    /**
     * Route to handle update form for answer
     * @param mixed $id Id of answer to update
     *
     * @return object
    */
    public function updateAction($id)
    {
        // Route guard for already logged in
        if (!$this->di->auth->loggedIn()) {
            throw new ForbiddenException("This action requires login");
        }

        $answer = new Answer($this->di->dbqb);
        $answer->findById($id);

        if (is_null($answer->id)) {
            throw new ForbiddenException("Answer doesn't exist");
        } else if ($this->di->auth->user->id != $answer->userId) {
            throw new ForbiddenException("Only the author of this answer can view this page");
        }

        $form = new UpdateForm($this->di, $answer);
        $form->check();

        $this->di->page->add("site/answer/update", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Update answer",
        ]);
    }



    /**
     * Route to handle delete form for answer
     * @param mixed $id Id of answer to delete
     *
     * @return object
    */
    public function deleteAction($id): object
    {
        // Route guard for already logged in
        if (!$this->di->auth->loggedIn()) {
            throw new ForbiddenException("This action requires login");
        }

        $answer = new Answer($this->di->dbqb);
        $answer->findById($id);

        if (is_null($answer->id)) {
            throw new ForbiddenException("Answer doesn't exist");
        } else if ($this->di->auth->user->id != $answer->userId) {
            throw new ForbiddenException("Only the author of this answer can view this page");
        }

        $form = new DeleteForm($this->di, $answer);
        $form->check();

        $this->di->page->add("site/answer/delete", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Delete answer",
        ]);
    }



    /**
     * Route to upvote answer
     * @param mixed $id Id of answer to upvote
     *
     * @return array
    */
    public function upvoteActionPost($id): array
    {
        $json = [];

        try {
            if (!$this->di->auth->loggedIn()) {
                throw new \Exception("Not logged in, user can't vote.");
            }

            $this->di->auth->user->voteAnswer($id, User::UPVOTE);

            // Get the new amount of votes
            $answer = new Answer($this->di->dbqb);
            $answer->id = $id;

            $status = 200;
            $json["message"] = "Upvoting answer successfull.";
            $json["votes"] = $answer->votes();
        } catch (\Exception $ex) {
            $status = 400;
            $json["message"] = $ex->getMessage();
        }


        $json["status"] = $status;
        return [ $json, $status ];
    }



    /**
     * Route to downvote answer
     * @param mixed $id Id of answer to downvote
     *
     * @return array
    */
    public function downvoteActionPost($id): array
    {
        $json = [];

        try {
            if (!$this->di->auth->loggedIn()) {
                throw new \Exception("Not logged in, user can't vote.");
            }

            $this->di->auth->user->voteAnswer($id, User::DOWNVOTE);

            // Get the new amount of votes
            $answer = new Answer($this->di->dbqb);
            $answer->id = $id;

            $status = 200;
            $json["message"] = "Downvoting answer successfull.";
            $json["votes"] = $answer->votes();
        } catch (\Exception $ex) {
            $status = 400;
            $json["message"] = $ex->getMessage();
        }


        $json["status"] = $status;
        return [ $json, $status ];
    }
}

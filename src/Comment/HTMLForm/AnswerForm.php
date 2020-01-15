<?php

namespace Faxity\Comment\HTMLForm;

use Anax\HTMLForm\FormModel;
use Faxity\Models\Answer;
use Psr\Container\ContainerInterface;
use Faxity\Models\Comment;

/**
 * Form to create a comment for an answer.
 */
class AnswerForm extends FormModel
{
    /** @var Answer $answer */
    private $answer;

    /**
     * Constructor injects with DI container.
     *
     * @param Psr\Container\ContainerInterface $di a service container
     * @param Answer $answer
     */
    public function __construct(ContainerInterface $di, $answer)
    {
        parent::__construct($di);
        $this->answer = $answer;
        $this->form->create(
            [
                "id" => __CLASS__,
                "use_fieldset" => false,
            ],
            [
                "id" => [
                    "type"       => "hidden",
                    "value"      => $answer->id,
                ],
                "content" => [
                    "type"       => "textarea",
                    "validation" => ["not_empty"],
                ],
                "submit" => [
                    "type"       => "submit",
                    "value"      => "Post comment",
                    "class"      => "solid",
                    "callback"   => [$this, "callbackSubmit"]
                ],
            ]
        );
    }



    /**
     * Callback for submit-button which should return true if it could
     * carry out its work and false if something failed.
     *
     * @return bool true if ok, false if something went wrong.
     */
    public function callbackSubmit() : bool
    {
        $comment = new Comment($this->di->dbqb);
        $comment->questionId = $this->answer->questionId;
        $comment->answerId = $this->answer->id;
        $comment->content = trim(htmlspecialchars_decode($this->form->value("content")));
        $comment->userId = $this->di->auth->user->id;
        $comment->save();
        return true;
    }



    /**
     * Callback what to do if the form was successfully submitted, this
     * happen when the submit callback method returns true. This method
     * can/should be implemented by the subclass for a different behaviour.
     */
    public function callbackSuccess()
    {
        $id = $this->answer->questionId;
        $this->di->flash->ok("Comment successfully created");
        $this->di->response->redirect("question/$id")->send();
    }
}

<?php

namespace Faxity\Comment\HTMLForm;

use Anax\HTMLForm\FormModel;
use Psr\Container\ContainerInterface;
use Faxity\Models\Comment;

/**
 * Form to create a comment for a question.
 */
class QuestionForm extends FormModel
{
    /** @var Comment $comment */
    private $comment;

    /**
     * Constructor injects with DI container.
     *
     * @param Psr\Container\ContainerInterface $di a service container
     * @param int $id
     */
    public function __construct(ContainerInterface $di, $id)
    {
        parent::__construct($di);
        $this->form->create(
            [
                "id" => __CLASS__,
                "use_fieldset" => false,
            ],
            [
                "id" => [
                    "type"  => "hidden",
                    "value" => $id,
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
        $this->comment = new Comment($this->di->dbqb);
        $this->comment->questionId = $this->form->value("id");
        $this->comment->content = trim(htmlspecialchars_decode($this->form->value("content")));
        $this->comment->userId = $this->di->auth->user->id;
        $this->comment->save();
        return true;
    }



    /**
     * Callback what to do if the form was successfully submitted, this
     * happen when the submit callback method returns true. This method
     * can/should be implemented by the subclass for a different behaviour.
     */
    public function callbackSuccess()
    {
        $questionId = $this->comment->questionId;
        $commentId = $this->comment->id;

        $this->di->flash->ok("Comment successfully created");
        $this->di->response->redirect("question/$questionId#comment$commentId")->send();
    }
}

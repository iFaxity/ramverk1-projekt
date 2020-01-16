<?php

namespace Faxity\Comment\HTMLForm;

use Anax\HTMLForm\FormModel;
use Faxity\Models\Answer;
use Psr\Container\ContainerInterface;
use Faxity\Models\Comment;

/**
 * Form to update a comment.
 */
class UpdateForm extends FormModel
{
    /** @var Comment $comment */
    private $comment;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Psr\Container\ContainerInterface $di a service container
     * @param Comment $comment
     */
    public function __construct(ContainerInterface $di, Comment $comment)
    {
        parent::__construct($di);
        $this->comment = $comment;
        $this->form->create(
            [
                "id" => __CLASS__,
                "use_fieldset" => false,
            ],
            [
                "id" => [
                    "type"  => "hidden",
                    "value" => $comment->id,
                ],
                "content" => [
                    "type"       => "textarea",
                    "validation" => ["not_empty"],
                    "value"      => $comment->content,
                ],
                "submit" => [
                    "type"       => "submit",
                    "value"      => "Create question",
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
    public function callbackSubmit(): bool
    {
        $this->comment->content = trim(htmlspecialchars_decode($this->form->value("content")));
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

        $this->di->flash->ok("Comment successfully updated");
        $this->di->response->redirect("question/$questionId#comment$commentId")->send();
    }
}

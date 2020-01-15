<?php

namespace Faxity\Question\HTMLForm;

use Anax\HTMLForm\FormModel;
use Psr\Container\ContainerInterface;
use Faxity\Models\Question;

/**
 * Form to delete a question.
 */
class DeleteForm extends FormModel
{
    /** @var Question $question */
    private $question;

    /**
     * Constructor injects with DI container.
     *
     * @param Psr\Container\ContainerInterface $di a service container
     * @param Question $question
     */
    public function __construct(ContainerInterface $di, Question $question)
    {
        parent::__construct($di);
        $this->question = $question;
        $this->form->create(
            [
                "id" => __CLASS__,
                "use_fieldset" => false,
            ],
            [
                "id" => [
                    "type"  => "hidden",
                    "value" => $question->id,
                ],
                "content" => [
                    "type"       => "textarea",
                    "validation" => ["not_empty"],
                    "value"      => $question->content,
                    "readonly"   => true,
                ],
                "submit" => [
                    "type"     => "submit",
                    "value"    => "Delete question",
                    "class"    => "solid",
                    "callback" => [$this, "callbackSubmit"]
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
        $this->question->delete();
        return true;
    }



    /**
     * Callback what to do if the form was successfully submitted, this
     * happen when the submit callback method returns true. This method
     * can/should be implemented by the subclass for a different behaviour.
     */
    public function callbackSuccess()
    {
        $this->di->flash->ok("Question successfully removed");
        $this->di->response->redirect("question")->send();
    }
}

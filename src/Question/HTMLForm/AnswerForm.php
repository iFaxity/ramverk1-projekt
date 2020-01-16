<?php

namespace Faxity\Question\HTMLForm;

use Anax\HTMLForm\FormModel;
use Psr\Container\ContainerInterface;
use Faxity\Models\Answer;
use Faxity\Models\Question;

/**
 * Form to answer a question.
 */
class AnswerForm extends FormModel
{
    /** @var Question $question */
    private $question;
    /** @var Answer $answer */
    private $answer;

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
                ],
                "submit" => [
                    "type"       => "submit",
                    "value"      => "Post answer",
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
        $user = $this->di->auth->user;
        $this->answer = new Answer($this->di->dbqb);

        $this->answer->userId = $user->id;
        $this->answer->questionId = $this->question->id;
        $this->answer->content = trim(htmlspecialchars_decode($this->form->value("content")));
        $this->answer->save();

        return true;
    }



    /**
     * Callback what to do if the form was successfully submitted, this
     * happen when the submit callback method returns true. This method
     * can/should be implemented by the subclass for a different behaviour.
     */
    public function callbackSuccess()
    {
        $questionId = $this->answer->questionId;
        $answerId = $this->answer->id;

        $this->di->flash->ok("Answer successfully created");
        $this->di->response->redirect("question/$questionId#answer$answerId")->send();
    }
}

<?php

namespace Faxity\Question\HTMLForm;

use Anax\HTMLForm\FormModel;
use Psr\Container\ContainerInterface;
use Faxity\Models\Question;
use Faxity\Models\Tag;
use Faxity\Models\QuestionToTags;

/**
 * Form to update an item.
 */
class CreateForm extends FormModel
{
    /** @var Question $question */
    private $question;

    /**
     * Constructor injects with DI container.
     *
     * @param Psr\Container\ContainerInterface $di a service container
     */
    public function __construct(ContainerInterface $di)
    {
        parent::__construct($di);
        $this->form->create(
            [
                "id" => __CLASS__,
                "use_fieldset" => false,
            ],
            [
                "title" => [
                    "type"       => "text",
                    "validation" => ["not_empty"],
                ],
                "content" => [
                    "type"       => "textarea",
                    "validation" => ["not_empty"],
                ],
                "tags" => [
                    "type"       => "text",
                    "label"      => "Tags (separated by spaces)",
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


    private function createTags(int $questionId): void
    {
        // Add the tags to the question
        $tags = explode(" ", $this->form->value("tags"));
        $tags = array_map(function ($tag) {
            return strtolower(trim($tag));
        }, $tags);
        $tags = array_filter(array_unique($tags));

        foreach ($tags as $str) {
            $str = strtolower(trim($str));

            $tag = new Tag($this->di->dbqb);
            $tag->find("tag", $str);

            if (!$tag->id) {
                $tag->tag = $str;
                $tag->save();
            }

            $qt = new QuestionToTags($this->di->dbqb);
            $qt->questionId = $questionId;
            $qt->tagId = $tag->id;
            $qt->save();
        }
    }



    /**
     * Callback for submit-button which should return true if it could
     * carry out its work and false if something failed.
     *
     * @return bool true if ok, false if something went wrong.
     */
    public function callbackSubmit() : bool
    {
        $this->question = new Question($this->di->dbqb);
        $this->question->find("id", $this->form->value("id"));
        $this->question->title = trim($this->form->value("title"));
        $this->question->content = trim(htmlspecialchars_decode($this->form->value("content")));
        $this->question->userId = $this->di->auth->user->id;
        $this->question->save();

        $this->createTags($this->question->id);
        return true;
    }



    /**
     * Callback what to do if the form was successfully submitted, this
     * happen when the submit callback method returns true. This method
     * can/should be implemented by the subclass for a different behaviour.
     */
    public function callbackSuccess()
    {
        $id = $this->question->id;
        $this->di->flash->ok("Question successfully created");
        $this->di->response->redirect("question/$id")->send();
    }
}

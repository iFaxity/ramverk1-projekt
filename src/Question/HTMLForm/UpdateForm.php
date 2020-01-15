<?php

namespace Faxity\Question\HTMLForm;

use Anax\HTMLForm\FormModel;
use Faxity\Models\Answer;
use Psr\Container\ContainerInterface;
use Faxity\Models\Question;
use Faxity\Models\Tag;
use Faxity\Models\QuestionToTags;
use function Anax\View\previewMarkdown;

/**
 * Form to update a question.
 */
class UpdateForm extends FormModel
{
    /** @var Question $question */
    private $question;

    /**
     * Constructor injects with DI container and the id to update.
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
                "title" => [
                    "type"       => "text",
                    "validation" => ["not_empty"],
                    "value"      => $question->title,
                ],
                "content" => [
                    "type"       => "textarea",
                    "validation" => ["not_empty"],
                    "value"      => $question->content,
                ],
                "tags" => [
                    "type"       => "text",
                    "label"      => "Tags (separated by spaces)",
                    "value"      => implode(" ", $question->tags()),
                ],
                "answer" => [
                    "type"       => "select",
                    "label"      => "Accepted answer",
                    "options"    => $this->getAnswers($question->id),
                ],
                "submit" => [
                    "type"       => "submit",
                    "value"      => "Update question",
                    "class"      => "solid",
                    "callback"   => [$this, "callbackSubmit"]
                ],
            ]
        );
    }



    /**
     * Get answers to this question
     * @param int $id Id of question
     *
     * @return array
     */
    public function getAnswers(int $id): array
    {
        $answer = new Answer($this->di->dbqb);
        $answers = $answer->findAllWhere("questionId = ?", $id);

        $books = ["-1" => "Select an answer..."];

        return array_reduce($answers, function ($acc, $answer) {
            $author = $answer->author();
            $text = previewMarkdown($answer->content, 40);

            $acc[$answer->id] = "$text - $author->alias";
            return $acc;
        }, $books);
    }

    /**
     * Update the tags to a specified question
     * @param int $questionId ID of question
     * @param array $newTags  New tags, array of strings
     * @param array $oldTags  Old tags, array of QuestionToTag objects
     * @return void
     */
    public function updateTags(int $questionId, array $newTags, array $oldTags): bool
    {
        // if tag doesnt exist, insert new Tag, also link QuestionToTags
        // if tag exists, link QuestionToTags if not exists
        foreach ($newTags as $tag) {
            $qt = new QuestionToTags($this->di->dbqb);
            $t = new Tag($this->di->dbqb);
            $t->find("tag", $tag);
            $hasTag = false;

            // Create tag if it doesnt exist
            if (!$t->id) {
                $t->tag = $tag;
                $t->save();
            } else {
                // Check if we need to link the tag
                foreach ($oldTags as $key => $ot) {
                    if ($ot->tagId == $t->id) {
                        $hasTag = true;
                        array_splice($oldTags, $key, 1);
                        break;
                    }
                }
            }

            // Link tag if needed
            if (!$hasTag) {
                $qt->questionId = $questionId;
                $qt->tagId = $t->id;
                $qt->save();
            }
        }

        // Remove all old tags that remains
        foreach ($oldTags as $tag) {
            $tag->delete();
        }

        return false;
    }


    /**
     * Callback for submit-button which should return true if it could
     * carry out its work and false if something failed.
     *
     * @return bool true if ok, false if something went wrong.
     */
    public function callbackSubmit(): bool
    {
        $this->question->title = trim($this->form->value("title"));
        $this->question->content = trim(htmlspecialchars_decode($this->form->value("content")));

        // Add accepted answer
        $answerId = $this->form->value("answer");
        if ($answerId != "-1") {
            $this->question->answerId = $answerId;
        }

        $this->question->save();

        // Diff the tags to update in database
        $newTags = explode(" ", $this->form->value("tags"));
        $newTags = array_map(function ($tag) {
            return strtolower(trim($tag));
        }, $newTags);
        $newTags = array_filter(array_unique($newTags));

        $qt = new QuestionToTags($this->di->dbqb);
        $oldTags = $qt->findAllWhere("questionId = ?", $this->question->id);

        $this->updateTags($this->question->id, $newTags, $oldTags);
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
        $this->di->flash->ok("Question successfully updated");
        $this->di->response->redirect("question/$id")->send();
    }
}

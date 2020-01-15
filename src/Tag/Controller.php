<?php

namespace Faxity\Tag;

use Anax\Commons\ContainerInjectableInterface;
use Anax\Commons\ContainerInjectableTrait;
use Anax\Route\Exception\NotFoundException;
use Faxity\Models\Tag;
use Faxity\Models\QuestionToTags;

/**
 * A controller to show tags and questions linked to specific tags
 */
class Controller implements ContainerInjectableInterface
{
    use ContainerInjectableTrait;

    /**
     * Renders view for showing questions linked to tag
     *
     * @return object
    */
    public function catchAll(...$args): object
    {
        if (count($args) != 1) {
            throw new NotFoundException("Tag doesn't exist");
        }

        $tag = strtolower($args[0]); // tag name
        $qt = new QuestionToTags($this->di->dbqb);
        $questions = $qt->findAllWithTag($tag);

        $this->di->page->add("site/tags/tag", [
            "tag"       => $tag,
            "questions" => $questions,
        ]);

        return $this->di->page->render([
            "title" => "Tag: $tag",
        ]);
    }



    /**
     * Renders view for showing all tags
     *
     * @return object
    */
    public function indexActionGet(): object
    {
        $tag = new Tag($this->di->dbqb);
        $tags = $tag->findAllByQuestionCount();

        $this->di->page->add("site/tags/index", [
            "tags" => $tags,
        ]);

        return $this->di->page->render([
            "title" => "Tags",
        ]);
    }
}

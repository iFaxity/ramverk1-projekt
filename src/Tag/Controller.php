<?php

namespace Faxity\Tag;

use Anax\Commons\ContainerInjectableInterface;
use Anax\Commons\ContainerInjectableTrait;
use Faxity\Models\Tag;
use Faxity\Models\QuestionToTags;

/**
 * A controller for /tags routes.
 */
class Controller implements ContainerInjectableInterface
{
    use ContainerInjectableTrait;


    public function catchAll(...$args)
    {
        if (count($args) != 1) {
            return false;
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


    public function indexActionGet()
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

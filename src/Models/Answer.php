<?php

namespace Faxity\Models;

/**
 * A database driven model using the Active Record design pattern.
 */
class Answer extends Post
{
    /** @var string $tableName name of the database table. */
    protected $tableName = "Answer";

    protected static $propTypes = [
        "id" => "int",
        "userId" => "int",
        "questionId" => "int",
    ];

    /** @var int $questionId */
    protected $questionId;


    /**
     * Gets the comments of this post
     */
    public function comments(): array
    {
        $this->checkDb();
        if (is_null($this->id)) {
            throw new \Exception("No id set");
        }

        $comment = new Comment($this->db);
        return $comment->findAllWhere("answerId = ?", $this->id);
    }
}

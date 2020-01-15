<?php

namespace Faxity\Models;

/**
 * A database driven model using the Active Record design pattern.
 */
class QuestionToTags extends DatabaseModel
{
    /** @var string $tableName name of the database table. */
    protected $tableName = "QuestionToTags";

    protected static $propTypes = [
        "id" => "int",
        "questionId" => "int",
        "tagId" => "int",
    ];

    /** @var int $id primary key auto incremented */
    protected $id;
    /** @var int $questionId */
    protected $questionId;
    /** @var int $tagId */
    protected $tagId;


    /**
     * Finds all questions with tag
     * @param string $tag Tag to match
     *
     * @return array
     */
    public function findAllWithTag(string $tag): array
    {
        $this->checkDb();
        $this->db->connect();

        $tag = strtolower($tag);
        $questions = $this->db->select("q.*")
            ->from("{$this->tableName} AS qt")
            ->join("Tag AS t", "t.id = qt.tagId")
            ->join("Question AS q", "q.id = qt.questionId")
            ->where("t.tag = ?")
            ->execute([ $tag ])
            ->fetchAllClass(Question::class);

        return $this->mapModels($questions);
    }
}

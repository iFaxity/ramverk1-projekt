<?php

namespace Faxity\Models;

use Faxity\Models\Answer;

/**
 * A database driven model using the Active Record design pattern.
 */
class Question extends Post
{
    /** @var string $tableName name of the database table. */
    protected $tableName = "Question";

    protected static $propTypes = [
        "id"       => "int",
        "userId"   => "int",
        "answerId" => "int",
    ];

    /** @var int $answerId */
    protected $answerId;


    /**
     * Gets tags for this question
     *
     * @return array
     */
    public function tags(): array
    {
        $this->checkDb();
        if (is_null($this->id)) {
            throw new \Exception("No id set");
        }

        // Get all answers
        $this->db->connect();
        $res = $this->db->select("GROUP_CONCAT(DISTINCT t.tag) AS tags")
            ->from("QuestionToTags AS qt")
            ->leftJoin("Tag AS t", "t.id = qt.tagId")
            ->where("qt.questionId = ?")
            ->execute([ $this->id ])
            ->fetch();

        return explode(",", $res->tags ?? "");
    }


    /**
     * Gets answers related to this question
     * @param string|null $sort
     *
     * @return array
     */
    public function answers($sort = null): array
    {
        $this->checkDb();
        if (is_null($this->id)) {
            throw new \Exception("No id set");
        }

        // Get all answers
        $this->db->connect();
        $query = $this->db->select("
            a.*,
            SUM(v.vote) AS votes,
            COALESCE(a.updated, a.created) AS timestamp
            ")
            ->from("Answer AS a")
            ->leftJoin("Vote AS v", "a.id = v.answerId")
            ->where("a.questionId = ?")
            ->groupBy("v.answerId");

        // Sort mode
        if ($sort == "date") {
            $query->orderBy("timestamp DESC, votes DESC");
        } else {
            $query->orderBy("votes DESC, timestamp DESC");
        }

        $answers = $query->execute([ $this->id ])->fetchAllClass(Answer::class);
        return $this->mapModels($answers);
    }


    /**
     * Gets answers related to this question
     *
     * @return array
     */
    public function answersCount(): int
    {
        $this->checkDb();

        // Get all answers
        $this->db->connect();
        $res = $this->db->select("COUNT(a.id) AS answers")
            ->from("Question AS q")
            ->join("Answer AS a", "a.questionId = q.id")
            ->where("q.id = ?")
            ->execute([ $this->id ])
            ->fetch();

        return (int) $res->answers ?? 0;
    }


    /**
     * Gets the comments of this post
     *
     * @return array
     */
    public function comments(): array
    {
        $this->checkDb();
        if (is_null($this->id)) {
            throw new \Exception("No id set");
        }

        $comment = new Comment($this->db);
        return $comment->findAllWhere("questionId = ? AND answerId IS NULL", $this->id);
    }
}

<?php

namespace Faxity\Models;

/**
 * A database driven model using the Active Record design pattern.
 */
class Tag extends DatabaseModel
{
    /** @var string $tableName name of the database table. */
    protected $tableName = "Tag";

    protected static $propTypes = [
        "id" => "int",
    ];

    /** @var int $id primary key auto incremented */
    protected $id;
    /** @var string $tag */
    protected $tag;


    // Get tags with most questions linked
    public function findAllByQuestionCount(?int $limit = null)
    {
        $this->checkDb();
        $this->db->connect();

        $query = $this->db->select("t.*, COUNT(qt.tagId) AS count")
            ->from("{$this->tableName} AS t")
            ->leftJoin("QuestionToTags AS qt", "t.id = qt.tagId")
            ->groupBy("t.id")
            ->orderBy("count DESC");

        if (is_int($limit)) {
            $query->limit($limit);
        }

        return $query->execute()->fetchAll();
    }
}

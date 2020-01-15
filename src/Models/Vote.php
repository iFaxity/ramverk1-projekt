<?php

namespace Faxity\Models;

/**
 * A database driven model using the Active Record design pattern.
 */
class Vote extends DatabaseModel
{
    /** @var string $tableName name of the database table. */
    protected $tableName = "Vote";

    protected static $propTypes = [
        "id" => "int",
        "questionId" => "int",
        "answerId" => "int",
        "commentId" => "int",
        "vote" => "int",
    ];

    /** @var int $id primary key auto incremented */
    protected $id;
    /** @var int $questionId */
    protected $questionId;
    /** @var int $answerId */
    protected $answerId;
    /** @var int $commentId */
    protected $commentId;
    /** @var int $vote */
    protected $vote;
}

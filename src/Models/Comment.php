<?php

namespace Faxity\Models;

/**
 * A database driven model using the Active Record design pattern.
 */
class Comment extends Post
{
    /** @var string $tableName name of the database table. */
    protected $tableName = "Comment";

    protected static $propTypes = [
        "id" => "int",
        "userId" => "int",
        "questionId" => "int",
        "answerId" => "int",
    ];

    /** @var int $questionId */
    protected $questionId;
    /** @var int $answerId */
    protected $answerId;
}

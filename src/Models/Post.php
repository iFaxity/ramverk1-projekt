<?php

namespace Faxity\Models;

use DateTime;

/**
 * A database driven model using the Active Record design pattern.
 */
class Post extends DatabaseModel
{
    /** @var string DATE_FORMAT format to use when getting dates. */
    const DATE_FORMAT = "M j 'y H:i";

    /** @var int $id primary key auto incremented */
    protected $id;
    /** @var int $userId */
    protected $userId;
    /** @var string $content */
    protected $content;
    /** @var string $created */
    protected $created;
    /** @var string $updated */
    protected $updated;


    /**
     * Gets when the item was created
     *
     * @return string
     */
    public function createdTimestamp(): string
    {
        $dt = DateTime::createFromFormat("Y-m-d H:i:s", $this->created);
        return $dt->format($this::DATE_FORMAT);
    }


    /**
     * Gets when the item was last updated
     *
     * @return string
     */
    public function updatedTimestamp(): string
    {
        $dt = DateTime::createFromFormat("Y-m-d H:i:s", $this->updated);
        return $dt->format($this::DATE_FORMAT);
    }


    /**
     * Gets current voting reputation of this post
     *
     * @return int
     */
    public function votes(): int
    {
        $this->checkDb();
        if (is_null($this->id)) {
            throw new \Exception("No id set");
        }

        $postName = strtolower($this->tableName);

        // Get all answers
        $this->db->connect();
        $res = $this->db->select("SUM(vote) AS votes")
            ->from("Vote")
            ->where("{$postName}Id = ?")
            ->execute([ $this->id ])
            ->fetch();

        return $res->votes ?? 0;
    }


    /**
     * Gets the author of this post
     *
     * @return User
     */
    public function author(): User
    {
        $user = new User($this->db);
        $user->findById($this->userId);
        return $user;
    }
}

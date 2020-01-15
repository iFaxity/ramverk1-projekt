<?php

namespace Faxity\Models;

/**
 * A database driven model using the Active Record design pattern.
 */
class User extends DatabaseModel
{
    protected $tableName = "User";
    protected static $propTypes = [
        "id" => "int",
        "rep" => "int",
    ];

    const UPVOTE = 1;
    const DOWNVOTE = -1;

    /** @var int $id primary key auto incremented */
    protected $id;
    /** @var string $alias */
    protected $alias;
    /** @var string $email */
    protected $email;
    /** @var string $password */
    protected $password;
    /** @var int $rep */
    protected $rep;
    /** @var string $created */
    protected $created;
    /** @var string $updated */
    protected $updated;


    /**
     * Internal function for sharing code between voting functions
     * @param string $type Type (question, answer or comment)
     * @param int    $id   Id of post
     * @param int    $userVote 1 for upvote -1 for downvote
     *
     * @return void
     */
    private function vote(string $type, int $id, int $userVote): void
    {
        $prop = "{$type}Id";
        $vote = new Vote($this->db);
        $vote->findWhere("userId = ? AND $prop = ?", [ $this->id, $id ]);

        if (is_null($vote->id)) {
            $vote->$prop = $id;
            $vote->userId = $this->id;
        }

        if ($vote->vote !== $userVote) {
            $vote->vote = $userVote;
            $vote->save();
        }
    }


    /**
     * Hashes the password and sets it to the user object
     * @param string $password
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }


    /**
     * Checks if the password matches the users password field
     * @param string $password
     *
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }


    /**
     * Gets gravatar image from the users email
     * @param string $size    Size in pixels, defaults to 80px [ 1 - 2048 ]
     * @param string $default Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
     * @param string $rating  Maximum rating (inclusive) [ g | pg | r | x ]
     * @source https://gravatar.com/site/implement/images/php/
     *
     * @return string containing either just a URL or a complete image tag
     */
    public function gravatar(int $size = 80, string $default = "identicon", string $rating = "g") : string
    {
        $email = strtolower(trim($this->email));
        $hash = md5($email);
        return "https://www.gravatar.com/avatar/$hash?s=$size&d=$default&r=$rating";
    }


    /**
     * Votes on a specific comment as this user
     * @param int $commentId ID of comment
     * @param int $vote 1 for upvote -1 for downvote
     *
     * @return void
     */
    public function voteComment(int $commentId, int $vote): void
    {
        $comment = new Comment($this->db);
        $comment->findById($commentId);

        if (is_null($comment->id)) {
            throw new \Exception("Can't vote on that comment, it doesn't exist.");
        } else if ($comment->userId == $this->id) {
            throw new \Exception("Can't vote on your own comment.");
        }

        $this->vote("comment", $commentId, $vote);
    }


    /**
     * Votes on a specific answer as this user
     * @param int $answerId ID of answer
     * @param int $vote 1 for upvote -1 for downvote
     *
     * @return void
     */
    public function voteAnswer(int $answerId, int $vote): void
    {
        $answer = new Answer($this->db);
        $answer->findById($answerId);

        if (is_null($answer->id)) {
            throw new \Exception("Can't vote on that answer, it doesn't exist.");
        } else if ($answer->userId == $this->id) {
            throw new \Exception("Can't vote on your own answer.");
        }

        $this->vote("answer", $answerId, $vote);
    }


    /**
     * Votes on a specific question as this user
     * @param int $questionId ID of question
     * @param int $vote       1 for upvote -1 for downvote
     *
     * @return void
     */
    public function voteQuestion(int $questionId, int $vote): void
    {
        $question = new Question($this->db);
        $question->findById($questionId);

        if (is_null($question->id)) {
            throw new \Exception("Can't vote on that question, it doesn't exist.");
        } else if ($question->userId == $this->id) {
            throw new \Exception("Can't vote on your own question.");
        }

        $this->vote("question", $questionId, $vote);
    }


    /**
     * Gets the number of posts this user has made
     *
     * @return object
     */
    public function postsCount(): object
    {
        $this->checkDb();
        $this->db->connect();

        // Count posts
        $res = $this->db->select("
                COUNT(DISTINCT q.id) AS questions,
                COUNT(DISTINCT a.id) AS answers,
                COUNT(DISTINCT c.id) AS comments,
                COUNT(DISTINCT v.id) AS votes
            ")
            ->from("{$this->tableName} AS u")
            ->leftJoin("Question AS q", "u.id = q.userId")
            ->leftJoin("Answer AS a", "u.id = a.userId")
            ->leftJoin("Comment AS c", "u.id = c.userId")
            ->leftJoin("Vote AS v", "u.id = v.userId")
            ->where("u.id = ?")
            ->execute([ $this->id ])
            ->fetch();

        return (object) [
            "questions" => (int) $res->questions,
            "answers"   => (int) $res->answers,
            "comments"  => (int) $res->comments,
            "votes"     => (int) $res->votes,
        ];
    }



    /**
     * Checks if user has voted on post
     * @param Post $post Answer, Question or Comment object.
     *
     * @return int 0 if user hasn't voted, 1 for upvoted and -1 for downvoted.
     */
    public function votedOnPost(Post $post): int
    {
        $this->checkDb();
        if (is_null($this->id)) {
            throw new \Exception("User id is null");
        } else if (is_null($post->id)) {
            throw new \Exception("Post id is null");
        }

        if ($post instanceof Answer) {
            $postName = "answer";
        } else if ($post instanceof Question) {
            $postName = "question";
        } else if ($post instanceof Comment) {
            $postName = "comment";
        } else {
            throw new \TypeError();
        }

        // Get if a user voted
        $this->db->connect();
        $res = $this->db->select("vote")
            ->from("Vote")
            ->where("{$postName}Id = ? AND userId = ?")
            ->execute([ $post->id, $this->id ])
            ->fetch();

        return is_null($res) ? 0 : (int) $res->vote;
    }
}

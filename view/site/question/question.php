<?php

namespace Anax\View;

/**
 * View to read question.
 */
global $di;

$loggedIn = !is_null($user);
$author = $loggedIn ? $question->author() : null;

function ownPost($post)
{
    global $di;
    return $di->auth->loggedIn() && $di->auth->user->id == $post->userId;
}

function printEdit($post, $type)
{
    if (ownPost($post)) {
        $editUrl = url("$type/update/$post->id");
        $deleteUrl = url("$type/delete/$post->id");
        return "
            <a class='icon edit' href='$editUrl'></a>
            <a class='icon delete' href='$deleteUrl'></a>
        ";
    }

    return "";
}
?>

<div class="question <?= ownPost($question) ? "own" : "" ?>">
    <h1 class="title"><?= $question->title ?></h1>

    <?= printEdit($question, "question") ?>
    <div class="content"><?= markdown($question->content) ?></div>
    <div class="tags">Tags: <?= implode(" ", $question->tags()) ?></div>
    <div class="meta">
        <?php
            $post = $question;
            include "meta.php";
        ?>
    </div>

    <h4>Comments</h4>
    <div class="comments">
        <?php foreach ($question->comments() as $comment) : ?>
            <div class="comment <?= ownPost($comment) ? "own" : "" ?>">
                <?= printEdit($comment, "comment") ?>
                <div class="content"><?= markdown($comment->content) ?></div>
                <div class="meta">
                    <?php
                        $post = $comment;
                        include "meta.php";
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($loggedIn) : ?>
        <div class="actions">
            <a class="button" href="<?= url("comment/question/{$question->id}") ?>">
                Comment
            </a>

            <?php if ($user->id != $author->id) : ?>
                <a class="button" href="<?= url("question/answer/{$question->id}") ?>">
                    Answer
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div class="sorting">
    Sorting: 
    <a class="icon rep <?= $sort != "date" ? "disabled" : "" ?>" href="<?= url("question/$question->id") ?>"> Rank</a>
    <a class="icon date <?= $sort == "date" ? "disabled" : "" ?>" href="<?= url("question/$question->id?sort=date") ?>"> Date</a>
</div>

<h3>Answers</h3>

<?php foreach ($answers as $answer) : ?>
    <div class="answer <?= ownPost($answer) ? "own" : "" ?>">
        <?php if ($answer->id == $question->answerId) : ?>
            <h4>Accepted answer</h4>
        <?php endif; ?>

        <?= printEdit($answer, "answer") ?>
        <div class="content"><?= markdown($answer->content) ?></div>
        <div class="meta">
            <?php
                $post = $answer;
                include "meta.php";
            ?>
        </div>

        <h4>Comments</h4>
        <div class="comments">
            <?php foreach ($answer->comments() as $comment) : ?>
                <div class="comment <?= ownPost($comment) ? "own" : "" ?>">
                    <?= printEdit($comment, "comment") ?>
                    <div class="content"><?= markdown($comment->content) ?></div>
                    <div class="meta">
                        <?php
                            $post = $comment;
                            include "meta.php";
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($loggedIn) : ?>
            <div class="actions">
                <a class="button" href="<?= url("comment/answer/{$answer->id}") ?>">
                    Comment
                </a>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

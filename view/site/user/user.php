<?php

namespace Anax\View;

/**
 * View to view user profile.
 */
?>

<h1><?= e($user->alias) ?></h1>

<img src="<?= e($user->gravatar(150)) ?>" alt="Avatar for user <?= e($user->alias) ?>">
<span class="rep">Reputation <?= $user->rep ?></span>

<h3>Questions</h3>
<ul class="questions">
    <?php foreach ($questions as $question) : ?>
        <li>
            <a href="<?= url("question/$question->id") ?>"><?= e($question->title) ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<h3>Answers</h3>
<ul class="answers">
    <?php foreach ($answers as $answer) : ?>
        <li>
            <a href="<?= url("question/$answer->questionId#answer$answer->id") ?>"><?= previewMarkdown($answer->content) ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<h3>Comments</h3>
<ul class="comments">
    <?php foreach ($comments as $comment) : ?>
        <li>
            <a href="<?= url("question/$comment->questionId#comment$comment->id") ?>"><?= previewMarkdown($comment->content) ?></a>
        </li>
    <?php endforeach; ?>
</ul>

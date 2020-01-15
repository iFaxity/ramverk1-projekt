<?php

namespace Anax\View;

/**
 * View to show new questions and popular tags.
 */
?>

<h1>Welcome to CodeCommunity</h1>

<h2>Latest questions</h2>
<ul class="questions">
    <?php foreach ($questions as $question) : ?>
        <li>
            <a href="<?= url("question/$question->id") ?>">
                <?= e($question->title) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<h2>Top tags</h2>
<ul class="tags">
    <?php foreach ($tags as $tag) : ?>
        <li>
            <a href="<?= url("tags/$tag->tag") ?>">#<?= $tag->tag ?></a>
            <?= $tag->count ?> questions
        </li>
    <?php endforeach; ?>
</ul>

<h2>Active users</h2>
<ul class="users">
    <?php foreach ($users as $user) : ?>
        <li>
            <a href="<?= url("users/$user->alias") ?>"><?= $user->alias ?></a>
             rep <?= $user->rep ?>
        </li>
    <?php endforeach; ?>
</ul>

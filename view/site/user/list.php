<?php

namespace Anax\View;

/**
 * View to view users.
 */
?>

<h1>Users</h1>

<div class="users">
    <?php foreach ($users as $user) :
        $posts = $user->postsCount();
        ?>
        <a class="user" href="<?= url("users/$user->alias") ?>">
            <img src="<?= e($user->gravatar(50)) ?>" alt="Avatar for user <?= e($user->alias) ?>">
            <span class="alias"><?= $user->alias ?></span> 
            <span class="rep">- rep <?= $user->rep ?></span>
        </a>
        <div class="posts">
            <span class="posts-questions">Questions <?= $posts->questions ?></span>
            <span class="posts-answers">Answers <?= $posts->answers ?></span>
            <span class="posts-comments">Comments <?= $posts->comments ?></span>
            <span class="posts-votes">Votes <?= $posts->votes ?></span>
        </div>
    <?php endforeach; ?>
</div>

<?php

namespace Anax\View;

/**
 * View to list questions.
 */

global $di;
$author = $post->author();
$upvotedClasses = ["icon", "upvote"];
$downvotedClasses = ["icon", "downvote"];

if ($di->auth->loggedIn()) {
    $voted = $di->auth->user->votedOnPost($post);
    if ($voted === 1) {
        $upvotedClasses[] = "active";
    } else if ($voted === -1) {
        $downvotedClasses[] = "active";
    }

    if ($di->auth->user->id == $author->id) {
        $upvotedClasses[] = "disabled";
        $downvotedClasses[] = "disabled";
    }
}

if ($post instanceof \Faxity\Models\Question) {
    $postId = "q$post->id";
} else if ($post instanceof \Faxity\Models\Answer) {
    $postId = "a$post->id";
} else if ($post instanceof \Faxity\Models\Comment) {
    $postId = "c$post->id";
} else {
    throw new \Exception("Post not of a valid subclass");
}
?>

<div class="left">
    <span class="votes"><?= $post->votes() ?></span>
    <div class="vote">
        <i class="icon <?= implode(" ", $upvotedClasses) ?>" title="Upvote" data-id="<?= $postId ?>">Upvote</i>
        <i class="icon <?= implode(" ", $downvotedClasses) ?>" title="Downvote" data-id="<?= $postId ?>">Downvote</i>
    </div>
</div>
<div class="right">
    <a class="author" href="<?= url("users/$author->alias") ?>">
        <span class="alias"><?= $author->alias ?></span>
        <img src="<?= $author->gravatar(25) ?>" alt="Avatar for user <?= $author->alias ?>">
    </a>
    <div class="timestamps">
        <span class="timestamp created">Posted <?= $post->createdTimestamp() ?></span>
        <?php if (!is_null($post->updated)) : ?>
            <span class="timestamp updated">Edited <?= $post->updatedTimestamp() ?></span>
        <?php endif; ?>
    </div>
</div>

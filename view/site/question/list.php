<?php

namespace Anax\View;

/**
 * View to list questions.
 */
?>

<h1>Questions</h1>

<a class="button solid" href="<?= url("question/create") ?>">Create new question</a>

<?php if (empty($questions)) : ?>
    <p>No questions...yet</p>
<?php else : ?>
<ul>
    <?php foreach ($questions as $question) : ?>
        <li>
            <span class="rep">Rep: <?= $question->votes() ?></span>
            -
            <a href="<?= url("question/$question->id") ?>">
                <?= $question->title ?>
            </a>
            -
            <span class="answers"><?= $question->answersCount() ?> answers</span>
        </li>
    <?php endforeach; ?>
</ul>

<?php endif; ?>

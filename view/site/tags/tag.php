<?php

namespace Anax\View;

/**
 * View to list questions linked to tag.
 */
?>

<h1>#<?= $tag ?></h1>

<?php if (empty($questions)) : ?>
    <p>This tag is not linked to any question</p>
<?php else : ?>
<ul class="questions">
    <?php foreach ($questions as $question) : ?>
        <li>
            <a href="<?= url("question/$question->id") ?>">
                <?= $question->title ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

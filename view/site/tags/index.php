<?php

namespace Anax\View;

/**
 * View to list tags.
 */
?>

<h1>Tags</h1>

<?php if (empty($tags)) : ?>
    <p>No tags...yet</p>
<?php else : ?>
<ul class="tags">
    <?php foreach ($tags as $tag) : ?>
        <li>
            <a href="<?= url("tags/$tag->tag") ?>">
                #<?= $tag->tag ?> (<?= $tag->count ?> posts)
            </a>
        </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

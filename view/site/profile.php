<?php

namespace Anax\View;

/**
 * View to view and edit current profile.
 */
?>

<h1>Profile: <?= e($user->alias) ?></h1>

<img src="<?= e($user->gravatar(150)) ?>" alt="Avatar for user <?= e($user->alias) ?>">

<?= $form ?>

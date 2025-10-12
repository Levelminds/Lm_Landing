<?php
require __DIR__ . '/bootstrap/app.php';

$content = lm_view('pages/team');

echo lm_view('layouts/app', [
    'title' => 'LevelMinds Team | Builders of the educator hiring platform',
    'description' => 'Meet the product, engineering, and educator success team crafting LevelMinds for schools and teachers.',
    'bodyClass' => 'team-page',
    'content' => $content,
]);

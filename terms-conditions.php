<?php
require __DIR__ . '/bootstrap/app.php';

$content = lm_view('pages/terms');

echo lm_view('layouts/app', [
    'title' => 'LevelMinds Terms of Service',
    'description' => 'Review the terms that govern your use of LevelMinds marketing experiences and educator hiring platform.',
    'bodyClass' => 'terms-page',
    'content' => $content,
]);

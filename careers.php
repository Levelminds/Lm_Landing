<?php
require __DIR__ . '/bootstrap/app.php';

$content = lm_view('pages/careers');

echo lm_view('layouts/app', [
    'title' => 'Careers at LevelMinds | Build the future of teacher hiring',
    'description' => 'Explore open roles at LevelMinds across product, engineering, design, and educator success.',
    'bodyClass' => 'careers-page',
    'content' => $content,
]);

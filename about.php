<?php
require __DIR__ . '/bootstrap/app.php';

$content = lm_view('pages/about');

echo lm_view('layouts/app', [
    'title' => 'About LevelMinds | Our mission to transform teacher hiring',
    'description' => 'Learn about LevelMinds, the team empowering schools and teachers with transparent, data-backed hiring journeys.',
    'bodyClass' => 'about-page',
    'content' => $content,
]);

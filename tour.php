<?php
require __DIR__ . '/bootstrap/app.php';

$content = lm_view('pages/tour');

echo lm_view('layouts/app', [
    'title' => 'LevelMinds Tour | Walk through the hiring command center',
    'description' => 'Explore how LevelMinds streamlines teacher recruitment with collaborative workflows, analytics, and teacher-first experiences.',
    'bodyClass' => 'tour-page',
    'content' => $content,
]);

<?php
require __DIR__ . '/bootstrap/app.php';

$content = lm_view('pages/solutions');

echo lm_view('layouts/app', [
    'title' => 'LevelMinds Solutions | Built for schools, trusts, and teachers',
    'description' => 'Discover how LevelMinds powers hiring operations for schools, networks, and educators with configurable workflows and insights.',
    'bodyClass' => 'solutions-page',
    'content' => $content,
]);

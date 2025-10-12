<?php
require __DIR__ . '/bootstrap/app.php';

$content = lm_view('pages/privacy');

echo lm_view('layouts/app', [
    'title' => 'LevelMinds Privacy Policy',
    'description' => 'Understand how LevelMinds collects, uses, and protects personal data for schools, teachers, and partners.',
    'bodyClass' => 'privacy-page',
    'content' => $content,
]);

<?php
require __DIR__ . '/bootstrap/app.php';

$content = lm_view('pages/home');

echo lm_view('layouts/app', [
    'title' => 'LevelMinds | Connect inspiring teachers with visionary schools',
    'description' => 'LevelMinds is the hiring command center for schools and teachers—post jobs, track applications, and unlock classrooms that thrive.',
    'bodyClass' => 'landing-page',
    'content' => $content,
]);

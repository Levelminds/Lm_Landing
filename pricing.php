<?php
require __DIR__ . '/bootstrap/app.php';

$content = lm_view('pages/pricing');

echo lm_view('layouts/app', [
    'title' => 'LevelMinds Pricing | Plans for schools and educator networks',
    'description' => 'Choose a LevelMinds plan that matches your institution. Flexible subscriptions for single campuses, school groups, and national networks.',
    'bodyClass' => 'pricing-page',
    'content' => $content,
]);

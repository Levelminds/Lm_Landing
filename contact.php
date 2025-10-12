<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/api/contact.php';
    return;
}

require __DIR__ . '/bootstrap/app.php';

$content = lm_view('pages/contact');

echo lm_view('layouts/app', [
    'title' => 'Contact LevelMinds | Talk to our team',
    'description' => 'Connect with the LevelMinds team for product walkthroughs, support, or partnership conversations.',
    'bodyClass' => 'contact-page',
    'content' => $content,
]);

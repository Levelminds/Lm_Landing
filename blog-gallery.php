<?php
require __DIR__ . '/bootstrap/app.php';

$categories = [
    'teachers' => 'Teachers',
    'schools' => 'Schools',
    'general' => 'General',
];

$featured = [];
$postsByCategory = [
    'teachers' => [],
    'schools' => [],
    'general' => [],
];
$error = '';

try {
    $pdo = lm_db();
    $posts = lm_fetch_blog_posts($pdo);
    [$featuredPost, $buckets] = lm_partition_blog_posts($posts);

    if (!empty($featuredPost)) {
        $featured = [
            'id' => (int) $featuredPost['id'],
            'title' => lm_decode_blog_text($featuredPost['title'] ?? ''),
            'author' => lm_decode_blog_text($featuredPost['author'] ?? 'LevelMinds Team'),
            'excerpt' => lm_blog_excerpt($featuredPost, 220),
            'published_on' => lm_format_blog_date($featuredPost['created_at'] ?? ''),
            'media_url' => lm_blog_media_url($featuredPost),
        ];
    }

    foreach ($categories as $key => $label) {
        $categoryPosts = $buckets[$key] ?? [];
        $postsByCategory[$key] = array_map(static function (array $post) {
            return [
                'id' => (int) $post['id'],
                'title' => lm_decode_blog_text($post['title'] ?? ''),
                'author' => lm_decode_blog_text($post['author'] ?? 'LevelMinds Team'),
                'excerpt' => lm_blog_excerpt($post, 160),
                'published_on' => lm_format_blog_date($post['created_at'] ?? ''),
                'media_url' => lm_blog_media_url($post),
            ];
        }, array_slice($categoryPosts, 0, 6));
    }
} catch (Throwable $exception) {
    $error = 'We could not load the latest stories right now. Please try again later.';
}

$content = lm_view('pages/blog-gallery', [
    'featured' => $featured,
    'categories' => $categories,
    'postsByCategory' => $postsByCategory,
]);

echo lm_view('layouts/app', [
    'title' => 'LevelMinds Blog Gallery | Insights for Schools & Teachers',
    'description' => 'Browse the latest LevelMinds articles covering hiring playbooks, teacher journeys, and platform updates.',
    'bodyClass' => 'blog-gallery-page',
    'content' => ($error !== '' ? '<section class="py-5"><div class="container"><div class="alert alert-warning">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div></div></section>' : '') . $content,
]);

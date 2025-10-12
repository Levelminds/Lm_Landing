<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$postId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid blog identifier']);
    exit;
}

try {
    $pdo = lm_db();
    $stmt = $pdo->prepare(
        "SELECT id, title, author, summary, content, media_url, created_at, views, likes, category, status
         FROM blog_posts
         WHERE id = :id AND LOWER(status) IN ('published', 'approved')
         LIMIT 1"
    );
    $stmt->execute([':id' => $postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Blog not found or unpublished']);
        exit;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $hostName = $_SERVER['HTTP_HOST'] ?? 'www.levelminds.in';
    $baseShareUrl = sprintf('%s://%s', $scheme, $hostName);

    $visitorToken = $_GET['visitor_token'] ?? '';
    $likesStmt = $pdo->prepare('SELECT COUNT(*) FROM blog_likes WHERE post_id = :id');
    $likesStmt->execute([':id' => $postId]);
    $likesCount = (int) $likesStmt->fetchColumn();

    $liked = false;
    if ($visitorToken !== '') {
        $likedStmt = $pdo->prepare('SELECT COUNT(*) FROM blog_likes WHERE post_id = :id AND visitor_token = :token');
        $likedStmt->execute([':id' => $postId, ':token' => $visitorToken]);
        $liked = (bool) $likedStmt->fetchColumn();
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => (int) $post['id'],
            'title' => lm_decode_blog_text($post['title'] ?? ''),
            'author' => lm_decode_blog_text($post['author'] ?? 'LevelMinds Team'),
            'summary' => lm_blog_excerpt($post, 200),
            'content' => lm_decode_blog_html($post['content'] ?? ''),
            'media_url' => lm_blog_media_url($post),
            'category' => lm_blog_category_label($post['category'] ?? 'general'),
            'views' => (int) $post['views'],
            'likes' => $likesCount,
            'likes_raw' => (int) $post['likes'],
            'published_on' => lm_format_blog_date($post['created_at'] ?? ''),
            'read_time' => lm_blog_read_time($post),
            'share_url' => lm_blog_share_url((int) $post['id'], $baseShareUrl),
            'user_liked' => $liked,
        ],
    ]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to load the blog at the moment.',
        'details' => $exception->getMessage(),
    ]);
}

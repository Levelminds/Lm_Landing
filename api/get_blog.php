<?php
declare(strict_types=1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$postId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid blog identifier']);
    exit;
}

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

function decodeBlogHtml($value): string
{
    return trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function decodeBlogText($value): string
{
    return trim(strip_tags(decodeBlogHtml($value)));
}

function normaliseCategory($value): string
{
    $normalised = strtolower(trim((string) $value));

    $mapping = [
        'teacher' => 'Teachers',
        'teachers' => 'Teachers',
        'school' => 'Schools',
        'schools' => 'Schools',
        'general' => 'General',
    ];

    return $mapping[$normalised] ?? 'General';
}

function formatBlogDate($value): string
{
    if ($value === null || $value === '') {
        return '—';
    }

    try {
        $date = new DateTime($value);
        return $date->format('M j, Y');
    } catch (Throwable $exception) {
        return '—';
    }
}

function calculateReadTime(string $content, string $summary): string
{
    $text = strip_tags($content !== '' ? $content : $summary);
    if ($text === '') {
        return '';
    }

    $wordCount = str_word_count($text);
    if ($wordCount === 0) {
        return '';
    }

    $minutes = max(1, (int) ceil($wordCount / 200));
    return sprintf('%d min read', $minutes);
}

function fallbackContent(string $content, string $summary): string
{
    if ($content !== '') {
        return $content;
    }

    if ($summary !== '') {
        return $summary;
    }

    return '<p>Full story coming soon.</p>';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $statement = $pdo->prepare('SELECT id, title, author, summary, content, media_url, created_at, views, likes, category, status FROM blog_posts WHERE id = :id LIMIT 1');
    $statement->execute([':id' => $postId]);

    $post = $statement->fetch(PDO::FETCH_ASSOC);

    $status = strtolower(trim((string) ($post['status'] ?? '')));

    if (!$post || ($status !== 'published' && $status !== 'approved')) {
        http_response_code(404);
        echo json_encode(['error' => 'Blog post not found']);
        exit;
    }

    $title = decodeBlogText($post['title'] ?? '');
    $author = decodeBlogText($post['author'] ?? 'LevelMinds Team');
    $summary = decodeBlogHtml($post['summary'] ?? '');
    $content = decodeBlogHtml($post['content'] ?? '');
    $category = normaliseCategory($post['category'] ?? '');
    $views = (int) ($post['views'] ?? 0);
    $likes = (int) ($post['likes'] ?? 0);
    $createdAt = $post['created_at'] ?? '';

    $fullContent = fallbackContent($content, $summary);

    $readTime = calculateReadTime($content, $summary);

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $hostName = $_SERVER['HTTP_HOST'] ?? 'www.levelminds.in';
    $shareUrl = sprintf('%s://%s/blog_single.php?id=%d', $scheme, $hostName, $postId);

    echo json_encode([
        'success' => true,
        'id' => $postId,
        'title' => $title,
        'author' => $author,
        'date' => formatBlogDate($createdAt),
        'category' => $category,
        'summary' => decodeBlogText($post['summary'] ?? ''),
        'content' => $fullContent,
        'views' => $views,
        'likes' => $likes,
        'read_time' => $readTime,
        'share_url' => $shareUrl,
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load blog post']);
}

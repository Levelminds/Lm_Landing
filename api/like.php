<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
    exit;
}

$postId = isset($payload['post_id']) ? (int) $payload['post_id'] : 0;
$visitorToken = isset($payload['visitor_token']) ? trim((string) $payload['visitor_token']) : '';

if ($postId <= 0 || $visitorToken === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid like request']);
    exit;
}

$tokensPath = dirname(__DIR__) . '/data/blog_like_tokens.json';
if (!is_dir(dirname($tokensPath))) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Storage directory missing']);
    exit;
}

$tokenData = [];
if (is_file($tokensPath) && is_readable($tokensPath)) {
    $decodedTokens = json_decode((string) file_get_contents($tokensPath), true);
    if (is_array($decodedTokens)) {
        $tokenData = $decodedTokens;
    }
}

$currentLiked = (bool) ($tokenData[$postId][$visitorToken]['liked'] ?? false);
$newLiked = !$currentLiked;
$delta = $newLiked ? 1 : -1;
$likes = null;

try {
    $pdo = lm_db();
    $pdo->beginTransaction();

    $select = $pdo->prepare('SELECT likes FROM blog_posts WHERE id = :id FOR UPDATE');
    $select->execute([':id' => $postId]);
    $row = $select->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Blog post not found']);
        exit;
    }

    $likes = (int) $row['likes'];
    $likes = max(0, $likes + $delta);

    $update = $pdo->prepare('UPDATE blog_posts SET likes = :likes WHERE id = :id');
    $update->execute([':likes' => $likes, ':id' => $postId]);

    $log = $pdo->prepare('INSERT INTO blog_likes (post_id, visitor_token, liked_at, liked) VALUES (:id, :token, NOW(), :liked)
        ON DUPLICATE KEY UPDATE liked = VALUES(liked), liked_at = VALUES(liked_at)');
    $log->execute([
        ':id' => $postId,
        ':token' => $visitorToken,
        ':liked' => $newLiked ? 1 : 0,
    ]);

    $pdo->commit();
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to update like']);
    exit;
}

$tokenData[$postId] = $tokenData[$postId] ?? [];
$tokenData[$postId][$visitorToken] = ['liked' => $newLiked, 'updated_at' => time()];

file_put_contents($tokensPath, json_encode($tokenData, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'data' => [
        'likes' => $likes,
        'liked' => $newLiked,
    ],
]);

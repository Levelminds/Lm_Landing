<?php
declare(strict_types=1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

$postId = isset($payload['post_id']) ? (int) $payload['post_id'] : 0;
$visitorToken = isset($payload['visitor_token']) ? trim((string) $payload['visitor_token']) : '';

if ($postId <= 0 || $visitorToken === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid like request']);
    exit;
}

$delta = 0;
$tokensPath = dirname(__DIR__) . '/data/blog_like_tokens.json';
$tokensDir = dirname($tokensPath);

if (!is_dir($tokensDir)) {
    http_response_code(500);
    echo json_encode(['error' => 'Storage directory missing']);
    exit;
}

$tokenData = [];

if (is_file($tokensPath) && is_readable($tokensPath)) {
    $decodedTokens = json_decode((string) file_get_contents($tokensPath), true);
    if (is_array($decodedTokens)) {
        $tokenData = $decodedTokens;
    }
}

$currentTokenEntry = $tokenData[$postId][$visitorToken] ?? null;
$currentLiked = false;

if (is_array($currentTokenEntry)) {
    if (array_key_exists('liked', $currentTokenEntry)) {
        $currentLiked = (bool) $currentTokenEntry['liked'];
    } elseif (array_key_exists('likes', $currentTokenEntry)) {
        $currentLiked = true;
    }
} elseif ($currentTokenEntry !== null) {
    $currentLiked = (bool) $currentTokenEntry;
}

$newLiked = !$currentLiked;
$delta = $newLiked ? 1 : -1;

$likes = null;

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

$pdo = null;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->beginTransaction();
    $select = $pdo->prepare('SELECT likes FROM blog_posts WHERE id = :id FOR UPDATE');
    $select->execute([':id' => $postId]);
    $row = $select->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $currentLikes = max(0, (int) $row['likes']);
        $updatedLikes = $currentLikes + $delta;
        if ($updatedLikes < 0) {
            $updatedLikes = 0;
        }

        $update = $pdo->prepare('UPDATE blog_posts SET likes = :likes WHERE id = :id');
        $update->execute([
            ':likes' => $updatedLikes,
            ':id' => $postId,
        ]);

        $pdo->commit();
        $likes = $updatedLikes;
    } else {
        $pdo->rollBack();
    }
} catch (PDOException $exception) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $likes = null;
}

if ($likes === null) {
    $fallbackPath = dirname(__DIR__) . '/data/blogs.json';
    if (is_file($fallbackPath) && is_readable($fallbackPath) && is_writable($fallbackPath)) {
        $contents = json_decode((string) file_get_contents($fallbackPath), true);
        if (is_array($contents)) {
            foreach ($contents as $index => $item) {
                if ((int) ($item['id'] ?? 0) === $postId) {
                    $currentLikes = max(0, (int) ($item['likes'] ?? 0));
                    $updatedLikes = $currentLikes + $delta;
                    if ($updatedLikes < 0) {
                        $updatedLikes = 0;
                    }
                    $contents[$index]['likes'] = $updatedLikes;
                    $likes = $updatedLikes;
                    file_put_contents(
                        $fallbackPath,
                        json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                        LOCK_EX
                    );
                    break;
                }
            }
        }
    }

    if ($likes === null) {
        $likes = $delta > 0 ? 1 : 0;
    }
}

if (!isset($tokenData[$postId]) || !is_array($tokenData[$postId])) {
    $tokenData[$postId] = [];
}

if ($newLiked) {
    $tokenData[$postId][$visitorToken] = [
        'timestamp' => time(),
        'liked' => true,
    ];
} else {
    unset($tokenData[$postId][$visitorToken]);
    if (empty($tokenData[$postId])) {
        unset($tokenData[$postId]);
    }
}

file_put_contents(
    $tokensPath,
    json_encode($tokenData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

echo json_encode([
    'success' => true,
    'liked' => $newLiked,
    'likes' => $likes,
]);


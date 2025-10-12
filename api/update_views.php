<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
    exit;
}

$postId = isset($payload['post_id']) ? (int) $payload['post_id'] : 0;
if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid blog identifier']);
    exit;
}

try {
    $pdo = lm_db();
    $pdo->beginTransaction();

    $increment = $pdo->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = :id');
    $increment->execute([':id' => $postId]);

    if ($increment->rowCount() === 0) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Blog post not found']);
        exit;
    }

    $select = $pdo->prepare('SELECT views FROM blog_posts WHERE id = :id');
    $select->execute([':id' => $postId]);
    $row = $select->fetch(PDO::FETCH_ASSOC);

    $pdo->commit();

    $views = $row ? (int) $row['views'] : 0;

    echo json_encode([
        'success' => true,
        'views' => $views,
    ]);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to update views']);
}

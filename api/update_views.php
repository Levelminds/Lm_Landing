<?php
declare(strict_types=1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

$postId = isset($payload['post_id']) ? (int) $payload['post_id'] : 0;

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid blog identifier']);
    exit;
}

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $increment = $pdo->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = :id');
    $increment->execute([':id' => $postId]);

    if ($increment->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Blog post not found']);
        exit;
    }

    $select = $pdo->prepare('SELECT views FROM blog_posts WHERE id = :id');
    $select->execute([':id' => $postId]);
    $row = $select->fetch(PDO::FETCH_ASSOC);

    $views = $row ? (int) $row['views'] : 0;

    echo json_encode([
        'success' => true,
        'views' => $views,
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to update views']);
}

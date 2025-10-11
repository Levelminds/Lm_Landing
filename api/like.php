<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$blogId = isset($input['blog_id']) ? (int) $input['blog_id'] : 0;
$action = isset($input['action']) && $input['action'] === 'unlike' ? 'unlike' : 'like';

if ($blogId <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid blog id']);
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

    $select = $pdo->prepare('SELECT likes FROM blog_posts WHERE id = :id LIMIT 1');
    $select->execute(['id' => $blogId]);
    $current = $select->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Blog not found']);
        exit;
    }

    if ($action === 'unlike') {
        $update = $pdo->prepare('UPDATE blog_posts SET likes = GREATEST(likes - 1, 0) WHERE id = :id');
    } else {
        $update = $pdo->prepare('UPDATE blog_posts SET likes = likes + 1 WHERE id = :id');
    }
    $update->execute(['id' => $blogId]);

    $select->execute(['id' => $blogId]);
    $latest = $select->fetch(PDO::FETCH_ASSOC);
    $likes = (int) ($latest['likes'] ?? 0);

    echo json_encode(['status' => 'ok', 'likes' => $likes]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error', 'details' => $e->getMessage()]);
}

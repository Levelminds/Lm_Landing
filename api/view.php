<?php
// api/view.php
header('Content-Type: application/json');

try {
  // You can reuse the same credentials as in blogs.php
  $host = 'localhost';
  $dbname = 'u420143207_LM_landing';
  $username = 'u420143207_lmlanding';
  $password = 'Levelminds@2024';

  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input) { throw new Exception('Invalid payload', 400); }

  $postId = isset($input['post_id']) ? (int)$input['post_id'] : 0;
  if ($postId <= 0) { throw new Exception('Missing post_id', 400); }

  $pdo->beginTransaction();
  $pdo->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = ?')->execute([$postId]);
  $stmt = $pdo->prepare('SELECT views FROM blog_posts WHERE id = ?');
  $stmt->execute([$postId]);
  $views = (int) ($stmt->fetchColumn() ?: 0);
  $pdo->commit();

  echo json_encode(['ok' => true, 'views' => $views]);
} catch (Exception $e) {
  if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
  $code = (int)$e->getCode();
  if ($code < 400 || $code >= 600) { $code = 500; }
  http_response_code($code);
  echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}

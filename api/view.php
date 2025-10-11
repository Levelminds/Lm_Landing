<?php
// api/view.php — increments views and returns latest count.
// Expects JSON: { post_id: number }
header('Content-Type: application/json');

$dsn = 'mysql:host=localhost;dbname=u420143207_LM_landing;charset=utf8mb4';
$user = 'u420143207_lmlanding';
$pass = 'Levelminds@2024';

try {
  $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  $in = json_decode(file_get_contents('php://input'), true) ?: [];
  $id = (int)($in['post_id'] ?? 0);
  if ($id <= 0) throw new Exception('Bad post_id', 400);
  $pdo->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = ?')->execute([$id]);
  $stmt = $pdo->prepare('SELECT views FROM blog_posts WHERE id = ?'); $stmt->execute([$id]);
  $views = (int)$stmt->fetchColumn();
  echo json_encode(['ok'=>true,'views'=>$views]);
} catch (Exception $e) {
  http_response_code(($e->getCode()>=400 && $e->getCode()<600) ? $e->getCode() : 500);
  echo json_encode(['ok'=>false,'message'=>$e->getMessage()]);
}

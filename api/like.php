<?php
// api/like.php
header('Content-Type: application/json');

try {
  // Use your existing DB connector if you have one:
  // require_once __DIR__ . '/../db.php';  // must define $pdo

  // Or inline creds (replace):
  $dsn = 'mysql:host=localhost;dbname=u420143207_LM_landing;charset=utf8mb4';
  $user = 'u420143207_lmlanding';
  $pass = 'Levelminds@2024';
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input) { throw new Exception('Invalid payload', 400); }

  $postId = isset($input['post_id']) ? (int)$input['post_id'] : 0;
  $email  = isset($input['email']) ? trim($input['email']) : '';
  if ($postId <= 0 || $email === '') { throw new Exception('Missing post_id or email', 400); }

  // Must be subscribed (active)
  $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ? AND status = 'active'");
  $stmt->execute([$email]);
  if (!$stmt->fetchColumn()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Please subscribe to LM Blogs to use the Like feature.']);
    exit;
  }

  // Ensure likes table exists (run once manually in SQL ideally)
  // CREATE TABLE IF NOT EXISTS blog_likes (id INT AUTO_INCREMENT PRIMARY KEY, post_id INT NOT NULL, email VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uniq_like (post_id, email), FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE);

  $pdo->beginTransaction();

  // Toggle like
  $stmt = $pdo->prepare('SELECT id FROM blog_likes WHERE post_id = ? AND email = ?');
  $stmt->execute([$postId, $email]);
  $existing = $stmt->fetchColumn();

  if ($existing) {
    $pdo->prepare('DELETE FROM blog_likes WHERE id = ?')->execute([$existing]);
    $pdo->prepare('UPDATE blog_posts SET likes = GREATEST(likes - 1, 0) WHERE id = ?')->execute([$postId]);
    $liked = false;
  } else {
    $pdo->prepare('INSERT INTO blog_likes (post_id, email) VALUES (?, ?)')->execute([$postId, $email]);
    $pdo->prepare('UPDATE blog_posts SET likes = likes + 1 WHERE id = ?')->execute([$postId]);
    $liked = true;
  }

  $stmt = $pdo->prepare('SELECT likes FROM blog_posts WHERE id = ?');
  $stmt->execute([$postId]);
  $likes = (int) ($stmt->fetchColumn() ?: 0);

  $pdo->commit();

  echo json_encode(['ok' => true, 'liked' => $liked, 'likes' => $likes]);
} catch (Exception $e) {
  if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
  $code = $e->getCode();
  if ($code < 400 || $code >= 600) { $code = 500; }
  http_response_code($code);
  echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}

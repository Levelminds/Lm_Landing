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

  $pdo->exec("CREATE TABLE IF NOT EXISTS blog_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    visitor_token VARCHAR(64) NOT NULL,
    email VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_like (post_id, visitor_token),
    KEY idx_token (visitor_token),
    KEY idx_email (email),
    CONSTRAINT fk_blog_likes_post
      FOREIGN KEY (post_id)
      REFERENCES blog_posts(id)
      ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

  // Lightweight migration for older schemas that only stored email
  $columns = $pdo->query('SHOW COLUMNS FROM blog_likes')->fetchAll(PDO::FETCH_COLUMN);
  if (!in_array('visitor_token', $columns, true)) {
    $pdo->exec("ALTER TABLE blog_likes ADD COLUMN visitor_token VARCHAR(64) NOT NULL AFTER post_id");
    $pdo->exec("UPDATE blog_likes SET visitor_token = SHA2(CONCAT(post_id, ':', email), 256)");
    $pdo->exec("ALTER TABLE blog_likes MODIFY email VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE blog_likes DROP INDEX uniq_like, ADD UNIQUE KEY uniq_like (post_id, visitor_token), ADD KEY idx_token (visitor_token), ADD KEY idx_email (email)");
  }

  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input) { throw new Exception('Invalid payload', 400); }

  $postId = isset($input['post_id']) ? (int)$input['post_id'] : 0;
  $token  = isset($input['visitor_token']) ? trim($input['visitor_token']) : '';
  $email  = isset($input['email']) ? trim($input['email']) : '';

  if ($postId <= 0) { throw new Exception('Missing post_id', 400); }
  if ($token === '') { throw new Exception('Missing visitor token', 400); }
  if ($email === '') { throw new Exception('Please subscribe to like this post.', 403); }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Invalid email address provided', 400);
  }

  $subscriberStmt = $pdo->prepare('SELECT status FROM newsletter_subscribers WHERE email = ? LIMIT 1');
  $subscriberStmt->execute([$email]);
  $subscriber = $subscriberStmt->fetch(PDO::FETCH_ASSOC);
  if (!$subscriber || strtolower($subscriber['status'] ?? '') !== 'active') {
    throw new Exception('Only active subscribers can like posts. Please subscribe first.', 403);
  }

  $pdo->beginTransaction();

  $stmt = $pdo->prepare('SELECT id FROM blog_likes WHERE post_id = ? AND email = ? LIMIT 1');
  $stmt->execute([$postId, $email]);
  $existing = $stmt->fetchColumn();

  if ($existing) {
    $pdo->prepare('DELETE FROM blog_likes WHERE id = ?')->execute([$existing]);
    $pdo->prepare('UPDATE blog_posts SET likes = GREATEST(likes - 1, 0) WHERE id = ?')->execute([$postId]);
    $liked = false;
  } else {
    // clean up any stale token-based like for this visitor
    $cleanup = $pdo->prepare('DELETE FROM blog_likes WHERE post_id = ? AND visitor_token = ?');
    $cleanup->execute([$postId, $token]);

    $insert = $pdo->prepare('INSERT INTO blog_likes (post_id, visitor_token, email) VALUES (?, ?, ?)');
    $insert->execute([$postId, $token, $email]);
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

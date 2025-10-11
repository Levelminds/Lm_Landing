<?php
// api/like.php — toggles like for (post_id, visitor_token).
// Anonymous likes: no email required; dedup by device token.
// Accepts JSON: { post_id: number, visitor_token: string }  (email optional/ignored)

header('Content-Type: application/json');

$dsn  = 'mysql:host=localhost;dbname=u420143207_LM_landing;charset=utf8mb4';
$user = 'u420143207_lmlanding';
$pass = 'Levelminds@2024';

try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  // Ensure table supports visitor_token (and migrate if needed)
  $pdo->exec("CREATE TABLE IF NOT EXISTS blog_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    visitor_token VARCHAR(64) NOT NULL,
    email VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_like (post_id, visitor_token),
    KEY idx_token (visitor_token),
    KEY idx_email (email),
    CONSTRAINT fk_bl_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  // Lightweight migration if table previously lacked visitor_token
  $cols = $pdo->query('SHOW COLUMNS FROM blog_likes')->fetchAll(PDO::FETCH_COLUMN);
  if ($cols && !in_array('visitor_token', $cols, true)) {
    $pdo->exec("ALTER TABLE blog_likes ADD COLUMN visitor_token VARCHAR(64) NOT NULL AFTER post_id");
    // Populate tokens from email if present (stable mapping)
    $pdo->exec("UPDATE blog_likes SET visitor_token = SHA2(CONCAT(post_id, ':', COALESCE(email,'')), 256)");
    // Adjust indexes
    $pdo->exec("ALTER TABLE blog_likes DROP INDEX uniq_like");
    $pdo->exec("ALTER TABLE blog_likes ADD UNIQUE KEY uniq_like (post_id, visitor_token)");
    if (!in_array('idx_token', $cols, true)) {
      $pdo->exec("ALTER TABLE blog_likes ADD KEY idx_token (visitor_token)");
    }
  }

  $input = json_decode(file_get_contents('php://input'), true) ?: [];
  $postId = (int)($input['post_id'] ?? 0);
  $token  = trim((string)($input['visitor_token'] ?? ''));

  if ($postId <= 0 || $token === '') {
    throw new Exception('Missing post_id or visitor_token', 400);
  }

  $pdo->beginTransaction();

  // Toggle like
  $sel = $pdo->prepare('SELECT id FROM blog_likes WHERE post_id = ? AND visitor_token = ?');
  $sel->execute([$postId, $token]);
  $rowId = $sel->fetchColumn();

  if ($rowId) {
    $pdo->prepare('DELETE FROM blog_likes WHERE id = ?')->execute([$rowId]);
    $pdo->prepare('UPDATE blog_posts SET likes = GREATEST(likes - 1, 0) WHERE id = ?')->execute([$postId]);
    $liked = false;
  } else {
    $ins = $pdo->prepare('INSERT INTO blog_likes (post_id, visitor_token) VALUES (?, ?)');
    $ins->execute([$postId, $token]);
    $pdo->prepare('UPDATE blog_posts SET likes = likes + 1 WHERE id = ?')->execute([$postId]);
    $liked = true;
  }

  $cnt = $pdo->prepare('SELECT likes FROM blog_posts WHERE id = ?');
  $cnt->execute([$postId]);
  $likes = (int)($cnt->fetchColumn() ?: 0);

  $pdo->commit();

  echo json_encode(['ok'=>true, 'liked'=>$liked, 'likes'=>$likes]);
} catch (Exception $e) {
  if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
  $code = $e->getCode();
  http_response_code(($code >= 400 && $code < 600) ? $code : 500);
  echo json_encode(['ok'=>false, 'message'=>$e->getMessage()]);
}

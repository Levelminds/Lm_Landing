<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: blogs-manage.php');
    exit;
}

$message = '';
$error = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header('Location: blogs-manage.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? 'LevelMinds Team');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $mediaType = $_POST['media_type'] ?? 'photo';
        $mediaUrl = trim($_POST['media_url'] ?? '');
        $status = $_POST['status'] ?? 'published';
        $views = max(0, (int)($_POST['views'] ?? $post['views']));
        $likes = max(0, (int)($_POST['likes'] ?? $post['likes']));
        $responses = max(0, (int)($_POST['responses'] ?? $post['responses']));

        if ($title === '' || $summary === '' || $content === '') {
            $error = 'Please fill in the required fields (title, summary, and content).';
        } else {
            if ($mediaType === 'photo' && isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg','jpeg','png','gif','webp'];
                $ext = strtolower(pathinfo($_FILES['media_file']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed, true)) {
                    $error = 'Please upload a JPG, PNG, GIF, or WEBP image.';
                } else {
                    $uploadDir = __DIR__ . '/uploads/blogs/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $filename = uniqid('blog_', true) . '.' . $ext;
                    $destination = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['media_file']['tmp_name'], $destination)) {
                        $mediaUrl = 'uploads/blogs/' . $filename;
                    }
                }
            }

            if (!$error && $mediaUrl === '') {
                $error = 'Provide an image or video URL for this post.';
            }

            if (!$error) {
                $update = $pdo->prepare('UPDATE blog_posts SET title = :title, author = :author, summary = :summary, content = :content, media_type = :media_type, media_url = :media_url, status = :status, views = :views, likes = :likes, responses = :responses, updated_at = NOW() WHERE id = :id');
                $update->execute([
                    'title' => $title,
                    'author' => $author,
                    'summary' => $summary,
                    'content' => $content,
                    'media_type' => $mediaType,
                    'media_url' => $mediaUrl,
                    'status' => $status,
                    'views' => $views,
                    'likes' => $likes,
                    'responses' => $responses,
                    'id' => $id,
                ]);

                $stmt->execute(['id' => $id]);
                $post = $stmt->fetch(PDO::FETCH_ASSOC);
                $message = 'Blog post updated.';
            }
        }
    }
} catch (PDOException $e) {
    $error = 'Database error: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Blog Post | LevelMinds Admin</title>
  <link rel="icon" href="assets/images/logo/logo.svg" type="image/svg+xml">
  <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body { background: #f5f7fb; font-family: 'Public Sans', sans-serif; }
    .admin-shell { max-width: 960px; margin: 0 auto; padding: 32px 16px 96px; }
    .admin-nav { background: #ffffff; border-radius: 18px; box-shadow: 0 15px 45px rgba(15, 46, 91, 0.12); padding: 18px 28px; margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; }
    .admin-nav .brand { display: flex; align-items: center; gap: 12px; color: #0F1D3B; font-weight: 700; text-decoration: none; }
    .admin-nav nav a { margin-left: 18px; text-decoration: none; color: #51617A; font-weight: 500; }
    .admin-nav nav a.active, .admin-nav nav a:hover { color: #3C8DFF; }
    .admin-card { background: #ffffff; border-radius: 18px; box-shadow: 0 20px 60px rgba(15, 46, 91, 0.08); padding: 32px; }
  </style>
</head>
<body>
  <div class="admin-shell">
    <div class="admin-nav">
      <a href="dashboard.php" class="brand">
        <img src="assets/images/logo/logo.svg" alt="LevelMinds" height="40">
        <span>LevelMinds Admin</span>
      </a>
      <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="blogs-manage.php" class="active">Manage Blogs</a>
        <a href="post-blog.php">New Blog</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
      </nav>
    </div>

    <div class="admin-card">
      <h1 class="h4 mb-3" style="font-weight: 700; color: #0F1D3B;">Edit blog post</h1>
      <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <form method="POST" class="row g-4" enctype="multipart/form-data">
        <div class="col-12">
          <label class="form-label fw-semibold">Title *</label>
          <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Author</label>
          <input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($post['author']); ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Blog Type *</label>
          <select name="media_type" class="form-select" required>
            <option value="photo" <?php echo $post['media_type'] === 'photo' ? 'selected' : ''; ?>>Photo Blog</option>
            <option value="video" <?php echo $post['media_type'] === 'video' ? 'selected' : ''; ?>>Video Blog</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Status *</label>
          <select name="status" class="form-select" required>
            <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
            <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Image or Video URL *</label>
          <input type="url" name="media_url" class="form-control" value="<?php echo htmlspecialchars($post['media_url']); ?>" placeholder="https://...">
          <small class="text-muted">Provide a cover image URL for photo blogs or an embeddable video URL (e.g. YouTube embed). You can also upload a new image below.</small>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Upload new image (optional)</label>
          <input type="file" name="media_file" class="form-control" accept="image/*">
          <?php if ($post['media_type'] === 'photo'): ?>
          <small class="text-muted">Current image:</small>
          <div class="mt-2"><img src="<?php echo htmlspecialchars($post['media_url']); ?>" alt="Current" style="max-height: 120px; border-radius: 12px;"></div>
          <?php endif; ?>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Short Summary *</label>
          <textarea name="summary" class="form-control" rows="2" required><?php echo htmlspecialchars($post['summary']); ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Main Content *</label>
          <textarea name="content" class="form-control" rows="8" required><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Views</label>
          <input type="number" name="views" class="form-control" min="0" value="<?php echo (int)$post['views']; ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Likes</label>
          <input type="number" name="likes" class="form-control" min="0" value="<?php echo (int)$post['likes']; ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Responses</label>
          <input type="number" name="responses" class="form-control" min="0" value="<?php echo (int)$post['responses']; ?>">
        </div>
        <div class="col-12 d-flex gap-3">
          <button type="submit" class="btn btn-primary px-4">Save Changes</button>
          <a href="blogs-manage.php" class="btn btn-outline-secondary">Back to list</a>
        </div>
      </form>
    </div>
  </div>

  <div data-global-footer></div>
  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="assets/js/footer.js"></script>
</body>
</html>







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

$message = '';
$error = '';
$posts = [];
$hasCategoryColumn = false;
$audienceLabels = [
    'teachers' => 'For Teachers',
    'schools'  => 'For Schools',
    'general'  => 'General'
];

function blogCategoryColumnExists(PDO $pdo)
{
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'blog_posts' AND COLUMN_NAME = 'category'");
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        try {
            $check = $pdo->query("SHOW COLUMNS FROM blog_posts LIKE 'category'");
            return $check && $check->fetch();
        } catch (PDOException $inner) {
            return false;
        }
    }
}

function ensureBlogCategoryColumn(PDO $pdo)
{
    if (blogCategoryColumnExists($pdo)) {
        return true;
    }

    try {
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN category ENUM('teachers','schools','general') NOT NULL DEFAULT 'general' AFTER media_url, ADD INDEX idx_category (category)");
    } catch (PDOException $e) {
        return false;
    }

    return blogCategoryColumnExists($pdo);
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $deleteId = (int)$_POST['delete_id'];
        $stmt = $pdo->prepare('DELETE FROM blog_posts WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $deleteId]);
        $message = 'Blog post deleted.';
    }

    $hasCategoryColumn = ensureBlogCategoryColumn($pdo);
    $columns = 'id, title, author, media_type, status, created_at, views, likes';
    if ($hasCategoryColumn) {
        $columns .= ', category';
    }

    try {
        $postsStmt = $pdo->query("SELECT $columns FROM blog_posts ORDER BY created_at DESC");
    } catch (PDOException $queryException) {
        if ($hasCategoryColumn) {
            $hasCategoryColumn = false;
            $postsStmt = $pdo->query('SELECT id, title, author, media_type, status, created_at, views, likes FROM blog_posts ORDER BY created_at DESC');
        } else {
            throw $queryException;
        }
    }
    $postsStmt = $pdo->query('SELECT id, title, author, media_type, category, status, created_at, views, likes FROM blog_posts ORDER BY created_at DESC');
    $posts = $postsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Database error: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Blog Posts | LevelMinds Admin</title>
  <link rel="icon" href="assets/images/logo/logo.svg" type="image/svg+xml">
  <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body { background: #f5f7fb; font-family: 'Public Sans', sans-serif; }
    .admin-shell { max-width: 1200px; margin: 0 auto; padding: 32px 16px 96px; }
    .admin-nav { background: #ffffff; border-radius: 18px; box-shadow: 0 15px 45px rgba(15, 46, 91, 0.12); padding: 18px 28px; margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; }
    .admin-nav .brand { display: flex; align-items: center; gap: 12px; color: #0F1D3B; font-weight: 700; text-decoration: none; }
    .admin-nav nav a { margin-left: 18px; text-decoration: none; color: #51617A; font-weight: 500; }
    .admin-nav nav a.active, .admin-nav nav a:hover { color: #3C8DFF; }
    .table thead { background: #0F1D3B; color: #ffffff; }
    .table tbody tr:hover { background: rgba(60, 141, 255, 0.05); }
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

    <h1 class="h4 mb-3" style="font-weight: 700; color: #0F1D3B;">Blog posts</h1>
    <p class="text-muted mb-4">Review, edit, or remove content shown on the LevelMinds blog.</p>

    <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <?php if (empty($posts)): ?>
          <div class="p-4 text-center text-muted">No blog posts yet. Use "New Blog" to create one.</div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table mb-0 align-middle">
            <thead>
              <tr>
                <th scope="col">Title</th>
                <th scope="col">Type</th>
                <th scope="col">Audience</th>
                <th scope="col">Status</th>
                <th scope="col">Views</th>
                <th scope="col">Likes</th>
                <th scope="col">Published</th>
                <th scope="col" class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($posts as $post): ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($post['title']); ?></strong><br>
                  <small class="text-muted">By <?php echo htmlspecialchars($post['author']); ?></small>
                </td>
                <td><span class="badge bg-<?php echo $post['media_type'] === 'video' ? 'info' : 'secondary'; ?>"><?php echo ucfirst($post['media_type']); ?></span></td>
                <td><?php
                  $categoryKey = $hasCategoryColumn ? ($post['category'] ?? 'general') : 'general';
                  $categoryKey = $post['category'] ?? 'general';
                  $label = $audienceLabels[$categoryKey] ?? ucfirst($categoryKey);
                  echo htmlspecialchars($label);
                ?></td>
                <td><span class="badge bg-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>"><?php echo ucfirst($post['status']); ?></span></td>
                <td><?php echo (int)$post['views']; ?></td>
                <td><?php echo (int)$post['likes']; ?></td>
                <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                <td class="text-end">
                  <a href="blog-edit.php?id=<?php echo (int)$post['id']; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Delete this post?');">
                    <input type="hidden" name="delete_id" value="<?php echo (int)$post['id']; ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div data-global-footer></div>
  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="assets/js/footer.js"></script>
</body>
</html>







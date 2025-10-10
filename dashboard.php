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

$totalPosts = $totalViews = $totalLikes = $totalResponses = $videoPosts = $photoPosts = 0;
$recentPosts = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $statsStmt = $pdo->query('SELECT COUNT(*) AS total_posts,
                                     SUM(views) AS total_views,
                                     SUM(likes) AS total_likes,
                                     SUM(responses) AS total_responses,
                                     SUM(media_type = "video") AS video_posts
                              FROM blog_posts');
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $totalPosts = (int)($stats['total_posts'] ?? 0);
    $totalViews = (int)($stats['total_views'] ?? 0);
    $totalLikes = (int)($stats['total_likes'] ?? 0);
    $totalResponses = (int)($stats['total_responses'] ?? 0);
    $videoPosts = (int)($stats['video_posts'] ?? 0);
    $photoPosts = max(0, $totalPosts - $videoPosts);

    $recentStmt = $pdo->query('SELECT id, title, media_type, created_at, views, likes FROM blog_posts ORDER BY created_at DESC LIMIT 6');
    $recentPosts = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Database error: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LevelMinds Admin | Dashboard</title>
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
    .stat-card { background: #ffffff; border-radius: 18px; padding: 24px; box-shadow: 0 18px 55px rgba(15, 46, 91, 0.08); }
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
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="blogs-manage.php">Manage Blogs</a>
        <a href="post-blog.php">New Blog</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
      </nav>
    </div>

    <h1 class="h3 mb-4" style="font-weight: 700; color: #0F1D3B;">Overview</h1>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="text-muted">Total Posts</div>
          <div class="display-6 fw-bold"><?php echo $totalPosts; ?></div>
          <small class="text-muted"><i class="bi bi-image me-1"></i><?php echo $photoPosts; ?> photo &middot; <i class="bi bi-play-circle me-1"></i><?php echo $videoPosts; ?> video</small>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="text-muted">Total Views</div>
          <div class="display-6 fw-bold"><?php echo $totalViews; ?></div>
          <small class="text-muted">Across all published posts</small>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="text-muted">Total Likes</div>
          <div class="display-6 fw-bold"><?php echo $totalLikes; ?></div>
          <small class="text-muted">Audience appreciation</small>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="text-muted">Responses</div>
          <div class="display-6 fw-bold"><?php echo $totalResponses; ?></div>
          <small class="text-muted">Form submissions or comments</small>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0" style="font-weight: 600; color: #0F1D3B;">Latest posts</h2>
        <a href="post-blog.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Blog</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentPosts)): ?>
          <div class="p-4 text-center text-muted">No blog posts yet. Use "New Blog" to create your first update.</div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table mb-0 align-middle">
            <thead>
              <tr>
                <th scope="col">Title</th>
                <th scope="col">Type</th>
                <th scope="col">Views</th>
                <th scope="col">Likes</th>
                <th scope="col">Published</th>
                <th scope="col" class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentPosts as $post): ?>
              <tr>
                <td><?php echo htmlspecialchars($post['title']); ?></td>
                <td><span class="badge bg-<?php echo $post['media_type'] === 'video' ? 'info' : 'secondary'; ?>"><?php echo ucfirst($post['media_type']); ?></span></td>
                <td><?php echo (int)$post['views']; ?></td>
                <td><?php echo (int)$post['likes']; ?></td>
                <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                <td class="text-end">
                  <a href="blog-edit.php?id=<?php echo (int)$post['id']; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                  <form method="POST" action="blogs-manage.php" class="d-inline">
                    <input type="hidden" name="delete_id" value="<?php echo (int)$post['id']; ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this post?');"><i class="bi bi-trash me-1"></i>Delete</button>
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
    <?php endif; ?>
  </div>

  
  <div data-global-footer></div>
  
  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="assets/js/footer.js"></script>
</body>
</html>







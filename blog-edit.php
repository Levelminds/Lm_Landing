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

$html5Flag = defined('ENT_HTML5') ? ENT_HTML5 : ENT_COMPAT;
$substituteFlag = defined('ENT_SUBSTITUTE') ? ENT_SUBSTITUTE : 0;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: blogs-manage.php');
    exit;
}

$message = '';
$error = '';
$hasCategoryColumn = false;
$hasStatusColumn = false;

function blogColumnExists(PDO $pdo, $column)
{
    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'blog_posts' AND COLUMN_NAME = :column"
        );
        $stmt->execute(['column' => $column]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        try {
            $check = $pdo->prepare("SHOW COLUMNS FROM blog_posts LIKE :column");
            $check->execute(['column' => $column]);
            return (bool) $check->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $inner) {
            return false;
        }
    }
}

function blogCategoryColumnExists(PDO $pdo)
{
    return blogColumnExists($pdo, 'category');
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

function adminEscape($value)
{
    global $substituteFlag;
    return htmlspecialchars((string) $value, ENT_QUOTES | $substituteFlag, 'UTF-8');
}

function adminDecode($value)
{
    global $html5Flag;
    return htmlspecialchars_decode((string) $value, ENT_QUOTES | $html5Flag);
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $hasCategoryColumn = ensureBlogCategoryColumn($pdo);
    $hasStatusColumn = blogColumnExists($pdo, 'status');

    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post && !$hasCategoryColumn && !isset($post['category'])) {
        $post['category'] = 'general';
    }
    if ($post && !$hasStatusColumn) {
        $post['status'] = 'published';
    }

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
        $status = $_POST['status'] ?? ($post['status'] ?? 'published');
        $category = $_POST['category'] ?? ($post['category'] ?? 'general');
        $views = max(0, (int)($_POST['views'] ?? $post['views']));
        $likes = max(0, (int)($_POST['likes'] ?? $post['likes']));
        $responses = max(0, (int)($_POST['responses'] ?? $post['responses']));

        if ($hasCategoryColumn) {
            $allowedCategories = ['teachers', 'schools', 'general'];
            if (!in_array($category, $allowedCategories, true)) {
                $category = 'general';
            }
        } else {
            $category = 'general';
        }

        if ($hasStatusColumn) {
            $status = strtolower(trim($status));
            $allowedStatuses = ['published', 'draft'];
            if (!in_array($status, $allowedStatuses, true)) {
                $status = 'published';
            }
        } else {
            $status = 'published';
        }

        if ($title === '' || $summary === '' || $content === '') {
            $error = 'Please fill in the required fields (title, summary, and content).';
        } else {
            if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['media_file']['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Unable to upload file. Please try again.';
                } else {
                    $ext = strtolower(pathinfo($_FILES['media_file']['name'], PATHINFO_EXTENSION));
                    $allowed = $mediaType === 'photo'
                        ? ['jpg','jpeg','png','gif','webp']
                        : ['mp4','mov','m4v','webm','ogv','ogg'];

                    if (!in_array($ext, $allowed, true)) {
                        $error = $mediaType === 'photo'
                            ? 'Please upload a JPG, PNG, GIF, or WEBP image.'
                            : 'Please upload an MP4, MOV, M4V, WEBM, or OGG video.';
                    } else {
                        $uploadDir = __DIR__ . '/uploads/blogs/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $prefix = $mediaType === 'video' ? 'video_' : 'blog_';
                        $filename = uniqid($prefix, true) . '.' . $ext;
                        $destination = $uploadDir . $filename;
                        if (move_uploaded_file($_FILES['media_file']['tmp_name'], $destination)) {
                            $mediaUrl = 'uploads/blogs/' . $filename;
                        } else {
                            $error = 'Unable to upload file. Please try again.';
                        }
                    }
                }
            }

            if (!$error && $mediaUrl === '') {
                $error = 'Provide an image, video link, or upload for this post.';
            }

            if (!$error) {
                $buildUpdate = function (bool $includeCategory, bool $includeStatus) {
                    $fields = [
                        'title' => ':title',
                        'author' => ':author',
                        'summary' => ':summary',
                        'content' => ':content',
                        'media_type' => ':media_type',
                        'media_url' => ':media_url',
                        'views' => ':views',
                        'likes' => ':likes',
                        'responses' => ':responses',
                    ];

                    if ($includeCategory) {
                        $fields['category'] = ':category';
                    }

                    if ($includeStatus) {
                        $fields['status'] = ':status';
                    }

                    $setParts = [];
                    foreach ($fields as $column => $placeholder) {
                        $setParts[] = "$column = $placeholder";
                    }
                    $setParts[] = 'updated_at = NOW()';

                    return 'UPDATE blog_posts SET ' . implode(', ', $setParts) . ' WHERE id = :id';
                };

                $params = [
                    'title' => $title,
                    'author' => $author,
                    'summary' => $summary,
                    'content' => $content,
                    'media_type' => $mediaType,
                    'media_url' => $mediaUrl,
                    'views' => $views,
                    'likes' => $likes,
                    'responses' => $responses,
                    'id' => $id,
                ];

                if ($hasCategoryColumn) {
                    $params['category'] = $category;
                }

                if ($hasStatusColumn) {
                    $params['status'] = $status;
                }

                $sql = $buildUpdate($hasCategoryColumn, $hasStatusColumn);

                try {
                    $update = $pdo->prepare($sql);
                    $update->execute($params);
                } catch (PDOException $updateException) {
                    $shouldRetry = false;

                    if ($hasCategoryColumn && !blogCategoryColumnExists($pdo)) {
                        unset($params['category']);
                        $hasCategoryColumn = false;
                        $shouldRetry = true;
                    }

                    if ($hasStatusColumn && !blogColumnExists($pdo, 'status')) {
                        unset($params['status']);
                        $hasStatusColumn = false;
                        $shouldRetry = true;
                    }

                    if ($shouldRetry) {
                        $sql = $buildUpdate($hasCategoryColumn, $hasStatusColumn);
                        $update = $pdo->prepare($sql);
                        $update->execute($params);
                    } else {
                        throw $updateException;
                    }
                }

                $stmt->execute(['id' => $id]);
                $post = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($post && !$hasCategoryColumn) {
                    $post['category'] = 'general';
                }
                if ($post && !$hasStatusColumn) {
                    $post['status'] = 'published';
                }
                $message = 'Blog post updated.';
            }
        }
    }
} catch (Exception $e) {
    error_log('[blog-edit.php] ' . $e->getMessage());
    $error = 'Database error: ' . adminEscape($e->getMessage());
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
  <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet">
  <style>
    body { background: #f5f7fb; font-family: 'Public Sans', sans-serif; }
    .admin-shell { max-width: 960px; margin: 0 auto; padding: 32px 16px 96px; }
    .admin-nav { background: #ffffff; border-radius: 18px; box-shadow: 0 15px 45px rgba(15, 46, 91, 0.12); padding: 18px 28px; margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; }
    .admin-nav .brand { display: flex; align-items: center; gap: 12px; color: #0F1D3B; font-weight: 700; text-decoration: none; }
    .admin-nav nav a { margin-left: 18px; text-decoration: none; color: #51617A; font-weight: 500; }
    .admin-nav nav a.active, .admin-nav nav a:hover { color: #3C8DFF; }
    .admin-card { background: #ffffff; border-radius: 18px; box-shadow: 0 20px 60px rgba(15, 46, 91, 0.08); padding: 32px; }
    textarea.js-rich-editor { min-height: 180px; }
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
      <?php if ($message): ?><div class="alert alert-success"><?php echo adminEscape($message); ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo adminEscape($error); ?></div><?php endif; ?>

      <form method="POST" class="row g-4" enctype="multipart/form-data" data-blog-form>
        <div class="col-12">
          <label class="form-label fw-semibold">Title *</label>
          <input type="text" name="title" class="form-control" value="<?php echo adminEscape($post['title']); ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Author</label>
          <input type="text" name="author" class="form-control" value="<?php echo adminEscape($post['author']); ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Audience Category *</label>
          <?php $currentCategory = $post['category'] ?? 'general'; ?>
          <?php if ($hasCategoryColumn): ?>
          <select name="category" class="form-select" required>
            <option value="teachers" <?php echo $currentCategory === 'teachers' ? 'selected' : ''; ?>>For Teachers</option>
            <option value="schools" <?php echo $currentCategory === 'schools' ? 'selected' : ''; ?>>For Schools</option>
            <option value="general" <?php echo $currentCategory === 'general' ? 'selected' : ''; ?>>General Insights</option>
          </select>
          <?php else: ?>
          <input type="hidden" name="category" value="general">
          <div class="form-text text-muted">Categories will default to General until the database is updated.</div>
          <?php endif; ?>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Blog Type *</label>
          <select name="media_type" class="form-select" required>
            <option value="photo" <?php echo $post['media_type'] === 'photo' ? 'selected' : ''; ?>>Photo Blog</option>
            <option value="video" <?php echo $post['media_type'] === 'video' ? 'selected' : ''; ?>>Video Blog</option>
          </select>
        </div>
        <?php if ($hasStatusColumn): ?>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Status *</label>
          <select name="status" class="form-select" required>
            <option value="published" <?php echo ($post['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>Published</option>
            <option value="draft" <?php echo ($post['status'] ?? 'published') === 'draft' ? 'selected' : ''; ?>>Draft</option>
          </select>
        </div>
        <?php else: ?>
        <input type="hidden" name="status" value="published">
        <?php endif; ?>
        <div class="col-12">
          <label class="form-label fw-semibold">Image or Video URL *</label>
          <input type="url" name="media_url" class="form-control" value="<?php echo adminEscape($post['media_url']); ?>" placeholder="https://...">
          <small class="text-muted">Provide a hosted image or video URL (e.g. CDN, YouTube). Uploading a file below will override this field.</small>
        </div>
        <div class="col-12" data-media-field>
          <label class="form-label fw-semibold">Upload new media file (optional)</label>
          <div class="d-flex flex-column flex-md-row gap-3 align-items-stretch">
            <div class="flex-grow-1">
              <input type="file" name="media_file" class="form-control" accept="image/*,video/*" data-media-input>
              <small class="text-muted d-block mt-1">Supported formats: JPG, JPEG, PNG, GIF, WEBP, MP4, MOV, M4V, WEBM, OGG.</small>
              <button type="button" class="btn btn-outline-primary btn-sm mt-3 d-none" data-open-crop>
                <i class="bi bi-sliders"></i> Adjust crop &amp; size
              </button>
            </div>
            <div class="flex-shrink-0 w-100" data-media-preview style="max-width: 320px;">
              <?php if ($post['media_type'] === 'photo' && $post['media_url']): ?>
                <img src="<?php echo adminEscape($post['media_url']); ?>" alt="Current image" class="rounded border w-100" style="max-height: 180px; object-fit: cover;">
              <?php elseif ($post['media_type'] === 'video' && $post['media_url']): ?>
                <?php if (filter_var($post['media_url'], FILTER_VALIDATE_URL)): ?>
                  <div class="ratio ratio-16x9 border rounded overflow-hidden">
                    <iframe src="<?php echo adminEscape($post['media_url']); ?>" title="Current video" allowfullscreen></iframe>
                  </div>
                <?php else: ?>
                  <video controls class="rounded border w-100" style="max-height: 180px; object-fit: cover;">
                    <source src="<?php echo adminEscape($post['media_url']); ?>">
                    Your browser does not support the video tag.
                  </video>
                <?php endif; ?>
              <?php else: ?>
                <div class="border rounded d-flex align-items-center justify-content-center text-muted bg-light-subtle p-3" style="min-height: 160px;">
                  <span class="small">No media selected yet.</span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Short Summary *</label>
          <textarea name="summary" class="form-control js-rich-editor" rows="2" placeholder="Write a short summary that appears on the blog card." required><?php echo adminDecode($post['summary'] ?? ''); ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Main Content *</label>
          <textarea name="content" class="form-control js-rich-editor" rows="8" placeholder="Share the full story, add headings, and include helpful links." required><?php echo adminDecode($post['content'] ?? ''); ?></textarea>
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
  <div class="modal fade" id="mediaCropModal" tabindex="-1" aria-labelledby="mediaCropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="mediaCropModalLabel">Fine-tune media</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-4">
            <div class="col-lg-8">
              <div class="border rounded position-relative overflow-hidden bg-light-subtle" style="min-height: 360px;">
                <img src="" alt="Media preview" data-cropper-target class="w-100 h-100" style="object-fit: contain;">
              </div>
            </div>
            <div class="col-lg-4">
              <div class="mb-3">
                <label class="form-label fw-semibold">Aspect ratio</label>
                <select class="form-select" data-aspect-select>
                  <option value="original">Original</option>
                  <option value="16:9">16:9 Landscape</option>
                  <option value="4:3">4:3 Classic</option>
                  <option value="1:1">1:1 Square</option>
                  <option value="9:16">9:16 Portrait</option>
                  <option value="free">Freeform</option>
                </select>
              </div>
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label fw-semibold">Width (px)</label>
                  <input type="number" class="form-control" min="1" data-output-width>
                </div>
                <div class="col-6">
                  <label class="form-label fw-semibold">Height (px)</label>
                  <input type="number" class="form-control" min="1" data-output-height>
                </div>
              </div>
              <div class="alert alert-secondary mt-3 py-2 px-3 small mb-2">
                Original size: <span data-original-size>—</span>
              </div>
              <p class="small text-muted mb-2 d-none" data-processing-hint>Large videos may take a minute to process in your browser.</p>
              <div class="alert alert-danger d-none" data-processing-error></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="me-auto d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm text-primary d-none" role="status" data-processing-indicator>
              <span class="visually-hidden">Processing…</span>
            </div>
            <span class="small text-muted" data-processing-status></span>
          </div>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" data-apply-crop>Apply adjustments</button>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="assets/js/footer.js"></script>
  <script src="https://cdn.tiny.cloud/1/8n6fw6tstamnd3rc1e3gaye4n5f53gfatj9klefklbm7scjm/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.12.6/dist/ffmpeg.min.js"></script>
  <script src="assets/js/admin-media-tools.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof tinymce === 'undefined') {
        return;
      }

      tinymce.init({
        selector: 'textarea.js-rich-editor',
        plugins: 'advlist autolink lists link charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime table help wordcount',
        toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | removeformat | code',
        menubar: false,
        branding: false,
        skin: 'oxide',
        content_css: 'default',
        height: 340,
        resize: true,
        browser_spellcheck: true,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: false,
        setup: function (editor) {
          editor.on('init', function () {
            editor.getContainer().style.transition = 'box-shadow 0.2s ease';
          });
        },
        content_style: "body { font-family: 'Public Sans', sans-serif; font-size: 16px; line-height: 1.6; color: #23324d; }"
      });

      document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
          if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
          }
        });
      });
    });
  </script>
</body>
</html>



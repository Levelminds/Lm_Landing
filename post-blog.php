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

$message = null;
$error = null;
$hasCategoryColumn = null;

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
function ensureBlogCategoryColumn(PDO $pdo)
{
    try {
        $check = $pdo->query("SHOW COLUMNS FROM blog_posts LIKE 'category'");
        if ($check && $check->fetch()) {
            return true;
        }

        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN category ENUM('teachers','schools','general') NOT NULL DEFAULT 'general' AFTER media_url, ADD INDEX idx_category (category)");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

try {
    $pdoSchema = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $hasCategoryColumn = ensureBlogCategoryColumn($pdoSchema);
    $pdoSchema = null;
} catch (PDOException $e) {
    $hasCategoryColumn = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $titleValue = $title;

    $authorInput = trim($_POST['author'] ?? '');
    $authorValue = $authorInput;
    $author = $authorInput !== '' ? $authorInput : 'LevelMinds Team';

    $summary = trim($_POST['summary'] ?? '');
    $summaryValue = $summary;

    $content = trim($_POST['content'] ?? '');
    $contentValue = $content;

    $mediaType = $_POST['media_type'] ?? 'photo';
    $mediaType = $mediaType === 'video' ? 'video' : 'photo';
    $mediaTypeValue = $mediaType;

    $mediaUrl = trim($_POST['media_url'] ?? '');
    $category = $_POST['category'] ?? 'general';
    $views = max(0, (int)($_POST['views'] ?? 0));
    $likes = max(0, (int)($_POST['likes'] ?? 0));
    $responses = max(0, (int)($_POST['responses'] ?? 0));

    $allowedCategories = ['teachers', 'schools', 'general'];
    if (!in_array($category, $allowedCategories, true)) {
        $category = 'general';
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
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);

                $hasCategoryColumn = ensureBlogCategoryColumn($pdo);
                $columns = 'title, author, summary, content, media_type, media_url, views, likes, responses';
                $placeholders = ':title, :author, :summary, :content, :media_type, :media_url, :views, :likes, :responses';
                $params = [
                $stmt = $pdo->prepare('INSERT INTO blog_posts (title, author, summary, content, media_type, media_url, category, views, likes, responses) VALUES (:title, :author, :summary, :content, :media_type, :media_url, :category, :views, :likes, :responses)');
                $stmt->execute([
                    'title' => $title,
                    'author' => $author,
                    'summary' => $summary,
                    'content' => $content,
                    'media_type' => $mediaType,
                    'media_url' => $mediaUrl,
                    'category' => $category,
                    'views' => $views,
                    'likes' => $likes,
                    'responses' => $responses,
                ];
                ]);

                if ($hasCategoryColumn) {
                    $columns = 'title, author, summary, content, media_type, media_url, category, views, likes, responses';
                    $placeholders = ':title, :author, :summary, :content, :media_type, :media_url, :category, :views, :likes, :responses';
                    $params['category'] = $category;
                }

                try {
                    $stmt = $pdo->prepare("INSERT INTO blog_posts ($columns) VALUES ($placeholders)");
                    $stmt->execute($params);
                } catch (PDOException $insertException) {
                    if ($hasCategoryColumn) {
                        $hasCategoryColumn = false;
                        unset($params['category']);
                        $columns = 'title, author, summary, content, media_type, media_url, views, likes, responses';
                        $placeholders = ':title, :author, :summary, :content, :media_type, :media_url, :views, :likes, :responses';
                        $stmt = $pdo->prepare("INSERT INTO blog_posts ($columns) VALUES ($placeholders)");
                        $stmt->execute($params);
                    } else {
                        throw $insertException;
                    }
                }
                $stmt = $pdo->prepare("INSERT INTO blog_posts ($columns) VALUES ($placeholders)");
                $stmt->execute($params);
                $message = 'Blog post saved successfully.';
            } catch (Throwable $e) {
                error_log('[post-blog.php] ' . $e->getMessage());
                $error = 'Database error: ' . adminEscape($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LevelMinds Admin | Post a Blog</title>
  <link rel="icon" href="assets/images/logo/logo.svg" type="image/svg+xml">
  <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet">
  <style>
    body { background-color: #f5f7fb; font-family: 'Public Sans', sans-serif; }
    .admin-shell { max-width: 960px; margin: 0 auto; padding: 32px 16px 96px; }
    .admin-nav { background: #ffffff; border-radius: 18px; box-shadow: 0 15px 45px rgba(15, 46, 91, 0.12); padding: 18px 28px; margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; }
    .admin-nav .brand { display: flex; align-items: center; gap: 12px; color: #0F1D3B; font-weight: 700; text-decoration: none; }
    .admin-nav nav a { margin-left: 18px; text-decoration: none; color: #51617A; font-weight: 500; }
    .admin-nav nav a.active, .admin-nav nav a:hover { color: #3C8DFF; }
    .admin-card { background: #ffffff; border-radius: 18px; box-shadow: 0 20px 60px rgba(15, 46, 91, 0.08); padding: 32px; }
    textarea.js-rich-editor { min-height: 180px; }
    .rich-editor { border: 1px solid #dbe4f3; border-radius: 14px; overflow: hidden; background: #ffffff; box-shadow: inset 0 1px 2px rgba(15, 46, 91, 0.06); }
    .rich-toolbar { display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0.45rem 0.55rem; background: #f0f5ff; border-bottom: 1px solid #dbe4f3; }
    .rich-toolbar button { border: none; background: transparent; color: #405275; border-radius: 8px; width: 2.2rem; height: 2.2rem; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; transition: background 0.2s ease, color 0.2s ease; }
    .rich-toolbar button:focus { outline: none; box-shadow: 0 0 0 2px rgba(60, 141, 255, 0.25); }
    .rich-toolbar button:hover, .rich-toolbar button.is-active { background: rgba(60, 141, 255, 0.12); color: #2a62d5; }
    .rich-toolbar select { border-radius: 8px; border: 1px solid #c7d3e8; padding: 0.25rem 0.5rem; background: #ffffff; color: #2f3f5d; font-size: 0.85rem; }
    .rich-content { min-height: 160px; padding: 0.9rem; font-size: 0.98rem; line-height: 1.6; color: #23324d; }
    .rich-content:focus { outline: none; box-shadow: inset 0 0 0 2px rgba(60, 141, 255, 0.18); }
    .rich-content[data-empty="true"]::before { content: attr(data-placeholder); color: #8ea2c2; pointer-events: none; }
    .rich-content a { color: #2a62d5; text-decoration: underline; }
    .rich-content ul, .rich-content ol { padding-left: 1.25rem; margin-bottom: 0.75rem; }
    textarea.js-rich-editor.js-rich-source-hidden { display: none !important; }
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
        <a href="blogs-manage.php">Manage Blogs</a>
        <a href="post-blog.php" class="active">New Blog</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
      </nav>
    </div>

    <div class="admin-card">
      <h1 class="h4 mb-3" style="font-weight: 700; color: #0F1D3B;">Publish a new blog post</h1>
      <p class="text-muted mb-4">Use this form to add photo or video updates that appear on the LevelMinds blog.</p>

      <?php if ($message): ?><div class="alert alert-success"><?php echo adminEscape($message); ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo adminEscape($error); ?></div><?php endif; ?>

      <form method="POST" class="row g-4" enctype="multipart/form-data" data-blog-form>
        <div class="col-12">
          <label class="form-label fw-semibold">Title *</label>
          <input type="text" name="title" class="form-control" value="<?php echo adminEscape($titleValue); ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Author</label>
          <input type="text" name="author" class="form-control" value="<?php echo adminEscape($authorValue); ?>" placeholder="LevelMinds Team">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Audience Category *</label>
          <?php if ($hasCategoryColumn): ?>
          <select name="category" class="form-select" required>
            <option value="teachers">For Teachers</option>
            <option value="schools">For Schools</option>
            <option value="general" selected>General Insights</option>
          </select>
          <?php else: ?>
          <input type="hidden" name="category" value="general">
          <div class="form-text text-muted">Categories will default to General until the database is updated.</div>
          <?php endif; ?>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Blog Type *</label>
          <select name="media_type" class="form-select" required>
            <option value="photo" <?php echo $mediaTypeValue === 'photo' ? 'selected' : ''; ?>>Photo Blog</option>
            <option value="video" <?php echo $mediaTypeValue === 'video' ? 'selected' : ''; ?>>Video Blog</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Image or Video URL *</label>
          <input type="url" name="media_url" class="form-control" value="<?php echo adminEscape($mediaUrlValue); ?>" placeholder="https://...">
          <small class="text-muted">Provide a hosted image or video URL (e.g. CDN, YouTube). Uploading a file below will override this field.</small>
        </div>
        <div class="col-12" data-media-field>
          <label class="form-label fw-semibold">Upload media file (optional)</label>
          <div class="d-flex flex-column flex-md-row gap-3 align-items-stretch">
            <div class="flex-grow-1">
              <input type="file" name="media_file" class="form-control" accept="image/*,video/*" data-media-input>
              <small class="text-muted d-block mt-1">Supported formats: JPG, JPEG, PNG, GIF, WEBP, MP4, MOV, M4V, WEBM, OGG.</small>
              <button type="button" class="btn btn-outline-primary btn-sm mt-3 d-none" data-open-crop>
                <i class="bi bi-sliders"></i> Adjust crop &amp; size
              </button>
            </div>
            <div class="flex-shrink-0 w-100" data-media-preview style="max-width: 320px;">
              <div class="border rounded d-flex align-items-center justify-content-center text-muted bg-light-subtle p-3" style="min-height: 160px;">
                <span class="small">No media selected yet.</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Short Summary *</label>
          <textarea name="summary" class="form-control js-rich-editor" rows="2" placeholder="Write a short summary that appears on the blog card." required></textarea>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Main Content *</label>
          <textarea name="content" class="form-control js-rich-editor" rows="8" placeholder="Share the full story, add headings, and include helpful links." required></textarea>
          <textarea name="summary" class="form-control js-rich-editor" rows="2" required></textarea>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Main Content *</label>
          <textarea name="content" class="form-control js-rich-editor" rows="8" required></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Initial Views</label>
          <input type="number" name="views" class="form-control" min="0" value="<?php echo (int) $viewsValue; ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Initial Likes</label>
          <input type="number" name="likes" class="form-control" min="0" value="<?php echo (int) $likesValue; ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Initial Responses</label>
          <input type="number" name="responses" class="form-control" min="0" value="<?php echo (int) $responsesValue; ?>">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary btn-lg px-4">Save Blog Post</button>
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
  <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
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
        menubar: false,
        branding: false,
        plugins: 'lists link table image media code fullscreen autoresize',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link table | removeformat | code fullscreen',
        min_height: 220,
        autoresize_bottom_margin: 16,
        convert_urls: false,
        setup: (editor) => {
          editor.on('change keyup setcontent', () => {
            editor.save();
          });
        }
      });

      document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
          if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
          }
        });
      });
    });
  </script>
</body>
</html>

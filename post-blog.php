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

/**
 * Resize an image to a maximum width while maintaining aspect ratio.
 */
function resizeImageToMaxWidth(string $path, int $maxWidth = 1600): void
{
    $info = @getimagesize($path);
    if (!$info) {
        return;
    }

    [$width, $height, $type] = $info;
    if ($width <= $maxWidth) {
        return;
    }

    $ratio = $height / $width;
    $newWidth = $maxWidth;
    $newHeight = (int) round($newWidth * $ratio);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($path);
            $save = static function ($image, $destination) {
                imagejpeg($image, $destination, 85);
            };
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($path);
            $save = static function ($image, $destination) {
                imagepng($image, $destination, 6);
            };
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($path);
            $save = static function ($image, $destination) {
                imagegif($image, $destination);
            };
            break;
        case IMAGETYPE_WEBP:
            if (!function_exists('imagecreatefromwebp')) {
                return;
            }
            $source = imagecreatefromwebp($path);
            $save = static function ($image, $destination) {
                imagewebp($image, $destination, 80);
            };
            break;
        default:
            return;
    }

    if (!$source) {
        return;
    }

    $resized = imagecreatetruecolor($newWidth, $newHeight);

    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
        imagecolortransparent($resized, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
    }

    imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    $save($resized, $path);

    imagedestroy($source);
    imagedestroy($resized);
}

function persistImageBinary(string $binary, string $extension, string $uploadDir, ?string &$error): ?string
{
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        $error = 'Unable to create upload directory.';
        return null;
    }

    $filename = uniqid('blog_', true) . '.' . $extension;
    $destination = $uploadDir . $filename;

    if (file_put_contents($destination, $binary) === false) {
        $error = 'Unable to save uploaded image.';
        return null;
    }

    resizeImageToMaxWidth($destination);

    return 'uploads/blogs/' . $filename;
}

function handleUploadedImage(array $file, array $allowedExtensions, string $uploadDir, ?string &$error): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Unable to upload image. Please try again.';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $ext = $ext === 'jpeg' ? 'jpg' : $ext;

    if (!in_array($ext, $allowedExtensions, true)) {
        $error = 'Please upload a JPG, PNG, GIF, or WEBP image.';
        return null;
    }

    $binary = file_get_contents($file['tmp_name']);
    if ($binary === false) {
        $error = 'Unable to read uploaded image.';
        return null;
    }

    return persistImageBinary($binary, $ext, $uploadDir, $error);
}

function handleCroppedImage(?string $dataUrl, array $allowedExtensions, string $uploadDir, ?string &$error): ?string
{
    if (!$dataUrl) {
        return null;
    }

    if (!preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches)) {
        $error = 'Invalid cropped image data provided.';
        return null;
    }

    $ext = strtolower($matches[1]);
    $ext = $ext === 'jpeg' ? 'jpg' : $ext;

    if (!in_array($ext, $allowedExtensions, true)) {
        $error = 'Unsupported cropped image format.';
        return null;
    }

    $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
    if ($binary === false) {
        $error = 'Failed to decode cropped image.';
        return null;
    }

    return persistImageBinary($binary, $ext, $uploadDir, $error);
}

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? 'LevelMinds Team');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $mediaType = $_POST['media_type'] ?? 'photo';
    $mediaUrl = trim($_POST['media_url'] ?? '');
    $views = max(0, (int)($_POST['views'] ?? 0));
    $likes = max(0, (int)($_POST['likes'] ?? 0));
    $responses = max(0, (int)($_POST['responses'] ?? 0));

    if ($title === '' || $summary === '' || $content === '') {
        $error = 'Please fill in the required fields (title, summary, and content).';
    } else {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $uploadDir = __DIR__ . '/uploads/blogs/';

        if ($mediaType === 'photo') {
            $mediaFromCrop = handleCroppedImage($_POST['cropped_image'] ?? null, $allowed, $uploadDir, $error);
            if ($mediaFromCrop) {
                $mediaUrl = $mediaFromCrop;
            } elseif (!$error && isset($_FILES['media_file']) && $_FILES['media_file']['tmp_name']) {
                $mediaFromUpload = handleUploadedImage($_FILES['media_file'], $allowed, $uploadDir, $error);
                if ($mediaFromUpload) {
                    $mediaUrl = $mediaFromUpload;
                }
            }
        }

        if (!$error && $mediaUrl === '') {
            $error = 'Provide an image or video URL for this post.';
        }

        if (!$error) {
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);

                $stmt = $pdo->prepare('INSERT INTO blog_posts (title, author, summary, content, media_type, media_url, views, likes, responses) VALUES (:title, :author, :summary, :content, :media_type, :media_url, :views, :likes, :responses)');
                $stmt->execute([
                    'title' => $title,
                    'author' => $author,
                    'summary' => $summary,
                    'content' => $content,
                    'media_type' => $mediaType,
                    'media_url' => $mediaUrl,
                    'views' => $views,
                    'likes' => $likes,
                    'responses' => $responses,
                ]);
                $message = 'Blog post saved successfully.';
            } catch (PDOException $e) {
                $error = 'Database error: ' . htmlspecialchars($e->getMessage());
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
  <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" integrity="sha512-6H4Cp8eHZBPqY8XBr2J1xBd+4WNP52ZiAQDA2RjqqWvAvLOewJIHk0n3SM8IcbkNsSn+C/edyy6fEtBmRa92Og==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    body { background-color: #f5f7fb; font-family: 'Public Sans', sans-serif; }
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
        <a href="blogs-manage.php">Manage Blogs</a>
        <a href="post-blog.php" class="active">New Blog</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
      </nav>
    </div>

    <div class="admin-card">
      <h1 class="h4 mb-3" style="font-weight: 700; color: #0F1D3B;">Publish a new blog post</h1>
      <p class="text-muted mb-4">Use this form to add photo or video updates that appear on the LevelMinds blog.</p>

      <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <form method="POST" class="row g-4" enctype="multipart/form-data">
        <div class="col-12">
          <label class="form-label fw-semibold">Title *</label>
          <input type="text" name="title" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Author</label>
          <input type="text" name="author" class="form-control" placeholder="LevelMinds Team">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Blog Type *</label>
          <select name="media_type" class="form-select" required>
            <option value="photo">Photo Blog</option>
            <option value="video">Video Blog</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Image or Video URL *</label>
          <input type="url" name="media_url" class="form-control" placeholder="https://...">
          <small class="text-muted">Provide a cover image URL for photo blogs or an embeddable video URL (YouTube, Vimeo, etc.). If you upload an image below it will override this field.</small>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Upload image (optional for photo blogs)</label>
          <input type="file" name="media_file" class="form-control" accept="image/*" id="mediaFileInput">
          <input type="hidden" name="cropped_image" id="croppedImageInput">
          <div class="mt-3">
            <img id="mediaPreview" src="" alt="Preview" class="img-fluid rounded d-none" style="max-height: 240px; object-fit: cover;">
          </div>
          <small class="text-muted d-block mt-2">Select an image to crop before upload. The image will be resized on the server to a maximum width of 1600px.</small>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Short Summary *</label>
          <textarea name="summary" class="form-control rich-text" rows="2" required></textarea>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Main Content *</label>
          <textarea name="content" class="form-control rich-text" rows="8" required></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Initial Views</label>
          <input type="number" name="views" class="form-control" min="0" value="0">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Initial Likes</label>
          <input type="number" name="likes" class="form-control" min="0" value="0">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Initial Responses</label>
          <input type="number" name="responses" class="form-control" min="0" value="0">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary btn-lg px-4">Save Blog Post</button>
        </div>
      </form>
    </div>
  </div>

  <div data-global-footer></div>
  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Crop image</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="ratio ratio-16x9 bg-light border rounded">
            <img id="cropperImage" src="" alt="Crop preview" style="max-width: 100%; display: block;">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="cropperSaveBtn">Crop &amp; Save</button>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.tiny.cloud/1/8n6fw6tstamnd3rc1e3gaye4n5f53gfatj9klefklbm7scjm/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js" integrity="sha512-t1fUFAmHd7CrFuw+ECopQQ8C1INR+9HXv+x7Z/UBAV/KyeN6uZu76guf4Hx8bgvSFKPS/JU7+i5t9EYI2MQESA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="assets/js/footer.js"></script>
  <script>
    tinymce.init({
      selector: 'textarea.rich-text',
      plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
      toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | removeformat | code fullscreen',
      menubar: false,
      branding: false,
      height: 400,
      convert_urls: false,
      browser_spellcheck: true,
      content_style: 'body { font-family: "Public Sans", sans-serif; font-size: 16px; }'
    });

    (function () {
      const fileInput = document.getElementById('mediaFileInput');
      const preview = document.getElementById('mediaPreview');
      const croppedInput = document.getElementById('croppedImageInput');
      const modalElement = document.getElementById('cropperModal');
      if (!fileInput || !modalElement) {
        return;
      }

      const modal = new bootstrap.Modal(modalElement);
      const cropImage = document.getElementById('cropperImage');
      const saveBtn = document.getElementById('cropperSaveBtn');
      let cropperInstance = null;
      let pendingFileType = '';

      const resetCropper = () => {
        if (cropperInstance) {
          cropperInstance.destroy();
          cropperInstance = null;
        }
        cropImage.src = '';
        pendingFileType = '';
      };

      modalElement.addEventListener('hidden.bs.modal', resetCropper);

      fileInput.addEventListener('change', function () {
        const [file] = this.files;
        croppedInput.value = '';
        if (!file) {
          return;
        }

        if (!file.type.startsWith('image/')) {
          alert('Please choose an image file for cropping.');
          fileInput.value = '';
          return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
          cropImage.src = event.target.result;
          pendingFileType = file.type;
          modal.show();
          setTimeout(() => {
            cropperInstance = new Cropper(cropImage, {
              viewMode: 1,
              autoCropArea: 1,
              responsive: true,
              background: false
            });
          }, 150);
        };
        reader.readAsDataURL(file);
      });

      saveBtn.addEventListener('click', function () {
        if (!cropperInstance) {
          return;
        }

        const canvas = cropperInstance.getCroppedCanvas({
          maxWidth: 1600,
          imageSmoothingEnabled: true,
          imageSmoothingQuality: 'high'
        });

        if (!canvas) {
          alert('Unable to generate a cropped image. Please try again.');
          return;
        }

        canvas.toBlob(function (blob) {
          if (!blob) {
            return;
          }
          const reader = new FileReader();
          reader.onloadend = function () {
            croppedInput.value = reader.result;
            preview.src = reader.result;
            preview.classList.remove('d-none');
            fileInput.value = '';
            modal.hide();
          };
          reader.readAsDataURL(blob);
        }, pendingFileType || 'image/png');
      });
    })();
  </script>
</body>
</html>





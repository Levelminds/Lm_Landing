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

$uploadDir = __DIR__ . '/uploads/blogs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function lm_resize_image_resource($image, int $maxWidth = 1600)
{
    $width = imagesx($image);
    $height = imagesy($image);

    if ($width <= $maxWidth) {
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        return $canvas;
    }

    $ratio = $maxWidth / $width;
    $newWidth = $maxWidth;
    $newHeight = (int) round($height * $ratio);
    $canvas = imagecreatetruecolor($newWidth, $newHeight);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
    imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
    imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    return $canvas;
}

function lm_export_png($image, string $path): bool
{
    imagesavealpha($image, true);
    return imagepng($image, $path, 8);
}

function lm_export_jpeg($image, string $path): bool
{
    $width = imagesx($image);
    $height = imagesy($image);
    $canvas = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $white);
    imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
    $result = imagejpeg($canvas, $path, 90);
    imagedestroy($canvas);
    return $result;
}

function lm_save_image_from_data(string $dataUrl, string $uploadDir, ?string &$error): ?string
{
    $error = null;
    $dataUrl = trim($dataUrl);
    if ($dataUrl === '') {
        return null;
    }
    if (!preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches)) {
        $error = 'Invalid cropped image data.';
        return null;
    }
    $type = strtolower($matches[1]);
    $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
    $data = base64_decode($base64);
    if ($data === false) {
        $error = 'Unable to decode cropped image.';
        return null;
    }
    $image = imagecreatefromstring($data);
    if (!$image) {
        $error = 'Unable to process cropped image.';
        return null;
    }
    $resized = lm_resize_image_resource($image);
    imagedestroy($image);

    $extension = $type === 'png' ? 'png' : 'jpg';
    $filename = uniqid('blog_', true) . '.' . $extension;
    $path = rtrim($uploadDir, '/') . '/' . $filename;
    $saved = $extension === 'png' ? lm_export_png($resized, $path) : lm_export_jpeg($resized, $path);
    imagedestroy($resized);

    if (!$saved) {
        $error = 'Unable to save cropped image.';
        return null;
    }

    return 'uploads/blogs/' . $filename;
}

function lm_save_image_from_upload(array $file, string $uploadDir, ?string &$error): ?string
{
    $error = null;
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $info = @getimagesize($file['tmp_name']);
    if (!$info) {
        $error = 'Please upload a valid image file (JPG, PNG, or WEBP).';
        return null;
    }
    $mime = $info['mime'] ?? '';
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mime, $allowed, true)) {
        $error = 'Please upload a JPG, PNG, WEBP, or GIF image.';
        return null;
    }

    $contents = file_get_contents($file['tmp_name']);
    if ($contents === false) {
        $error = 'Unable to read uploaded image.';
        return null;
    }
    $image = imagecreatefromstring($contents);
    if (!$image) {
        $error = 'Unable to process uploaded image.';
        return null;
    }

    $resized = lm_resize_image_resource($image);
    imagedestroy($image);

    $extension = $mime === 'image/png' ? 'png' : 'jpg';
    $filename = uniqid('blog_', true) . '.' . $extension;
    $path = rtrim($uploadDir, '/') . '/' . $filename;
    $saved = $extension === 'png' ? lm_export_png($resized, $path) : lm_export_jpeg($resized, $path);
    imagedestroy($resized);

    if (!$saved) {
        $error = 'Unable to save uploaded image.';
        return null;
    }

    return 'uploads/blogs/' . $filename;
}

$message = null;
$error = null;

$title = '';
$author = 'LevelMinds Team';
$summary = '';
$content = '';
$mediaType = 'photo';
$mediaUrl = '';
$views = 0;
$likes = 0;
$responses = 0;

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
    }

    $processedMediaUrl = $mediaUrl;
    $imageError = null;

    if (!$error && $mediaType === 'photo') {
        if (!empty($_POST['cropped_image'])) {
            $processedMediaUrl = lm_save_image_from_data($_POST['cropped_image'], $uploadDir, $imageError);
        } elseif (!empty($_FILES['media_file']['name'])) {
            $processedMediaUrl = lm_save_image_from_upload($_FILES['media_file'], $uploadDir, $imageError);
        }
        if ($imageError) {
            $error = $imageError;
        }
    }

    if (!$error && $mediaType === 'photo' && !$processedMediaUrl) {
        $error = 'Please provide an image URL or upload & crop an image.';
    }

    if (!$error && $mediaType === 'video' && $processedMediaUrl === '') {
        $error = 'Please provide an embeddable video URL for video posts.';
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
                'media_url' => $processedMediaUrl,
                'views' => $views,
                'likes' => $likes,
                'responses' => $responses,
            ]);
            $message = 'Blog post saved successfully.';
            $title = '';
            $author = 'LevelMinds Team';
            $summary = '';
            $content = '';
            $mediaType = 'photo';
            $mediaUrl = '';
            $views = 0;
            $likes = 0;
            $responses = 0;
        } catch (PDOException $e) {
            $error = 'Database error: ' . htmlspecialchars($e->getMessage());
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
  <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" integrity="sha512-6bQf9b5vVsQt1zAr72Xd1LSeX776BF3nf6/Dr7guPmyAnbcW2CYwiVdc+GqOR/mdrIW6DCeA44yWiATNPgEe9w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    body { background-color: #f5f7fb; font-family: 'Public Sans', sans-serif; }
    .admin-shell { max-width: 1024px; margin: 0 auto; padding: 32px 16px 96px; }
    .admin-nav { background: #ffffff; border-radius: 18px; box-shadow: 0 15px 45px rgba(15, 46, 91, 0.12); padding: 18px 28px; margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; }
    .admin-nav .brand { display: flex; align-items: center; gap: 12px; color: #0F1D3B; font-weight: 700; text-decoration: none; }
    .admin-nav nav a { margin-left: 18px; text-decoration: none; color: #51617A; font-weight: 500; }
    .admin-nav nav a.active, .admin-nav nav a:hover { color: #3C8DFF; }
    .admin-card { background: #ffffff; border-radius: 18px; box-shadow: 0 20px 60px rgba(15, 46, 91, 0.08); padding: 32px; }
    .image-cropper { border: 1px dashed rgba(15, 46, 91, 0.15); border-radius: 16px; padding: 16px; background: #fafbff; }
    .image-cropper .ratio { border-radius: 12px; background: rgba(15, 46, 91, 0.05); }
    .image-cropper-controls { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 16px; }
    .image-cropper-controls button { border-radius: 999px; padding: 0.45rem 1.4rem; font-weight: 600; }
    .crop-preview-wrapper { border: 1px solid rgba(15, 46, 91, 0.12); border-radius: 16px; padding: 16px; background: #fff; }
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
      <p class="text-muted mb-4">Craft stories with rich formatting, crop hero imagery perfectly, and publish instantly to the LevelMinds blog.</p>

      <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <form method="POST" class="row g-4" enctype="multipart/form-data">
        <div class="col-12">
          <label class="form-label fw-semibold">Title *</label>
          <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Author</label>
          <input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($author); ?>" placeholder="LevelMinds Team">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Blog Type *</label>
          <select name="media_type" class="form-select" required>
            <option value="photo" <?php echo $mediaType === 'photo' ? 'selected' : ''; ?>>Photo Blog</option>
            <option value="video" <?php echo $mediaType === 'video' ? 'selected' : ''; ?>>Video Blog</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Image or Video URL *</label>
          <input type="url" name="media_url" class="form-control" value="<?php echo htmlspecialchars($mediaUrl); ?>" placeholder="https://...">
          <small class="text-muted">Provide a cover image URL for photo blogs or an embeddable video URL (YouTube, Vimeo, etc.). If you crop &amp; upload an image below it will override this field.</small>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Upload image (optional for photo blogs)</label>
          <input type="file" name="media_file" class="form-control" accept="image/*" id="newPostImageInput">
          <div class="image-cropper mt-3">
            <div class="ratio ratio-16x9 overflow-hidden position-relative">
              <img src="" alt="Crop selection" id="newPostCropperImage" class="w-100 h-100 d-none" style="object-fit: cover;">
              <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted" id="newPostCropperPlaceholder">Select an image to start cropping</div>
            </div>
            <div class="image-cropper-controls">
              <button type="button" class="btn btn-outline-secondary btn-sm" id="newPostCropReset" disabled>Reset</button>
              <div class="ms-auto d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" id="newPostCropRotate" disabled><i class="bi bi-arrow-repeat me-1"></i>Rotate</button>
                <button type="button" class="btn btn-primary btn-sm" id="newPostCropApply" disabled>Save cropped image</button>
              </div>
            </div>
          </div>
          <div class="mt-3 crop-preview-wrapper d-none" id="newPostCroppedPreviewWrapper">
            <p class="text-muted small mb-2">Cropped image preview</p>
            <img src="" alt="Cropped preview" id="newPostCroppedPreview" class="img-fluid rounded shadow-sm">
          </div>
          <input type="hidden" name="cropped_image" id="newPostCroppedImageInput">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Short Summary *</label>
          <textarea name="summary" class="form-control tinymce-editor" id="newPostSummary" rows="3" required><?php echo htmlspecialchars($summary); ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Main Content *</label>
          <textarea name="content" class="form-control tinymce-editor" id="newPostContent" rows="10" required><?php echo htmlspecialchars($content); ?></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Initial Views</label>
          <input type="number" name="views" class="form-control" min="0" value="<?php echo (int) $views; ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Initial Likes</label>
          <input type="number" name="likes" class="form-control" min="0" value="<?php echo (int) $likes; ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Initial Responses</label>
          <input type="number" name="responses" class="form-control" min="0" value="<?php echo (int) $responses; ?>">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary btn-lg px-4">Save Blog Post</button>
        </div>
      </form>
    </div>
  </div>

  <div data-global-footer></div>
  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="assets/js/footer.js"></script>
  <script src="https://cdn.tiny.cloud/1/8n6fw6tstamnd3rc1e3gaye4n5f53gfatj9klefklbm7scjm/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js" integrity="sha512-oqY8K6xNcNehJIK/gB1LsYpe3K8QxSNaben7TBPhYu95LjAqS4I2oXeB9lnAaPDXmgZxziWSi0KWs/oUNsGr1A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>
    tinymce.init({
      selector: 'textarea.tinymce-editor',
      plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount autoresize',
      toolbar: 'undo redo | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code removeformat fullscreen',
      menubar: 'file edit view insert format tools table help',
      height: 320,
      branding: false,
      autoresize_bottom_margin: 40,
      convert_urls: false,
      image_caption: true,
      content_style: "body { font-family: 'Public Sans', sans-serif; font-size: 16px; }"
    });

    (function initCropper(config) {
      const fileInput = document.getElementById(config.fileInputId);
      if (!fileInput) return;
      const cropperImage = document.getElementById(config.cropperImageId);
      const placeholder = document.getElementById(config.placeholderId);
      const applyButton = document.getElementById(config.applyButtonId);
      const resetButton = document.getElementById(config.resetButtonId);
      const rotateButton = document.getElementById(config.rotateButtonId);
      const previewWrapper = document.getElementById(config.previewWrapperId);
      const previewImage = document.getElementById(config.previewImageId);
      const hiddenInput = document.getElementById(config.hiddenInputId);

      let cropperInstance = null;

      function destroyCropper() {
        if (cropperInstance) {
          cropperInstance.destroy();
          cropperInstance = null;
        }
      }

      function resetState() {
        destroyCropper();
        cropperImage.classList.add('d-none');
        cropperImage.src = '';
        placeholder.classList.remove('d-none');
        applyButton.disabled = true;
        resetButton.disabled = true;
        rotateButton.disabled = true;
        hiddenInput.value = '';
        if (previewWrapper) {
          previewWrapper.classList.add('d-none');
        }
        if (previewImage) {
          previewImage.src = '';
        }
      }

      resetState();

      fileInput.addEventListener('change', (event) => {
        const [file] = event.target.files || [];
        if (!file) {
          resetState();
          return;
        }
        const reader = new FileReader();
        reader.onload = (loadEvent) => {
          cropperImage.src = loadEvent.target.result;
          cropperImage.classList.remove('d-none');
          placeholder.classList.add('d-none');
          destroyCropper();
          cropperInstance = new Cropper(cropperImage, {
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            responsive: true,
            background: false,
            aspectRatio: 16 / 9,
            ready() {
              applyButton.disabled = false;
              resetButton.disabled = false;
              rotateButton.disabled = false;
            }
          });
        };
        reader.readAsDataURL(file);
      });

      applyButton.addEventListener('click', () => {
        if (!cropperInstance) return;
        const canvas = cropperInstance.getCroppedCanvas({
          maxWidth: 1600,
          imageSmoothingQuality: 'high',
          fillColor: '#ffffff'
        });
        if (!canvas) return;
        canvas.toBlob((blob) => {
          if (!blob) return;
          const reader = new FileReader();
          reader.onloadend = () => {
            hiddenInput.value = reader.result;
            if (previewWrapper && previewImage) {
              previewImage.src = reader.result;
              previewWrapper.classList.remove('d-none');
            }
          };
          reader.readAsDataURL(blob);
        }, 'image/jpeg', 0.92);
      });

      resetButton.addEventListener('click', () => {
        fileInput.value = '';
        resetState();
      });

      rotateButton.addEventListener('click', () => {
        if (cropperInstance) {
          cropperInstance.rotate(90);
        }
      });
    })({
      fileInputId: 'newPostImageInput',
      cropperImageId: 'newPostCropperImage',
      placeholderId: 'newPostCropperPlaceholder',
      applyButtonId: 'newPostCropApply',
      resetButtonId: 'newPostCropReset',
      rotateButtonId: 'newPostCropRotate',
      previewWrapperId: 'newPostCroppedPreviewWrapper',
      previewImageId: 'newPostCroppedPreview',
      hiddenInputId: 'newPostCroppedImageInput'
    });
  </script>
</body>
</html>

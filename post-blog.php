<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: admin.php'); exit; }

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

$msg = $err = '';

function save_image_from_upload_or_base64(array $file, ?string $b64, string $destDir): array {
    // returns ['ok'=>bool,'path'=>string,'error'=>string]
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    // If cropped image provided as base64 (CropperJS)
    if ($b64) {
        if (!preg_match('#^data:image/(png|jpe?g|webp);base64,#i', $b64, $m)) {
            return ['ok'=>false,'path'=>'','error'=>'Invalid cropped image format.'];
        }
        $ext = strtolower($m[1] === 'jpeg' ? 'jpg' : $m[1]);
        $data = base64_decode(preg_replace('#^data:image/[^;]+;base64,#', '', $b64));
        if ($data === false) return ['ok'=>false,'path'=>'','error'=>'Invalid cropped image data.'];
        $name = 'blog_'.uniqid().'.'.$ext;
        $target = rtrim($destDir,'/').'/'.$name;
        if (file_put_contents($target, $data) === false) return ['ok'=>false,'path'=>'','error'=>'Write failed.'];
        image_auto_resize($target, $ext);
        return ['ok'=>true,'path'=>"uploads/blogs/$name",'error'=>''];
    }

    // Otherwise, use the uploaded file
    if (!isset($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok'=>false,'path'=>'','error'=>'No file uploaded.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) return ['ok'=>false,'path'=>'','error'=>'Upload error.'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
        return ['ok'=>false,'path'=>'','error'=>'Unsupported image type.'];
    }
    $name = 'blog_'.uniqid().'.'.$ext;
    $target = rtrim($destDir,'/').'/'.$name;
    if (!move_uploaded_file($file['tmp_name'], $target)) return ['ok'=>false,'path'=>'','error'=>'Failed to move file.'];
    image_auto_resize($target, $ext);
    return ['ok'=>true,'path'=>"uploads/blogs/$name",'error'=>''];
}

function image_auto_resize(string $path, string $ext, int $max=1600): void {
    // simple GD downscale, keeps aspect; saves in-place
    $create = ['jpg'=>'imagecreatefromjpeg','jpeg'=>'imagecreatefromjpeg','png'=>'imagecreatefrompng','gif'=>'imagecreatefromgif','webp'=>'imagecreatefromwebp'];
    $save   = ['jpg'=>'imagejpeg','jpeg'=>'imagejpeg','png'=>'imagepng','gif'=>'imagegif','webp'=>'imagewebp'];
    $ext = strtolower($ext);
    if (!isset($create[$ext], $save[$ext])) return;
    $img = @$create[$ext]($path);
    if (!$img) return;
    $w = imagesx($img); $h = imagesy($img);
    if ($w <= $max && $h <= $max) { imagedestroy($img); return; }
    $ratio = $w/$h;
    if ($w >= $h) { $nw=$max; $nh=(int)round($max/$ratio); } else { $nh=$max; $nw=(int)round($max*$ratio); }
    $dst = imagecreatetruecolor($nw,$nh);
    imagealphablending($dst, false); imagesavealpha($dst, true);
    imagecopyresampled($dst,$img,0,0,0,0,$nw,$nh,$w,$h);
    if ($ext==='jpg' || $ext==='jpeg') $save[$ext]($dst,$path,85);
    else $save[$ext]($dst,$path);
    imagedestroy($img); imagedestroy($dst);
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? 'LevelMinds Team');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $mediaType = $_POST['media_type'] ?? 'photo';
    $mediaUrl = trim($_POST['media_url'] ?? ''); // for video url OR image url if provided
    $status = 'published';
    $category = $_POST['category'] ?? 'general';
    $views = max(0, (int)($_POST['views'] ?? 0));
    $likes = max(0, (int)($_POST['likes'] ?? 0));
    $responses = max(0, (int)($_POST['responses'] ?? 0));
    $croppedB64 = $_POST['cropped_image'] ?? ''; // from CropperJS

    if ($title==='' || $summary==='' || $content==='') {
        $err = 'Please fill in title, summary, and content.';
    }

    // If photo type, prefer uploaded/cropped image
    if (!$err) {
        if ($mediaType === 'photo') {
            $saved = save_image_from_upload_or_base64($_FILES['media_file'] ?? [], $croppedB64, __DIR__ . '/uploads/blogs');
            if ($saved['ok']) {
                $mediaUrl = $saved['path'];
            } elseif ($mediaUrl === '') {
                $err = 'Provide an image (upload or URL).';
            }
        } else {
            if ($mediaUrl === '') $err = 'Provide a video URL.';
        }
    }

    if (!$err) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
            $sql = "INSERT INTO blog_posts
                    (title,author,summary,content,media_type,media_url,category,status,views,likes,responses,created_at)
                    VALUES
                    (:title,:author,:summary,:content,:media_type,:media_url,:category,:status,:views,:likes,:responses,NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title'=>$title, ':author'=>$author, ':summary'=>$summary, ':content'=>$content,
                ':media_type'=>$mediaType, ':media_url'=>$mediaUrl, ':category'=>$category, ':status'=>$status,
                ':views'=>$views, ':likes'=>$likes, ':responses'=>$responses
            ]);
            $msg = 'Blog post saved successfully.';
        } catch (PDOException $e) { $err = 'Database error: '.htmlspecialchars($e->getMessage()); }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>LevelMinds Admin | Post a Blog</title>
  <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- CropperJS (optional cropping) -->
  <link href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
  <style>.cropper-modal{background:rgba(0,0,0,.6)}</style>
</head>
<body class="p-4">
  <div class="container">
    <h1 class="h4 mb-3">Publish a new blog post</h1>
    <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="row g-3">
      <div class="col-12"><label class="form-label">Title *</label><input name="title" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Author</label><input name="author" class="form-control" placeholder="LevelMinds Team"></div>
      <div class="col-md-4">
        <label class="form-label">Audience Category *</label>
        <select name="category" class="form-select">
          <option value="teachers">For Teachers</option>
          <option value="schools">For Schools</option>
          <option value="general" selected>General Insights</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Blog Type *</label>
        <select name="media_type" class="form-select" id="mediaType">
          <option value="photo">Photo Blog</option>
          <option value="video">Video Blog</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Image or Video URL *</label>
        <input name="media_url" class="form-control" placeholder="https://...">
        <small class="text-muted">Upload below will override this for *photo* blogs.</small>
      </div>

      <div class="col-12" id="photoInputs">
        <label class="form-label">Upload image (optional)</label>
        <input type="file" name="media_file" id="imageFile" class="form-control" accept="image/*">
        <input type="hidden" name="cropped_image" id="croppedImage">
        <small class="text-muted">You can crop after selecting a file.</small>

        <!-- Cropping modal -->
        <div class="modal fade" id="cropperModal" tabindex="-1">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header"><h5 class="modal-title">Crop image</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body"><img id="cropperImage" style="max-width:100%"></div>
              <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="applyCrop">Use cropped</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <label class="form-label">Short Summary *</label>
        <textarea name="summary" class="form-control" rows="3" required></textarea>
      </div>
      <div class="col-12">
        <label class="form-label">Main Content *</label>
        <textarea name="content" class="form-control" rows="8" required></textarea>
      </div>

      <div class="col-md-4"><label class="form-label">Initial Views</label><input type="number" name="views" value="0" min="0" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Initial Likes</label><input type="number" name="likes" value="0" min="0" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Initial Responses</label><input type="number" name="responses" value="0" min="0" class="form-control"></div>

      <div class="col-12"><button class="btn btn-primary">Save Blog Post</button></div>
    </form>
  </div>

<script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
(() => {
  const imgFile = document.getElementById('imageFile');
  const modalEl = document.getElementById('cropperModal');
  const cropImg = document.getElementById('cropperImage');
  const cropped = document.getElementById('croppedImage');
  let cropper;
  imgFile.addEventListener('change', e => {
    const f = e.target.files[0]; if (!f) return;
    const url = URL.createObjectURL(f);
    cropImg.src = url;
    const m = new bootstrap.Modal(modalEl); m.show();
    modalEl.addEventListener('shown.bs.modal', () => {
      cropper = new Cropper(cropImg, {aspectRatio: 16/9, viewMode: 1});
    }, {once:true});
    modalEl.addEventListener('hidden.bs.modal', () => { cropper?.destroy(); cropper=null; URL.revokeObjectURL(url); }, {once:true});
  });
  document.getElementById('applyCrop').addEventListener('click', () => {
    if (!cropper) return;
    const cnv = cropper.getCroppedCanvas({maxWidth:1600,maxHeight:1600, imageSmoothingQuality:'high'});
    cropped.value = cnv.toDataURL('image/jpeg', 0.9);
    bootstrap.Modal.getInstance(modalEl).hide();
  });
})();
</script>
</body>
</html>

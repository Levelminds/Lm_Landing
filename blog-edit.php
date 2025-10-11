<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: admin.php'); exit; }

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: blogs-manage.php'); exit; }

$msg = $err = ''; $post = [];

function image_auto_resize(string $path, string $ext, int $max=1600): void {
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
function save_image_from_upload_or_base64(array $file, ?string $b64, string $destDir): array {
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    if ($b64) {
        if (!preg_match('#^data:image/(png|jpe?g|webp);base64,#i', $b64, $m)) return ['ok'=>false,'path'=>'','error'=>'Invalid cropped image format.'];
        $ext = strtolower($m[1] === 'jpeg' ? 'jpg' : $m[1]);
        $data = base64_decode(preg_replace('#^data:image/[^;]+;base64,#','',$b64));
        if ($data === false) return ['ok'=>false,'path'=>'','error'=>'Invalid cropped image data.'];
        $name = 'blog_'.uniqid().'.'.$ext; $target = rtrim($destDir,'/').'/'.$name;
        if (file_put_contents($target,$data)===false) return ['ok'=>false,'path'=>'','error'=>'Write failed.'];
        image_auto_resize($target,$ext);
        return ['ok'=>true,'path'=>"uploads/blogs/$name",'error'=>''];
    }
    if (!isset($file['tmp_name']) || $file['error']===UPLOAD_ERR_NO_FILE) return ['ok'=>false,'path'=>'','error'=>'No file uploaded.'];
    if ($file['error']!==UPLOAD_ERR_OK) return ['ok'=>false,'path'=>'','error'=>'Upload error.'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext,['jpg','jpeg','png','gif','webp'],true)) return ['ok'=>false,'path'=>'','error'=>'Unsupported image type.'];
    $name = 'blog_'.uniqid().'.'.$ext; $target = rtrim($destDir,'/').'/'.$name;
    if (!move_uploaded_file($file['tmp_name'],$target)) return ['ok'=>false,'path'=>'','error'=>'Move failed.'];
    image_auto_resize($target,$ext);
    return ['ok'=>true,'path'=>"uploads/blogs/$name",'error'=>''];
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id=:id LIMIT 1');
    $stmt->execute([':id'=>$id]); $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$post) { header('Location: blogs-manage.php'); exit; }

    if ($_SERVER['REQUEST_METHOD']==='POST') {
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? 'LevelMinds Team');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $mediaType = $_POST['media_type'] ?? $post['media_type'];
        $mediaUrl = trim($_POST['media_url'] ?? $post['media_url']);
        $status = $_POST['status'] ?? $post['status'];
        $category = $_POST['category'] ?? ($post['category'] ?? 'general');
        $views = max(0, (int)($_POST['views'] ?? $post['views']));
        $likes = max(0, (int)($_POST['likes'] ?? $post['likes']));
        $responses = max(0, (int)($_POST['responses'] ?? $post['responses']));
        $croppedB64 = $_POST['cropped_image'] ?? '';

        if ($title==='' || $summary==='' || $content==='') $err = 'Please fill in title, summary and content.';

        if (!$err && $mediaType==='photo' && (isset($_FILES['media_file']) && $_FILES['media_file']['error']!==UPLOAD_ERR_NO_FILE || $croppedB64)) {
            $saved = save_image_from_upload_or_base64($_FILES['media_file'] ?? [], $croppedB64, __DIR__.'/uploads/blogs');
            if ($saved['ok']) $mediaUrl = $saved['path']; else $err = $saved['error'];
        }
        if (!$err && $mediaType==='video' && $mediaUrl==='') $err = 'Provide a video URL.';

        if (!$err) {
            $sql = "UPDATE blog_posts SET title=:title,author=:author,summary=:summary,content=:content,
                    media_type=:media_type,media_url=:media_url,category=:category,status=:status,
                    views=:views,likes=:likes,responses=:responses,updated_at=NOW()
                    WHERE id=:id";
            $upd = $pdo->prepare($sql);
            $upd->execute([
                ':title'=>$title, ':author'=>$author, ':summary'=>$summary, ':content'=>$content,
                ':media_type'=>$mediaType, ':media_url'=>$mediaUrl, ':category'=>$category, ':status'=>$status,
                ':views'=>$views, ':likes'=>$likes, ':responses'=>$responses, ':id'=>$id
            ]);
            $msg = 'Blog post updated.';
            // refresh
            $stmt->execute([':id'=>$id]); $post = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) { $err = 'Database error: '.htmlspecialchars($e->getMessage()); }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Blog Post | LevelMinds Admin</title>
  <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
  <h1 class="h4 mb-3">Edit Blog Post</h1>
  <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="row g-3">
    <div class="col-12"><label class="form-label">Title *</label><input name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required></div>
    <div class="col-md-3"><label class="form-label">Author</label><input name="author" class="form-control" value="<?php echo htmlspecialchars($post['author']); ?>"></div>
    <div class="col-md-3"><label class="form-label">Audience *</label>
      <select name="category" class="form-select">
        <option value="teachers" <?php echo ($post['category']==='teachers'?'selected':''); ?>>For Teachers</option>
        <option value="schools" <?php echo ($post['category']==='schools'?'selected':''); ?>>For Schools</option>
        <option value="general" <?php echo ($post['category']==='general'?'selected':''); ?>>General Insights</option>
      </select>
    </div>
    <div class="col-md-3"><label class="form-label">Type *</label>
      <select name="media_type" class="form-select">
        <option value="photo" <?php echo ($post['media_type']==='photo'?'selected':''); ?>>Photo</option>
        <option value="video" <?php echo ($post['media_type']==='video'?'selected':''); ?>>Video</option>
      </select>
    </div>
    <div class="col-md-3"><label class="form-label">Status *</label>
      <select name="status" class="form-select">
        <option value="published" <?php echo ($post['status']==='published'?'selected':''); ?>>Published</option>
        <option value="draft" <?php echo ($post['status']==='draft'?'selected':''); ?>>Draft</option>
      </select>
    </div>

    <div class="col-12"><label class="form-label">Image / Video URL *</label>
      <input name="media_url" class="form-control" value="<?php echo htmlspecialchars($post['media_url']); ?>">
      <small class="text-muted">Upload below overrides for photo blogs.</small>
    </div>

    <div class="col-12">
      <label class="form-label">Upload new image (optional)</label>
      <input type="file" name="media_file" id="imageFile" class="form-control" accept="image/*">
      <input type="hidden" name="cropped_image" id="croppedImage">
      <?php if ($post['media_type']==='photo' && $post['media_url']): ?>
        <div class="mt-2"><img src="<?php echo htmlspecialchars($post['media_url']); ?>" style="max-height:120px;border-radius:12px"></div>
      <?php endif; ?>
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

    <div class="col-12"><label class="form-label">Summary *</label>
      <textarea name="summary" class="form-control" rows="3" required><?php echo $post['summary']; ?></textarea>
    </div>
    <div class="col-12"><label class="form-label">Content *</label>
      <textarea name="content" class="form-control" rows="8" required><?php echo $post['content']; ?></textarea>
    </div>

    <div class="col-md-4"><label class="form-label">Views</label><input type="number" name="views" value="<?php echo (int)$post['views']; ?>" min="0" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Likes</label><input type="number" name="likes" value="<?php echo (int)$post['likes']; ?>" min="0" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Responses</label><input type="number" name="responses" value="<?php echo (int)$post['responses']; ?>" min="0" class="form-control"></div>

    <div class="col-12"><button class="btn btn-primary">Save Changes</button>
      <a class="btn btn-outline-secondary" href="blogs-manage.php">Back</a>
    </div>
  </form>
</div>

<script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
(() => {
  const f = document.getElementById('imageFile');
  const modalEl = document.getElementById('cropperModal');
  const cropImg = document.getElementById('cropperImage');
  const out = document.getElementById('croppedImage');
  let cropper;
  f.addEventListener('change', e=>{
    const file = e.target.files[0]; if (!file) return;
    const url = URL.createObjectURL(file);
    cropImg.src = url;
    const m = new bootstrap.Modal(modalEl); m.show();
    modalEl.addEventListener('shown.bs.modal', ()=>{ cropper = new Cropper(cropImg,{aspectRatio:16/9, viewMode:1}); }, {once:true});
    modalEl.addEventListener('hidden.bs.modal', ()=>{ cropper?.destroy(); cropper=null; URL.revokeObjectURL(url); }, {once:true});
  });
  document.getElementById('applyCrop').addEventListener('click', ()=>{
    if (!cropper) return;
    const cnv = cropper.getCroppedCanvas({maxWidth:1600,maxHeight:1600,imageSmoothingQuality:'high'});
    out.value = cnv.toDataURL('image/jpeg', 0.9);
    bootstrap.Modal.getInstance(modalEl).hide();
  });
})();
</script>
</body>
</html>

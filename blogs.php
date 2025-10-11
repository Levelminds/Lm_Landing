<?php
/* blogs.php — public blog listing + modal
 * - Fixes previous HTTP 500 (duplicate funcs / missing braces)
 * - Decodes HTML correctly inside modal (no &ldquo;/<p> artifacts)
 * - Adds Share (Web Share API + clipboard fallback)
 * - Hooks Likes (requires email cookie)
 */

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

$posts = [];
$error = '';

function formatBlogDate($value) {
    if (empty($value)) return '—';
    try { $d = new DateTime($value); return $d->format('M j, Y'); } catch (Exception $e) { return '—'; }
}
function decodePlain($v) {
    if ($v === null || $v === '') return '';
    return trim(html_entity_decode(strip_tags((string)$v), ENT_QUOTES|ENT_HTML5, 'UTF-8'));
}
function b64($v) {
    return $v ? base64_encode((string)$v) : '';
}
function buildShareUrl($postId) {
    $postId = (int)$postId;
    $host = $_SERVER['HTTP_HOST'] ?? 'levelminds.in';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $postId ? sprintf('%s://%s/blogs.php?post=%d', $scheme, $host, $postId) : '';
}

$audienceLabels = ['teachers'=>'For Teachers','schools'=>'For Schools','general'=>'General Insights'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    // keep it simple: assume column exists now
    $stmt = $pdo->query("SELECT id,title,author,summary,content,media_type,media_url,category,status,created_at,views,likes
                         FROM blog_posts
                         WHERE status='published'
                         ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $error = 'Unable to load blog posts right now.'; }

$featured = $posts[0] ?? null;

// group by category except featured
$grouped = [];
if ($posts) {
    foreach ($posts as $idx=>$p) {
        if ($idx === 0) continue;
        $cat = $p['category'] ?? 'general';
        $grouped[$cat][] = $p;
    }
}
$orderedCats = array_values(array_intersect(['teachers','schools','general'], array_keys($grouped)));
foreach (array_keys($grouped) as $cat) {
    if (!in_array($cat, $orderedCats, true)) $orderedCats[] = $cat;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>LevelMinds Blog | Hiring Insights for Schools & Teachers</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/overrides.css" rel="stylesheet">
  <style>
    .blog-card{border-radius:22px;background:#fff;box-shadow:0 22px 60px rgba(15,29,59,.10);height:100%;overflow:hidden}
    .blog-card__media img,.blog-card__media video,.blog-card__media iframe{width:100%;height:220px;object-fit:cover}
    .blog-card__body{padding:1.8rem}
    .blog-card__footer{padding:0 1.8rem 1.8rem;display:flex;justify-content:space-between;align-items:center}
    .btn-like{border:none;background:transparent;display:inline-flex;gap:.35rem;align-items:center;cursor:pointer}
    .blog-modal .modal-content{border:none;border-radius:24px;box-shadow:0 30px 80px rgba(15,29,59,.25)}
    .blog-modal-media img,.blog-modal-media video,.blog-modal-media iframe{width:100%;border-radius:18px}
  </style>
</head>
<body>
<header class="navbar navbar-expand-lg navbar-light fbs__net-navbar">
  <div class="container">
    <a class="navbar-brand" href="index.html"><img src="assets/images/logo/logo.svg" height="40" alt="LevelMinds"></a>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item"><a class="nav-link active" href="blogs.php">Blogs</a></li>
    </ul>
  </div>
</header>

<main style="padding-top:110px">
<section class="py-5">
  <div class="container">
    <?php if ($error): ?>
      <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif (!$featured): ?>
      <div class="text-center text-muted py-5"><i class="bi bi-journal-text display-5 d-block mb-3"></i>No blog posts yet.</div>
    <?php else:
      $fid = (int)$featured['id'];
      $fcat = $featured['category'] ?? 'general';
      $flabel = $audienceLabels[$fcat] ?? ucfirst($fcat);
      $fdate = formatBlogDate($featured['created_at'] ?? null);
      $fviews = (int)$featured['views'];
      $flikes = (int)$featured['likes'];
      $fsumRaw = (string)($featured['summary'] ?? '');
      $fconRaw = (string)($featured['content'] ?? '');
      $fsumPlain = decodePlain($fsumRaw);
      $shareUrl = buildShareUrl($fid);
      $shareSummary = $fsumPlain ?: decodePlain($fconRaw);
    ?>
    <article class="featured-card mb-5" style="border-radius:28px;background:#fff;box-shadow:0 32px 80px rgba(32,139,255,.18)">
      <div class="row g-0 align-items-stretch">
        <div class="col-lg-6">
          <?php if ($featured['media_type']==='video'):
            $ext = preg_match('/\.(mp4|mov|m4v|webm|ogv|ogg)(\?|$)/i', $featured['media_url']);
            if (!$ext && preg_match('/^https?:\/\//i',$featured['media_url'])): ?>
              <div class="ratio ratio-16x9 h-100"><iframe src="<?php echo htmlspecialchars($featured['media_url']); ?>" allowfullscreen></iframe></div>
            <?php else: ?>
              <video class="w-100 h-100" controls style="object-fit:cover"><source src="<?php echo htmlspecialchars($featured['media_url']); ?>"></video>
            <?php endif; else: ?>
              <img class="w-100 h-100" style="object-fit:cover" src="<?php echo htmlspecialchars($featured['media_url']); ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>">
          <?php endif; ?>
        </div>
        <div class="col-lg-6 d-flex align-items-center">
          <div class="p-4 p-lg-5 w-100">
            <span class="badge bg-primary-subtle text-primary mb-2"><?php echo htmlspecialchars($flabel); ?></span>
            <h2 class="mb-2"><?php echo htmlspecialchars($featured['title']); ?></h2>
            <p class="lead text-muted mb-3"><?php echo htmlspecialchars($fsumPlain); ?></p>
            <div class="d-flex flex-wrap gap-3 text-muted mb-3">
              <span><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($featured['author']); ?></span>
              <span><i class="bi bi-calendar-event"></i> <?php echo $fdate; ?></span>
              <span><i class="bi bi-eye"></i> <span data-views-for="<?php echo $fid; ?>"><?php echo number_format($fviews); ?></span> views</span>
            </div>
            <div class="d-flex gap-3">
              <button class="btn btn-primary"
                data-bs-toggle="modal" data-bs-target="#blogModal"
                data-id="<?php echo $fid; ?>"
                data-title="<?php echo htmlspecialchars($featured['title']); ?>"
                data-author="<?php echo htmlspecialchars($featured['author']); ?>"
                data-date="<?php echo $fdate; ?>"
                data-category-label="<?php echo htmlspecialchars($flabel); ?>"
                data-views="<?php echo $fviews; ?>"
                data-likes="<?php echo $flikes; ?>"
                data-summary-b64="<?php echo htmlspecialchars(b64($fsumRaw)); ?>"
                data-content-b64="<?php echo htmlspecialchars(b64($fconRaw)); ?>"
                data-media-type="<?php echo htmlspecialchars($featured['media_type']); ?>"
                data-media-url="<?php echo htmlspecialchars($featured['media_url']); ?>"
                data-share-url="<?php echo htmlspecialchars($shareUrl); ?>"
                data-share-title="<?php echo htmlspecialchars($featured['title']); ?>"
                data-share-summary-b64="<?php echo htmlspecialchars(b64($shareSummary)); ?>">
                Read full story
              </button>

              <button type="button" class="btn btn-outline-primary" data-share-inline
                data-share-url="<?php echo htmlspecialchars($shareUrl); ?>"
                data-share-title="<?php echo htmlspecialchars($featured['title']); ?>"
                data-share-summary="<?php echo htmlspecialchars($shareSummary); ?>">
                <i class="bi bi-share"></i>
              </button>

              <button class="btn-like" type="button" data-like-btn data-post-id="<?php echo $fid; ?>">
                <i class="bi bi-heart"></i> <span data-like-count><?php echo number_format($flikes); ?></span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </article>
    <?php endif; ?>
  </div>
</section>

<?php if ($featured && $grouped): ?>
<section class="py-5">
  <div class="container">
    <div class="d-flex gap-2 mb-3 flex-wrap">
      <button class="btn btn-primary btn-sm" data-filter="all">Latest articles</button>
      <?php foreach ($orderedCats as $cat): $label = $audienceLabels[$cat] ?? ucfirst($cat); ?>
        <button class="btn btn-outline-primary btn-sm" data-filter="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($label); ?></button>
      <?php endforeach; ?>
    </div>

    <div class="row g-4" id="blogCards">
      <?php foreach ($orderedCats as $cat): $label = $audienceLabels[$cat] ?? ucfirst($cat); ?>
        <?php foreach ($grouped[$cat] as $p):
          $pid=(int)$p['id']; $pdate=formatBlogDate($p['created_at']??null);
          $pviews=(int)$p['views']; $plikes=(int)$p['likes'];
          $sumRaw=(string)($p['summary']??''); $contentRaw=(string)($p['content']??'');
          $sumPlain=decodePlain($sumRaw);
          $shareUrl=buildShareUrl($pid);
          $shareSummary=$sumPlain ?: decodePlain($contentRaw);
        ?>
        <div class="col-xl-4 col-md-6" data-blog-card data-category="<?php echo htmlspecialchars($cat); ?>">
          <article class="blog-card h-100 d-flex flex-column">
            <div class="blog-card__media">
              <?php if ($p['media_type']==='video'):
                $ext = preg_match('/\.(mp4|mov|m4v|webm|ogv|ogg)(\?|$)/i', $p['media_url']);
                if (!$ext && preg_match('/^https?:\/\//i',$p['media_url'])): ?>
                  <div class="ratio ratio-16x9"><iframe src="<?php echo htmlspecialchars($p['media_url']); ?>" allowfullscreen></iframe></div>
                <?php else: ?>
                  <video class="w-100" controls style="object-fit:cover;height:220px"><source src="<?php echo htmlspecialchars($p['media_url']); ?>"></video>
                <?php endif; else: ?>
                  <img src="<?php echo htmlspecialchars($p['media_url']); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
              <?php endif; ?>
            </div>
            <div class="blog-card__body">
              <span class="text-primary fw-bold small text-uppercase"><?php echo htmlspecialchars($label); ?></span>
              <h3 class="h5 mb-2"><?php echo htmlspecialchars($p['title']); ?></h3>
              <p class="text-muted mb-2"><?php echo htmlspecialchars($sumPlain); ?></p>
              <div class="small text-muted d-flex gap-3">
                <span><i class="bi bi-person"></i> <?php echo htmlspecialchars($p['author']); ?></span>
                <span><i class="bi bi-calendar-event"></i> <?php echo $pdate; ?></span>
              </div>
            </div>
            <div class="blog-card__footer">
              <span class="text-muted"><i class="bi bi-eye"></i> <span data-views-for="<?php echo $pid; ?>"><?php echo number_format($pviews); ?></span></span>
              <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-primary btn-sm"
                  data-bs-toggle="modal" data-bs-target="#blogModal"
                  data-id="<?php echo $pid; ?>"
                  data-title="<?php echo htmlspecialchars($p['title']); ?>"
                  data-author="<?php echo htmlspecialchars($p['author']); ?>"
                  data-date="<?php echo $pdate; ?>"
                  data-category-label="<?php echo htmlspecialchars($label); ?>"
                  data-views="<?php echo $pviews; ?>"
                  data-likes="<?php echo $plikes; ?>"
                  data-summary-b64="<?php echo htmlspecialchars(b64($sumRaw)); ?>"
                  data-content-b64="<?php echo htmlspecialchars(b64($contentRaw)); ?>"
                  data-media-type="<?php echo htmlspecialchars($p['media_type']); ?>"
                  data-media-url="<?php echo htmlspecialchars($p['media_url']); ?>"
                  data-share-url="<?php echo htmlspecialchars($shareUrl); ?>"
                  data-share-title="<?php echo htmlspecialchars($p['title']); ?>"
                  data-share-summary-b64="<?php echo htmlspecialchars(b64($shareSummary)); ?>">
                  Read
                </button>
                <button class="btn btn-outline-primary btn-sm" data-share-inline
                  data-share-url="<?php echo htmlspecialchars($shareUrl); ?>"
                  data-share-title="<?php echo htmlspecialchars($p['title']); ?>"
                  data-share-summary="<?php echo htmlspecialchars($shareSummary); ?>">
                  <i class="bi bi-share"></i>
                </button>
                <button class="btn-like" type="button" data-like-btn data-post-id="<?php echo $pid; ?>">
                  <i class="bi bi-heart"></i> <span data-like-count><?php echo number_format($plikes); ?></span>
                </button>
              </div>
            </div>
          </article>
        </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>

<!-- Modal -->
<div class="modal fade blog-modal" id="blogModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <span class="badge bg-primary-subtle text-primary" data-modal-category></span>
          <h2 class="h4 mt-2 mb-0" data-post-title>Blog post</h2>
        </div>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="blog-modal-media mb-4" data-post-media></div>
        <div class="d-flex align-items-center gap-3 text-muted mb-3">
          <span><i class="bi bi-person-circle"></i> <span data-post-author></span></span>
          <span><i class="bi bi-calendar-event"></i> <span data-post-date></span></span>
          <span><i class="bi bi-eye"></i> <span data-post-views>0</span> views</span>
          <div class="ms-auto d-flex align-items-center gap-2">
            <button class="btn btn-outline-primary btn-sm" data-modal-share><i class="bi bi-share"></i></button>
            <button class="btn-like" type="button" data-like-btn data-modal-like data-post-id=""><i class="bi bi-heart"></i> <span data-like-count>0</span></button>
          </div>
        </div>
        <p class="lead text-muted" data-post-summary></p>
        <div data-post-content class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

<script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
<script>
(() => {
  const $ = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));
  const b64dec = v => { try { return v ? atob(v) : ''; } catch(e) { return ''; } };
  const fmt = n => Number.isFinite(+n) ? (+n).toLocaleString() : n;

  // Filter
  $$( '[data-filter]' ).forEach(btn => {
    btn.addEventListener('click', () => {
      $$( '[data-filter]' ).forEach(b=>b.classList.toggle('btn-primary', false));
      $$( '[data-filter]' ).forEach(b=>b.classList.toggle('btn-outline-primary', true));
      btn.classList.toggle('btn-primary', true);
      btn.classList.toggle('btn-outline-primary', false);
      const f = btn.dataset.filter;
      $$( '[data-blog-card]' ).forEach(card => {
        const c = card.dataset.category;
        card.style.display = (f==='all' || c===f) ? '' : 'none';
      });
    });
  });

  // Share
  function share(url, title, text) {
    if (navigator.share) return navigator.share({title, text, url}).catch(()=>{});
    navigator.clipboard?.writeText(url);
    alert('Link copied to clipboard.');
  }
  $$( '[data-share-inline]' ).forEach(b => {
    b.addEventListener('click', () => share(b.dataset.shareUrl, b.dataset.shareTitle, b.dataset.shareSummary));
  });

  // Modal
  const modalEl = $('#blogModal');
  const bsModal = new bootstrap.Modal(modalEl);
  function renderMedia(container, type, url, title) {
    container.innerHTML = '';
    if (!url) return;
    const isExternal = /^https?:\/\//i.test(url);
    if (type === 'video') {
      if (isExternal && !/\.(mp4|mov|m4v|webm|ogv|ogg)(\?|$)/i.test(url)) {
        const iframe = document.createElement('iframe');
        iframe.src = url; iframe.allowFullscreen = true; iframe.className='w-100 rounded'; iframe.style.minHeight='360px';
        container.appendChild(iframe);
      } else {
        const video = document.createElement('video');
        video.controls = true; video.className='w-100 rounded';
        const src = document.createElement('source'); src.src = url; video.appendChild(src);
        container.appendChild(video);
      }
    } else {
      const img = document.createElement('img');
      img.src = url; img.alt = title || 'Blog media'; img.className='img-fluid rounded';
      container.appendChild(img);
    }
  }

  modalEl.addEventListener('show.bs.modal', ev => {
    const t = ev.relatedTarget; if (!t) return;
    const ds = t.dataset;
    const id = ds.id||'';
    const title = ds.title||''; const author = ds.author||''; const date = ds.date||'';
    const cat = ds.categoryLabel||''; const views = parseInt(ds.views||'0',10);
    const likes = parseInt(ds.likes||'0',10);
    const summary = b64dec(ds.summaryB64||'');
    const content = b64dec(ds.contentB64||'');
    const mtype = (ds.mediaType||'').toLowerCase(); const murl = ds.mediaUrl||'';
    const shareUrl = ds.shareUrl||''; const shareSummary = b64dec(ds.shareSummaryB64||'') || summary;

    modalEl.dataset.postId = id;
    $('[data-post-title]', modalEl).textContent = title;
    $('[data-post-author]', modalEl).textContent = author || 'LevelMinds Team';
    $('[data-post-date]', modalEl).textContent = date;
    $('[data-post-views]', modalEl).textContent = fmt(views);
    $('[data-modal-category]', modalEl).textContent = cat;
    $('[data-post-summary]', modalEl).textContent = summary;
    $('[data-post-content]', modalEl).innerHTML = content;
    const likeBtn = $('[data-modal-like]', modalEl);
    if (likeBtn) { likeBtn.setAttribute('data-post-id', id); $('[data-like-count]', likeBtn).textContent = fmt(likes); }
    $('[data-modal-share]', modalEl).onclick = () => share(shareUrl, title, shareSummary);
    renderMedia($('[data-post-media]', modalEl), mtype, murl, title);

    // track views
    fetch('api/view.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({post_id: id})})
      .then(r=>r.json()).then(({ok,views:latest})=>{
        if (ok && Number.isFinite(+latest)) {
          $('[data-post-views]', modalEl).textContent = fmt(latest);
          $$(`[data-views-for="${id}"]`).forEach(el => el.textContent = fmt(latest));
        }
      }).catch(()=>{});
  });

  // Likes (requires email cookie)
  function getEmail() {
    const m = document.cookie.match(/(?:^|;)\s*lm_email=([^;]+)/);
    return m ? decodeURIComponent(m[1]) : '';
  }
  async function ensureEmail() {
    let email = getEmail();
    if (!email) {
      email = prompt('Enter your email to like (subscribers only):') || '';
      email = email.trim();
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return '';
      document.cookie = 'lm_email='+encodeURIComponent(email)+';path=/;max-age='+(3600*24*365);
    }
    return email;
  }
  async function toggleLike(btn) {
    const id = btn.getAttribute('data-post-id');
    const email = await ensureEmail(); if (!email) return;
    const r = await fetch('api/like.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({post_id:id,email})});
    const json = await r.json().catch(()=>({}));
    if (json && json.ok) {
      $$(`[data-like-btn][data-post-id="${id}"]`).forEach(b => {
        const el = b.querySelector('[data-like-count]'); if (el) el.textContent = fmt(json.likes);
      });
    } else {
      alert(json?.message || 'Unable to like right now.');
    }
  }
  $$( '[data-like-btn]' ).forEach(b => b.addEventListener('click', () => toggleLike(b)));

  // Open modal by URL ?post=ID
  const params = new URLSearchParams(location.search);
  const initial = params.get('post');
  if (initial) {
    const opener = document.querySelector(`[data-bs-target="#blogModal"][data-id="${initial}"]`);
    if (opener) setTimeout(()=>opener.click(), 300);
  }
})();
</script>
</body>
</html>

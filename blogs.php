<?php
$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

$posts = [];
$error = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $stmt = $pdo->query("SELECT id, title, author, summary, content, media_type, media_url, created_at, views, likes FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Unable to load blog posts right now.';
}

$featured = $posts ? array_shift($posts) : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LevelMinds Blog | Hiring Insights for Schools & Teachers</title>
  <meta name="description" content="Explore insights, playbooks, and stories to help schools hire the right educators and help teachers grow careers they love.">
  <link rel="icon" href="assets/images/logo/logo.svg" type="image/svg+xml">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/logo/logo.svg">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/logo/logo.svg">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/images/logo/logo.svg">
  <link rel="manifest" href="assets/images/logo/logo.svg">
  <meta name="msapplication-TileImage" content="assets/images/logo/logo.svg">
  <meta name="msapplication-TileColor" content="#ffffff">

  <link rel="canonical" href="https://LevelMinds.com/blogs.php">
  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[
    {"@type":"ListItem","position":1,"name":"Home","item":"https://LevelMinds.com/"},
    {"@type":"ListItem","position":2,"name":"Blog","item":"https://LevelMinds.com/blogs.php"}
  ]}
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  \1
  <link href="assets/css/overrides.css" rel="stylesheet"><style>
    body { font-family: 'Public Sans', sans-serif; background-color: #F5FAFF; color: #1B2A4B; }
    .fbs__net-navbar { position: fixed; width: 100%; top: 0; left: 0; z-index: 1030; background-color: #FFFFFF !important; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); }
    .fbs__net-navbar .nav-link { color: #1B2A4B !important; }
    .fbs__net-navbar .nav-link:hover,
    .fbs__net-navbar .nav-link.active { color: #3C8DFF !important; }
    .blog-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .blog-card:hover { transform: translateY(-8px); box-shadow: 0 18px 45px rgba(32,139,255,0.15) !important; }
    .featured-card { border-radius: 24px; overflow: hidden; box-shadow: 0 24px 65px rgba(32,139,255,0.18); background: #ffffff; }
  </style>
</head>

<body>
    <header class="fbs__net-navbar navbar navbar-expand-lg navbar-light" aria-label="LevelMinds navbar">
      <div class="container d-flex align-items-center justify-content-between">
        <a class="navbar-brand w-auto" href="index.html">
          <img src="assets/images/logo/logo.svg" alt="LevelMinds" class="logo" height="40">
        </a>

        <div class="offcanvas offcanvas-start w-75" id="fbs__net-navbars" tabindex="-1" aria-labelledby="fbs__net-navbarsLabel">
          <div class="offcanvas-header">
            <div class="offcanvas-header-logo">
              <a class="logo-link" id="fbs__net-navbarsLabel" href="index.html">
                <img src="assets/images/logo/logo.svg" alt="LevelMinds" class="logo" height="35">
              </a>
            </div>
            <button class="btn-close text-reset" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>

          <div class="offcanvas-body align-items-lg-center">
            <ul class="navbar-nav nav me-auto ps-lg-5 mb-2 mb-lg-0">
              <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
              <li class="nav-item"><a class="nav-link" href="team.html">Team</a></li>
              <li class="nav-item"><a class="nav-link" href="tour.html">Tour</a></li>
              <li class="nav-item"><a class="nav-link active" href="blogs.php" aria-current="page">Blogs</a></li>
              <li class="nav-item"><a class="nav-link" href="career.html">Careers</a></li>
              <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>
            </ul>
            <div class="d-lg-none mt-4 w-100">
              <a class="btn btn-nav-outline w-100" href="https://www.lmap.in" target="_blank" rel="noopener">Login / Sign Up</a>
            </div>
          </div>
        </div>

        <div class="d-flex align-items-center gap-3">
          <div class="header-actions d-none d-lg-flex align-items-center gap-2">
            <a class="btn btn-nav-outline" href="https://www.lmap.in" target="_blank" rel="noopener">Login / Sign Up</a>
          </div>
          <button class="fbs__net-navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#fbs__net-navbars" aria-controls="fbs__net-navbars" aria-label="Toggle navigation">
            <svg class="fbs__net-icon-menu" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="21" x2="3" y1="6" y2="6"></line>
              <line x1="21" x2="3" y1="12" y2="12"></line>
              <line x1="21" x2="3" y1="18" y2="18"></line>
            </svg>
          </button>
        </div>
      </div>
    </header>

  <main style="padding-top: 110px;">
    <section class="hero__v6 section" style="padding: 140px 0 80px;">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
            <h1 class="hero-title mb-4" style="color: #0F1D3B; font-size: 3.5rem; font-weight: 800;">LevelMinds Blog</h1>
            <p class="lead mb-4" style="color: rgba(15,29,59,0.72);">Insights, playbooks, and stories to help schools hire the right educatorsand help teachers grow careers they love.</p>
            <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
              <a href="#latest" class="btn btn-primary btn-lg px-4">Browse Posts</a>
              <a href="#newsletter" class="btn btn-outline-primary btn-lg px-4">Subscribe</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="latest" style="padding: 80px 0;">
      <div class="container">
        <?php if ($error): ?>
          <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (!$featured): ?>
          <div class="text-center text-muted py-5">
            <i class="bi bi-journal-text display-5 d-block mb-3"></i>
            <p class="lead">No blog posts yet. Check back soon.</p>
          </div>
        <?php else: ?>
          <div class="row g-4 mb-5">
            <div class="col-12">
              <div class="featured-card">
                <div class="row g-0">
                  <div class="col-lg-6">
                    <?php if ($featured['media_type'] === 'video'): ?>
                      <?php $isFeaturedExternal = preg_match('/^https?:\/\//i', $featured['media_url']); ?>
                      <?php if ($isFeaturedExternal): ?>
                      <div class="ratio ratio-16x9">
                        <iframe src="<?php echo htmlspecialchars($featured['media_url']); ?>" title="<?php echo htmlspecialchars($featured['title']); ?>" allowfullscreen></iframe>
                      </div>
                      <?php else: ?>
                      <div class="ratio ratio-16x9">
                        <video controls class="w-100 h-100" style="object-fit: cover;">
                          <source src="<?php echo htmlspecialchars($featured['media_url']); ?>">
                          Your browser does not support the video tag.
                        </video>
                      </div>
                      <?php endif; ?>
                    <?php else: ?>
                      <img src="<?php echo htmlspecialchars($featured['media_url']); ?>" class="img-fluid h-100" alt="<?php echo htmlspecialchars($featured['title']); ?>" style="object-fit: cover;">
                    <?php endif; ?>
                  </div>
                  <div class="col-lg-6 d-flex align-items-center">
                    <div class="p-4 p-lg-5">
                      <span class="badge bg-primary-subtle text-primary mb-3">Featured</span>
                      <h2 style="font-weight: 700; color: #0F1D3B;"><?php echo htmlspecialchars($featured['title']); ?></h2>
                      <p class="mt-3" style="color: #51617A;"><?php echo nl2br(htmlspecialchars($featured['summary'])); ?></p>
                      <?php if (!empty($featured['content'])): ?>
                      <div class="mt-3" style="color: #51617A;"><?php echo nl2br(htmlspecialchars($featured['content'])); ?></div>
                      <?php endif; ?>
                      <div class="mt-4">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#postModal"
                          data-title="<?php echo htmlspecialchars($featured['title']); ?>"
                          data-summary="<?php echo htmlspecialchars($featured['summary'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                          data-content="<?php echo htmlspecialchars($featured['content'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                          data-media-type="<?php echo htmlspecialchars($featured['media_type']); ?>"
                          data-media-url="<?php echo htmlspecialchars($featured['media_url'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                          data-author="<?php echo htmlspecialchars($featured['author']); ?>"
                          data-date="<?php echo date('M j, Y', strtotime($featured['created_at'])); ?>"
                          data-views="<?php echo (int)$featured['views']; ?>"
                          data-likes="<?php echo isset($featured['likes']) ? (int)$featured['likes'] : 0; ?>">
                          View full post
                        </button>
                      </div>
                      <div class="d-flex align-items-center gap-3 mt-4">
                        <div>
                          <div style="color: #1B2A4B; font-weight: 600;"><?php echo htmlspecialchars($featured['author']); ?><button class="btn-like" type="button" data-like-btn data-post-id="<?php echo (int)$featured['id']; ?>">
  <i class="bi bi-heart"></i> <span data-like-count><?php echo isset($featured['likes']) ? (int)$featured['likes'] : 0; ?></span>
</button></div>
                          <small style="color: #51617A;"><?php echo date('M j, Y', strtotime($featured['created_at'])); ?> &bull; <?php echo (int)$featured['views']; ?> views</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <?php if ($posts): ?>
          <div class="row g-4">
            <?php foreach ($posts as $post): ?>
            <div class="col-lg-4 col-md-6">
              <div class="card blog-card border-0 shadow-sm h-100">
                <?php if ($post['media_type'] === 'video'): ?>
                  <?php $isExternalVideo = preg_match('/^https?:\/\//i', $post['media_url']); ?>
                  <?php if ($isExternalVideo): ?>
                  <div class="ratio ratio-16x9">
                    <iframe src="<?php echo htmlspecialchars($post['media_url']); ?>" title="<?php echo htmlspecialchars($post['title']); ?>" allowfullscreen></iframe>
                  </div>
                  <?php else: ?>
                  <video controls class="card-img-top" style="height: 220px; object-fit: cover;">
                    <source src="<?php echo htmlspecialchars($post['media_url']); ?>">
                    Your browser does not support the video tag.
                  </video>
                  <?php endif; ?>
                <?php else: ?>
                  <img src="<?php echo htmlspecialchars($post['media_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>" style="height: 220px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body p-4">
                  <?php $badgeClass = $post['media_type'] === 'video' ? 'bg-info text-white' : 'bg-primary-subtle text-primary'; ?>
                  <span class="badge <?php echo $badgeClass; ?> mb-2"><?php echo ucfirst($post['media_type']); ?></span>
                  <p class="card-text" style="color: #51617A;">
                    <?php echo nl2br(htmlspecialchars($post['summary'])); ?>
                  </p>
                  <div class="mt-3">
                    <button type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#postModal"
                        data-id="<?php echo (int)$post['id']; ?>"
                        data-title="<?php echo htmlspecialchars($post['title']); ?>"
                        data-author="<?php echo htmlspecialchars($post['author']); ?>"
                        data-date="<?php echo date('M j, Y', strtotime($post['created_at'])); ?>"
                        data-views="<?php echo (int)$post['views']; ?>"
                        data-likes="<?php echo (int)$post['likes']; ?>"
                        data-summary="<?php echo htmlspecialchars($post['summary']); ?>"
                        data-content="<?php echo htmlspecialchars($post['content']); ?>"
                        data-media-type="<?php echo htmlspecialchars($post['media_type']); ?>"
                        data-media-url="<?php echo htmlspecialchars($post['media_url']); ?>">
                  View details
                </button>

                  </div>
                </div>
                <div class="card-footer bg-white border-0 p-4 pt-0 d-flex justify-content-between align-items-center">
  <small class="text-muted"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></small>

  <button class="btn-like" type="button"
          data-like-btn
          data-post-id="<?php echo (int)$post['id']; ?>">
    <i class="bi bi-heart"></i>
    <span data-like-count><?php echo (int)$post['likes']; ?></span>
  </button>
</div>

              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>

    <div class="modal fade" id="postModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" data-post-title>Post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <!-- Media -->
        <div class="mb-3" data-post-media></div>

        <!-- Meta row -->
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="text-muted" data-post-author>LM</span>
          <span class="mx-1">•</span>
          <span class="text-muted" data-post-date></span>
          <span class="mx-1">•</span>
          <span class="text-muted"><span data-post-views>0</span> views</span>

          <!-- Like in modal (floats right) -->
          <button class="btn-like ms-auto" type="button"
                  data-like-btn
                  data-modal-like
                  data-post-id="">
            <i class="bi bi-heart"></i>
            <span data-like-count>0</span>
          </button>
        </div>

        <!-- Summary + Content -->
        <p class="mb-3" data-post-summary></p>
        <div data-post-content class=""></div>
      </div>
    </div>
  </div>
</div>

      </div>
    </div>

    <section class="section" id="newsletter" style="padding: 80px 0;">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-lg-7">
            <h2 style="font-size: 2.5rem; font-weight: 700; color: #1B2A4B;">Get hiring insights in your inbox</h2>
            <p class="lead mb-4" style="color: #51617A;">Monthly stories on hiring best practices, educator success, and product releases.</p>
            <form class="d-flex flex-column flex-md-row gap-3 justify-content-center" action="newsletter.php" method="post">
              <input type="email" class="form-control form-control-lg" name="email" placeholder="Email address" required>
              <button class="btn btn-primary btn-lg px-4" type="submit">Subscribe</button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>

  <div data-global-footer></div>
  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="assets/js/footer.js"></script>
  <script>
    (function () {
      const modalEl = document.getElementById('postModal');
      if (!modalEl) {
        return;
      }
      modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) {
          return;
        }
        const titleEl = modalEl.querySelector('[data-post-title]');
        const summaryEl = modalEl.querySelector('[data-post-summary]');
        const contentEl = modalEl.querySelector('[data-post-content]');
        const authorEl = modalEl.querySelector('[data-post-author]');
        const dateEl = modalEl.querySelector('[data-post-date]');
        const viewsEl = modalEl.querySelector('[data-post-views]');
        const likesEl = modalEl.querySelector('[data-post-likes]');
        const likesBullet = likesEl ? likesEl.previousElementSibling : null;
        const mediaWrapper = modalEl.querySelector('[data-post-media]');

        const title = trigger.getAttribute('data-title') || '';
        const summary = trigger.getAttribute('data-summary') || '';
        const content = trigger.getAttribute('data-content') || '';
        const author = trigger.getAttribute('data-author') || '';
        const created = trigger.getAttribute('data-date') || '';
        const views = trigger.getAttribute('data-views') || '';
        const likes = trigger.getAttribute('data-likes') || '';
        const mediaType = (trigger.getAttribute('data-media-type') || '').toLowerCase();
        const mediaUrl = trigger.getAttribute('data-media-url') || '';

        titleEl.textContent = title;
        summaryEl.innerHTML = formatText(summary);
        if (content.trim()) {
          contentEl.innerHTML = formatText(content);
          contentEl.style.display = '';
        } else {
          contentEl.innerHTML = '';
          contentEl.style.display = 'none';
        }
        authorEl.textContent = author || 'LevelMinds';
        dateEl.textContent = created;
        if (viewsEl) {
          if (views) {
            viewsEl.textContent = views + ' views';
            viewsEl.style.display = '';
          } else {
            viewsEl.textContent = '';
            viewsEl.style.display = 'none';
          }
        }
        if (likesEl) {
          if (likes) {
            likesEl.textContent = likes + ' likes';
            likesEl.style.display = '';
            if (likesBullet) likesBullet.style.display = '';
          } else {
            likesEl.textContent = '';
            likesEl.style.display = 'none';
            if (likesBullet) likesBullet.style.display = 'none';
          }
        }
        renderMedia(mediaWrapper, mediaType, mediaUrl, title);
      });

      function formatText(str) {
        return (str || '').replace(/(?:\r\n|\r|\n)/g, '<br>');
      }

      function renderMedia(container, type, url, title) {
        if (!container) {
          return;
        }
        container.innerHTML = '';
        if (!url) {
          container.style.display = 'none';
          return;
        }
        container.style.display = '';
        const isExternal = /^https?:\/\//i.test(url);
        if (type === 'video') {
          if (isExternal && !url.match(/\.(mp4|mov|m4v|webm|ogv|ogg)(\?|$)/i)) {
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.title = 'Video';
            iframe.allowFullscreen = true;
            iframe.className = 'w-100';
            iframe.style.minHeight = '360px';
            container.appendChild(iframe);
          } else {
            const video = document.createElement('video');
            video.controls = true;
            video.className = 'w-100 rounded';
            const source = document.createElement('source');
            source.src = url;
            video.appendChild(source);
            container.appendChild(video);
          }
        } else {
          const img = document.createElement('img');
          img.src = url;
          img.alt = title || 'Blog media';
          img.className = 'img-fluid rounded';
          container.appendChild(img);
        }
      }
    })();
  </script>
  <script>
document.addEventListener('DOMContentLoaded', function() {
  const modalEl = document.getElementById('postModal');
  if (!modalEl) return;

  modalEl.addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    if (!trigger) return;

    // Pull id/likes from the trigger's data-* attributes
    const id = trigger.getAttribute('data-id');
    const likes = parseInt(trigger.getAttribute('data-likes') || '0', 10);

    const likeBtn = modalEl.querySelector('[data-modal-like]');
    if (!likeBtn) return;

    likeBtn.setAttribute('data-post-id', id);
    const countEl = likeBtn.querySelector('[data-like-count]');
    if (countEl) countEl.textContent = String(likes);
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const modalEl = document.getElementById('postModal');
  if (!modalEl) return;

  function decodeHtmlEntities(str) {
    const txt = document.createElement('textarea');
    txt.innerHTML = str == null ? '' : String(str);
    return txt.value;
  }

  modalEl.addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    if (!trigger) return;

    // Read data-* from the button
    const id        = trigger.getAttribute('data-id');
    const title     = trigger.getAttribute('data-title') || '';
    const author    = trigger.getAttribute('data-author') || 'LM';
    const date      = trigger.getAttribute('data-date') || '';
    const views     = trigger.getAttribute('data-views') || '0';
    const likes     = trigger.getAttribute('data-likes') || '0';
    const summary   = trigger.getAttribute('data-summary') || '';
    const content   = trigger.getAttribute('data-content') || '';
    const mediaType = (trigger.getAttribute('data-media-type') || '').toLowerCase();
    const mediaUrl  = trigger.getAttribute('data-media-url') || '';

    // Fill basic fields
    const titleEl   = modalEl.querySelector('[data-post-title]');
    const authorEl  = modalEl.querySelector('[data-post-author]');
    const dateEl    = modalEl.querySelector('[data-post-date]');
    const viewsEl   = modalEl.querySelector('[data-post-views]');
    const sumEl     = modalEl.querySelector('[data-post-summary]');
    const contentEl = modalEl.querySelector('[data-post-content]');
    const mediaEl   = modalEl.querySelector('[data-post-media]');
    const likeBtn   = modalEl.querySelector('[data-modal-like]');

    if (titleEl)  titleEl.textContent = title;
    if (authorEl) authorEl.textContent = author;
    if (dateEl)   dateEl.textContent = date;
    if (viewsEl)  viewsEl.textContent = views;
    if (sumEl)    sumEl.textContent = decodeHtmlEntities(summary);

    // Content is HTML: decode entities, then set as innerHTML
    if (contentEl) contentEl.innerHTML = decodeHtmlEntities(content);

    // Media rendering
    if (mediaEl) {
      mediaEl.innerHTML = '';
      if (mediaType === 'video' && mediaUrl) {
        // If it's an external URL (e.g., YouTube), show <video> fallback if same-origin is required
        const isExternal = /^https?:\/\//i.test(mediaUrl);
        if (isExternal && /\.(mp4|webm|ogg)$/i.test(mediaUrl)) {
          mediaEl.innerHTML = '<video controls class="w-100 rounded"><source src="'+ mediaUrl +'"></video>';
        } else if (!isExternal) {
          mediaEl.innerHTML = '<video controls class="w-100 rounded"><source src="'+ mediaUrl +'"></video>';
        } else {
          mediaEl.innerHTML = '<div class="ratio ratio-16x9"><iframe src="'+ mediaUrl +'" allowfullscreen frameborder="0"></iframe></div>';
        }
      } else if (mediaType === 'photo' && mediaUrl) {
        mediaEl.innerHTML = '<img src="'+ mediaUrl +'" class="img-fluid rounded" alt="">';
      }
    }

    // Wire modal like button
    if (likeBtn) {
      likeBtn.setAttribute('data-post-id', id);
      const countEl = likeBtn.querySelector('[data-like-count]');
      if (countEl) countEl.textContent = String(likes);

      // Re-hydrate icon state for this id (in case it changed in the list)
      if (window.LM && typeof window.LM.hydrateLikes === 'function') {
        window.LM.hydrateLikes();
      } else {
        // simple local hydrate
        const liked = localStorage.getItem('lm_like_' + id) === '1';
        const icon = likeBtn.querySelector('.bi');
        if (liked) {
          likeBtn.classList.add('liked');
          icon && icon.classList.remove('bi-heart');
          icon && icon.classList.add('bi-heart-fill');
        } else {
          likeBtn.classList.remove('liked');
          icon && icon.classList.add('bi-heart');
          icon && icon.classList.remove('bi-heart-fill');
        }
      }
    }
  });
});
</script>
<script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/js/custom.js?v=4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const modalEl = document.getElementById('postModal');
  if (!modalEl) return;

  function decodeHtmlEntities(str) {
    const txt = document.createElement('textarea');
    txt.innerHTML = str == null ? '' : String(str);
    return txt.value;
  }

  // Populate modal on SHOW (before it becomes visible)
  modalEl.addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    if (!trigger) return;

    const id        = trigger.getAttribute('data-id');
    const title     = trigger.getAttribute('data-title') || '';
    const author    = trigger.getAttribute('data-author') || 'LM';
    const date      = trigger.getAttribute('data-date') || '';
    const views     = parseInt(trigger.getAttribute('data-views') || '0', 10);
    const likes     = parseInt(trigger.getAttribute('data-likes') || '0', 10);
    const summary   = trigger.getAttribute('data-summary') || '';
    const content   = trigger.getAttribute('data-content') || '';
    const mediaType = (trigger.getAttribute('data-media-type') || '').toLowerCase();
    const mediaUrl  = trigger.getAttribute('data-media-url') || '';

    // Stash id for the 'shown' handler
    modalEl.dataset.postId = id;

    const titleEl   = modalEl.querySelector('[data-post-title]');
    const authorEl  = modalEl.querySelector('[data-post-author]');
    const dateEl    = modalEl.querySelector('[data-post-date]');
    const viewsEl   = modalEl.querySelector('[data-post-views]');
    const sumEl     = modalEl.querySelector('[data-post-summary]');
    const contentEl = modalEl.querySelector('[data-post-content]');
    const mediaEl   = modalEl.querySelector('[data-post-media]');
    const likeBtn   = modalEl.querySelector('[data-modal-like]');

    if (titleEl)  titleEl.textContent = title;
    if (authorEl) authorEl.textContent = author;
    if (dateEl)   dateEl.textContent = date;
    if (viewsEl)  viewsEl.textContent = String(views);
    if (sumEl)    sumEl.textContent = decodeHtmlEntities(summary);
    if (contentEl) contentEl.innerHTML = decodeHtmlEntities(content);

    if (mediaEl) {
      mediaEl.innerHTML = '';
      if (mediaType === 'video' && mediaUrl) {
        const isExternal = /^https?:\/\//i.test(mediaUrl);
        if (isExternal && /\.(mp4|webm|ogg)$/i.test(mediaUrl)) {
          mediaEl.innerHTML = '<video controls class="w-100 rounded"><source src="'+ mediaUrl +'"></video>';
        } else if (!isExternal) {
          mediaEl.innerHTML = '<video controls class="w-100 rounded"><source src="'+ mediaUrl +'"></video>';
        } else {
          mediaEl.innerHTML = '<div class="ratio ratio-16x9"><iframe src="'+ mediaUrl +'" allowfullscreen frameborder="0"></iframe></div>';
        }
      } else if (mediaType === 'photo' && mediaUrl) {
        mediaEl.innerHTML = '<img src="'+ mediaUrl +'" class="img-fluid rounded" alt="">';
      }
    }

    if (likeBtn) {
      likeBtn.setAttribute('data-post-id', id);
      const countEl = likeBtn.querySelector('[data-like-count]');
      if (countEl) countEl.textContent = String(likes);
    }
  });

  // After the modal is fully visible, increment views (once/6h) and update UI
  modalEl.addEventListener('shown.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    const id = modalEl.dataset.postId;
    if (!id) return;

    const viewsEl = modalEl.querySelector('[data-post-views]');

    function updateViewsUI(latest) {
      // Update modal
      if (viewsEl) viewsEl.textContent = String(latest);

      // Update the trigger button’s data and any views label on the card
      if (trigger) {
        trigger.setAttribute('data-views', String(latest));
        const card = trigger.closest('.card');
        if (card) {
          const cardViewsEl = card.querySelector('[data-post-views]');
          if (cardViewsEl) cardViewsEl.textContent = String(latest);
        }
      }
    }

    // Kick the counter (unique-ish via localStorage TTL)
    if (window.LM && typeof window.LM.trackView === 'function') {
      const initialViews = parseInt(trigger?.getAttribute('data-views') || '0', 10);
      window.LM.trackView(id, { initialViews, updateUI: updateViewsUI });
    }
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const modalEl = document.getElementById('postModal');
  if (!modalEl) return;

  function decodeHtmlEntities(str) {
    const txt = document.createElement('textarea');
    txt.innerHTML = str == null ? '' : String(str);
    return txt.value;
  }

  function renderMedia(container, type, url, title) {
    if (!container) return;
    container.innerHTML = '';
    if (!url) { container.style.display = 'none'; return; }
    container.style.display = '';
    const isExternal = /^https?:\/\//i.test(url);
    if (type === 'video') {
      if (isExternal && !url.match(/\.(mp4|mov|m4v|webm|ogv|ogg)(\?|$)/i)) {
        const iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.title = title || 'Video';
        iframe.allowFullscreen = true;
        iframe.className = 'w-100';
        iframe.style.minHeight = '360px';
        container.appendChild(iframe);
      } else {
        const video = document.createElement('video');
        video.controls = true;
        video.className = 'w-100 rounded';
        const source = document.createElement('source');
        source.src = url;
        video.appendChild(source);
        container.appendChild(video);
      }
    } else {
      const img = document.createElement('img');
      img.src = url;
      img.alt = title || 'Blog media';
      img.className = 'img-fluid rounded';
      container.appendChild(img);
    }
  }

  // 1) Populate modal just before it opens
  modalEl.addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    if (!trigger) return;

    const id        = trigger.getAttribute('data-id');
    const title     = trigger.getAttribute('data-title') || '';
    const author    = trigger.getAttribute('data-author') || 'LM';
    const date      = trigger.getAttribute('data-date') || '';
    const views     = parseInt(trigger.getAttribute('data-views') || '0', 10);
    const likes     = parseInt(trigger.getAttribute('data-likes') || '0', 10);
    const summary   = trigger.getAttribute('data-summary') || '';
    const content   = trigger.getAttribute('data-content') || '';
    const mediaType = (trigger.getAttribute('data-media-type') || '').toLowerCase();
    const mediaUrl  = trigger.getAttribute('data-media-url') || '';

    modalEl.dataset.postId = id;

    const titleEl   = modalEl.querySelector('[data-post-title]');
    const authorEl  = modalEl.querySelector('[data-post-author]');
    const dateEl    = modalEl.querySelector('[data-post-date]');
    const viewsEl   = modalEl.querySelector('[data-post-views]');
    const sumEl     = modalEl.querySelector('[data-post-summary]');
    const contentEl = modalEl.querySelector('[data-post-content]');
    const mediaEl   = modalEl.querySelector('[data-post-media]');
    const likeBtn   = modalEl.querySelector('[data-modal-like]');

    if (titleEl)  titleEl.textContent = title;
    if (authorEl) authorEl.textContent = author;
    if (dateEl)   dateEl.textContent = date;
    if (viewsEl)  viewsEl.textContent = String(views);  // numeric only
    if (sumEl)    sumEl.textContent = decodeHtmlEntities(summary);
    if (contentEl) contentEl.innerHTML = decodeHtmlEntities(content);

    renderMedia(mediaEl, mediaType, mediaUrl, title);

    if (likeBtn) {
      likeBtn.setAttribute('data-post-id', id);
      const countEl = likeBtn.querySelector('[data-like-count]');
      if (countEl) countEl.textContent = String(likes);

      // hydrate icon state from localStorage if your like.js isn't loaded yet
      const liked = localStorage.getItem('lm_like_' + id) === '1';
      const icon = likeBtn.querySelector('.bi');
      if (liked) {
        likeBtn.classList.add('liked');
        icon && icon.classList.remove('bi-heart');
        icon && icon.classList.add('bi-heart-fill');
      } else {
        likeBtn.classList.remove('liked');
        icon && icon.classList.add('bi-heart');
        icon && icon.classList.remove('bi-heart-fill');
      }
    }
  });

  // 2) After it’s visible, increment views once per 6h per device and update UI
  modalEl.addEventListener('shown.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    const id = modalEl.dataset.postId;
    if (!id) return;

    const viewsEl = modalEl.querySelector('[data-post-views]');
    const API_URL = 'api/view.php';
    const VIEW_TTL_MS = 6 * 60 * 60 * 1000;
    const LS_KEY = 'lm_viewed_' + id;

    const last = parseInt(localStorage.getItem(LS_KEY) || '0', 10);
    const freshEnough = Number.isFinite(last) && (Date.now() - last) < VIEW_TTL_MS;

    // If we counted very recently, just reflect what the trigger currently has
    if (freshEnough) {
      const latest = parseInt(trigger.getAttribute('data-views') || '0', 10);
      if (viewsEl) viewsEl.textContent = String(latest);
      return;
    }

    fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: id })
    })
    .then(r => r.json())
    .then(data => {
      if (!data || !data.ok || typeof data.views !== 'number') return;
      localStorage.setItem(LS_KEY, String(Date.now()));

      // Update modal
      if (viewsEl) viewsEl.textContent = String(data.views);

      // Update the trigger button’s data and any views label on the card
      if (trigger) {
        trigger.setAttribute('data-views', String(data.views));
        const card = trigger.closest('.card');
        if (card) {
          const cardViewsEl = card.querySelector('[data-post-views]');
          if (cardViewsEl) cardViewsEl.textContent = String(data.views);
        }
      }
    })
    .catch(() => {});
  });
});
</script>

</body>
</html>







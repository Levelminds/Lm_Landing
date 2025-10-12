<?php
declare(strict_types=1);

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

$posts = [];
$error = '';

function decodeBlogHtml($value): string
{
    return trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function decodeBlogText($value): string
{
    return trim(strip_tags(decodeBlogHtml($value)));
}

function mbStringLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function mbStringSlice(string $value, int $start, int $length): string
{
    return function_exists('mb_substr') ? mb_substr($value, $start, $length, 'UTF-8') : substr($value, $start, $length);
}

function formatBlogDate($value): string
{
    if (empty($value)) {
        return '—';
    }

    try {
        $date = new DateTime($value);
        return $date->format('M j, Y');
    } catch (Throwable $exception) {
        return '—';
    }
}

function normaliseCategory($value): string
{
    $normalised = strtolower(trim((string) $value));

    $mapping = [
        'teacher' => 'teachers',
        'teachers' => 'teachers',
        'school' => 'schools',
        'schools' => 'schools',
        'general' => 'general',
    ];

    if (isset($mapping[$normalised])) {
        return $mapping[$normalised];
    }

    return 'general';
}

function blogExcerpt(array $post, int $limit = 180): string
{
    $text = decodeBlogText($post['summary'] ?? '');

    if ($text === '') {
        $text = decodeBlogText($post['content'] ?? '');
    }

    if ($text === '') {
        return '';
    }

    if (mbStringLength($text) > $limit) {
        return rtrim(mbStringSlice($text, 0, $limit - 1)) . '…';
    }

    return $text;
}

function blogReadTime(array $post): string
{
    $content = decodeBlogHtml($post['content'] ?? '');

    if ($content === '') {
        $content = decodeBlogHtml($post['summary'] ?? '');
    }

    $words = str_word_count(strip_tags($content));
    $minutes = max(1, (int) ceil($words / 200));

    return sprintf('%d min read', $minutes);
}

function blogMediaUrl(array $post): string
{
    $url = trim((string) ($post['media_url'] ?? ''));

    if ($url !== '') {
        return $url;
    }

    return 'assets/images/img-1-min.jpg';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $columns = 'id, title, author, summary, content, media_type, media_url, created_at, views, likes, category';
    $query = $pdo->query("SELECT $columns FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC");
    $posts = $query->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $exception) {
    $error = 'We\'re having trouble loading the latest stories right now. Please try again soon.';
}

$preferredCategories = ['teachers', 'schools', 'general'];
$featured = $posts[0] ?? null;

$categoryBuckets = [
    'teachers' => [],
    'schools' => [],
    'general' => [],
];

foreach ($posts as $index => $post) {
    $category = normaliseCategory($post['category'] ?? 'general');

    if ($featured && $index === 0) {
        continue;
    }

    $categoryBuckets[$category][] = $post;
}

$hasPosts = (bool) $posts;

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$hostName = $_SERVER['HTTP_HOST'] ?? 'www.levelminds.in';
$baseShareUrl = rtrim(sprintf('%s://%s', $scheme, $hostName), '/');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LevelMinds Blogs | Insights for Schools &amp; Teachers</title>
  <meta name="description" content="Explore the latest LevelMinds stories for teachers, schools, and education leaders.">
  <link rel="icon" href="assets/images/logo/logo.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/images/logo/logo.svg">
  <link rel="manifest" href="assets/images/logo/logo.svg">
  <link rel="canonical" href="https://www.levelminds.in/blogs.php">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/custom.css" rel="stylesheet">

  <style>
    body {
      background-color: #f3f8ff;
      font-family: 'Public Sans', sans-serif;
      color: #0f254f;
    }

    .blog-hero {
      position: relative;
      padding: 6rem 0 4rem;
      background: linear-gradient(135deg, #d7e7ff 0%, #f3f8ff 100%);
    }

    .blog-hero__card {
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 24px 60px rgba(15, 37, 79, 0.12);
      overflow: hidden;
    }

    .blog-hero__image img {
      height: 100%;
      object-fit: cover;
    }

    .blog-hero__meta span {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      font-size: .95rem;
      color: #47628b;
    }

    .blog-hero__meta i,
    .blog-card__meta i {
      color: #3b82f6;
    }

    .section-heading {
      margin-bottom: 1.5rem;
    }

    .section-heading .eyebrow {
      text-transform: uppercase;
      letter-spacing: .08em;
      font-weight: 700;
      color: #3b82f6;
      font-size: .875rem;
    }

    .section-heading h2 {
      font-weight: 800;
      color: #0f254f;
    }

    .blog-card {
      height: 100%;
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 12px 30px rgba(15, 37, 79, 0.08);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .blog-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 18px 38px rgba(15, 37, 79, 0.12);
    }

    .blog-card__media img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .blog-card__body {
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      gap: .75rem;
    }

    .blog-card__category {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      font-weight: 600;
      color: #1b4cc2;
      font-size: .9rem;
    }

    .blog-card__category::before {
      content: '';
      width: 10px;
      height: 10px;
      border-radius: 999px;
      background: #3b82f6;
      opacity: .4;
    }

    .blog-card__title {
      font-size: 1.15rem;
      font-weight: 700;
      color: #0f254f;
      margin: 0;
    }

    .blog-card__summary {
      color: #47628b;
      font-size: .98rem;
    }

    .blog-card__meta {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      color: #47628b;
      font-size: .9rem;
    }

    .blog-card__footer {
      margin-top: auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }

    .blog-card__stats {
      display: inline-flex;
      align-items: center;
      gap: .75rem;
      color: #47628b;
      font-size: .9rem;
    }

    .btn-read-more {
      background: #1b4cc2;
      color: #fff;
      font-weight: 600;
      border-radius: 999px;
      padding: .5rem 1.4rem;
    }

    .btn-read-more:hover {
      color: #fff;
      background: #133b9a;
    }

    .blog-action {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      border-radius: 999px;
      border: 1px solid rgba(59, 130, 246, 0.2);
      padding: .45rem 1rem;
      font-size: .9rem;
      color: #1b4cc2;
      transition: background-color .2s ease, color .2s ease, border-color .2s ease;
    }

    .blog-action:hover {
      background: rgba(59, 130, 246, 0.1);
      color: #133b9a;
      border-color: rgba(59, 130, 246, 0.4);
    }

    .blog-action.is-liked,
    .blog-action.is-liked:hover {
      background: #ffe7ef;
      border-color: #ff7aa8;
      color: #d83c6b;
    }

    .blog-categories {
      padding: 4rem 0 5rem;
    }

    .category-section {
      margin-top: 3rem;
    }

    .empty-state {
      background: #fff;
      border-radius: 20px;
      padding: 3rem;
      text-align: center;
      color: #47628b;
      box-shadow: 0 12px 30px rgba(15, 37, 79, 0.08);
    }

    @media (max-width: 991.98px) {
      .blog-hero {
        padding: 4rem 0 3rem;
      }

      .blog-hero__card {
        border-radius: 20px;
      }
    }
  </style>
</head>

<body class="blog-page">
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
            <a class="btn btn-nav-outline w-100" href="https://www.staging.levelminds.in" target="_blank" rel="noopener">Login / Sign Up</a>
          </div>
        </div>
      </div>

      <div class="d-flex align-items-center gap-3">
        <div class="header-actions d-none d-lg-flex align-items-center gap-2">
          <a class="btn btn-nav-outline" href="https://www.staging.levelminds.in" target="_blank" rel="noopener">Login / Sign Up</a>
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

  <main class="mt-5 pt-4">
    <section class="blog-hero">
      <div class="container">
        <?php if ($featured): ?>
          <?php
            $featuredId = (int) ($featured['id'] ?? 0);
            $featuredTitle = decodeBlogText($featured['title'] ?? '');
            $featuredAuthor = decodeBlogText($featured['author'] ?? 'LevelMinds Team');
            $featuredDate = formatBlogDate($featured['created_at'] ?? '');
            $featuredViews = (int) ($featured['views'] ?? 0);
            $featuredLikes = (int) ($featured['likes'] ?? 0);
            $featuredSummary = blogExcerpt($featured, 220);
            $featuredReadTime = blogReadTime($featured);
            $featuredCategory = normaliseCategory($featured['category'] ?? 'general');
            $featuredCategoryLabel = ucfirst($featuredCategory === 'general' ? 'General' : $featuredCategory);
            $featuredShareUrl = sprintf('%s/blog_single.php?id=%d', $baseShareUrl, $featuredId);
          ?>
          <div class="blog-hero__card">
            <div class="row g-0 align-items-stretch">
              <div class="col-lg-7">
                <div class="blog-hero__image h-100">
                  <img src="<?php echo htmlspecialchars(blogMediaUrl($featured), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?>" class="w-100">
                </div>
              </div>
              <div class="col-lg-5">
                <div class="p-4 p-lg-5 h-100 d-flex flex-column justify-content-between">
                  <div>
                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis mb-3">Featured • <?php echo htmlspecialchars($featuredCategoryLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                    <h1 class="display-6 fw-bold mb-3" style="color:#0f254f;"><?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <?php if ($featuredSummary !== ''): ?>
                      <p class="mb-4" style="color:#47628b; font-size:1.05rem;">
                        <?php echo htmlspecialchars($featuredSummary, ENT_QUOTES, 'UTF-8'); ?>
                      </p>
                    <?php endif; ?>
                  </div>
                  <div class="d-flex flex-column gap-4">
                    <div class="blog-hero__meta d-flex flex-wrap gap-3">
                      <span><i class="bi bi-person-circle"></i><?php echo htmlspecialchars($featuredAuthor, ENT_QUOTES, 'UTF-8'); ?></span>
                      <span><i class="bi bi-calendar3"></i><?php echo htmlspecialchars($featuredDate, ENT_QUOTES, 'UTF-8'); ?></span>
                      <span><i class="bi bi-clock-history"></i><?php echo htmlspecialchars($featuredReadTime, ENT_QUOTES, 'UTF-8'); ?></span>
                      <span><i class="bi bi-eye"></i><span class="js-view-count" data-post-id="<?php echo $featuredId; ?>"><?php echo number_format($featuredViews); ?></span> views</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                      <a class="btn btn-read-more js-read-more" href="blog_single.php?id=<?php echo $featuredId; ?>" data-post-id="<?php echo $featuredId; ?>">Read More</a>
                      <button class="blog-action js-like" type="button" data-post-id="<?php echo $featuredId; ?>">
                        <i class="bi bi-heart"></i>
                        <span class="js-like-label">Like</span>
                        <span class="js-like-count" data-post-id="<?php echo $featuredId; ?>"><?php echo number_format($featuredLikes); ?></span>
                      </button>
                      <button class="blog-action js-share" type="button" data-share-title="<?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?>" data-share-text="<?php echo htmlspecialchars($featuredSummary !== '' ? $featuredSummary : $featuredTitle, ENT_QUOTES, 'UTF-8'); ?>" data-share-url="<?php echo htmlspecialchars($featuredShareUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="bi bi-share"></i>
                        Share
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="text-center py-5">
            <h1 class="display-5 fw-bold mb-3" style="color:#0f254f;">LevelMinds Blogs</h1>
            <p class="lead mb-0" style="color:#47628b;">Discover stories from real educators, school leaders, and the LevelMinds team.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <section class="blog-categories">
      <div class="container">
        <div class="section-heading text-center">
          <p class="eyebrow mb-2">Discover Blogs by Categories</p>
          <h2 class="h1 mb-3">Fresh insights for every learning community</h2>
          <p class="mb-0" style="color:#47628b;">Browse the latest published posts curated for teachers, schools, and the broader education ecosystem.</p>
        </div>

        <?php if ($error !== ''): ?>
          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="empty-state"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
          </div>
        <?php elseif (!$hasPosts): ?>
          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="empty-state">Approved blog posts will appear here as soon as they are published by the LevelMinds team.</div>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($preferredCategories as $categoryKey): ?>
            <?php $categoryPosts = $categoryBuckets[$categoryKey] ?? []; ?>
            <?php if (!$categoryPosts): ?>
              <?php continue; ?>
            <?php endif; ?>
            <?php
              $categoryTitle = $categoryKey === 'general' ? 'General' : ucfirst($categoryKey);
            ?>
            <div class="category-section" id="category-<?php echo htmlspecialchars($categoryKey, ENT_QUOTES, 'UTF-8'); ?>">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                  <h3 class="h3 fw-bold mb-1" style="color:#0f254f;">For <?php echo htmlspecialchars($categoryTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                  <p class="mb-0" style="color:#47628b;">Latest approved posts tailored for <?php echo htmlspecialchars($categoryTitle, ENT_QUOTES, 'UTF-8'); ?>.</p>
                </div>
              </div>
              <div class="row g-4">
                <?php foreach ($categoryPosts as $post): ?>
                  <?php
                    $postId = (int) ($post['id'] ?? 0);
                    $postTitle = decodeBlogText($post['title'] ?? '');
                    $postAuthor = decodeBlogText($post['author'] ?? 'LevelMinds Team');
                    $postDate = formatBlogDate($post['created_at'] ?? '');
                    $postSummary = blogExcerpt($post);
                    $postViews = (int) ($post['views'] ?? 0);
                    $postLikes = (int) ($post['likes'] ?? 0);
                    $postReadTime = blogReadTime($post);
                    $shareUrl = sprintf('%s/blog_single.php?id=%d', $baseShareUrl, $postId);
                  ?>
                  <div class="col-md-6 col-lg-4">
                    <article class="blog-card">
                      <div class="ratio ratio-16x9 blog-card__media">
                        <img src="<?php echo htmlspecialchars(blogMediaUrl($post), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="blog-card__body">
                        <span class="blog-card__category">For <?php echo htmlspecialchars($categoryTitle, ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3 class="blog-card__title"><?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <?php if ($postSummary !== ''): ?>
                          <p class="blog-card__summary"><?php echo htmlspecialchars($postSummary, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <div class="blog-card__meta">
                          <span><i class="bi bi-person-circle"></i><?php echo htmlspecialchars($postAuthor, ENT_QUOTES, 'UTF-8'); ?></span>
                          <span><i class="bi bi-calendar3"></i><?php echo htmlspecialchars($postDate, ENT_QUOTES, 'UTF-8'); ?></span>
                          <span><i class="bi bi-clock-history"></i><?php echo htmlspecialchars($postReadTime, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="blog-card__footer">
                          <div class="blog-card__stats">
                            <span><i class="bi bi-eye"></i><span class="js-view-count" data-post-id="<?php echo $postId; ?>"><?php echo number_format($postViews); ?></span></span>
                            <span><i class="bi bi-heart"></i><span class="js-like-count" data-post-id="<?php echo $postId; ?>"><?php echo number_format($postLikes); ?></span></span>
                          </div>
                          <a class="btn btn-read-more js-read-more" href="blog_single.php?id=<?php echo $postId; ?>" data-post-id="<?php echo $postId; ?>">Read More</a>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                          <button class="blog-action js-like" type="button" data-post-id="<?php echo $postId; ?>">
                            <i class="bi bi-heart"></i>
                            <span class="js-like-label">Like</span>
                            <span class="js-like-count" data-post-id="<?php echo $postId; ?>"><?php echo number_format($postLikes); ?></span>
                          </button>
                          <button class="blog-action js-share" type="button" data-share-title="<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>" data-share-text="<?php echo htmlspecialchars($postSummary !== '' ? $postSummary : $postTitle, ENT_QUOTES, 'UTF-8'); ?>" data-share-url="<?php echo htmlspecialchars($shareUrl, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="bi bi-share"></i>
                            Share
                          </button>
                        </div>
                      </div>
                    </article>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php foreach ($posts as $post): ?>
    <?php
      $templateId = (int) ($post['id'] ?? 0);
      if (!$templateId) {
          continue;
      }
      $templateContent = blogFullContent($post);
    ?>
    <template id="blog-modal-content-<?php echo $templateId; ?>">
      <?php echo $templateContent; ?>
    </template>
  <?php endforeach; ?>

  <?php include 'footer.html'; ?>

  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="shareToast" class="toast align-items-center" role="status" aria-live="polite" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"></div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      const visitorTokenKey = 'lm_visitor_token';
      const likedPostsKey = 'lm_liked_posts';
      const storageTestKey = 'lm_storage_test';

      function ensureVisitorToken() {
        let token = localStorage.getItem(visitorTokenKey);
        if (!token) {
          const random = window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : Math.random().toString(36).slice(2);
          token = `lm_${random}`;
          localStorage.setItem(visitorTokenKey, token);
        }
        return token;
      }

      function getLikedPosts() {
        try {
          const stored = localStorage.getItem(likedPostsKey);
          if (!stored) {
            return new Set();
          }
          const parsed = JSON.parse(stored);
          if (Array.isArray(parsed)) {
            return new Set(parsed.map(Number));
          }
          return new Set();
        } catch (error) {
          console.error('Unable to read liked posts', error);
          return new Set();
        }
      }

      function setLikedPosts(set) {
        try {
          localStorage.setItem(likedPostsKey, JSON.stringify(Array.from(set)));
        } catch (error) {
          return String(value);
        }
      }

      function formatNumber(value) {
        try {
          return new Intl.NumberFormat().format(value);
        } catch (error) {
          return String(value);
        }
      }

      function parseNumberFromText(value) {
        const digits = String(value || '').replace(/[^0-9]/g, '');
        return digits ? Number(digits) : 0;
      }

      function getCurrentLikeCount(postId) {
        const node = document.querySelector(`.js-like-count[data-post-id="${postId}"]`);
        return node ? parseNumberFromText(node.textContent) : 0;
      }

      function updateLikeState(postId, liked, likeCount) {
        document.querySelectorAll(`.js-like-count[data-post-id="${postId}"]`).forEach((node) => {
          node.textContent = formatNumber(likeCount);
        });

        document.querySelectorAll(`.js-like[data-post-id="${postId}"]`).forEach((button) => {
          button.classList.toggle('is-liked', liked);
          button.setAttribute('aria-pressed', liked ? 'true' : 'false');
          const label = button.querySelector('.js-like-label');
          if (label) {
            label.textContent = liked ? 'Liked' : 'Like';
          }
        }

        const modalLink = modalElement.querySelector('.js-modal-link');
        if (modalLink) {
          modalLink.href = data.url || '#';
        }
      }

      function updateViewCount(postId, newCount) {
        document.querySelectorAll(`.js-view-count[data-post-id="${postId}"]`).forEach((node) => {
          node.textContent = new Intl.NumberFormat().format(newCount);
        });
      }

      async function incrementViews(postId) {
        try {
          const response = await fetch('api/views.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId }),
          });

          if (!response.ok) {
            throw new Error('Failed to update views');
          }

          const result = await response.json();
          if (result && typeof result.views === 'number') {
            updateViewCount(postId, result.views);
          }
        } catch (error) {
          console.error(error);
        }
      }

      async function toggleLike(button, likedPosts) {
        const postId = Number(button.dataset.postId);
        if (!postId) {
          return;
        }

        button.disabled = true;
        const wasLiked = likedPosts.has(postId);

        try {
          const response = await fetch('blog-like.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              post_id: postId,
              visitor_token: ensureVisitorToken(),
            }),
          });

          if (!response.ok) {
            throw new Error('Unable to toggle like');
          }

          const result = await response.json();
          const newLikeCount = Number(result.likes ?? 0);
          const isLiked = Boolean(result.liked);

          updateLikeState(postId, isLiked, newLikeCount);

          if (isLiked) {
            likedPosts.add(postId);
          } else {
            likedPosts.delete(postId);
          }
          setLikedPosts(likedPosts);
        } catch (error) {
          console.error(error);
          updateLikeState(postId, wasLiked, getCurrentLikeCount(postId));
        } finally {
          button.disabled = false;
        }
      }

      function initialiseLikes() {
        const likedPosts = getLikedPosts();

        document.querySelectorAll('.js-like').forEach((button) => {
          const postId = Number(button.dataset.postId);
          if (!postId) {
            return;
          }

          if (likedPosts.has(postId)) {
            button.classList.add('is-liked');
            button.setAttribute('aria-pressed', 'true');
            const label = button.querySelector('.js-like-label');
            if (label) {
              label.textContent = 'Liked';
            }
          } else {
            button.setAttribute('aria-pressed', 'false');
          }

          button.addEventListener('click', () => toggleLike(button, likedPosts));
        });
      }

      async function handleReadMore(event) {
        const anchor = event.currentTarget;
        const postId = Number(anchor.dataset.postId);
        if (!postId) {
          return;
        }

        event.preventDefault();
        anchor.classList.add('disabled');
        anchor.setAttribute('aria-disabled', 'true');

        await incrementViews(postId);

        window.location.href = anchor.getAttribute('href');
      }

      async function handleShare(event) {
        const button = event.currentTarget;
        const shareData = {
          title: button.dataset.shareTitle || document.title,
          text: button.dataset.shareText || 'Check out this blog from LevelMinds',
          url: button.dataset.shareUrl || window.location.href,
        };

        const toastEl = document.getElementById('shareToast');
        const toastBody = toastEl ? toastEl.querySelector('.toast-body') : null;

        if (navigator.share) {
          try {
            await navigator.share(shareData);
            if (toastEl && toastBody) {
              toastBody.textContent = 'Thanks for sharing this story!';
              bootstrap.Toast.getOrCreateInstance(toastEl).show();
            }
            return;
          } catch (error) {
            if (error.name === 'AbortError') {
              return;
            }
            console.error('Share failed, falling back to clipboard', error);
          }
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
          try {
            await navigator.clipboard.writeText(shareData.url);
            if (toastEl && toastBody) {
              toastBody.textContent = 'Link copied to clipboard!';
              bootstrap.Toast.getOrCreateInstance(toastEl).show();
            }
            return;
          } catch (error) {
            console.error('Clipboard fallback failed', error);
          }
        }

        if (toastEl && toastBody) {
          toastBody.textContent = 'Copy this link manually: ' + shareData.url;
          bootstrap.Toast.getOrCreateInstance(toastEl).show();
        }
      }

      function initialiseReads() {
        document.querySelectorAll('.js-read-more').forEach((anchor) => {
          anchor.addEventListener('click', handleReadMore);
        });
      }

      function initialiseShares() {
        document.querySelectorAll('.js-share').forEach((button) => {
          button.addEventListener('click', handleShare);
        });
      }

      document.addEventListener('DOMContentLoaded', () => {
        ensureVisitorToken();
        initialiseLikes();
        initialiseReads();
        initialiseShares();
      });
    })();
  </script>
</body>

</html>

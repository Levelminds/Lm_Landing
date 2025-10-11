<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
try {
    require_once __FILE__;
} catch (Throwable $e) {
    echo "<pre style='color:red;'>PHP ERROR: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine() . "</pre>";
    exit;
}

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

$posts = [];
$error = '';
$hasCategoryColumn = false;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

function blogCategoryColumnExists(PDO $pdo): bool {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM blog_posts LIKE 'category'");
        return $stmt && $stmt->fetch() ? true : false;
    } catch (PDOException $e) {
        return false;
    }
}

function ensureBlogCategoryColumn(PDO $pdo): bool {
    if (blogCategoryColumnExists($pdo)) {
        return true;
    }
    try {
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN category ENUM('teachers','schools','general') NOT NULL DEFAULT 'general' AFTER media_url");
    } catch (PDOException $e) {
        return false;
    }
    return blogCategoryColumnExists($pdo);
}

function deduplicateBlogPosts(array $items): array {
    $unique = [];
    $seen = [];
    foreach ($items as $item) {
        $id = isset($item['id']) ? (string) $item['id'] : '';
        $key = $id !== '' ? $id : sha1(json_encode($item));
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $unique[] = $item;
    }
    return $unique;
}

function encodeBlogDataAttr($value): string {
    return $value === null || $value === '' ? '' : base64_encode((string)$value);
}

function formatBlogDate($value): string {
    if (empty($value)) return '—';
    try {
        $date = new DateTime($value);
        return $date->format('M j, Y');
    } catch (Exception $e) {
        return '—';
    }
}

function decodeBlogPlain($value): string {
    return trim(html_entity_decode(strip_tags((string)$value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function buildBlogShareUrl($postId): string {
    $postId = (int)$postId;
    if ($postId <= 0) return '';
    $host = $_SERVER['HTTP_HOST'] ?? 'levelminds.in';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return sprintf('%s://%s/blogs.php?post=%d', $scheme, $host, $postId);
}

// ensure category column exists
$hasCategoryColumn = ensureBlogCategoryColumn($pdo);

// fetch posts
$columns = "id, title, author, summary, content, media_type, media_url, created_at, views, likes" . 
           ($hasCategoryColumn ? ", category" : "");

try {
    $stmt = $pdo->query("SELECT $columns FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC");
    $posts = deduplicateBlogPosts($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    $error = "Unable to load blog posts: " . $e->getMessage();
}

// fallback if empty
if (!$posts) {
    $fallbackPath = __DIR__ . '/data/blogs.json';
    if (is_readable($fallbackPath)) {
        $json = json_decode(file_get_contents($fallbackPath), true);
        if (is_array($json)) {
            $posts = deduplicateBlogPosts($json);
        }
    }
}

$featured = $posts[0] ?? null;
$audienceLabels = [
    'teachers' => 'For Teachers',
    'schools'  => 'For Schools',
    'general'  => 'General Insights'
];


$groupedPosts = [];
foreach ($posts as $post) {
    $category = $hasCategoryColumn ? ($post['category'] ?? 'general') : 'general';
    $category = $post['category'] ?? 'general';
    if (!isset($groupedPosts[$category])) {
        $groupedPosts[$category] = [];
    }
    $groupedPosts[$category][] = $post;
}

if ($featured) {
    $featuredId = (int)($featured['id'] ?? 0);
    $featuredCategory = $hasCategoryColumn ? ($featured['category'] ?? 'general') : 'general';
    $featuredCategory = $featured['category'] ?? 'general';
    if ($featuredId && isset($groupedPosts[$featuredCategory])) {
        $groupedPosts[$featuredCategory] = array_values(array_filter(
            $groupedPosts[$featuredCategory],
            function ($item) use ($featuredId) {
                return (int)($item['id'] ?? 0) !== $featuredId;
            }
        ));
        if (!$groupedPosts[$featuredCategory]) {
            unset($groupedPosts[$featuredCategory]);
        }
    }
}

$preferredOrder = ['teachers', 'schools', 'general'];
$availableCategories = array_keys($groupedPosts);
$orderedCategories = array_values(array_intersect($preferredOrder, $availableCategories));
foreach ($availableCategories as $category) {
    if (!in_array($category, $orderedCategories, true)) {
        $orderedCategories[] = $category;
    }
}
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
  <link href="assets/css/overrides.css" rel="stylesheet">
  <style>
    body { font-family: 'Public Sans', sans-serif; background-color: #F5FAFF; color: #1B2A4B; }
    .fbs__net-navbar { position: fixed; width: 100%; top: 0; left: 0; z-index: 1030; background-color: #FFFFFF !important; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); }
    .fbs__net-navbar .nav-link { color: #1B2A4B !important; }
    .fbs__net-navbar .nav-link:hover,
    .fbs__net-navbar .nav-link.active { color: #3C8DFF !important; }

    .blog-hero { padding: 140px 0 80px; background: radial-gradient(circle at top right, rgba(60, 141, 255, 0.12), transparent 55%), linear-gradient(180deg, rgba(245, 250, 255, 0.35), rgba(245, 250, 255, 0)); }
    .blog-hero h1 { font-size: 3.4rem; font-weight: 800; color: #0F1D3B; }
    .blog-hero p.lead { color: rgba(15, 29, 59, 0.72); max-width: 620px; }
    .hero-highlight { display: flex; flex-direction: column; gap: 1.5rem; }
    .hero-feature { border-radius: 28px; overflow: hidden; background: #ffffff; box-shadow: 0 28px 80px rgba(32, 139, 255, 0.18); display: flex; flex-direction: column; height: 100%; }
    .hero-feature-media { position: relative; min-height: 260px; background: #0F1D3B; }
    .hero-feature-media img,
    .hero-feature-media video,
    .hero-feature-media iframe { width: 100%; height: 100%; object-fit: cover; display: block; }
    .hero-feature-body { padding: 2.25rem; display: flex; flex-direction: column; gap: 1rem; }
    .hero-feature-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; color: #51617A; font-size: 0.95rem; }
    .hero-feature-meta span { display: inline-flex; align-items: center; gap: 0.35rem; }
    .hero-empty { border-radius: 24px; background: rgba(255,255,255,0.9); padding: 3rem; text-align: center; box-shadow: 0 16px 40px rgba(15,29,59,0.12); color: #51617A; }
    .blog-hero { padding: 140px 0 80px; }
    .blog-hero h1 { font-size: 3.5rem; font-weight: 800; color: #0F1D3B; }
    .blog-hero p.lead { color: rgba(15, 29, 59, 0.72); max-width: 720px; margin: 0 auto; }

    .featured-card { border-radius: 28px; overflow: hidden; background: linear-gradient(135deg, rgba(255,255,255,0.95), #F5FAFF); box-shadow: 0 32px 80px rgba(32, 139, 255, 0.18); }
    .featured-card__media { position: relative; min-height: 320px; }
    .featured-card__body { padding: 2.75rem; }
    .featured-meta { display: flex; flex-wrap: wrap; gap: 1rem; color: #51617A; font-size: 0.95rem; }
    .featured-meta span { display: inline-flex; align-items: center; gap: 0.35rem; }

    .blog-section-heading { font-weight: 700; color: #0F1D3B; }
    .blog-filter { gap: 0.75rem; overflow-x: auto; padding-bottom: 0.5rem; }
    .blog-filter .btn { border-radius: 999px; padding: 0.5rem 1.5rem; font-weight: 600; color: #1B2A4B; border: 1px solid transparent; background: #fff; box-shadow: 0 10px 25px rgba(15,29,59,0.08); }
    .blog-filter .btn.active, .blog-filter .btn:hover { background: #3C8DFF; color: #fff; }

    .blog-card { border-radius: 22px; overflow: hidden; border: none; background: #ffffff; box-shadow: 0 22px 60px rgba(15,29,59,0.10); height: 100%; transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .blog-card:hover { transform: translateY(-8px); box-shadow: 0 26px 70px rgba(32,139,255,0.20); }
    .blog-card__media { position: relative; overflow: hidden; }
    .blog-card__media img, .blog-card__media video, .blog-card__media iframe { width: 100%; height: 220px; object-fit: cover; }
    .blog-card__body { padding: 1.8rem; display: flex; flex-direction: column; gap: 1rem; }
    .blog-card__category { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.06em; color: #3C8DFF; font-weight: 700; }
    .blog-card__title { font-size: 1.25rem; font-weight: 700; color: #0F1D3B; }
    .blog-card__summary { color: #51617A; font-size: 0.98rem; }
    .blog-card__footer { padding: 0 1.8rem 1.8rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; color: #51617A; font-size: 0.9rem; }
    .blog-card__footer { padding: 0 1.8rem 1.8rem; display: flex; align-items: center; justify-content: space-between; color: #51617A; font-size: 0.9rem; }
    .btn-like { background: transparent; border: none; color: #51617A; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; cursor: pointer; transition: color 0.2s ease; }
    .btn-like .bi { font-size: 1.1rem; }
    .btn-like:hover, .btn-like.liked { color: #e6397f; }

    .btn-share { background: #ffffff; border: 1px solid rgba(60, 141, 255, 0.35); border-radius: 999px; color: #3C8DFF; font-weight: 600; padding: 0.45rem 1.1rem; display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease; }
    .btn-share:hover { background: #3C8DFF; color: #ffffff; box-shadow: 0 8px 22px rgba(60, 141, 255, 0.25); }
    .btn-share--icon { padding: 0.4rem; width: 38px; height: 38px; border-radius: 50%; justify-content: center; }
    .btn-share--icon i { margin: 0; }
    .hero-feature-meta .btn-share { margin-left: auto; }

    .lm-share-menu { background: #ffffff; border-radius: 14px; padding: 0.5rem; min-width: 210px; box-shadow: 0 20px 45px rgba(15,29,59,0.18); z-index: 1080; }
    .lm-share-menu__item { border: none; background: transparent; width: 100%; text-align: left; padding: 0.45rem 0.75rem; border-radius: 10px; font-weight: 600; color: #1B2A4B; display: flex; align-items: center; gap: 0.5rem; transition: background 0.2s ease, color 0.2s ease; }
    .lm-share-menu__item:hover { background: rgba(60, 141, 255, 0.12); color: #0F1D3B; }

    .blog-empty { padding: 6rem 0; text-align: center; color: #51617A; }

    .blog-modal .modal-content { border-radius: 24px; border: none; box-shadow: 0 30px 80px rgba(15,29,59,0.25); }
    .blog-modal .modal-header { border-bottom: none; padding: 1.5rem 1.8rem 0; }
    .blog-modal .modal-body { padding: 0 1.8rem 1.8rem; }
    .blog-modal-media img, .blog-modal-media video, .blog-modal-media iframe { width: 100%; border-radius: 18px; }
    .blog-modal-meta { display: flex; flex-wrap: wrap; gap: 1rem; color: #51617A; font-size: 0.95rem; margin-bottom: 1.5rem; }

    @media (max-width: 991.98px) {
      .blog-hero { padding: 120px 0 60px; }
      .blog-hero h1 { font-size: 2.8rem; }
      .hero-feature-body { padding: 1.75rem; }
      .blog-hero h1 { font-size: 2.75rem; }
      .featured-card__body { padding: 2rem; }
      .blog-card__body { padding: 1.5rem; }
      .blog-card__footer { padding: 0 1.5rem 1.5rem; }
    }

    @media (max-width: 575.98px) {
      .blog-hero h1 { font-size: 2.2rem; }
      .hero-feature-media { min-height: 220px; }
      .blog-card__media img, .blog-card__media video, .blog-card__media iframe { height: 180px; }
    }
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
  <section class="blog-hero">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-5">
          <div class="hero-highlight">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2">LevelMinds Blog</span>
            <h1 class="mb-0">Stories &amp; strategies for modern learning teams</h1>
            <p class="lead mb-0">Insights, playbooks, and stories crafted for school leaders and teachers building the future of learning.</p>
            <div class="d-flex flex-column flex-sm-row gap-3">
              <a href="#blog-categories" class="btn btn-primary btn-lg px-4">Discover Stories</a>
              <a href="#newsletter" class="btn btn-outline-primary btn-lg px-4">Subscribe</a>
            </div>
      <div class="row justify-content-center">
        <div class="col-lg-9 text-center">
          <h1 class="mb-3">LevelMinds Blog</h1>
          <p class="lead mb-4">Insights, playbooks, and stories crafted for school leaders and teachers building the future of learning.</p>
          <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
            <a href="#blog-categories" class="btn btn-primary btn-lg px-4">Discover Stories</a>
            <a href="#newsletter" class="btn btn-outline-primary btn-lg px-4">Subscribe</a>
          </div>
        </div>
        <div class="col-lg-7">
          <?php if ($error): ?>
            <div class="hero-empty">
              <i class="bi bi-exclamation-triangle display-5 d-block mb-3"></i>
              <p class="lead mb-0"><?php echo htmlspecialchars($error); ?></p>
            </div>
          <?php elseif (!$featured): ?>
            <div class="hero-empty">
              <i class="bi bi-journal-text display-5 d-block mb-3"></i>
              <p class="lead mb-0">No blog posts yet. Check back soon.</p>
            </div>
          <?php else: ?>
            <?php
              $featuredId = (int) ($featured['id'] ?? 0);
              $featuredCategory = $hasCategoryColumn ? ($featured['category'] ?? 'general') : 'general';
              $featuredLabel = $audienceLabels[$featuredCategory] ?? ucfirst($featuredCategory);
              $featuredViews = (int)($featured['views'] ?? 0);
              $featuredLikes = (int)($featured['likes'] ?? 0);
              $featuredDate = formatBlogDate($featured['created_at'] ?? null);
              $featuredSummaryRaw = (string) ($featured['summary'] ?? '');
              $featuredContentRaw = (string) ($featured['content'] ?? '');
              $featuredSummaryPlain = decodeBlogPlain($featuredSummaryRaw);
              $featuredShareUrl = buildBlogShareUrl($featuredId);
              $featuredShareSummary = $featuredSummaryPlain !== '' ? $featuredSummaryPlain : decodeBlogPlain($featuredContentRaw);
              $featuredSummaryEncoded = encodeBlogDataAttr($featuredSummaryRaw);
              $featuredContentEncoded = encodeBlogDataAttr($featuredContentRaw);
              $featuredShareSummaryEncoded = encodeBlogDataAttr($featuredShareSummary);
              $featuredSummaryPlain = trim(strip_tags($featured['summary'] ?? ''));
              $isFeaturedExternal = $featured['media_type'] === 'video' ? preg_match('/^https?:\/\//i', $featured['media_url']) : false;
            ?>
            <article class="hero-feature">
              <div class="hero-feature-media">
                <?php if ($featured['media_type'] === 'video'): ?>
                  <?php if ($isFeaturedExternal && !preg_match('/\.(mp4|mov|m4v|webm|ogv|ogg)(\?|$)/i', $featured['media_url'])): ?>
                    <iframe src="<?php echo htmlspecialchars($featured['media_url']); ?>" title="<?php echo htmlspecialchars($featured['title']); ?>" allowfullscreen></iframe>
                  <?php else: ?>
                    <video class="w-100 h-100" controls>
                      <source src="<?php echo htmlspecialchars($featured['media_url']); ?>">
                      Your browser does not support the video tag.
                    </video>
                  <?php endif; ?>
                <?php else: ?>
                  <img src="<?php echo htmlspecialchars($featured['media_url']); ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>">
                <?php endif; ?>
              </div>
              <div class="hero-feature-body">
                <div>
                  <span class="badge bg-primary-subtle text-primary text-uppercase fw-semibold px-3 py-2"><?php echo htmlspecialchars($featuredLabel); ?></span>
                </div>
                <h2 class="mb-0"><?php echo htmlspecialchars($featured['title']); ?></h2>
                <p class="lead text-muted mb-0"><?php echo htmlspecialchars($featuredSummaryPlain); ?></p>
                <div class="hero-feature-meta">
                  <span><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($featured['author']); ?></span>
                  <span><i class="bi bi-calendar-event"></i> <?php echo $featuredDate; ?></span>
                  <span><i class="bi bi-eye"></i> <span data-views-for="<?php echo $featuredId; ?>"><?php echo number_format($featuredViews); ?></span> views</span>
                  <button class="btn-share" type="button"
                    data-share-btn
                    data-post-id="<?php echo $featuredId; ?>"
                    data-share-url="<?php echo htmlspecialchars($featuredShareUrl, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-share-title="<?php echo htmlspecialchars($featured['title']); ?>"
                    data-share-summary="<?php echo htmlspecialchars($featuredShareSummary, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-share-summary-b64="<?php echo htmlspecialchars($featuredShareSummaryEncoded, ENT_QUOTES | ENT_SUBSTITUTE); ?>">
                    <i class="bi bi-share"></i> Share
                  </button>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3">
                  <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#blogModal"
                    data-id="<?php echo $featuredId; ?>"
                  <span><i class="bi bi-eye"></i> <span data-views-for="<?php echo (int)$featured['id']; ?>"><?php echo number_format($featuredViews); ?></span> views</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3">
                  <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#blogModal"
                    data-id="<?php echo (int)$featured['id']; ?>"
                    data-title="<?php echo htmlspecialchars($featured['title']); ?>"
                    data-author="<?php echo htmlspecialchars($featured['author']); ?>"
                    data-date="<?php echo $featuredDate; ?>"
                    data-category="<?php echo htmlspecialchars($featuredCategory); ?>"
                    data-category-label="<?php echo htmlspecialchars($featuredLabel); ?>"
                    data-views="<?php echo $featuredViews; ?>"
                    data-likes="<?php echo $featuredLikes; ?>"
                    data-summary="<?php echo htmlspecialchars($featuredSummaryRaw, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-summary-b64="<?php echo htmlspecialchars($featuredSummaryEncoded, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-content="<?php echo htmlspecialchars($featuredContentRaw, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-content-b64="<?php echo htmlspecialchars($featuredContentEncoded, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-media-type="<?php echo htmlspecialchars($featured['media_type']); ?>"
                    data-media-url="<?php echo htmlspecialchars($featured['media_url'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-share-url="<?php echo htmlspecialchars($featuredShareUrl, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-share-title="<?php echo htmlspecialchars($featured['title']); ?>"
                    data-share-summary="<?php echo htmlspecialchars($featuredShareSummary, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-share-summary-b64="<?php echo htmlspecialchars($featuredShareSummaryEncoded, ENT_QUOTES | ENT_SUBSTITUTE); ?>">
                    Read full story
                  </button>
                  <button class="btn-like" type="button" data-like-btn data-post-id="<?php echo $featuredId; ?>">
                    data-summary="<?php echo htmlspecialchars($featured['summary'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-content="<?php echo htmlspecialchars($featured['content'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-media-type="<?php echo htmlspecialchars($featured['media_type']); ?>"
                    data-media-url="<?php echo htmlspecialchars($featured['media_url'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>">
                    Read full story
                  </button>
                  <button class="btn-like" type="button" data-like-btn data-post-id="<?php echo (int)$featured['id']; ?>">
                    <i class="bi bi-heart"></i>
                    <span data-like-count><?php echo number_format($featuredLikes); ?></span>
                  </button>
                </div>
              </div>
            </article>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <?php if ($featured && !empty($groupedPosts)): ?>
  <section class="py-5" id="blog-categories">
    <div class="container">
      <div class="row align-items-center mb-4">
        <div class="col-lg-8">
          <h2 class="blog-section-heading mb-2">Discover blogs by categories</h2>
          <p class="text-muted mb-0">Switch between content designed for teachers and school leaders.</p>
        </div>
      </div>
      <div class="d-flex blog-filter" role="tablist">
        <button type="button" class="btn active" data-filter="all">Latest articles</button>
        <?php foreach ($orderedCategories as $categoryKey): ?>
          <?php if (empty($groupedPosts[$categoryKey])) { continue; }
          $label = $audienceLabels[$categoryKey] ?? ucfirst($categoryKey); ?>
          <button type="button" class="btn" data-filter="<?php echo htmlspecialchars($categoryKey); ?>"><?php echo htmlspecialchars($label); ?></button>
        <?php endforeach; ?>
      </div>
      <?php $renderedPostIds = []; ?>
      <div class="row g-4 mt-2" id="blogCards">
        <?php foreach ($orderedCategories as $categoryKey): ?>
          <?php if (empty($groupedPosts[$categoryKey])) { continue; }
          $label = $audienceLabels[$categoryKey] ?? ucfirst($categoryKey); ?>
          <?php foreach ($groupedPosts[$categoryKey] as $post): ?>
            <?php
              $postId = (int)$post['id'];
              if ($postId > 0 && isset($renderedPostIds[$postId])) {
                  continue;
              }
              if ($postId > 0) {
                  $renderedPostIds[$postId] = true;
              }
              $postViews = (int)($post['views'] ?? 0);
              $postLikes = (int)($post['likes'] ?? 0);
              $postDate = formatBlogDate($post['created_at'] ?? null);
              $postSummary = (string) ($post['summary'] ?? '');
              $postContentRaw = (string) ($post['content'] ?? '');
              $postSummaryPlain = decodeBlogPlain($postSummary);
              $postShareUrl = buildBlogShareUrl($postId);
              $postShareSummary = $postSummaryPlain !== '' ? $postSummaryPlain : decodeBlogPlain($postContentRaw);
              $postSummaryEncoded = encodeBlogDataAttr($postSummary);
              $postContentEncoded = encodeBlogDataAttr($postContentRaw);
              $postShareSummaryEncoded = encodeBlogDataAttr($postShareSummary);
            ?>
            <div class="col-xl-4 col-md-6" data-blog-card data-category="<?php echo htmlspecialchars($categoryKey); ?>">
              <article class="blog-card h-100 d-flex flex-column">
                <div class="blog-card__media">
                  <?php if ($post['media_type'] === 'video'): ?>
                    <?php $isExternalVideo = preg_match('/^https?:\/\//i', $post['media_url']); ?>
                    <?php if ($isExternalVideo && !preg_match('/\.(mp4|mov|m4v|webm|ogv|ogg)(\?|$)/i', $post['media_url'])): ?>
                      <div class="ratio ratio-16x9">
                        <iframe src="<?php echo htmlspecialchars($post['media_url']); ?>" title="<?php echo htmlspecialchars($post['title']); ?>" allowfullscreen></iframe>
                      </div>
                    <?php else: ?>
                      <video class="w-100" controls style="object-fit: cover; height: 220px;">
                        <source src="<?php echo htmlspecialchars($post['media_url']); ?>">
                        Your browser does not support the video tag.
                      </video>
                    <?php endif; ?>
                  <?php else: ?>
                    <img src="<?php echo htmlspecialchars($post['media_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; height: 220px; object-fit: cover;">
                  <?php endif; ?>
                </div>
                <div class="blog-card__body">
                  <span class="blog-card__category"><?php echo htmlspecialchars($label); ?></span>
                  <h3 class="blog-card__title"><?php echo htmlspecialchars($post['title']); ?></h3>
                  <p class="blog-card__summary mb-0"><?php echo htmlspecialchars($postSummaryPlain); ?></p>
                  <div class="d-flex flex-wrap gap-3 text-muted small">
                    <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-person"></i> <?php echo htmlspecialchars($post['author']); ?></span>
                    <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-calendar-event"></i> <?php echo $postDate; ?></span>
                  </div>
                  <div>
                    <button type="button" class="btn btn-outline-primary px-3" data-bs-toggle="modal" data-bs-target="#blogModal"
                      data-id="<?php echo $postId; ?>"
                      data-title="<?php echo htmlspecialchars($post['title']); ?>"
                      data-author="<?php echo htmlspecialchars($post['author']); ?>"
                      data-date="<?php echo $postDate; ?>"
                      data-category="<?php echo htmlspecialchars($categoryKey); ?>"
                      data-category-label="<?php echo htmlspecialchars($label); ?>"
                      data-views="<?php echo $postViews; ?>"
                      data-likes="<?php echo $postLikes; ?>"
                      data-summary="<?php echo htmlspecialchars($postSummary, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                      data-summary-b64="<?php echo htmlspecialchars($postSummaryEncoded, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                      data-content="<?php echo htmlspecialchars($postContentRaw, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                      data-content-b64="<?php echo htmlspecialchars($postContentEncoded, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                      data-media-type="<?php echo htmlspecialchars($post['media_type']); ?>"
                      data-media-url="<?php echo htmlspecialchars($post['media_url'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                      data-share-url="<?php echo htmlspecialchars($postShareUrl, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                      data-share-title="<?php echo htmlspecialchars($post['title']); ?>"
                      data-share-summary="<?php echo htmlspecialchars($postShareSummary, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                      data-share-summary-b64="<?php echo htmlspecialchars($postShareSummaryEncoded, ENT_QUOTES | ENT_SUBSTITUTE); ?>">
                      Read article
                    </button>
                  </div>
                </div>
                <div class="blog-card__footer">
                  <span class="d-inline-flex align-items-center gap-1 text-muted"><i class="bi bi-eye"></i> <span data-views-for="<?php echo $postId; ?>"><?php echo number_format($postViews); ?></span></span>
                  <div class="d-flex align-items-center gap-2">
                    <button class="btn-share btn-share--icon" type="button"
                      data-share-btn
                      data-post-id="<?php echo $postId; ?>"
                      data-share-url="<?php echo htmlspecialchars($postShareUrl, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                      data-share-title="<?php echo htmlspecialchars($post['title']); ?>"
                      data-share-summary="<?php echo htmlspecialchars($postShareSummary, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                      data-share-summary-b64="<?php echo htmlspecialchars($postShareSummaryEncoded, ENT_QUOTES | ENT_SUBSTITUTE); ?>">
                      <i class="bi bi-share"></i>
                    </button>
                    <button class="btn-like" type="button" data-like-btn data-post-id="<?php echo $postId; ?>">
                      <i class="bi bi-heart"></i>
                      <span data-like-count><?php echo number_format($postLikes); ?></span>
                    </button>
  <section class="py-5">
    <div class="container">
      <?php if ($error): ?>
        <div class="alert alert-danger text-center mb-0"><?php echo htmlspecialchars($error); ?></div>
      <?php elseif (!$featured): ?>
        <div class="blog-empty">
          <i class="bi bi-journal-text display-5 d-block mb-3"></i>
          <p class="lead">No blog posts yet. Check back soon.</p>
        </div>
      <?php else: ?>
        <?php
          $featuredCategory = $featured['category'] ?? 'general';
          $featuredLabel = $audienceLabels[$featuredCategory] ?? ucfirst($featuredCategory);
          $featuredViews = (int)($featured['views'] ?? 0);
          $featuredLikes = (int)($featured['likes'] ?? 0);
          $featuredDate = formatBlogDate($featured['created_at'] ?? null);
          $featuredSummaryPlain = trim(strip_tags($featured['summary'] ?? ''));
        ?>
        <article class="featured-card mb-5">
          <div class="row g-0 align-items-stretch">
            <div class="col-lg-6 featured-card__media">
              <?php if ($featured['media_type'] === 'video'): ?>
                <?php $isFeaturedExternal = preg_match('/^https?:\/\//i', $featured['media_url']); ?>
                <?php if ($isFeaturedExternal && !preg_match('/\.(mp4|mov|m4v|webm|ogv|ogg)(\?|$)/i', $featured['media_url'])): ?>
                  <div class="ratio ratio-16x9 h-100">
                    <iframe src="<?php echo htmlspecialchars($featured['media_url']); ?>" title="<?php echo htmlspecialchars($featured['title']); ?>" allowfullscreen></iframe>
                  </div>
                <?php else: ?>
                  <video class="w-100 h-100" controls style="object-fit: cover;">
                    <source src="<?php echo htmlspecialchars($featured['media_url']); ?>">
                    Your browser does not support the video tag.
                  </video>
                <?php endif; ?>
              <?php else: ?>
                <img src="<?php echo htmlspecialchars($featured['media_url']); ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>" class="w-100 h-100" style="object-fit: cover;">
              <?php endif; ?>
            </div>
            <div class="col-lg-6 d-flex align-items-stretch">
              <div class="featured-card__body d-flex flex-column gap-3 justify-content-center">
                <div>
                  <span class="badge bg-primary-subtle text-primary"><?php echo htmlspecialchars($featuredLabel); ?></span>
                </div>
                <h2 class="mb-0"><?php echo htmlspecialchars($featured['title']); ?></h2>
                <p class="lead text-muted mb-0"><?php echo htmlspecialchars($featuredSummaryPlain); ?></p>
                <div class="featured-meta">
                  <span><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($featured['author']); ?></span>
                  <span><i class="bi bi-calendar-event"></i> <?php echo $featuredDate; ?></span>
                  <span><i class="bi bi-eye"></i> <span data-views-for="<?php echo (int)$featured['id']; ?>"><?php echo number_format($featuredViews); ?></span> views</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3">
                  <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#blogModal"
                    data-id="<?php echo (int)$featured['id']; ?>"
                    data-title="<?php echo htmlspecialchars($featured['title']); ?>"
                    data-author="<?php echo htmlspecialchars($featured['author']); ?>"
                    data-date="<?php echo $featuredDate; ?>"
                    data-category="<?php echo htmlspecialchars($featuredCategory); ?>"
                    data-category-label="<?php echo htmlspecialchars($featuredLabel); ?>"
                    data-views="<?php echo $featuredViews; ?>"
                    data-likes="<?php echo $featuredLikes; ?>"
                    data-summary="<?php echo htmlspecialchars($featured['summary'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-content="<?php echo htmlspecialchars($featured['content'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                    data-media-type="<?php echo htmlspecialchars($featured['media_type']); ?>"
                    data-media-url="<?php echo htmlspecialchars($featured['media_url'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>">
                    Read full story
                  </button>
                  <button class="btn-like" type="button" data-like-btn data-post-id="<?php echo (int)$featured['id']; ?>">
                    <i class="bi bi-heart"></i>
                    <span data-like-count><?php echo number_format($featuredLikes); ?></span>
                  </button>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php elseif ($featured): ?>
  <section class="py-5" id="blog-categories">
    <div class="container">
      <div class="row justify-content-center text-center">
        <div class="col-lg-7">
          <h2 class="blog-section-heading mb-3">More stories coming soon</h2>
          <p class="text-muted mb-0">We're crafting fresh insights for teachers and school leaders. Check back shortly for new additions.</p>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <div class="modal fade blog-modal" id="blogModal" tabindex="-1" aria-labelledby="blogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header align-items-start">
          <div>
            <span class="badge bg-primary-subtle text-primary" data-modal-category></span>
            <h2 class="modal-title h3 mt-2 mb-0" id="blogModalLabel" data-post-title>Blog post</h2>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="blog-modal-media mb-4" data-post-media></div>
          <div class="blog-modal-meta">
            <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-person-circle"></i> <span data-post-author></span></span>
            <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-calendar-event"></i> <span data-post-date></span></span>
            <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-eye"></i> <span data-post-views>0</span> views</span>
            <div class="ms-auto d-flex align-items-center gap-2">
              <button class="btn-share btn-share--icon" type="button"
                data-share-btn
                data-modal-share
                data-share-url=""
                data-share-title=""
                data-share-summary="">
                <i class="bi bi-share"></i>
              </button>
              <button class="btn-like" type="button" data-like-btn data-modal-like data-post-id="">
                <i class="bi bi-heart"></i>
                <span data-like-count>0</span>
              </button>
            </div>
          </div>
          <p class="lead text-muted" data-post-summary></p>
          <div data-post-content class="mt-3"></div>
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
              </div>
            </div>
          </div>
        </article>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($featured && !empty($groupedPosts)): ?>
  <section class="py-5" id="blog-categories">
    <div class="container">
      <div class="row align-items-center mb-4">
        <div class="col-lg-8">
          <h2 class="blog-section-heading mb-2">Discover blogs by categories</h2>
          <p class="text-muted mb-0">Switch between content designed for teachers and school leaders.</p>
        </div>
      </div>
      <div class="d-flex blog-filter" role="tablist">
        <button type="button" class="btn active" data-filter="all">Latest articles</button>
        <?php foreach ($orderedCategories as $categoryKey): ?>
          <?php if (empty($groupedPosts[$categoryKey])) { continue; }
          $label = $audienceLabels[$categoryKey] ?? ucfirst($categoryKey); ?>
          <button type="button" class="btn" data-filter="<?php echo htmlspecialchars($categoryKey); ?>"><?php echo htmlspecialchars($label); ?></button>
        <?php endforeach; ?>
      </div>
      <div class="row g-4 mt-2" id="blogCards">
        <?php foreach ($orderedCategories as $categoryKey): ?>
          <?php if (empty($groupedPosts[$categoryKey])) { continue; }
          $label = $audienceLabels[$categoryKey] ?? ucfirst($categoryKey); ?>
          <?php foreach ($groupedPosts[$categoryKey] as $post): ?>
            <?php
              $postId = (int)$post['id'];
              $postViews = (int)($post['views'] ?? 0);
              $postLikes = (int)($post['likes'] ?? 0);
              $postDate = formatBlogDate($post['created_at'] ?? null);
              $postSummary = $post['summary'] ?? '';
              $postSummaryPlain = trim(strip_tags($postSummary));
            ?>
            <div class="col-xl-4 col-md-6" data-blog-card data-category="<?php echo htmlspecialchars($categoryKey); ?>">
              <article class="blog-card h-100 d-flex flex-column">
                <div class="blog-card__media">
                  <?php if ($post['media_type'] === 'video'): ?>
                    <?php $isExternalVideo = preg_match('/^https?:\/\//i', $post['media_url']); ?>
                    <?php if ($isExternalVideo && !preg_match('/\.(mp4|mov|m4v|webm|ogv|ogg)(\?|$)/i', $post['media_url'])): ?>
                      <div class="ratio ratio-16x9">
                        <iframe src="<?php echo htmlspecialchars($post['media_url']); ?>" title="<?php echo htmlspecialchars($post['title']); ?>" allowfullscreen></iframe>
                      </div>
                    <?php else: ?>
                      <video class="w-100" controls style="object-fit: cover; height: 220px;">
                        <source src="<?php echo htmlspecialchars($post['media_url']); ?>">
                        Your browser does not support the video tag.
                      </video>
                    <?php endif; ?>
                  <?php else: ?>
                    <img src="<?php echo htmlspecialchars($post['media_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; height: 220px; object-fit: cover;">
                  <?php endif; ?>
                </div>
                <div class="blog-card__body">
                  <span class="blog-card__category"><?php echo htmlspecialchars($label); ?></span>
                  <h3 class="blog-card__title"><?php echo htmlspecialchars($post['title']); ?></h3>
                  <p class="blog-card__summary mb-0"><?php echo htmlspecialchars($postSummaryPlain); ?></p>
                  <div class="d-flex flex-wrap gap-3 text-muted small">
                    <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-person"></i> <?php echo htmlspecialchars($post['author']); ?></span>
                    <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-calendar-event"></i> <?php echo $postDate; ?></span>
                  </div>
                  <div>
                    <button type="button" class="btn btn-outline-primary px-3" data-bs-toggle="modal" data-bs-target="#blogModal"
                      data-id="<?php echo $postId; ?>"
                      data-title="<?php echo htmlspecialchars($post['title']); ?>"
                      data-author="<?php echo htmlspecialchars($post['author']); ?>"
                      data-date="<?php echo $postDate; ?>"
                      data-category="<?php echo htmlspecialchars($categoryKey); ?>"
                      data-category-label="<?php echo htmlspecialchars($label); ?>"
                      data-views="<?php echo $postViews; ?>"
                      data-likes="<?php echo $postLikes; ?>"
                      data-summary="<?php echo htmlspecialchars($postSummary, ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                      data-content="<?php echo htmlspecialchars($post['content'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>"
                      data-media-type="<?php echo htmlspecialchars($post['media_type']); ?>"
                      data-media-url="<?php echo htmlspecialchars($post['media_url'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>">
                      Read article
                    </button>
                  </div>
                </div>
                <div class="blog-card__footer">
                  <span class="d-inline-flex align-items-center gap-1 text-muted"><i class="bi bi-eye"></i> <span data-views-for="<?php echo $postId; ?>"><?php echo number_format($postViews); ?></span></span>
                  <button class="btn-like" type="button" data-like-btn data-post-id="<?php echo $postId; ?>">
                    <i class="bi bi-heart"></i>
                    <span data-like-count><?php echo number_format($postLikes); ?></span>
                  </button>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php elseif ($featured): ?>
  <section class="py-5" id="blog-categories">
    <div class="container">
      <div class="row justify-content-center text-center">
        <div class="col-lg-7">
          <h2 class="blog-section-heading mb-3">More stories coming soon</h2>
          <p class="text-muted mb-0">We're crafting fresh insights for teachers and school leaders. Check back shortly for new additions.</p>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <div class="modal fade blog-modal" id="blogModal" tabindex="-1" aria-labelledby="blogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header align-items-start">
          <div>
            <span class="badge bg-primary-subtle text-primary" data-modal-category></span>
            <h2 class="modal-title h3 mt-2 mb-0" id="blogModalLabel" data-post-title>Blog post</h2>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="blog-modal-media mb-4" data-post-media></div>
          <div class="blog-modal-meta">
            <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-person-circle"></i> <span data-post-author></span></span>
            <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-calendar-event"></i> <span data-post-date></span></span>
            <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-eye"></i> <span data-post-views>0</span> views</span>
            <button class="btn-like ms-auto" type="button" data-like-btn data-modal-like data-post-id="">
              <i class="bi bi-heart"></i>
              <span data-like-count>0</span>
            </button>
          </div>
          <p class="lead text-muted" data-post-summary></p>
          <div data-post-content class="mt-3"></div>
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
  <script src="assets/js/custom.js?v=4"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const filterButtons = document.querySelectorAll('[data-filter]');
      const cards = document.querySelectorAll('[data-blog-card]');

      filterButtons.forEach(function (button) {
        button.addEventListener('click', function () {
          filterButtons.forEach(btn => btn.classList.remove('active'));
          button.classList.add('active');
          const filter = button.getAttribute('data-filter');
          cards.forEach(function (card) {
            const category = card.getAttribute('data-category');
            const matches = filter === 'all' || category === filter;
            card.style.display = matches ? '' : 'none';
          });
        });
      });

      const formatNumber = (value) => {
        const num = Number(value);
        return Number.isFinite(num) ? num.toLocaleString() : String(value || '0');
      };

      const modalEl = document.getElementById('blogModal');
      if (!modalEl) {
        return;
      }

      const decodeHtml = (value) => {
        if (value == null) {
          return '';
        }
        const textarea = document.createElement('textarea');
        textarea.innerHTML = String(value);
        return textarea.value;
      };

      const decodeBase64 = (value) => {
        if (!value) {
          return '';
        }
        try {
          const binary = atob(value);
          if (window.TextDecoder) {
            const decoder = new TextDecoder('utf-8', { fatal: false });
            const bytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
            return decoder.decode(bytes);
          }
          return decodeURIComponent(Array.prototype.map.call(binary, function (char) {
            return '%' + ('00' + char.charCodeAt(0).toString(16)).slice(-2);
          }).join(''));
        } catch (error) {
          return '';
        }
      };

      const encodeBase64 = (value) => {
        if (!value) {
          return '';
        }
        try {
          if (window.TextEncoder) {
            const encoder = new TextEncoder();
            const bytes = encoder.encode(value);
            let binary = '';
            bytes.forEach((byte) => {
              binary += String.fromCharCode(byte);
            });
            return btoa(binary);
          }
          return btoa(unescape(encodeURIComponent(value)));
        } catch (error) {
          return '';
        }
      };

      const readDatasetContent = (dataset, base64Key, fallbackKey) => {
        if (base64Key && dataset[base64Key]) {
          const decoded = decodeBase64(dataset[base64Key]);
          if (decoded !== '') {
            return decoded;
          }
        }
        if (fallbackKey && dataset[fallbackKey]) {
          return decodeHtml(dataset[fallbackKey]);
        }
        return '';
      };

      const buildShareUrl = (id) => {
        const postId = String(id || '').trim();
        const origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
        if (!postId) {
          return origin + window.location.pathname;
        }
        return origin.replace(/\/$/, '') + '/blogs.php?post=' + postId;

      const decode = (value) => {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = value == null ? '' : String(value);
        return textarea.value;
      };

      const renderMedia = (container, type, url, title) => {
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
            iframe.title = title || 'Video';
            iframe.allowFullscreen = true;
            iframe.className = 'w-100 rounded';
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
      };

      modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) {
          return;
        }

        const dataset = trigger.dataset;
        const postId = dataset.id || '';
        const title = dataset.title || '';
        const author = dataset.author || '';
        const date = dataset.date || '';
        const categoryLabel = dataset.categoryLabel || '';
        const summary = readDatasetContent(dataset, 'summaryB64', 'summary');
        const content = readDatasetContent(dataset, 'contentB64', 'content');
        const mediaType = (dataset.mediaType || '').toLowerCase();
        const mediaUrl = dataset.mediaUrl || '';
        const shareUrl = dataset.shareUrl || '';
        let shareSummary = readDatasetContent(dataset, 'shareSummaryB64', 'shareSummary');
        if (!shareSummary) {
          shareSummary = summary;
        }

      modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) {
          return;
        }

        const dataset = trigger.dataset;
        const postId = dataset.id || '';
        const title = dataset.title || '';
        const author = dataset.author || '';
        const date = dataset.date || '';
        const categoryLabel = dataset.categoryLabel || '';
        const summary = decode(dataset.summary || '');
        const content = decode(dataset.content || '');
        const mediaType = (dataset.mediaType || '').toLowerCase();
        const mediaUrl = dataset.mediaUrl || '';
        const likes = parseInt(dataset.likes || '0', 10);
        const views = parseInt(dataset.views || '0', 10);

        modalEl.dataset.postId = postId;
        modalEl.__relatedTrigger = trigger;

        const titleEl = modalEl.querySelector('[data-post-title]');
        const authorEl = modalEl.querySelector('[data-post-author]');
        const dateEl = modalEl.querySelector('[data-post-date]');
        const viewsEl = modalEl.querySelector('[data-post-views]');
        const summaryEl = modalEl.querySelector('[data-post-summary]');
        const contentEl = modalEl.querySelector('[data-post-content]');
        const mediaEl = modalEl.querySelector('[data-post-media]');
        const categoryEl = modalEl.querySelector('[data-modal-category]');
        const likeBtn = modalEl.querySelector('[data-modal-like]');
        const shareBtn = modalEl.querySelector('[data-modal-share]');

        if (titleEl) titleEl.textContent = title;
        if (authorEl) authorEl.textContent = author || 'LevelMinds Team';
        if (dateEl) dateEl.textContent = date;
        if (viewsEl) viewsEl.textContent = formatNumber(views);
        if (summaryEl) summaryEl.textContent = summary;
        if (contentEl) contentEl.innerHTML = content;
        if (categoryEl) {
          categoryEl.textContent = categoryLabel;
          categoryEl.style.display = categoryLabel ? '' : 'none';
        }

        renderMedia(mediaEl, mediaType, mediaUrl, title);

        if (likeBtn) {
          likeBtn.setAttribute('data-post-id', postId);
          const countEl = likeBtn.querySelector('[data-like-count]');
          if (countEl) countEl.textContent = formatNumber(likes);
          if (window.LM && typeof window.LM.hydrateLikes === 'function') {
            window.LM.hydrateLikes();
          }
        }

        if (shareBtn) {
          shareBtn.setAttribute('data-post-id', postId);
          const computedShareUrl = shareUrl || buildShareUrl(postId);
          const computedShareSummary = shareSummary || summary;
          shareBtn.setAttribute('data-share-url', computedShareUrl);
          shareBtn.setAttribute('data-share-title', title);
          shareBtn.setAttribute('data-share-summary', computedShareSummary);
          const encoded = encodeBase64(computedShareSummary);
          if (encoded) {
            shareBtn.setAttribute('data-share-summary-b64', encoded);
          } else {
            shareBtn.removeAttribute('data-share-summary-b64');
          }
        }
      });

      modalEl.addEventListener('hidden.bs.modal', function () {
        modalEl.__relatedTrigger = null;
        if (window.history && typeof window.history.replaceState === 'function') {
          const current = new URL(window.location.href);
          if (current.searchParams.has('post')) {
            current.searchParams.delete('post');
            const nextSearch = current.searchParams.toString();
            const nextUrl = nextSearch ? `${current.pathname}?${nextSearch}` : current.pathname;
            window.history.replaceState({}, '', nextUrl);
          }
        }
      });

      modalEl.addEventListener('shown.bs.modal', function () {
        const trigger = modalEl.__relatedTrigger;
        const postId = modalEl.dataset.postId;
        if (!postId || !window.LM || typeof window.LM.trackView !== 'function') {
          return;
        }
        const initialViews = trigger ? parseInt(trigger.getAttribute('data-views') || '0', 10) : 0;
        window.LM.trackView(postId, {
          initialViews,
          updateUI: function (latest) {
            const viewsEl = modalEl.querySelector('[data-post-views]');
            if (viewsEl) viewsEl.textContent = formatNumber(latest);
            document.querySelectorAll('[data-views-for="' + postId + '"]').forEach(function (el) {
              el.textContent = formatNumber(latest);
            });
            if (trigger) {
              trigger.setAttribute('data-views', String(latest));
            }
          }
        });
      });

      });

      modalEl.addEventListener('hidden.bs.modal', function () {
        modalEl.__relatedTrigger = null;
      });

      modalEl.addEventListener('shown.bs.modal', function () {
        const trigger = modalEl.__relatedTrigger;
        const postId = modalEl.dataset.postId;
        if (!postId || !window.LM || typeof window.LM.trackView !== 'function') {
          return;
        }
        const initialViews = trigger ? parseInt(trigger.getAttribute('data-views') || '0', 10) : 0;
        window.LM.trackView(postId, {
          initialViews,
          updateUI: function (latest) {
            const viewsEl = modalEl.querySelector('[data-post-views]');
            if (viewsEl) viewsEl.textContent = formatNumber(latest);
            document.querySelectorAll('[data-views-for="' + postId + '"]').forEach(function (el) {
              el.textContent = formatNumber(latest);
            });
            if (trigger) {
              trigger.setAttribute('data-views', String(latest));
            }
          }
        });
      });

      document.addEventListener('lm:likes-updated', function (event) {
        const detail = event.detail || {};
        const postId = detail.postId;
        if (!postId) return;
        const likes = typeof detail.likes === 'number' ? detail.likes : null;
        document.querySelectorAll('[data-like-btn][data-post-id="' + postId + '"]').forEach(function (btn) {
          const countEl = btn.querySelector('[data-like-count]');
          if (countEl && likes !== null) {
            countEl.textContent = formatNumber(likes);
          }
        });
        document.querySelectorAll('[data-id="' + postId + '"]').forEach(function (trigger) {
          if (likes !== null) {
            trigger.setAttribute('data-likes', String(likes));
          }
        });
      });

      const params = new URLSearchParams(window.location.search);
      const initialPost = params.get('post');
      if (initialPost) {
        const opener = document.querySelector('[data-bs-target="#blogModal"][data-id="' + initialPost + '"]');
        if (opener) {
          setTimeout(function () {
            opener.click();
          }, 350);
        }
      }
    });
  </script>
</body>
</html>







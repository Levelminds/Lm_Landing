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

function lm_text_has(string $text, array $needles): bool
{
    foreach ($needles as $needle) {
        if ($needle !== '' && strpos($text, $needle) !== false) {
            return true;
        }
    }
    return false;
}

function lm_blog_categories(array $post): array
{
    $text = strtolower(html_entity_decode(($post['title'] ?? '') . ' ' . ($post['summary'] ?? '') . ' ' . ($post['content'] ?? '')));
    $categories = [];

    if (lm_text_has($text, ['teacher', 'classroom', 'educator'])) {
        $categories[] = 'teachers';
    }
    if (lm_text_has($text, ['school', 'district', 'principal'])) {
        $categories[] = 'schools';
    }
    if (lm_text_has($text, ['career', 'growth', 'job', 'hiring'])) {
        $categories[] = 'career-growth';
    }
    if (lm_text_has($text, ['product', 'feature', 'release'])) {
        $categories[] = 'product-updates';
    }
    if (lm_text_has($text, ['community', 'story', 'stories'])) {
        $categories[] = 'community-stories';
    }
    if (!$categories) {
        $categories[] = 'general-insights';
    }

    return array_values(array_unique($categories));
}

function lm_category_label(string $slug): string
{
    return [
        'general-insights' => 'General Insights',
        'teachers' => 'Teachers',
        'schools' => 'Schools',
        'career-growth' => 'Career Growth',
        'product-updates' => 'Product Updates',
        'community-stories' => 'Community Stories',
    ][$slug] ?? 'General Insights';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LevelMinds Blog | Stories & Strategies for Modern Learning Teams</title>
  <meta name="description" content="Insights, playbooks, and stories crafted for school leaders and teachers building the future of learning.">
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
  <link href="assets/css/custom.css" rel="stylesheet">

  <style>
    :root {
      --lm-navy: #0F1D3B;
      --lm-ink: #1B2A4B;
      --lm-muted: #51617A;
      --lm-sky: #E8F1FF;
      --lm-primary: #2F6BFF;
      --lm-primary-soft: rgba(47, 107, 255, 0.12);
    }

    body {
      font-family: 'Public Sans', sans-serif;
      background: #F5FAFF;
      color: var(--lm-ink);
    }

    .fbs__net-navbar {
      position: fixed;
      width: 100%;
      top: 0;
      left: 0;
      z-index: 1030;
      background-color: #FFFFFF !important;
      box-shadow: 0 12px 40px rgba(15, 29, 59, 0.08);
    }

    .fbs__net-navbar .nav-link {
      color: var(--lm-ink) !important;
      font-weight: 500;
    }

    .fbs__net-navbar .nav-link:hover,
    .fbs__net-navbar .nav-link.active {
      color: var(--lm-primary) !important;
    }

    main {
      padding-top: 112px;
    }

    .blog-hero {
      padding: 120px 0 72px;
    }

    .blog-hero__heading {
      font-size: clamp(2.5rem, 5vw, 3.75rem);
      font-weight: 800;
      color: var(--lm-navy);
    }

    .blog-hero__subheading {
      font-size: 1.1rem;
      color: var(--lm-muted);
      line-height: 1.7;
      max-width: 540px;
    }

    .hero-actions .btn {
      border-radius: 50px;
      padding: 0.75rem 2rem;
      font-weight: 600;
    }

    .featured-card {
      background: #fff;
      border-radius: 28px;
      box-shadow: 0 28px 60px rgba(15, 29, 59, 0.12);
      overflow: hidden;
    }

    .featured-card__body {
      padding: 2.5rem;
    }

    .featured-card__title {
      color: var(--lm-navy);
      font-weight: 700;
      font-size: clamp(1.75rem, 3vw, 2.3rem);
    }

    .featured-card__summary {
      color: var(--lm-muted);
      margin-top: 1.25rem;
    }

    .blog-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.4rem 1rem;
      border-radius: 999px;
      background: var(--lm-primary-soft);
      color: var(--lm-primary);
      font-weight: 600;
      letter-spacing: 0.02em;
      font-size: 0.85rem;
      text-transform: uppercase;
    }

    .meta-group {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 1.25rem;
      margin-top: 1.75rem;
      color: var(--lm-muted);
      font-weight: 500;
    }

    .meta-group span i {
      color: var(--lm-primary);
      margin-right: 0.4rem;
    }

    .btn-lm-primary {
      background: var(--lm-primary);
      border-color: var(--lm-primary);
      color: #fff;
      border-radius: 50px;
      font-weight: 600;
      padding: 0.65rem 1.8rem;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-lm-primary:hover,
    .btn-lm-primary:focus {
      background: #1f54d6;
      border-color: #1f54d6;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 14px 40px rgba(47, 107, 255, 0.28);
    }

    .btn-lm-outline {
      border-radius: 50px;
      font-weight: 600;
      padding: 0.65rem 1.8rem;
      border: 1px solid rgba(47, 107, 255, 0.45);
      color: var(--lm-primary);
      background: rgba(47, 107, 255, 0.08);
    }

    .btn-lm-outline:hover,
    .btn-lm-outline:focus {
      border-color: var(--lm-primary);
      background: var(--lm-primary);
      color: #fff;
    }

    .featured-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      margin-top: 2.25rem;
    }

    .like-button,
    .share-button {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      border-radius: 999px;
      border: 1px solid rgba(27, 42, 75, 0.12);
      padding: 0.55rem 1.4rem;
      background: #fff;
      color: var(--lm-ink);
      font-weight: 600;
      transition: all 0.2s ease;
    }

    .like-button:hover,
    .share-button:hover,
    .like-button:focus,
    .share-button:focus {
      border-color: var(--lm-primary);
      color: var(--lm-primary);
      box-shadow: 0 8px 24px rgba(47, 107, 255, 0.18);
    }

    .like-button.liked {
      background: var(--lm-primary);
      border-color: var(--lm-primary);
      color: #fff;
      box-shadow: 0 12px 32px rgba(47, 107, 255, 0.32);
    }

    .blog-section {
      padding: 32px 0 96px;
    }

    .section-title {
      color: var(--lm-navy);
      font-weight: 700;
      font-size: clamp(2rem, 3vw, 2.6rem);
      margin-bottom: 1.5rem;
      text-align: center;
    }

    .section-subtitle {
      color: var(--lm-muted);
      text-align: center;
      max-width: 640px;
      margin: 0 auto 2.5rem;
    }

    .filter-pills {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 0.75rem;
      margin-bottom: 3rem;
    }

    .filter-pills .btn {
      border-radius: 999px;
      border: 1px solid rgba(27, 42, 75, 0.12);
      padding: 0.55rem 1.5rem;
      color: var(--lm-muted);
      font-weight: 600;
      background: #fff;
    }

    .filter-pills .btn.active,
    .filter-pills .btn:hover,
    .filter-pills .btn:focus {
      background: var(--lm-primary);
      color: #fff;
      border-color: var(--lm-primary);
      box-shadow: 0 12px 28px rgba(47, 107, 255, 0.24);
    }

    .blog-card {
      background: #fff;
      border-radius: 24px;
      border: none;
      box-shadow: 0 26px 60px rgba(15, 29, 59, 0.08);
      height: 100%;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .blog-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 40px 90px rgba(15, 29, 59, 0.16);
    }

    .blog-card img,
    .blog-card iframe {
      width: 100%;
      height: 220px;
      object-fit: cover;
    }

    .blog-card__body {
      padding: 1.8rem 1.9rem 1.2rem;
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
    }

    .blog-card__title {
      font-weight: 700;
      color: var(--lm-navy);
      font-size: 1.35rem;
      margin-top: 1rem;
      margin-bottom: 0.65rem;
    }

    .blog-summary {
      color: var(--lm-muted);
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .blog-card__meta {
      margin-top: 1.4rem;
      color: var(--lm-muted);
      font-size: 0.95rem;
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
    }

    .blog-card__meta span i {
      color: var(--lm-primary);
      margin-right: 0.45rem;
    }

    .blog-card__footer {
      padding: 1.2rem 1.9rem 1.8rem;
      border-top: 1px solid rgba(27, 42, 75, 0.08);
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
    }

    .blog-card__footer .actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 0.75rem;
    }

    .blog-card__footer .stats {
      display: flex;
      gap: 1.25rem;
      align-items: center;
      color: var(--lm-muted);
      font-weight: 600;
    }

    .blog-card__footer .stats span {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }

    .newsletter-section {
      padding: 96px 0;
      background: linear-gradient(135deg, rgba(47, 107, 255, 0.08) 0%, rgba(47, 107, 255, 0.18) 100%);
    }

    .newsletter-card {
      background: #fff;
      border-radius: 28px;
      padding: 3.5rem 3rem;
      box-shadow: 0 40px 90px rgba(15, 29, 59, 0.12);
      text-align: center;
      max-width: 760px;
      margin: 0 auto;
    }

    .newsletter-card h2 {
      font-weight: 700;
      font-size: clamp(2rem, 4vw, 2.6rem);
      color: var(--lm-navy);
      margin-bottom: 1.1rem;
    }

    .newsletter-card p {
      color: var(--lm-muted);
      margin-bottom: 2.2rem;
    }

    .newsletter-card form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    @media (min-width: 576px) {
      .newsletter-card form {
        flex-direction: row;
        align-items: center;
      }
    }

    .newsletter-card input {
      border-radius: 999px;
      padding: 0.85rem 1.5rem;
      font-size: 1rem;
      border: 1px solid rgba(27, 42, 75, 0.12);
      flex: 1;
    }

    .newsletter-card button {
      border-radius: 999px;
      padding: 0.9rem 2.4rem;
      font-weight: 600;
    }

    .share-toast {
      position: fixed;
      top: 100px;
      right: 24px;
      background: var(--lm-primary);
      color: #fff;
      padding: 0.75rem 1.25rem;
      border-radius: 12px;
      box-shadow: 0 12px 34px rgba(47, 107, 255, 0.28);
      opacity: 0;
      transform: translateY(-10px);
      pointer-events: none;
      transition: opacity 0.3s ease, transform 0.3s ease;
      z-index: 1090;
    }

    .share-toast.show {
      opacity: 1;
      transform: translateY(0);
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
            <a class="btn btn-nav-outline w-100" href="https://www.staging.levelminds.in" target="_blank" rel="noopener">Login</a>
          </div>
        </div>
      </div>

      <div class="d-flex align-items-center gap-3">
        <div class="header-actions d-none d-lg-flex align-items-center gap-2">
          <a class="btn btn-nav-outline" href="https://www.staging.levelminds.in" target="_blank" rel="noopener">Login</a>
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

  <main>
    <section class="blog-hero" id="hero">
      <div class="container">
        <div class="row g-5 align-items-center">
          <div class="col-lg-5">
            <p class="blog-badge mb-4"><i class="bi bi-stars"></i>LevelMinds Blog</p>
            <h1 class="blog-hero__heading">Stories &amp; strategies for modern learning teams</h1>
            <p class="blog-hero__subheading mt-4">Insights, playbooks, and stories crafted for school leaders and teachers building the future of learning.</p>
            <div class="hero-actions d-flex flex-column flex-md-row gap-3 mt-5">
              <a href="#discover" class="btn btn-lm-primary">Discover Stories</a>
              <a href="#newsletter" class="btn btn-lm-outline">Subscribe</a>
            </div>
          </div>

          <div class="col-lg-7">
            <?php if ($error): ?>
              <div class="alert alert-danger mb-0"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (!$featured): ?>
              <div class="featured-card p-5 text-center">
                <i class="bi bi-journal-text display-4 text-primary"></i>
                <h2 class="mt-3">No stories yet</h2>
                <p class="text-muted">Our editorial team is crafting the first collection of insights. Check back soon for fresh content.</p>
              </div>
            <?php else: ?>
              <?php $featuredCategories = lm_blog_categories($featured); $featuredCategoryLabel = lm_category_label($featuredCategories[0] ?? 'general-insights'); ?>
              <article class="featured-card">
                <div class="row g-0">
                  <div class="col-lg-6">
                    <?php if ($featured['media_type'] === 'video'): ?>
                      <div class="ratio ratio-16x9 h-100">
                        <iframe src="<?php echo htmlspecialchars($featured['media_url']); ?>" title="<?php echo htmlspecialchars($featured['title']); ?>" allowfullscreen></iframe>
                      </div>
                    <?php else: ?>
                      <img src="<?php echo htmlspecialchars($featured['media_url']); ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>" class="w-100 h-100" style="object-fit: cover;">
                    <?php endif; ?>
                  </div>
                  <div class="col-lg-6 d-flex align-items-stretch">
                    <div class="featured-card__body d-flex flex-column">
                      <span class="blog-badge"><?php echo htmlspecialchars($featuredCategoryLabel); ?></span>
                      <h2 class="featured-card__title mt-3"><?php echo htmlspecialchars($featured['title']); ?></h2>
                      <div class="featured-card__summary"><?php echo html_entity_decode($featured['summary']); ?></div>
                      <div class="meta-group">
                        <span><i class="bi bi-person-circle"></i><?php echo htmlspecialchars($featured['author']); ?></span>
                        <span><i class="bi bi-calendar3"></i><?php echo date('M j, Y', strtotime($featured['created_at'])); ?></span>
                        <span><i class="bi bi-eye"></i><?php echo number_format((int) $featured['views']); ?> views</span>
                      </div>
                      <div class="featured-actions mt-auto">
                        <a class="btn btn-lm-primary" href="blog-detail.php?id=<?php echo (int) $featured['id']; ?>">Read full story</a>
                        <button type="button" class="like-button" data-post-id="<?php echo (int) $featured['id']; ?>" data-like-count="<?php echo (int) $featured['likes']; ?>">
                          <i class="bi bi-heart-fill"></i><span class="like-label">Like</span>
                          <span class="like-count"><?php echo number_format((int) $featured['likes']); ?></span>
                        </button>
                        <button type="button" class="share-button" data-share-title="<?php echo htmlspecialchars($featured['title']); ?>" data-share-url="<?php echo htmlspecialchars('https://levelminds.com/blog-detail.php?id=' . (int) $featured['id']); ?>">
                          <i class="bi bi-share-fill"></i>Share
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </article>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <?php if ($featured && !$error): ?>
    <section class="blog-section" id="discover">
      <div class="container">
        <h2 class="section-title">Discover blogs by categories</h2>
        <p class="section-subtitle">Browse the latest articles, tailored insights, and classroom stories curated for learning leaders.</p>

        <div class="filter-pills">
          <button type="button" class="btn active" data-filter="all">Latest articles</button>
          <button type="button" class="btn" data-filter="general-insights">General Insights</button>
          <button type="button" class="btn" data-filter="teachers">Teachers</button>
          <button type="button" class="btn" data-filter="schools">Schools</button>
          <button type="button" class="btn" data-filter="career-growth">Career Growth</button>
          <button type="button" class="btn" data-filter="product-updates">Product Updates</button>
          <button type="button" class="btn" data-filter="community-stories">Community Stories</button>
        </div>

        <div class="row g-4" id="blogGrid">
          <?php if (!$posts): ?>
            <div class="col-12">
              <div class="alert alert-light text-center border-0 shadow-sm py-5">
                <i class="bi bi-hourglass-split display-6 d-block mb-3 text-primary"></i>
                <p class="mb-0 lead">More articles are publishing shortly. Stay tuned!</p>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($posts as $post): ?>
              <?php $categories = lm_blog_categories($post); $primaryCategoryLabel = lm_category_label($categories[0] ?? 'general-insights'); ?>
              <div class="col-xl-4 col-md-6 blog-grid-item" data-categories="<?php echo htmlspecialchars(implode(',', $categories)); ?>">
                <article class="card blog-card h-100">
                  <?php if ($post['media_type'] === 'video'): ?>
                    <div class="ratio ratio-16x9">
                      <iframe src="<?php echo htmlspecialchars($post['media_url']); ?>" title="<?php echo htmlspecialchars($post['title']); ?>" allowfullscreen></iframe>
                    </div>
                  <?php else: ?>
                    <img src="<?php echo htmlspecialchars($post['media_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                  <?php endif; ?>
                  <div class="blog-card__body">
                    <span class="blog-badge"><?php echo htmlspecialchars($primaryCategoryLabel); ?></span>
                    <h3 class="blog-card__title"><?php echo htmlspecialchars($post['title']); ?></h3>
                    <div class="blog-summary"><?php echo html_entity_decode($post['summary']); ?></div>
                    <div class="blog-card__meta mt-auto">
                      <span><i class="bi bi-person"></i><?php echo htmlspecialchars($post['author']); ?></span>
                      <span><i class="bi bi-calendar3"></i><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                    </div>
                  </div>
                  <div class="blog-card__footer">
                    <div class="actions">
                      <a class="btn btn-lm-primary" href="blog-detail.php?id=<?php echo (int) $post['id']; ?>">Read article</a>
                      <div class="stats">
                        <span><i class="bi bi-eye"></i><?php echo number_format((int) $post['views']); ?></span>
                        <span><i class="bi bi-heart-fill"></i><span class="like-count" data-post-id="<?php echo (int) $post['id']; ?>"><?php echo number_format((int) $post['likes']); ?></span></span>
                        <button type="button" class="share-button" data-share-title="<?php echo htmlspecialchars($post['title']); ?>" data-share-url="<?php echo htmlspecialchars('https://levelminds.com/blog-detail.php?id=' . (int) $post['id']); ?>" aria-label="Share <?php echo htmlspecialchars($post['title']); ?>">
                          <i class="bi bi-share"></i>
                        </button>
                      </div>
                    </div>
                    <div class="d-flex gap-2">
                      <button type="button" class="like-button flex-fill" data-post-id="<?php echo (int) $post['id']; ?>" data-like-count="<?php echo (int) $post['likes']; ?>">
                        <i class="bi bi-heart-fill"></i><span class="like-label">Like</span>
                        <span class="like-count"><?php echo number_format((int) $post['likes']); ?></span>
                      </button>
                      <button type="button" class="share-button flex-fill" data-share-title="<?php echo htmlspecialchars($post['title']); ?>" data-share-url="<?php echo htmlspecialchars('https://levelminds.com/blog-detail.php?id=' . (int) $post['id']); ?>">
                        <i class="bi bi-send"></i> Share
                      </button>
                    </div>
                    <div class="d-none blog-content" data-post-id="<?php echo (int) $post['id']; ?>"><?php echo html_entity_decode($post['content']); ?></div>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <section class="newsletter-section" id="newsletter">
      <div class="container">
        <div class="newsletter-card">
          <h2>Get hiring insights in your inbox</h2>
          <p>Monthly stories on hiring best practices, educator success, and product releases.</p>
          <form action="newsletter.php" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button class="btn btn-lm-primary" type="submit">Subscribe</button>
          </form>
        </div>
      </div>
    </section>
  </main>

  <div class="share-toast" role="status" aria-live="assertive">Link copied to clipboard</div>
  <div data-global-footer></div>
  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="assets/js/footer.js"></script>
  <script>
    (function () {
      const filterButtons = document.querySelectorAll('.filter-pills .btn');
      const cards = document.querySelectorAll('.blog-grid-item');
      filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
          const filter = button.dataset.filter;
          filterButtons.forEach((btn) => btn.classList.remove('active'));
          button.classList.add('active');
          cards.forEach((card) => {
            const categories = (card.dataset.categories || '').split(',');
            if (filter === 'all' || categories.includes(filter)) {
              card.classList.remove('d-none');
            } else {
              card.classList.add('d-none');
            }
          });
        });
      });
    })();

    (function () {
      const tokenKey = 'lm_visitor_token';
      const likeKey = 'lm_liked_posts';
      const toast = document.querySelector('.share-toast');

      function ensureVisitorToken() {
        let token = localStorage.getItem(tokenKey);
        if (!token) {
          token = 'lm_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
          localStorage.setItem(tokenKey, token);
        }
        return token;
      }

      function getLikedPosts() {
        try {
          return JSON.parse(localStorage.getItem(likeKey) || '{}');
        } catch (e) {
          return {};
        }
      }

      function setLikedPosts(data) {
        localStorage.setItem(likeKey, JSON.stringify(data));
      }

      function updateButtonState(button, liked) {
        if (!button) return;
        const label = button.querySelector('.like-label');
        if (liked) {
          button.classList.add('liked');
          if (label) label.textContent = 'Liked';
        } else {
          button.classList.remove('liked');
          if (label) label.textContent = 'Like';
        }
      }

      function formatCount(value) {
        return new Intl.NumberFormat('en-US').format(value);
      }

      async function sendLike(postId, action) {
        try {
          const response = await fetch('api/like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              blog_id: postId,
              action,
              visitor_token: ensureVisitorToken()
            })
          });
          if (!response.ok) {
            throw new Error('Unable to update like');
          }
          return await response.json();
        } catch (error) {
          console.error(error);
          return null;
        }
      }

      document.querySelectorAll('.like-button').forEach((button) => {
        const postId = button.dataset.postId;
        const likedPosts = getLikedPosts();
        updateButtonState(button, likedPosts[postId]);
        button.addEventListener('click', async () => {
          const likedMap = getLikedPosts();
          const isLiked = Boolean(likedMap[postId]);
          const nextAction = isLiked ? 'unlike' : 'like';
          updateButtonState(button, !isLiked);
          const countElement = button.querySelector('.like-count');
          let currentCount = parseInt(button.dataset.likeCount || '0', 10);
          currentCount = Number.isNaN(currentCount) ? 0 : currentCount;
          currentCount += isLiked ? -1 : 1;
          if (currentCount < 0) currentCount = 0;
          if (countElement) {
            countElement.textContent = formatCount(currentCount);
          }
          button.dataset.likeCount = currentCount;
          document.querySelectorAll(`.like-count[data-post-id="${postId}"]`).forEach((el) => {
            el.textContent = formatCount(currentCount);
          });
          if (!isLiked) {
            likedMap[postId] = true;
          } else {
            delete likedMap[postId];
          }
          setLikedPosts(likedMap);

          const result = await sendLike(postId, nextAction);
          if (result && typeof result.likes !== 'undefined') {
            const serverCount = Number(result.likes);
            button.dataset.likeCount = serverCount;
            if (countElement) {
              countElement.textContent = formatCount(serverCount);
            }
            document.querySelectorAll(`.like-count[data-post-id="${postId}"]`).forEach((el) => {
              el.textContent = formatCount(serverCount);
            });
          }
        });
      });

      document.querySelectorAll('.share-button').forEach((button) => {
        button.addEventListener('click', async () => {
          const title = button.dataset.shareTitle || document.title;
          const url = button.dataset.shareUrl || window.location.href;
          const text = `${title} – LevelMinds`;
          try {
            if (navigator.share) {
              await navigator.share({ title, text, url });
            } else if (navigator.clipboard && navigator.clipboard.writeText) {
              await navigator.clipboard.writeText(`${title}\n${url}`);
              toast.textContent = 'Link copied to clipboard';
              toast.classList.add('show');
              setTimeout(() => toast.classList.remove('show'), 2400);
            }
          } catch (error) {
            console.error('Share failed', error);
            toast.textContent = 'Unable to share right now';
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2400);
          }
        });
      });
    })();
  </script>
</body>

</html>

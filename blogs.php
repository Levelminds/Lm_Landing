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

    return $mapping[$normalised] ?? 'general';
}

function blogCategoryLabel(string $category): string
{
    switch (normaliseCategory($category)) {
        case 'teachers':
            return 'Teachers';
        case 'schools':
            return 'Schools';
        default:
            return 'General';
    }
}

function mbStringLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function mbStringSlice(string $value, int $start, int $length): string
{
    return function_exists('mb_substr') ? mb_substr($value, $start, $length, 'UTF-8') : substr($value, $start, $length);
}

function blogExcerpt(array $post, int $limit = 140): string
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

    if ($content === '') {
        return '';
    }

    $words = str_word_count(strip_tags($content));
    if ($words === 0) {
        return '';
    }

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

    $columns = 'id, title, author, summary, content, media_type, media_url, created_at, views, likes, category, status';

    $statement = $pdo->prepare(
        "SELECT $columns FROM blog_posts WHERE LOWER(status) IN ('published', 'approved') ORDER BY created_at DESC"
    );

    $statement->execute();
    $posts = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $exception) {
    $error = 'We\'re having trouble loading the latest stories right now. Please try again soon.';
}

$featured = $posts[0] ?? null;
$categoryBuckets = [
    'teachers' => [],
    'schools' => [],
    'general' => [],
];

foreach ($posts as $index => $post) {
    if ($index === 0) {
        continue;
    }

    $category = normaliseCategory($post['category'] ?? 'general');
    $categoryBuckets[$category][] = $post;
}

$hasPosts = !empty($posts);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$hostName = $_SERVER['HTTP_HOST'] ?? 'www.levelminds.in';
$baseShareUrl = rtrim(sprintf('%s://%s', $scheme, $hostName), '/');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LevelMinds Blog | Hiring Insights for Schools & Teachers</title>
  <meta name="description" content="Explore insights, playbooks, and stories from the LevelMinds community." />
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/logo/logo.svg">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/logo/logo.svg">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/images/logo/logo.svg">
  <link rel="apple-touch-icon" sizes="152x152" href="assets/images/logo/logo.svg">
  <link rel="apple-touch-icon" sizes="120x120" href="assets/images/logo/logo.svg">
  <link rel="icon" type="image/png" sizes="192x192" href="assets/images/logo/logo.svg">
  <link rel="icon" type="image/png" sizes="512x512" href="assets/images/logo/logo.svg">
  <link rel="manifest" href="assets/images/logo/logo.svg">
  <link rel="icon" href="assets/images/logo/logo.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/custom.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Public Sans', sans-serif;
      background-color: #ffffff;
      color: #0f254f;
    }

    .text-ink-900 {
      color: #0f254f;
    }

    .text-ink-600 {
      color: #47628b;
    }

    .text-ink-500 {
      color: #4c6294;
    }

    .blog-card__count {
      font-size: 0.75rem;
      letter-spacing: 0.12em;
      color: #8a9bbf;
      text-transform: uppercase;
    }

    .blog-modal-image {
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 45px rgba(15, 37, 79, 0.18);
    }

    .blog-modal-image img {
      width: 100%;
      height: auto;
      display: block;
    }

    .blog-modal-content > *:first-child {
      margin-top: 0;
    }

    .blog-modal-content > *:last-child {
      margin-bottom: 0;
    }

    .blog-modal-content p,
    .blog-modal-content ul,
    .blog-modal-content ol {
      font-size: 1.02rem;
      color: #3a4f7a;
    }

    .blog-modal-views {
      background: rgba(31, 106, 225, 0.05);
      border-color: transparent;
    }

    .blog-toast-container {
      z-index: 1080;
    }

    .fbs__net-navbar {
      background-color: #ffffff !important;
      box-shadow: 0 4px 18px rgba(15, 37, 79, 0.07);
    }

    .blog-main {
      background: linear-gradient(180deg, #f5f9ff 0%, #ffffff 35%);
    }

    .blog-hero {
      padding: 6rem 0 4rem;
    }

    .blog-hero__card {
      border-radius: 28px;
      overflow: hidden;
      background: #ffffff;
      box-shadow: 0 24px 55px rgba(15, 37, 79, 0.12);
    }

    .blog-hero__body {
      padding: 2.75rem;
    }

    .blog-hero__meta span {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.95rem;
      color: #47628b;
    }

    .blog-hero__actions .btn-read-more {
      background: #1f6ae1;
      color: #ffffff;
      border: none;
      padding: 0.75rem 1.75rem;
      border-radius: 999px;
      font-weight: 600;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .blog-hero__actions .btn-read-more:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 26px rgba(31, 106, 225, 0.2);
    }

    .blog-hero__image img {
      object-fit: cover;
      height: 100%;
    }

    .section-heading {
      margin-bottom: 1rem;
    }

    .section-heading h2 {
      font-weight: 700;
      color: #0f254f;
    }

    .section-heading p {
      color: #4c6294;
      max-width: 660px;
      margin: 0 auto;
    }

    .category-row {
      margin-top: 2.5rem;
    }

    .category-label {
      display: inline-block;
      font-size: 0.85rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      font-weight: 700;
      color: #1f6ae1;
    }

    .blog-card {
      border-radius: 22px;
      overflow: hidden;
      background: #ffffff;
      border: 1px solid rgba(31, 106, 225, 0.08);
      box-shadow: 0 14px 34px rgba(15, 37, 79, 0.08);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .blog-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 24px 48px rgba(15, 37, 79, 0.12);
    }

    .blog-card__media img {
      height: 180px;
      object-fit: cover;
      width: 100%;
    }

    .blog-card__body {
      padding: 1.5rem;
    }

    .blog-card__title {
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 0.6rem;
      color: #0f254f;
    }

    .blog-card__summary {
      color: #4c6294;
      font-size: 0.95rem;
      margin-bottom: 1rem;
      min-height: 3.5rem;
    }

    .blog-card__meta {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      font-size: 0.85rem;
      color: #60749c;
    }

    .blog-card__meta span {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
    }

    .blog-card__footer {
      margin-top: 1.25rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }

    .btn-read-more {
      background: #1f6ae1;
      color: #ffffff;
      border: none;
      padding: 0.55rem 1.35rem;
      border-radius: 999px;
      font-size: 0.9rem;
      font-weight: 600;
    }

    .blog-card__stats {
      display: flex;
      gap: 1rem;
      color: #60749c;
      font-size: 0.85rem;
    }

    .blog-card__stats span {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
    }

    .blog-action {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      font-size: 0.9rem;
      padding: 0.55rem 1.1rem;
      border-radius: 999px;
      border: 1px solid rgba(31, 106, 225, 0.2);
      background: rgba(31, 106, 225, 0.08);
      color: #1f3c6d;
      transition: all 0.2s ease;
    }

    .blog-action:hover,
    .blog-action.is-liked {
      background: #1f6ae1;
      color: #ffffff;
      border-color: #1f6ae1;
    }

    .modal-content {
      border-radius: 20px;
      border: none;
      box-shadow: 0 24px 60px rgba(15, 37, 79, 0.2);
    }

    .modal-body {
      color: #3a4f7a;
      line-height: 1.7;
    }

    .modal-body p {
      font-size: 1.02rem;
    }

    .empty-state {
      background: #ffffff;
      border-radius: 24px;
      padding: 3rem;
      box-shadow: 0 18px 40px rgba(15, 37, 79, 0.12);
      text-align: center;
    }

    @media (max-width: 991.98px) {
      .blog-hero {
        padding: 5rem 0 3rem;
      }

      .blog-hero__body {
        padding: 2rem;
      }

      .blog-card__media img {
        height: 200px;
      }
    }

    @media (max-width: 575.98px) {
      .blog-card__body {
        padding: 1.3rem;
      }

      .blog-card__summary {
        min-height: auto;
      }
    }
  </style>
</head>

<body class="bg-white">
  <?php include 'team_header.tmp'; ?>

  <main class="blog-main mt-5 pt-4">
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
            $featuredCategoryLabel = blogCategoryLabel($featuredCategory);
            $featuredShareUrl = sprintf('%s/blog_single.php?id=%d', $baseShareUrl, $featuredId);
          ?>
          <div class="blog-hero__card">
            <div class="row g-0 align-items-stretch">
              <div class="col-lg-6">
                <div class="blog-hero__image h-100">
                  <img src="<?php echo htmlspecialchars(blogMediaUrl($featured), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?>" class="w-100 h-100">
                </div>
              </div>
              <div class="col-lg-6 d-flex align-items-center">
                <div class="blog-hero__body w-100">
                  <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis mb-3">Featured • <?php echo htmlspecialchars($featuredCategoryLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                  <h1 class="display-6 fw-bold mb-3 text-ink-900"><?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                  <?php if ($featuredSummary !== ''): ?>
                    <p class="lead text-ink-600"><?php echo htmlspecialchars($featuredSummary, ENT_QUOTES, 'UTF-8'); ?></p>
                  <?php endif; ?>
                  <div class="blog-hero__meta d-flex flex-wrap gap-3 mt-4 mb-4">
                    <span><i class="bi bi-person-circle"></i><?php echo htmlspecialchars($featuredAuthor, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="bi bi-calendar3"></i><?php echo htmlspecialchars($featuredDate, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($featuredReadTime !== ''): ?>
                      <span><i class="bi bi-clock-history"></i><?php echo htmlspecialchars($featuredReadTime, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                    <span><i class="bi bi-eye"></i><span class="js-view-count" data-post-id="<?php echo $featuredId; ?>"><?php echo number_format($featuredViews); ?></span> views</span>
                  </div>
                  <div class="blog-hero__actions d-flex flex-wrap gap-2 align-items-center">
                    <button
                      class="btn btn-read-more js-read-more"
                      type="button"
                      data-post-id="<?php echo $featuredId; ?>"
                      data-title="<?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?>"
                      data-author="<?php echo htmlspecialchars($featuredAuthor, ENT_QUOTES, 'UTF-8'); ?>"
                      data-date="<?php echo htmlspecialchars($featuredDate, ENT_QUOTES, 'UTF-8'); ?>"
                      data-read-time="<?php echo htmlspecialchars($featuredReadTime, ENT_QUOTES, 'UTF-8'); ?>"
                      data-category="<?php echo htmlspecialchars($featuredCategoryLabel, ENT_QUOTES, 'UTF-8'); ?>"
                      data-share-url="<?php echo htmlspecialchars($featuredShareUrl, ENT_QUOTES, 'UTF-8'); ?>"
                    >Read More</button>
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
        <?php elseif ($error !== ''): ?>
          <div class="empty-state">
            <h1 class="h3 mb-2 text-ink-900">Hang tight!</h1>
            <p class="mb-0 text-ink-500">We&apos;re having trouble loading fresh stories right now. Please refresh or try again later.</p>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <h1 class="h3 mb-2 text-ink-900">LevelMinds Blogs</h1>
            <p class="mb-0 text-ink-500">Discover stories from real educators, school leaders, and the LevelMinds team.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <section class="blog-categories pb-5">
      <div class="container">
        <div class="section-heading text-center">
          <h2>Discover Blogs by Categories</h2>
          <p>Curated insights for teachers, schools, and the entire education community.</p>
        </div>

        <?php foreach ($categoryBuckets as $categoryKey => $categoryPosts): ?>
          <?php
            $categoryTitle = blogCategoryLabel($categoryKey);
          ?>
          <div class="category-row">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
              <span class="category-label">For <?php echo htmlspecialchars($categoryTitle, ENT_QUOTES, 'UTF-8'); ?></span>
              <div class="blog-card__stats text-uppercase blog-card__count">
                <span><?php echo count($categoryPosts); ?> Articles</span>
              </div>
            </div>

            <?php if (empty($categoryPosts)): ?>
              <div class="alert alert-light border-0 shadow-sm" role="status">
                Fresh stories for <?php echo htmlspecialchars(strtolower($categoryTitle), ENT_QUOTES, 'UTF-8'); ?> are on the way. Check back soon!
              </div>
            <?php else: ?>
              <div class="row g-4">
                <?php foreach ($categoryPosts as $post): ?>
                  <?php
                    $postId = (int) ($post['id'] ?? 0);
                    $postTitle = decodeBlogText($post['title'] ?? '');
                    $postAuthor = decodeBlogText($post['author'] ?? 'LevelMinds Team');
                    $postDate = formatBlogDate($post['created_at'] ?? '');
                    $postSummary = blogExcerpt($post, 160);
                    $postReadTime = blogReadTime($post);
                    $postViews = (int) ($post['views'] ?? 0);
                    $postLikes = (int) ($post['likes'] ?? 0);
                    $shareUrl = sprintf('%s/blog_single.php?id=%d', $baseShareUrl, $postId);
                  ?>
                  <div class="col-sm-6 col-lg-4 col-xl-3">
                    <article class="blog-card h-100 d-flex flex-column">
                      <div class="blog-card__media">
                        <img src="<?php echo htmlspecialchars(blogMediaUrl($post), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="blog-card__body d-flex flex-column flex-grow-1">
                        <span class="badge bg-primary-subtle text-primary mb-2">For <?php echo htmlspecialchars($categoryTitle, ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3 class="blog-card__title"><?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <?php if ($postSummary !== ''): ?>
                          <p class="blog-card__summary"><?php echo htmlspecialchars($postSummary, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <div class="blog-card__meta mt-auto">
                          <span><i class="bi bi-person-circle"></i><?php echo htmlspecialchars($postAuthor, ENT_QUOTES, 'UTF-8'); ?></span>
                          <span><i class="bi bi-calendar3"></i><?php echo htmlspecialchars($postDate, ENT_QUOTES, 'UTF-8'); ?></span>
                          <?php if ($postReadTime !== ''): ?>
                            <span><i class="bi bi-clock-history"></i><?php echo htmlspecialchars($postReadTime, ENT_QUOTES, 'UTF-8'); ?></span>
                          <?php endif; ?>
                        </div>
                        <div class="blog-card__footer">
                          <div class="blog-card__stats">
                            <span><i class="bi bi-eye"></i><span class="js-view-count" data-post-id="<?php echo $postId; ?>"><?php echo number_format($postViews); ?></span></span>
                            <span><i class="bi bi-heart"></i><span class="js-like-count" data-post-id="<?php echo $postId; ?>"><?php echo number_format($postLikes); ?></span></span>
                          </div>
                          <button
                            class="btn btn-read-more js-read-more"
                            type="button"
                            data-post-id="<?php echo $postId; ?>"
                            data-title="<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>"
                            data-author="<?php echo htmlspecialchars($postAuthor, ENT_QUOTES, 'UTF-8'); ?>"
                            data-date="<?php echo htmlspecialchars($postDate, ENT_QUOTES, 'UTF-8'); ?>"
                            data-read-time="<?php echo htmlspecialchars($postReadTime, ENT_QUOTES, 'UTF-8'); ?>"
                            data-category="<?php echo htmlspecialchars($categoryTitle, ENT_QUOTES, 'UTF-8'); ?>"
                            data-share-url="<?php echo htmlspecialchars($shareUrl, ENT_QUOTES, 'UTF-8'); ?>"
                          >Read More</button>
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
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <?php include 'footer.html'; ?>

  <div class="modal fade" id="blogModal" tabindex="-1" aria-labelledby="blogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis mb-2 js-modal-category d-none"></span>
            <h2 class="modal-title h4 mb-0 js-modal-title" id="blogModalLabel"></h2>
            <p class="text-muted small mb-0 js-modal-meta d-none"></p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-0">
          <div class="js-modal-body"></div>
        </div>
        <div class="modal-footer flex-column flex-lg-row align-items-lg-center gap-3 border-0 pt-0 pb-4 px-lg-4">
          <div class="d-flex flex-wrap gap-2">
            <button class="blog-action js-like js-modal-like" type="button" data-post-id="0" aria-pressed="false">
              <i class="bi bi-heart"></i>
              <span class="js-like-label">Like</span>
              <span class="js-like-count" data-post-id="0">0</span>
            </button>
            <button class="blog-action js-share js-modal-share" type="button" data-share-title="" data-share-text="" data-share-url="">
              <i class="bi bi-share"></i>
              Share
            </button>
          </div>
          <div class="ms-lg-auto">
            <span class="blog-action disabled d-inline-flex align-items-center gap-2 js-modal-views blog-modal-views">
              <i class="bi bi-eye"></i>
              <span class="js-view-count js-modal-view-count" data-post-id="0">0</span>
              <span class="text-muted">views</span>
            </span>
          </div>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed bottom-0 end-0 p-3 blog-toast-container">
    <div id="shareToast" class="toast align-items-center" role="status" aria-live="polite" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"></div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="assets/js/footer.js"></script>
  <script>
    (function () {
      const visitorTokenKey = 'lm_visitor_token';
      const likedPostsKey = 'lm_liked_posts';
      const viewedPostsKey = 'lm_viewed_posts';
      const storageTestKey = 'lm_storage_test';

      const modalElement = document.getElementById('blogModal');
      const modalBody = modalElement ? modalElement.querySelector('.js-modal-body') : null;
      const modalTitle = modalElement ? modalElement.querySelector('.js-modal-title') : null;
      const modalCategory = modalElement ? modalElement.querySelector('.js-modal-category') : null;
      const modalMeta = modalElement ? modalElement.querySelector('.js-modal-meta') : null;
      const modalLikeButton = modalElement ? modalElement.querySelector('.js-modal-like') : null;
      const modalLikeCount = modalElement ? modalElement.querySelector('.js-modal-like .js-like-count') : null;
      const modalShareButton = modalElement ? modalElement.querySelector('.js-modal-share') : null;
      const modalViewCount = modalElement ? modalElement.querySelector('.js-modal-view-count') : null;

      const storageAvailable = (() => {
        try {
          localStorage.setItem(storageTestKey, '1');
          localStorage.removeItem(storageTestKey);
          return true;
        } catch (error) {
          return false;
        }
      })();

      let memoryVisitorToken = null;
      let memoryLikedPosts = new Set();
      let memoryViewedPosts = new Set();
      let likedPosts = new Set();
      let viewedPosts = new Set();

      function generateVisitorToken() {
        const random = window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : Math.random().toString(36).slice(2);
        return `lm_${random}`;
      }

      function ensureVisitorToken() {
        if (storageAvailable) {
          try {
            let token = localStorage.getItem(visitorTokenKey);
            if (!token) {
              token = generateVisitorToken();
              localStorage.setItem(visitorTokenKey, token);
            }
            return token;
          } catch (error) {
            // continue with in-memory token fallback
          }
        }

        if (!memoryVisitorToken) {
          memoryVisitorToken = generateVisitorToken();
        }

        return memoryVisitorToken;
      }

      function getLikedPosts() {
        if (storageAvailable) {
          try {
            const raw = localStorage.getItem(likedPostsKey);
            if (!raw) {
              return new Set();
            }
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
              return new Set(parsed.map(Number));
            }
          } catch (error) {
            // ignore storage read issues
          }
        }

        return new Set(memoryLikedPosts);
      }

      function setLikedPosts(set) {
        memoryLikedPosts = new Set(set);

        if (storageAvailable) {
          try {
            localStorage.setItem(likedPostsKey, JSON.stringify(Array.from(set)));
          } catch (error) {
            // ignore storage write issues
          }
        }
      }

      function getViewedPosts() {
        if (storageAvailable) {
          try {
            const raw = localStorage.getItem(viewedPostsKey);
            if (!raw) {
              return new Set();
            }
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
              return new Set(parsed.map(Number));
            }
          } catch (error) {
            // ignore storage read issues
          }
        }

        return new Set(memoryViewedPosts);
      }

      function setViewedPosts(set) {
        memoryViewedPosts = new Set(set);

        if (storageAvailable) {
          try {
            localStorage.setItem(viewedPostsKey, JSON.stringify(Array.from(set)));
          } catch (error) {
            // ignore storage write issues
          }
        }
      }

      function hasViewedPost(postId) {
        return viewedPosts.has(postId);
      }

      function markPostViewed(postId) {
        viewedPosts.add(postId);
        setViewedPosts(viewedPosts);
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
        });
      }

      function updateViewCount(postId, newCount) {
        document.querySelectorAll(`.js-view-count[data-post-id="${postId}"]`).forEach((node) => {
          node.textContent = formatNumber(newCount);
        });
      }

      async function incrementViews(postId) {
        try {
          const response = await fetch('api/update_views.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId }),
          });

          if (!response.ok) {
            return null;
          }

          const result = await response.json();
          if (result && typeof result.views === 'number') {
            updateViewCount(postId, result.views);
            return result.views;
          }
        } catch (error) {
          // ignore network errors
        }

        return null;
      }

      function buildMeta(author, date, readTime) {
        return [author, date, readTime].filter(Boolean).join(' • ');
      }

      function setModalLoading() {
        if (!modalElement) {
          return;
        }

        if (modalTitle) {
          modalTitle.textContent = 'Loading story…';
        }

        if (modalCategory) {
          modalCategory.classList.add('d-none');
        }

        if (modalMeta) {
          modalMeta.classList.add('d-none');
          modalMeta.textContent = '';
        }

        if (modalBody) {
          modalBody.innerHTML = '<div class="py-5 text-center text-muted">Fetching the full story…</div>';
        }

        if (modalLikeButton) {
          modalLikeButton.dataset.postId = '0';
          modalLikeButton.classList.remove('is-liked');
          modalLikeButton.setAttribute('aria-pressed', 'false');
        }

        if (modalLikeCount) {
          modalLikeCount.dataset.postId = '0';
          modalLikeCount.textContent = '0';
        }

        if (modalShareButton) {
          modalShareButton.dataset.shareTitle = '';
          modalShareButton.dataset.shareText = '';
          modalShareButton.dataset.shareUrl = '';
        }

        if (modalViewCount) {
          modalViewCount.dataset.postId = '0';
          modalViewCount.textContent = '0';
        }
      }

      function setModalError(message) {
        if (!modalElement) {
          return;
        }

        if (modalTitle) {
          modalTitle.textContent = 'Blog unavailable';
        }

        if (modalCategory) {
          modalCategory.classList.add('d-none');
        }

        if (modalMeta) {
          modalMeta.classList.add('d-none');
          modalMeta.textContent = '';
        }

        if (modalBody) {
          modalBody.innerHTML = `<div class="py-5 text-center text-muted">${message}</div>`;
        }

        if (modalLikeButton) {
          modalLikeButton.dataset.postId = '0';
          modalLikeButton.classList.remove('is-liked');
          modalLikeButton.setAttribute('aria-pressed', 'false');
        }

        if (modalLikeCount) {
          modalLikeCount.dataset.postId = '0';
          modalLikeCount.textContent = '0';
        }

        if (modalShareButton) {
          modalShareButton.dataset.shareTitle = '';
          modalShareButton.dataset.shareText = '';
          modalShareButton.dataset.shareUrl = '';
        }

        if (modalViewCount) {
          modalViewCount.dataset.postId = '0';
          modalViewCount.textContent = '0';
        }
      }

      function applyModalData(data) {
        if (!modalElement) {
          return;
        }

        const postId = Number(data.id || 0);
        const isLiked = postId ? likedPosts.has(postId) : false;

        if (modalTitle) {
          modalTitle.textContent = data.title || '';
        }

        if (modalCategory) {
          const hasCategory = Boolean(data.category);
          modalCategory.textContent = hasCategory ? data.category : '';
          modalCategory.classList.toggle('d-none', !hasCategory);
        }

        if (modalMeta) {
          const metaText = buildMeta(data.author, data.date, data.read_time);
          modalMeta.textContent = metaText;
          modalMeta.classList.toggle('d-none', metaText === '');
        }

        if (modalBody) {
          modalBody.innerHTML = '';

          if (data.image) {
            const figure = document.createElement('figure');
            figure.className = 'blog-modal-image mb-4';
            const img = document.createElement('img');
            img.className = 'img-fluid';
            img.src = data.image;
            img.alt = data.title || 'Blog cover image';
            figure.appendChild(img);
            modalBody.appendChild(figure);
          }

          const article = document.createElement('article');
          article.className = 'blog-modal-content';
          article.innerHTML = data.content || '<p class="mb-0">Full story coming soon.</p>';
          modalBody.appendChild(article);
        }

        if (modalLikeButton) {
          modalLikeButton.dataset.postId = postId ? String(postId) : '0';
          modalLikeButton.classList.toggle('is-liked', isLiked);
          modalLikeButton.setAttribute('aria-pressed', isLiked ? 'true' : 'false');
          const label = modalLikeButton.querySelector('.js-like-label');
          if (label) {
            label.textContent = isLiked ? 'Liked' : 'Like';
          }
        }

        if (modalLikeCount) {
          modalLikeCount.dataset.postId = postId ? String(postId) : '0';
          modalLikeCount.textContent = formatNumber(typeof data.likes === 'number' ? data.likes : 0);
        }

        if (modalShareButton) {
          modalShareButton.dataset.shareTitle = data.title || document.title;
          modalShareButton.dataset.shareText = data.summary || data.title || '';
          modalShareButton.dataset.shareUrl = data.share_url || window.location.href;
        }

        if (modalViewCount) {
          modalViewCount.dataset.postId = postId ? String(postId) : '0';
          modalViewCount.textContent = formatNumber(typeof data.views === 'number' ? data.views : 0);
        }
      }

      async function fetchBlog(postId) {
        const response = await fetch(`api/get_blog.php?id=${encodeURIComponent(postId)}`, {
          headers: {
            'Accept': 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error('Unable to load blog post');
        }

        const result = await response.json();

        if (!result || result.success !== true) {
          const message = result && result.error ? result.error : 'Unable to load blog post';
          throw new Error(message);
        }

        return result;
      }

      async function openModalForPost(trigger) {
        const postId = Number(trigger.dataset.postId);
        if (!postId || !modalElement) {
          return;
        }

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
        setModalLoading();
        modalInstance.show();

        try {
          const data = await fetchBlog(postId);
          applyModalData(data);
          const initialViews = typeof data.views === 'number' ? data.views : 0;
          updateViewCount(postId, initialViews);

          if (!hasViewedPost(postId)) {
            const newViews = await incrementViews(postId);
            if (typeof newViews === 'number') {
              markPostViewed(postId);
            }
          }
        } catch (error) {
          setModalError('This blog could not be loaded. It might have been removed or unpublished.');
        }
      }

      async function toggleLike(button) {
        const postId = Number(button.dataset.postId);
        if (!postId) {
          return;
        }

        button.disabled = true;
        const wasLiked = likedPosts.has(postId);

        try {
          const response = await fetch('api/like.php', {
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
          updateLikeState(postId, wasLiked, getCurrentLikeCount(postId));
        } finally {
          button.disabled = false;
        }
      }

      function initialiseLikes() {
        if (!(likedPosts instanceof Set)) {
          likedPosts = getLikedPosts();
        }

        document.querySelectorAll('.js-like').forEach((button) => {
          const postId = Number(button.dataset.postId);

          if (postId && likedPosts.has(postId)) {
            button.classList.add('is-liked');
            button.setAttribute('aria-pressed', 'true');
            const label = button.querySelector('.js-like-label');
            if (label) {
              label.textContent = 'Liked';
            }
          } else {
            button.setAttribute('aria-pressed', 'false');
          }

          button.addEventListener('click', () => toggleLike(button));
        });
      }

      function handleReadMore(event) {
        event.preventDefault();
        event.stopPropagation();
        const trigger = event.currentTarget;
        openModalForPost(trigger);
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
            // fall back to clipboard
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
            // fall through to manual copy message
          }
        }

        if (toastEl && toastBody) {
          toastBody.textContent = 'Copy this link manually: ' + shareData.url;
          bootstrap.Toast.getOrCreateInstance(toastEl).show();
        }
      }

      function initialiseReads() {
        document.querySelectorAll('.js-read-more').forEach((trigger) => {
          trigger.addEventListener('click', handleReadMore);
        });
      }

      function initialiseShares() {
        document.querySelectorAll('.js-share').forEach((button) => {
          button.addEventListener('click', handleShare);
        });
      }

      document.addEventListener('DOMContentLoaded', () => {
        ensureVisitorToken();
        likedPosts = getLikedPosts();
        viewedPosts = getViewedPosts();
        initialiseLikes();
        initialiseReads();
        initialiseShares();

        document.querySelectorAll('.fbs__net-navbar .nav-link').forEach((link) => {
          const href = (link.getAttribute('href') || '').toLowerCase();
          if (href.includes('blogs.php')) {
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
          } else {
            link.classList.remove('active');
            link.removeAttribute('aria-current');
          }
        });
      });
    })();
  </script>
</body>

</html>

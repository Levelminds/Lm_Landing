<?php
$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

$posts = [];
$error = '';
$pdo = null;

function deduplicateBlogPosts(array $items): array
{
    $unique = [];
    $seen = [];

    foreach ($items as $item) {
        $id = isset($item['id']) ? (string) $item['id'] : '';
        $key = $id !== '' ? $id : sha1(json_encode($item));

        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $unique[] = $item;
    }

    return $unique;
}

function decodeBlogHtml($value): string
{
    return trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function decodeBlogText($value): string
{
    return trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function formatBlogDate($value): string
{
    if (empty($value)) {
        return '—';
    }

    try {
        $date = new DateTime($value);

        return $date->format('M j, Y');
    } catch (Exception $e) {
        return '—';
    }
}

function normalizeBlogCategory($value): string
{
    $normalized = strtolower(trim((string) $value));
    $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized);
    $normalized = trim((string) $normalized, '-');

    if ($normalized === '') {
        return 'general';
    }

    return $normalized;
}

function resolveBlogCategoryLabel(string $slug, $original, array $labels): string
{
    if (isset($labels[$slug])) {
        return $labels[$slug];
    }

    $source = trim((string) $original);
    if ($source === '') {
        $source = $slug;
    }

    $source = preg_replace('/[-_]+/', ' ', strtolower($source));

    return ucwords($source ?: 'General Insights');
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $columns = 'id, title, author, summary, content, media_type, media_url, created_at, views, likes, category';
    $stmt = $pdo->query("SELECT $columns FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Unable to load blog posts right now.';
}

if (!$posts) {
    $fallbackPath = __DIR__ . '/data/blogs.json';

    if (is_readable($fallbackPath)) {
        $json = json_decode((string) file_get_contents($fallbackPath), true);

        if (is_array($json)) {
            $posts = $json;
        }
    }
}

$posts = deduplicateBlogPosts($posts);
$featured = $posts[0] ?? null;

$audienceLabels = [
    'teachers' => 'For Teachers',
    'schools'  => 'For Schools',
    'general'  => 'General Insights',
];

$normalizedPosts = [];
$groupedPosts = [];

foreach ($posts as $post) {
    $categorySlug = normalizeBlogCategory($post['category'] ?? '');
    $categoryLabel = resolveBlogCategoryLabel($categorySlug, $post['category'] ?? '', $audienceLabels);

    $post['category_slug'] = $categorySlug;
    $post['category_label'] = $categoryLabel;

    if (!isset($groupedPosts[$categorySlug])) {
        $groupedPosts[$categorySlug] = [];
    }

    $groupedPosts[$categorySlug][] = $post;
    $normalizedPosts[] = $post;
}

$posts = $normalizedPosts;

if ($featured) {
    $featuredId = (int) ($featured['id'] ?? 0);
    $featuredCategorySlug = $featured['category_slug'] ?? normalizeBlogCategory($featured['category'] ?? '');

    if ($featuredId && isset($groupedPosts[$featuredCategorySlug])) {
        $groupedPosts[$featuredCategorySlug] = array_values(array_filter(
            $groupedPosts[$featuredCategorySlug],
            static function ($item) use ($featuredId) {
                return (int) ($item['id'] ?? 0) !== $featuredId;
            }
        ));

        if (!$groupedPosts[$featuredCategorySlug]) {
            unset($groupedPosts[$featuredCategorySlug]);
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

$categoryFilters = ['all' => 'All Posts'];
foreach ($preferredOrder as $filter) {
    if (isset($groupedPosts[$filter])) {
        $categoryFilters[$filter] = resolveBlogCategoryLabel($filter, $filter, $audienceLabels);
    }
}

foreach ($availableCategories as $filter) {
    if (!isset($categoryFilters[$filter])) {
        $categoryFilters[$filter] = resolveBlogCategoryLabel($filter, $filter, $audienceLabels);
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

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/custom.css" rel="stylesheet">
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

  <main class="blog-main">
    <?php if ($featured): ?>
      <?php
        $featuredId = (int) ($featured['id'] ?? 0);
        $featuredTitle = decodeBlogText($featured['title'] ?? '');
        $featuredAuthor = decodeBlogText($featured['author'] ?? '');
        $featuredSummary = decodeBlogText($featured['summary'] ?? '');
        $featuredContent = decodeBlogHtml($featured['content'] ?? '');
        $featuredDate = formatBlogDate($featured['created_at'] ?? '');
        $featuredViews = (int) ($featured['views'] ?? 0);
        $featuredLikes = (int) ($featured['likes'] ?? 0);
        $featuredCategorySlug = $featured['category_slug'] ?? normalizeBlogCategory($featured['category'] ?? '');
        $featuredCategoryLabel = $featured['category_label'] ?? resolveBlogCategoryLabel($featuredCategorySlug, $featured['category'] ?? '', $audienceLabels);
        $featuredMediaType = strtolower((string) ($featured['media_type'] ?? 'image'));
        $featuredMediaUrl = trim((string) ($featured['media_url'] ?? ''));
        $featuredShareUrl = sprintf('%s#post-%d', $_SERVER['REQUEST_URI'] ?? 'blogs.php', $featuredId ?: 0);
      ?>
      <section class="blog-hero has-featured">
        <div class="container">
          <div class="blog-hero__card">
            <div class="row g-5 align-items-center">
              <div class="col-lg-6">
                <div class="blog-hero__content">
                  <span class="blog-hero__eyebrow">Stories &amp; strategies for modern learning teams</span>
                  <h1 class="blog-hero__title"><?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                  <p class="blog-hero__description"><?php echo htmlspecialchars($featuredSummary, ENT_QUOTES, 'UTF-8'); ?></p>
                  <div class="blog-meta">
                    <span><i class="bi bi-person-circle"></i><?php echo htmlspecialchars($featuredAuthor ?: 'LevelMinds Team', ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="bi bi-calendar3"></i><?php echo htmlspecialchars($featuredDate, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="bi bi-eye"></i><?php echo number_format($featuredViews); ?> views</span>
                    <span><i class="bi bi-heart"></i><span class="like-count"><?php echo number_format($featuredLikes); ?></span> likes</span>
                  </div>
                  <div class="blog-hero__actions">
                    <button class="btn btn-primary btn-lg js-read-story" type="button"
                            data-post-id="<?php echo $featuredId; ?>"
                            data-title="<?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?>"
                            data-author="<?php echo htmlspecialchars($featuredAuthor ?: 'LevelMinds Team', ENT_QUOTES, 'UTF-8'); ?>"
                            data-date="<?php echo htmlspecialchars($featuredDate, ENT_QUOTES, 'UTF-8'); ?>"
                            data-content="<?php echo htmlspecialchars($featuredContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                      Read Full Story
                    </button>
                    <button class="btn btn-lg blog-like-btn" type="button"
                            data-post-id="<?php echo $featuredId; ?>"
                            data-likes="<?php echo $featuredLikes; ?>">
                      <i class="bi bi-heart"></i>
                      <span class="blog-like-btn__label">Like</span>
                      <span class="blog-like-btn__count"><?php echo number_format($featuredLikes); ?></span>
                    </button>
                    <button class="btn btn-lg blog-share-btn" type="button"
                            data-share-title="<?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?>"
                            data-share-text="<?php echo htmlspecialchars($featuredSummary, ENT_QUOTES, 'UTF-8'); ?>"
                            data-share-url="<?php echo htmlspecialchars($featuredShareUrl, ENT_QUOTES, 'UTF-8'); ?>">
                      <i class="bi bi-share"></i>
                      Share
                    </button>
                  </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="blog-hero__media">
                  <span class="blog-hero__category">
                    Featured • <?php echo htmlspecialchars($featuredCategoryLabel, ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                  <div class="blog-hero__media-frame ratio ratio-16x9">
                    <?php if ($featuredMediaType === 'video' && $featuredMediaUrl): ?>
                      <iframe src="<?php echo htmlspecialchars($featuredMediaUrl, ENT_QUOTES, 'UTF-8'); ?>" title="Featured video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                    <?php elseif ($featuredMediaUrl): ?>
                      <img src="<?php echo htmlspecialchars($featuredMediaUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid">
                    <?php else: ?>
                      <div class="blog-hero__placeholder">
                        <i class="bi bi-image"></i>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    <?php else: ?>
      <section class="blog-hero">
        <div class="container">
          <div class="row align-items-center g-5">
            <div class="col-lg-8 mx-auto text-center">
              <h1 class="blog-hero__title">LevelMinds Blog</h1>
              <p class="blog-hero__description">Insights, playbooks, and stories to help schools hire the right educators—and help teachers grow careers they love.</p>
            </div>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <section class="blog-filter">
      <div class="container">
        <div class="row g-4 align-items-center">
          <div class="col-lg-6">
            <h2 class="blog-filter__title">Discover blogs by categories</h2>
            <p class="blog-filter__description">Filter stories tailored for teachers, school leaders, and the broader education community.</p>
          </div>
          <div class="col-lg-6">
            <div class="blog-filter__actions d-flex flex-wrap gap-2 justify-content-lg-end">
              <?php foreach ($categoryFilters as $filterSlug => $filterLabel): ?>
                <button class="btn btn-filter<?php echo $filterSlug === 'all' ? ' active' : ''; ?>" type="button" data-filter="<?php echo htmlspecialchars($filterSlug, ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo htmlspecialchars($filterLabel, ENT_QUOTES, 'UTF-8'); ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="blog-grid">
      <div class="container">
        <?php if (!$posts): ?>
          <div class="row justify-content-center">
            <div class="col-md-8">
              <div class="alert alert-info text-center mb-0">No blog posts available right now. Please check back soon.</div>
            </div>
          </div>
        <?php else: ?>
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="blogCards">
            <?php foreach ($orderedCategories as $categorySlug): ?>
              <?php foreach ($groupedPosts[$categorySlug] as $post): ?>
                <?php
                  $postId = (int) ($post['id'] ?? 0);
                  $postTitle = decodeBlogText($post['title'] ?? '');
                  $postAuthor = decodeBlogText($post['author'] ?? 'LevelMinds Team');
                  $postSummary = decodeBlogText($post['summary'] ?? '');
                  $postContent = decodeBlogHtml($post['content'] ?? '');
                  $postDate = formatBlogDate($post['created_at'] ?? '');
                  $postViews = (int) ($post['views'] ?? 0);
                  $postLikes = (int) ($post['likes'] ?? 0);
                  $postMediaType = strtolower((string) ($post['media_type'] ?? 'image'));
                  $postMediaUrl = trim((string) ($post['media_url'] ?? ''));
                  $shareUrl = sprintf('%s#post-%d', $_SERVER['REQUEST_URI'] ?? 'blogs.php', $postId ?: 0);
                  $postCategorySlug = $post['category_slug'] ?? $categorySlug;
                  $postCategoryLabel = $post['category_label'] ?? resolveBlogCategoryLabel($postCategorySlug, $post['category'] ?? '', $audienceLabels);
                ?>
                <div class="col" data-category="<?php echo htmlspecialchars($postCategorySlug, ENT_QUOTES, 'UTF-8'); ?>">
                  <article class="blog-card h-100" id="post-<?php echo $postId; ?>">
                    <div class="blog-card__media ratio ratio-16x9">
                      <?php if ($postMediaType === 'video' && $postMediaUrl): ?>
                        <iframe src="<?php echo htmlspecialchars($postMediaUrl, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                      <?php elseif ($postMediaUrl): ?>
                        <img src="<?php echo htmlspecialchars($postMediaUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid">
                      <?php else: ?>
                        <div class="blog-card__placeholder">
                          <i class="bi bi-image"></i>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div class="blog-card__body">
                      <span class="blog-card__category mb-2"><?php echo htmlspecialchars($postCategoryLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                      <h3 class="blog-card__title"><?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                      <p class="blog-card__summary"><?php echo htmlspecialchars($postSummary, ENT_QUOTES, 'UTF-8'); ?></p>
                      <div class="blog-meta">
                        <span><i class="bi bi-person-circle"></i><?php echo htmlspecialchars($postAuthor, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span><i class="bi bi-calendar3"></i><?php echo htmlspecialchars($postDate, ENT_QUOTES, 'UTF-8'); ?></span>
                      </div>
                      <div class="blog-card__footer">
                        <div class="blog-card__stats">
                          <span><i class="bi bi-eye"></i><?php echo number_format($postViews); ?></span>
                          <span><i class="bi bi-heart"></i><span class="like-count"><?php echo number_format($postLikes); ?></span></span>
                        </div>
                        <div class="blog-card__actions">
                          <button class="btn btn-link p-0 js-read-story" type="button"
                                  data-post-id="<?php echo $postId; ?>"
                                  data-title="<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                  data-author="<?php echo htmlspecialchars($postAuthor, ENT_QUOTES, 'UTF-8'); ?>"
                                  data-date="<?php echo htmlspecialchars($postDate, ENT_QUOTES, 'UTF-8'); ?>"
                                  data-content="<?php echo htmlspecialchars($postContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                            Read Full Story
                          </button>
                        </div>
                      </div>
                      <div class="d-flex flex-wrap gap-2 mt-3">
                        <button class="btn blog-like-btn" type="button"
                                data-post-id="<?php echo $postId; ?>"
                                data-likes="<?php echo $postLikes; ?>">
                          <i class="bi bi-heart"></i>
                          <span class="blog-like-btn__label">Like</span>
                          <span class="blog-like-btn__count"><?php echo number_format($postLikes); ?></span>
                        </button>
                        <button class="btn blog-share-btn" type="button"
                                data-share-title="<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                data-share-text="<?php echo htmlspecialchars($postSummary, ENT_QUOTES, 'UTF-8'); ?>"
                                data-share-url="<?php echo htmlspecialchars($shareUrl, ENT_QUOTES, 'UTF-8'); ?>">
                          <i class="bi bi-share"></i>
                          Share
                        </button>
                      </div>
                    </div>
                  </article>
                </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include 'footer.html'; ?>

  <div class="modal fade" id="storyModal" tabindex="-1" aria-labelledby="storyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title" id="storyModalLabel"></h5>
            <p class="modal-subtitle mb-0"></p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="shareToast" class="toast" role="status" aria-live="polite" aria-atomic="true">
      <div class="toast-body"></div>
    </div>
  </div>

  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      const visitorStorageKey = 'lm_visitor_token';
      const likedPostsKey = 'lm_liked_posts';

      function decodeEntities(encodedString) {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = encodedString;
        return textarea.value;
      }

      function ensureVisitorToken() {
        let token = localStorage.getItem(visitorStorageKey);

        if (!token) {
          token = 'lm_' + (window.crypto?.randomUUID ? window.crypto.randomUUID() : Math.random().toString(36).slice(2));
          localStorage.setItem(visitorStorageKey, token);
        }

        return token;
      }

      function getLikedPosts() {
        try {
          const raw = localStorage.getItem(likedPostsKey);
          if (!raw) {
            return [];
          }
          const parsed = JSON.parse(raw);
          return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
          console.error('Unable to read liked posts from storage', error);
          return [];
        }
      }

      function setLikedPosts(ids) {
        try {
          localStorage.setItem(likedPostsKey, JSON.stringify(ids));
        } catch (error) {
          console.error('Unable to persist liked posts', error);
        }
      }

      function markLikedButtons() {
        const likedIds = new Set(getLikedPosts());
        document.querySelectorAll('.blog-like-btn').forEach((button) => {
          const postId = button.dataset.postId;
          if (!postId) {
            return;
          }
          if (likedIds.has(Number(postId))) {
            button.classList.add('is-liked');
            button.setAttribute('aria-pressed', 'true');
          } else {
            button.classList.remove('is-liked');
            button.setAttribute('aria-pressed', 'false');
          }
        });
      }

      function handleFilterClick(event) {
        const button = event.currentTarget;
        const filter = button.dataset.filter;
        if (!filter) {
          return;
        }

        document.querySelectorAll('.btn-filter').forEach((btn) => btn.classList.remove('active'));
        button.classList.add('active');

        const cards = document.querySelectorAll('#blogCards [data-category]');
        cards.forEach((card) => {
          if (filter === 'all' || card.dataset.category === filter) {
            card.classList.remove('d-none');
          } else {
            card.classList.add('d-none');
          }
        });
      }

      function handleStoryClick(event) {
        const button = event.currentTarget;
        const title = button.dataset.title || '';
        const author = button.dataset.author || 'LevelMinds Team';
        const date = button.dataset.date || '';
        const content = decodeEntities(button.dataset.content || '');

        const modalEl = document.getElementById('storyModal');
        const modalTitle = modalEl.querySelector('.modal-title');
        const modalSubtitle = modalEl.querySelector('.modal-subtitle');
        const modalBody = modalEl.querySelector('.modal-body');

        modalTitle.textContent = title;
        modalSubtitle.textContent = [author, date].filter(Boolean).join(' • ');
        modalBody.innerHTML = content || '<p>No additional content available.</p>';

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
      }

      async function handleLikeClick(event) {
        const button = event.currentTarget;
        const postId = Number(button.dataset.postId);
        if (!postId) {
          return;
        }

        const storedLikedPosts = new Set(getLikedPosts());
        const wasLiked = storedLikedPosts.has(postId);

        button.disabled = true;
        const countEl = button.querySelector('.blog-like-btn__count');
        const originalCount = Number(countEl?.textContent?.replace(/[^0-9]/g, '')) || 0;

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
            throw new Error('Failed to toggle like');
          }

          const result = await response.json();
          const updatedLikes = Number(result.likes);
          const isLiked = Boolean(result.liked);

          if (!Number.isNaN(updatedLikes) && countEl) {
            countEl.textContent = new Intl.NumberFormat().format(updatedLikes);
          }

          const likedPosts = new Set(storedLikedPosts);
          if (isLiked) {
            likedPosts.add(postId);
            button.classList.add('is-liked');
            button.setAttribute('aria-pressed', 'true');
          } else {
            likedPosts.delete(postId);
            button.classList.remove('is-liked');
            button.setAttribute('aria-pressed', 'false');
          }

          setLikedPosts(Array.from(likedPosts));
        } catch (error) {
          console.error(error);
          if (countEl) {
            countEl.textContent = new Intl.NumberFormat().format(originalCount);
          }
          if (wasLiked) {
            button.classList.add('is-liked');
            button.setAttribute('aria-pressed', 'true');
          } else {
            button.classList.remove('is-liked');
            button.setAttribute('aria-pressed', 'false');
          }
          setLikedPosts(Array.from(storedLikedPosts));
        } finally {
          button.disabled = false;
        }
      }

      async function handleShareClick(event) {
        const button = event.currentTarget;
        const shareData = {
          title: button.dataset.shareTitle || document.title,
          text: button.dataset.shareText || 'Check out this story from LevelMinds',
          url: button.dataset.shareUrl || window.location.href,
        };

        const toastEl = document.getElementById('shareToast');
        const toastBody = toastEl?.querySelector('.toast-body');

        if (navigator.share) {
          try {
            await navigator.share(shareData);
            if (toastBody) {
              toastBody.textContent = 'Story shared successfully!';
              bootstrap.Toast.getOrCreateInstance(toastEl).show();
            }
            return;
          } catch (error) {
            if (error.name !== 'AbortError') {
              console.error('Share failed, trying clipboard fallback', error);
            } else {
              return;
            }
          }
        }

        try {
          await navigator.clipboard.writeText(shareData.url);
          if (toastBody) {
            toastBody.textContent = 'Link copied to clipboard!';
            bootstrap.Toast.getOrCreateInstance(toastEl).show();
          }
        } catch (error) {
          console.error('Clipboard fallback failed', error);
          if (toastBody) {
            toastBody.textContent = 'Unable to share automatically. Please copy the URL from your browser.';
            bootstrap.Toast.getOrCreateInstance(toastEl).show();
          }
        }
      }

      function init() {
        ensureVisitorToken();
        markLikedButtons();

        document.querySelectorAll('.btn-filter').forEach((button) => {
          button.addEventListener('click', handleFilterClick);
        });

        document.querySelectorAll('.js-read-story').forEach((button) => {
          button.addEventListener('click', handleStoryClick);
        });

        document.querySelectorAll('.blog-like-btn').forEach((button) => {
          button.addEventListener('click', handleLikeClick);
        });

        document.querySelectorAll('.blog-share-btn').forEach((button) => {
          button.addEventListener('click', handleShareClick);
        });
      }

      document.addEventListener('DOMContentLoaded', init);
    })();
  </script>
</body>

</html>

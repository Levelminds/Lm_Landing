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
  <link href="assets/css/custom.css" rel="stylesheet">

  <style>
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

  <main style="padding-top: 110px;">
    <section class="hero__v6 section" style="padding: 140px 0 80px;">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
            <h1 class="hero-title mb-4" style="color: #0F1D3B; font-size: 3.5rem; font-weight: 800;">LevelMinds Blog</h1>
            <p class="lead mb-4" style="color: rgba(15,29,59,0.72);">Insights, playbooks, and stories to help schools hire the right educators—and help teachers grow careers they love.</p>
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
                      <div class="ratio ratio-16x9">
                        <iframe src="<?php echo htmlspecialchars($featured['media_url']); ?>" title="<?php echo htmlspecialchars($featured['title']); ?>" allowfullscreen></iframe>
                      </div>
                    <?php else: ?>
                      <img src="<?php echo htmlspecialchars($featured['media_url']); ?>" class="img-fluid h-100" alt="<?php echo htmlspecialchars($featured['title']); ?>" style="object-fit: cover;">
                    <?php endif; ?>
                  </div>
                  <div class="col-lg-6 d-flex align-items-center">
                    <div class="p-4 p-lg-5">
                      <span class="badge bg-primary-subtle text-primary mb-3">Featured</span>
                      <h2 style="font-weight: 700; color: #0F1D3B;"><?php echo htmlspecialchars($featured['title']); ?></h2>
                      <p class="mt-3" style="color: #51617A;"><?php echo htmlspecialchars($featured['summary']); ?></p>
                      <div class="d-flex align-items-center gap-3 mt-4">
                        <div>
                          <div style="color: #1B2A4B; font-weight: 600;"><?php echo htmlspecialchars($featured['author']); ?></div>
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
                  <div class="ratio ratio-16x9">
                    <iframe src="<?php echo htmlspecialchars($post['media_url']); ?>" title="<?php echo htmlspecialchars($post['title']); ?>" allowfullscreen></iframe>
                  </div>
                <?php else: ?>
                  <img src="<?php echo htmlspecialchars($post['media_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>" style="height: 220px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body p-4">
                  <?php $badgeClass = $post['media_type'] === 'video' ? 'bg-info text-white' : 'bg-primary-subtle text-primary'; ?>
                  <span class="badge <?php echo $badgeClass; ?> mb-2"><?php echo ucfirst($post['media_type']); ?></span>
                  <p class="card-text" style="color: #51617A;">
                    <?php echo htmlspecialchars(mb_strimwidth($post['summary'], 0, 140, '…')); ?>
                  </p>
                </div>
                <div class="card-footer bg-white border-0 p-4 pt-0 d-flex justify-content-between align-items-center">
                  <small class="text-muted"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></small>
                  <small class="text-muted"><i class="bi bi-heart me-1"></i><?php echo (int)$post['likes']; ?></small>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>

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
</body>
</html>






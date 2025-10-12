<?php
$navItems = lm_navigation_items();
$app = lm_config();
$base = rtrim($app['base_url'] ?? '', '/');
?>
<!-- ======= Header =======-->
<header class="fbs__net-navbar navbar navbar-expand-lg navbar-light" aria-label="LevelMinds navigation">
  <div class="container d-flex align-items-center justify-content-between py-3">
    <a class="navbar-brand w-auto" href="<?= $base ?: '/'; ?>">
      <img src="<?= lm_asset('assets/images/logo/logo.svg'); ?>" alt="<?= htmlspecialchars($app['name'], ENT_QUOTES, 'UTF-8'); ?>" class="logo" height="40">
    </a>

    <div class="offcanvas offcanvas-start w-75" id="lm-nav" tabindex="-1" aria-labelledby="lm-nav-label">
      <div class="offcanvas-header">
        <a class="logo-link" id="lm-nav-label" href="<?= $base ?: '/'; ?>">
          <img src="<?= lm_asset('assets/images/logo/logo.svg'); ?>" alt="<?= htmlspecialchars($app['name'], ENT_QUOTES, 'UTF-8'); ?>" class="logo" height="35">
        </a>
        <button class="btn-close text-reset" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body align-items-lg-center">
        <ul class="navbar-nav nav me-auto ps-lg-5 mb-4 mb-lg-0">
          <?php foreach ($navItems as $item): $href = $item['href']; ?>
            <li class="nav-item">
              <a class="nav-link<?= lm_route_is_active($href) ? ' active' : ''; ?>" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">
                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="d-lg-none mt-4 w-100">
        <a class="btn btn-nav-primary w-100 mb-2" href="<?= htmlspecialchars($base . '/tour.php', ENT_QUOTES, 'UTF-8'); ?>">Explore Platform</a>
          <a class="btn btn-nav-outline w-100" href="https://www.lmap.in" target="_blank" rel="noopener">Login / Sign Up</a>
        </div>
      </div>
    </div>

    <div class="d-flex align-items-center gap-3">
      <div class="header-actions d-none d-lg-flex align-items-center gap-2">
        <a class="btn btn-nav-outline" href="https://www.lmap.in" target="_blank" rel="noopener">Login / Sign Up</a>
        <a class="btn btn-nav-primary" href="<?= htmlspecialchars($base . '/tour.php', ENT_QUOTES, 'UTF-8'); ?>">Book a Demo</a>
      </div>
      <button class="fbs__net-navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#lm-nav" aria-controls="lm-nav" aria-label="Toggle navigation">
        <svg class="fbs__net-icon-menu" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="21" x2="3" y1="6" y2="6"></line>
          <line x1="21" x2="3" y1="12" y2="12"></line>
          <line x1="21" x2="3" y1="18" y2="18"></line>
        </svg>
      </button>
    </div>
  </div>
</header>
<!-- End Header-->

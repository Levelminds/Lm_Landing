<?php
$app = lm_config();
$navItems = lm_navigation_items();
$supportPages = $app['support_pages'] ?? [];
?>
<!-- ======= Footer =======-->
<footer class="lm-footer" aria-label="LevelMinds footer">
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-4 col-md-6">
        <img src="<?= lm_asset('assets/images/logo/logo-light.svg'); ?>" alt="<?= htmlspecialchars($app['name'], ENT_QUOTES, 'UTF-8'); ?>" height="38" class="mb-3">
        <p class="mb-4 pe-lg-4"><?= htmlspecialchars($app['tagline'], ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="d-flex gap-3">
          <?php foreach ($app['social'] as $social): ?>
            <a class="badge-lm" href="<?= htmlspecialchars($social['href'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
              <?= htmlspecialchars($social['label'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-2 col-md-6">
        <h5>Company</h5>
        <ul class="list-unstyled d-grid gap-2">
          <?php foreach ($navItems as $item): ?>
            <li><a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php if (!empty($supportPages)): ?>
        <div class="col-lg-2 col-md-6">
          <h5>Explore</h5>
          <ul class="list-unstyled d-grid gap-2">
            <?php foreach ($supportPages as $item): ?>
              <li><a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <div class="col-lg-3 col-md-6">
        <h5>Contact</h5>
        <ul class="list-unstyled d-grid gap-2">
          <li><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($app['contact']['email'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($app['contact']['email'], ENT_QUOTES, 'UTF-8'); ?></a></li>
          <li><strong>Phone:</strong> <a href="tel:<?= htmlspecialchars($app['contact']['phone'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($app['contact']['phone'], ENT_QUOTES, 'UTF-8'); ?></a></li>
          <li><strong>Address:</strong> <span><?= htmlspecialchars($app['contact']['address'], ENT_QUOTES, 'UTF-8'); ?></span></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h5>Stay informed</h5>
        <p class="mb-3">Receive hiring insights, teacher spotlights, and platform updates.</p>
        <form class="d-flex flex-column gap-2" action="newsletter.php" method="post">
          <input class="form-control" type="email" name="email" placeholder="Enter your email" required>
          <button class="btn btn-nav-primary" type="submit">Subscribe</button>
        </form>
      </div>
    </div>
    <div class="footer-bottom mt-5 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
      <span>&copy; <span data-current-year></span> <?= htmlspecialchars($app['name'], ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.</span>
      <div class="d-flex gap-3">
        <a href="/privacy-policy.php">Privacy Policy</a>
        <a href="/terms-conditions.php">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>
<!-- End Footer -->

<section class="py-5 hero-surface">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <div class="hero-tag">Meet the people behind LevelMinds</div>
        <h1 class="hero-heading">A cross-functional team shaping the future of hiring for schools and teachers.</h1>
        <p class="hero-subtext">We blend educational insight, product craftsmanship, and relentless empathy to build experiences that empower classrooms.</p>
      </div>
      <div class="col-lg-5">
        <div class="hero-card">
          <h4 class="mb-3 text-dark">What drives us</h4>
          <ul class="list-unstyled d-grid gap-3 mb-0">
            <li class="d-flex gap-3">
              <div class="feature-icon">🎓</div>
              <div>
                <strong>Education-first mindset</strong>
                <p class="mb-0 text-muted">Many of us have taught, mentored, or led schools. We co-create with educators at every step.</p>
              </div>
            </li>
            <li class="d-flex gap-3">
              <div class="feature-icon">🤝</div>
              <div>
                <strong>Radical collaboration</strong>
                <p class="mb-0 text-muted">Product, engineering, design, and success squads operate in small pods that iterate quickly.</p>
              </div>
            </li>
            <li class="d-flex gap-3">
              <div class="feature-icon">💡</div>
              <div>
                <strong>Inclusive innovation</strong>
                <p class="mb-0 text-muted">We build features that scale across metros and tier-2 cities, ensuring equitable access.</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-between align-items-center mb-5">
      <div class="col-lg-6">
        <h2 class="section-heading">Leadership</h2>
        <p class="section-subtitle">Experienced operators from edtech, recruitment, and SaaS guiding the LevelMinds vision.</p>
      </div>
      <div class="col-lg-5 text-lg-end">
        <a class="lm-btn-outline" href="/careers.php">Join our mission</a>
      </div>
    </div>
    <div class="row g-4">
      <?php
      $leaders = [
        ['name' => 'Ritika Sharma', 'role' => 'Co-founder & CEO', 'img' => 'https://i.pravatar.cc/160?img=32'],
        ['name' => 'Kabir Nair', 'role' => 'Co-founder & Chief Product Officer', 'img' => 'https://i.pravatar.cc/160?img=57'],
        ['name' => 'Vaishnavi Rao', 'role' => 'VP, School Success', 'img' => 'https://i.pravatar.cc/160?img=48'],
        ['name' => 'Arjun Prakash', 'role' => 'Head of Engineering', 'img' => 'https://i.pravatar.cc/160?img=15'],
        ['name' => 'Meera Pillai', 'role' => 'Director of Educator Growth', 'img' => 'https://i.pravatar.cc/160?img=25'],
        ['name' => 'Aniket Bose', 'role' => 'Head of Design', 'img' => 'https://i.pravatar.cc/160?img=67'],
      ];
      foreach ($leaders as $leader): ?>
        <div class="col-md-4 col-sm-6">
          <div class="team-card">
            <img src="<?= htmlspecialchars($leader['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($leader['name'], ENT_QUOTES, 'UTF-8'); ?>">
            <h5><?= htmlspecialchars($leader['name'], ENT_QUOTES, 'UTF-8'); ?></h5>
            <p class="text-muted mb-0"><?= htmlspecialchars($leader['role'], ENT_QUOTES, 'UTF-8'); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <h2 class="section-heading">Our values</h2>
        <p class="section-subtitle">These principles shape how we build, collaborate, and show up for schools and educators daily.</p>
      </div>
      <div class="col-lg-6">
        <div class="row g-4">
          <?php
          $values = [
            ['icon' => '💙', 'title' => 'Empathy for educators', 'body' => 'We listen deeply to teachers and administrators before writing a single line of code.'],
            ['icon' => '🚀', 'title' => 'Progress over perfection', 'body' => 'We ship in small slices, measure impact, and evolve with data-backed insights.'],
            ['icon' => '🔍', 'title' => 'Transparent journeys', 'body' => 'Clarity for teams and applicants is non-negotiable. We communicate openly and often.'],
            ['icon' => '🌍', 'title' => 'Equity & access', 'body' => 'Every release considers accessibility, device constraints, and diverse school contexts.'],
          ];
          foreach ($values as $value): ?>
            <div class="col-sm-6">
              <div class="card-elevated h-100">
                <div class="feature-icon mb-3"><?= htmlspecialchars($value['icon'], ENT_QUOTES, 'UTF-8'); ?></div>
                <h5><?= htmlspecialchars($value['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($value['body'], ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <h2 class="section-heading">Life at LevelMinds</h2>
        <p class="section-subtitle">We are remote-friendly with collaboration hubs in Bengaluru and Hyderabad. Expect design jams, product dojo sessions, and monthly teacher immersion visits.</p>
        <ul class="list-unstyled d-grid gap-3 mt-4">
          <li class="d-flex gap-3">
            <div class="feature-icon">🧪</div>
            <div>
              <strong>Experimentation budget</strong>
              <p class="mb-0 text-muted">Every team member receives learning credits to explore new tools and attend educator conferences.</p>
            </div>
          </li>
          <li class="d-flex gap-3">
            <div class="feature-icon">🧘</div>
            <div>
              <strong>Wellbeing first</strong>
              <p class="mb-0 text-muted">Flexible schedules, wellness resources, and recharge Fridays every quarter.</p>
            </div>
          </li>
          <li class="d-flex gap-3">
            <div class="feature-icon">🤗</div>
            <div>
              <strong>Inclusive culture</strong>
              <p class="mb-0 text-muted">Affinity circles, mentorship programs, and transparent compensation philosophies.</p>
            </div>
          </li>
        </ul>
      </div>
      <div class="col-lg-5">
        <div class="card-elevated">
          <h4 class="mb-3">Open roles</h4>
          <p class="text-muted">We are hiring across product, engineering, customer success, and educator enablement.</p>
          <a class="lm-btn-primary" href="/careers.php">View open positions</a>
        </div>
      </div>
    </div>
  </div>
</section>

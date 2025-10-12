<section class="hero-surface">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <div class="hero-tag">Careers at LevelMinds</div>
        <h1 class="hero-heading">Join the team connecting educators with schools that help them flourish.</h1>
        <p class="hero-subtext">We are builders, teachers, and product thinkers reimagining how talent finds the right classroom. Your work will shape the hiring experience for thousands of educators.</p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a class="lm-btn-primary" href="https://www.lmap.in" target="_blank" rel="noopener">Sign in to apply</a>
          <a class="lm-btn-outline" href="#open-roles">View open roles</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="hero-card">
          <h4 class="mb-3 text-dark">Why LevelMinds?</h4>
          <ul class="list-unstyled d-grid gap-3 mb-0">
            <li class="d-flex gap-3">
              <div class="feature-icon">🌱</div>
              <div>
                <strong>Grow fast</strong>
                <p class="mb-0 text-muted">Own meaningful problems with mentorship from experienced operators.</p>
              </div>
            </li>
            <li class="d-flex gap-3">
              <div class="feature-icon">👥</div>
              <div>
                <strong>Build for impact</strong>
                <p class="mb-0 text-muted">Create products that directly influence how teachers secure fulfilling roles.</p>
              </div>
            </li>
            <li class="d-flex gap-3">
              <div class="feature-icon">🌍</div>
              <div>
                <strong>Hybrid by default</strong>
                <p class="mb-0 text-muted">Remote-friendly with quarterly onsites for collaboration and celebration.</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5" id="open-roles">
  <div class="container">
    <div class="row justify-content-between align-items-center mb-5">
      <div class="col-lg-7">
        <h2 class="section-heading">Open positions</h2>
        <p class="section-subtitle">We hire across product, engineering, design, customer success, and educator growth. Roles listed here are updated monthly.</p>
      </div>
      <div class="col-lg-3 text-lg-end">
        <a class="lm-btn-outline" href="mailto:careers@levelminds.in">Share your profile</a>
      </div>
    </div>
    <div class="row g-4">
      <?php
      $roles = [
        ['team' => 'Product', 'title' => 'Senior Product Manager', 'location' => 'Bengaluru or Remote (India)', 'type' => 'Full-time', 'description' => 'Own the teacher-facing roadmap, prioritise features, and partner with design for delightful experiences.'],
        ['team' => 'Engineering', 'title' => 'Full Stack Engineer (PHP + React)', 'location' => 'Remote (India)', 'type' => 'Full-time', 'description' => 'Build modular services, optimise APIs for shared hosting, and collaborate closely with product pods.'],
        ['team' => 'Customer Success', 'title' => 'Implementation Specialist', 'location' => 'Hyderabad / Bengaluru', 'type' => 'Full-time', 'description' => 'Lead onboarding for new school networks, tailor workflows, and ensure adoption milestones.'],
        ['team' => 'Educator Growth', 'title' => 'Teacher Success Strategist', 'location' => 'Remote (India)', 'type' => 'Full-time', 'description' => 'Support teachers through application journeys, curate resources, and collect platform feedback.'],
      ];
      foreach ($roles as $role): ?>
        <div class="col-lg-6">
          <article class="career-card">
            <span class="badge-lm"><?= htmlspecialchars($role['team'], ENT_QUOTES, 'UTF-8'); ?></span>
            <h4><?= htmlspecialchars($role['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
            <p class="text-muted mb-1"><strong>Location:</strong> <?= htmlspecialchars($role['location'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-muted mb-2"><strong>Type:</strong> <?= htmlspecialchars($role['type'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-muted flex-grow-1"><?= htmlspecialchars($role['description'], ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="d-flex flex-wrap gap-2">
              <a class="lm-btn-primary" href="https://www.lmap.in" target="_blank" rel="noopener">Apply on LevelMinds</a>
              <a class="lm-btn-outline" href="mailto:careers@levelminds.in?subject=Role%20query:%20<?= rawurlencode($role['title']); ?>">Ask about this role</a>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <h2 class="section-heading">Your growth, our priority</h2>
        <p class="section-subtitle">We invest in career progression and wellbeing so you can do your best work.</p>
      </div>
      <div class="col-lg-6">
        <div class="row g-4">
          <?php
          $benefits = [
            ['icon' => '📚', 'title' => 'Learning wallets', 'body' => 'Annual allowance for courses, certifications, and conferences.'],
            ['icon' => '🏖️', 'title' => 'Flexible time off', 'body' => 'Recharge with generous leave, recharge Fridays, and mental health days.'],
            ['icon' => '🩺', 'title' => 'Comprehensive cover', 'body' => 'Medical insurance for you and your family with telemedicine support.'],
            ['icon' => '🤝', 'title' => 'Mentorship circles', 'body' => 'Access cross-functional mentors and leadership coaching.'],
          ];
          foreach ($benefits as $benefit): ?>
            <div class="col-sm-6">
              <div class="card-elevated h-100">
                <div class="feature-icon mb-3"><?= htmlspecialchars($benefit['icon'], ENT_QUOTES, 'UTF-8'); ?></div>
                <h5><?= htmlspecialchars($benefit['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($benefit['body'], ENT_QUOTES, 'UTF-8'); ?></p>
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
      <div class="col-lg-5">
        <h2 class="section-heading">Hiring process</h2>
        <p class="section-subtitle">We keep things transparent and collaborative—expect warm conversations and thoughtful feedback.</p>
      </div>
      <div class="col-lg-7">
        <div class="row g-4">
          <?php
          $process = [
            ['step' => '1', 'title' => 'Profile review', 'body' => 'We read your story, portfolio, and anything else you want to share.'],
            ['step' => '2', 'title' => 'Conversation with hiring manager', 'body' => 'Discuss your experience, motivations, and how you like to work.'],
            ['step' => '3', 'title' => 'Craft exercise', 'body' => 'Collaborative challenge aligned to real work—you choose between async or live.'],
            ['step' => '4', 'title' => 'Team panel', 'body' => 'Meet peers and cross-functional partners to explore collaboration fit.'],
            ['step' => '5', 'title' => 'Offer & onboarding', 'body' => 'We share a transparent offer, benefits, and onboarding plan to set you up for success.'],
          ];
          foreach ($process as $item): ?>
            <div class="col-md-4">
              <div class="card-elevated h-100">
                <span class="badge-lm mb-3">Step <?= htmlspecialchars($item['step'], ENT_QUOTES, 'UTF-8'); ?></span>
                <h5><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($item['body'], ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-light">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <h2 class="section-heading">Let’s craft the future of hiring together.</h2>
        <p class="section-subtitle">Even if you don’t see the perfect role, we’d love to hear from you. Share your interests and we’ll connect when a fit opens up.</p>
      </div>
      <div class="col-lg-5">
        <div class="card-elevated">
          <h4 class="mb-3">Tell us about you</h4>
          <p class="text-muted">Email <a href="mailto:careers@levelminds.in">careers@levelminds.in</a> with your CV, portfolio, or even a Loom introducing yourself.</p>
          <a class="lm-btn-primary" href="mailto:careers@levelminds.in">Drop us a note</a>
        </div>
      </div>
    </div>
  </div>
</section>

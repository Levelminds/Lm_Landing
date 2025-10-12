<section class="page-hero py-5">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <div class="hero-tag">Interactive product walkthrough</div>
        <h1 class="page-hero-title">Experience the LevelMinds hiring command center in minutes.</h1>
        <p class="page-hero-subtext">See how principals, HR partners, and academic leads collaborate in one workspace to attract brilliant teachers, review applications together, and move offers forward without losing context.</p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a class="lm-btn-primary" href="https://www.lmap.in" target="_blank" rel="noopener">Launch LevelMinds Portal</a>
          <a class="lm-btn-outline" href="#tour-flow">Preview the guided flow</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="tour-hero-media">
          <div class="tour-hero-chip">Live dashboard</div>
          <div class="tour-hero-card">
            <span class="tour-hero-label">Today&#39;s snapshot</span>
            <h3>12 open requisitions</h3>
            <p class="mb-0">3 waiting on interviews · 5 in review · 4 ready for offer</p>
          </div>
          <div class="tour-hero-card">
            <span class="tour-hero-label">Teacher spotlight</span>
            <h3>Rohit Sharma shortlisted</h3>
            <p class="mb-0">Mathematics · 8 yrs · Bengaluru · Portfolio uploaded</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5" id="tour-flow">
  <div class="container">
    <div class="row justify-content-between align-items-center mb-5">
      <div class="col-lg-7">
        <h2 class="section-heading">A guided flow that mirrors your actual hiring journey.</h2>
        <p class="section-subtitle">Each step of the tour highlights how schools configure pipelines, invite collaborators, and keep teachers informed—no custom setup required.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <span class="badge-lm">15-minute walkthrough</span>
      </div>
    </div>
    <div class="lm-step-grid">
      <?php
      $steps = [
        [
          'title' => 'Blueprint your pipeline',
          'body' => 'Pick from proven templates or drag-and-drop custom stages for interviews, demo lessons, and leadership approvals.',
          'icon' => '🧭',
        ],
        [
          'title' => 'Launch requisitions with context',
          'body' => 'Centralize job briefs, must-have credentials, and scoring rubrics so every reviewer evaluates consistently.',
          'icon' => '📝',
        ],
        [
          'title' => 'Collaborate in real time',
          'body' => 'Tag principals, collect structured feedback, and nudge interviewers. Everyone sees the same timeline and notes.',
          'icon' => '🤝',
        ],
        [
          'title' => 'Delight teachers with transparency',
          'body' => 'Applicants track their status instantly, respond to document requests, and receive offer letters right in the portal.',
          'icon' => '✨',
        ],
      ];
      $index = 1;
      foreach ($steps as $step): ?>
        <article class="lm-step">
          <div class="lm-step-count">0<?= $index++; ?></div>
          <div class="feature-icon mb-3"><?= htmlspecialchars($step['icon'], ENT_QUOTES, 'UTF-8'); ?></div>
          <h3><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <p class="text-muted mb-0"><?= htmlspecialchars($step['body'], ENT_QUOTES, 'UTF-8'); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5 bg-light" id="collaboration">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <span class="badge-lm mb-3">Made for collaborative teams</span>
        <h2 class="section-heading">Tools that shorten decision cycles.</h2>
        <p class="section-subtitle">From first application to signed offer, the tour showcases how LevelMinds removes manual handoffs and keeps leadership aligned.</p>
        <ul class="list-unstyled d-grid gap-3 mt-4">
          <li class="d-flex gap-3">
            <div class="feature-icon">📣</div>
            <div>
              <strong>Shared inbox</strong>
              <p class="mb-0 text-muted">Route candidate updates to principals and HR leads with auto-assigned follow-ups.</p>
            </div>
          </li>
          <li class="d-flex gap-3">
            <div class="feature-icon">📊</div>
            <div>
              <strong>Realtime analytics</strong>
              <p class="mb-0 text-muted">Monitor funnel health, stage duration, and offer acceptance rates as you progress through the tour.</p>
            </div>
          </li>
          <li class="d-flex gap-3">
            <div class="feature-icon">🔐</div>
            <div>
              <strong>Secure access controls</strong>
              <p class="mb-0 text-muted">Decide who sees what—guest reviewers view anonymized profiles, while admins manage the full pipeline.</p>
            </div>
          </li>
        </ul>
      </div>
      <div class="col-lg-6">
        <div class="card-elevated h-100 tour-analytics">
          <h4 class="mb-4">What the tour reveals</h4>
          <ul class="list-unstyled d-grid gap-3 mb-0">
            <li>
              <strong>Pipeline efficiency score</strong>
              <p class="mb-0 text-muted">Benchmark your average time-in-stage against top-performing schools on LevelMinds.</p>
            </li>
            <li>
              <strong>Teacher experience insights</strong>
              <p class="mb-0 text-muted">Sample feedback from applicants, highlighting clarity, communication cadence, and onboarding readiness.</p>
            </li>
            <li>
              <strong>Enablement resources</strong>
              <p class="mb-0 text-muted">Download guides for hiring committees, teacher onboarding checklists, and communication templates.</p>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-5">
        <span class="badge-lm mb-3">Tailored for your institution</span>
        <h2 class="section-heading">Custom tours for every network.</h2>
        <p class="section-subtitle">Whether you operate a single campus or a multi-city network, we craft a walkthrough that mirrors your structure, approvals, and teacher cohorts.</p>
      </div>
      <div class="col-lg-7">
        <div class="row g-4">
          <?php
          $personas = [
            ['title' => 'Independent schools', 'body' => 'Highlight nimble pipelines, share best practices for a small recruitment team, and surface time-saving automations.'],
            ['title' => 'School groups & chains', 'body' => 'Demonstrate how multi-campus approvals, centralized talent pools, and governance controls operate in LevelMinds.'],
            ['title' => 'Trusts & NGOs', 'body' => 'Focus on inclusive hiring, compliance checks, and visibility across remote or hybrid recruiting teams.'],
          ];
          foreach ($personas as $persona): ?>
            <div class="col-md-4">
              <div class="card-elevated h-100 text-center">
                <h5><?= htmlspecialchars($persona['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($persona['body'], ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-light text-center">
  <div class="container">
    <h2 class="section-heading">Ready to see the full experience?</h2>
    <p class="section-subtitle mx-auto">Share a few details about your hiring goals and we&#39;ll curate a personalised tour with your data in mind.</p>
    <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
      <a class="lm-btn-primary" href="/contact.php">Book a guided session</a>
      <a class="lm-btn-outline" href="https://www.lmap.in" target="_blank" rel="noopener">Jump straight to the platform</a>
    </div>
  </div>
</section>

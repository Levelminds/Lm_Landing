<section class="page-hero soft-gradient py-5">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <div class="hero-tag">Platform solutions</div>
        <h1 class="page-hero-title">One platform, tailored experiences for every hiring stakeholder.</h1>
        <p class="page-hero-subtext">LevelMinds delivers configurable journeys that let school networks, independent institutions, and teachers collaborate seamlessly—from requisition to onboarding.</p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a class="lm-btn-primary" href="https://www.lmap.in" target="_blank" rel="noopener">Access live platform</a>
          <a class="lm-btn-outline" href="/tour.php">Book a guided tour</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="solutions-hero-card">
          <h3>What you&#39;ll configure</h3>
          <ul class="list-unstyled d-grid gap-3 mb-0">
            <li>
              <strong>Hiring journeys</strong>
              <p class="mb-0 text-muted">Setup stages, scoring guides, and notifications that mirror your process.</p>
            </li>
            <li>
              <strong>Collaboration rules</strong>
              <p class="mb-0 text-muted">Invite principals, HR partners, and academic directors with precise permissions.</p>
            </li>
            <li>
              <strong>Teacher experiences</strong>
              <p class="mb-0 text-muted">Deliver transparent dashboards, document requests, and milestone updates to applicants.</p>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5" id="personas">
  <div class="container">
    <div class="row justify-content-between align-items-end mb-4">
      <div class="col-lg-7">
        <h2 class="section-heading">Solutions for every type of institution.</h2>
        <p class="section-subtitle">Select the journey that matches your structure. Each solution includes curated workflows, KPIs, and best practices from schools already hiring with LevelMinds.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <span class="badge-lm">Flexible by design</span>
      </div>
    </div>
    <div class="row g-4">
      <?php
      $audiences = [
        [
          'label' => 'Independent schools',
          'body' => 'Streamline annual teacher recruitment with reusable templates, SMS/email nudges, and applicant scoring dashboards.',
          'cta' => 'View the independent school tour',
        ],
        [
          'label' => 'School groups & trusts',
          'body' => 'Coordinate multi-campus approvals, calibrate hiring velocity, and ensure brand consistency across every branch.',
          'cta' => 'Explore multi-campus workflows',
        ],
        [
          'label' => 'Teacher communities',
          'body' => 'Empower educators with profile builders, opportunity alerts, and a unified application inbox for every institution.',
          'cta' => 'See the teacher journey',
        ],
      ];
      foreach ($audiences as $audience): ?>
        <div class="col-md-4">
          <div class="card-elevated h-100">
            <h5><?= htmlspecialchars($audience['label'], ENT_QUOTES, 'UTF-8'); ?></h5>
            <p class="text-muted"><?= htmlspecialchars($audience['body'], ENT_QUOTES, 'UTF-8'); ?></p>
            <a class="link-primary" href="/tour.php"><?= htmlspecialchars($audience['cta'], ENT_QUOTES, 'UTF-8'); ?> →</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5 bg-light" id="capabilities">
  <div class="container">
    <div class="row g-4 align-items-start">
      <div class="col-lg-5">
        <span class="badge-lm mb-3">Core capabilities</span>
        <h2 class="section-heading">Everything you need to run hiring like a product team.</h2>
        <p class="section-subtitle">LevelMinds provides out-of-the-box automation with the guardrails required for education hiring. Enable only what you need—no developer dependency.</p>
      </div>
      <div class="col-lg-7">
        <div class="row g-4">
          <?php
          $capabilities = [
            ['icon' => '🧮', 'title' => 'Intelligent scoring', 'body' => 'Weight certifications, pedagogy experience, and cultural signals to surface best-fit candidates.'],
            ['icon' => '📂', 'title' => 'Centralized documents', 'body' => 'Collect lesson plans, demo recordings, and references with automated reminders.'],
            ['icon' => '⚡', 'title' => 'Automation rules', 'body' => 'Trigger notifications, panel invites, and status changes when applications move forward.'],
            ['icon' => '🌐', 'title' => 'Marketplace-ready APIs', 'body' => 'Expose future job feeds and teacher profiles securely to other LevelMinds surfaces.'],
          ];
          foreach ($capabilities as $capability): ?>
            <div class="col-sm-6">
              <div class="card-elevated h-100">
                <div class="feature-icon mb-3"><?= htmlspecialchars($capability['icon'], ENT_QUOTES, 'UTF-8'); ?></div>
                <h5><?= htmlspecialchars($capability['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($capability['body'], ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5" id="implementation">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-5">
        <h2 class="section-heading">Launch in weeks, not months.</h2>
        <p class="section-subtitle">Our implementation specialists co-pilot onboarding so your teams adopt LevelMinds without heavy lifts.</p>
      </div>
      <div class="col-lg-7">
        <div class="lm-timeline">
          <?php
          $timeline = [
            ['title' => 'Discover & map requirements', 'body' => 'Review existing workflows, data sources, and hiring KPIs to plan the rollout.'],
            ['title' => 'Configure journeys & import data', 'body' => 'Set up stages, automation, and user roles; migrate active requisitions into the system.'],
            ['title' => 'Enable & optimize', 'body' => 'Train stakeholders, monitor adoption, and iterate with analytics-led recommendations.'],
          ];
          foreach ($timeline as $item): ?>
            <div class="lm-timeline-item">
              <div class="lm-timeline-marker"></div>
              <div>
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

<section class="py-5 bg-light" id="case-studies">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <div class="case-study-card">
          <h4>Case study: NorthStar Academies</h4>
          <p class="text-muted">A five-campus network that reduced teacher hiring time by 58% while improving applicant satisfaction scores.</p>
          <ul class="list-unstyled d-grid gap-2 mb-4">
            <li><strong>Challenge:</strong> Disconnected approvals and manual applicant updates.</li>
            <li><strong>Solution:</strong> Multi-campus pipeline templates, shared comments, and automated offer generation.</li>
            <li><strong>Impact:</strong> 3x faster shortlist cycles and a 35% increase in accepted offers.</li>
          </ul>
          <a class="lm-btn-outline" href="/contact.php">Request the full playbook</a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card-elevated h-100">
          <h5 class="mb-4">Why schools choose LevelMinds</h5>
          <ul class="list-unstyled d-grid gap-3 mb-0">
            <li>
              <strong>Built for India</strong>
              <p class="mb-0 text-muted">Supports CBSE, ICSE, IB, and state-board processes with compliance-ready templates.</p>
            </li>
            <li>
              <strong>Teacher-first ethos</strong>
              <p class="mb-0 text-muted">Crafted with educator feedback to ensure clarity, respect, and career progression.</p>
            </li>
            <li>
              <strong>Scales with your network</strong>
              <p class="mb-0 text-muted">From single campus to nationwide group, LevelMinds grows with your teams.</p>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 text-center">
  <div class="container">
    <h2 class="section-heading">Let&#39;s co-create your ideal hiring flow.</h2>
    <p class="section-subtitle mx-auto">Share your goals and we&#39;ll tailor a plan that meets your teachers, leadership, and compliance needs.</p>
    <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
      <a class="lm-btn-primary" href="/contact.php">Talk to our team</a>
      <a class="lm-btn-outline" href="/careers.php">Join LevelMinds</a>
    </div>
  </div>
</section>

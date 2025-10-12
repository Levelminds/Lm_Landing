<section class="page-hero soft-gradient py-5">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <div class="hero-tag">About LevelMinds</div>
        <h1 class="page-hero-title">We build hiring journeys that help educators and schools thrive.</h1>
        <p class="page-hero-subtext">LevelMinds was founded by former school leaders, product builders, and teacher mentors who believe every classroom deserves the right talent. Our platform bridges schools and educators with transparency, empathy, and measurable outcomes.</p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a class="lm-btn-primary" href="/team.php">Meet the leadership team</a>
          <a class="lm-btn-outline" href="/careers.php">Join our mission</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="about-highlight">
          <h3>Impact snapshot</h3>
          <ul class="list-unstyled d-grid gap-3 mb-0">
            <li><strong>200+</strong> schools orchestrating hiring pipelines with LevelMinds.</li>
            <li><strong>20,000+</strong> teacher applications tracked with real-time status updates.</li>
            <li><strong>4.9/5</strong> average satisfaction from principals and HR partners.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5" id="mission">
  <div class="container">
    <div class="row align-items-start g-5">
      <div class="col-lg-6">
        <h2 class="section-heading">Our mission</h2>
        <p class="section-subtitle">Unlock educator-led classrooms by giving schools a unified hiring command center and teachers a transparent journey.</p>
        <p class="mb-4 text-muted">We partner with institutions across metros and tier-2 cities, co-designing workflows that respect time, surface the right talent, and promote inclusive hiring. From the start, LevelMinds has been remote-friendly with collaboration hubs in Bengaluru and Hyderabad.</p>
        <div class="d-flex flex-wrap gap-3">
          <div class="metric-card">
            <h3>3x</h3>
            <p class="mb-0 text-muted">Faster offer cycles after adopting LevelMinds automation.</p>
          </div>
          <div class="metric-card">
            <h3>87%</h3>
            <p class="mb-0 text-muted">Teachers reporting higher confidence in their hiring experience.</p>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card-elevated h-100">
          <h4 class="mb-4">What guides us</h4>
          <ul class="list-unstyled d-grid gap-3 mb-0">
            <li>
              <strong>Teacher empathy</strong>
              <p class="mb-0 text-muted">We listen deeply to educators, ensuring every feature respects their time and aspirations.</p>
            </li>
            <li>
              <strong>Data with heart</strong>
              <p class="mb-0 text-muted">Analytics drive insight, but human stories shape how we prioritize roadmaps.</p>
            </li>
            <li>
              <strong>Shared accountability</strong>
              <p class="mb-0 text-muted">Hiring is a team sport. We create spaces where principals, HR, and teachers collaborate.</p>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-light" id="milestones">
  <div class="container">
    <div class="row justify-content-between align-items-center mb-4">
      <div class="col-lg-7">
        <h2 class="section-heading">Milestones we&#39;re proud of.</h2>
        <p class="section-subtitle">Each chapter represents co-creation with schools and teachers who trusted LevelMinds to reimagine hiring.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <span class="badge-lm">Built in India · Impacting globally</span>
      </div>
    </div>
    <div class="lm-timeline">
      <?php
      $milestones = [
        ['title' => '2021 · Prototype and pilot', 'body' => 'Tested early versions with 5 Bengaluru schools to validate workflows and teacher transparency.'],
        ['title' => '2022 · Marketplace launch', 'body' => 'Introduced the teacher portal, enabling educators to build profiles, apply, and track status in one place.'],
        ['title' => '2023 · Scale to networks', 'body' => 'Expanded to multi-campus trusts, adding governance, analytics, and integrations.'],
        ['title' => '2024 · Hiring intelligence', 'body' => 'Rolled out predictive insights to help schools forecast hiring velocity and educator availability.'],
      ];
      foreach ($milestones as $milestone): ?>
        <div class="lm-timeline-item">
          <div class="lm-timeline-marker"></div>
          <div>
            <h5><?= htmlspecialchars($milestone['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
            <p class="text-muted mb-0"><?= htmlspecialchars($milestone['body'], ENT_QUOTES, 'UTF-8'); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5" id="advisors">
  <div class="container">
    <div class="row align-items-center mb-4">
      <div class="col-lg-6">
        <h2 class="section-heading">Advisors & educator council</h2>
        <p class="section-subtitle">Leaders from K-12, higher education, and edtech help us stay grounded in classroom realities.</p>
      </div>
      <div class="col-lg-6 text-lg-end">
        <a class="lm-btn-outline" href="/team.php">See our core team</a>
      </div>
    </div>
    <div class="row g-4">
      <?php
      $advisors = [
        ['name' => 'Dr. Ananya Patel', 'role' => 'Former School Director · Advisory Chair'],
        ['name' => 'Mohan Deshpande', 'role' => 'Education Technology Strategist'],
        ['name' => 'Priya Menon', 'role' => 'Teacher Development Coach'],
        ['name' => 'Rahul Bansal', 'role' => 'HR Leader, Multi-campus Trust'],
      ];
      foreach ($advisors as $advisor): ?>
        <div class="col-md-3 col-sm-6">
          <div class="advisor-card">
            <h5><?= htmlspecialchars($advisor['name'], ENT_QUOTES, 'UTF-8'); ?></h5>
            <p class="text-muted mb-0"><?= htmlspecialchars($advisor['role'], ENT_QUOTES, 'UTF-8'); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5 bg-light" id="culture">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <h2 class="section-heading">Our culture in action.</h2>
        <p class="section-subtitle">We operate as distributed squads with rituals that keep us connected to educators every week.</p>
        <ul class="list-unstyled d-grid gap-3 mt-4">
          <li class="d-flex gap-3">
            <div class="feature-icon">🧪</div>
            <div>
              <strong>Product dojos</strong>
              <p class="mb-0 text-muted">Cross-functional sprints where designers, engineers, and teacher liaisons prototype new flows.</p>
            </div>
          </li>
          <li class="d-flex gap-3">
            <div class="feature-icon">🎤</div>
            <div>
              <strong>Teacher panels</strong>
              <p class="mb-0 text-muted">Monthly sessions with educators sharing classroom realities and feedback on upcoming releases.</p>
            </div>
          </li>
          <li class="d-flex gap-3">
            <div class="feature-icon">🌱</div>
            <div>
              <strong>Growth stipends</strong>
              <p class="mb-0 text-muted">Learning credits for conferences, certifications, and wellbeing experiences.</p>
            </div>
          </li>
        </ul>
      </div>
      <div class="col-lg-6">
        <div class="card-elevated h-100">
          <h4 class="mb-3">Life at LevelMinds</h4>
          <p class="text-muted">We are remote-first with regional collaboration hubs. Expect weekly design critiques, teacher immersion visits, and hack weeks dedicated to educator experience improvements.</p>
          <a class="lm-btn-primary mt-3" href="/careers.php">View open roles</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 text-center">
  <div class="container">
    <h2 class="section-heading">Let’s build the future of educator hiring together.</h2>
    <p class="section-subtitle mx-auto">Schools, teachers, and partners who share our mission are invited to collaborate, co-design features, and share impact stories.</p>
    <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
      <a class="lm-btn-primary" href="/contact.php">Partner with us</a>
      <a class="lm-btn-outline" href="https://www.lmap.in" target="_blank" rel="noopener">Try LevelMinds</a>
    </div>
  </div>
</section>

<section class="page-hero py-5">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <div class="hero-tag">Pricing & packaging</div>
        <h1 class="page-hero-title">Flexible plans that scale with your hiring ambitions.</h1>
        <p class="page-hero-subtext">Start with a plan that matches your campus size and upgrade as you expand. Every subscription includes implementation support, training, and continuous product updates.</p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a class="lm-btn-primary" href="/contact.php">Talk to sales</a>
          <a class="lm-btn-outline" href="/tour.php">Preview the platform tour</a>
        </div>
        <p class="text-muted mt-3 mb-0">All plans integrate with the LevelMinds teacher marketplace and analytics dashboards.</p>
      </div>
      <div class="col-lg-5">
        <div class="pricing-highlight">
          <h4>Need a custom rollout?</h4>
          <p class="text-muted">Enterprise pricing includes tailored onboarding, dedicated success specialists, and advanced governance controls.</p>
          <a class="lm-btn-outline" href="mailto:hello@levelminds.in">Email our partnerships team</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-light" id="plans">
  <div class="container">
    <div class="row g-4">
      <?php
      $tiers = [
        [
          'name' => 'Emerging Campus',
          'price' => '₹14,999/mo',
          'description' => 'Perfect for independent schools running up to 5 concurrent requisitions.',
          'features' => [
            'Up to 10 hiring team members',
            'Pipeline templates & automation rules',
            'Teacher portal with status tracking',
            'Email + chat support',
          ],
        ],
        [
          'name' => 'Growth Network',
          'price' => '₹29,999/mo',
          'description' => 'For school groups or trusts with multi-campus hiring needs and governance layers.',
          'features' => [
            'Unlimited requisitions & talent pools',
            'Role-based access and approval routing',
            'Custom analytics dashboards',
            'Dedicated success manager',
          ],
          'popular' => true,
        ],
        [
          'name' => 'Nationwide Enterprise',
          'price' => 'Let’s discuss',
          'description' => 'Designed for large networks, NGOs, and education brands hiring across regions.',
          'features' => [
            'Single sign-on (SSO) & advanced compliance',
            'Data residency & private cloud options',
            'API access for marketplace integrations',
            'Onsite & virtual enablement programs',
          ],
        ],
      ];
      foreach ($tiers as $tier): ?>
        <div class="col-md-4">
          <div class="lm-pricing-card<?= !empty($tier['popular']) ? ' is-popular' : ''; ?>">
            <?php if (!empty($tier['popular'])): ?>
              <span class="lm-pricing-badge">Most popular</span>
            <?php endif; ?>
            <h3><?= htmlspecialchars($tier['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p class="lm-pricing-price"><?= htmlspecialchars($tier['price'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-muted mb-4"><?= htmlspecialchars($tier['description'], ENT_QUOTES, 'UTF-8'); ?></p>
            <ul class="list-unstyled pricing-feature-list mb-4">
              <?php foreach ($tier['features'] as $feature): ?>
                <li><?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8'); ?></li>
              <?php endforeach; ?>
            </ul>
            <a class="lm-btn-primary w-100" href="/contact.php">Schedule a demo</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5" id="inclusions">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-5">
        <h2 class="section-heading">Every plan includes LevelMinds essentials.</h2>
        <p class="section-subtitle">We invest in your success with structured onboarding, educator support, and continuous enhancements.</p>
      </div>
      <div class="col-lg-7">
        <div class="row g-4">
          <?php
          $inclusions = [
            ['icon' => '🎓', 'title' => 'Teacher-first workflows', 'body' => 'Application updates, interview prep, and offer management built with educator feedback.'],
            ['icon' => '📈', 'title' => 'Insights & reporting', 'body' => 'Track time-to-hire, funnel conversion, and diversity indicators in real time.'],
            ['icon' => '🤝', 'title' => 'Dedicated enablement', 'body' => 'Implementation specialists configure your pipeline and train your hiring squads.'],
            ['icon' => '🔒', 'title' => 'Secure hosting', 'body' => 'Encrypted data at rest and in transit, with audit trails and access controls.'],
          ];
          foreach ($inclusions as $inclusion): ?>
            <div class="col-sm-6">
              <div class="card-elevated h-100">
                <div class="feature-icon mb-3"><?= htmlspecialchars($inclusion['icon'], ENT_QUOTES, 'UTF-8'); ?></div>
                <h5><?= htmlspecialchars($inclusion['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($inclusion['body'], ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-light" id="faq">
  <div class="container">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-7 text-center">
        <h2 class="section-heading">Frequently asked questions</h2>
        <p class="section-subtitle">Can’t find what you need? <a href="mailto:hello@levelminds.in">Email us</a> and we’ll help you choose the right plan.</p>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="lm-faq">
          <?php
          $faqs = [
            ['question' => 'Do you offer annual billing discounts?', 'answer' => 'Yes. Schools that opt for annual subscriptions receive 2 months complimentary access plus a dedicated success review each quarter.'],
            ['question' => 'Can we integrate with our HRMS or ERP?', 'answer' => 'Growth and Enterprise plans include API access and SFTP exports so you can sync candidate data with HR systems, payroll, or BI dashboards.'],
            ['question' => 'How does onboarding work?', 'answer' => 'We host discovery workshops, configure your pipeline, migrate existing requisitions, and train your hiring committees. Typical go-live takes 3–4 weeks.'],
            ['question' => 'Is there pricing for teacher communities?', 'answer' => 'Educators always join LevelMinds free. Schools and trusts fund the platform so teachers gain a transparent, empowering experience.'],
          ];
          foreach ($faqs as $faq): ?>
            <div class="lm-faq-item">
              <button class="lm-faq-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#faq-<?= md5($faq['question']); ?>" aria-expanded="false">
                <?= htmlspecialchars($faq['question'], ENT_QUOTES, 'UTF-8'); ?>
                <span class="lm-faq-icon">+</span>
              </button>
              <div class="collapse" id="faq-<?= md5($faq['question']); ?>">
                <p class="text-muted mb-0 pt-3"><?= htmlspecialchars($faq['answer'], ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 text-center">
  <div class="container">
    <h2 class="section-heading">Partner with us for the next hiring season.</h2>
    <p class="section-subtitle mx-auto">Share your hiring targets and we’ll recommend a plan, onboarding timeline, and success metrics.</p>
    <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
      <a class="lm-btn-primary" href="/contact.php">Start the conversation</a>
      <a class="lm-btn-outline" href="https://www.lmap.in" target="_blank" rel="noopener">Explore the live platform</a>
    </div>
  </div>
</section>

<section class="policy-hero py-5">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-8">
        <h1 class="page-hero-title">Privacy Policy</h1>
        <p class="page-hero-subtext">We respect the trust you place in LevelMinds. This policy explains how we collect, use, and safeguard information for schools, teachers, and partners who engage with our marketing site and platform.</p>
        <p class="text-muted mb-0">Effective date: January 1, 2024 · Last updated: <?= date('F j, Y'); ?></p>
      </div>
      <div class="col-lg-4">
        <div class="policy-summary card-elevated">
          <h5>Questions?</h5>
          <p class="text-muted">Email <a href="mailto:privacy@levelminds.in">privacy@levelminds.in</a> and we’ll respond within 2 business days.</p>
          <a class="lm-btn-outline" href="/contact.php">Contact support</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <?php
    $sections = [
      [
        'title' => '1. Information we collect',
        'items' => [
          'Contact details provided through forms (name, email, phone, institution).',
          'Usage data captured via analytics tools to improve content and performance.',
          'Information submitted during demos, surveys, or waitlist signups.',
        ],
      ],
      [
        'title' => '2. How we use information',
        'items' => [
          'Respond to enquiries, schedule demos, and deliver requested resources.',
          'Personalise marketing content and product recommendations.',
          'Improve product features, security, and customer experience.',
        ],
      ],
      [
        'title' => '3. Sharing with third parties',
        'items' => [
          'We do not sell personal data. We may share information with trusted service providers (e.g., email, analytics) who process data on our behalf.',
          'Disclosures may be required to comply with legal obligations or protect the rights of LevelMinds and our users.',
        ],
      ],
      [
        'title' => '4. Data retention & security',
        'items' => [
          'Information is stored on secure servers hosted in India with encryption at rest and in transit.',
          'We retain contact and usage data for as long as needed to provide services or comply with legal requirements.',
          'Access is restricted to authorised LevelMinds team members and partners bound by confidentiality.',
        ],
      ],
      [
        'title' => '5. Your choices',
        'items' => [
          'Update or delete your information by emailing <a href="mailto:privacy@levelminds.in">privacy@levelminds.in</a>.',
          'Opt out of marketing emails via the unsubscribe link or by contacting us.',
          'Disable cookies in your browser; note that some features may not function optimally.',
        ],
      ],
      [
        'title' => "6. Children's privacy",
        'items' => [
          'LevelMinds is designed for institutions and educators. We do not knowingly collect data from children under 16.',
          'If you believe a child provided personal information, please notify us so we can remove it promptly.',
        ],
      ],
      [
        'title' => '7. Policy updates',
        'items' => [
          'We may update this policy to reflect product or regulatory changes. Updated versions will include a revised effective date.',
          'Significant updates will be communicated via email or prominent notices on our site.',
        ],
      ],
    ];
    foreach ($sections as $section): ?>
      <article class="policy-section">
        <h2><?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <ul>
          <?php foreach ($section['items'] as $item): ?>
            <li><?= $item; ?></li>
          <?php endforeach; ?>
        </ul>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-7">
        <h2 class="section-heading">We are committed to responsible data practices.</h2>
        <p class="section-subtitle">Security reviews, privacy-by-design standards, and transparent communication help us protect educators and schools.</p>
      </div>
      <div class="col-lg-5 text-lg-end">
        <a class="lm-btn-primary" href="mailto:privacy@levelminds.in">Request a DPA</a>
        <a class="lm-btn-outline ms-lg-3 mt-3 mt-lg-0" href="/terms-conditions.php">Read our Terms</a>
      </div>
    </div>
  </div>
</section>

<section class="policy-hero py-5">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-8">
        <h1 class="page-hero-title">Terms of Service</h1>
        <p class="page-hero-subtext">These terms govern access to the LevelMinds marketing site and related services. By visiting our pages or requesting demos, you agree to the responsibilities outlined below.</p>
        <p class="text-muted mb-0">Effective date: January 1, 2024 · Last updated: <?= date('F j, Y'); ?></p>
      </div>
      <div class="col-lg-4">
        <div class="policy-summary card-elevated">
          <h5>Need help interpreting these terms?</h5>
          <p class="text-muted">Email <a href="mailto:legal@levelminds.in">legal@levelminds.in</a> for clarifications or partnership agreements.</p>
          <a class="lm-btn-outline" href="/contact.php">Talk to our team</a>
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
        'title' => '1. Acceptance of terms',
        'items' => [
          'By accessing LevelMinds marketing pages or submitting forms, you agree to these terms and our Privacy Policy.',
          'If you represent a school, organisation, or teacher community, you confirm you are authorised to share relevant information.',
        ],
      ],
      [
        'title' => '2. Use of content',
        'items' => [
          'All copy, graphics, and downloadable resources are the intellectual property of LevelMinds unless stated otherwise.',
          'You may reference our materials for non-commercial purposes with proper attribution.',
        ],
      ],
      [
        'title' => '3. Demo requests and submissions',
        'items' => [
          'Information provided through forms must be accurate. You are responsible for ensuring the consent of individuals whose data you share.',
          'LevelMinds may contact you via email or phone to deliver demos, onboarding resources, or product updates.',
        ],
      ],
      [
        'title' => '4. Platform access',
        'items' => [
          'Access to the LevelMinds hiring portal at lmap.in is governed by separate agreements and user terms.',
          'We reserve the right to suspend access if misuse, security risk, or unauthorised activity is detected.',
        ],
      ],
      [
        'title' => '5. Limitation of liability',
        'items' => [
          'LevelMinds is not liable for indirect or consequential damages resulting from use of marketing materials or demo experiences.',
          'We strive for accuracy but do not guarantee uninterrupted availability of the site.',
        ],
      ],
      [
        'title' => '6. Changes to services',
        'items' => [
          'We may update content, features, or pricing without prior notice. Major changes will be announced on our site or via email.',
        ],
      ],
      [
        'title' => '7. Governing law',
        'items' => [
          'These terms are governed by the laws of India. Disputes will be handled in Bengaluru, Karnataka.',
        ],
      ],
      [
        'title' => '8. Contact',
        'items' => [
          'For legal enquiries, email <a href="mailto:legal@levelminds.in">legal@levelminds.in</a> or write to LevelMinds HQ, Bengaluru, India.',
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
        <h2 class="section-heading">Keeping our partnership transparent.</h2>
        <p class="section-subtitle">We share updates when our terms evolve and welcome feedback that keeps LevelMinds aligned with educator and institution needs.</p>
      </div>
      <div class="col-lg-5 text-lg-end">
        <a class="lm-btn-primary" href="mailto:legal@levelminds.in">Request contract support</a>
        <a class="lm-btn-outline ms-lg-3 mt-3 mt-lg-0" href="/privacy-policy.php">Read the Privacy Policy</a>
      </div>
    </div>
  </div>
</section>

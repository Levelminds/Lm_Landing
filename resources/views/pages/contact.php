<section class="hero-surface">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <div class="hero-tag">Contact LevelMinds</div>
        <h1 class="hero-heading">We’d love to hear from you.</h1>
        <p class="hero-subtext">Whether you are a school exploring LevelMinds or a teacher seeking guidance, our team is ready to help. Reach out and we’ll respond within one business day.</p>
      </div>
      <div class="col-lg-5">
        <div class="hero-card">
          <h4 class="mb-3 text-dark">Need immediate assistance?</h4>
          <p class="text-muted mb-1"><strong>Email:</strong> <a class="text-decoration-none" href="mailto:hello@levelminds.in">hello@levelminds.in</a></p>
          <p class="text-muted mb-1"><strong>Phone:</strong> <a class="text-decoration-none" href="tel:+919876543210">+91 98765 43210</a></p>
          <p class="text-muted mb-0"><strong>Address:</strong> LevelMinds HQ, Bengaluru, India</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5">
        <div class="card-elevated h-100">
          <h4 class="mb-3">How can we help?</h4>
          <p class="text-muted">Select the path that best matches your question—we’ll route it to the right specialist.</p>
          <ul class="list-unstyled d-grid gap-3 mt-4">
            <li class="d-flex gap-3">
              <div class="feature-icon">🏫</div>
              <div>
                <strong>Schools & institutions</strong>
                <p class="mb-0 text-muted">Request a product walkthrough, pricing details, or onboarding support.</p>
              </div>
            </li>
            <li class="d-flex gap-3">
              <div class="feature-icon">👩‍🏫</div>
              <div>
                <strong>Teachers</strong>
                <p class="mb-0 text-muted">Get help with applications, profile setup, or LevelMinds best practices.</p>
              </div>
            </li>
            <li class="d-flex gap-3">
              <div class="feature-icon">🤝</div>
              <div>
                <strong>Partnerships</strong>
                <p class="mb-0 text-muted">Collaborate on events, content, or community initiatives.</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="contact-card">
          <h4 class="mb-3">Send us a message</h4>
          <form id="contact-form" method="post" novalidate>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="contact-name">Full name</label>
                <input class="form-control" id="contact-name" name="name" type="text" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="contact-email">Email</label>
                <input class="form-control" id="contact-email" name="email" type="email" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="contact-phone">Phone</label>
                <input class="form-control" id="contact-phone" name="phone" type="tel" placeholder="Optional">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="contact-subject">Subject</label>
                <input class="form-control" id="contact-subject" name="subject" type="text" placeholder="How can we help?">
              </div>
              <div class="col-12">
                <label class="form-label" for="contact-message">Message</label>
                <textarea class="form-control" id="contact-message" name="message" rows="4" required></textarea>
              </div>
            </div>
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3 mt-4">
              <button class="lm-btn-primary" type="submit">Send message</button>
              <div class="text-muted" id="contact-status" role="status" aria-live="polite"></div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <h2 class="section-heading">Visit our collaboration hub</h2>
        <p class="section-subtitle">We host workshops and onboarding sessions from our Bengaluru experience centre. Book a visit to see LevelMinds in action.</p>
      </div>
      <div class="col-lg-6">
        <div class="card-elevated">
          <h5 class="mb-2">LevelMinds HQ</h5>
          <p class="text-muted mb-3">91 Springboard, Koramangala, Bengaluru, India</p>
          <iframe class="w-100 rounded" height="220" style="border:0" loading="lazy" allowfullscreen src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.0757875598906!2d77.62710571533402!3d12.936238719196427!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae15d45e42f59b%3A0x8d9b46ac3f621f5a!2sKoramangala%2091springboard!5e0!3m2!1sen!2sin!4v1700000000000"></iframe>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
(function () {
  const form = document.getElementById('contact-form');
  if (!form) return;

  const status = document.getElementById('contact-status');
  const endpoint = 'api/contact.php';

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    status.textContent = 'Sending…';
    status.classList.remove('text-success', 'text-danger');

    const formData = new FormData(form);

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'application/json' }
      });

      if (!response.ok) {
        throw new Error('Network error');
      }

      const data = await response.json();
      if (data.success) {
        status.textContent = data.message || 'Message sent successfully.';
        status.classList.add('text-success');
        form.reset();
      } else {
        throw new Error(data.error || 'Something went wrong.');
      }
    } catch (error) {
      status.textContent = error.message;
      status.classList.add('text-danger');
    }
  });
})();
</script>

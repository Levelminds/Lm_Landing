(function () {
  const footerHTML = `
<footer class="footer pt-5 pb-5">
  <div class="container" style="max-width: 1140px;">
    <div class="row gy-5">
      <div class="col-md-4">
        <h3 class="mb-3 text-dark">LevelMinds</h3>
        <p class="mb-4" style="color: rgba(15,29,59,0.7);">LevelMinds helps schools hire great teachers and empowers educators to showcase their impact. It's a free ecosystem for connecting talent with opportunity.</p>
        <div class="social-links d-flex gap-3 mt-4">
          <a href="https://www.linkedin.com/company/level-mind/about/?viewAsMember=true" class="text-dark" target="_blank" rel="noopener"><i class="bi bi-linkedin fs-5"></i></a>
        </div>
      </div>
      <div class="col-md-8">
        <div class="d-flex justify-content-center align-items-center h-100">
          <a href="tour.html" class="btn btn-primary btn-lg px-5">Explore More</a>
        </div>
      </div>
    </div>
    <div class="row mt-4 pt-3" style="border-top: 1px solid rgba(7,27,54,0.08);">
      <div class="col-md-6">
        <small style="color: rgba(15,29,59,0.5);">&copy; 2024 LevelMinds. All rights reserved.</small>
      </div>
      <div class="col-md-6 text-md-end">
        <a href="privacy-policy.html" style="color: rgba(15,29,59,0.7);" class="me-3">Privacy Policy</a>
        <a href="terms-conditions.html" style="color: rgba(15,29,59,0.7);">Terms</a>
      </div>
    </div>
  </div>
</footer>`;

  document.querySelectorAll('[data-global-footer]').forEach(function (slot) {
    slot.innerHTML = footerHTML;
  });
})();

(function () {
  function onReady(callback) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback);
    } else {
      callback();
    }
  }

  function initNewsletterFeedback() {
    var forms = document.querySelectorAll('form[action$="newsletter.php"]');
    if (!forms.length) {
      return;
    }

    var params = new URLSearchParams(window.location.search);
    var status = params.get('newsletter');
    if (status) {
      params.delete('newsletter');
    }

    var cleanQuery = params.toString();
    var cleanUrl = window.location.pathname + (cleanQuery ? '?' + cleanQuery : '') + window.location.hash;

    forms.forEach(function (form) {
      var redirectInput = form.querySelector('input[name="redirect"]');
      if (!redirectInput) {
        redirectInput = document.createElement('input');
        redirectInput.type = 'hidden';
        redirectInput.name = 'redirect';
        form.appendChild(redirectInput);
      }
      redirectInput.value = cleanUrl;
    });

    if (!status) {
      return;
    }

    var messages = {
      success: { text: 'Successfully subscribed to newsletter!', className: 'alert-success' },
      exists: { text: 'You are already subscribed with that email address.', className: 'alert-info' },
      invalid: { text: 'Please enter a valid email address.', className: 'alert-warning' },
      error: { text: 'We could not complete your subscription. Please try again later.', className: 'alert-danger' }
    };

    var feedback = messages[status] || messages.error;

    forms.forEach(function (form) {
      var alert = form.nextElementSibling;
      if (!alert || alert.getAttribute('data-newsletter-feedback') !== 'true') {
        alert = document.createElement('div');
        alert.setAttribute('data-newsletter-feedback', 'true');
        alert.className = 'alert mt-3';
        form.insertAdjacentElement('afterend', alert);
      }
      alert.className = 'alert mt-3 ' + feedback.className;
      alert.setAttribute('role', 'alert');
      alert.textContent = feedback.text;
    });

    if (window.history && typeof window.history.replaceState === 'function') {
      var historyUrl = cleanUrl || (window.location.pathname + (window.location.hash || ''));
      window.history.replaceState({}, document.title, historyUrl);
    }
  }

  onReady(initNewsletterFeedback);
})();

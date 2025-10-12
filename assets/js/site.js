(function () {
  const navLinks = document.querySelectorAll('.fbs__net-navbar .nav-link');
  const current = window.location.pathname.replace(/\/index\.php$/, '/');
  navLinks.forEach((link) => {
    const href = link.getAttribute('href');
    if (!href) {
      return;
    }
    const target = href.replace(/\/index\.php$/, '/');
    if (target === current) {
      link.classList.add('active');
    }
  });

  const yearTarget = document.querySelector('[data-current-year]');
  if (yearTarget) {
    yearTarget.textContent = new Date().getFullYear().toString();
  }
})();

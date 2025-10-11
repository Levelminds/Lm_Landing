document.addEventListener("DOMContentLoaded", function () {
  
  

  const sections = document.querySelectorAll(".section");
  const navLinks = document.querySelectorAll(".fbs__net-navbar .scroll-link");

  function removeActiveClasses() {
    if (navLinks) {
      navLinks.forEach((link) => link.classList.remove("active"));
    }
  }

  

  function addActiveClass(currentSectionId) {
    const activeLink = document.querySelector(
      `.fbs__net-navbar .scroll-link[href="#${currentSectionId}"]`
    );
    if (activeLink) {
      activeLink.classList.add("active");
    }
  }

  function getCurrentSection() {
    let currentSection = null;
    let minDistance = Infinity;
    if (sections) {
      sections.forEach((section) => {
        const rect = section.getBoundingClientRect();
        const distance = Math.abs(rect.top - window.innerHeight / 4);

        if (distance < minDistance && rect.top < window.innerHeight) {
          minDistance = distance;
          currentSection = section.getAttribute("id");
        }
      });
    }

    return currentSection;
  }

  function updateActiveLink() {
    const currentSectionId = getCurrentSection();
    if (currentSectionId) {
      removeActiveClasses();
      addActiveClass(currentSectionId);
    }
  }

  window.addEventListener("scroll", updateActiveLink);

  const portfolioGrid = document.querySelector('#portfolio-grid');
  if (portfolioGrid) {
    var iso = new Isotope("#portfolio-grid", {
      itemSelector: ".portfolio-item",
      layoutMode: "masonry",
    });

    if (iso) {
      iso.on("layoutComplete", updateActiveLink);

      imagesLoaded("#portfolio-grid", function () {
        iso.layout();
        updateActiveLink();
      });
    }

    var filterButtons = document.querySelectorAll(".filter-button");
    if (filterButtons) {
      filterButtons.forEach(function (button) {
        button.addEventListener("click", function (e) {
          e.preventDefault();
          var filterValue = button.getAttribute("data-filter");
          iso.arrange({ filter: filterValue });

          filterButtons.forEach(function (btn) {
            btn.classList.remove("active");
          });
          button.classList.add("active");
          updateActiveLink();
        });
      });
    }

    updateActiveLink();
  }
});

const navbarScrollInit = () => {
  var navbar = document.querySelector(".fbs__net-navbar");

  var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  if (navbar) {
    if (scrollTop > 0) {
      navbar.classList.add("active");
    } else {
      navbar.classList.remove("active");
    }
  }
};

const navbarInit = () => {
  document.querySelectorAll('.dropdown-toggle[href="#"]').forEach(function (el, index) {
    el.addEventListener("click", function (event) {
      event.stopPropagation();
    });
  });
};

// ======= Marquee =======
const logoMarqueeInit = () => {
  const wrapper = document.querySelector(".logo-wrapper");
  const boxes = gsap.utils.toArray(".logo-item");
  
  if (boxes.length > 0) {
    const loop = horizontalLoop(boxes, {
      paused: false,
      repeat: -1,
      speed: 0.25,
      reversed: false,
    });
    
    function horizontalLoop(items, config) {
      items = gsap.utils.toArray(items);
      config = config || {};
      let tl = gsap.timeline({
          repeat: config.repeat,
          paused: config.paused,
          defaults: { ease: "none" },
          onReverseComplete: () =>
            tl.totalTime(tl.rawTime() + tl.duration() * 100),
        }),
        length = items.length,
        startX = items[0].offsetLeft,
        times = [],
        widths = [],
        xPercents = [],
        curIndex = 0,
        pixelsPerSecond = (config.speed || 1) * 100,
        snap =
          config.snap === false ? (v) => v : gsap.utils.snap(config.snap || 1), // some browsers shift by a pixel to accommodate flex layouts, so for example if width is 20% the first element's width might be 242px, and the next 243px, alternating back and forth. So we snap to 5 percentage points to make things look more natural
        totalWidth,
        curX,
        distanceToStart,
        distanceToLoop,
        item,
        i;
      gsap.set(items, {
        // convert "x" to "xPercent" to make things responsive, and populate the widths/xPercents Arrays to make lookups faster.
        xPercent: (i, el) => {
          let w = (widths[i] = parseFloat(gsap.getProperty(el, "width", "px")));
          xPercents[i] = snap(
            (parseFloat(gsap.getProperty(el, "x", "px")) / w) * 100 +
              gsap.getProperty(el, "xPercent")
          );
          return xPercents[i];
        },
      });
      gsap.set(items, { x: 0 });
      totalWidth =
        items[length - 1].offsetLeft +
        (xPercents[length - 1] / 100) * widths[length - 1] -
        startX +
        items[length - 1].offsetWidth *
          gsap.getProperty(items[length - 1], "scaleX") +
        (parseFloat(config.paddingRight) || 0);
      for (i = 0; i < length; i++) {
        item = items[i];
        curX = (xPercents[i] / 100) * widths[i];
        distanceToStart = item.offsetLeft + curX - startX;
        distanceToLoop =
          distanceToStart + widths[i] * gsap.getProperty(item, "scaleX");
        tl.to(
          item,
          {
            xPercent: snap(((curX - distanceToLoop) / widths[i]) * 100),
            duration: distanceToLoop / pixelsPerSecond,
          },
          0
        )
          .fromTo(
            item,
            {
              xPercent: snap(
                ((curX - distanceToLoop + totalWidth) / widths[i]) * 100
              ),
            },
            {
              xPercent: xPercents[i],
              duration:
                (curX - distanceToLoop + totalWidth - curX) / pixelsPerSecond,
              immediateRender: false,
            },
            distanceToLoop / pixelsPerSecond
          )
          .add("label" + i, distanceToStart / pixelsPerSecond);
        times[i] = distanceToStart / pixelsPerSecond;
      }
      function toIndex(index, vars) {
        vars = vars || {};
        Math.abs(index - curIndex) > length / 2 &&
          (index += index > curIndex ? -length : length); // always go in the shortest direction
        let newIndex = gsap.utils.wrap(0, length, index),
          time = times[newIndex];
        if (time > tl.time() !== index > curIndex) {
          // if we're wrapping the timeline's playhead, make the proper adjustments
          vars.modifiers = { time: gsap.utils.wrap(0, tl.duration()) };
          time += tl.duration() * (index > curIndex ? 1 : -1);
        }
        curIndex = newIndex;
        vars.overwrite = true;
        return tl.tweenTo(time, vars);
      }
      tl.next = (vars) => toIndex(curIndex + 1, vars);
      tl.previous = (vars) => toIndex(curIndex - 1, vars);
      tl.current = () => curIndex;
      tl.toIndex = (index, vars) => toIndex(index, vars);
      tl.times = times;
      tl.progress(1, true).progress(0, true); // pre-render for performance
      if (config.reversed) {
        tl.vars.onReverseComplete();
        tl.reverse();
      }
      return tl;
    }
  }
};

document.addEventListener("DOMContentLoaded", logoMarqueeInit);

// ======= Navbar Scroll =======
document.addEventListener("DOMContentLoaded", function () {
  logoMarqueeInit();
  navbarInit();
  window.addEventListener("scroll", navbarScrollInit);
});

// ======= Swiper =======
const swiperInit = () => {
  var swiper = new Swiper(".testimonialSwiper", {
    slidesPerView: 1,
    speed: 700,
    spaceBetween: 30,
    loop: true,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    breakpoints: {
      640: {
        slidesPerView: 1.5,
        spaceBetween: 20,
      },
      768: {
        slidesPerView: 2.5,
        spaceBetween: 30,
      },
      1024: {
        slidesPerView: 2.5,
        spaceBetween: 30,
      },
    },
    navigation: {
      nextEl: ".custom-button-next",
      prevEl: ".custom-button-prev",
    },
  });

  const progressCircle = document.querySelector(".autoplay-progress svg");
  const progressContent = document.querySelector(".autoplay-progress span");
  if (progressCircle && progressContent ) {
    var swiper2 = new Swiper(".sliderSwiper", {
      slidesPerView: 1,
      speed: 700,
      spaceBetween: 0,
      loop: true,
      centeredSlides: true,
      autoplay: {
        delay: 7000,
        disableOnInteraction: false
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".custom-button-next",
        prevEl: ".custom-button-prev",
      },

      on: {
        autoplayTimeLeft(s, time, progress) {
          progressCircle.style.setProperty("--progress", 1 - progress);
          progressContent.textContent = `${Math.ceil(time / 1000)}s`;
        }
      }
    });
  }

};

document.addEventListener("DOMContentLoaded", swiperInit);

// ======= Glightbox =======
const glightBoxInit = () => {
  const lightbox = GLightbox({
    touchNavigation: true,
    loop: true,
    autoplayVideos: true,
  });
};
document.addEventListener("DOMContentLoaded", glightBoxInit);

// ======= BS OffCanvass =======
const bsOffCanvasInit = () => {
  var offcanvasElement = document.getElementById("fbs__net-navbars");
  if (offcanvasElement) {
    offcanvasElement.addEventListener("show.bs.offcanvas", function () {
      document.body.classList.add("offcanvas-active");
    });

    offcanvasElement.addEventListener("hidden.bs.offcanvas", function () {
      document.body.classList.remove("offcanvas-active");
    });
  }
};
document.addEventListener("DOMContentLoaded", bsOffCanvasInit);

// ======= Back To Top =======
const backToTopInit = () => {
  const backToTopButton = document.getElementById("back-to-top");
  if (backToTopButton) {
    window.addEventListener("scroll", () => {
      if (window.scrollY > 170) {
        backToTopButton.classList.add("show");
      } else {
        backToTopButton.classList.remove("show");
      }
    });
    backToTopButton.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  }
};

document.addEventListener("DOMContentLoaded", backToTopInit);


// ======= Inline SVG =======
const inlineSvgInit = () => {
  const imgElements = document.querySelectorAll(".js-img-to-inline-svg");
  if (imgElements) {
    imgElements.forEach((imgElement) => {
      const imgURL = imgElement.getAttribute("src");

      fetch(imgURL)
        .then((response) => response.text())
        .then((svgText) => {
          const parser = new DOMParser();
          const svgDocument = parser.parseFromString(svgText, "image/svg+xml");
          const svgElement = svgDocument.documentElement;

          Array.from(imgElement.attributes).forEach((attr) => {
            if (attr.name !== "class") {
              svgElement.setAttribute(attr.name, attr.value);
            } else {
              const classes = attr.value
                .split(" ")
                .filter((className) => className !== "js-img-to-inline-svg");
              if (classes.length > 0) {
                svgElement.setAttribute("class", classes.join(" "));
              }
            }
          });

          imgElement.replaceWith(svgElement);
        })
        .catch((error) => console.error("Error fetching SVG:", error));
    });
  }
};

document.addEventListener("DOMContentLoaded", inlineSvgInit);

// ======= AOS =======
const aosInit = () => {
  AOS.init({
    duration: 800,
    easing: 'slide',
    once: true
  });
}
document.addEventListener("DOMContentLoaded", aosInit);

// ======= PureCounter =======
const pureCounterInit = () => {
  new PureCounter({
    selector: ".purecounter",
  });
}
document.addEventListener("DOMContentLoaded", pureCounterInit);

// ======= Disable Click Navbar Dropdown =======
const addHoverEvents = (dropdown) => {
  const dropdownToggle = dropdown.querySelector('.dropdown-toggle');

  const preventClick = (event) => event.preventDefault();
  const showDropdown = () => {
    dropdown.classList.add('show');
    dropdownToggle.setAttribute('aria-expanded', 'true');
    const dropdownMenu = dropdown.querySelector('.dropdown-menu');
    dropdownMenu.classList.add('show');
  };
  const hideDropdown = () => {
    dropdown.classList.remove('show');
    dropdownToggle.setAttribute('aria-expanded', 'false');
    const dropdownMenu = dropdown.querySelector('.dropdown-menu');
    dropdownMenu.classList.remove('show');
  };

  // Disable the click event for toggling the dropdown
  dropdownToggle.addEventListener('click', preventClick);

  // Open dropdown on hover
  dropdown.addEventListener('mouseover', showDropdown);

  // Close dropdown when mouse leaves
  dropdown.addEventListener('mouseleave', hideDropdown);

  // Store references to the event listeners for later removal
  dropdown.__events = { preventClick, showDropdown, hideDropdown };
};

const removeHoverEvents = (dropdown) => {
  const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
  const { preventClick, showDropdown, hideDropdown } = dropdown.__events || {};

  if (preventClick) {
    // Remove the event listeners
    dropdownToggle.removeEventListener('click', preventClick);
    dropdown.removeEventListener('mouseover', showDropdown);
    dropdown.removeEventListener('mouseleave', hideDropdown);

    // Remove the reference to the stored events
    delete dropdown.__events;
  }
};

const handleNavbarEvents = () => {
  const dropdowns = document.querySelectorAll('.navbar .dropdown');
  const dropstarts = document.querySelectorAll('.navbar .dropstart');
  const dropends = document.querySelectorAll('.navbar .dropend');

  if (window.innerWidth >= 992) {

    // Add hover events to dropdowns
    dropdowns.forEach(addHoverEvents);
    dropstarts.forEach(addHoverEvents);
    dropends.forEach(addHoverEvents);
  } else {

    // Remove hover events from dropdowns
    dropdowns.forEach(removeHoverEvents);
    dropstarts.forEach(removeHoverEvents);
    dropends.forEach(removeHoverEvents);
  }
};

// Function to handle resizing
const handleResize = () => {
  const dropdowns = document.querySelectorAll('.navbar .dropdown');
  const dropstarts = document.querySelectorAll('.navbar .dropstart');
  const dropends = document.querySelectorAll('.navbar .dropend');

  // Remove hover events before rechecking window size
  dropdowns.forEach(removeHoverEvents);
  dropstarts.forEach(removeHoverEvents);
  dropends.forEach(removeHoverEvents);

  // Re-apply hover events based on window size
  handleNavbarEvents();
};

// Call the function on resize event and initially
window.addEventListener('resize', handleResize);
handleNavbarEvents();



// ======= Coming Soon Countdown =======
const countdownInit = () => {

  // Get the current year
  const currentYear = new Date().getFullYear();
  const nextYear = currentYear + 1;
  const launchDate = new Date(`December 31, ${nextYear} 23:59:59`).getTime();

  // Change this "December 31, 2024 23:59:59" to your your website launch date
  // const launchDate = new Date("December 31, 2024 23:59:59").getTime();


  const x = setInterval(function () {

    const now = new Date().getTime();
      
    const distance = launchDate - now;
      
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
      
    // Output the result in an element with id
    const daysEl = document.getElementById("days");
    const hoursEl = document.getElementById("hours");
    const minutesEl = document.getElementById("minutes");
    const secondsEl = document.getElementById("seconds");
    if (daysEl) {
      daysEl.innerText = days;
    }
    if (hoursEl) {
      hoursEl.innerText = hours;
    }
    if (minutesEl) {
      minutesEl.innerText = minutes;
    }
    if (secondsEl) {
      secondsEl.innerText = seconds;
    }
      
    // If the count down is finished, write some text
    if (distance < 0) {
      clearInterval(x);
      document.querySelector(".countdown").innerText = "Launched!";
    }
  }, 1000);
};
document.addEventListener('DOMContentLoaded', countdownInit);




// ========== LM Blogs: Subscriber-gated Likes & Sharing ==========
(function() {
  const EMAIL_KEY = 'lmEmail';
  const TOKEN_KEY = 'lmVisitorToken';
  const LIKE_STATE_PREFIX = 'lm_like_state_';
  const API_URL = 'api/like.php';
  const SUBSCRIBER_STATUS_KEY = 'lmSubscriberStatus';
  const SUBSCRIBER_STATUS_TTL_MS = 24 * 60 * 60 * 1000; // 24 hours
  const SUBSCRIBER_API = 'api/subscriber-status.php';
  const SHARE_TARGETS = [
    { network: 'copy', label: 'Copy link', icon: 'bi-clipboard' },
    { network: 'whatsapp', label: 'WhatsApp', icon: 'bi-whatsapp' },
    { network: 'facebook', label: 'Facebook', icon: 'bi-facebook' },
    { network: 'linkedin', label: 'LinkedIn', icon: 'bi-linkedin' },
    { network: 'twitter', label: 'X (Twitter)', icon: 'bi-twitter' }
  ];

  let activeShareMenu = null;

  function decodeBase64(value) {
    if (!value) {
      return '';
    }
    try {
      const binary = atob(value);
      if (window.TextDecoder) {
        const decoder = new TextDecoder('utf-8', { fatal: false });
        const bytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
        return decoder.decode(bytes);
      }
      return decodeURIComponent(Array.prototype.map.call(binary, function (char) {
        return '%' + ('00' + char.charCodeAt(0).toString(16)).slice(-2);
      }).join(''));
    } catch (error) {
      return '';
    }
  }

  function normaliseEmail(value) {
    return (value || '').trim().toLowerCase();
  }

  function getEmail() {
    return (localStorage.getItem(EMAIL_KEY) || '').trim();
  }

  function setEmail(email) {
    if (email) {
      localStorage.setItem(EMAIL_KEY, email.trim());
    }
  }

  function clearEmail() {
    localStorage.removeItem(EMAIL_KEY);
    localStorage.removeItem(SUBSCRIBER_STATUS_KEY);
  }

  function readSubscriberCache() {
    try {
      const raw = localStorage.getItem(SUBSCRIBER_STATUS_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch (error) {
      return null;
    }
  }

  function writeSubscriberCache(email, active) {
    if (!email) {
      localStorage.removeItem(SUBSCRIBER_STATUS_KEY);
      return;
    }
    const payload = {
      email: normaliseEmail(email),
      active: !!active,
      timestamp: Date.now()
    };
    localStorage.setItem(SUBSCRIBER_STATUS_KEY, JSON.stringify(payload));
  }

  async function verifySubscriber(email) {
    const normalized = normaliseEmail(email);
    const cached = readSubscriberCache();
    if (cached && cached.email === normalized && (Date.now() - cached.timestamp) < SUBSCRIBER_STATUS_TTL_MS) {
      return !!cached.active;
    }

    const res = await fetch(SUBSCRIBER_API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email })
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || typeof data.active !== 'boolean') {
      throw new Error((data && data.message) || 'Unable to verify subscription right now.');
    }
    writeSubscriberCache(email, data.active);
    return data.active;
  }

  function scrollToNewsletter() {
    const section = document.getElementById('newsletter');
    if (section && typeof section.scrollIntoView === 'function') {
      section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function emailLooksValid(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  async function ensureSubscriberEmail() {
    let email = getEmail();
    if (email) {
      try {
        const active = await verifySubscriber(email);
        if (active) {
          return email;
        }
        clearEmail();
        showToast('We could not verify your subscription. Please subscribe again.');
        scrollToNewsletter();
      } catch (err) {
        showToast(err && err.message ? err.message : 'Unable to verify your subscription right now.');
        return null;
      }
    }

    for (let attempt = 0; attempt < 3; attempt += 1) {
      const input = window.prompt('Enter the email you used to subscribe:');
      if (!input) {
        showToast('Subscription is required to continue.');
        scrollToNewsletter();
        return null;
      }
      const candidate = input.trim();
      if (!emailLooksValid(candidate)) {
        showToast('Please enter a valid email address.');
        continue;
      }
      try {
        const active = await verifySubscriber(candidate);
        if (active) {
          setEmail(candidate);
          return candidate;
        }
        showToast('We could not find an active subscription for that email.');
        scrollToNewsletter();
      } catch (err) {
        showToast(err && err.message ? err.message : 'Unable to verify your subscription right now.');
        return null;
      }
    }
    return null;
  }

  function generateToken() {
    if (window.crypto && crypto.getRandomValues) {
      const array = new Uint8Array(16);
      crypto.getRandomValues(array);
      return Array.from(array).map(b => b.toString(16).padStart(2, '0')).join('');
    }
    return 'lm_' + Math.random().toString(36).slice(2) + Date.now().toString(36);
  }

  function getVisitorToken() {
    let token = localStorage.getItem(TOKEN_KEY);
    if (!token) {
      token = generateToken();
      localStorage.setItem(TOKEN_KEY, token);
    }
    return token;
  }

  function isLocallyLiked(postId) {
    const newKey = LIKE_STATE_PREFIX + postId;
    if (localStorage.getItem(newKey) === '1') {
      return true;
    }
    const legacyKey = 'lm_like_' + postId;
    if (localStorage.getItem(legacyKey) === '1') {
      localStorage.setItem(newKey, '1');
      localStorage.removeItem(legacyKey);
      return true;
    }
    return false;
  }

  function setLocalLike(postId, liked) {
    const key = LIKE_STATE_PREFIX + postId;
    if (liked) {
      localStorage.setItem(key, '1');
    } else {
      localStorage.removeItem(key);
    }
    localStorage.removeItem('lm_like_' + postId);
  }

  function showToast(message) {
    let container = document.getElementById('lm-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'lm-toast-container';
      container.style.position = 'fixed';
      container.style.top = '20px';
      container.style.right = '20px';
      container.style.zIndex = '9999';
      document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'shadow-sm rounded-pill px-3 py-2 mb-2';
    toast.style.background = '#111827';
    toast.style.color = '#fff';
    toast.style.fontSize = '0.95rem';
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.transition = 'opacity .4s ease';
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 400);
    }, 2200);
  }

  document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.closest && form.closest('#newsletter')) {
      const emailInput = form.querySelector('input[type="email"], input[name="email"]');
      if (emailInput && emailInput.value) {
        setEmail(emailInput.value);
        writeSubscriberCache(emailInput.value, false);
      }
      showToast('🎉 Thanks for subscribing!');
    }
  }, true);

  function renderLikedState(btn, liked) {
    const icon = btn.querySelector('.bi');
    if (liked) {
      btn.classList.add('liked');
      if (icon) {
        icon.classList.add('bi-heart-fill');
        icon.classList.remove('bi-heart');
      }
    } else {
      btn.classList.remove('liked');
      if (icon) {
        icon.classList.add('bi-heart');
        icon.classList.remove('bi-heart-fill');
      }
    }
  }

  function hydrateLikes() {
    document.querySelectorAll('[data-like-btn]').forEach(btn => {
      const postId = btn.getAttribute('data-post-id');
      const liked = isLocallyLiked(postId);
      renderLikedState(btn, liked);
    });
  }

  async function toggleLikeServer(postId, email) {
    const payload = {
      post_id: postId,
      visitor_token: getVisitorToken(),
      email: email
    };
    const res = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data || typeof data.likes !== 'number') {
      const msg = data && data.message ? data.message : 'Unable to like right now.';
      throw new Error(msg);
    }
    return data;
  }

  function closeShareMenu() {
    if (activeShareMenu && activeShareMenu.menu && activeShareMenu.menu.parentNode) {
      activeShareMenu.menu.parentNode.removeChild(activeShareMenu.menu);
    }
    activeShareMenu = null;
  }

  function buildShareMenu() {
    const menu = document.createElement('div');
    menu.className = 'lm-share-menu shadow';
    SHARE_TARGETS.forEach(target => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'lm-share-menu__item';
      button.setAttribute('data-share-network', target.network);
      button.innerHTML = `<i class="bi ${target.icon} me-2"></i>${target.label}`;
      menu.appendChild(button);
    });
    return menu;
  }

  function openShareMenu(btn, data) {
    closeShareMenu();
    const menu = buildShareMenu();
    document.body.appendChild(menu);
    const rect = btn.getBoundingClientRect();
    const top = window.scrollY + rect.bottom + 8;
    let left = window.scrollX + rect.left;
    const maxLeft = window.scrollX + window.innerWidth - menu.offsetWidth - 16;
    if (!Number.isFinite(left)) {
      left = window.scrollX + 16;
    }
    if (left > maxLeft) {
      left = Math.max(window.scrollX + 16, maxLeft);
    }
    menu.style.position = 'absolute';
    menu.style.top = `${Math.round(top)}px`;
    menu.style.left = `${Math.round(left)}px`;
    activeShareMenu = { menu, data };
  }

  function shareUrlFor(network, data) {
    const url = data.url;
    const title = data.title;
    const summary = data.summary || title;
    switch (network) {
      case 'whatsapp':
        return `https://wa.me/?text=${encodeURIComponent(`${title}\n${url}`)}`;
      case 'facebook':
        return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
      case 'linkedin':
        return `https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}&summary=${encodeURIComponent(summary)}`;
      case 'twitter':
        return `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
      default:
        return '';
    }
  }

  async function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      try {
        await navigator.clipboard.writeText(text);
        return true;
      } catch (error) {
        return false;
      }
    }
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    let success = false;
    try {
      success = document.execCommand('copy');
    } catch (error) {
      success = false;
    }
    textarea.remove();
    return success;
  }

  document.addEventListener('click', async function(e) {
    const btn = e.target.closest && e.target.closest('[data-like-btn]');
    if (!btn) {
      return;
    }

    const postId = btn.getAttribute('data-post-id');
    if (!postId) {
      return;
    }

    const countEl = btn.querySelector('[data-like-count]');
    const email = await ensureSubscriberEmail();
    if (!email) {
      renderLikedState(btn, isLocallyLiked(postId));
      return;
    }

    try {
      const result = await toggleLikeServer(postId, email);
      const liked = !!result.liked;
      const likes = Number(result.likes);

      if (Number.isFinite(likes) && countEl) {
        countEl.textContent = likes.toLocaleString();
      }
      renderLikedState(btn, liked);
      setLocalLike(postId, liked);
      writeSubscriberCache(email, true);

      document.dispatchEvent(new CustomEvent('lm:likes-updated', {
        detail: { postId: postId, likes: likes, liked: liked }
      }));
    } catch (err) {
      showToast(err && err.message ? err.message : 'Unable to like right now.');
      renderLikedState(btn, isLocallyLiked(postId));
    }
  });

  document.addEventListener('click', async function(e) {
    const shareBtn = e.target.closest && e.target.closest('[data-share-btn]');
    if (!shareBtn) {
      return;
    }
    e.preventDefault();

    const email = await ensureSubscriberEmail();
    if (!email) {
      closeShareMenu();
      return;
    }
    writeSubscriberCache(email, true);

    const dataset = shareBtn.dataset || {};
    const shareData = {
      url: dataset.shareUrl || window.location.href,
      title: dataset.shareTitle || document.title,
      summary: dataset.shareSummaryB64 ? decodeBase64(dataset.shareSummaryB64) : (dataset.shareSummary || '')
    };

    if (navigator.share) {
      try {
        await navigator.share({
          title: shareData.title,
          text: shareData.summary || shareData.title,
          url: shareData.url
        });
        showToast('Thanks for sharing!');
      } catch (err) {
        if (!err || err.name !== 'AbortError') {
          showToast('Unable to complete the share right now.');
        }
      }
      return;
    }

    openShareMenu(shareBtn, shareData);
  });

  document.addEventListener('click', function(event) {
    const option = event.target.closest && event.target.closest('[data-share-network]');
    if (!option || !activeShareMenu) {
      return;
    }
    event.preventDefault();
    const network = option.getAttribute('data-share-network');
    const data = activeShareMenu.data;
    closeShareMenu();

    if (network === 'copy') {
      copyToClipboard(data.url).then(success => {
        showToast(success ? 'Link copied. Share it with your network!' : 'Unable to copy link automatically.');
      });
      return;
    }

    const url = shareUrlFor(network, data);
    if (url) {
      window.open(url, '_blank', 'noopener,width=600,height=520');
      showToast('Sharing window opened.');
    } else {
      showToast('Share option is not available right now.');
    }
  });

  document.addEventListener('click', function(event) {
    if (!activeShareMenu) {
      return;
    }
    const isMenu = activeShareMenu.menu.contains(event.target);
    const isTrigger = event.target.closest && event.target.closest('[data-share-btn]');
    if (!isMenu && !isTrigger) {
      closeShareMenu();
    }
  });

  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      closeShareMenu();
    }
  });

  window.LM = window.LM || {};
  window.LM.hydrateLikes = hydrateLikes;
  window.LM.ensureSubscriberEmail = ensureSubscriberEmail;

  document.addEventListener('DOMContentLoaded', hydrateLikes);
})();

/* ========== LM Blogs: View Counter (modal + cards) ========== */
(function() {
  const API_URL = 'api/view.php';     // adjust path if your API lives elsewhere
  const VIEW_TTL_MS = 6 * 60 * 60 * 1000; // 6 hours
  const LS_PREFIX = 'lm_viewed_';

  function viewedRecently(postId) {
    const raw = localStorage.getItem(LS_PREFIX + postId);
    if (!raw) return false;
    const ts = parseInt(raw, 10);
    return Number.isFinite(ts) && (Date.now() - ts) < VIEW_TTL_MS;
  }
  function markViewed(postId) {
    localStorage.setItem(LS_PREFIX + postId, String(Date.now()));
  }

  async function incrementOnServer(postId) {
    const res = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: postId })
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data || typeof data.views !== 'number') {
      throw new Error((data && data.message) || 'Failed to update views');
    }
    return data.views;
  }

  // Expose a small API
  window.LM = window.LM || {};
  window.LM.trackView = async function(postId, opts) {
    // opts: { initialViews?: number, updateUI?: (latestViews:number)=>void }
    try {
      if (viewedRecently(postId)) {
        if (opts && typeof opts.updateUI === 'function' && typeof opts.initialViews === 'number') {
          opts.updateUI(opts.initialViews);
        }
        return;
      }
      markViewed(postId);
      const latest = await incrementOnServer(postId);
      if (opts && typeof opts.updateUI === 'function') {
        opts.updateUI(latest);
      }
    } catch (e) {
      // Silent fail is OK; just don't break the UI
      // console.warn(e);
    }
  };
})();


<?php
/** @var array $featured */
/** @var array $categories */
/** @var array $postsByCategory */
?>
<section class="hero-surface">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <div class="hero-tag">LevelMinds Insights</div>
        <h1 class="hero-heading">Stories, strategies, and spotlights for thriving classrooms.</h1>
        <p class="hero-subtext">Explore research-backed hiring tactics, teacher success stories, and platform updates curated by the LevelMinds editorial team.</p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a class="lm-btn-primary" href="/blogs.php">View all blogs</a>
          <a class="lm-btn-outline" href="#categories">Browse by category</a>
        </div>
      </div>
      <?php if (!empty($featured)): ?>
        <div class="col-lg-6">
          <article class="hero-card d-flex flex-column h-100">
            <?php if (!empty($featured['media_url'])): ?>
              <img class="w-100 rounded mb-3" src="<?= htmlspecialchars($featured['media_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($featured['title'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
            <span class="badge-lm mb-2">Featured</span>
            <h3 class="h4 mb-2"><?= htmlspecialchars($featured['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p class="text-muted flex-grow-1"><?= htmlspecialchars($featured['excerpt'], ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="d-flex align-items-center justify-content-between mt-3">
              <div class="text-muted small">
                <?= htmlspecialchars($featured['author'], ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($featured['published_on'], ENT_QUOTES, 'UTF-8'); ?>
              </div>
              <a class="lm-btn-outline" href="#" data-blog-id="<?= (int) $featured['id']; ?>" data-action="open-blog">Read story</a>
            </div>
          </article>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="py-5" id="categories">
  <div class="container">
    <div class="row justify-content-between align-items-center mb-5">
      <div class="col-lg-8">
        <h2 class="section-heading">Discover by category</h2>
        <p class="section-subtitle">From leadership playbooks to classroom innovation—navigate the library tailored for teachers and institutions.</p>
      </div>
      <div class="col-lg-3 text-lg-end">
        <a class="lm-btn-outline" href="/blogs.php">Explore live blog feed</a>
      </div>
    </div>
    <?php foreach ($categories as $categoryKey => $categoryLabel):
      $posts = $postsByCategory[$categoryKey] ?? [];
      if (empty($posts)) {
        continue;
      }
      ?>
      <div class="mb-5">
        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
          <h3 class="h4 mb-0"><?= htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8'); ?></h3>
          <a class="lm-btn-outline" href="/blogs.php#<?= htmlspecialchars($categoryKey, ENT_QUOTES, 'UTF-8'); ?>">View category</a>
        </div>
        <div class="row g-4">
          <?php foreach ($posts as $post): ?>
            <div class="col-lg-4 col-md-6">
              <article class="blog-gallery-card">
                <?php if (!empty($post['media_url'])): ?>
                  <img src="<?= htmlspecialchars($post['media_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                  <span class="badge-lm mb-2"><?= htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                  <h4 class="h5 mb-2"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                  <p class="text-muted flex-grow-1"><?= htmlspecialchars($post['excerpt'], ENT_QUOTES, 'UTF-8'); ?></p>
                  <div class="d-flex align-items-center justify-content-between mt-3">
                    <small class="text-muted"><?= htmlspecialchars($post['author'], ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($post['published_on'], ENT_QUOTES, 'UTF-8'); ?></small>
                    <a class="lm-btn-outline" href="#" data-blog-id="<?= (int) $post['id']; ?>" data-action="open-blog">Read</a>
                  </div>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-7">
        <h2 class="section-heading">Never miss an insight</h2>
        <p class="section-subtitle">Subscribe to our monthly newsletter to receive the latest hiring playbooks, classroom innovation, and LevelMinds product updates.</p>
      </div>
      <div class="col-lg-5">
        <form class="card-elevated d-flex flex-column gap-3" action="newsletter.php" method="post">
          <h4 class="mb-0">LevelMinds Dispatch</h4>
          <p class="text-muted mb-2">One email, once a month—no spam, only actionable insights.</p>
          <input class="form-control" type="email" name="email" placeholder="Your email address" required>
          <button class="lm-btn-primary" type="submit">Subscribe</button>
        </form>
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="blogGalleryModal" tabindex="-1" aria-labelledby="blogGalleryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-4" id="blogGalleryModalLabel">Loading…</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="loader-spinner"></div>
      </div>
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <div class="d-flex gap-3 align-items-center">
          <button class="btn btn-outline-primary" type="button" data-action="like" disabled>👍 Like</button>
          <span class="text-muted" data-modal-views>— views</span>
        </div>
        <button class="lm-btn-outline" type="button" data-action="share" disabled>Share</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const modalElement = document.getElementById('blogGalleryModal');
  if (!modalElement) return;

  const modal = new bootstrap.Modal(modalElement);
  const titleNode = modalElement.querySelector('.modal-title');
  const bodyNode = modalElement.querySelector('.modal-body');
  const likeButton = modalElement.querySelector('[data-action="like"]');
  const shareButton = modalElement.querySelector('[data-action="share"]');
  const viewsNode = modalElement.querySelector('[data-modal-views]');

  const getVisitorToken = () => {
    try {
      const stored = localStorage.getItem('lm_visitor_token');
      if (stored) return stored;
      const token = crypto.randomUUID();
      localStorage.setItem('lm_visitor_token', token);
      return token;
    } catch (error) {
      return 'anonymous';
    }
  };

  const openBlog = async (id) => {
    titleNode.textContent = 'Loading…';
    bodyNode.innerHTML = '<div class="loader-spinner"></div>';
    likeButton.disabled = true;
    shareButton.disabled = true;
    viewsNode.textContent = '— views';

    modal.show();

    try {
      const [blogResponse, viewResponse] = await Promise.all([
        fetch(`api/get_blog.php?id=${id}`),
        fetch('api/update_views.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ post_id: id })
        })
      ]);

      if (!blogResponse.ok) throw new Error('Unable to load blog.');

      const blogData = await blogResponse.json();
      if (!blogData.success) throw new Error(blogData.error || 'Blog not found');

      const viewData = viewResponse.ok ? await viewResponse.json() : null;

      titleNode.textContent = blogData.data.title;
      bodyNode.innerHTML = `
        ${blogData.data.media_url ? `<img class="w-100 rounded mb-3" src="${blogData.data.media_url}" alt="${blogData.data.title}">` : ''}
        <div class="d-flex flex-wrap gap-3 text-muted mb-3">
          <span>${blogData.data.category}</span>
          <span>${blogData.data.author}</span>
          <span>${blogData.data.published_on}</span>
          ${blogData.data.read_time ? `<span>${blogData.data.read_time}</span>` : ''}
        </div>
        <div class="blog-content">${blogData.data.content}</div>
      `;

      const views = viewData && viewData.success ? viewData.views : blogData.data.views;
      viewsNode.textContent = `${views} views`;

      likeButton.disabled = false;
      shareButton.disabled = false;
      likeButton.dataset.blogId = id;
      likeButton.textContent = blogData.data.user_liked ? `👍 Liked (${blogData.data.likes})` : `👍 Like (${blogData.data.likes})`;
      likeButton.classList.toggle('btn-primary', !!blogData.data.user_liked);
      likeButton.classList.toggle('btn-outline-primary', !blogData.data.user_liked);
      likeButton.dataset.liked = blogData.data.user_liked ? '1' : '0';
    } catch (error) {
      titleNode.textContent = 'Unable to load blog';
      bodyNode.innerHTML = `<p class="text-danger">${error.message}</p>`;
    }
  };

  const likeBlog = async (button) => {
    const id = Number(button.dataset.blogId);
    if (!id) return;
    button.disabled = true;

    try {
      const response = await fetch('api/like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ post_id: id, visitor_token: getVisitorToken() })
      });
      if (!response.ok) throw new Error('Network error');
      const data = await response.json();
      if (!data.success) throw new Error(data.error || 'Unable to like post');

      button.textContent = data.data.liked ? `👍 Liked (${data.data.likes})` : `👍 Like (${data.data.likes})`;
      button.dataset.liked = data.data.liked ? '1' : '0';
      button.classList.toggle('btn-primary', data.data.liked);
      button.classList.toggle('btn-outline-primary', !data.data.liked);
    } catch (error) {
      alert(error.message);
    } finally {
      button.disabled = false;
    }
  };

  const shareBlog = async (button) => {
    const id = Number(likeButton.dataset.blogId);
    if (!id) return;

    const shareData = {
      title: titleNode.textContent,
      text: 'Read this LevelMinds blog',
      url: `${window.location.origin}/blogs.php?post=${id}`
    };

    try {
      if (navigator.share) {
        await navigator.share(shareData);
      } else {
        await navigator.clipboard.writeText(shareData.url);
        button.textContent = 'Link copied!';
        setTimeout(() => (button.textContent = 'Share'), 2000);
      }
    } catch (error) {
      console.error(error);
    }
  };

  document.querySelectorAll('[data-action="open-blog"]').forEach((trigger) => {
    trigger.addEventListener('click', (event) => {
      event.preventDefault();
      const id = trigger.getAttribute('data-blog-id');
      if (id) {
        openBlog(id);
      }
    });
  });

  likeButton.addEventListener('click', () => likeBlog(likeButton));
  shareButton.addEventListener('click', () => shareBlog(shareButton));
})();
</script>

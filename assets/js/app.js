/* ============================================================
   app.js — public site behaviour
   blog.skoolyst.com
   ============================================================ */

(function () {
  'use strict';

  /* ---- Mobile nav toggle ---- */
  function initNavToggle() {
    var toggle = document.querySelector('.nav-toggle');
    var links = document.querySelector('.site-nav .nav-links');
    if (!toggle || !links) return;
    toggle.addEventListener('click', function () {
      links.classList.toggle('open');
      var expanded = links.classList.contains('open');
      toggle.setAttribute('aria-expanded', expanded);
    });
  }

  /* ---- Toast (shared helper) ---- */
  window.showToast = function (message, type) {
    var container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    var t = document.createElement('div');
    t.className = 'toast ' + (type || 'info');
    var icon = type === 'success' ? '\u2713' : type === 'error' ? '\u26a0' : '\u2139';
    t.innerHTML = '<span class="toast-icon">' + icon + '</span><span>' + escapeHtml(message) + '</span>';
    container.appendChild(t);
    setTimeout(function () {
      t.classList.add('removing');
      setTimeout(function () { t.remove(); }, 250);
    }, 3000);
  };

  /* ---- Render a single post card ---- */
  function renderPostCard(post) {
    var cat = getCategoryById(post.category);
    var author = getAuthorById(post.author);
    var safeTitle = escapeHtml(post.title);
    var safeExcerpt = escapeHtml(post.excerpt);
    var safeAuthor = escapeHtml(author.name);
    var safeCat = escapeHtml(cat.name);
    var safeDate = escapeHtml(formatDate(post.publishedDate));

    var card = document.createElement('article');
    card.className = 'post-card';
    card.innerHTML =
      '<div class="card-cover">' +
        '<a href="post.html?id=' + encodeURIComponent(post.slug) + '">' +
          '<img src="' + post.coverImage + '" alt="' + safeTitle + '" loading="lazy" />' +
        '</a>' +
      '</div>' +
      '<div class="card-body">' +
        '<span class="card-chip" style="background:' + cat.color + '15;color:' + cat.color + '">' + safeCat + '</span>' +
        '<h3 class="card-title"><a href="post.html?id=' + encodeURIComponent(post.slug) + '">' + safeTitle + '</a></h3>' +
        '<p class="card-excerpt">' + safeExcerpt + '</p>' +
        '<div class="card-meta">' +
          '<img src="' + author.avatar + '" alt="' + safeAuthor + '" loading="lazy" />' +
          '<span>' + safeAuthor + '</span>' +
          '<span class="meta-dot"></span>' +
          '<span>' + safeDate + '</span>' +
          '<span class="meta-dot"></span>' +
          '<span>' + post.readTimeMinutes + ' min read</span>' +
        '</div>' +
      '</div>';
    return card;
  }

  window.renderPostCard = renderPostCard;

  /* ---- Render featured (large) card ---- */
  function renderFeaturedCard(post) {
    var card = renderPostCard(post);
    card.classList.add('featured');
    return card;
  }

  /* ---- Home page: featured + latest grid ---- */
  function initHome() {
    var featuredContainer = document.getElementById('featured-posts');
    var latestContainer = document.getElementById('latest-posts');
    if (!featuredContainer && !latestContainer) return;

    var published = getPublishedPosts();
    if (featuredContainer) {
      var feat = published.slice(0, 1);
      feat.forEach(function (p) { featuredContainer.appendChild(renderFeaturedCard(p)); });
    }
    if (latestContainer) {
      var latest = published.slice(1, 7);
      latest.forEach(function (p) { latestContainer.appendChild(renderPostCard(p)); });
    }
  }

  /* ---- Blog archive: list, search, filter, sort, paginate ---- */
  var blogState = { page: 1, perPage: 6, search: '', category: '', sort: 'newest' };

  function initBlog() {
    var container = document.getElementById('blog-posts');
    if (!container) return;

    var searchInput = document.getElementById('blog-search');
    var catSelect = document.getElementById('blog-category');
    var sortSelect = document.getElementById('blog-sort');
    var pagination = document.getElementById('blog-pagination');

    function getFiltered() {
      var posts = getPublishedPosts();
      if (blogState.search) {
        var q = blogState.search.toLowerCase();
        posts = posts.filter(function (p) {
          return p.title.toLowerCase().indexOf(q) > -1 ||
            p.excerpt.toLowerCase().indexOf(q) > -1;
        });
      }
      if (blogState.category) {
        posts = posts.filter(function (p) { return p.category === blogState.category; });
      }
      if (blogState.sort === 'newest') {
        posts.sort(function (a, b) { return new Date(b.publishedDate) - new Date(a.publishedDate); });
      } else if (blogState.sort === 'oldest') {
        posts.sort(function (a, b) { return new Date(a.publishedDate) - new Date(b.publishedDate); });
      } else if (blogState.sort === 'views') {
        posts.sort(function (a, b) { return b.views - a.views; });
      }
      return posts;
    }

    function render() {
      var posts = getFiltered();
      var totalPages = Math.max(1, Math.ceil(posts.length / blogState.perPage));
      if (blogState.page > totalPages) blogState.page = totalPages;
      var start = (blogState.page - 1) * blogState.perPage;
      var pagePosts = posts.slice(start, start + blogState.perPage);

      container.innerHTML = '';
      if (pagePosts.length === 0) {
        container.innerHTML = '<div class="empty-state"><div class="empty-icon">\u{1F50D}</div><h3>No posts found</h3><p>Try a different search or filter.</p></div>';
      } else {
        pagePosts.forEach(function (p) { container.appendChild(renderPostCard(p)); });
      }

      renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
      if (!pagination) return;
      pagination.innerHTML = '';
      if (totalPages <= 1) return;

      var prev = document.createElement('button');
      prev.textContent = '\u2190 Prev';
      prev.className = blogState.page === 1 ? 'disabled' : '';
      prev.disabled = blogState.page === 1;
      prev.addEventListener('click', function () {
        if (blogState.page > 1) { blogState.page--; render(); }
      });
      pagination.appendChild(prev);

      for (var i = 1; i <= totalPages; i++) {
        var btn = document.createElement('button');
        btn.textContent = i;
        btn.className = i === blogState.page ? 'active' : '';
        (function (pageNum) {
          btn.addEventListener('click', function () {
            blogState.page = pageNum;
            render();
            window.scrollTo({ top: container.offsetTop - 100, behavior: 'smooth' });
          });
        })(i);
        pagination.appendChild(btn);
      }

      var next = document.createElement('button');
      next.textContent = 'Next \u2192';
      next.className = blogState.page === totalPages ? 'disabled' : '';
      next.disabled = blogState.page === totalPages;
      next.addEventListener('click', function () {
        if (blogState.page < totalPages) { blogState.page++; render(); }
      });
      pagination.appendChild(next);
    }

    if (searchInput) {
      var debounce;
      searchInput.addEventListener('input', function () {
        clearTimeout(debounce);
        debounce = setTimeout(function () {
          blogState.search = searchInput.value.trim();
          blogState.page = 1;
          render();
        }, 200);
      });
    }
    if (catSelect) {
      catSelect.addEventListener('change', function () {
        blogState.category = catSelect.value;
        blogState.page = 1;
        render();
      });
    }
    if (sortSelect) {
      sortSelect.addEventListener('change', function () {
        blogState.sort = sortSelect.value;
        render();
      });
    }

    render();
  }

  /* ---- Category page ---- */
  function initCategoryPage() {
    var container = document.getElementById('category-posts');
    if (!container) return;

    var params = new URLSearchParams(window.location.search);
    var slug = params.get('cat') || '';
    var cat = MOCK_CATEGORIES.find(function (c) { return c.slug === slug; });
    var titleEl = document.getElementById('category-title');
    var descEl = document.getElementById('category-desc');

    if (!cat) {
      if (titleEl) titleEl.textContent = 'Category Not Found';
      container.innerHTML = '<div class="empty-state"><h3>Category not found</h3><p>This category doesn\u2019t exist.</p></div>';
      return;
    }

    if (titleEl) titleEl.textContent = cat.name;
    if (descEl) descEl.textContent = cat.description;

    var posts = getPostsByCategory(cat.id);
    if (posts.length === 0) {
      container.innerHTML = '<div class="empty-state"><h3>No posts yet</h3><p>There are no published posts in this category.</p></div>';
    } else {
      posts.forEach(function (p) { container.appendChild(renderPostCard(p)); });
    }
  }

  /* ---- Single post page ---- */
  function initPostPage() {
    var container = document.getElementById('post-content');
    if (!container) return;

    var params = new URLSearchParams(window.location.search);
    var id = params.get('id') || '';
    var post = getPostById(id);

    if (!post || post.status !== 'published') {
      document.getElementById('post-hero').innerHTML = '<h1>Post Not Found</h1><p>This post doesn\u2019t exist or isn\u2019t published yet.</p>';
      document.getElementById('post-cover-wrap').style.display = 'none';
      document.getElementById('post-body').style.display = 'none';
      document.getElementById('post-tags').style.display = 'none';
      document.getElementById('author-block').style.display = 'none';
      document.getElementById('share-row').style.display = 'none';
      document.getElementById('comments-section').style.display = 'none';
      document.getElementById('related-posts').style.display = 'none';
      return;
    }

    var cat = getCategoryById(post.category);
    var author = getAuthorById(post.author);

    document.title = post.title + ' \u2014 Skoolyst Blog';

    var heroEl = document.getElementById('post-hero');
    heroEl.innerHTML =
      '<span class="post-chip" style="background:' + cat.color + '15;color:' + cat.color + '">' + escapeHtml(cat.name) + '</span>' +
      '<h1>' + escapeHtml(post.title) + '</h1>' +
      '<div class="post-meta">' +
        '<img src="' + author.avatar + '" alt="' + escapeHtml(author.name) + '" />' +
        '<span>' + escapeHtml(author.name) + '</span>' +
        '<span class="meta-dot"></span>' +
        '<span>' + escapeHtml(formatDate(post.publishedDate)) + '</span>' +
        '<span class="meta-dot"></span>' +
        '<span>' + post.readTimeMinutes + ' min read</span>' +
        '<span class="meta-dot"></span>' +
        '<span>' + post.views.toLocaleString() + ' views</span>' +
      '</div>';

    var coverWrap = document.getElementById('post-cover-wrap');
    coverWrap.innerHTML = '<img src="' + post.coverImage + '" alt="' + escapeHtml(post.title) + '" />';

    var bodyEl = document.getElementById('post-body');
    bodyEl.innerHTML = renderBody(post.body);

    var tagsEl = document.getElementById('post-tags');
    if (post.tags && post.tags.length) {
      tagsEl.innerHTML = post.tags.map(function (t) {
        return '<span class="tag">' + escapeHtml(t) + '</span>';
      }).join('');
    } else {
      tagsEl.style.display = 'none';
    }

    var authorEl = document.getElementById('author-block');
    authorEl.innerHTML =
      '<img src="' + author.avatar + '" alt="' + escapeHtml(author.name) + '" />' +
      '<div>' +
        '<p class="author-name">' + escapeHtml(author.name) + '</p>' +
        '<p class="author-bio">' + escapeHtml(author.bio) + '</p>' +
      '</div>';

    /* Related posts */
    var relatedEl = document.getElementById('related-posts');
    var related = getRelatedPosts(post, 3);
    if (related.length > 0) {
      var grid = document.createElement('div');
      grid.className = 'post-grid';
      related.forEach(function (p) { grid.appendChild(renderPostCard(p)); });
      relatedEl.appendChild(grid);
    } else {
      relatedEl.style.display = 'none';
    }

    /* Comments */
    renderComments(post.id);

    /* Comment form */
    var commentForm = document.getElementById('comment-form-el');
    if (commentForm) {
      commentForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var textarea = commentForm.querySelector('textarea');
        if (!textarea.value.trim()) {
          window.showToast('Please write a comment first.', 'error');
          return;
        }
        window.showToast('Comment submitted! It will appear once approved. (Demo only \u2014 no backend)', 'success');
        textarea.value = '';
      });
    }
  }

  /* ---- Render markdown-ish body ---- */
  function renderBody(body) {
    var html = escapeHtml(body);
    /* Headings */
    html = html.replace(/^## (.+)$/gm, '<h2>$1</h2>');
    /* Paragraphs (lines not already wrapped) */
    html = html.split('\n\n').map(function (block) {
      block = block.trim();
      if (!block) return '';
      if (block.startsWith('<h2>')) return block;
      return '<p>' + block.replace(/\n/g, '<br>') + '</p>';
    }).join('\n');
    /* Inline code */
    html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
    return html;
  }
  window.renderBody = renderBody;

  /* ---- Comments ---- */
  function renderComments(postId) {
    var container = document.getElementById('comments-list');
    if (!container) return;
    var comments = MOCK_COMMENTS.filter(function (c) { return c.postId === postId; });
    if (comments.length === 0) {
      container.innerHTML = '<p style="color:var(--text-muted)">No comments yet. Be the first to share your thoughts.</p>';
      return;
    }
    container.innerHTML = comments.map(function (c) {
      return '<div class="comment">' +
        '<img src="' + c.avatar + '" alt="' + escapeHtml(c.author) + '" loading="lazy" />' +
        '<div>' +
          '<div class="comment-head">' +
            '<span class="comment-author">' + escapeHtml(c.author) + '</span>' +
            '<span class="comment-date">' + escapeHtml(formatDate(c.date)) + '</span>' +
          '</div>' +
          '<p class="comment-body">' + escapeHtml(c.body) + '</p>' +
        '</div>' +
      '</div>';
    }).join('');
  }

  /* ---- Contact form validation ---- */
  function initContactForm() {
    var form = document.getElementById('contact-form-el');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var valid = true;
      var fields = ['name', 'email', 'message'];
      fields.forEach(function (name) {
        var input = form.querySelector('[name="' + name + '"]');
        var err = form.querySelector('[data-error="' + name + '"]');
        if (err) err.classList.remove('show');
        if (!input.value.trim()) {
          if (err) { err.textContent = 'This field is required.'; err.classList.add('show'); }
          valid = false;
        }
      });
      var email = form.querySelector('[name="email"]');
      var emailErr = form.querySelector('[data-error="email"]');
      if (email.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        if (emailErr) { emailErr.textContent = 'Please enter a valid email address.'; emailErr.classList.add('show'); }
        valid = false;
      }
      if (valid) {
        window.showToast('Message sent! We\u2019ll get back to you soon. (Demo only \u2014 no backend)', 'success');
        form.reset();
      }
    });
  }

  /* ---- Newsletter forms ---- */
  function initNewsletter() {
    var forms = document.querySelectorAll('.newsletter form, .hero-search');
    forms.forEach(function (form) {
      if (form.tagName === 'FORM') {
        form.addEventListener('submit', function (e) {
          e.preventDefault();
          var input = form.querySelector('input[type="email"]');
          if (input && !input.value.trim()) {
            window.showToast('Please enter your email address.', 'error');
            return;
          }
          window.showToast('You\u2019re subscribed! (Demo only \u2014 no backend)', 'success');
          form.reset();
        });
      }
    });
  }

  /* ---- Share buttons ---- */
  window.sharePost = function (platform, url, title) {
    var u = encodeURIComponent(url);
    var t = encodeURIComponent(title);
    var links = {
      twitter: 'https://twitter.com/intent/tweet?url=' + u + '&text=' + t,
      facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + u,
      linkedin: 'https://www.linkedin.com/sharing/share-offsite/?url=' + u
    };
    if (links[platform]) {
      window.open(links[platform], '_blank', 'noopener,noreferrer');
    }
  };

  /* ---- Init ---- */
  document.addEventListener('DOMContentLoaded', function () {
    initNavToggle();
    initHome();
    initBlog();
    initCategoryPage();
    initPostPage();
    initContactForm();
    initNewsletter();
  });
})();

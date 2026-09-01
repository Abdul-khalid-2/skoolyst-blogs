/* ============================================================
   app.js — public site behaviour
   blog.skoolyst.com

   Section 11 rewire: every page fetches from the real /api/v1
   backend (via api.js's Api.*) instead of reading MOCK_* arrays.
   Only escapeHtml()/formatDate() (now in utils.js) are used here.
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

  /* Backwards-compatible public alias for the shared post-card component. */
  window.renderPostCard = Card.post;

  /* ---- Empty/error state helper ---- */
  function stateBlock(icon, title, message) {
    return '<div class="empty-state"><div class="empty-icon">' + icon + '</div><h3>' + escapeHtml(title) + '</h3><p>' + escapeHtml(message) + '</p></div>';
  }

  /* ---- Render featured (large) card ---- */
  function renderFeaturedCard(post) {
    var card = Card.post(post);
    card.classList.add('featured');
    return card;
  }

  /* ---- Home page: featured + latest grid ---- */
  function initHome() {
    var featuredContainer = document.getElementById('featured-posts');
    var latestContainer = document.getElementById('latest-posts');
    if (!featuredContainer && !latestContainer) return;

    Api.get('/posts?per_page=7').then(function (res) {
      var posts = res.data || [];
      if (featuredContainer) {
        posts.slice(0, 1).forEach(function (p) { featuredContainer.appendChild(renderFeaturedCard(p)); });
        if (posts.length === 0) featuredContainer.innerHTML = stateBlock('\u{1F4DD}', 'No posts yet', 'Check back soon for new articles.');
      }
      if (latestContainer) {
        posts.slice(1, 7).forEach(function (p) { latestContainer.appendChild(Card.post(p)); });
      }
    }).catch(function (err) {
      if (featuredContainer) featuredContainer.innerHTML = stateBlock('\u26a0', 'Couldn\u2019t load posts', err.message);
    });
  }

  /* ---- Blog archive: list, search, filter, sort, paginate (server-side) ---- */
  var blogState = { page: 1, perPage: 6, search: '', category: '', sort: 'newest' };

  function initBlog() {
    var container = document.getElementById('blog-posts');
    if (!container) return;

    var searchInput = document.getElementById('blog-search');
    var catSelect = document.getElementById('blog-category');
    var sortSelect = document.getElementById('blog-sort');
    var pagination = document.getElementById('blog-pagination');

    /* Populate the category filter from the real API (was a hand-rolled
       inline script reading MOCK_CATEGORIES before). Values are category
       slugs, matching what PostRepository::publishedPaginated() filters on. */
    if (catSelect) {
      Api.get('/categories').then(function (res) {
        (res.data || []).forEach(function (c) {
          var opt = document.createElement('option');
          opt.value = c.slug;
          opt.textContent = c.name;
          catSelect.appendChild(opt);
        });
      }).catch(function () { /* filter just stays at "All categories" */ });
    }

    function sortPosts(posts) {
      /* The API only sorts by published date server-side; "oldest"/"views"
         are applied client-side on the current page, same scope the old
         mock version had (it only ever sorted within its filtered set). */
      if (blogState.sort === 'oldest') {
        posts.sort(function (a, b) { return new Date(a.published_date) - new Date(b.published_date); });
      } else if (blogState.sort === 'views') {
        posts.sort(function (a, b) { return b.views - a.views; });
      }
      return posts;
    }

    function render() {
      var params = new URLSearchParams();
      params.set('page', blogState.page);
      params.set('per_page', blogState.perPage);
      if (blogState.search) params.set('search', blogState.search);
      if (blogState.category) params.set('category', blogState.category);

      Api.get('/posts?' + params.toString()).then(function (res) {
        var posts = sortPosts(res.data || []);
        var totalPages = (res.meta && res.meta.total_pages) || 1;
        if (blogState.page > totalPages && totalPages > 0) {
          blogState.page = totalPages;
          render();
          return;
        }

        container.innerHTML = '';
        if (posts.length === 0) {
          container.innerHTML = stateBlock('\u{1F50D}', 'No posts found', 'Try a different search or filter.');
        } else {
          posts.forEach(function (p) { container.appendChild(Card.post(p)); });
        }

        renderPagination(totalPages);
      }).catch(function (err) {
        container.innerHTML = stateBlock('\u26a0', 'Couldn\u2019t load posts', err.message);
        if (pagination) pagination.innerHTML = '';
      });
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
        }, 300);
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
    var titleEl = document.getElementById('category-title');
    var descEl = document.getElementById('category-desc');

    if (!slug) {
      if (titleEl) titleEl.textContent = 'Category Not Found';
      container.innerHTML = stateBlock('\u{1F937}', 'Category not found', 'This category doesn\u2019t exist.');
      return;
    }

    Api.get('/categories/' + encodeURIComponent(slug)).then(function (res) {
      var cat = res.data;
      if (titleEl) titleEl.textContent = cat.name;
      if (descEl) descEl.textContent = cat.description || '';

      return Api.get('/posts?category=' + encodeURIComponent(slug) + '&per_page=50').then(function (postsRes) {
        var posts = postsRes.data || [];
        if (posts.length === 0) {
          container.innerHTML = stateBlock('\u{1F4ED}', 'No posts yet', 'There are no published posts in this category.');
        } else {
          posts.forEach(function (p) { container.appendChild(Card.post(p)); });
        }
      });
    }).catch(function (err) {
      if (err.status === 404) {
        if (titleEl) titleEl.textContent = 'Category Not Found';
        container.innerHTML = stateBlock('\u{1F937}', 'Category not found', 'This category doesn\u2019t exist.');
      } else {
        container.innerHTML = stateBlock('\u26a0', 'Couldn\u2019t load category', err.message);
      }
    });
  }

  /* ---- Single post page ---- */
  function initPostPage() {
    var container = document.getElementById('post-content');
    if (!container) return;

    var params = new URLSearchParams(window.location.search);
    var id = params.get('id') || '';

    function showNotFound() {
      document.getElementById('post-hero').innerHTML = '<h1>Post Not Found</h1><p>This post doesn\u2019t exist or isn\u2019t published yet.</p>';
      document.getElementById('post-cover-wrap').style.display = 'none';
      document.getElementById('post-body').style.display = 'none';
      document.getElementById('post-tags').style.display = 'none';
      document.getElementById('author-block').style.display = 'none';
      document.getElementById('share-row').style.display = 'none';
      document.getElementById('comments-section').style.display = 'none';
      document.getElementById('related-posts').style.display = 'none';
    }

    if (!id || !/^\d+$/.test(id)) {
      showNotFound();
      return;
    }

    Api.get('/posts/' + id).then(function (res) {
      var post = res.data;

      Api.post('/posts/' + id + '/view', {}).catch(function () { /* view counting is best-effort */ });

      var cat = post.category || {};

      document.title = post.title + ' \u2014 Skoolyst Blog';

      var heroEl = document.getElementById('post-hero');
      heroEl.innerHTML =
        '<span class="post-chip" style="background:' + (cat.color || '#4361ee') + '15;color:' + (cat.color || '#4361ee') + '">' + escapeHtml(cat.name || 'Uncategorized') + '</span>' +
        '<h1>' + escapeHtml(post.title) + '</h1>' +
        '<div class="post-meta">' +
          '<span>' + escapeHtml(post.author_name || 'Skoolyst') + '</span>' +
          '<span class="meta-dot"></span>' +
          '<span>' + escapeHtml(formatDate(post.published_date)) + '</span>' +
          '<span class="meta-dot"></span>' +
          '<span>' + Card.readTime(post.body) + ' min read</span>' +
          '<span class="meta-dot"></span>' +
          '<span>' + post.views.toLocaleString() + ' views</span>' +
        '</div>';

      var coverWrap = document.getElementById('post-cover-wrap');
      if (post.cover_image) {
        coverWrap.innerHTML = '<img src="' + post.cover_image + '" alt="' + escapeHtml(post.title) + '" />';
      } else {
        coverWrap.style.display = 'none';
      }

      var bodyEl = document.getElementById('post-body');
      bodyEl.innerHTML = renderBody(post.body);

      /* The backend doesn't store/return post tags yet (Post::toArray() has
         no tags field) — hide the tags row rather than show stale/fake data. */
      var tagsEl = document.getElementById('post-tags');
      tagsEl.style.display = 'none';

      var authorEl = document.getElementById('author-block');
      authorEl.innerHTML =
        '<div>' +
          '<p class="author-name">' + escapeHtml(post.author_name || 'Skoolyst') + '</p>' +
        '</div>';

      /* Related posts: other published posts in the same category. */
      var relatedEl = document.getElementById('related-posts');
      if (post.category_id) {
        Api.get('/posts?category=' + encodeURIComponent(cat.slug) + '&per_page=4').then(function (relRes) {
          var related = (relRes.data || []).filter(function (p) { return p.id !== post.id; }).slice(0, 3);
          if (related.length > 0) {
            var grid = document.createElement('div');
            grid.className = 'post-grid';
            related.forEach(function (p) { grid.appendChild(Card.post(p)); });
            relatedEl.appendChild(grid);
          } else {
            relatedEl.style.display = 'none';
          }
        }).catch(function () { relatedEl.style.display = 'none'; });
      } else {
        relatedEl.style.display = 'none';
      }

      /* Comments (embedded in the post response) + submission form */
      renderComments(post.comments || []);
      wireCommentForm(post.id);
    }).catch(function (err) {
      if (err.status === 404) {
        showNotFound();
      } else {
        document.getElementById('post-hero').innerHTML = '<h1>Couldn\u2019t load this post</h1><p>' + escapeHtml(err.message) + '</p>';
      }
    });
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
  function renderComments(comments) {
    var container = document.getElementById('comments-list');
    if (!container) return;
    if (comments.length === 0) {
      container.innerHTML = '<p style="color:var(--text-muted)">No comments yet. Be the first to share your thoughts.</p>';
      return;
    }
    container.innerHTML = comments.map(function (c) {
      return '<div class="comment">' +
        '<div>' +
          '<div class="comment-head">' +
            '<span class="comment-author">' + escapeHtml(c.author_name) + '</span>' +
            '<span class="comment-date">' + escapeHtml(formatDate(c.created_at)) + '</span>' +
          '</div>' +
          '<p class="comment-body">' + escapeHtml(c.body) + '</p>' +
        '</div>' +
      '</div>';
    }).join('');
  }

  /* Submitting posts to POST /posts/{id}/comments, which requires a name
     + email the old demo-only form never collected — added here since
     there's no way to satisfy that validation without them. */
  function wireCommentForm(postId) {
    var commentForm = document.getElementById('comment-form-el');
    if (!commentForm) return;
    var textarea = commentForm.querySelector('textarea');
    var nameInput = commentForm.querySelector('[name="comment-name"]');
    var emailInput = commentForm.querySelector('[name="comment-email"]');
    var submitBtn = commentForm.querySelector('button[type="submit"]');

    commentForm.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!nameInput.value.trim() || !emailInput.value.trim() || !textarea.value.trim()) {
        window.showToast('Please fill in your name, email, and a comment.', 'error');
        return;
      }

      submitBtn.disabled = true;
      Api.post('/posts/' + postId + '/comments', {
        author_name: nameInput.value.trim(),
        author_email: emailInput.value.trim(),
        body: textarea.value.trim()
      }).then(function () {
        window.showToast('Comment submitted! It will appear once approved.', 'success');
        textarea.value = '';
        nameInput.value = '';
        emailInput.value = '';
      }).catch(function (err) {
        window.showToast(err.message || 'Could not submit comment.', 'error');
      }).finally(function () {
        submitBtn.disabled = false;
      });
    });
  }

  /* ---- Contact form validation (no backend endpoint for this yet \u2014 stays demo-only) ---- */
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

  /* ---- Newsletter forms (no backend endpoint for this yet \u2014 stays demo-only) ---- */
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

/* ============================================================
   dashboard.js — dashboard behaviour (mock CRUD)
   blog.skoolyst.com
   ============================================================ */

(function () {
  'use strict';

  /* ---- State (in-memory + localStorage persistence) ---- */
  var STORAGE_KEY = 'skoolyst_blog_data';

  function loadState() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (raw) return JSON.parse(raw);
    } catch (e) { /* ignore */ }
    return null;
  }

  function saveState() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({
        posts: MOCK_POSTS,
        categories: MOCK_CATEGORIES,
        media: MOCK_MEDIA
      }));
    } catch (e) { /* ignore */ }
  }

  function initState() {
    var stored = loadState();
    if (stored) {
      if (stored.posts) window.MOCK_POSTS = stored.posts;
      if (stored.categories) window.MOCK_CATEGORIES = stored.categories;
      if (stored.media) window.MOCK_MEDIA = stored.media;
    }
  }

  /* ---- Toast ---- */
  window.dashToast = function (message, type) {
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

  /* ---- Sidebar toggle ---- */
  function initSidebar() {
    var toggle = document.querySelector('.dash-sidebar-toggle');
    var sidebar = document.querySelector('.dash-sidebar');
    var backdrop = document.querySelector('.sidebar-backdrop');
    if (!toggle || !sidebar) return;
    function close() {
      sidebar.classList.remove('open');
      if (backdrop) backdrop.classList.remove('show');
    }
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
      if (backdrop) backdrop.classList.toggle('show');
    });
    if (backdrop) backdrop.addEventListener('click', close);
  }

  /* ---- Dashboard overview ---- */
  function initOverview() {
    var statsEl = document.getElementById('dash-stats');
    if (!statsEl) return;

    var stats = {
      total: MOCK_POSTS.length,
      published: MOCK_POSTS.filter(function (p) { return p.status === 'published'; }).length,
      drafts: MOCK_POSTS.filter(function (p) { return p.status === 'draft'; }).length,
      views: MOCK_POSTS.reduce(function (s, p) { return s + p.views; }, 0)
    };

    statsEl.innerHTML =
      Card.stat('blue', '\u{1F4DD}', 'Total Posts', stats.total, '+12%', true) +
      Card.stat('green', '\u2705', 'Published', stats.published, '+3', true) +
      Card.stat('amber', '\u270f\uFE0F', 'Drafts', stats.drafts, '\u2014', false) +
      Card.stat('purple', '\u{1F441}', 'Total Views', stats.views.toLocaleString(), '+18%', true);

    /* Bar chart */
    var chartEl = document.getElementById('views-chart');
    if (chartEl) {
      var data = MOCK_STATS.monthlyViews;
      var max = Math.max.apply(null, data.map(function (d) { return d.views; }));
      chartEl.innerHTML = data.map(function (d) {
        var h = Math.round((d.views / max) * 100);
        return '<div class="bar-col">' +
          '<div class="bar" style="height:' + h + '%">' +
            '<span class="bar-value">' + d.views.toLocaleString() + '</span>' +
          '</div>' +
          '<span class="bar-label">' + d.month + '</span>' +
        '</div>';
      }).join('');
    }

    /* Recent posts table */
    var recentEl = document.getElementById('recent-posts');
    if (recentEl) {
      var recent = MOCK_POSTS.slice().sort(function (a, b) {
        return new Date(b.publishedDate) - new Date(a.publishedDate);
      }).slice(0, 5);
      recentEl.innerHTML = recent.map(function (p) {
        var cat = getCategoryById(p.category);
        return '<tr>' +
          '<td><a href="post-editor.html?edit=' + p.id + '" class="table-title">' + escapeHtml(p.title) + '</a></td>' +
          '<td>' + escapeHtml(cat.name) + '</td>' +
          '<td>' + Badge.status(p.status) + '</td>' +
          '<td>' + p.views.toLocaleString() + '</td>' +
          '<td>' + escapeHtml(formatDate(p.publishedDate)) + '</td>' +
        '</tr>';
      }).join('');
    }
  }

  /* ---- Posts management ---- */
  var postsState = { search: '', status: '', category: '' };

  function initPosts() {
    var tableBody = document.getElementById('posts-table-body');
    if (!tableBody) return;

    var searchInput = document.getElementById('posts-search');
    var statusSelect = document.getElementById('posts-status');
    var catSelect = document.getElementById('posts-category');

    function getFiltered() {
      return MOCK_POSTS.filter(function (p) {
        if (postsState.search) {
          var q = postsState.search.toLowerCase();
          if (p.title.toLowerCase().indexOf(q) === -1) return false;
        }
        if (postsState.status && p.status !== postsState.status) return false;
        if (postsState.category && p.category !== postsState.category) return false;
        return true;
      });
    }

    function render() {
      var posts = getFiltered();
      if (posts.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">No posts found.</td></tr>';
        return;
      }
      tableBody.innerHTML = posts.map(function (p) {
        var cat = getCategoryById(p.category);
        return '<tr data-id="' + p.id + '">' +
          '<td>' +
            '<div style="display:flex;gap:.75rem;align-items:center">' +
              '<img src="' + p.coverImage + '" class="table-thumb" alt="" loading="lazy" />' +
              '<a href="post-editor.html?edit=' + p.id + '" class="table-title">' + escapeHtml(p.title) + '</a>' +
            '</div>' +
          '</td>' +
          '<td>' + escapeHtml(cat.name) + '</td>' +
          '<td>' + Badge.status(p.status) + '</td>' +
          '<td>' + p.views.toLocaleString() + '</td>' +
          '<td>' + escapeHtml(formatDate(p.publishedDate)) + '</td>' +
          '<td>' + Table.actions([
              Button.action('\u270f\uFE0F', { href: 'post-editor.html?edit=' + p.id, title: 'Edit' }),
              Button.action(p.status === 'published' ? '\u{1F441}' : '\u{1F4E2}', {
                extraClass: 'toggle-pub', dataId: p.id,
                title: p.status === 'published' ? 'Unpublish' : 'Publish', ariaLabel: 'Toggle publish'
              }),
              Button.action('\u{1F5D1}', { extraClass: 'delete-post', dataId: p.id, danger: true, title: 'Delete' })
            ]) +
          '</td>' +
        '</tr>';
      }).join('');

      /* Wire action buttons */
      tableBody.querySelectorAll('.toggle-pub').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-id');
          var post = MOCK_POSTS.find(function (p) { return p.id === id; });
          if (!post) return;
          post.status = post.status === 'published' ? 'draft' : 'published';
          saveState();
          dashToast(post.status === 'published' ? 'Post published.' : 'Post unpublished.', 'success');
          render();
        });
      });

      tableBody.querySelectorAll('.delete-post').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-id');
          var post = MOCK_POSTS.find(function (p) { return p.id === id; });
          if (!post) return;
          Modal.confirm('Delete "' + post.title + '"? This cannot be undone.', function () {
            var idx = MOCK_POSTS.indexOf(post);
            MOCK_POSTS.splice(idx, 1);
            saveState();
            dashToast('Post deleted.', 'success');
            render();
          });
        });
      });
    }

    if (searchInput) {
      var debounce;
      searchInput.addEventListener('input', function () {
        clearTimeout(debounce);
        debounce = setTimeout(function () {
          postsState.search = searchInput.value.trim();
          render();
        }, 200);
      });
    }
    if (statusSelect) {
      statusSelect.addEventListener('change', function () {
        postsState.status = statusSelect.value;
        render();
      });
    }
    if (catSelect) {
      catSelect.addEventListener('change', function () {
        postsState.category = catSelect.value;
        render();
      });
    }

    render();
  }

  /* ---- Post editor ---- */
  function initPostEditor() {
    var form = document.getElementById('post-editor-form');
    if (!form) return;

    renderPostEditorFields();

    var params = new URLSearchParams(window.location.search);
    var editId = params.get('edit');
    var editing = editId ? MOCK_POSTS.find(function (p) { return p.id === editId; }) : null;

    var titleInput = form.querySelector('[name="title"]');
    var slugInput = form.querySelector('[name="slug"]');
    var categorySelect = form.querySelector('[name="category"]');
    var tagsInput = form.querySelector('[name="tags"]');
    var excerptInput = form.querySelector('[name="excerpt"]');
    var bodyInput = form.querySelector('[name="body"]');
    var coverPreview = document.getElementById('cover-preview');
    var coverInput = document.getElementById('cover-input');
    var statusRadios = form.querySelectorAll('[name="status"]');
    var seoTitleInput = form.querySelector('[name="seo-title"]');
    var seoDescInput = form.querySelector('[name="seo-desc"]');

    /* Populate categories */
    if (categorySelect) {
      categorySelect.innerHTML = '<option value="">Select category\u2026</option>' +
        MOCK_CATEGORIES.map(function (c) {
          return '<option value="' + c.id + '">' + escapeHtml(c.name) + '</option>';
        }).join('');
    }

    /* Prefill if editing */
    if (editing) {
      if (titleInput) titleInput.value = editing.title;
      if (slugInput) slugInput.value = editing.slug;
      if (categorySelect) categorySelect.value = editing.category;
      if (tagsInput) tagsInput.value = editing.tags.join(', ');
      if (excerptInput) excerptInput.value = editing.excerpt;
      if (bodyInput) bodyInput.value = editing.body;
      if (coverPreview) coverPreview.innerHTML = '<img src="' + editing.coverImage + '" alt="Cover preview" />';
      if (seoTitleInput) seoTitleInput.value = editing.title;
      if (seoDescInput) seoDescInput.value = editing.excerpt;
      statusRadios.forEach(function (r) {
        r.checked = r.value === editing.status;
      });
      var titleEl = document.getElementById('editor-page-title');
      if (titleEl) titleEl.textContent = 'Edit Post';
    } else {
      var titleEl2 = document.getElementById('editor-page-title');
      if (titleEl2) titleEl2.textContent = 'New Post';
    }

    /* Auto-generate slug from title */
    if (titleInput && slugInput) {
      titleInput.addEventListener('input', function () {
        if (editing && slugInput.value) return; /* don't override if editing */
        slugInput.value = slugify(titleInput.value);
      });
    }

    /* Slug regenerate button */
    var regenBtn = document.getElementById('regen-slug');
    if (regenBtn && titleInput && slugInput) {
      regenBtn.addEventListener('click', function () {
        slugInput.value = slugify(titleInput.value);
      });
    }

    /* Cover image preview */
    if (coverInput && coverPreview) {
      coverInput.addEventListener('change', function () {
        var file = coverInput.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
          coverPreview.innerHTML = '<img src="' + e.target.result + '" alt="Cover preview" />';
        };
        reader.readAsDataURL(file);
      });
    }

    /* Save */
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      /* Validate */
      var errors = [];
      if (!titleInput.value.trim()) errors.push('Title is required.');
      if (!slugInput.value.trim()) errors.push('Slug is required.');
      if (!categorySelect.value) errors.push('Please select a category.');
      if (!bodyInput.value.trim()) errors.push('Post body is required.');

      if (errors.length) {
        dashToast(errors[0], 'error');
        return;
      }

      var status = 'draft';
      statusRadios.forEach(function (r) { if (r.checked) status = r.value; });

      var coverUrl = editing ? editing.coverImage : 'https://images.pexels.com/photos/8197511/pexels-photo-8197511.jpeg?auto=compress&cs=tinysrgb&h=650&w=940';
      var previewImg = coverPreview ? coverPreview.querySelector('img') : null;
      if (previewImg) coverUrl = previewImg.src;

      var tags = tagsInput.value.split(',').map(function (t) { return t.trim(); }).filter(Boolean);

      if (editing) {
        editing.title = titleInput.value.trim();
        editing.slug = slugInput.value.trim();
        editing.category = categorySelect.value;
        editing.tags = tags;
        editing.excerpt = excerptInput.value.trim();
        editing.body = bodyInput.value.trim();
        editing.coverImage = coverUrl;
        editing.status = status;
        dashToast('Post updated successfully.', 'success');
      } else {
        var newPost = {
          id: 'p' + Date.now(),
          title: titleInput.value.trim(),
          slug: slugInput.value.trim(),
          excerpt: excerptInput.value.trim() || bodyInput.value.trim().slice(0, 150) + '\u2026',
          body: bodyInput.value.trim(),
          coverImage: coverUrl,
          category: categorySelect.value,
          tags: tags,
          author: 'a1',
          status: status,
          publishedDate: new Date().toISOString().slice(0, 10),
          views: 0,
          readTimeMinutes: Math.max(1, Math.round(bodyInput.value.trim().split(/\s+/).length / 200))
        };
        MOCK_POSTS.unshift(newPost);
        dashToast('Post created successfully.', 'success');
      }

      saveState();
      setTimeout(function () { window.location.href = 'posts.html'; }, 800);
    });

    /* Cancel */
    var cancelBtn = document.getElementById('editor-cancel');
    if (cancelBtn) {
      cancelBtn.addEventListener('click', function () {
        window.location.href = 'posts.html';
      });
    }
  }

  /* Build the post-editor controls from the shared form components. Keeping
     the editor's existing IDs, names, classes, and wrapper structure means
     its CSS and save/edit behaviour remain unchanged. */
  function renderPostEditorFields() {
    var content = document.getElementById('editor-content-fields');
    var seo = document.getElementById('editor-seo-fields');
    var status = document.getElementById('editor-status-field');
    var category = document.getElementById('editor-category-field');
    var tags = document.getElementById('editor-tags-field');
    var cover = document.getElementById('editor-cover-field');

    if (content) {
      content.innerHTML =
        FormGroup.text('post-title', { label: 'Title', name: 'title', placeholder: 'Enter post title\u2026', required: true }) +
        FormGroup.wrap('Slug', 'post-slug',
          '<div class="slug-row">' + InputField.text('post-slug', { name: 'slug', placeholder: 'auto-generated-slug' }) +
          '<button type="button" id="regen-slug">Regenerate</button></div>',
          { required: true, hint: 'The URL-friendly version of the title.' }) +
        FormGroup.textarea('post-excerpt', { label: 'Excerpt', name: 'excerpt', rows: 2, placeholder: 'A short summary shown in post cards\u2026' }) +
        FormGroup.textarea('post-body', { label: 'Body', name: 'body', className: 'body-area', placeholder: 'Write your post here. Use ## for subheadings.\n\nParagraphs are separated by blank lines.', required: true, hint: 'Supports ## subheadings and blank-line paragraph breaks. No rich formatting in this demo.' });
    }
    if (seo) {
      seo.innerHTML =
        FormGroup.text('seo-title', { label: 'SEO Title', name: 'seo-title', placeholder: 'Title for search engines\u2026' }) +
        FormGroup.textarea('seo-desc', { label: 'SEO Description', name: 'seo-desc', rows: 2, placeholder: 'Meta description for search results\u2026' });
    }
    if (status) {
      status.innerHTML = FormGroup.radio('post-status', {
        label: 'Status', style: 'margin:0',
        radiosHtml: InputField.radio('status', 'draft', 'Draft', { checked: true }) + InputField.radio('status', 'published', 'Publish')
      });
    }
    if (category) category.innerHTML = FormGroup.select('post-category', { label: 'Category', labelClass: 'visually-hidden', name: 'category', style: 'margin:0' });
    if (tags) tags.innerHTML = FormGroup.text('post-tags', { label: 'Tags', labelClass: 'visually-hidden', name: 'tags', placeholder: 'comma, separated, tags', hint: 'Separate tags with commas.', style: 'margin:0' });
    if (cover) {
      cover.innerHTML = '<div class="cover-upload" id="cover-upload"><div class="upload-icon" aria-hidden="true">\u{1F4F7}</div>' +
        '<div class="upload-text">Click to upload or drag a file</div>' + InputField.file('cover-input', { accept: 'image/*', style: 'display:none' }) +
        '</div>';
    }
  }

  function slugify(text) {
    return text.toString().toLowerCase()
      .replace(/[^\w\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  /* ---- Categories management ---- */
  function initCategories() {
    var container = document.getElementById('categories-list');
    if (!container) return;

    var modal = document.getElementById('cat-modal');
    var modalTitle = document.getElementById('cat-modal-title');
    var modalBody = document.getElementById('cat-modal-body');

    /* Modal fields built via FormGroup/InputField instead of hand-written
       HTML (see README Section 5) — same classes/structure as before, so
       there's no visual change, just one shared place to build a form field. */
    if (modalBody) {
      modalBody.innerHTML =
        FormGroup.text('cat-name', { label: 'Name', required: true, placeholder: 'e.g. Classroom Tech' }) +
        FormGroup.textarea('cat-desc', { label: 'Description', rows: 2, placeholder: 'A short description of this category\u2026' }) +
        FormGroup.color('cat-color', { label: 'Color', value: '#4361ee' });
    }

    var nameInput = document.getElementById('cat-name');
    var descInput = document.getElementById('cat-desc');
    var colorInput = document.getElementById('cat-color');
    var saveBtn = document.getElementById('cat-save');
    var addBtn = document.getElementById('cat-add-btn');
    var closeBtn = document.getElementById('cat-modal-close');
    var cancelBtn = document.getElementById('cat-cancel');
    var editingId = null;

    function render() {
      container.innerHTML = MOCK_CATEGORIES.map(function (c) {
        var count = MOCK_POSTS.filter(function (p) { return p.category === c.id; }).length;
        return '<div class="cat-row" data-id="' + c.id + '">' +
          '<span class="cat-color" style="background:' + c.color + '"></span>' +
          '<div class="cat-info">' +
            '<p class="cat-name">' + escapeHtml(c.name) + '</p>' +
            '<p class="cat-desc">' + escapeHtml(c.description || '') + '</p>' +
          '</div>' +
          '<span class="cat-count">' + count + ' posts</span>' +
          Table.actions([
            Button.action('\u270f\uFE0F', { extraClass: 'edit-cat', dataId: c.id, title: 'Edit' }),
            Button.action('\u{1F5D1}', { extraClass: 'delete-cat', dataId: c.id, danger: true, title: 'Delete' })
          ]) +
        '</div>';
      }).join('');

      container.querySelectorAll('.edit-cat').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-id');
          var cat = MOCK_CATEGORIES.find(function (c) { return c.id === id; });
          if (!cat) return;
          editingId = id;
          modalTitle.textContent = 'Edit Category';
          nameInput.value = cat.name;
          descInput.value = cat.description || '';
          colorInput.value = cat.color;
          modal.classList.add('show');
        });
      });

      container.querySelectorAll('.delete-cat').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-id');
          var cat = MOCK_CATEGORIES.find(function (c) { return c.id === id; });
          if (!cat) return;
          var count = MOCK_POSTS.filter(function (p) { return p.category === id; }).length;
          if (count > 0) {
            dashToast('Cannot delete: ' + count + ' posts use this category.', 'error');
            return;
          }
          Modal.confirm('Delete "' + cat.name + '"?', function () {
            var idx = MOCK_CATEGORIES.indexOf(cat);
            MOCK_CATEGORIES.splice(idx, 1);
            saveState();
            dashToast('Category deleted.', 'success');
            render();
          });
        });
      });
    }

    function closeModal() {
      modal.classList.remove('show');
      editingId = null;
      nameInput.value = '';
      descInput.value = '';
      colorInput.value = '#4361ee';
    }

    if (addBtn) {
      addBtn.addEventListener('click', function () {
        editingId = null;
        modalTitle.textContent = 'Add Category';
        nameInput.value = '';
        descInput.value = '';
        colorInput.value = '#4361ee';
        modal.classList.add('show');
      });
    }
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        if (!nameInput.value.trim()) {
          dashToast('Category name is required.', 'error');
          return;
        }
        if (editingId) {
          var cat = MOCK_CATEGORIES.find(function (c) { return c.id === editingId; });
          if (cat) {
            cat.name = nameInput.value.trim();
            cat.description = descInput.value.trim();
            cat.color = colorInput.value;
          }
          dashToast('Category updated.', 'success');
        } else {
          MOCK_CATEGORIES.push({
            id: 'c' + Date.now(),
            name: nameInput.value.trim(),
            slug: slugify(nameInput.value.trim()),
            description: descInput.value.trim(),
            color: colorInput.value
          });
          dashToast('Category added.', 'success');
        }
        saveState();
        closeModal();
        render();
      });
    }

    /* Click outside modal to close */
    if (modal) {
      modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
      });
    }

    render();
  }

  /* ---- Media library ---- */
  function initMedia() {
    var grid = document.getElementById('media-grid');
    var uploadArea = document.getElementById('media-upload');
    var fileInput = document.getElementById('media-file-input');
    if (!grid) return;

    function render() {
      grid.innerHTML = MOCK_MEDIA.map(function (m) {
        return '<div class="media-item" data-id="' + m.id + '">' +
          '<div class="media-thumb"><img src="' + m.url + '" alt="' + escapeHtml(m.name) + '" loading="lazy" /></div>' +
          '<div class="media-info">' +
            '<p class="media-name">' + escapeHtml(m.name) + '</p>' +
            '<div class="media-meta">' +
              '<span>' + escapeHtml(m.size) + '</span>' +
              '<button class="media-delete" data-id="' + m.id + '">Delete</button>' +
            '</div>' +
          '</div>' +
        '</div>';
      }).join('');

      grid.querySelectorAll('.media-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-id');
          var item = MOCK_MEDIA.find(function (m) { return m.id === id; });
          if (!item) return;
          Modal.confirm('Delete "' + item.name + '"?', function () {
            var idx = MOCK_MEDIA.indexOf(item);
            MOCK_MEDIA.splice(idx, 1);
            saveState();
            dashToast('Image deleted.', 'success');
            render();
          });
        });
      });
    }

    if (uploadArea && fileInput) {
      uploadArea.addEventListener('click', function () { fileInput.click(); });
      uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        uploadArea.style.borderColor = 'var(--secondary)';
        uploadArea.style.background = 'var(--bg-alt)';
      });
      uploadArea.addEventListener('dragleave', function () {
        uploadArea.style.borderColor = '';
        uploadArea.style.background = '';
      });
      uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        uploadArea.style.borderColor = '';
        uploadArea.style.background = '';
        handleFiles(e.dataTransfer.files);
      });
      fileInput.addEventListener('change', function () {
        handleFiles(fileInput.files);
        fileInput.value = '';
      });
    }

    function handleFiles(files) {
      var added = 0;
      Array.from(files).forEach(function (file) {
        if (!file.type.startsWith('image/')) return;
        var reader = new FileReader();
        reader.onload = function (e) {
          MOCK_MEDIA.unshift({
            id: 'm' + Date.now() + Math.random().toString(36).slice(2, 6),
            name: file.name,
            url: e.target.result,
            size: formatBytes(file.size),
            uploaded: new Date().toISOString().slice(0, 10)
          });
          saveState();
          added++;
          if (added === files.length) {
            dashToast(added + ' image(s) added.', 'success');
            render();
          }
        };
        reader.readAsDataURL(file);
      });
    }

    function formatBytes(bytes) {
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
      return (bytes / 1048576).toFixed(1) + ' MB';
    }

    render();
  }

  /* ---- Init ---- */
  document.addEventListener('DOMContentLoaded', function () {
    initState();
    initSidebar();
    initOverview();
    initPosts();
    initPostEditor();
    initCategories();
    initMedia();
  });
})();

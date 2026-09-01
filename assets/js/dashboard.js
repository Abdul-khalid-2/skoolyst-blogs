/* ============================================================
   dashboard.js — dashboard behaviour
   blog.skoolyst.com

   Section 11 rewire: every page now requires a real logged-in
   session (requireDashboardAuth() in api.js redirects to
   login.html if there isn't one) and reads/writes through the
   real /api/v1 backend instead of MOCK_* arrays + localStorage.

   Role awareness: an admin gets the /admin/* endpoints (any post,
   full category/media CRUD). A non-admin author only gets their
   own posts via /author/posts — Categories and Media are
   admin-only on the backend, so those two pages show a plain
   "admin access required" state for an author instead of erroring.
   ============================================================ */

(function () {
  'use strict';

  var currentUser = null; /* {id, name, email, role} once requireDashboardAuth() resolves */

  function isAdmin() { return !!currentUser && currentUser.role === 'admin'; }
  function postsBase() { return isAdmin() ? '/admin/posts' : '/author/posts'; }

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

  function stateBlock(icon, title, message) {
    return '<div class="empty-state"><div class="empty-icon">' + icon + '</div><h3>' + escapeHtml(title) + '</h3><p>' + escapeHtml(message) + '</p></div>';
  }

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

  /* ---- Topbar user info + logout (every page) ---- */
  function initTopbar() {
    var nameEl = document.getElementById('topbar-user-name');
    var roleEl = document.getElementById('topbar-user-role');
    if (nameEl) nameEl.textContent = currentUser.name;
    if (roleEl) roleEl.textContent = currentUser.role === 'admin' ? 'Admin' : 'Author';

    var logoutBtn = document.getElementById('dash-logout-btn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function () { window.dashLogout(); });
    }
  }

  /* ---- Dashboard overview ---- */
  function initOverview() {
    var statsEl = document.getElementById('dash-stats');
    if (!statsEl) return;

    Api.get(postsBase() + '?per_page=50').then(function (res) {
      var posts = res.data || [];
      var total = (res.meta && res.meta.total) || posts.length;
      var published = posts.filter(function (p) { return p.status === 'published'; }).length;
      var drafts = posts.filter(function (p) { return p.status === 'draft'; }).length;
      var views = posts.reduce(function (s, p) { return s + p.views; }, 0);

      statsEl.innerHTML =
        Card.stat('blue', '\u{1F4DD}', 'Total Posts', total, null, false) +
        Card.stat('green', '\u2705', 'Published', published, null, false) +
        Card.stat('amber', '\u270f\uFE0F', 'Drafts', drafts, null, false) +
        Card.stat('purple', '\u{1F441}', 'Total Views', views.toLocaleString(), null, false);

      /* No monthly-breakdown endpoint exists on the backend yet (posts only
         carry a running view total, not per-month figures) — show a plain
         note here instead of fabricating a chart from nothing. */
      var chartEl = document.getElementById('views-chart');
      if (chartEl) {
        chartEl.innerHTML = '<p style="color:var(--text-muted);padding:1rem 0">Monthly view trends aren\u2019t tracked by the API yet.</p>';
      }

      var recentEl = document.getElementById('recent-posts');
      if (recentEl) {
        var recent = posts.slice().sort(function (a, b) {
          return new Date(b.published_date || 0) - new Date(a.published_date || 0);
        }).slice(0, 5);
        if (recent.length === 0) {
          recentEl.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted)">No posts yet.</td></tr>';
        } else {
          recentEl.innerHTML = recent.map(function (p) {
            var cat = p.category || {};
            return '<tr>' +
              '<td><a href="post-editor.html?edit=' + p.id + '" class="table-title">' + escapeHtml(p.title) + '</a></td>' +
              '<td>' + escapeHtml(cat.name || '\u2014') + '</td>' +
              '<td>' + Badge.status(p.status) + '</td>' +
              '<td>' + p.views.toLocaleString() + '</td>' +
              '<td>' + (p.published_date ? escapeHtml(formatDate(p.published_date)) : '\u2014') + '</td>' +
            '</tr>';
          }).join('');
        }
      }
    }).catch(function (err) {
      statsEl.innerHTML = stateBlock('\u26a0', 'Couldn\u2019t load stats', err.message);
    });
  }

  /* ---- Posts management ---- */
  var postsState = { search: '', status: '', category: '' };

  function initPosts() {
    var tableBody = document.getElementById('posts-table-body');
    if (!tableBody) return;

    var searchInput = document.getElementById('posts-search');
    var statusSelect = document.getElementById('posts-status');
    var catSelect = document.getElementById('posts-category');

    /* Category filter options, from the real API (values are category ids,
       matching post.category.id — filtering itself stays client-side since
       neither /admin/posts nor /author/posts support a category param). */
    if (catSelect) {
      Api.get('/categories').then(function (res) {
        (res.data || []).forEach(function (c) {
          var opt = document.createElement('option');
          opt.value = c.id;
          opt.textContent = c.name;
          catSelect.appendChild(opt);
        });
      }).catch(function () { /* filter just stays at "All categories" */ });
    }

    function load() {
      var params = new URLSearchParams({ per_page: 50 });
      if (isAdmin() && postsState.status) params.set('status', postsState.status);

      Api.get(postsBase() + '?' + params.toString()).then(function (res) {
        var posts = res.data || [];

        if (!isAdmin() && postsState.status) {
          posts = posts.filter(function (p) { return p.status === postsState.status; });
        }
        if (postsState.search) {
          var q = postsState.search.toLowerCase();
          posts = posts.filter(function (p) { return p.title.toLowerCase().indexOf(q) > -1; });
        }
        if (postsState.category) {
          posts = posts.filter(function (p) { return p.category_id === Number(postsState.category); });
        }

        render(posts);
      }).catch(function (err) {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--error)">' + escapeHtml(err.message) + '</td></tr>';
      });
    }

    function render(posts) {
      if (posts.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">No posts found.</td></tr>';
        return;
      }
      tableBody.innerHTML = posts.map(function (p) {
        var cat = p.category || {};
        return '<tr data-id="' + p.id + '">' +
          '<td>' +
            '<div style="display:flex;gap:.75rem;align-items:center">' +
              '<img src="' + (p.cover_image || Card.DEFAULT_COVER) + '" class="table-thumb" alt="" loading="lazy" />' +
              '<a href="post-editor.html?edit=' + p.id + '" class="table-title">' + escapeHtml(p.title) + '</a>' +
            '</div>' +
          '</td>' +
          '<td>' + escapeHtml(cat.name || '\u2014') + '</td>' +
          '<td>' + Badge.status(p.status) + '</td>' +
          '<td>' + p.views.toLocaleString() + '</td>' +
          '<td>' + (p.published_date ? escapeHtml(formatDate(p.published_date)) : '\u2014') + '</td>' +
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

      tableBody.querySelectorAll('.toggle-pub').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-id');
          var row = posts.find(function (p) { return String(p.id) === id; });
          if (!row) return;
          var newStatus = row.status === 'published' ? 'draft' : 'published';
          Api.patch(postsBase() + '/' + id, {
            title: row.title, body: row.body, excerpt: row.excerpt,
            cover_image: row.cover_image, status: newStatus,
            category_id: row.category_id, seo_title: row.seo_title, seo_description: row.seo_description
          }).then(function () {
            dashToast(newStatus === 'published' ? 'Post published.' : 'Post unpublished.', 'success');
            load();
          }).catch(function (err) { dashToast(err.message || 'Could not update post.', 'error'); });
        });
      });

      tableBody.querySelectorAll('.delete-post').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-id');
          var row = posts.find(function (p) { return String(p.id) === id; });
          if (!row) return;
          Modal.confirm('Delete "' + row.title + '"? This cannot be undone.', function () {
            Api.del(postsBase() + '/' + id).then(function () {
              dashToast('Post deleted.', 'success');
              load();
            }).catch(function (err) { dashToast(err.message || 'Could not delete post.', 'error'); });
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
          load();
        }, 300);
      });
    }
    if (statusSelect) {
      statusSelect.addEventListener('change', function () {
        postsState.status = statusSelect.value;
        load();
      });
    }
    if (catSelect) {
      catSelect.addEventListener('change', function () {
        postsState.category = catSelect.value;
        load();
      });
    }

    load();
  }

  /* ---- Post editor ---- */
  function initPostEditor() {
    var form = document.getElementById('post-editor-form');
    if (!form) return;

    renderPostEditorFields();

    var params = new URLSearchParams(window.location.search);
    var editId = params.get('edit');

    var titleInput = form.querySelector('[name="title"]');
    var slugInput = form.querySelector('[name="slug"]');
    var categorySelect = form.querySelector('[name="category"]');
    var excerptInput = form.querySelector('[name="excerpt"]');
    var bodyInput = form.querySelector('[name="body"]');
    var coverPreview = document.getElementById('cover-preview');
    var coverUrlInput = document.getElementById('cover-url-input');
    var coverFileInput = document.getElementById('cover-input');
    var statusRadios = form.querySelectorAll('[name="status"]');
    var seoTitleInput = form.querySelector('[name="seo-title"]');
    var seoDescInput = form.querySelector('[name="seo-desc"]');
    var submitBtn = form.querySelector('button[type="submit"]');

    /* The backend doesn't store tags at all (Post::toArray() has no tags
       field) — hide the field rather than let it silently do nothing. */
    var tagsCard = document.getElementById('editor-tags-field');
    if (tagsCard && tagsCard.closest('.sidebar-card')) {
      tagsCard.closest('.sidebar-card').style.display = 'none';
    }

    /* Populate categories */
    var categoriesLoaded = Api.get('/categories').then(function (res) {
      if (categorySelect) {
        categorySelect.innerHTML = '<option value="">Select category\u2026</option>' +
          (res.data || []).map(function (c) {
            return '<option value="' + c.id + '">' + escapeHtml(c.name) + '</option>';
          }).join('');
      }
    }).catch(function () { /* select stays empty */ });

    var editing = null;

    function prefill(post) {
      editing = post;
      if (titleInput) titleInput.value = post.title;
      if (slugInput) slugInput.value = post.slug;
      if (categorySelect) categoriesLoaded.then(function () { categorySelect.value = post.category_id || ''; });
      if (excerptInput) excerptInput.value = post.excerpt || '';
      if (bodyInput) bodyInput.value = post.body;
      if (post.cover_image) {
        if (coverPreview) coverPreview.innerHTML = '<img src="' + post.cover_image + '" alt="Cover preview" />';
        if (coverUrlInput) coverUrlInput.value = post.cover_image;
      }
      if (seoTitleInput) seoTitleInput.value = post.seo_title || '';
      if (seoDescInput) seoDescInput.value = post.seo_description || '';
      statusRadios.forEach(function (r) { r.checked = r.value === post.status; });
      var titleEl = document.getElementById('editor-page-title');
      if (titleEl) titleEl.textContent = 'Edit Post';
    }

    if (editId) {
      var loadEditing = isAdmin()
        ? Api.get('/admin/posts/' + editId).then(function (res) { return res.data; })
        : Api.get('/author/posts?per_page=50').then(function (res) {
            return (res.data || []).find(function (p) { return String(p.id) === editId; }) || null;
          });

      loadEditing.then(function (post) {
        if (!post) {
          dashToast('Post not found.', 'error');
          window.location.href = 'posts.html';
          return;
        }
        prefill(post);
      }).catch(function (err) {
        dashToast(err.message || 'Could not load post.', 'error');
        window.location.href = 'posts.html';
      });
    } else {
      var titleEl2 = document.getElementById('editor-page-title');
      if (titleEl2) titleEl2.textContent = 'New Post';
      /* Slug is generated server-side (PostController never reads a
         client-supplied slug) — the field is read-only, filled in once the
         post is actually saved. */
      if (slugInput) slugInput.placeholder = 'Generated automatically on save';
    }

    /* Cover image preview (both the URL-input and file-upload variants) */
    if (coverUrlInput && coverPreview) {
      coverUrlInput.addEventListener('input', function () {
        var url = coverUrlInput.value.trim();
        coverPreview.innerHTML = url ? '<img src="' + url + '" alt="Cover preview" />' : '';
      });
    }
    if (coverFileInput && coverPreview) {
      coverFileInput.addEventListener('change', function () {
        var file = coverFileInput.files[0];
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

      var errors = [];
      if (!titleInput.value.trim()) errors.push('Title is required.');
      if (!bodyInput.value.trim()) errors.push('Post body is required.');
      if (errors.length) {
        dashToast(errors[0], 'error');
        return;
      }

      var status = 'draft';
      statusRadios.forEach(function (r) { if (r.checked) status = r.value; });

      var payload = {
        title: titleInput.value.trim(),
        body: bodyInput.value.trim(),
        excerpt: excerptInput.value.trim() || null,
        status: status,
        category_id: categorySelect && categorySelect.value ? Number(categorySelect.value) : null,
        seo_title: seoTitleInput.value.trim() || null,
        seo_description: seoDescInput.value.trim() || null
      };
      /* Admin's cover field is a plain URL (adminUpdate/adminStore take
         cover_image as a string) — the author's own file upload happens
         separately below, after the post id is known. */
      if (coverUrlInput) payload.cover_image = coverUrlInput.value.trim() || null;
      /* adminStore requires author_id on every create (there's no
         author-picker in this dashboard, so admin always posts as
         themselves); adminUpdate leaves the existing author alone unless
         author_id is sent, so it's only needed on the create path. */
      if (isAdmin() && !editing) payload.author_id = currentUser.id;

      submitBtn.disabled = true;

      var savePromise = editing
        ? Api.patch(postsBase() + '/' + editing.id, payload)
        : Api.post(postsBase(), payload);

      savePromise.then(function (res) {
        var saved = res.data;
        var pendingFile = coverFileInput && coverFileInput.files && coverFileInput.files[0];
        if (pendingFile) {
          var fd = new FormData();
          fd.append('image', pendingFile);
          return Api.upload('/author/posts/' + saved.id + '/image', fd).catch(function (err) {
            dashToast('Post saved, but the cover image upload failed: ' + err.message, 'error');
          });
        }
      }).then(function () {
        dashToast(editing ? 'Post updated successfully.' : 'Post created successfully.', 'success');
        setTimeout(function () { window.location.href = 'posts.html'; }, 800);
      }).catch(function (err) {
        submitBtn.disabled = false;
        var firstError = err.errors && Object.values(err.errors)[0];
        dashToast((firstError && firstError[0]) || err.message || 'Could not save post.', 'error');
      });
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
     its CSS and save/edit behaviour remain unchanged.

     Cover field varies by role: an admin can post on any author's behalf,
     but /author/posts/{id}/image only accepts uploads for the caller's own
     post — so admin gets a plain cover-image URL field (which adminStore/
     adminUpdate genuinely accept), while an author gets the original
     drag/drop file widget. */
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
          InputField.text('post-slug', { name: 'slug', className: 'readonly-field' }),
          { hint: 'Generated automatically from the title when you save.' }) +
        FormGroup.textarea('post-excerpt', { label: 'Excerpt', name: 'excerpt', rows: 2, placeholder: 'A short summary shown in post cards\u2026' }) +
        FormGroup.textarea('post-body', { label: 'Body', name: 'body', className: 'body-area', placeholder: 'Write your post here. Use ## for subheadings.\n\nParagraphs are separated by blank lines.', required: true, hint: 'Supports ## subheadings and blank-line paragraph breaks. No rich formatting in this demo.' });
      var slugField = content.querySelector('#post-slug');
      if (slugField) slugField.readOnly = true;
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
      if (isAdmin()) {
        cover.innerHTML = FormGroup.text('cover-url-input', { label: 'Cover Image URL', labelClass: 'visually-hidden', placeholder: 'https:// or /public/uploads/media/\u2026', style: 'margin:0' });
      } else {
        cover.innerHTML = '<div class="cover-upload" id="cover-upload"><div class="upload-icon" aria-hidden="true">\u{1F4F7}</div>' +
          '<div class="upload-text">Click to upload or drag a file</div>' + InputField.file('cover-input', { accept: 'image/*', style: 'display:none' }) +
          '</div>';
        var uploadArea = cover.querySelector('#cover-upload');
        var fileInput = cover.querySelector('#cover-input');
        if (uploadArea && fileInput) {
          uploadArea.addEventListener('click', function () { fileInput.click(); });
        }
      }
    }
  }

  /* ---- Categories management (admin only on the backend) ---- */
  function initCategories() {
    var container = document.getElementById('categories-list');
    if (!container) return;

    if (!isAdmin()) {
      container.innerHTML = stateBlock('\u{1F512}', 'Admin access required', 'Category management is only available to admin accounts.');
      var addBtn0 = document.getElementById('cat-add-btn');
      if (addBtn0) addBtn0.style.display = 'none';
      return;
    }

    var modal = document.getElementById('cat-modal');
    var modalTitle = document.getElementById('cat-modal-title');
    var modalBody = document.getElementById('cat-modal-body');

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

    function load() {
      Api.get('/categories').then(function (res) { render(res.data || []); })
        .catch(function (err) { container.innerHTML = stateBlock('\u26a0', 'Couldn\u2019t load categories', err.message); });
    }

    function render(categories) {
      if (categories.length === 0) {
        container.innerHTML = stateBlock('\u{1F3F7}', 'No categories yet', 'Add one to get started.');
        return;
      }
      container.innerHTML = categories.map(function (c) {
        return '<div class="cat-row" data-id="' + c.id + '">' +
          '<span class="cat-color" style="background:' + c.color + '"></span>' +
          '<div class="cat-info">' +
            '<p class="cat-name">' + escapeHtml(c.name) + '</p>' +
            '<p class="cat-desc">' + escapeHtml(c.description || '') + '</p>' +
          '</div>' +
          '<span class="cat-count">' + c.post_count + ' posts</span>' +
          Table.actions([
            Button.action('\u270f\uFE0F', { extraClass: 'edit-cat', dataId: c.id, title: 'Edit' }),
            Button.action('\u{1F5D1}', { extraClass: 'delete-cat', dataId: c.id, danger: true, title: 'Delete' })
          ]) +
        '</div>';
      }).join('');

      container.querySelectorAll('.edit-cat').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-id');
          var cat = categories.find(function (c) { return String(c.id) === id; });
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
          var cat = categories.find(function (c) { return String(c.id) === id; });
          if (!cat) return;
          Modal.confirm('Delete "' + cat.name + '"?', function () {
            Api.del('/admin/categories/' + id).then(function () {
              dashToast('Category deleted.', 'success');
              load();
            }).catch(function (err) { dashToast(err.message || 'Could not delete category.', 'error'); });
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
        var payload = {
          name: nameInput.value.trim(),
          description: descInput.value.trim() || null,
          color: colorInput.value
        };
        var request = editingId
          ? Api.patch('/admin/categories/' + editingId, payload)
          : Api.post('/admin/categories', payload);

        request.then(function () {
          dashToast(editingId ? 'Category updated.' : 'Category added.', 'success');
          closeModal();
          load();
        }).catch(function (err) {
          var firstError = err.errors && Object.values(err.errors)[0];
          dashToast((firstError && firstError[0]) || err.message || 'Could not save category.', 'error');
        });
      });
    }

    if (modal) {
      modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
      });
    }

    load();
  }

  /* ---- Media library (admin only on the backend) ---- */
  function initMedia() {
    var grid = document.getElementById('media-grid');
    var uploadArea = document.getElementById('media-upload');
    var fileInput = document.getElementById('media-file-input');
    if (!grid) return;

    if (!isAdmin()) {
      grid.innerHTML = stateBlock('\u{1F512}', 'Admin access required', 'The media library is only available to admin accounts.');
      if (uploadArea) uploadArea.style.display = 'none';
      return;
    }

    function load() {
      Api.get('/admin/media?per_page=50').then(function (res) { render(res.data || []); })
        .catch(function (err) { grid.innerHTML = stateBlock('\u26a0', 'Couldn\u2019t load media', err.message); });
    }

    function render(items) {
      if (items.length === 0) {
        grid.innerHTML = stateBlock('\u{1F4F7}', 'No media yet', 'Upload an image to get started.');
        return;
      }
      grid.innerHTML = items.map(function (m) {
        return '<div class="media-item" data-id="' + m.id + '">' +
          '<div class="media-thumb"><img src="' + m.file_path + '" alt="' + escapeHtml(m.filename) + '" loading="lazy" /></div>' +
          '<div class="media-info">' +
            '<p class="media-name">' + escapeHtml(m.filename) + '</p>' +
            '<div class="media-meta">' +
              '<span>' + escapeHtml(formatDate(m.created_at)) + '</span>' +
              '<button class="media-delete" data-id="' + m.id + '">Delete</button>' +
            '</div>' +
          '</div>' +
        '</div>';
      }).join('');

      grid.querySelectorAll('.media-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-id');
          var item = items.find(function (m) { return String(m.id) === id; });
          if (!item) return;
          Modal.confirm('Delete "' + item.filename + '"?', function () {
            Api.del('/admin/media/' + id).then(function () {
              dashToast('Image deleted.', 'success');
              load();
            }).catch(function (err) { dashToast(err.message || 'Could not delete image.', 'error'); });
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

    /* /admin/media accepts one file per request — upload sequentially so a
       multi-file drop/select still works, same as the old mock version. */
    function handleFiles(files) {
      var list = Array.from(files).filter(function (f) { return f.type.startsWith('image/'); });
      if (list.length === 0) return;

      var uploaded = 0;
      var failed = 0;

      function next(i) {
        if (i >= list.length) {
          if (uploaded) dashToast(uploaded + ' image(s) uploaded.', 'success');
          if (failed) dashToast(failed + ' image(s) failed to upload.', 'error');
          load();
          return;
        }
        var fd = new FormData();
        fd.append('file', list[i]);
        Api.upload('/admin/media', fd).then(function () {
          uploaded++;
          next(i + 1);
        }).catch(function () {
          failed++;
          next(i + 1);
        });
      }
      next(0);
    }

    load();
  }

  /* ---- Init ---- */
  document.addEventListener('DOMContentLoaded', function () {
    requireDashboardAuth().then(function (user) {
      currentUser = user;
      initTopbar();
      initSidebar();
      initOverview();
      initPosts();
      initPostEditor();
      initCategories();
      initMedia();
    }); /* on failure, requireDashboardAuth() has already redirected to login.html */
  });
})();

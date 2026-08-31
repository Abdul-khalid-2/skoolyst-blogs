/* ============================================================
   components.js — shared, reusable markup-building functions
   blog.skoolyst.com

   Mirrors the component pattern used in the sibling
   skoolyst-advertisement app (views/components/*.php), but as
   plain JS functions here since this frontend is static /
   mock-data driven rather than server-rendered.

   Load order: mock-data.js (escapeHtml/formatDate) -> components.js -> app.js / dashboard.js
   ============================================================ */

var Badge = {
  /**
   * Status pill used for post status (draft/published) and
   * comment/other statuses. Previously duplicated verbatim in
   * initOverview()'s recent-posts table AND initPosts()'s table
   * in dashboard.js — now built in exactly one place.
   */
  status: function (status) {
    return '<span class="badge-status ' + status + '"><span class="badge-dot"></span>' + status + '</span>';
  }
};

var Card = {
  /**
   * Dashboard overview stat card (icon + label + value + trend).
   * Same markup dashboard.js's old standalone statCard() produced —
   * moved here so every "card" in the app has one home.
   */
  stat: function (color, icon, label, value, trend, isUp) {
    return '<div class="stat-card">' +
      '<div class="stat-icon ' + color + '">' + icon + '</div>' +
      '<div class="stat-label">' + escapeHtml(label) + '</div>' +
      '<div class="stat-value">' + value + '</div>' +
      (trend ? '<div class="stat-trend ' + (isUp ? 'up' : '') + '">' + trend + ' vs last month</div>' : '') +
    '</div>';
  },

  /**
   * Public blog post card. Returns an element rather than an HTML string so
   * callers can append it directly to a post grid. This lived in app.js
   * originally, even though the same card is used by the home, archive,
   * category, and related-post sections; keeping it here gives every card
   * type one shared home.
   */
  post: function (post) {
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
};

var Button = {
  /**
   * Icon-only row-action button (edit / toggle-publish / delete, etc.).
   * Previously the `class="action-btn ..." data-id="..." title="..." aria-label="..."`
   * shape was hand-copied for every action, in both the posts table AND the
   * categories list, inside dashboard.js. Renders as <a> when opts.href is
   * given (e.g. the "Edit" link to post-editor.html), otherwise <button>.
   */
  action: function (icon, opts) {
    opts = opts || {};
    var cls = 'action-btn' + (opts.danger ? ' danger' : '') + (opts.extraClass ? ' ' + opts.extraClass : '');
    var attrs = ' class="' + cls + '"' +
      (opts.dataId !== undefined ? ' data-id="' + opts.dataId + '"' : '') +
      ' title="' + escapeHtml(opts.title || '') + '"' +
      ' aria-label="' + escapeHtml(opts.ariaLabel || opts.title || '') + '"';
    if (opts.href) {
      return '<a href="' + opts.href + '"' + attrs + '>' + icon + '</a>';
    }
    return '<button type="button"' + attrs + '>' + icon + '</button>';
  }
};

var Table = {
  /**
   * Wraps a set of Button.action(...) strings in the standard
   * `<div class="table-actions">...</div>` container. That wrapper was
   * duplicated verbatim in the posts table row-builder AND the
   * categories row-builder in dashboard.js.
   */
  actions: function (buttonsHtml) {
    return '<div class="table-actions">' + buttonsHtml.join('') + '</div>';
  }
};

var InputField = {
  /** Matches the plain `<input type="text">` markup used in post-editor.html / cat-modal. */
  text: function (id, opts) {
    opts = opts || {};
    return '<input type="text" id="' + id + '"' +
      (opts.name ? ' name="' + opts.name + '"' : '') +
      (opts.placeholder ? ' placeholder="' + escapeHtml(opts.placeholder) + '"' : '') +
      (opts.value ? ' value="' + escapeHtml(opts.value) + '"' : '') + ' />';
  },
  textarea: function (id, opts) {
    opts = opts || {};
    return '<textarea id="' + id + '"' +
      (opts.name ? ' name="' + opts.name + '"' : '') +
      ' rows="' + (opts.rows || 2) + '"' +
      (opts.placeholder ? ' placeholder="' + escapeHtml(opts.placeholder) + '"' : '') + '>' +
      (opts.value ? escapeHtml(opts.value) : '') + '</textarea>';
  },
  color: function (id, opts) {
    opts = opts || {};
    return '<input type="color" id="' + id + '" value="' + (opts.value || '#4361ee') + '" />';
  }
};

var FormGroup = {
  /**
   * label + input/textarea wrapper matching the `.form-group` markup already
   * used (hand-written) in post-editor.html and, until this refactor, in
   * categories.html's add/edit-category modal. Field-level helpers below
   * (text/textarea/color) compose this with InputField so callers don't
   * hand-roll the wrapper each time. Deliberately produces the exact same
   * classes/structure as the old hand-written HTML — this is a markup-source
   * change, not a visual one (there's no `.modal-box .form-group` CSS rule,
   * only `.editor-form .form-group`, so styling is identical either way).
   */
  wrap: function (labelText, forId, inputHtml, opts) {
    opts = opts || {};
    return '<div class="form-group"' + (opts.style ? ' style="' + opts.style + '"' : '') + '>' +
      '<label for="' + forId + '">' + escapeHtml(labelText) + (opts.required ? ' <span class="req">*</span>' : '') + '</label>' +
      inputHtml +
      (opts.hint ? '<div class="form-hint">' + escapeHtml(opts.hint) + '</div>' : '') +
      '</div>';
  },
  text: function (id, opts) {
    opts = opts || {};
    return FormGroup.wrap(opts.label, id, InputField.text(id, opts), opts);
  },
  textarea: function (id, opts) {
    opts = opts || {};
    return FormGroup.wrap(opts.label, id, InputField.textarea(id, opts), opts);
  },
  color: function (id, opts) {
    opts = opts || {};
    return FormGroup.wrap(opts.label, id, InputField.color(id, opts), opts);
  }
};

var Modal = {
  /**
   * Generic `.modal-overlay > .modal-box` shell (header/body/footer).
   * Generalizes the markup that used to exist only once, hand-rolled, as
   * categories.html's `#cat-modal` — so a *new* modal (like the confirm
   * dialog below) doesn't need its own copy of `.modal-overlay`/`.modal-box`.
   */
  wrapper: function (id, titleText, bodyHtml, footerHtml) {
    return '<div class="modal-overlay" id="' + id + '">' +
      '<div class="modal-box">' +
        '<div class="modal-header"><h3>' + escapeHtml(titleText) + '</h3>' +
          '<button type="button" class="modal-close" data-modal-close aria-label="Close">\u2715</button></div>' +
        '<div class="modal-body">' + bodyHtml + '</div>' +
        '<div class="modal-footer">' + footerHtml + '</div>' +
      '</div>' +
    '</div>';
  },

  /**
   * Custom confirm dialog built on Modal.wrapper(), replacing the browser's
   * native `confirm()`. Previously each delete flow (posts, categories,
   * media) called `window.confirm(...)` independently with its own message
   * string; this gives them one shared, styled component instead. Injects
   * itself into <body>, wires Cancel/close/backdrop-click/X, and removes
   * itself from the DOM after any close.
   */
  confirm: function (message, onConfirm) {
    var existing = document.getElementById('confirm-modal');
    if (existing) existing.remove();
    var html = Modal.wrapper(
      'confirm-modal',
      'Please Confirm',
      '<p>' + escapeHtml(message) + '</p>',
      '<button type="button" data-modal-close>Cancel</button>' +
      '<button type="button" class="btn-primary-dash" id="confirm-modal-ok">Confirm</button>'
    );
    document.body.insertAdjacentHTML('beforeend', html);
    var modal = document.getElementById('confirm-modal');

    function close() { modal.remove(); }

    modal.querySelectorAll('[data-modal-close]').forEach(function (btn) {
      btn.addEventListener('click', close);
    });
    modal.addEventListener('click', function (e) {
      if (e.target === modal) close();
    });
    document.getElementById('confirm-modal-ok').addEventListener('click', function () {
      close();
      onConfirm();
    });

    /* class added on the next frame so the CSS animation (modalIn) plays,
       same technique the existing cat-modal show/hide already relies on */
    requestAnimationFrame(function () { modal.classList.add('show'); });
  }
};

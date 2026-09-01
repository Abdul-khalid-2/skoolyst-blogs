/* ============================================================
   api.js — shared fetch wrapper for the /api/v1 backend
   blog.skoolyst.com

   Every response from the backend is the uniform envelope from
   core/Response.php: { success, data?, meta? } or
   { success:false, error, errors? }. This wrapper unwraps that,
   resolving with the parsed body on success (2xx) and rejecting
   with an Error (err.status, err.errors) otherwise — callers
   don't need to touch res.ok / res.json() themselves.

   Load order: mock-data.js -> api.js -> components.js -> app.js / dashboard.js
   ============================================================ */

(function () {
  'use strict';

  var API_BASE = '/api/v1';

  function request(path, opts) {
    opts = opts || {};
    var fetchOpts = {
      method: opts.method || 'GET',
      credentials: 'same-origin',
      headers: {}
    };

    if (opts.body instanceof FormData) {
      /* Let the browser set Content-Type (with the multipart boundary). */
      fetchOpts.body = opts.body;
    } else if (opts.body !== undefined) {
      fetchOpts.headers['Content-Type'] = 'application/json';
      fetchOpts.body = JSON.stringify(opts.body);
    }

    return fetch(API_BASE + path, fetchOpts).then(
      function (res) {
        return res.json().catch(function () { return {}; }).then(function (json) {
          if (!res.ok) {
            var err = new Error((json && json.error) || ('Request failed (' + res.status + ').'));
            err.status = res.status;
            err.errors = (json && json.errors) || null;
            throw err;
          }
          return json;
        });
      },
      function () {
        var err = new Error('Network error \u2014 please check your connection and try again.');
        err.status = 0;
        throw err;
      }
    );
  }

  window.Api = {
    get: function (path) { return request(path); },
    post: function (path, body) { return request(path, { method: 'POST', body: body }); },
    patch: function (path, body) { return request(path, { method: 'PATCH', body: body }); },
    del: function (path) { return request(path, { method: 'DELETE' }); },
    /** method defaults to POST; pass 'PATCH' for the media alt-text-only update, which takes a plain object instead. */
    upload: function (path, formData, method) { return request(path, { method: method || 'POST', body: formData }); }
  };

  /**
   * Dashboard pages call this first. Resolves with the current user's
   * {id, name, email, role} on success; on a 401 (or any failure) it
   * redirects to login.html?redirect=<this page> and rejects, so the
   * caller's .then() never runs and nothing partial gets rendered.
   */
  window.requireDashboardAuth = function () {
    return Api.get('/auth/me').then(
      function (res) { return res.data; },
      function (err) {
        var here = window.location.pathname.split('/').pop() || 'index.html';
        if (here !== 'login.html') {
          window.location.href = 'login.html?redirect=' + encodeURIComponent(here);
        }
        return Promise.reject(err);
      }
    );
  };

  window.dashLogout = function () {
    Api.post('/auth/logout').then(finish, finish);
    function finish() { window.location.href = 'login.html'; }
  };
})();

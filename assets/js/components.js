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
  }
};

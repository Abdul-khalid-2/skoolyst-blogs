/* ============================================================
   utils.js — shared display helpers used across every page
   blog.skoolyst.com

   Extracted out of mock-data.js (Section 12) — formatDate/escapeHtml
   were never mock content, they're genuinely shared utilities that
   app.js, components.js, dashboard.js, and inline page scripts all
   call, so they get their own small file instead of disappearing
   along with the mock data.

   Load order: utils.js -> api.js -> components.js -> app.js / dashboard.js
   ============================================================ */

function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function escapeHtml(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/* Expose globally for non-module scripts */
window.formatDate = formatDate;
window.escapeHtml = escapeHtml;

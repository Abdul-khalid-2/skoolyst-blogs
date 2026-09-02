<?php
/**
 * Shared dashboard header partial.
 * Section 13 proof of concept — covers everything from <!doctype> through
 * the topbar and the opening <main class="dash-content"> tag. The page
 * itself fills in <main>'s content, then requires footer.php.
 *
 * Expected variables (set by the including page before requiring this):
 *   $pageTitle        string  <title> text (required)
 *   $activeSidebar    string  passed through to sidebar.php
 *   $topbarTitle      string  the <h1> in the topbar
 *   $topbarSubtitle   string  the <p> under the topbar title
 *   $topbarActionHtml string  raw HTML for the one action link/button
 *                             that sits before the user/logout block
 *                             (e.g. "+ New Post" or "← Back to Posts") —
 *                             not escaped, since it's markup, not text
 */
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/dashboard.css" />
</head>
<body>
  <div class="dash-layout">
    <div class="sidebar-backdrop"></div>
<?php require __DIR__ . '/sidebar.php'; ?>

    <div class="dash-main">
      <header class="dash-topbar">
        <div style="display:flex;align-items:center;gap:.75rem">
          <button class="dash-sidebar-toggle" aria-label="Toggle sidebar">☰</button>
          <div class="topbar-title">
            <h1><?= $topbarTitle ?></h1>
            <p><?= $topbarSubtitle ?></p>
          </div>
        </div>
        <div class="topbar-actions">
          <?= $topbarActionHtml ?>

          <div class="topbar-user">
            <img src="https://i.pravatar.cc/150?img=47" alt="" />
            <div>
              <div class="user-name" id="topbar-user-name">…</div>
              <div class="user-role" id="topbar-user-role"></div>
            </div>
          </div>
          <button type="button" class="btn-secondary-dash" id="dash-logout-btn" style="padding:.5rem .9rem">Log Out</button>
        </div>
      </header>

      <main class="dash-content">

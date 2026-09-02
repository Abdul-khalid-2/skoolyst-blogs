<?php
/**
 * Shared public-site header partial.
 * Section 13 proof of concept — extracted verbatim from the identical
 * <head>/<nav> markup that index.html, blog.html, about.html,
 * category.html, post.html, and contact.html each carried their own
 * copy of.
 *
 * Expected variables (set by the including page before requiring this):
 *   $pageTitle       string  <title> text (required)
 *   $pageDescription string  meta description (required)
 *   $activeNav       string  one of: 'home','blog','about','contact', or '' for none
 *   $extraHead       string  optional extra <head> markup (e.g. index.html's
 *                            og:image/twitter meta tags) — raw HTML, not escaped
 *
 * Known pre-existing issue (not introduced by this conversion, left as-is):
 * the original pages contain literal "\u2630" (hamburger icon) and
 * "\u2014"/"\u2019"/"\u00A9" (em dash / right quote / copyright) text
 * instead of real Unicode characters or HTML entities — carried over
 * verbatim here rather than silently "fixed" mid-refactor.
 */
$activeNav = $activeNav ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= $pageDescription ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
<?php if (!empty($extraHead)): ?>
  <?= $extraHead ?>
<?php endif; ?>
</head>
<body>
  <nav class="site-nav" aria-label="Main navigation">
    <div class="nav-inner">
      <a href="index.html" class="brand">
        <span class="brand-dot"></span>
        Skoolyst<span style="font-weight:400;color:var(--text-muted)"> Blog</span>
      </a>
      <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
        <span aria-hidden="true">\u2630</span>
      </button>
      <ul class="nav-links">
        <li><a href="index.html"<?= $activeNav === 'home' ? ' class="active"' : '' ?>>Home</a></li>
        <li><a href="blog.html"<?= $activeNav === 'blog' ? ' class="active"' : '' ?>>Blog</a></li>
        <li><a href="about.html"<?= $activeNav === 'about' ? ' class="active"' : '' ?>>About</a></li>
        <li><a href="contact.html"<?= $activeNav === 'contact' ? ' class="active"' : '' ?>>Contact</a></li>
        <li><a href="dashboard/index.html" class="nav-cta">Dashboard</a></li>
      </ul>
    </div>
  </nav>

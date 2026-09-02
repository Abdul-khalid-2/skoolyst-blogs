<?php
$pageTitle = 'Skoolyst Blog \u2014 Ideas for Modern Educators';
$pageDescription = 'The Skoolyst blog covers teaching strategies, edtech, student success, online learning, and education policy for modern educators.';
$activeNav = 'home';
$extraHead = "<meta property=\"og:image\" content=\"https://bolt.new/static/og_default.png\">\n"
  . "    <meta name=\"twitter:card\" content=\"summary_large_image\">\n"
  . "    <meta name=\"twitter:image\" content=\"https://bolt.new/static/og_default.png\">\n";
require __DIR__ . '/partials/header.php';
?>

  <header class="hero">
    <span class="hero-eyebrow">Skoolyst Blog</span>
    <h1>Ideas, strategies, and stories for modern educators</h1>
    <p class="hero-sub">Research-backed insights on teaching, edtech, student success, and the future of learning.</p>
    <form class="hero-search" role="search">
      <label for="hero-search-input" class="visually-hidden">Search posts</label>
      <input type="search" id="hero-search-input" placeholder="Search articles\u2026" />
      <button type="submit">Search</button>
    </form>
  </header>

  <section class="section">
    <div class="section-header">
      <h2>Featured Post</h2>
    </div>
    <div id="featured-posts" class="post-grid"></div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2>Latest Articles</h2>
      <a href="blog.html">View all \u2192</a>
    </div>
    <div id="latest-posts" class="post-grid"></div>
  </section>

  <section class="newsletter">
    <h2>Never miss an article</h2>
    <p>Get the latest on teaching, edtech, and student success delivered to your inbox.</p>
    <form>
      <label for="newsletter-email" class="visually-hidden">Email address</label>
      <input type="email" id="newsletter-email" placeholder="you@example.com" required />
      <button type="submit">Subscribe</button>
    </form>
  </section>

<?php require __DIR__ . '/partials/footer.php'; ?>

  <script src="assets/js/utils.js"></script>
  <script src="assets/js/api.js"></script>
  <script src="assets/js/components.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>

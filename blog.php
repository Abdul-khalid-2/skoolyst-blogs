<?php
$pageTitle = 'All Articles \u2014 Skoolyst Blog';
$pageDescription = 'Browse all articles on the Skoolyst blog. Filter by category, search by keyword, and sort by date or popularity.';
$activeNav = 'blog';
require __DIR__ . '/partials/header.php';
?>

  <header class="archive-header">
    <h1>All Articles</h1>
    <p>Explore every post on the Skoolyst blog</p>
  </header>

  <div class="filter-bar">
    <div class="search-box">
      <span class="search-icon" aria-hidden="true">\u{1F50D}</span>
      <label for="blog-search" class="visually-hidden">Search posts</label>
      <input type="search" id="blog-search" placeholder="Search by title\u2026" />
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
      <label for="blog-category" class="visually-hidden">Filter by category</label>
      <select id="blog-category">
        <option value="">All categories</option>
      </select>
      <label for="blog-sort" class="visually-hidden">Sort posts</label>
      <select id="blog-sort">
        <option value="newest">Newest first</option>
        <option value="oldest">Oldest first</option>
        <option value="views">Most viewed</option>
      </select>
    </div>
  </div>

  <section class="section">
    <div id="blog-posts" class="post-grid"></div>
    <div id="blog-pagination" class="pagination"></div>
  </section>

<?php require __DIR__ . '/partials/footer.php'; ?>

  <script src="assets/js/utils.js"></script>
  <script src="assets/js/api.js"></script>
  <script src="assets/js/components.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>

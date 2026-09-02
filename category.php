<?php
$pageTitle = 'Category \u2014 Skoolyst Blog';
$pageDescription = 'Browse posts by category on the Skoolyst blog.';
$activeNav = '';
require __DIR__ . '/partials/header.php';
?>

  <header class="archive-header">
    <h1 id="category-title">Category</h1>
    <p id="category-desc"></p>
  </header>

  <section class="section">
    <div id="category-posts" class="post-grid"></div>
  </section>

<?php require __DIR__ . '/partials/footer.php'; ?>

  <script src="assets/js/utils.js"></script>
  <script src="assets/js/api.js"></script>
  <script src="assets/js/components.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>

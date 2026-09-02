<?php
$pageTitle = 'Posts — Skoolyst Blog Dashboard';
$activeSidebar = 'posts';
$topbarTitle = 'All Posts';
$topbarSubtitle = 'Manage your blog posts';
$topbarActionHtml = '<a href="post-editor.html" class="btn-primary-dash">+ New Post</a>';
require __DIR__ . '/partials/header.php';
?>
        <div class="dash-filter-bar">
          <div class="filter-search">
            <span class="search-icon" aria-hidden="true">🔍</span>
            <label for="posts-search" class="visually-hidden">Search posts</label>
            <input type="search" id="posts-search" placeholder="Search by title…" />
          </div>
          <label for="posts-status" class="visually-hidden">Filter by status</label>
          <select id="posts-status">
            <option value="">All statuses</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
          </select>
          <label for="posts-category" class="visually-hidden">Filter by category</label>
          <select id="posts-category">
            <option value="">All categories</option>
          </select>
        </div>

        <div class="dash-card">
          <div class="dash-table-wrap">
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Status</th>
                  <th>Views</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="posts-table-body"></tbody>
            </table>
          </div>
        </div>
<?php require __DIR__ . '/partials/footer.php'; ?>

  <script src="../assets/js/utils.js"></script>
  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/components.js"></script>
  <script src="../assets/js/dashboard.js"></script>
</body>
</html>

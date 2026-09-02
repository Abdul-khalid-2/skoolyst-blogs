<?php
$pageTitle = 'Dashboard — Skoolyst Blog';
$activeSidebar = 'overview';
$topbarTitle = 'Overview';
$topbarSubtitle = 'Welcome back, Sarah';
$topbarActionHtml = '<a href="post-editor.html" class="btn-primary-dash">+ New Post</a>';
require __DIR__ . '/partials/header.php';
?>
        <div class="stat-grid" id="dash-stats"></div>

        <div class="dash-card">
          <div class="card-header">
            <h3>Monthly Views</h3>
            <span style="font-size:.85rem;color:var(--text-muted)">Last 8 months</span>
          </div>
          <div class="card-body">
            <div class="bar-chart" id="views-chart"></div>
          </div>
        </div>

        <div class="dash-card">
          <div class="card-header">
            <h3>Recent Posts</h3>
            <a href="posts.html" style="font-size:.85rem;font-weight:600">View all →</a>
          </div>
          <div class="dash-table-wrap">
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Status</th>
                  <th>Views</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody id="recent-posts"></tbody>
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

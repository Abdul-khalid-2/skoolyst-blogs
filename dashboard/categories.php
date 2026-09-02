<?php
$pageTitle = 'Categories — Skoolyst Blog Dashboard';
$activeSidebar = 'categories';
$topbarTitle = 'Categories';
$topbarSubtitle = 'Organize your blog content';
$topbarActionHtml = '<a href="post-editor.html" class="btn-primary-dash">+ New Post</a>';
require __DIR__ . '/partials/header.php';
?>
        <div class="dash-card">
          <div class="card-header">
            <h3>All Categories</h3>
            <button type="button" id="cat-add-btn" class="btn-primary-dash">+ Add Category</button>
          </div>
          <div class="card-body" style="padding:0">
            <div id="categories-list"></div>
          </div>
        </div>
<?php require __DIR__ . '/partials/footer.php'; ?>

  <div class="modal-overlay" id="cat-modal">
    <div class="modal-box">
      <div class="modal-header">
        <h3 id="cat-modal-title">Add Category</h3>
        <button type="button" class="modal-close" id="cat-modal-close" aria-label="Close">✕</button>
      </div>
      <div class="modal-body" id="cat-modal-body"></div>
      <div class="modal-footer">
        <button type="button" id="cat-cancel">Cancel</button>
        <button type="button" id="cat-save" class="btn-primary-dash">Save</button>
      </div>
    </div>
  </div>

  <script src="../assets/js/utils.js"></script>
  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/components.js"></script>
  <script src="../assets/js/dashboard.js"></script>
</body>
</html>

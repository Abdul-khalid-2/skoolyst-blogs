<?php
$pageTitle = 'Post Editor — Skoolyst Blog Dashboard';
$activeSidebar = 'post-editor';
$topbarTitle = 'New Post';
$topbarTitleId = 'editor-page-title';
$topbarSubtitle = 'Create or edit a blog post';
$topbarActionHtml = '<a href="posts.html" class="btn-secondary-dash">← Back to Posts</a>';
require __DIR__ . '/partials/header.php';
?>
        <form class="editor-form" id="post-editor-form">
          <div class="editor-grid">
            <div>
              <div class="dash-card">
                <div class="card-header"><h3>Content</h3></div>
                <div class="card-body" id="editor-content-fields"></div>
              </div>

              <div class="dash-card">
                <div class="card-header"><h3>SEO</h3></div>
                <div class="card-body" id="editor-seo-fields"></div>
              </div>
            </div>

            <div class="editor-sidebar">
              <div class="sidebar-card">
                <h4>Publish</h4>
                <div id="editor-status-field"></div>
              </div>

              <div class="sidebar-card">
                <h4>Category</h4>
                <div id="editor-category-field"></div>
              </div>

              <div class="sidebar-card">
                <h4>Tags</h4>
                <div id="editor-tags-field"></div>
              </div>

              <div class="sidebar-card">
                <h4>Cover Image</h4>
                <div id="editor-cover-field"></div>
                <div class="cover-preview" id="cover-preview"></div>
              </div>
            </div>
          </div>

          <div class="editor-actions">
            <button type="submit" class="btn-primary-dash">Save Post</button>
            <button type="button" class="btn-secondary-dash" id="editor-cancel">Cancel</button>
          </div>
        </form>
<?php require __DIR__ . '/partials/footer.php'; ?>

  <script src="../assets/js/utils.js"></script>
  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/components.js"></script>
  <script src="../assets/js/dashboard.js"></script>
  <script>
    /* Wire cover upload click */
    document.addEventListener('DOMContentLoaded', function () {
      var uploadArea = document.getElementById('cover-upload');
      var fileInput = document.getElementById('cover-input');
      if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', function () { fileInput.click(); });
      }
    });
  </script>
</body>
</html>

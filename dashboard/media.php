<?php
$pageTitle = 'Media — Skoolyst Blog Dashboard';
$activeSidebar = 'media';
$topbarTitle = 'Media Library';
$topbarSubtitle = 'Manage uploaded images';
$topbarActionHtml = '<a href="post-editor.html" class="btn-primary-dash">+ New Post</a>';
require __DIR__ . '/partials/header.php';
?>
        <div class="dash-card">
          <div class="card-body">
            <div class="media-upload-area" id="media-upload">
              <div class="upload-icon" aria-hidden="true">📤</div>
              <p class="upload-text">Click or drag images here to upload</p>
              <p class="upload-hint">PNG, JPG, or GIF</p>
              <label for="media-file-input" class="visually-hidden">Upload images</label>
              <input type="file" id="media-file-input" accept="image/*" multiple hidden />
            </div>
          </div>
        </div>

        <div class="dash-card">
          <div class="card-header">
            <h3>All Media</h3>
          </div>
          <div class="card-body">
            <div id="media-grid" class="media-grid"></div>
          </div>
        </div>
<?php require __DIR__ . '/partials/footer.php'; ?>

  <script src="../assets/js/utils.js"></script>
  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/components.js"></script>
  <script src="../assets/js/dashboard.js"></script>
</body>
</html>

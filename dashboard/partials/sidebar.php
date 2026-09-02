<?php
/**
 * Shared dashboard sidebar partial.
 * Required by dashboard/partials/header.php — expects $activeSidebar
 * to already be set by the page (one of: 'overview','posts',
 * 'post-editor','categories','media', or '' for none).
 */
$activeSidebar = $activeSidebar ?? '';
?>
    <aside class="dash-sidebar">
      <div class="sidebar-brand">
        <span class="brand-dot"></span>
        Skoolyst
        <span class="brand-sub">Blog</span>
      </div>
      <ul class="sidebar-nav">
        <li class="sidebar-section-label">Main</li>
        <li><a href="index.html"<?= $activeSidebar === 'overview' ? ' class="active"' : '' ?>><span class="nav-icon">🏠</span> Overview</a></li>
        <li><a href="posts.html"<?= $activeSidebar === 'posts' ? ' class="active"' : '' ?>><span class="nav-icon">📝</span> Posts</a></li>
        <li><a href="post-editor.html"<?= $activeSidebar === 'post-editor' ? ' class="active"' : '' ?>><span class="nav-icon">✏️</span> New Post</a></li>
        <li><a href="categories.html"<?= $activeSidebar === 'categories' ? ' class="active"' : '' ?>><span class="nav-icon">🏷</span> Categories</a></li>
        <li><a href="media.html"<?= $activeSidebar === 'media' ? ' class="active"' : '' ?>><span class="nav-icon">📷</span> Media</a></li>
        <li class="sidebar-section-label">System</li>
        <li><a href="#" onclick="return false"><span class="nav-icon">⚙️</span> Settings</a></li>
        <li><a href="../index.html"><span class="nav-icon">🔗</span> View Site</a></li>
      </ul>
      <div class="sidebar-footer">
        © 2026 Skoolyst
      </div>
    </aside>

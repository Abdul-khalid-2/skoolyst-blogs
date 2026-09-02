<?php
$pageTitle = 'Article \u2014 Skoolyst Blog';
$pageDescription = 'Read the full article on the Skoolyst blog.';
$activeNav = '';
require __DIR__ . '/partials/header.php';
?>

  <article id="post-content">
    <header class="post-hero" id="post-hero"></header>
    <div class="post-cover-wrap" id="post-cover-wrap"></div>
    <div class="post-body" id="post-body"></div>
    <div class="post-tags" id="post-tags"></div>
    <div class="author-block" id="author-block"></div>
    <div class="share-row" id="share-row">
      <span class="share-label">Share:</span>
      <button class="share-btn" onclick="sharePost('twitter', window.location.href, document.title)" aria-label="Share on Twitter">X</button>
      <button class="share-btn" onclick="sharePost('facebook', window.location.href, document.title)" aria-label="Share on Facebook">f</button>
      <button class="share-btn" onclick="sharePost('linkedin', window.location.href, document.title)" aria-label="Share on LinkedIn">in</button>
    </div>
    <section class="comments-section" id="comments-section">
      <h3>Comments</h3>
      <div id="comments-list"></div>
      <form class="comment-form" id="comment-form-el">
        <label for="comment-name" class="visually-hidden">Your name</label>
        <input type="text" id="comment-name" name="comment-name" placeholder="Your name" required />
        <label for="comment-email" class="visually-hidden">Your email</label>
        <input type="email" id="comment-email" name="comment-email" placeholder="Your email" required />
        <label for="comment-text" class="visually-hidden">Write a comment</label>
        <textarea id="comment-text" placeholder="Share your thoughts\u2026" required></textarea>
        <button type="submit">Post Comment</button>
        <p class="form-note">Comments are moderated and will appear once approved.</p>
      </form>
    </section>
  </article>

  <section class="related-posts" id="related-posts">
    <h3>Related Articles</h3>
  </section>

<?php require __DIR__ . '/partials/footer.php'; ?>

  <script src="assets/js/utils.js"></script>
  <script src="assets/js/api.js"></script>
  <script src="assets/js/components.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>

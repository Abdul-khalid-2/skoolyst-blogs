<?php
$pageTitle = 'Contact \u2014 Skoolyst Blog';
$pageDescription = 'Get in touch with the Skoolyst blog team.';
$activeNav = 'contact';
require __DIR__ . '/partials/header.php';
?>

  <div class="contact-wrap">
    <div class="contact-info">
      <h2>Get in touch</h2>
      <p>Have a question, a story idea, or feedback? We\u2019d love to hear from you. Fill out the form and we\u2019ll get back to you as soon as we can.</p>
      <div class="info-item">
        <div class="info-icon" aria-hidden="true">\u2709</div>
        <div>
          <div class="info-label">Email</div>
          <div class="info-value">blog@skoolyst.com</div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon" aria-hidden="true">\u{1F4CD}</div>
        <div>
          <div class="info-label">Address</div>
          <div class="info-value">123 Education Way, Suite 200, San Francisco, CA</div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon" aria-hidden="true">\u23F0</div>
        <div>
          <div class="info-label">Response Time</div>
          <div class="info-value">Within 2\u20133 business days</div>
        </div>
      </div>
    </div>

    <form class="contact-form" id="contact-form-el" novalidate>
      <div class="form-group">
        <label for="contact-name">Name <span style="color:var(--error)">*</span></label>
        <input type="text" id="contact-name" name="name" required />
        <div class="form-error" data-error="name"></div>
      </div>
      <div class="form-group">
        <label for="contact-email">Email <span style="color:var(--error)">*</span></label>
        <input type="email" id="contact-email" name="email" required />
        <div class="form-error" data-error="email"></div>
      </div>
      <div class="form-group">
        <label for="contact-subject">Subject</label>
        <input type="text" id="contact-subject" name="subject" />
      </div>
      <div class="form-group">
        <label for="contact-message">Message <span style="color:var(--error)">*</span></label>
        <textarea id="contact-message" name="message" required></textarea>
        <div class="form-error" data-error="message"></div>
      </div>
      <button type="submit">Send Message</button>
      <p class="form-note">This is a demo form \u2014 no message is actually sent.</p>
    </form>
  </div>

<?php require __DIR__ . '/partials/footer.php'; ?>

  <script src="assets/js/utils.js"></script>
  <script src="assets/js/api.js"></script>
  <script src="assets/js/components.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>

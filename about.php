<?php
$pageTitle = 'About \u2014 Skoolyst Blog';
$pageDescription = 'Learn about the Skoolyst blog: our mission, our team, and why we write about education.';
$activeNav = 'about';
require __DIR__ . '/partials/header.php';
?>

  <header class="about-hero">
    <h1>About the Skoolyst Blog</h1>
    <p>We write about what works in education \u2014 and what doesn\u2019t.</p>
  </header>

  <div class="about-content">
    <h2>Our Mission</h2>
    <p>Education is changing faster than ever. New technology, new research, and new expectations are reshaping what happens in classrooms and online. But the conversation is often split between academic research and practical advice \u2014 and educators are left to bridge the gap themselves.</p>
    <p>The Skoolyst Blog exists to bridge that gap. We take what the research says, combine it with what teachers actually experience, and publish articles that are both credible and useful. No jargon, no hype, no listicles with nothing behind them.</p>

    <h2>What We Cover</h2>
    <p>We write across five areas that matter to modern educators:</p>
    <p><strong>Teaching Strategies</strong> \u2014 practical, classroom-tested approaches that improve engagement and learning outcomes.</p>
    <p><strong>EdTech</strong> \u2014 the tools and platforms reshaping how students learn, with honest takes on what\u2019s worth your time.</p>
    <p><strong>Student Success</strong> \u2014 research and strategies for helping every learner thrive, from literacy to mindset.</p>
    <p><strong>Online Learning</strong> \u2014 best practices for virtual and hybrid classrooms, from design to delivery.</p>
    <p><strong>Education Policy</strong> \u2014 funding, standards, and the decisions that shape what\u2019s possible in your school.</p>

    <h2>Who We Are</h2>
    <p>Our writers are educators, researchers, and education journalists. We\u2019ve been teachers, instructional designers, and policy analysts. We bring that range of experience to every article.</p>
  </div>

  <section class="section" style="padding-top:0">
    <div class="section-header">
      <h2>Our Team</h2>
    </div>
    <div class="team-grid" id="team-grid"></div>
  </section>

  <section class="newsletter">
    <h2>Join the conversation</h2>
    <p>Get our latest articles delivered straight to your inbox.</p>
    <form>
      <label for="about-newsletter-email" class="visually-hidden">Email address</label>
      <input type="email" id="about-newsletter-email" placeholder="you@example.com" required />
      <button type="submit">Subscribe</button>
    </form>
  </section>

<?php require __DIR__ . '/partials/footer.php'; ?>

  <script src="assets/js/utils.js"></script>
  <script src="assets/js/api.js"></script>
  <script src="assets/js/components.js"></script>
  <script src="assets/js/app.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var grid = document.getElementById('team-grid');
      /* Display-only labels — not stored in the DB, keyed by name since
         the /authors API has no "role title" concept of its own. */
      var roles = {
        'Sarah Chen': 'Lead Content Strategist',
        'Marcus Johnson': 'EdTech Writer',
        'Priya Patel': 'Online Learning Editor',
        'David Kim': 'Education Policy Writer'
      };
      Api.get('/authors').then(function (res) {
        (res.data || []).forEach(function (a) {
          var card = document.createElement('div');
          card.className = 'team-card';
          card.innerHTML =
            '<img src="' + a.avatar_url + '" alt="' + escapeHtml(a.name) + '" loading="lazy" />' +
            '<p class="team-name">' + escapeHtml(a.name) + '</p>' +
            '<p class="team-role">' + escapeHtml(roles[a.name] || 'Writer') + '</p>' +
            '<p class="team-bio">' + escapeHtml(a.bio || '') + '</p>';
          grid.appendChild(card);
        });
      }).catch(function () {
        grid.innerHTML = '<p class="team-error">Unable to load team members right now.</p>';
      });
    });
  </script>
</body>
</html>

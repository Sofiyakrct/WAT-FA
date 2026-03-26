<?php
require_once 'config/db.php';

// Fetch featured courses (top 6 by rating)
$featuredQuery = "SELECT * FROM courses ORDER BY rating DESC LIMIT 6";
$featuredResult = $conn->query($featuredQuery);
$featuredCourses = $featuredResult ? $featuredResult->fetch_all(MYSQLI_ASSOC) : [];

// Stats
$totalCourses = $conn->query("SELECT COUNT(*) as c FROM courses")->fetch_assoc()['c'];
$totalUsers   = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LearnHub – Online Learning Platform</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ── NAVBAR ── -->
<nav>
  <a class="nav-brand" href="index.php">Learn<span>Hub</span></a>
  <ul class="nav-links">
    <li><a href="index.php" class="active">Home</a></li>
    <li><a href="courses.php">Courses</a></li>
    <?php if ($user): ?>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="logout.php">Log out</a></li>
    <?php else: ?>
      <li><a href="login.php">Login</a></li>
      <li><a href="register.php" class="btn btn-primary" style="padding:.4rem .9rem">Get Started</a></li>
    <?php endif; ?>
  </ul>
</nav>

<!-- ── HERO ── -->
<section class="hero">
  <span class="hero-eyebrow">🎓 Online Learning Platform</span>
  <h1>Learn Without<br>Limits</h1>
  <p>Master in-demand skills with expert-led courses. Join thousands of learners building their future, one lesson at a time.</p>
  <div class="hero-actions">
    <a href="courses.php" class="btn btn-primary btn-lg">Browse Courses</a>
    <?php if (!$user): ?>
      <a href="register.php" class="btn btn-outline btn-lg">Create Free Account</a>
    <?php else: ?>
      <a href="dashboard.php" class="btn btn-outline btn-lg">My Dashboard →</a>
    <?php endif; ?>
  </div>

  <div class="stats-bar">
    <div class="stat-item">
      <div class="stat-num"><?= $totalCourses ?>+</div>
      <div class="stat-label">Expert Courses</div>
    </div>
    <div class="stat-item">
      <div class="stat-num"><?= max($totalUsers, 1) ?>+</div>
      <div class="stat-label">Active Learners</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">4.8★</div>
      <div class="stat-label">Avg. Rating</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">∞</div>
      <div class="stat-label">Lifetime Access</div>
    </div>
  </div>
</section>

<!-- ── FEATURES ── -->
<section class="section" style="background: var(--bg2); border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
  <div class="container">
    <div class="section-header">
      <h2>Why LearnHub?</h2>
      <p>Everything you need to grow your skills and advance your career.</p>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">🎯</div>
        <h3>Expert Instructors</h3>
        <p>Learn from industry professionals with real-world experience in their fields.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">📱</div>
        <h3>Learn Anywhere</h3>
        <p>Access your courses from any device, any time. Download for offline viewing.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🏆</div>
        <h3>Certificates</h3>
        <p>Earn shareable certificates upon course completion to boost your portfolio.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🔄</div>
        <h3>Lifetime Access</h3>
        <p>Pay once, access forever. Course updates included at no extra cost.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">💬</div>
        <h3>Community Q&A</h3>
        <p>Ask questions, get answers. A thriving community of learners has your back.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🚀</div>
        <h3>Project-Based</h3>
        <p>Build real projects that you can show to employers and clients.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── FEATURED COURSES ── -->
<section class="section">
  <div class="container">
    <div class="section-header" style="display:flex;align-items:flex-end;justify-content:space-between;">
      <div>
        <h2>Top-Rated Courses</h2>
        <p>Handpicked by our experts for maximum impact.</p>
      </div>
      <a href="courses.php" class="btn btn-outline">View All →</a>
    </div>
    <div class="courses-grid">
      <?php foreach ($featuredCourses as $course): ?>
        <?php
          $emoji = courseEmoji($course['category']);
          $bg    = courseBg($course['category']);
          $isFree = floatval($course['price']) === 0.0;
        ?>
        <div class="course-card">
          <div class="course-thumbnail" style="background: <?= $bg ?>">
            <?= $emoji ?>
            <span class="course-badge"><?= htmlspecialchars($course['level']) ?></span>
          </div>
          <div class="course-body">
            <div class="course-category"><?= htmlspecialchars($course['category']) ?></div>
            <div class="course-title"><?= htmlspecialchars($course['title']) ?></div>
            <div class="course-desc"><?= htmlspecialchars($course['description']) ?></div>
            <div class="course-meta">
              <span class="meta-item">⏱ <?= htmlspecialchars($course['duration']) ?></span>
              <span class="meta-item">📖 <?= $course['lessons'] ?> lessons</span>
              <span class="meta-item">⭐ <?= $course['rating'] ?></span>
            </div>
            <div class="course-footer">
              <span class="course-price <?= $isFree ? 'free' : '' ?>">
                <?= $isFree ? 'Free' : '$' . number_format($course['price'], 2) ?>
              </span>
              <a href="course-details.php?id=<?= $course['id'] ?>" class="btn btn-primary">View Course</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<?php if (!$user): ?>
<section style="background: var(--bg2); border-top:1px solid var(--border); padding: 4rem 0; text-align:center;">
  <div class="container">
    <h2 style="font-family:'Syne',sans-serif;font-size:2rem;font-weight:700;margin-bottom:.75rem;">Ready to start learning?</h2>
    <p style="color:var(--text2);margin-bottom:2rem;max-width:450px;margin-left:auto;margin-right:auto;">Create your free account and get instant access to free courses today.</p>
    <a href="register.php" class="btn btn-primary btn-lg">Get Started for Free</a>
  </div>
</section>
<?php endif; ?>

<!-- ── FOOTER ── -->
<footer>
  <p>© <?= date('Y') ?> <a href="index.php">LearnHub</a>. Built with PHP &amp; MySQL.</p>
</footer>

</body>
</html>

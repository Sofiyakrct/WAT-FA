<?php
require_once 'config/db.php';

$user = currentUser();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: courses.php');
    exit;
}

// Fetch course
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header('Location: courses.php');
    exit;
}

// Check enrollment
$isEnrolled = false;
if ($user) {
    $es = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
    $es->bind_param('ii', $user['id'], $id);
    $es->execute();
    $isEnrolled = (bool)$es->get_result()->fetch_assoc();
}

$emoji  = courseEmoji($course['category']);
$bg     = courseBg($course['category']);
$isFree = floatval($course['price']) === 0.0;

// Build a sample curriculum (in a real app this would be a lessons table)
$sampleLessons = [
    ['Introduction & Setup', 'Getting started, tools, and environment'],
    ['Core Concepts – Part 1', 'Foundational principles and terminology'],
    ['Core Concepts – Part 2', 'Deep dive into key patterns'],
    ['Hands-on Project #1', 'Build your first real-world example'],
    ['Advanced Techniques', 'Performance, best practices, edge cases'],
    ['Hands-on Project #2', 'Apply advanced techniques in a project'],
    ['Testing & Debugging', 'Write tests and debug like a pro'],
    ['Deployment & Production', 'Ship your project to the world'],
    ['Final Project', 'Capstone project combining everything'],
    ['Wrap-up & Next Steps', 'What to learn next, resources, community'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($course['title']) ?> – LearnHub</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav>
  <a class="nav-brand" href="index.php">Learn<span>Hub</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="courses.php" class="active">Courses</a></li>
    <?php if ($user): ?>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="logout.php">Log out</a></li>
    <?php else: ?>
      <li><a href="login.php">Login</a></li>
      <li><a href="register.php" class="btn btn-primary" style="padding:.4rem .9rem">Get Started</a></li>
    <?php endif; ?>
  </ul>
</nav>

<!-- ── DETAIL HERO ── -->
<div class="course-detail-hero">
  <div class="container">
    <div class="course-detail-layout">
      <div>
        <div style="margin-bottom:.75rem">
          <a href="courses.php" style="color:var(--text3);text-decoration:none;font-size:.85rem">← All Courses</a>
          <span style="color:var(--text3);margin:0 .4rem">/</span>
          <span style="color:var(--accent);font-size:.85rem"><?= htmlspecialchars($course['category']) ?></span>
        </div>

        <h1 style="font-family:'Syne',sans-serif;font-size:2rem;font-weight:700;line-height:1.2;margin-bottom:1rem;">
          <?= htmlspecialchars($course['title']) ?>
        </h1>

        <p style="color:var(--text2);font-size:.95rem;line-height:1.6;max-width:620px;">
          <?= htmlspecialchars($course['description']) ?>
        </p>

        <div class="course-detail-meta">
          <span class="detail-meta-item">⭐ <strong><?= $course['rating'] ?></strong> rating</span>
          <span class="detail-meta-item">👤 <strong><?= htmlspecialchars($course['instructor']) ?></strong></span>
          <span class="detail-meta-item">⏱ <?= htmlspecialchars($course['duration']) ?></span>
          <span class="detail-meta-item">📖 <?= $course['lessons'] ?> lessons</span>
          <span class="detail-meta-item">
            <span class="badge badge-purple"><?= htmlspecialchars($course['level']) ?></span>
          </span>
        </div>

        <?php if ($isEnrolled): ?>
          <div class="alert alert-success" style="max-width:420px">
            ✅ You're enrolled in this course! <a href="dashboard.php" style="color:var(--accent3)">Go to your dashboard →</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- ── ENROLL CARD ── -->
      <div class="enroll-card">
        <div class="enroll-preview" style="background:<?= $bg ?>">
          <?= $emoji ?>
        </div>

        <div class="enroll-price <?= $isFree ? 'free' : '' ?>" style="<?= $isFree ? 'color:var(--accent3)' : '' ?>">
          <?= $isFree ? 'Free' : '$' . number_format($course['price'], 2) ?>
        </div>

        <?php if ($isEnrolled): ?>
          <a href="dashboard.php" class="btn btn-success btn-full btn-lg" style="margin-bottom:.75rem">
            📚 Go to My Dashboard
          </a>
        <?php elseif ($user): ?>
          <form method="POST" action="enroll.php">
            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
            <input type="hidden" name="redirect" value="course-details.php?id=<?= $course['id'] ?>">
            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-bottom:.75rem">
              🎓 Enroll Now
            </button>
          </form>
        <?php else: ?>
          <a href="login.php?redirect=course-details.php%3Fid%3D<?= $course['id'] ?>"
             class="btn btn-primary btn-full btn-lg" style="margin-bottom:.75rem">
            🎓 Enroll Now
          </a>
          <p style="font-size:.8rem;color:var(--text3);text-align:center">
            <a href="register.php" style="color:var(--accent)">Create an account</a> to enroll for free
          </p>
        <?php endif; ?>

        <ul class="enroll-features">
          <li><span class="check">✓</span> <?= htmlspecialchars($course['duration']) ?> of on-demand video</li>
          <li><span class="check">✓</span> <?= $course['lessons'] ?> downloadable lessons</li>
          <li><span class="check">✓</span> Full lifetime access</li>
          <li><span class="check">✓</span> Access on mobile and desktop</li>
          <li><span class="check">✓</span> Certificate of completion</li>
          <?php if ($isFree): ?>
            <li><span class="check">✓</span> Completely free</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- ── CURRICULUM ── -->
<div class="container" style="padding-top:3rem;padding-bottom:4rem;">
  <div style="max-width:700px;">
    <div class="curriculum">
      <h3>Course Curriculum</h3>
      <ul class="lesson-list">
        <?php foreach ($sampleLessons as $idx => $lesson): ?>
          <li class="lesson-item">
            <span class="lesson-num"><?= $idx + 1 ?></span>
            <div>
              <div style="font-weight:500;color:var(--text)"><?= htmlspecialchars($lesson[0]) ?></div>
              <div style="font-size:.8rem;margin-top:.15rem"><?= htmlspecialchars($lesson[1]) ?></div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="divider"></div>

    <!-- About Instructor -->
    <div>
      <h3 style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:700;margin-bottom:1rem;">Your Instructor</h3>
      <div style="display:flex;align-items:center;gap:1rem;margin-bottom:.75rem;">
        <div class="avatar" style="width:52px;height:52px;font-size:1.1rem;flex-shrink:0;">
          <?= strtoupper(substr($course['instructor'], 0, 1)) ?>
        </div>
        <div>
          <div style="font-weight:700;font-size:1rem;"><?= htmlspecialchars($course['instructor']) ?></div>
          <div style="color:var(--text2);font-size:.85rem;"><?= htmlspecialchars($course['category']) ?> Expert</div>
        </div>
      </div>
      <p style="color:var(--text2);font-size:.9rem;line-height:1.6;">
        <?= htmlspecialchars($course['instructor']) ?> is an industry veteran with years of hands-on experience in
        <?= strtolower($course['category']) ?>. They have helped thousands of students achieve their goals through
        practical, project-based teaching.
      </p>
    </div>
  </div>
</div>

<footer>
  <p>© <?= date('Y') ?> <a href="index.php">LearnHub</a>. Built with PHP &amp; MySQL.</p>
</footer>

</body>
</html>

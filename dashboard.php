<?php
require_once 'config/db.php';
requireLogin();

$user = currentUser();

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Enrolled courses with progress
$stmt = $conn->prepare("
    SELECT c.*, e.progress, e.enrolled_at
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$enrolledCourses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Stats
$totalEnrolled  = count($enrolledCourses);
$completedCount = count(array_filter($enrolledCourses, fn($c) => $c['progress'] >= 100));
$avgProgress    = $totalEnrolled > 0
    ? round(array_sum(array_column($enrolledCourses, 'progress')) / $totalEnrolled)
    : 0;

// User initials
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), explode(' ', trim($user['name']))));
$initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – LearnHub</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav>
  <a class="nav-brand" href="index.php">Learn<span>Hub</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="courses.php">Courses</a></li>
    <li><a href="dashboard.php" class="active">Dashboard</a></li>
    <li><a href="logout.php">Log out</a></li>
  </ul>
</nav>

<div class="container">
  <div class="dashboard-layout">

    <!-- ── SIDEBAR ── -->
    <aside class="sidebar">
      <div class="sidebar-user">
        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
        <div class="sidebar-user-info">
          <div class="name"><?= htmlspecialchars($user['name']) ?></div>
          <div class="role">Student</div>
        </div>
      </div>

      <ul class="sidebar-nav">
        <li><a href="dashboard.php" class="active">📊 My Learning</a></li>
        <li><a href="courses.php">📚 Browse Courses</a></li>
        <li><a href="logout.php">🚪 Log Out</a></li>
      </ul>

      <div class="divider"></div>

      <div style="font-size:.8rem;color:var(--text3)">
        <div style="margin-bottom:.4rem">📧 <?= htmlspecialchars($user['email']) ?></div>
        <div>🎓 <?= $totalEnrolled ?> course<?= $totalEnrolled !== 1 ? 's' : '' ?> enrolled</div>
      </div>
    </aside>

    <!-- ── MAIN ── -->
    <main class="main-content">

      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <h1 class="page-title">Welcome back, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>! 👋</h1>
      <p class="page-subtitle">Here's your learning progress at a glance.</p>

      <!-- Stats -->
      <div class="dash-stats">
        <div class="stat-card">
          <div class="stat-icon icon-purple">📚</div>
          <div class="stat-val"><?= $totalEnrolled ?></div>
          <div class="stat-lbl">Courses Enrolled</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-green">🏆</div>
          <div class="stat-val"><?= $completedCount ?></div>
          <div class="stat-lbl">Completed</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-pink">📈</div>
          <div class="stat-val"><?= $avgProgress ?>%</div>
          <div class="stat-lbl">Avg. Progress</div>
        </div>
      </div>

      <!-- Section heading -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <h2 style="font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:700;">My Courses</h2>
        <a href="courses.php" class="btn btn-outline" style="font-size:.8rem">+ Enroll in New Course</a>
      </div>

      <?php if (empty($enrolledCourses)): ?>
        <div class="empty-state">
          <div class="empty-icon">📭</div>
          <h3>No courses yet</h3>
          <p>Browse our catalog and enroll in your first course.</p>
          <a href="courses.php" class="btn btn-primary">Browse Courses</a>
        </div>
      <?php else: ?>
        <div class="enrolled-grid">
          <?php foreach ($enrolledCourses as $course): ?>
            <?php
              $emoji    = courseEmoji($course['category']);
              $bg       = courseBg($course['category']);
              $progress = (int)$course['progress'];
              $isDone   = $progress >= 100;
            ?>
            <div class="enrolled-card">
              <div class="enrolled-thumb" style="background:<?= $bg ?>">
                <?= $emoji ?>
              </div>
              <div class="enrolled-body">
                <div class="enrolled-cat"><?= htmlspecialchars($course['category']) ?></div>
                <div class="enrolled-title"><?= htmlspecialchars($course['title']) ?></div>

                <div class="progress-bar">
                  <div class="progress-fill" style="width:<?= $progress ?>%"></div>
                </div>
                <div class="progress-label">
                  <?php if ($isDone): ?>
                    ✅ Completed
                  <?php else: ?>
                    <?= $progress ?>% complete
                  <?php endif; ?>
                </div>

                <div style="display:flex;gap:.5rem;margin-top:.85rem;">
                  <a href="course-details.php?id=<?= $course['id'] ?>" class="btn btn-outline" style="font-size:.8rem;flex:1;justify-content:center">Details</a>
                  <?php if (!$isDone): ?>
                    <form method="POST" action="update-progress.php" style="flex:1">
                      <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                      <input type="hidden" name="progress"  value="<?= min(100, $progress + 10) ?>">
                      <button type="submit" class="btn btn-primary" style="font-size:.8rem;width:100%;justify-content:center">
                        Continue →
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="btn btn-success" style="font-size:.8rem;flex:1;justify-content:center;cursor:default;">🏆 Done!</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </main>
  </div>
</div>

<footer style="margin-top:3rem;">
  <p>© <?= date('Y') ?> <a href="index.php">LearnHub</a>. Built with PHP &amp; MySQL.</p>
</footer>

</body>
</html>

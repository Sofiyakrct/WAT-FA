<?php
require_once 'config/db.php';

$user = currentUser();

// ── Filters ──────────────────────────────────────────────────────────────────
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$level    = trim($_GET['level']    ?? '');
$sort     = $_GET['sort'] ?? 'rating';

// ── Build query ──────────────────────────────────────────────────────────────
$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = "(title LIKE ? OR description LIKE ? OR instructor LIKE ?)";
    $like     = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'sss';
}
if ($category !== '') {
    $where[]  = "category = ?";
    $params[] = $category;
    $types   .= 's';
}
if ($level !== '') {
    $where[]  = "level = ?";
    $params[] = $level;
    $types   .= 's';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$orderMap = [
    'rating'   => 'rating DESC',
    'price_lo' => 'price ASC',
    'price_hi' => 'price DESC',
    'newest'   => 'created_at DESC',
    'title'    => 'title ASC',
];
$orderSQL = $orderMap[$sort] ?? 'rating DESC';

$sql = "SELECT * FROM courses $whereSQL ORDER BY $orderSQL";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Enrolled course IDs for this user ────────────────────────────────────────
$enrolledIds = [];
if ($user) {
    $er = $conn->prepare("SELECT course_id FROM enrollments WHERE user_id = ?");
    $er->bind_param('i', $user['id']);
    $er->execute();
    $erRes = $er->get_result();
    while ($row = $erRes->fetch_assoc()) {
        $enrolledIds[] = $row['course_id'];
    }
}

// ── Categories for filter ────────────────────────────────────────────────────
$cats = $conn->query("SELECT DISTINCT category FROM courses ORDER BY category")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Courses – LearnHub</title>
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

<div style="background:var(--bg2);border-bottom:1px solid var(--border);padding:2rem 0;">
  <div class="container">
    <h1 style="font-family:'Syne',sans-serif;font-size:2rem;font-weight:700;margin-bottom:.35rem;">All Courses</h1>
    <p style="color:var(--text2);">
      <?= count($courses) ?> course<?= count($courses) !== 1 ? 's' : '' ?> available
      <?= $search ? ' matching "<em>' . htmlspecialchars($search) . '</em>"' : '' ?>
    </p>
  </div>
</div>

<div class="container" style="padding-top:2rem;padding-bottom:4rem;">

  <!-- ── FILTER BAR ── -->
  <form method="GET" class="filter-bar">
    <div class="search-input-wrap">
      <span class="search-icon">🔍</span>
      <input
        type="text"
        name="search"
        placeholder="Search courses, topics, instructors…"
        class="form-control search-input"
        value="<?= htmlspecialchars($search) ?>"
      >
    </div>

    <select name="category" class="select-control" onchange="this.form.submit()">
      <option value="">All Categories</option>
      <?php foreach ($cats as $cat): ?>
        <option value="<?= htmlspecialchars($cat['category']) ?>"
          <?= $category === $cat['category'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['category']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <select name="level" class="select-control" onchange="this.form.submit()">
      <option value="">All Levels</option>
      <option value="Beginner"     <?= $level === 'Beginner'     ? 'selected' : '' ?>>Beginner</option>
      <option value="Intermediate" <?= $level === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
      <option value="Advanced"     <?= $level === 'Advanced'     ? 'selected' : '' ?>>Advanced</option>
    </select>

    <select name="sort" class="select-control" onchange="this.form.submit()">
      <option value="rating"   <?= $sort === 'rating'   ? 'selected' : '' ?>>Top Rated</option>
      <option value="newest"   <?= $sort === 'newest'   ? 'selected' : '' ?>>Newest</option>
      <option value="price_lo" <?= $sort === 'price_lo' ? 'selected' : '' ?>>Price: Low to High</option>
      <option value="price_hi" <?= $sort === 'price_hi' ? 'selected' : '' ?>>Price: High to Low</option>
      <option value="title"    <?= $sort === 'title'    ? 'selected' : '' ?>>A – Z</option>
    </select>

    <button type="submit" class="btn btn-primary">Search</button>
    <?php if ($search || $category || $level): ?>
      <a href="courses.php" class="btn btn-outline">Clear</a>
    <?php endif; ?>
  </form>

  <!-- ── COURSE GRID ── -->
  <?php if (empty($courses)): ?>
    <div class="empty-state">
      <div class="empty-icon">🔍</div>
      <h3>No courses found</h3>
      <p>Try adjusting your search or filters.</p>
      <a href="courses.php" class="btn btn-primary">Clear Filters</a>
    </div>
  <?php else: ?>
    <div class="courses-grid">
      <?php foreach ($courses as $course): ?>
        <?php
          $emoji    = courseEmoji($course['category']);
          $bg       = courseBg($course['category']);
          $isFree   = floatval($course['price']) === 0.0;
          $enrolled = in_array($course['id'], $enrolledIds);
        ?>
        <div class="course-card">
          <div class="course-thumbnail" style="background: <?= $bg ?>">
            <?= $emoji ?>
            <span class="course-badge"><?= htmlspecialchars($course['level']) ?></span>
            <?php if ($enrolled): ?>
              <span class="enrolled-badge">✓ Enrolled</span>
            <?php endif; ?>
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
              <div style="display:flex;gap:.5rem">
                <a href="course-details.php?id=<?= $course['id'] ?>" class="btn btn-outline">Details</a>
                <?php if ($enrolled): ?>
                  <a href="dashboard.php" class="btn btn-success">Go to Course</a>
                <?php elseif ($user): ?>
                  <form method="POST" action="enroll.php">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <input type="hidden" name="redirect" value="courses.php">
                    <button type="submit" class="btn btn-primary">Enroll</button>
                  </form>
                <?php else: ?>
                  <a href="login.php?redirect=courses.php" class="btn btn-primary">Enroll</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<footer>
  <p>© <?= date('Y') ?> <a href="index.php">LearnHub</a>. Built with PHP &amp; MySQL.</p>
</footer>

</body>
</html>

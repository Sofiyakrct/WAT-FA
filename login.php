<?php
require_once 'config/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error    = '';
$redirect = $_GET['redirect'] ?? 'dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            $redirect = $_POST['redirect'] ?? 'dashboard.php';
            // Basic safety: only redirect to local paths
            if (str_starts_with($redirect, 'http') || str_starts_with($redirect, '//')) {
                $redirect = 'dashboard.php';
            }
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – LearnHub</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav>
  <a class="nav-brand" href="index.php">Learn<span>Hub</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="courses.php">Courses</a></li>
    <li><a href="login.php" class="active">Login</a></li>
    <li><a href="register.php" class="btn btn-primary" style="padding:.4rem .9rem">Get Started</a></li>
  </ul>
</nav>

<div class="auth-wrapper">
  <div class="auth-card">

    <div class="auth-logo">
      <div class="logo-icon">🎓</div>
      <h2>Welcome back</h2>
      <p>Sign in to continue learning</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Demo credentials hint -->
    <div class="alert alert-info" style="margin-bottom:1.5rem">
      💡 <strong>Demo:</strong> alex@demo.com / password123
    </div>

    <form method="POST">
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

      <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          class="form-control"
          placeholder="you@example.com"
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          required
          autofocus
        >
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control"
          placeholder="••••••••"
          required
        >
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:.5rem">
        Sign In →
      </button>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="register.php">Create one free</a>
    </div>
  </div>
</div>

<footer>
  <p>© <?= date('Y') ?> <a href="index.php">LearnHub</a>. Built with PHP &amp; MySQL.</p>
</footer>

</body>
</html>

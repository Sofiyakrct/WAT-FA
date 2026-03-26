<?php
require_once 'config/db.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check duplicate email
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->fetch_assoc()) {
            $error = 'An account with that email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins  = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $ins->bind_param('sss', $name, $email, $hash);
            if ($ins->execute()) {
                // Auto-login
                $_SESSION['user_id']    = $conn->insert_id;
                $_SESSION['user_name']  = $name;
                $_SESSION['user_email'] = $email;
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register – LearnHub</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav>
  <a class="nav-brand" href="index.php">Learn<span>Hub</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="courses.php">Courses</a></li>
    <li><a href="login.php">Login</a></li>
    <li><a href="register.php" class="btn btn-primary active" style="padding:.4rem .9rem">Get Started</a></li>
  </ul>
</nav>

<div class="auth-wrapper">
  <div class="auth-card">

    <div class="auth-logo">
      <div class="logo-icon">🚀</div>
      <h2>Create your account</h2>
      <p>Start learning for free today</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label" for="name">Full Name</label>
        <input type="text" id="name" name="name" class="form-control"
               placeholder="Jane Smith"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
               required autofocus>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@example.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Min. 6 characters" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="confirm">Confirm Password</label>
        <input type="password" id="confirm" name="confirm" class="form-control"
               placeholder="Repeat your password" required>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:.5rem">
        Create Account →
      </button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="login.php">Sign in</a>
    </div>
  </div>
</div>

<footer>
  <p>© <?= date('Y') ?> <a href="index.php">LearnHub</a>. Built with PHP &amp; MySQL.</p>
</footer>

</body>
</html>

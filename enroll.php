<?php
require_once 'config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: courses.php');
    exit;
}

$courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
$redirect = $_POST['redirect'] ?? 'dashboard.php';

// Safety: only local redirect
if (str_starts_with($redirect, 'http') || str_starts_with($redirect, '//')) {
    $redirect = 'dashboard.php';
}

if (!$courseId) {
    header('Location: courses.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Verify course exists
$cs = $conn->prepare("SELECT id FROM courses WHERE id = ?");
$cs->bind_param('i', $courseId);
$cs->execute();
if (!$cs->get_result()->fetch_assoc()) {
    header('Location: courses.php');
    exit;
}

// Check not already enrolled
$es = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
$es->bind_param('ii', $userId, $courseId);
$es->execute();

if (!$es->get_result()->fetch_assoc()) {
    // Enroll
    $ins = $conn->prepare("INSERT INTO enrollments (user_id, course_id, progress) VALUES (?, ?, 0)");
    $ins->bind_param('ii', $userId, $courseId);
    $ins->execute();
    $_SESSION['flash'] = ['type' => 'success', 'msg' => '🎉 You\'ve successfully enrolled in this course!'];
} else {
    $_SESSION['flash'] = ['type' => 'info', 'msg' => 'You\'re already enrolled in this course.'];
}

header('Location: ' . $redirect);
exit;

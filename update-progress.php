<?php
// update-progress.php – Simulate course progress (demo feature)
require_once 'config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = (int)($_POST['course_id'] ?? 0);
    $progress = max(0, min(100, (int)($_POST['progress'] ?? 0)));
    $userId   = $_SESSION['user_id'];

    if ($courseId) {
        $stmt = $conn->prepare(
            "UPDATE enrollments SET progress = ? WHERE user_id = ? AND course_id = ?"
        );
        $stmt->bind_param('iii', $progress, $userId, $courseId);
        $stmt->execute();

        if ($progress >= 100) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => '🏆 Congratulations! You completed this course!'];
        }
    }
}

header('Location: dashboard.php');
exit;

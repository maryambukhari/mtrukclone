<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'worker') {
    echo '<script>window.location = "login.php";</script>';
    exit;
}
$task_id = $_GET['task_id'];
$worker_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM applications WHERE task_id = ? AND worker_id = ?");
$stmt->execute([$task_id, $worker_id]);
if ($stmt->fetch()) {
    echo '<p style="color: #ff4444; text-align: center;">Already applied</p>';
    echo '<script>setTimeout(() => {window.location = "marketplace.php";}, 2000);</script>';
} else {
    $stmt = $pdo->prepare("INSERT INTO applications (task_id, worker_id) VALUES (?, ?)");
    $stmt->execute([$task_id, $worker_id]);
    echo '<script>window.location = "marketplace.php";</script>';
}
?>

<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'worker') {
    echo '<script>window.location = "login.php";</script>';
    exit;
}
$app_id = $_GET['app_id'];
$worker_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? AND worker_id = ? AND status = 'approved'");
$stmt->execute([$app_id, $worker_id]);
$app = $stmt->fetch();
if (!$app) {
    echo '<p style="color: #ff4444; text-align: center;">Not approved or not yours</p>';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];
    $stmt = $pdo->prepare("UPDATE applications SET status = 'completed' WHERE id = ?");
    $stmt->execute([$app_id]);
    $stmt = $pdo->prepare("SELECT payment, id FROM tasks WHERE id = ?");
    $stmt->execute([$app['task_id']]);
    $task = $stmt->fetch();
    $stmt = $pdo->prepare("INSERT INTO earnings (worker_id, task_id, amount) VALUES (?, ?, ?)");
    $stmt->execute([$worker_id, $task['id'], $task['payment']]);
    $stmt = $pdo->prepare("INSERT INTO reviews (task_id, worker_id, rating, feedback) VALUES (?, ?, ?, ?)");
    $stmt->execute([$app['task_id'], $worker_id, $rating, $feedback]);
    echo '<script>window.location = "dashboard.php";</script>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Task - MicroTask</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #56ab2f, #a8e063, #b3e0a2);
            color: #fff;
            margin: 0;
            padding: 20px;
            animation: greenPulse 6s infinite;
        }
        @keyframes greenPulse {
            0% { background: linear-gradient(135deg, #56ab2f, #a8e063, #b3e0a2); }
            50% { background: linear-gradient(135deg, #a8e063, #b3e0a2, #56ab2f); }
            100% { background: linear-gradient(135deg, #b3e0a2, #56ab2f, #a8e063); }
        }
        form {
            max-width: 400px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: swingIn 1s ease-out;
        }
        @keyframes swingIn {
            0% { transform: rotate(5deg) scale(0.9); opacity: 0; }
            50% { transform: rotate(-5deg) scale(1.05); }
            100% { transform: rotate(0) scale(1); opacity: 1; }
        }
        h2 {
            text-align: center;
            color: #fff;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
        }
        p {
            text-align: center;
            color: #333;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            transition: box-shadow 0.3s;
        }
        input:focus, textarea:focus {
            box-shadow: 0 0 10px rgba(86, 171, 47, 0.7);
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #a8e063, #56ab2f);
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s;
        }
        button:hover {
            transform: translateY(-3px);
            background: linear-gradient(45deg, #56ab2f, #a8e063);
        }
        @media (max-width: 600px) {
            form { margin: 20px auto; padding: 15px; }
        }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Complete Task</h2>
        <p>Submit your work (simulated).</p>
        <label>Rating (1-5)</label>
        <input type="number" min="1" max="5" name="rating" required>
        <label>Feedback</label>
        <textarea name="feedback" rows="4" required></textarea>
        <button type="submit">Submit Completion</button>
    </form>
</body>
</html>

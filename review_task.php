<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'worker') {
    echo '<script>window.location = "login.php";</script>';
    exit;
}

$task_id = $_GET['task_id'] ?? null;
if ($task_id) {
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE task_id = ? AND worker_id = ? AND status = 'completed'");
    $stmt->execute([$task_id, $_SESSION['user_id']]);
    $application = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $application) {
    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];
    $stmt = $pdo->prepare("INSERT INTO reviews (task_id, worker_id, rating, feedback, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$task_id, $_SESSION['user_id'], $rating, $feedback]);
    echo '<script>window.location = "dashboard.php";</script>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Task - MicroTask</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #6b48ff, #ff4e50);
            color: #fff;
            margin: 0;
            padding: 20px;
            animation: gradientShift 10s infinite alternate;
        }
        @keyframes gradientShift {
            0% { background: linear-gradient(135deg, #6b48ff, #ff4e50); }
            100% { background: linear-gradient(135deg, #ff4e50, #6b48ff); }
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        h2 {
            text-align: center;
            color: #333;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            font-weight: bold;
            color: #444;
        }
        input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #f9f9f9;
            transition: box-shadow 0.3s;
        }
        input[type="number"]:focus, textarea:focus {
            box-shadow: 0 0 10px rgba(107, 72, 255, 0.5);
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #ff7e5f, #feb47b);
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s;
        }
        button:hover {
            transform: translateY(-3px);
            background: linear-gradient(45deg, #feb47b, #ff7e5f);
        }
        @media (max-width: 600px) {
            .container { margin: 20px auto; padding: 15px; }
            button { font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Review Completed Task</h2>
        <?php if ($application): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Rating (1-5)</label>
                    <input type="number" name="rating" min="1" max="5" required>
                </div>
                <div class="form-group">
                    <label>Feedback</label>
                    <textarea name="feedback" rows="4" required></textarea>
                </div>
                <button type="submit">Submit Review</button>
            </form>
        <?php else: ?>
            <p style="color: #ff4444;">No completed task found to review.</p>
            <script> setTimeout(() => { window.location = "dashboard.php"; }, 2000); </script>
        <?php endif; ?>
    </div>
</body>
</html>

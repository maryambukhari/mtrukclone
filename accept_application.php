<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'requester') {
    echo '<script>window.location = "login.php";</script>';
    exit;
}

$app_id = $_GET['app_id'] ?? null;
$task_id = $_GET['task_id'] ?? null;

if ($app_id && $task_id) {
    $stmt = $pdo->prepare("SELECT a.*, t.requester_id, u.name FROM applications a JOIN tasks t ON a.task_id = t.id JOIN users u ON a.worker_id = u.id WHERE a.id = ? AND t.id = ? AND t.requester_id = ?");
    $stmt->execute([$app_id, $task_id, $_SESSION['user_id']]);
    $application = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $application) {
    $action = $_POST['action'];
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE applications SET status = 'approved' WHERE id = ?");
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE applications SET status = 'rejected' WHERE id = ?");
    }
    $stmt->execute([$app_id]);
    echo '<script>window.location = "requester_dashboard.php";</script>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accept Application - MicroTask</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #ff9a9e, #fad0c4, #ffdde1);
            color: #fff;
            margin: 0;
            padding: 20px;
            animation: glowBackground 8s infinite alternate;
        }
        @keyframes glowBackground {
            0% { background: linear-gradient(135deg, #ff9a9e, #fad0c4, #ffdde1); }
            50% { background: linear-gradient(135deg, #fad0c4, #ffdde1, #ff9a9e); }
            100% { background: linear-gradient(135deg, #ffdde1, #ff9a9e, #fad0c4); }
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: floatUp 1s ease-out;
        }
        @keyframes floatUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        h2 {
            text-align: center;
            color: #fff;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
            animation: pulseText 2s infinite;
        }
        @keyframes pulseText {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .app-details {
            background: linear-gradient(135deg, #fad0c4, #ff9a9e);
            padding: 15px;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .app-details:hover {
            transform: translateY(-5px);
        }
        form {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        button {
            padding: 12px 25px;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s;
        }
        button:hover {
            transform: scale(1.05);
            background: linear-gradient(45deg, #fad0c4, #ff9a9e);
        }
        button[name="action"][value="reject"] {
            background: linear-gradient(45deg, #ff6b6b, #ff8787);
        }
        button[name="action"][value="reject"]:hover {
            background: linear-gradient(45deg, #ff8787, #ff6b6b);
        }
        @media (max-width: 600px) {
            .container { margin: 20px auto; padding: 15px; }
            form { flex-direction: column; }
            button { width: 100%; margin: 5px 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Accept or Reject Application</h2>
        <?php if ($application): ?>
            <div class="app-details">
                <p><strong>Worker:</strong> <?php echo htmlspecialchars($application['name']); ?></p>
                <p><strong>Task:</strong> <?php echo htmlspecialchars($application['task_id']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($application['status']); ?></p>
            </div>
            <form method="POST">
                <button type="submit" name="action" value="approve">Approve</button>
                <button type="submit" name="action" value="reject">Reject</button>
            </form>
        <?php else: ?>
            <p style="color: #ff4444; text-align: center;">Invalid application or task.</p>
            <script> setTimeout(() => { window.location = "requester_dashboard.php"; }, 2000); </script>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'worker') {
    echo '<script>window.location = "login.php";</script>';
    exit;
}
$category = $_GET['category'] ?? '';
$where = $category ? "AND category = :category" : '';
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE status = 'open' $where");
if ($category) $stmt->bindParam(':category', $category);
$stmt->execute();
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Marketplace - MicroTask</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #ff6f61, #ffcc5c, #88d8b0);
            color: #fff;
            margin: 0;
            padding: 20px;
            animation: rainbowFlow 8s infinite;
        }
        @keyframes rainbowFlow {
            0% { background: linear-gradient(135deg, #ff6f61, #ffcc5c, #88d8b0); }
            33% { background: linear-gradient(135deg, #ffcc5c, #88d8b0, #ff6f61); }
            66% { background: linear-gradient(135deg, #88d8b0, #ff6f61, #ffcc5c); }
            100% { background: linear-gradient(135deg, #ff6f61, #ffcc5c, #88d8b0); }
        }
        .filters {
            text-align: center;
            margin-bottom: 20px;
            animation: fadeInDown 1s;
        }
        @keyframes fadeInDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .task {
            background: linear-gradient(135deg, #88d8b0, #ffcc5c);
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            animation: bounceIn 0.8s;
        }
        @keyframes bounceIn {
            0% { transform: scale(0.9); opacity: 0; }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); opacity: 1; }
        }
        .task:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        a {
            color: #ff6f61;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        a:hover {
            color: #ff4500;
        }
        @media (max-width: 600px) {
            .task { padding: 10px; }
        }
    </style>
</head>
<body>
    <h1>Task Marketplace</h1>
    <div class="filters">
        <a href="?category=Data Entry">Data Entry</a> |
        <a href="?category=Surveys">Surveys</a> |
        <a href="?category=Transcription">Transcription</a> |
        <a href="?">All</a>
    </div>
    <?php foreach ($tasks as $task): ?>
        <div class="task">
            <h3><?php echo htmlspecialchars($task['title']); ?></h3>
            <p><?php echo htmlspecialchars($task['description']); ?></p>
            <p>Category: <?php echo $task['category']; ?></p>
            <p>Payment: $<?php echo $task['payment']; ?></p>
            <p>Deadline: <?php echo $task['deadline']; ?></p>
            <a href="apply_task.php?task_id=<?php echo $task['id']; ?>">Apply</a>
        </div>
    <?php endforeach; ?>
</body>
</html>

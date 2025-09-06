<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'requester') {
    echo '<script>window.location = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE requester_id = ?");
$stmt->execute([$user_id]);
$posted = $stmt->fetchAll();

foreach ($posted as &$task) {
    $stmt = $pdo->prepare("SELECT a.*, u.name FROM applications a JOIN users u ON a.worker_id = u.id WHERE a.task_id = ?");
    $stmt->execute([$task['id']]);
    $task['apps'] = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requester Dashboard - MicroTask</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #00c6ff, #0072ff, #0097e6);
            color: #fff;
            margin: 0;
            padding: 20px;
            animation: oceanWave 8s infinite;
        }
        @keyframes oceanWave {
            0% { background: linear-gradient(135deg, #00c6ff, #0072ff, #0097e6); }
            50% { background: linear-gradient(135deg, #0072ff, #0097e6, #00c6ff); }
            100% { background: linear-gradient(135deg, #0097e6, #00c6ff, #0072ff); }
        }
        header {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            animation: bounceIn 1s;
        }
        @keyframes bounceIn {
            0% { transform: scale(0.9); opacity: 0; }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); opacity: 1; }
        }
        section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            color: #333;
            padding: 25px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            animation: slideUp 1s ease-out;
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .task-card {
            background: linear-gradient(to right, #ff9a9e, #fad0c4, #ffd1dc);
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
            animation: fadeIn 0.8s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: left;
        }
        th {
            background: linear-gradient(45deg, #ff5722, #ff8a65);
            color: white;
        }
        a {
            color: #ff9800;
            text-decoration: none;
            transition: color 0.3s;
        }
        a:hover {
            color: #f57c00;
        }
        @media (max-width: 600px) {
            section { padding: 15px; }
            table { font-size: 12px; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Requester Dashboard</h1>
        <a href="logout.php">Logout</a>
    </header>
    <section>
        <h2>Your Tasks</h2>
        <a href="post_task.php">Post New Task</a>
        <?php if (empty($posted)): ?>
            <p style="color: #ff4444;">No tasks posted yet.</p>
        <?php else: ?>
            <?php foreach ($posted as $task): ?>
                <div class="task-card">
                    <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                    <p><?php echo htmlspecialchars($task['description']); ?></p>
                    <p>Payment: $<?php echo $task['payment']; ?> | Deadline: <?php echo $task['deadline']; ?></p>
                    <h4>Applications:</h4>
                    <?php if (empty($task['apps'])): ?>
                        <p>No applications yet.</p>
                    <?php else: ?>
                        <table>
                            <tr>
                                <th>Worker</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            <?php foreach ($task['apps'] as $app): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($app['name']); ?></td>
                                    <td><?php echo htmlspecialchars($app['status']); ?></td>
                                    <td>
                                        <?php if ($app['status'] == 'pending'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                                <button type="submit" name="approve">Approve</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</body>
</html>

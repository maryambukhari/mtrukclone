<?php
include 'db.php';
session_start();

// Error handling for session and database
try {
    if (!isset($_SESSION['user_id'])) {
        echo '<script>window.location = "login.php";</script>';
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // Handle approve application
    if (isset($_POST['approve'])) {
        $app_id = $_POST['app_id'];
        $stmt = $pdo->prepare("UPDATE applications SET status = 'approved' WHERE id = ? AND task_id IN (SELECT id FROM tasks WHERE requester_id = ?)");
        if (!$stmt->execute([$app_id, $user_id])) {
            throw new Exception("Failed to approve application.");
        }
    }

    // Handle withdraw (simulate)
    if (isset($_POST['withdraw'])) {
        echo '<div class="notification success">Withdrawal requested successfully!</div>';
    }

    // Fetch data based on role
    if ($role == 'worker') {
        $stmt = $pdo->prepare("SELECT t.*, a.id as app_id FROM tasks t JOIN applications a ON t.id = a.task_id WHERE a.worker_id = ? AND a.status = 'approved'");
        if (!$stmt->execute([$user_id])) {
            throw new Exception("Failed to fetch accepted tasks.");
        }
        $accepted = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT t.*, a.id as app_id FROM tasks t JOIN applications a ON t.id = a.task_id WHERE a.worker_id = ? AND a.status = 'completed'");
        if (!$stmt->execute([$user_id])) {
            throw new Exception("Failed to fetch completed tasks.");
        }
        $completed = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM earnings WHERE worker_id = ?");
        if (!$stmt->execute([$user_id])) {
            throw new Exception("Failed to fetch earnings.");
        }
        $earnings = $stmt->fetch()['total'] ?? 0;
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE requester_id = ?");
        if (!$stmt->execute([$user_id])) {
            throw new Exception("Failed to fetch posted tasks.");
        }
        $posted = $stmt->fetchAll();

        foreach ($posted as &$task) {
            $stmt = $pdo->prepare("SELECT a.*, u.name FROM applications a JOIN users u ON a.worker_id = u.id WHERE a.task_id = ? AND a.status = 'pending'");
            if (!$stmt->execute([$task['id']])) {
                throw new Exception("Failed to fetch task applications.");
            }
            $task['apps'] = $stmt->fetchAll();
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo '<div class="notification error">500 Internal Server Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MicroTask</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #ff6e7f, #bfe9ff);
            color: #fff;
            margin: 0;
            padding: 20px;
            overflow-x: hidden;
        }
        header {
            text-align: center;
            padding: 30px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            animation: slideIn 1s ease-in-out;
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        h1, h2 {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        section {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 20px;
            margin: 20px 0;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 1.2s ease-in-out;
        }
        @keyframes fadeInUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .task {
            background: linear-gradient(to right, #ff9966, #ff5e62);
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .task:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #4CAF50;
            color: white;
        }
        button {
            background: linear-gradient(to right, #ff4b2b, #ff416c);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        button:hover {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            transform: scale(1.05);
        }
        a {
            color: #00b4db;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        a:hover {
            color: #0083b0;
        }
        .notification {
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            animation: fadeIn 0.5s;
            text-align: center;
        }
        .notification.success {
            background: #4CAF50;
            color: white;
        }
        .notification.error {
            background: #f44336;
            color: white;
        }
        @media (max-width: 600px) {
            section { padding: 10px; }
            table { font-size: 14px; }
            button { padding: 8px 16px; }
        }
    </style>
</head>
<body>
    <header>
        <h1><?php echo ucfirst($role); ?> Dashboard</h1>
        <a href="logout.php">Logout</a>
    </header>
    <?php if ($role == 'worker'): ?>
        <section>
            <h2>Accepted Tasks</h2>
            <?php if (empty($accepted)): ?>
                <p class="notification">No accepted tasks yet.</p>
            <?php else: ?>
                <?php foreach ($accepted as $task): ?>
                    <div class="task">
                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                        <p><?php echo htmlspecialchars($task['description']); ?></p>
                        <a href="complete_task.php?app_id=<?php echo $task['app_id']; ?>">Complete Task</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
        <section>
            <h2>Completed Tasks</h2>
            <?php if (empty($completed)): ?>
                <p class="notification">No completed tasks yet.</p>
            <?php else: ?>
                <?php foreach ($completed as $task): ?>
                    <div class="task">
                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                        <p><?php echo htmlspecialchars($task['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
        <section>
            <h2>Earnings: $<?php echo number_format($earnings, 2); ?></h2>
            <form method="POST">
                <button type="submit" name="withdraw">Request Withdrawal</button>
            </form>
        </section>
    <?php else: ?>
        <section>
            <h2>Posted Tasks</h2>
            <a href="post_task.php">Post New Task</a>
            <?php if (empty($posted)): ?>
                <p class="notification">No tasks posted yet.</p>
            <?php else: ?>
                <?php foreach ($posted as $task): ?>
                    <div class="task">
                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                        <p><?php echo htmlspecialchars($task['description']); ?></p>
                        <h4>Pending Applications:</h4>
                        <?php if (empty($task['apps'])): ?>
                            <p class="notification">No pending applications.</p>
                        <?php else: ?>
                            <table>
                                <tr>
                                    <th>Applicant</th>
                                    <th>Action</th>
                                </tr>
                                <?php foreach ($task['apps'] as $app): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($app['name']); ?></td>
                                        <td>
                                            <form method="POST">
                                                <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                                <button type="submit" name="approve">Approve</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</body>
</html>

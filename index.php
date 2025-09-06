<?php
include 'db.php';
session_start();

// Fetch featured tasks
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE status = 'open' LIMIT 5");
$stmt->execute();
$featured = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - MicroTask</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298, #4a69bd);
            color: #fff;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            animation: bgPulse 10s infinite alternate;
        }
        @keyframes bgPulse {
            0% { background: linear-gradient(135deg, #1e3c72, #2a5298, #4a69bd); }
            50% { background: linear-gradient(135deg, #2a5298, #4a69bd, #1e3c72); }
            100% { background: linear-gradient(135deg, #4a69bd, #1e3c72, #2a5298); }
        }
        header {
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            animation: float 3s infinite ease-in-out;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .signup {
            display: flex;
            justify-content: center;
            gap: 20px;
            animation: fadeIn 1.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .signup a {
            padding: 12px 25px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .signup a:hover {
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(255, 107, 107, 0.7);
        }
        .featured {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }
        .task {
            background: linear-gradient(135deg, #4a69bd, #6a7ba2);
            padding: 15px;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.8s ease-out;
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @media (max-width: 600px) {
            .signup { flex-direction: column; align-items: center; }
            .task { padding: 10px; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to MicroTask</h1>
        <p>A platform where requesters post tasks and workers complete them for payments.</p>
        <div class="signup">
            <a href="register.php?role=requester">Signup as Requester</a>
            <a href="register.php?role=worker">Signup as Worker</a>
            <a href="login.php">Login</a>
        </div>
    </header>
    <section class="featured">
        <h2>Featured Tasks</h2>
        <?php foreach ($featured as $task): ?>
            <div class="task">
                <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                <p><?php echo htmlspecialchars($task['description']); ?></p>
                <p>Payment: $<?php echo $task['payment']; ?></p>
                <p>Deadline: <?php echo $task['deadline']; ?></p>
            </div>
        <?php endforeach; ?>
    </section>
</body>
</html>

<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'worker') {
    echo '<script>window.location = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM earnings WHERE worker_id = ?");
$stmt->execute([$user_id]);
$earnings = $stmt->fetch()['total'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = min($_POST['amount'], $earnings);
    if ($amount > 0) {
        // Simulate withdrawal (in real app, integrate with payment gateway)
        echo '<div class="notification success">Withdrawal of $' . number_format($amount, 2) . ' requested successfully!</div>';
        // Update earnings (for demo)
        $stmt = $pdo->prepare("UPDATE earnings SET amount = 0 WHERE worker_id = ? AND amount <= ?");
        $stmt->execute([$user_id, $amount]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw - MicroTask</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #ff6f61, #cb2d3e, #ff8a00);
            color: #fff;
            margin: 0;
            padding: 20px;
            animation: colorWave 6s infinite alternate;
        }
        @keyframes colorWave {
            0% { background: linear-gradient(135deg, #ff6f61, #cb2d3e, #ff8a00); }
            50% { background: linear-gradient(135deg, #cb2d3e, #ff8a00, #ff6f61); }
            100% { background: linear-gradient(135deg, #ff8a00, #ff6f61, #cb2d3e); }
        }
        .container {
            max-width: 450px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            animation: popIn 1s ease-out;
        }
        @keyframes popIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        h2 {
            text-align: center;
            color: #fff;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            color: #ddd;
            font-weight: bold;
        }
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            transition: box-shadow 0.3s;
        }
        input[type="number"]:focus {
            box-shadow: 0 0 8px rgba(203, 45, 62, 0.5);
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #ff8a00, #e52e71);
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s;
        }
        button:hover {
            transform: translateY(-3px);
            background: linear-gradient(45deg, #e52e71, #ff8a00);
        }
        .notification {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
            animation: fadeIn 0.5s;
        }
        .notification.success {
            background: rgba(76, 175, 80, 0.8);
            color: white;
        }
        @media (max-width: 600px) {
            .container { margin: 20px auto; padding: 15px; }
            button { font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Withdraw Earnings</h2>
        <p>Available Balance: $<?php echo number_format($earnings, 2); ?></p>
        <form method="POST">
            <div class="form-group">
                <label>Amount to Withdraw</label>
                <input type="number" name="amount" min="0" max="<?php echo $earnings; ?>" step="0.01" required>
            </div>
            <button type="submit">Request Withdrawal</button>
        </form>
    </div>
</body>
</html>

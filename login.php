<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!$email || !$password) {
        $error = "Email and password are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    if ($user['role'] === 'worker' || $user['role'] === 'requester') { // Allow both roles for now
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['name'] = $user['name'];
                        $_SESSION['role'] = $user['role'];
                        header("Location: dashboard.php");
                        exit;
                    } else {
                        $error = "Invalid role for this platform.";
                    }
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "Email not found.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MicroTask</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #ff7f50, #ff4500, #ff6347);
            color: #fff;
            margin: 0;
            padding: 20px;
            animation: colorFlow 8s infinite alternate;
        }
        @keyframes colorFlow {
            0% { background: linear-gradient(135deg, #ff7f50, #ff4500, #ff6347); }
            50% { background: linear-gradient(135deg, #ff4500, #ff6347, #ff7f50); }
            100% { background: linear-gradient(135deg, #ff6347, #ff7f50, #ff4500); }
        }
        form {
            max-width: 400px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: rotateIn 1s ease-out;
        }
        @keyframes rotateIn {
            from { transform: rotate(-10deg) scale(0.9); opacity: 0; }
            to { transform: rotate(0) scale(1); opacity: 1; }
        }
        h2 {
            text-align: center;
            color: #fff;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
        }
        .error {
            color: #ff4444;
            text-align: center;
            margin-bottom: 10px;
            animation: shake 0.5s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            transition: box-shadow 0.3s;
        }
        input:focus {
            box-shadow: 0 0 10px rgba(255, 69, 0, 0.7);
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #ff4500, #ff8c00);
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s;
        }
        button:hover {
            transform: translateY(-3px);
            background: linear-gradient(45deg, #ff8c00, #ff4500);
        }
        .signup {
            text-align: center;
            margin-top: 10px;
        }
        .signup a {
            color: #ff8c00;
            text-decoration: none;
        }
        .signup a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            form { margin: 20px auto; padding: 15px; }
        }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <div class="signup">
            <p>Don't have an account? <a href="register.php?role=worker">Signup as Worker</a> or <a href="register.php?role=requester">Signup as Requester</a></p>
        </div>
    </form>
</body>
</html>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $company_name = $role === 'requester' ? trim($_POST['company_name']) : null;

    if (!$email || !$name || !$password || !$role) {
        $error = "All fields (Name, Email, Password, Role) are required.";
    } elseif ($role === 'requester' && empty($company_name)) {
        $error = "Company Name is required for Requester.";
    } else {
        try {
            // Check for existing email
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Email already registered.";
            } else {
                // Dynamically build the query based on column existence
                $columns = ['name', 'email', 'password', 'role'];
                $values = [$name, $email, $password, $role];
                $checkColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'company_name'");
                if ($checkColumn->rowCount() > 0 && $role === 'requester' && $company_name) {
                    $columns[] = 'company_name';
                    $values[] = $company_name;
                }
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $query = "INSERT INTO users (" . implode(', ', $columns) . ") VALUES (" . $placeholders . ")";
                $stmt = $pdo->prepare($query);
                $stmt->execute($values);
                echo '<script>alert("Registration successful! Please login."); window.location = "login.php";</script>';
                exit;
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}

$default_role = $_GET['role'] ?? 'worker';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MicroTask</title>
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
        input, select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            transition: box-shadow 0.3s;
        }
        input:focus, select:focus {
            box-shadow: 0 0 10px rgba(255, 69, 0, 0.7);
            outline: none;
        }
        .company-field {
            display: none;
        }
        .company-field.active {
            display: block;
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
        @media (max-width: 600px) {
            form { margin: 20px auto; padding: 15px; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.querySelector('select[name="role"]');
            const companyField = document.querySelector('.company-field');
            if (!roleSelect || !companyField) {
                console.error('Role select or company field not found');
                return;
            }
            roleSelect.addEventListener('change', function() {
                if (this.value === 'requester') {
                    companyField.classList.add('active');
                } else {
                    companyField.classList.remove('active');
                }
            });
            if (roleSelect.value === 'requester') {
                companyField.classList.add('active');
            }
        });
    </script>
</head>
<body>
    <form method="POST">
        <h2>Register</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <input type="text" name="name" placeholder="Name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="worker" <?php echo $default_role === 'worker' ? 'selected' : ''; ?>>Worker</option>
            <option value="requester" <?php echo $default_role === 'requester' ? 'selected' : ''; ?>>Requester</option>
        </select>
        <div class="company-field">
            <input type="text" name="company_name" placeholder="Company Name (required for Requester)" value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : ''; ?>">
        </div>
        <button type="submit">Register</button>
    </form>
</body>
</html>

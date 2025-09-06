<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'requester') {
    echo '<script>window.location = "login.php";</script>';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $payment = $_POST['payment'];
    $deadline = $_POST['deadline'];
    $requester_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT INTO tasks (requester_id, title, description, category, payment, deadline) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$requester_id, $title, $description, $category, $payment, $deadline]);
    echo '<script>window.location = "dashboard.php";</script>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Task - MicroTask</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #ff9a9e, #fad0c4, #ffd1dc);
            color: #fff;
            margin: 0;
            padding: 20px;
            animation: shimmer 6s infinite;
        }
        @keyframes shimmer {
            0% { background: linear-gradient(135deg, #ff9a9e, #fad0c4, #ffd1dc); }
            50% { background: linear-gradient(135deg, #fad0c4, #ffd1dc, #ff9a9e); }
            100% { background: linear-gradient(135deg, #ffd1dc, #ff9a9e, #fad0c4); }
        }
        form {
            max-width: 500px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: spinIn 1s ease-out;
        }
        @keyframes spinIn {
            from { transform: rotate(10deg) scale(0.9); opacity: 0; }
            to { transform: rotate(0) scale(1); opacity: 1; }
        }
        h2 {
            text-align: center;
            color: #fff;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
        }
        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: #000; /* Changed text color to black */
            transition: box-shadow 0.3s;
        }
        input:focus, textarea:focus, select:focus {
            box-shadow: 0 0 10px rgba(255, 154, 158, 0.7);
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s;
        }
        button:hover {
            transform: translateY(-3px);
            background: linear-gradient(45deg, #fad0c4, #ff9a9e);
        }
        @media (max-width: 600px) {
            form { margin: 20px auto; padding: 15px; }
        }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Post a New Task</h2>
        <input type="text" name="title" placeholder="Title" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <select name="category" required>
            <option value="Data Entry">Data Entry</option>
            <option value="Surveys">Surveys</option>
            <option value="Transcription">Transcription</option>
        </select>
        <input type="number" step="0.01" name="payment" placeholder="Payment" required>
        <input type="date" name="deadline" required>
        <button type="submit">Post Task</button>
    </form>
</body>
</html>

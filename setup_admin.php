<?php


$conn = new mysqli('localhost', 'root', '', 'library_db');

if ($conn->connect_error) {
    die('<p style="color:red">DB connection failed: ' . $conn->connect_error . '</p>');
}

// Create admins table
$conn->query("
    CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");


$message = '';
$defaultUser = 'admin';
$defaultPass = 'admin123';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? $defaultUser);
    $password = $_POST['password'] ?? $defaultPass;

    if (empty($username) || empty($password)) {
        $message = '<p style="color:orange">Please fill in all fields.</p>';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?) ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
        $stmt->bind_param('ss', $username, $hash);
        if ($stmt->execute()) {
            $message = '<p style="color:green">✅ Admin account created! Username: <strong>' . htmlspecialchars($username) . '</strong>.<br>You can now <a href="login.php">login here</a>.<br><strong style=\"color:red\">Delete this file (setup_admin.php) now!</strong></p>';
        } else {
            $message = '<p style="color:red">Error: ' . $conn->error . '</p>';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Setup</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: #0e0c0a; color: #e8ddd0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #161412; border: 1px solid #2a2520; border-radius: 12px; padding: 36px; max-width: 420px; width: 100%; }
        h2 { font-size: 20px; margin-bottom: 6px; color: #f5ede0; }
        p.sub { font-size: 13px; color: #7a6f63; margin-bottom: 24px; }
        label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #7a6f63; margin-bottom: 6px; margin-top: 16px; }
        input { width: 100%; background: #1e1b18; border: 1px solid #2a2520; border-radius: 8px; color: #e8ddd0; font-family: 'DM Sans', sans-serif; font-size: 14px; padding: 10px 14px; outline: none; }
        input:focus { border-color: #c9a84c; }
        button { margin-top: 24px; width: 100%; background: #c9a84c; color: #0e0c0a; border: none; border-radius: 8px; padding: 12px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; }
        button:hover { background: #e8c96e; }
        .warning { background: rgba(230,126,34,0.1); border: 1px solid rgba(230,126,34,0.3); border-radius: 8px; padding: 12px; font-size: 12px; color: #e67e22; margin-bottom: 20px; }
        .msg { margin-top: 20px; font-size: 14px; line-height: 1.6; }
        a { color: #c9a84c; }
    </style>
</head>
<body>
<div class="card">
    <h2>🔧 Admin Setup</h2>
    <p class="sub">Create the admin account for Library Management System</p>

    <div class="warning">⚠️ Run this script <strong>once only</strong>, then delete it from your server for security.</div>

    <?php if ($message): ?>
        <div class="msg"><?= $message ?></div>
    <?php else: ?>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" value="admin" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter a strong password" required>

            <button type="submit">Create Admin Account</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>

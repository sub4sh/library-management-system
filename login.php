<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Database config (inline to avoid circular include)
$conn = new mysqli('localhost', 'root', '', 'library_db');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } elseif ($conn->connect_error) {
        $error = 'Database connection failed.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']       = $row['id'];
                $_SESSION['admin_username'] = $row['username'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0e0c0a;
            --surface: #161412;
            --surface2: #1e1b18;
            --border: #2a2520;
            --gold: #c9a84c;
            --gold-light: #e8c96e;
            --cream: #f5ede0;
            --muted: #7a6f63;
            --text: #e8ddd0;
            --danger: #c0392b;
            --success: #27ae60;
            --radius: 8px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse at 20% 50%, rgba(201,168,76,0.06) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 20%, rgba(201,168,76,0.04) 0%, transparent 50%);
            pointer-events: none;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        /* Brand header above card */
        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: rgba(201,168,76,0.12);
            border: 1px solid rgba(201,168,76,0.25);
            border-radius: 14px;
            margin-bottom: 16px;
        }

        .brand-logo svg { width: 28px; height: 28px; color: var(--gold); }

        .brand h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            color: var(--gold);
            letter-spacing: 0.5px;
        }

        .brand p {
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 4px;
        }

        /* Card */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 36px 32px 32px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--cream);
            margin-bottom: 6px;
        }

        .card-sub {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 28px;
        }

        /* Alert */
        .alert {
            background: rgba(192, 57, 43, 0.12);
            border: 1px solid rgba(192, 57, 43, 0.3);
            border-radius: var(--radius);
            padding: 12px 14px;
            font-size: 13px;
            color: #e74c3c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Form */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--muted);
            margin-bottom: 8px;
            font-weight: 500;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: var(--muted);
            pointer-events: none;
        }

        .form-group input {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            padding: 11px 14px 11px 42px;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-group input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201,168,76,0.08);
        }

        .form-group input::placeholder { color: var(--muted); }

        /* Password toggle */
        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
        }

        .toggle-pw:hover { color: var(--text); }
        .toggle-pw svg { width: 16px; height: 16px; }

        /* Submit */
        .btn-login {
            width: 100%;
            background: var(--gold);
            color: #0e0c0a;
            border: none;
            border-radius: var(--radius);
            padding: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            margin-top: 8px;
            letter-spacing: 0.3px;
        }

        .btn-login:hover { background: var(--gold-light); }
        .btn-login:active { transform: scale(0.99); }

        /* Footer note */
        .card-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: var(--muted);
        }

        .card-footer a {
            color: var(--gold);
            text-decoration: none;
        }

        .card-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- Brand -->
    <div class="brand">
       
        <h1>Library</h1>
        <p>Management System</p>
    </div>

    <!-- Card -->
    <div class="card">
        <div class="card-title">Admin Sign In</div>
        <div class="card-sub">Enter your credentials to access the dashboard</div>

        <?php if ($error): ?>
        <div class="alert">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;flex-shrink:0">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrap">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input type="text" name="username" placeholder="admin" autocomplete="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                    <input type="password" name="password" id="pwField" placeholder="••••••••" autocomplete="current-password" required>
                    <button type="button" class="toggle-pw" onclick="togglePassword()" title="Show/hide password">
                        <svg id="eyeIcon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">Sign In to Dashboard</button>
        </form>

       
    </div>

</div>

<script>
function togglePassword() {
    const field = document.getElementById('pwField');
    const icon  = document.getElementById('eyeIcon');
    if (field.type === 'password') {
        field.type = 'text';
        icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;
    } else {
        field.type = 'password';
        icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    }
}
</script>
</body>
</html>

<?php
session_start();

// Hardcoded admin credentials
define('ADMIN_USERNAME', 'Admin');
define('ADMIN_PASSWORD', '0987');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = 'Administrator';
        header('Location: adminDashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy: #253154;
            --navy-light: #2e3d66;
            --navy-mid: #1e2a47;
            --navy-dark: #141e35;
            --white: #ffffff;
            --card-bg: #ffffff;
            --text-dark: #1a2340;
            --text-muted: #7a88a8;
            --green: #2ecc71;
            --green-hover: #27b860;
            --input-border: #dce3f0;
            --input-focus: #253154;
            --error-bg: #fff0f0;
            --error-text: #c0392b;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--navy);
            position: relative;
            overflow: hidden;
        }

        /* ── Background circles exactly like the staff login ── */
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.055);
        }
        .bg-circle-1 {
            width: 420px; height: 420px;
            top: -80px; left: -120px;
        }
        .bg-circle-2 {
            width: 340px; height: 340px;
            bottom: -60px; right: -80px;
        }
        .bg-circle-3 {
            width: 180px; height: 180px;
            top: 60%; left: 10%;
            background: rgba(255,255,255,0.03);
        }

        /* ── Card ── */
        .card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 48px 44px 40px;
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 10;
            box-shadow: 0 24px 64px rgba(0,0,0,0.28);
        }

        /* ── Icon ── */
        .brand-icon {
            width: 64px; height: 64px;
            background: var(--navy);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .brand-icon svg {
            width: 30px; height: 30px;
            fill: none;
            stroke: white;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* ── Headings ── */
        h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            text-align: center;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        /* ── Form ── */
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
        }
        .input-wrap {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px; height: 18px;
            stroke: var(--text-muted);
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 11px 14px 11px 42px;
            border: 1.5px solid var(--input-border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: var(--text-dark);
            background: #fafbfd;
            transition: border-color 0.18s, box-shadow 0.18s;
            outline: none;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--navy);
            background: white;
            box-shadow: 0 0 0 3px rgba(37,49,84,0.1);
        }

        /* ── Toggle password ── */
        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: var(--text-muted);
        }
        .toggle-pw svg {
            width: 18px; height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* ── Error ── */
        .error-box {
            background: var(--error-bg);
            border-left: 3px solid var(--error-text);
            color: var(--error-text);
            font-size: 13px;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        /* ── Buttons ── */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--navy);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: background 0.18s, transform 0.12s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }
        .btn-login:hover { background: var(--navy-light); }
        .btn-login:active { transform: scale(0.98); }
        .btn-login svg {
            width: 18px; height: 18px;
            fill: none;
            stroke: white;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* ── Footer tagline ── */
        .tagline {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 22px;
            letter-spacing: 0.3px;
        }
        .tagline span { color: #aab2c8; margin: 0 4px; }
    </style>
</head>
<body>

<!-- Background decorative circles -->
<div class="bg-circle bg-circle-1"></div>
<div class="bg-circle bg-circle-2"></div>
<div class="bg-circle bg-circle-3"></div>

<div class="card">

    <h1>Admin Login</h1>
    <p class="subtitle">Sign in to access the BUIH Laundry<br>administrator dashboard.</p>

    <!-- Error -->
    <?php if ($error): ?>
        <div class="error-box">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="">

        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <input type="text" id="username" name="username"
                       placeholder="Enter your username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       autocomplete="username" required>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                <input type="password" id="password" name="password"
                       placeholder="Enter your password"
                       autocomplete="current-password" required>
                <button type="button" class="toggle-pw" onclick="togglePassword()" title="Show/hide password">
                    <svg id="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-login">
            <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Login
        </button>
    </form>

    <p class="tagline">Fast <span>•</span> Reliable <span>•</span> Convenient Laundry Service</p>
</div>

<script>
function togglePassword() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        pw.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}
</script>

</body>
</html>
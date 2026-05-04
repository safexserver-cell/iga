<?php
session_start();
require_once '../api/db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | IGA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #061121; color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: 'Inter', sans-serif; }
        .login-card { background: #0B1F3A; padding: 40px; border-radius: 12px; border: 1px solid rgba(212, 175, 55, 0.2); box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 100%; max-width: 400px; }
        .btn-gold { background: linear-gradient(90deg, #D4AF37, #F5D76E); color: #0B1F3A; font-weight: 600; border: none; }
        .btn-gold:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h2 style="color: #D4AF37; font-family: 'Playfair Display', serif;">IGA Admin</h2>
            <p class="text-white">Sign in to manage laureates</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control bg-dark border-secondary text-light" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control bg-dark border-secondary text-light" required>
            </div>
            <button type="submit" class="btn btn-gold w-100 py-2">Login</button>
        </form>
        <div class="text-center mt-3 text-white" style="font-size: 0.8rem;">
            Default login: admin / admin123
        </div>
    </div>
</body>
</html>

<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id  = trim($_POST['user_id']);
    $password = trim($_POST['password']);

    if (!empty($user_id) && !empty($password)) {

        // --- Check Admin ---
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE user_id = :user_id AND status = 'active' LIMIT 1");
        $stmt->execute([':user_id' => $user_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['role'] = 'admin';
            $_SESSION['user_id'] = $admin['user_id'];
            $_SESSION['name'] = $admin['admin_name'];
            header("Location: dashboard.php");
            exit();
        }

        // --- Check System User ---
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id AND status = 'active' LIMIT 1");
        $stmt->execute([':user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['role'] = 'user';
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['staff_name'];
            header("Location: dashboard.php");
            exit();
        }

        $error = "Invalid User ID or Password";

    } else {
        $error = "Please enter User ID and Password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - eASSETS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #000000, #0d6efd);
        height: 100vh;
        font-family: 'Poppins', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }
    .login-card {
        backdrop-filter: blur(15px);
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        color: white;
        padding: 2rem;
        width: 100%;
        max-width: 400px;
    }
    .form-label { color: #ddd; }
    .form-control {
        background-color: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
    }
    .form-control::placeholder { color: #ccc; }
    .form-control:focus {
        background-color: rgba(255, 255, 255, 0.15);
        color: white;
        box-shadow: none;
    }
    .btn-primary {
        background-color: rgb(0, 0, 0);
        border: none;
        font-weight: bold;
    }
    .btn-primary:hover { background-color: #0b5ed7; }
    .alert { width: 100%; }
    .logo { display: block; margin: 0 auto 10px auto; width: 200px; height: auto; }
    .input-group-text {
        background-color: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
    }
    .input-group-text:hover { background-color: rgba(255, 255, 255, 0.2); }
    a { color: white; }
</style>
</head>
<body>

<div class="login-card">
    <img src="assets/images/logo_eAsset.png" alt="Logo" class="logo text-center">

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['activated'])): ?>
        <div class="alert alert-success">
            Account activated. Please login.
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3 text-start">
            <label for="user_id" class="form-label">User ID</label>
            <input type="text" class="form-control" id="user_id" name="user_id" placeholder="Enter your User ID" required>
        </div>

        <div class="mb-3 text-start">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>  
    </form>

    <!-- Forgot Password Link -->
    <div class="mt-3 text-center">
        <a href="forgot_password.php">Forgot Password?</a>
    </div>
</div>

<script>
    // Hide alert messages after 3 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500); // remove from DOM
        });
    }, 3000); // 3000ms = 3 seconds
</script>

</body>
</html>

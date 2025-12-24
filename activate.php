<?php
session_start();
require_once 'config/database.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid link.");
}

// Find user by token
$stmt = $pdo->prepare("SELECT * FROM users WHERE activation_token = :token LIMIT 1");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Invalid or expired link.");
}

// Determine purpose
$purpose = ($user['status'] === 'pending') ? 'activate' : 'reset';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    if (!$password || !$confirm) {
        $error = "Both fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        if ($purpose === 'activate') {
            // Activate account
            $stmt = $pdo->prepare("UPDATE users 
                                   SET password = :password, status = 'active', activation_token = NULL
                                   WHERE id = :id");
            $stmt->execute([
                ':password' => $hashed,
                ':id'       => $user['id']
            ]);
            $success = "Your account is now activated. You can <a href='login.php'>login</a>.";
        } else {
            // Reset password
            $stmt = $pdo->prepare("UPDATE users 
                                   SET password = :password, activation_token = NULL
                                   WHERE id = :id");
            $stmt->execute([
                ':password' => $hashed,
                ':id'       => $user['id']
            ]);
            $success = "Your password has been reset successfully. You can <a href='login.php'>login</a>.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= ($purpose === 'activate') ? 'Activate Account' : 'Reset Password' ?> | eASSETS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg,#000,#0d6efd); font-family:Poppins,sans-serif; height:100vh; display:flex; align-items:center; justify-content:center; }
.card { padding:2rem; border-radius:15px; backdrop-filter: blur(10px); background-color: rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; width:100%; max-width:400px; }
.form-label { color:#ddd; }
.form-control { background-color: rgba(255,255,255,0.1); border:none; color:white; }
.form-control:focus { background-color: rgba(255,255,255,0.15); color:white; box-shadow:none; }
.btn-primary { background-color:black; font-weight:bold; border:none; }
.btn-primary:hover { background-color:#0b5ed7; }
.alert { width:100%; }
.logo { display:block; margin:0 auto 15px; width:150px; height:auto; }
</style>
</head>
<body>
<div class="card text-center">
    <img src="assets/images/logo_eAsset.png" class="logo" alt="Logo">

    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php else: ?>
        <h4 class="mb-3"><?= ($purpose === 'activate') ? 'Activate Your Account' : 'Reset Your Password' ?></h4>
        <form method="POST">
            <div class="mb-3 text-start">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter new password" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
            </div>
            <button class="btn btn-primary w-100"><?= ($purpose === 'activate') ? 'Activate' : 'Reset' ?></button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>

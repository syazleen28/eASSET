<?php
session_start();
require_once 'config/database.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email) {
        $error = "Please enter your email address.";
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "No account found with that email address.";
        } else {
            // Generate reset token
            $token = bin2hex(random_bytes(16));

            // Save token in DB
            $stmt = $pdo->prepare("UPDATE users SET activation_token = :token WHERE id = :id");
            $stmt->execute([':token' => $token, ':id' => $user['id']]);

            // Send reset email
            $reset_link = "http://localhost/eASSET/activate.php?token=$token";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'nursyazleen28032003@gmail.com'; // your Gmail
                $mail->Password   = 'ykhu ntiv ttcu vofu';    // Gmail App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('your_email@gmail.com', 'eASSETS Admin');
                $mail->addAddress($email, $user['staff_name']);

                $mail->Subject = 'Reset Your Password';
                $mail->Body    = "Hello {$user['staff_name']},\n\nClick the link below to reset your password:\n$reset_link\n\nIf you didn't request this, please ignore this email.";

                $mail->send();
                $success = "A password reset link has been sent to your email.";
            } catch (Exception $e) {
                $error = "Mailer Error: " . $mail->ErrorInfo;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password | eASSETS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg,#000,#0d6efd); font-family:Poppins,sans-serif; height:100vh; display:flex; align-items:center; justify-content:center; }
.card { padding:2rem; border-radius:15px; backdrop-filter: blur(10px); background-color: rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; width:100%; max-width:400px; text-align:center; }
.form-label { color:#ddd; }
.form-control { background-color: rgba(255,255,255,0.1); border:none; color:white; }
.form-control:focus { background-color: rgba(255,255,255,0.15); color:white; box-shadow:none; }
.btn-primary { background-color:black; font-weight:bold; border:none; }
.btn-primary:hover { background-color:#0b5ed7; }
.alert { width:100%; }
.logo { display:block; margin:0 auto 15px; width:150px; height:auto; }
a { color:white; text-decoration:underline; }
</style>
</head>
<body>
<div class="card">
    <img src="assets/images/logo_eAsset.png" class="logo" alt="Logo">

    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php else: ?>
        <h4 class="mb-3">Forgot Password</h4>
        <form method="POST">
            <div class="mb-3 text-start">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <button class="btn btn-primary w-100">Send Reset Link</button>
        </form>
        <div class="mt-3">
            <a href="login.php">Back to Login</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>

<?php
session_start();
require_once 'config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $userid = trim($_POST['userid']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if(empty($fullname) || empty($email) || empty($userid) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    }

    if($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (fullname, email, userid, password) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$fullname, $email, $userid, $hashed_password]);
            $_SESSION['success'] = "Registration successful. You can now login.";
            header("Location: login.php");
            exit();
        } catch(PDOException $e) {
            if($e->getCode() == 23000) {
                $errors[] = "Email or User ID already exists.";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - eAssets Management System</title>
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
    }
    .register-card {
        backdrop-filter: blur(15px);
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        color: white;
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
    .alert { width: 100%; max-width: 400px; }
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

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card register-card p-4">
                <div class="card-body text-center">
                    <img src="assets/images/logo_eAsset.png" alt="Logo" class="logo">

                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3 text-start">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="fullname" placeholder="Enter your full name" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label">User ID</label>
                            <input type="text" class="form-control" name="userid" placeholder="Enter your user id" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                                <span class="input-group-text" id="togglePassword" style="cursor:pointer;">
                                    <i class="bi bi-eye-fill"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                                <span class="input-group-text" id="toggleConfirmPassword" style="cursor:pointer;">
                                    <i class="bi bi-eye-fill"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Register</button>

                        <div class="mt-3 text-center">
                            <a href="login.php"> Already have an account? Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');
togglePassword.addEventListener('click', function () {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    const icon = this.querySelector('i');
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
});

const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
const confirmPassword = document.querySelector('#confirm_password');
toggleConfirmPassword.addEventListener('click', function () {
    const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
    confirmPassword.setAttribute('type', type);
    const icon = this.querySelector('i');
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
});
</script>

</body>
</html>

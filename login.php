<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = $_POST['userid'] ?? '';
    $password = $_POST['password'] ?? '';

    // Fetch user by userid
    $stmt = $conn->prepare("SELECT * FROM users WHERE userid = ?");
    $stmt->execute([$userid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Login success
        $_SESSION['userid'] = $user['userid'];
        $_SESSION['fullname'] = $user['fullname'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid User ID or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - eAssets Management System</title>
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
    .logo { display: block; margin: 0 auto 10px auto; width: 300px; height: auto; }
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
            <div class="card login-card p-4">
                <div class="card-body text-center">
                    <img src="assets/images/logo_eAsset.png" alt="Logo" class="logo">

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3 text-start">
                            <label for="userid" class="form-label">User ID</label>
                            <input type="text" class="form-control" id="userid" name="userid" placeholder="Enter your User ID" required>
                        </div>

                        <div class="mb-3 text-start">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                <span class="input-group-text" id="togglePassword" style="cursor:pointer;">
                                    <i class="bi bi-eye-fill"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>

                        <!-- Link to Register -->
                        <div class="mt-3 text-center">
                            <a href="register.php">Don't have an account? Register</a>
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
</script>

</body>
</html>

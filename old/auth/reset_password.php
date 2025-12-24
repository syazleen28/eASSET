<?php
require_once '../config/database.php';

$token = $_GET['token'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Passwords do not match";
    } else {
        $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE users 
            SET password = :password,
                status = 'active',
                activation_token = NULL
            WHERE activation_token = :token
        ");

        $stmt->execute([
            ':password' => $hashed,
            ':token'    => $token
        ]);

        header("Location: ../login.php?activated=1");
        exit();
    }
}
?>
<form method="POST" class="card p-4 shadow-sm">
    <h5 class="mb-3">Set Your Password</h5>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <input type="password" name="password"
           class="form-control mb-2"
           placeholder="New Password" required>

    <input type="password" name="confirm_password"
           class="form-control mb-3"
           placeholder="Confirm Password" required>

    <button class="btn btn-success w-100">Activate Account</button>
</form>

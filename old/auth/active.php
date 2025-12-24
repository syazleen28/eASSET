<?php
require_once '../config/database.php';

$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare("
    SELECT id FROM users 
    WHERE activation_token = :token AND status = 'pending'
");
$stmt->execute([':token' => $token]);

if ($stmt->rowCount() === 1) {
    header("Location: reset_password.php?token=$token");
    exit();
} else {
    echo "Invalid or expired activation link.";
}

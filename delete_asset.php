<?php
session_start();
require_once 'config/database.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle POST request from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];

    // Optional: Check if the asset exists
    $stmt = $pdo->prepare("SELECT * FROM assets WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);
    $asset = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($asset) {
        $stmt = $pdo->prepare("DELETE FROM assets WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }

    // Redirect back to index_asset.php with success message
    header("Location: index_asset.php?delete=1");
    exit();
}

// If accessed directly, just redirect
header("Location: index_asset.php");
exit();
?>

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

    // Check if the supplier exists (optional)
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($supplier) {
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }

    // Redirect back to config_supplier.php with success message
    header("Location: config_supplier.php?delete=1");
    exit();
}

// If accessed directly, just redirect
header("Location: config_supplier.php");
exit();
?>

<?php
/*
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delete Supplier | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

    <div class="mb-4">
        <h5>CONFIGURATION &gt; Supplier &gt; Delete</h5>
    </div>

    <div class="card">
        <div class="card-body text-center">
            <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
            <p class="mt-3">
                Are you sure to delete this supplier?
            </p>

            <form method="post" style="display:inline-block;">
                <input type="hidden" name="delete_id" value="<?= isset($_GET['id']) ? (int)$_GET['id'] : '' ?>">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>

            <a href="config_supplier.php" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</div>

</body>
</html>
*/
?>


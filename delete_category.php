<?php
session_start();
require_once 'config/database.php';

// Protect page
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

// Handle POST request from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];

    // Check if the category exists (optional)
    $stmt = $pdo->prepare("SELECT * FROM asset_categories WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        $stmt = $pdo->prepare("DELETE FROM asset_categories WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }

    // Redirect back to config_category.php with success message
    header("Location: config_category.php?delete=1");
    exit();
}

// If accessed directly, just redirect
header("Location: config_category.php");
exit();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delete Asset Category | eAssets</title>
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
        <h5>CONFIGURATION &gt; Asset Category &gt; Delete</h5>
    </div>

    <div class="card">
        <div class="card-body text-center">
            <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
            <p class="mt-3">
                Are you sure you want to delete the category: 
                <strong><?= htmlspecialchars($category['category_name']) ?></strong>?
            </p>

            <form method="post" style="display:inline-block;">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>

            <a href="config_category.php" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</div>

</body>
</html>

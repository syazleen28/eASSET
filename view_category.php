<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: ../login.php");
    exit();
}

// validate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: config_category.php");
    exit();
}

$id = $_GET['id'];

// fetch category
$stmt = $pdo->prepare("SELECT * FROM asset_categories WHERE id = :id");
$stmt->execute([':id' => $id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: config_category.php");
    exit();
}

// success flag
$showSuccess = (isset($_GET['success']) && $_GET['success'] == 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Asset Category | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

    <!-- SUCCESS MESSAGE -->
    <?php if ($showSuccess): ?>
    <div class="alert alert-success d-flex align-items-center mb-3">
        <i class="bi bi-check-circle-fill me-2"></i>
        <div>
            <strong>Successful</strong><br>
            Data saved successfully !
        </div>
    </div>
    <?php endif; ?>

    <div class="mb-4">
        <h4>CONFIGURATION &gt; Asset Category &gt; View</h4>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Category Name :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($category['category_name']) ?>" readonly>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Description :</label>
                <div class="col-sm-10">
                    <textarea class="form-control" rows="4" readonly><?= htmlspecialchars($category['description']) ?></textarea>
                </div>
            </div>

            <div class="text-end">
                <a href="edit_category.php?id=<?= $category['id'] ?>" class="btn btn-primary">Update</a>
                <a href="delete_category.php?id=<?= $category['id'] ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('Are you sure to delete this category?');">
                   Delete
                </a>
                <a href="config_category.php" class="btn btn-secondary">Back</a>
            </div>

        </div>
    </div>

</div>

<!-- AUTO HIDE SUCCESS MESSAGE -->
<script>
setTimeout(() => {
    const alert = document.querySelector('.alert-success');
    if (alert) alert.style.display = 'none';
}, 3000);
</script>

</body>
</html>

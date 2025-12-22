<?php
session_start();
require_once 'config/database.php'; // PDO connection

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

// Fetch all asset categories
try {
    $stmt = $pdo->prepare("SELECT * FROM asset_categories ORDER BY id DESC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Asset Categories | eAssets</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background-color: #f5f5f5; }
.table-actions i { cursor: pointer; margin-right: 5px; }

</style>
</head>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <h4>CONFIGURATION &gt; Asset Category</h4>
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Asset Category</span>
            <a href="add_category.php" class="btn btn-primary btn-sm">New Record</a>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    Show 
                    <select class="form-select d-inline-block w-auto">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    records per page
                </div>
                <div>
                    Search: <input type="text" class="form-control form-control-sm d-inline-block w-auto">
                </div>
            </div>
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories): ?>
                        <?php foreach ($categories as $index => $cat): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($cat['category_name']) ?></td>
                                <td><?= htmlspecialchars($cat['description'] ?: 'No Description') ?></td>
                                <td class="table-actions">
                                    <a href="view_category.php?id=<?= $cat['id'] ?>" class="btn btn-info btn-sm"><i class="bi bi-eye-fill"></i></a>
                                    <a href="edit_category.php?id=<?= $cat['id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-fill"></i></a>
                                    <a href="delete_category.php?id=<?= $cat['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="bi bi-trash-fill"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No categories found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div>
                Page 1 of 1
                <nav class="d-inline-block float-end">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">&lt;</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item disabled"><a class="page-link" href="#">&gt;</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

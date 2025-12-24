<?php
session_start();
require_once 'config/database.php'; // PDO connection

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM assets WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);
    header("Location: index_asset.php?delete=1");
    exit();
}

// Fetch all assets with category and supplier info
try {
    $stmt = $pdo->prepare("
        SELECT a.*, c.category_name, s.supplier_name
        FROM assets a
        LEFT JOIN asset_categories c ON a.category_id = c.id
        LEFT JOIN suppliers s ON a.supplier = s.supplier_name
        ORDER BY a.id DESC
    ");
    $stmt->execute();
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching assets: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Asset List | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

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

<?php if (isset($_GET['delete']) && $_GET['delete'] == 1): ?>
    <div class="alert alert-success d-flex align-items-center mb-3" id="deleteAlert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <div>
            <strong>Deleted</strong><br>
            Asset deleted successfully!
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.getElementById('deleteAlert');
            if (alert) {
                setTimeout(() => {
                    alert.style.transition = "opacity 0.5s";
                    alert.style.opacity = "0";
                    setTimeout(() => {
                        alert.remove();
                        window.history.replaceState({}, document.title, "index_asset.php");
                    }, 500);
                }, 3000);
            }
        });
    </script>
<?php endif; ?>

<h5>ASSET MANAGEMENT &gt; Asset List</h5>
<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Assets</span>
        <a href="add_asset.php" class="btn btn-primary btn-sm">New Record</a>
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
                    <th>Asset Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($assets): ?>
                    <?php foreach ($assets as $index => $a): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($a['asset_name']) ?></td>
                            <td><?= htmlspecialchars($a['category_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($a['supplier'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($a['asset_status'] ?: '-') ?></td>
                            <td class="text-center">
                                <div class="action-group mx-auto">
                                    <a href="view_asset.php?id=<?= $a['id'] ?>" class="action-btn view" title="View">
                                        <i class="bi bi-search"></i>
                                    </a>
                                    <a href="edit_asset.php?id=<?= $a['id'] ?>" class="action-btn edit" title="Edit">
                                        <i class="bi bi-pen"></i>
                                    </a>
                                    <button type="button"
                                            class="action-btn delete deleteBtn"
                                            data-id="<?= $a['id'] ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No assets found</td>
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

<!-- DELETE CONFIRM MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
        <p class="mt-3">Are you sure to delete?</p>

        <form method="post" id="deleteForm">
            <input type="hidden" name="delete_id" id="deleteId">
            <button type="submit" class="btn btn-danger">Delete</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Pass asset id to modal
const deleteBtns = document.querySelectorAll('.deleteBtn');
deleteBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        document.getElementById('deleteId').value = id;
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

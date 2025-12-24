<?php
session_start();
require_once 'config/database.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);
    header("Location: config_supplier.php?delete=1");
    exit();
}

// Fetch all suppliers
try {
    $stmt = $pdo->prepare("SELECT * FROM suppliers ORDER BY id DESC");
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching suppliers: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Suppliers | eAssets</title>
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
            Supplier deleted successfully!
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
                        window.history.replaceState({}, document.title, "config_supplier.php");
                    }, 500);
                }, 3000);
            }
        });
    </script>
<?php endif; ?>

<h5>CONFIGURATION &gt; Suppliers</h5>
<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Suppliers</span>
        <a href="add_supplier.php" class="btn btn-primary btn-sm">New Record</a>
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>No.</th>
                    <th>Supplier Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($suppliers): ?>
                    <?php foreach ($suppliers as $index => $sup): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($sup['supplier_name']) ?></td>
                        <td><?= htmlspecialchars($sup['address'] ?: 'No Address') ?></td>
                        <td><?= htmlspecialchars($sup['phone'] ?: '-') ?></td>
                        <td class="text-center">
                            <div class="action-group mx-auto">
                                <a href="view_supplier.php?id=<?= $sup['id'] ?>" class="action-btn view" title="View">
                                    <i class="bi bi-search"></i>
                                </a>
                                <a href="edit_supplier.php?id=<?= $sup['id'] ?>" class="action-btn edit" title="Edit">
                                    <i class="bi bi-pen"></i>
                                </a>
                                <button type="button"
                                        class="action-btn delete deleteBtn"
                                        data-id="<?= $sup['id'] ?>"
                                        data-name="<?= htmlspecialchars($sup['supplier_name']) ?>"
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
                        <td colspan="5" class="text-center">No suppliers found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
</div>
<?php include 'includes/footer.php'; ?>
<script>
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

<?php
session_start();
require_once 'config/database.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ===== Pagination settings =====
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) $page = 1;
if (!in_array($limit, [10, 25, 50])) $limit = 10;

$offset = ($page - 1) * $limit;

// ===== Count total suppliers =====
$countStmt = $pdo->query("SELECT COUNT(*) FROM suppliers");
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// ===== Fetch suppliers with LIMIT & OFFSET =====
try {
    $stmt = $pdo->prepare("
        SELECT * FROM suppliers
        ORDER BY id DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
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
        <div class="d-flex justify-content-between mb-3">
            <!-- Records per page -->
            <form method="get">
                Show
                <select name="limit"
                        class="form-select d-inline-block w-auto"
                        onchange="this.form.submit()">
                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                </select>
                records per page
            </form>

            <!-- Live search -->
            <div>
                Search:
                <input type="text" id="searchInput" class="form-control form-control-sm d-inline-block w-auto">
            </div>
        </div>

        <table id="supplierTable" class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>No.</th>
                    <th>Supplier Name</th>
                    <th>Address</th>
                    
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($suppliers): ?>
                    <?php foreach ($suppliers as $index => $sup): ?>
                    <tr>
                        <td><?= $offset + $index + 1 ?></td>
                        <td><?= htmlspecialchars($sup['supplier_name']) ?></td>
                        <td><?= htmlspecialchars($sup['address'] ?: 'No Address') ?></td>
                        
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

        <!-- Pagination -->
        <div>
            Page <?= $page ?> of <?= $totalPages ?>
            <nav class="d-inline-block float-end">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>">&lt;</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&limit=<?= $limit ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>">&gt;</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
</div>

<!-- DELETE CONFIRM MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
        <p class="mt-3">Are you sure to delete?</p>
        <form method="post" action="delete_supplier.php">
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
// Set delete modal ID
const deleteBtns = document.querySelectorAll('.deleteBtn');
deleteBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('deleteId').value = btn.getAttribute('data-id');
    });
});

// Live search
const searchInput = document.getElementById('searchInput');
const table = document.getElementById('supplierTable');
const tbody = table.getElementsByTagName('tbody')[0];
const rows = tbody.getElementsByTagName('tr');

searchInput.addEventListener('keyup', function () {
    const filter = searchInput.value.toLowerCase();

    Array.from(rows).forEach(row => {
        const supplierName = row.cells[1].textContent.toLowerCase();
        const address      = row.cells[2].textContent.toLowerCase();
        

        if (supplierName.includes(filter) || address.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

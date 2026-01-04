<?php
session_start();
require_once 'config/database.php';

// Protect page
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

// ===== Count total assets =====
$countStmt = $pdo->query("SELECT COUNT(*) FROM assets");
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;

// ===== Fetch assets with category name =====
try {
    $stmt = $pdo->prepare("
        SELECT a.id, a.asset_code, a.asset_name, a.asset_status, a.serial_number, a.supplier,
               c.category_name
        FROM assets a
        JOIN asset_categories c ON c.id = a.category_id
        ORDER BY a.id DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
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
<title>Assets | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">

<style>
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

<h5>ASSETS ></h5>

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Assets</span>
        <a href="add_asset.php" class="btn btn-primary btn-sm">New Record</a>
    </div>

    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <!-- Records per page -->
            <form method="get">
                Show
                <select name="limit" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
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

        <table id="assetTable" class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>No.</th>
                    <th>Asset Code</th>
                    <th>Asset Name</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Serial No.</th>
                    <th>Supplier</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($assets): ?>
                <?php foreach ($assets as $index => $a): ?>
                <tr>
                    <td><?= $offset + $index + 1 ?></td>
                    <td><?= htmlspecialchars($a['asset_code']) ?></td>
                    <td><?= htmlspecialchars($a['asset_name']) ?></td>
                    <td><?= htmlspecialchars($a['category_name']) ?></td>
                    <td><?= htmlspecialchars($a['asset_status']) ?></td>
                    <td><?= htmlspecialchars($a['serial_number'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($a['supplier']) ?></td>

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
                    <td colspan="8" class="text-center">No assets found</td>
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

<!-- DELETE MODAL (you can create delete_asset.php later) -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
        <p class="mt-3">Are you sure to delete?</p>

        <form method="post" action="delete_asset.php">
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
document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('deleteId').value = btn.getAttribute('data-id');
    });
});

// Live search
const searchInput = document.getElementById('searchInput');
const rows = document.querySelectorAll('#assetTable tbody tr');

searchInput.addEventListener('keyup', function () {
    const filter = searchInput.value.toLowerCase();
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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

// ===== Count total maintenance assets =====
$countStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM assets
    WHERE asset_status = 'Maintenance'
");
$countStmt->execute();
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;

// ===== Fetch maintenance assets =====
try {
   $stmt = $pdo->prepare("
    SELECT 
        a.id,
        a.asset_code,
        a.asset_name,
        a.asset_status,
        c.category_name
    FROM assets a
    JOIN asset_categories c ON c.id = a.category_id
    WHERE a.asset_status = 'Maintenance'
    ORDER BY a.id DESC
    LIMIT :limit OFFSET :offset
");

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching maintenance assets: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Maintenance Assets | eAssets</title>
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

<h5>MAINTENANCE > </h5>

<div class="card mt-3">
    <div class="card-header">
        <span>Assets Under Maintenance</span>
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
        <td>
            <span class="badge bg-warning text-dark">
                <?= htmlspecialchars($a['asset_status']) ?>
            </span>
        </td>
        <td class="text-center">
            <div class="action-group mx-auto">
                <a href="view_maintenance.php?id=<?= $a['id'] ?>" class="action-btn view" title="View">
                    <i class="bi bi-search"></i>
                </a>
                <a href="edit_maintenance.php?id=<?= $a['id'] ?>&tab=maintenance" class="action-btn edit" title="Edit">
    <i class="bi bi-pen"></i>
</a>

            </div>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6" class="text-center">No maintenance assets found</td>
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

<?php include 'includes/footer.php'; ?>

<script>
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

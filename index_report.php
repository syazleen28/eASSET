<?php
session_start();
require_once 'config/database.php';

/* ==========================
   AUTH
========================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ==========================
   LOAD CATEGORIES
========================== */
$catStmt = $pdo->query("SELECT id, category_name FROM asset_categories ORDER BY category_name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
   FILTER INPUTS
========================== */
$reportType = $_GET['report_type'] ?? '';
$fromDate   = $_GET['from_date'] ?? '';
$toDate     = $_GET['to_date'] ?? '';

$categoryIds = $_GET['category_id'] ?? [];
$statuses    = $_GET['asset_status'] ?? [];

/* FORCE ARRAY */
if (!is_array($categoryIds) && $categoryIds !== '') {
    $categoryIds = [$categoryIds];
}
if (!is_array($statuses) && $statuses !== '') {
    $statuses = [$statuses];
}

$data = [];

/* ==========================
   BUILD QUERY
========================== */
if (!empty($reportType)) {

    /* ===== ASSET REPORT ===== */
    if ($reportType === 'asset') {

        $sql = "
            SELECT 
                a.asset_code,
                a.asset_name,
                c.category_name,
                a.asset_status
            FROM assets a
            JOIN asset_categories c ON c.id = a.category_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($categoryIds)) {
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $sql .= " AND a.category_id IN ($placeholders)";
            $params = array_merge($params, $categoryIds);
        }

        if (!empty($statuses)) {
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $sql .= " AND a.asset_status IN ($placeholders)";
            $params = array_merge($params, $statuses);
        }

        if ($fromDate && $toDate) {
            $sql .= " AND a.purchase_date BETWEEN ? AND ?";
            $params[] = $fromDate;
            $params[] = $toDate;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===== MAINTENANCE REPORT ===== */
    if ($reportType === 'maintenance') {

        $sql = "
            SELECT 
                a.asset_code,
                a.asset_name,
                c.category_name,
                'Maintenance' AS asset_status
            FROM asset_maintenance m
            JOIN assets a ON a.id = m.asset_id
            JOIN asset_categories c ON c.id = a.category_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($categoryIds)) {
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $sql .= " AND a.category_id IN ($placeholders)";
            $params = array_merge($params, $categoryIds);
        }

        if ($fromDate && $toDate) {
            $sql .= " AND m.issue_date BETWEEN ? AND ?";
            $params[] = $fromDate;
            $params[] = $toDate;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">

<style>
.dropdown-menu { max-height: 200px; overflow: auto; }
</style>
</head>

<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
<h5>REPORT ></h5>

<!-- ================= FILTER ================= -->
<div class="card mt-3 mb-3">
    <div class="card-header fw-bold">Report </div>
    <div class="card-body">
        <form method="get">
            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label fw-bold">Report Type</label>
                    <select name="report_type" class="form-select" required>
                        <option value="">-- Select --</option>
                        <option value="asset" <?= $reportType=='asset'?'selected':'' ?>>Asset Report</option>
                        <option value="maintenance" <?= $reportType=='maintenance'?'selected':'' ?>>Maintenance Report</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">From Date</label>
                    <input type="date" name="from_date" value="<?= $fromDate ?>" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">To Date</label>
                    <input type="date" name="to_date" value="<?= $toDate ?>" class="form-control">
                </div>

                            <!-- CATEGORY DROPDOWN -->
            <div class="col-md-6">
                <label class="form-label fw-bold">Asset Category</label>
                <div class="dropdown">
                    <button class="form-select text-start" type="button" data-bs-toggle="dropdown">
                        Select Asset Category
                    </button>

                    <ul class="dropdown-menu p-2">
                        <li>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cat_all">
                                <label class="form-check-label fw-bold">Select All</label>
                            </div>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <div class="form-check">
                                <input class="form-check-input cat_checkbox" type="checkbox" name="category_id[]" value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $categoryIds) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $cat['category_name'] ?></label>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- STATUS DROPDOWN -->
            <div class="col-md-6">
                <label class="form-label fw-bold">Asset Status</label>
                <div class="dropdown">
                    <button class="form-select text-start " type="button" data-bs-toggle="dropdown">
                        Select Asset Status
                    </button>
                    <ul class="dropdown-menu p-2">
                        <li>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="status_all">
                                <label class="form-check-label fw-bold">Select All</label>
                            </div>
                        </li>
                        <?php
                        $statusList = ['Available','In Use','Damaged','Maintenance'];
                        foreach ($statusList as $st):
                        ?>
                        <li>
                            <div class="form-check">
                                <input class="form-check-input status_checkbox" type="checkbox" name="asset_status[]" value="<?= $st ?>" <?= in_array($st, $statuses) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $st ?></label>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>


                <div class="col-md-12 text-end">
                    <button class="btn btn-primary mt-3">Generate Report</button>
                </div>

            </div>
        </form>
    </div>
</div>

<!-- ================= RESULT ================= -->
<div class="card">
    <div class="card-header fw-bold">Report Result</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Asset Code</th>
                        <th>Asset Name</th>
                        <th>Category</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($data): $no=1; foreach ($data as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row['asset_code'] ?></td>
                        <td><?= $row['asset_name'] ?></td>
                        <td><?= $row['category_name'] ?></td>
                        <td>
                            <span class="badge bg-<?=
                                $row['asset_status']=='Available'?'success':
                                ($row['asset_status']=='Maintenance'?'warning text-dark':
                                ($row['asset_status']=='Damaged'?'danger':'primary'))
                            ?>">
                                <?= $row['asset_status'] ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Please select filter and click Generate Report</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ===== Select All for Category =====
const catAll = document.getElementById('cat_all');
const catBoxes = document.querySelectorAll('.cat_checkbox');
catAll.addEventListener('change', function() {
    catBoxes.forEach(cb => cb.checked = catAll.checked);
});

// ===== Select All for Status =====
const statusAll = document.getElementById('status_all');
const statusBoxes = document.querySelectorAll('.status_checkbox');
statusAll.addEventListener('change', function() {
    statusBoxes.forEach(cb => cb.checked = statusAll.checked);
});
</script>

</body>
</html>

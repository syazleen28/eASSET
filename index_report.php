<?php
session_start();
require_once 'config/database.php';

/* ==========================
   AUTHENTICATION
========================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ==========================
   LOAD ASSET CATEGORIES
========================== */
$catStmt = $pdo->query("SELECT id, category_name FROM asset_categories ORDER BY category_name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
   GET FILTER INPUTS
========================== */
$reportType = $_GET['report_type'] ?? '';
$fromDate   = $_GET['from_date'] ?? '';
$toDate     = $_GET['to_date'] ?? '';
$categoryIds = $_GET['category_id'] ?? [];
$statuses    = $_GET['asset_status'] ?? [];

/* FORCE ARRAYS */
if (!is_array($categoryIds) && $categoryIds !== '') $categoryIds = [$categoryIds];
if (!is_array($statuses) && $statuses !== '') $statuses = [$statuses];

$data = [];

/* ==========================
   BUILD QUERY
========================== */
if (!empty($reportType)) {

    if ($reportType === 'asset') {
        // Asset Report
        $sql = "
    SELECT 
        a.asset_code,
        c.category_name,
        a.serial_number,
        a.asset_name,
        a.asset_status,
        a.purchase_date,
        a.assigned_user,
        a.location,
        a.spec,
        a.drive_info
    FROM assets a
    LEFT JOIN asset_categories c ON c.id = a.category_id
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

    if ($reportType === 'maintenance') {
        // Maintenance Report (with issue)
        $sql = "
            SELECT 
                c.category_name,
                a.serial_number,
                a.asset_name,
                a.asset_status,
                m.issue_occurred
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

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

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
                        <button class="form-select text-start" type="button" data-bs-toggle="dropdown">
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
    <?php if($data): ?>
    <a href="export_report.php?<?= $_SERVER['QUERY_STRING'] ?>" class="btn btn-success mt-3">
        <i class="bi bi-file-earmark-excel"></i> Export to Excel
    </a>
    <?php endif; ?>
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
            <table id="assetReportTable" class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <?php if($reportType==='asset'): ?>
                            <th>No</th>
                            <th>Asset Code</th>
                            <th>Category</th>
                            <th>Serial Number</th>
                            <th>Asset Name</th>
                            <th>Status</th>
                            <th>Purchase Date</th>
                            <th>Assigned User</th>
                            <th>Location</th>
                             <th>Spec</th>
    <th>Drive Information</th>
                        <?php elseif($reportType==='maintenance'): ?>
                            <th>No</th>
                            <th>Category</th>
                            <th>Serial Number</th>
                            <th>Asset Name</th>
                            <th>Status</th>
                            <th>Issue / Problem</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php if ($data): $no=1; foreach ($data as $row): ?>
                    <tr>
                        <?php if($reportType==='asset'): ?>
                            <td><?= $no++ ?></td>
                            <td><?= $row['asset_code'] ?></td>
                            <td><?= $row['category_name'] ?></td>
                            <td><?= $row['serial_number'] ?></td>
                            <td><?= $row['asset_name'] ?></td>
                            <td>
                                <span class="badge bg-<?=
                                    $row['asset_status']=='Available'?'success':
                                    ($row['asset_status']=='Maintenance'?'warning text-dark':
                                    ($row['asset_status']=='Damaged'?'danger':'primary'))
                                ?>">
                                    <?= $row['asset_status'] ?>
                                </span>
                            </td>
                            <td><?= $row['purchase_date'] ?></td>
                            <td><?= $row['assigned_user'] ?></td>
                            <td><?= $row['location'] ?></td>
                            <td><?= $row['spec'] ?></td>
    <td><?= $row['drive_info'] ?></td>

                        <?php elseif($reportType==='maintenance'): ?>
                            <td><?= $no++ ?></td>
                            <td><?= $row['category_name'] ?></td>
                            <td><?= $row['serial_number'] ?></td>
                            <td><?= $row['asset_name'] ?></td>
                            <td>
                                <span class="badge bg-warning text-dark">Maintenance</span>
                            </td>
                            <td><?= $row['issue_occurred'] ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <?php if($reportType==='asset'): ?>
                              <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        <?php elseif($reportType==='maintenance'): ?>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        <?php endif; ?>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

<?php include 'includes/footer.php'; ?>

<!-- JS Bootstrap & DataTables -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

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

// ===== DataTables Sorting for Asset Report =====
$(document).ready(function() {
    <?php if($reportType==='asset'): ?>
    $('#assetReportTable').DataTable({
        "order": [[6, "desc"]],
        "columnDefs": [
            { "orderable": true, "targets": [5, 6] },
            { "orderable": false, "targets": "_all" }
        ]
    });
    <?php endif; ?>
});
</script>

</body>
</html>

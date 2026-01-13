<?php
session_start();
require_once 'config/database.php';

$activeTab = $_GET['tab'] ?? 'asset';

/* ========================= PROTECT PAGE ========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ========================= VALIDATE ASSET ID ========================= */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index_maintenance.php");
    exit();
}

$asset_id = (int) $_GET['id'];
$showSuccess = (isset($_GET['success']) && $_GET['success'] == 1);

/* ========================= FETCH ASSET INFO ========================= */
$stmt = $pdo->prepare("
    SELECT a.*, c.category_name
    FROM assets a
    JOIN asset_categories c ON c.id = a.category_id
    WHERE a.id = ?
");
$stmt->execute([$asset_id]);
$asset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asset) {
    header("Location: index_maintenance.php");
    exit();
}

/* ========================= FETCH LATEST MAINTENANCE ========================= */
$mStmt = $pdo->prepare("
    SELECT *
    FROM asset_maintenance
    WHERE asset_id = ?
      AND date_completed IS NULL
    ORDER BY created_at DESC
    LIMIT 1
");

$mStmt->execute([$asset_id]);
$maintenance = $mStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Maintenance | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">
<style>
.nav-tabs .nav-link { color: #000; font-weight: 500; }
.nav-tabs .nav-link.active { color: #0d6efd !important; font-weight: 600; }
.nav-tabs .nav-link:hover { color: #000; }
</style>
</head>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <h5>MAINTENANCE &gt; View</h5>

    <?php if ($showSuccess): ?>
    <div class="alert alert-success d-flex align-items-center mb-3">
        <i class="bi bi-check-circle-fill me-2"></i>
        <div>
            <strong>Successful!</strong><br>Maintenance record saved successfully.
        </div>
    </div>
    <?php endif; ?>

    <div class="card mt-3">
        <div class="card-body">
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <button class="nav-link <?= $activeTab === 'asset' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#assetTab">
                        Asset Information
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $activeTab === 'maintenance' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#maintenanceTab">
                        Issues Information
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $activeTab === 'post-maintenance' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#postMaintenanceTab">
                        Maintenance Information
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- ================= TAB 1 : ASSET INFO ================= -->
<div class="tab-pane fade <?= $activeTab === 'asset' ? 'show active' : '' ?>" id="assetTab">
    <h6 class="mb-3 fw-bold">Asset Information</h6>

    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Asset Code :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['asset_code']) ?>" readonly>
        </div>
        <label class="col-sm-2 col-form-label">Asset Category :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['category_name'] ?? '-') ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Asset Status :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['asset_status']) ?>" readonly>
        </div>
        <label class="col-sm-2 col-form-label">Asset Name / Model :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['asset_name']) ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Brand :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['brand'] ?? '-') ?>" readonly>
        </div>
        <label class="col-sm-2 col-form-label">Serial Number :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['serial_number'] ?? '-') ?>" readonly>
        </div>
    </div>

    <h6 class="mb-3 mt-4 fw-bold">Purchase Information</h6>
    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Supplier :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['supplier'] ?? '-') ?>" readonly>
        </div>
        <label class="col-sm-2 col-form-label">Purchase Date :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['purchase_date'] ?? '-') ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Purchase Cost (RM) :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['purchase_cost'] ?? '-') ?>" readonly>
        </div>
    </div>

    <h6 class="mb-3 mt-4 fw-bold">Manufacture & Warranty</h6>
    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Manufacture Date :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['manufacture_date'] ?? '-') ?>" readonly>
        </div>
        <label class="col-sm-2 col-form-label">Warranty :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['warranty'] ?? '-') ?>" readonly>
        </div>
    </div>

    <h6 class="mb-3 mt-4 fw-bold">Assignment Information</h6>
    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Location :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['location'] ?? '-') ?>" readonly>
        </div>
        <label class="col-sm-2 col-form-label">Assigned User :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['assigned_user'] ?? '-') ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Description :</label>
        <div class="col-sm-10">
            <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($asset['description'] ?? '-') ?></textarea>
        </div>
    </div>

    <!-- ================= SYSTEM INFORMATION ================= -->
    <?php 
    $hasSystemInfo = !empty($asset['os']) || !empty($asset['os_version']) || !empty($asset['drive_info']) || !empty($asset['spec']);
    if ($hasSystemInfo): 
    ?>
    <h6 class="mb-3 mt-4 fw-bold">System Information</h6>

    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Operating System :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['os'] ?? '-') ?>" readonly>
        </div>
        <label class="col-sm-2 col-form-label">OS Version :</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['os_version'] ?? '-') ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Specifications :</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['spec'] ?? '-') ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Drive Information :</label>
        <div class="col-sm-10">
            <textarea class="form-control" rows="2" readonly><?= htmlspecialchars($asset['drive_info'] ?? '-') ?></textarea>
        </div>
    </div>
    <?php endif; ?>
</div>
<!-- ================= END ASSET INFO TAB ================= -->

                <!-- ================= TAB 2 : MAINTENANCE INFO ================= -->
                <div class="tab-pane fade <?= $activeTab === 'maintenance' ? 'show active' : '' ?>" id="maintenanceTab">
                    <div class="mb-3">
                        <label class="form-label">Issue Occurred :</label>
                        <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($maintenance['issue_occurred'] ?? '-') ?></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <label class="form-label">Issue Date :</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($maintenance['issue_date'] ?? '-') ?>" readonly>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Reported By :</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($maintenance['reported_by'] ?? '-') ?>" readonly>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Maintenance Location :</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($maintenance['maintenance_location'] ?? '-') ?>" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Notes :</label>
                        <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($maintenance['additional_notes'] ?? '-') ?></textarea>
                    </div>
                    <div class="text-end">
                        <a href="edit_maintenance.php?id=<?= $asset_id ?>&tab=maintenance" class="btn btn-primary">Update</a>
                        <a href="index_maintenance.php" class="btn btn-secondary">Back</a>
                    </div>
                </div>

                <!-- ================= TAB 3 : POST-MAINTENANCE ================= -->
                <div class="tab-pane fade <?= $activeTab === 'post-maintenance' ? 'show active' : '' ?>" id="postMaintenanceTab">
                    <form method="post" id="postMaintenanceForm">
                        <div class="mb-3">
                            <label class="form-label">Action Taken :</label>
                            <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($maintenance['action_taken'] ?? '-') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Completed :</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($maintenance['date_completed'] ?? '-') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Asset Status :</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($asset['asset_status'] ?? '-') ?>" readonly>
                        </div>
                        <div class="text-end">
                            <a href="edit_maintenance.php?id=<?= $asset_id ?>&tab=post-maintenance" class="btn btn-primary">Update</a>
                            <a href="index_maintenance.php" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end tab-content -->
        </div>
        <!-- end card-body -->
    </div>
    <!-- end card -->
</div>
<!-- end main-content -->

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- AUTO HIDE SUCCESS MESSAGE -->
<script>
setTimeout(() => {
    const alert = document.querySelector('.alert-success');
    if (alert) alert.style.display = 'none';
}, 3000);
</script>

</body>
</html>

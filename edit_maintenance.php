<?php
session_start();
require_once 'config/database.php';

$activeTab = $_GET['tab'] ?? 'asset';

/* =========================
   PROTECT PAGE
========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* =========================
   VALIDATE ASSET ID
========================= */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index_maintenance.php");
    exit();
}

$asset_id = (int) $_GET['id'];

$errors = [];

/* =========================
   FETCH ASSET INFO
========================= */
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

/* =========================
   FETCH LATEST MAINTENANCE
========================= */
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


/* =========================
   FETCH LOGGED-IN USER
========================= */
$userStmt = $pdo->prepare("SELECT staff_name FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$staffName = $user['staff_name'] ?? '';

/* =========================
   HANDLE MAINTENANCE INFO SUBMIT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maintenance_submit'])) {

    $issue_occurred        = trim($_POST['issue_occurred'] ?? '');
    $issue_date            = $_POST['issue_date'] ?? '';
    $reported_by           = trim($_POST['reported_by'] ?? '');
    $maintenance_location  = trim($_POST['maintenance_location'] ?? '');
    $additional_notes      = trim($_POST['additional_notes'] ?? '');

    if ($issue_occurred === '') $errors['issue_occurred'] = 'Issue description is required';
    if ($issue_date === '') $errors['issue_date'] = 'Issue date is required';
    if ($reported_by === '') $errors['reported_by'] = 'Reported By is required';

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO asset_maintenance
            (
                asset_id,
                issue_occurred,
                issue_date,
                reported_by,
                maintenance_location,
                additional_notes
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $asset_id,
            $issue_occurred,
            $issue_date,
            $reported_by,
            $maintenance_location ?: null,
            $additional_notes ?: null
        ]);

        // ✅ Redirect to view_maintenance.php with the same tab
        header("Location: view_maintenance.php?id=$asset_id&tab=maintenance&success=1");
        exit();
    }
}

/* =========================
   HANDLE POST-MAINTENANCE SUBMIT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_maintenance_submit'])) {

      // 🔒 SAFETY CHECK: no open maintenance record
    if (!$maintenance) {
        header("Location: index_maintenance.php");
        exit();
    }
    $action_taken   = trim($_POST['action_taken'] ?? '');
    $date_completed = $_POST['date_completed'] ?? '';
    $new_status     = $_POST['asset_status'] ?? '';

    if ($action_taken === '') $errors['action_taken'] = 'Action Taken is required';
    if ($date_completed === '') $errors['date_completed'] = 'Date Completed is required';

    if (empty($errors)) {

        // Update maintenance record
        $updateStmt = $pdo->prepare("
            UPDATE asset_maintenance
            SET action_taken = ?, date_completed = ?
            WHERE id = ?
        ");
        $updateStmt->execute([
            $action_taken,
            $date_completed,
            $maintenance['id']
        ]);

        // Update asset status
        $assetStmt = $pdo->prepare("UPDATE assets SET asset_status = ? WHERE id = ?");
        $assetStmt->execute([$new_status, $asset_id]);

        // ✅ Redirect to view_maintenance.php with the same tab
        header("Location: view_maintenance.php?id=$asset_id&tab=post-maintenance&success=1");
        exit();
    }
}



$showSuccess = isset($_GET['success']) && $_GET['success'] == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Maintenance | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">

<style>
.nav-tabs .nav-link {
    color: #000;
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    color: #0d6efd !important;
    font-weight: 600;
}
.nav-tabs .nav-link:hover {
    color: #000;
}
</style>
</head>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<h5>MAINTENANCE &gt; Update</h5>

<?php if ($showSuccess): ?>
<div class="alert alert-success mt-3">
    Maintenance record saved successfully.
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
           Issue Information
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

    

            <!-- ASSET INFORMATION -->
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

            <!-- PURCHASE INFORMATION -->
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

            <!-- MANUFACTURE & WARRANTY -->
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

            <!-- ASSIGNMENT INFORMATION -->
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

       

</div>
<!-- ================= TAB 2 : MAINTENANCE INFO ================= -->
<div class="tab-pane fade <?= $activeTab === 'maintenance' ? 'show active' : '' ?>" id="maintenanceTab">
<form method="post" id="editMaintenanceForm">
    <input type="hidden" name="maintenance_submit" value="1">
    <div class="mb-3">
        <label class="form-label">Issue Occurred <span class="text-danger">*</span></label>
        <textarea name="issue_occurred" class="form-control <?= isset($errors['issue_occurred']) ? 'is-invalid' : '' ?>" rows="3"><?= htmlspecialchars($maintenance['issue_occurred'] ?? '') ?></textarea>
        <div class="invalid-feedback"><?= $errors['issue_occurred'] ?? '' ?></div>
    </div>

    <div class="row mb-3">
        <div class="col-sm-4">
            <label class="form-label">Issue Date <span class="text-danger">*</span></label>
            <input type="date" name="issue_date" class="form-control <?= isset($errors['issue_date']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($maintenance['issue_date'] ?? '') ?>">
            <div class="invalid-feedback"><?= $errors['issue_date'] ?? '' ?></div>
        </div>
        <div class="col-sm-4">
            <label class="form-label">Reported By</label>
            <input type="text" name="reported_by" class="form-control" value="<?= htmlspecialchars($maintenance['reported_by'] ?? $staffName) ?>" readonly>
        </div>
        <div class="col-sm-4">
            <label class="form-label">Maintenance Location</label>
            <input type="text" name="maintenance_location" class="form-control" value="<?= htmlspecialchars($maintenance['maintenance_location'] ?? '') ?>">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Additional Notes</label>
        <textarea name="additional_notes" class="form-control" rows="3"><?= htmlspecialchars($maintenance['additional_notes'] ?? '') ?></textarea>
    </div>

     <div class="text-end">
        <a href="index_maintenance.php" class="btn btn-secondary">Back</a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModalMaintenance">Save</button>
    </div>
</form>
</div>

<!-- ================= TAB 3 : POST-MAINTENANCE ================= -->
<div class="tab-pane fade <?= $activeTab === 'post-maintenance' ? 'show active' : '' ?>" id="postMaintenanceTab">
<form method="post" id="postMaintenanceForm">
    <input type="hidden" name="post_maintenance_submit" value="1">
    <div class="mb-3">
        <label class="form-label">Action Taken <span class="text-danger">*</span></label>
        <textarea name="action_taken" class="form-control <?= isset($errors['action_taken']) ? 'is-invalid' : '' ?>" rows="3"><?= htmlspecialchars($maintenance['action_taken'] ?? '') ?></textarea>
        <div class="invalid-feedback"><?= $errors['action_taken'] ?? '' ?></div>
    </div>

    <div class="mb-3">
        <label class="form-label">Date Completed <span class="text-danger">*</span></label>
        <input type="date" name="date_completed" class="form-control <?= isset($errors['date_completed']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($maintenance['date_completed'] ?? '') ?>">
        <div class="invalid-feedback"><?= $errors['date_completed'] ?? '' ?></div>
    </div>

    <div class="mb-3">
        <label class="form-label">Update Asset Status</label>
        <select name="asset_status" class="form-select">
            <?php 
            $statuses = ['In Use', 'Available', 'Damaged', 'Maintenance'];

             foreach ($statuses as $status): ?>
        <option value="<?= $status ?>" <?= ($asset['asset_status'] === $status) ? 'selected' : '' ?>>
            <?= $status ?>
        </option>
    <?php endforeach; ?>
</select>
    </div>

      <div class="text-end">
        <a href="index_maintenance.php" class="btn btn-secondary">Back</a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModalPostMaintenance">Save</button>
    </div>
</form>
</div>

</div>
</div>
</div>

<!-- CONFIRM MODALS -->
<div class="modal fade" id="confirmModalMaintenance" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <i class="bi bi-exclamation-circle text-warning" style="font-size: 4rem;"></i>
        <p class="mt-3">Are you sure to save?</p>
        <button type="button" class="btn btn-primary" id="confirmSaveMaintenance">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirmModalPostMaintenance" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <i class="bi bi-exclamation-circle text-warning" style="font-size: 4rem;"></i>
        <p class="mt-3">Are you sure to save?</p>
        <button type="button" class="btn btn-primary" id="confirmSavePostMaintenance">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
      </div>
    </div>
  </div>
</div>
            </div>
<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Submit forms after confirmation
document.getElementById('confirmSaveMaintenance').addEventListener('click', function () {
    document.getElementById('editMaintenanceForm').submit();
});
document.getElementById('confirmSavePostMaintenance').addEventListener('click', function () {
    document.getElementById('postMaintenanceForm').submit();
});
</script>

</body>
</html>

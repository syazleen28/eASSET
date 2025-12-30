<?php
session_start();
require_once 'config/database.php';

$activeTab = $_GET['tab'] ?? 'asset'; // default to 'asset' tab

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate asset ID
if (!isset($_GET['id'])) {
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
   FETCH LATEST MAINTENANCE (IF ANY)
========================= */
$mStmt = $pdo->prepare("
    SELECT *
    FROM asset_maintenance
    WHERE asset_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$mStmt->execute([$asset_id]);
$maintenance = $mStmt->fetch(PDO::FETCH_ASSOC);

/* =========================
   HANDLE SUBMIT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $issue_occurred     = trim($_POST['issue_occurred'] ?? '');
    $issue_date         = $_POST['issue_date'] ?? '';
    $action_taken       = trim($_POST['action_taken'] ?? '');
    $maintenance_status = $_POST['maintenance_status'] ?? '';
    $completion_date    = $_POST['completion_date'] ?? null;

    if ($issue_occurred === '') {
        $errors['issue_occurred'] = 'Issue description is required';
    }
    if ($issue_date === '') {
        $errors['issue_date'] = 'Issue date is required';
    }
    if ($maintenance_status === '') {
        $errors['maintenance_status'] = 'Maintenance status is required';
    }

    if (empty($errors)) {

        // Insert new maintenance record
        $stmt = $pdo->prepare("
            INSERT INTO asset_maintenance
            (asset_id, issue_occurred, issue_date, action_taken, maintenance_status, completion_date)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $asset_id,
            $issue_occurred,
            $issue_date,
            $action_taken ?: null,
            $maintenance_status,
            $completion_date ?: null
        ]);

        // Auto update asset status
        if ($maintenance_status === 'Completed') {
            $pdo->prepare("
                UPDATE assets SET asset_status = 'Available'
                WHERE id = ?
            ")->execute([$asset_id]);
        } else {
            $pdo->prepare("
                UPDATE assets SET asset_status = 'Maintenance'
                WHERE id = ?
            ")->execute([$asset_id]);
        }

        header("Location: view_maintenance.php?id=$asset_id&success=1");
        exit();
    }
}
    // Assuming you have $_SESSION['user_id']
    $userStmt = $pdo->prepare("SELECT staff_name FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $staff = $userStmt->fetch(PDO::FETCH_ASSOC);
    $staffName = $staff['staff_name'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Maintenance | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* Default inactive tab */
.nav-tabs .nav-link {
    color: #000;               /* black */
    font-weight: 500;
}

/* Active tab (clicked) */
.nav-tabs .nav-link.active {
    color: #0d6efd !important;  /* blue */
    font-weight: 600;
}

/* Optional: hover effect */
.nav-tabs .nav-link:hover {
    color: #000;
}
</style>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">
</head>

<body>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<h5>ASSET MANAGEMENT &gt; Maintenance &gt; Update</h5>

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


<!-- ================= TAB 2 : MAINTENANCE ================= -->
  <div class="tab-pane fade <?= $activeTab === 'maintenance' ? 'show active' : '' ?>" id="maintenanceTab">

<form method="post">

<div class="mb-3">
    <label class="form-label">Issue Occurred <span class="text-danger">*</span></label>
    <textarea name="issue_occurred"
              class="form-control <?= isset($errors['issue_occurred']) ? 'is-invalid' : '' ?>"
              rows="3"><?= htmlspecialchars($maintenance['issue_occurred'] ?? '') ?></textarea>
    <div class="invalid-feedback"><?= $errors['issue_occurred'] ?? '' ?></div>
</div>

<div class="row mb-3">
    <div class="col-sm-4">
        <label class="form-label">Issue Date <span class="text-danger">*</span></label>
        <input type="date" name="issue_date"
               class="form-control <?= isset($errors['issue_date']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($maintenance['issue_date'] ?? '') ?>">
        <div class="invalid-feedback"><?= $errors['issue_date'] ?? '' ?></div>
    </div>

    <div class="col-sm-4">
    <label class="form-label">Reported By <span class="text-danger">*</span></label>
    <input type="text" name="reported_by" class="form-control" 
           value="<?= htmlspecialchars($maintenance['reported_by'] ?? $staffName) ?>" readonly>
</div>


    <div class="col-sm-4">
        <label class="form-label">Maintenance Location</label>
        <input type="text" name="maintenance_location" class="form-control"
               value="<?= htmlspecialchars($maintenance['maintenance_location'] ?? '') ?>">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Additional Notes</label>
    <textarea name="additional_notes" class="form-control" rows="3"><?= htmlspecialchars($maintenance['additional_notes'] ?? '') ?></textarea>
</div>

<div class="text-end">
    <a href="index_maintenance.php" class="btn btn-secondary">Back</a>
    <button type="submit" class="btn btn-primary">Submit Maintenance Request</button>
</div>
</form>

</div>

</div>
</div>
</div>
</div>
</div>
</div>


<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

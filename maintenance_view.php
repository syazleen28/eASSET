<?php
session_start();
require_once 'config/database.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate asset ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index_asset.php");
    exit();
}

$asset_id = (int) $_GET['id'];

/* ================= FETCH ASSET ================= */
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        c.category_name
    FROM assets a
    LEFT JOIN asset_categories c ON a.category_id = c.id
    WHERE a.id = :id
");
$stmt->execute([':id' => $asset_id]);
$asset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asset) {
    header("Location: index_asset.php");
    exit();
}

/* ================= FETCH MAINTENANCE (OPTIONAL) ================= */
$maintStmt = $pdo->prepare("
    SELECT * FROM maintenance
    WHERE asset_id = :asset_id
    LIMIT 1
");
$maintStmt->execute([':asset_id' => $asset_id]);
$maintenance = $maintStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Maintenance View | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

    <h5 class="mb-4">ASSET MANAGEMENT &gt; Maintenance</h5>

    <!-- TABS -->
    <ul class="nav nav-tabs" id="maintenanceTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#assetInfo">
                Asset Information
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#maintenanceInfo">
                Maintenance Information
            </button>
        </li>
    </ul>

    <div class="tab-content mt-3">

        <!-- ================= TAB 1 : ASSET INFO ================= -->
        <div class="tab-pane fade show active" id="assetInfo">

            <div class="card">
                <div class="card-body">

                    <h6 class="fw-bold mb-3">Asset Information</h6>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Asset Code :</label>
                        <div class="col-sm-4">
                            <input class="form-control" value="<?= htmlspecialchars($asset['asset_code']) ?>" readonly>
                        </div>

                        <label class="col-sm-2 col-form-label">Asset Category :</label>
                        <div class="col-sm-4">
                            <input class="form-control" value="<?= htmlspecialchars($asset['category_name']) ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Asset Name :</label>
                        <div class="col-sm-4">
                            <input class="form-control" value="<?= htmlspecialchars($asset['asset_name']) ?>" readonly>
                        </div>

                        <label class="col-sm-2 col-form-label">Status :</label>
                        <div class="col-sm-4">
                            <input class="form-control" value="<?= htmlspecialchars($asset['asset_status']) ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Brand :</label>
                        <div class="col-sm-4">
                            <input class="form-control" value="<?= htmlspecialchars($asset['brand']) ?>" readonly>
                        </div>

                        <label class="col-sm-2 col-form-label">Serial No :</label>
                        <div class="col-sm-4">
                            <input class="form-control" value="<?= htmlspecialchars($asset['serial_number']) ?>" readonly>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ================= TAB 2 : MAINTENANCE INFO ================= -->
        <div class="tab-pane fade" id="maintenanceInfo">

            <div class="card">
                <div class="card-body">

                    <h6 class="fw-bold mb-3">Maintenance Information</h6>

                    <form method="post" action="maintenance_save.php">

                        <input type="hidden" name="asset_id" value="<?= $asset_id ?>">
                        <input type="hidden" name="maintenance_id" value="<?= $maintenance['id'] ?? '' ?>">

                        <div class="mb-3">
                            <label class="form-label">Issue</label>
                            <textarea name="issue" class="form-control" rows="3"><?= htmlspecialchars($maintenance['issue'] ?? '') ?></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Maintenance Location</label>
                                <input type="text" name="maintenance_location" class="form-control"
                                    value="<?= htmlspecialchars($maintenance['maintenance_location'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Maintenance Date</label>
                                <input type="date" name="maintenance_date" class="form-control"
                                    value="<?= htmlspecialchars($maintenance['maintenance_date'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"><?= htmlspecialchars($maintenance['remarks'] ?? '') ?></textarea>
                        </div>

                        <!-- ACTION BUTTONS -->
                        <div class="text-end">
                            <button type="submit" name="save" class="btn btn-success">Save</button>
                            <button type="submit" name="update" class="btn btn-primary">Update</button>
                            <a href="index_asset.php" class="btn btn-secondary">Back</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

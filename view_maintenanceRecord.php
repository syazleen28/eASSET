<?php
session_start();
require_once 'config/database.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate maintenance ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index_asset.php");
    exit();
}

$id = (int) $_GET['id'];

// Fetch maintenance record
$stmt = $pdo->prepare("
    SELECT
        issue_occurred,
        issue_date,
        reported_by,
        maintenance_location,
        additional_notes,
        action_taken,
        date_completed
    FROM asset_maintenance
    WHERE id = :id
");
$stmt->execute([':id' => $id]);
$maintenance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$maintenance) {
    header("Location: index_asset.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Maintenance Record | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">
</head>

<body>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

    <div class="mb-4">
        <h5>ASSET MANAGEMENT &gt; Maintenance Records &gt; View</h5>
    </div>

    <div class="card">
        <div class="card-body">

            <h6 class="mb-4 fw-bold">Maintenance Record Details</h6>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Issue Occurred :</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($maintenance['issue_occurred']) ?>" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Issue Date :</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($maintenance['issue_date']) ?>" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Reported By :</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($maintenance['reported_by']) ?>" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Maintenance Location :</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($maintenance['maintenance_location']) ?>" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Additional Notes :</label>
                <div class="col-sm-9">
                    <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($maintenance['additional_notes']) ?></textarea>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Action Taken :</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($maintenance['action_taken']) ?>" readonly>
                </div>
            </div>

            <div class="row mb-4">
                <label class="col-sm-3 col-form-label">Date Completed :</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($maintenance['date_completed']) ?>" readonly>
                </div>
            </div>

            <div class="text-end">
                <button class="btn btn-secondary" onclick="history.back()">Back</button>
            </div>

        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

<?php
session_start();
require_once 'config/database.php';
$activeTab = $_GET['tab'] ?? 'asset'; // default to Asset Information tab
// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index_asset.php");
    exit();
}

$id = (int) $_GET['id'];

// Fetch asset with category
$stmt = $pdo->prepare("
     SELECT 
        a.id,
        a.asset_code,
        a.asset_name,
        a.asset_status,
        a.brand,
        a.serial_number,
        a.supplier,
        a.purchase_date,
        a.purchase_cost,
        a.manufacture_date,
        a.warranty,
        a.location,
        a.assigned_user,
        a.description,
        a.os,
        a.os_version,
        a.drive_info,
        a.spec,
        c.category_name
    FROM assets a
    LEFT JOIN asset_categories c ON a.category_id = c.id
    WHERE a.id = :id
");
$stmt->execute([':id' => $id]);
$asset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asset) {
    header("Location: index_asset.php");
    exit();
}

// Success flag
$showSuccess = (isset($_GET['success']) && $_GET['success'] == 1);

// Fetch maintenance records for this asset
$maintStmt = $pdo->prepare("
    SELECT 
        id,
        issue_occurred,
        issue_date,
        reported_by,
        action_taken,
        date_completed
    FROM asset_maintenance
    WHERE asset_id = :asset_id
    ORDER BY issue_date DESC
");
$maintStmt->execute([':asset_id' => $id]);
$maintenanceRecords = $maintStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Asset | eAssets</title>
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

    <!-- SUCCESS MESSAGE -->
    <?php if ($showSuccess): ?>
        <div class="alert alert-success d-flex align-items-center mb-3">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>
                <strong>Successful</strong><br>Data saved successfully!
            </div>
        </div>
    <?php endif; ?>

    <div class="mb-4">
        <h5>ASSET &gt; View</h5>
    </div>

    <div class="card">
        <div class="card-body">

            <!-- TAB NAVIGATION -->
            <ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <button class="nav-link <?= $activeTab === 'asset' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#assetTab">
            Asset Information
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link <?= $activeTab === 'maintenance' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#maintenanceRecordTab">
            Maintenance Records
        </button>
    </li>
</ul>

            <!-- TAB CONTENT -->
            <div class="tab-content">

                <!-- TAB 1: ASSET INFORMATION -->
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

                    <?php
                    // List of categories that require system info
                    $systemCategories = ['All-in-One PC','Desktop Computer','Laptop / Notebook'];
                    if (in_array($asset['category_name'], $systemCategories)):
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


                                        <!-- ACTION BUTTONS -->
                                        <div class="text-end">
                                            <a href="edit_asset.php?id=<?= $asset['id'] ?>" class="btn btn-primary">Update</a>
                                            <button class="btn btn-danger" data-id="<?= $asset['id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                                            <a href="index_asset.php" class="btn btn-secondary">Back</a>
                                        </div>

                </div> <!-- end assetTab -->

               <!-- TAB 2: MAINTENANCE RECORDS -->
 <div class="tab-pane fade <?= $activeTab === 'maintenance' ? 'show active' : '' ?>" id="maintenanceRecordTab">

    <h6 class="mb-3 fw-bold">Maintenance Records</h6>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Issue Occurred</th>
                    <th>Issue Date</th>
                    <th>Reported By</th>
                    <th>Action Taken</th>
                    <th>Date Completed</th>
                    <th>Actions</th> <!-- new column -->
                </tr>
            </thead>
            <tbody>
<?php if (!empty($maintenanceRecords)): ?>
    <?php foreach ($maintenanceRecords as $index => $row): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($row['issue_occurred']) ?></td>
            <td><?= htmlspecialchars($row['issue_date']) ?></td>
            <td><?= htmlspecialchars($row['reported_by']) ?></td>
            <td><?= htmlspecialchars($row['action_taken'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['date_completed'] ?? '-') ?></td>
            <td class="text-center">
                <a href="view_maintenanceRecord.php?id=<?= $row['id'] ?>" 
                   class="btn btn-sm btn-primary" title="View">
                    <i class="bi bi-search"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="7" class="text-center text-muted">
            No maintenance records found.
        </td>
    </tr>
<?php endif; ?>
</tbody>

        </table>
    </div>

</div> <!-- end maintenanceRecordTab -->

            </div> <!-- end tab-content -->

        </div>
    </div>

</div> <!-- end main-content -->

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
        <p class="mt-3">Are you sure to delete this asset?</p>
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
// Pass asset ID to delete modal
const deleteBtn = document.querySelector('button[data-bs-target="#deleteModal"]');
deleteBtn.addEventListener('click', () => {
    document.getElementById('deleteId').value = deleteBtn.getAttribute('data-id');
});

// Auto-hide success message
setTimeout(() => {
    const alert = document.querySelector('.alert-success');
    if (alert) alert.style.display = 'none';
}, 3000);
</script>

</body>
</html>

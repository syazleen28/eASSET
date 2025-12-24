<?php
session_start();
require_once 'config/database.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: asset_list.php");
    exit();
}

$id = (int) $_GET['id'];

// Fetch asset
$stmt = $pdo->prepare(
    "SELECT a.*, c.category_name, s.supplier_name
     FROM assets a
     LEFT JOIN asset_categories c ON a.category_id = c.id
     LEFT JOIN suppliers s ON a.supplier = s.id
     WHERE a.id = :id"
);
$stmt->execute([':id' => $id]);
$asset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asset) {
    header("Location: asset_list.php");
    exit();
}

// Success flag
$showSuccess = (isset($_GET['success']) && $_GET['success'] == 1);
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
        <h5>ASSET MANAGEMENT &gt; Asset &gt; View</h5>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Asset Category :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($asset['category_name'] ?? 'N/A') ?>" readonly>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Asset Status :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($asset['asset_status']) ?>" readonly>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Asset Name / Model :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($asset['asset_name']) ?>" readonly>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Brand :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($asset['brand']) ?>" readonly>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Serial Number (S/N) :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($asset['serial_number']) ?>" readonly>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Supplier :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($asset['supplier_name'] ?? $asset['supplier']) ?>" readonly>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Purchase Date :</label>
                <div class="col-sm-4">
                    <input type="date" class="form-control" value="<?= htmlspecialchars($asset['purchase_date']) ?>" readonly>
                </div>

                <label class="col-sm-2 col-form-label fw-bold">Purchase Cost (RM) :</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($asset['purchase_cost']) ?>" readonly>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Manufacture Date :</label>
                <div class="col-sm-4">
                    <input type="date" class="form-control" value="<?= htmlspecialchars($asset['manufacture_date']) ?>" readonly>
                </div>

                <label class="col-sm-2 col-form-label fw-bold">Warranty :</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($asset['warranty']) ?>" readonly>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Location :</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($asset['location']) ?>" readonly>
                </div>

                <label class="col-sm-2 col-form-label fw-bold">Assigned User :</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($asset['assigned_user']) ?>" readonly>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Description :</label>
                <div class="col-sm-10">
                    <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($asset['description']) ?></textarea>
                </div>
            </div>

            <div class="text-end">
                <a href="edit_asset.php?id=<?= $asset['id'] ?>" class="btn btn-primary">Update</a>
                <button class="btn btn-danger" data-id="<?= $asset['id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                <a href="index_asset.php" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>

<!-- DELETE CONFIRM MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
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
const deleteBtn = document.querySelector('button[data-bs-target="#deleteModal"]');
deleteBtn.addEventListener('click', () => {
    const id = deleteBtn.getAttribute('data-id');
    document.getElementById('deleteId').value = id;
});

// Auto-hide success message
setTimeout(() => {
    const alert = document.querySelector('.alert-success');
    if (alert) alert.style.display = 'none';
}, 3000);
</script>

</body>
</html>

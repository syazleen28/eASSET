<?php
session_start();
require_once 'config/database.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch categories for dropdown
$catStmt = $pdo->query("SELECT id, category_name FROM asset_categories ORDER BY category_name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch suppliers for dropdown
$supStmt = $pdo->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name");
$suppliers = $supStmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        'category_id'      => $_POST['category_id'] ?? '',
        'asset_name'       => trim($_POST['asset_name'] ?? ''),
        'asset_status'     => $_POST['asset_status'] ?? '',
        'description'      => trim($_POST['description'] ?? ''),
        'supplier'         => $_POST['supplier'] ?? '',
        'warranty'         => trim($_POST['warranty'] ?? ''),
        'location'         => trim($_POST['location'] ?? ''),
        'assigned_user'    => trim($_POST['assigned_user'] ?? ''),
        'serial_number'    => trim($_POST['serial_number'] ?? ''),
        'brand'            => trim($_POST['brand'] ?? ''),
        'manufacture_date' => $_POST['manufacture_date'] ?? '',
        'purchase_date'    => $_POST['purchase_date'] ?? '',
        'purchase_cost'    => $_POST['purchase_cost'] ?? ''
    ];

    // === VALIDATION ===
    if ($data['category_id'] === '') $errors['category_id'] = "Category is required.";
    if ($data['asset_name'] === '')  $errors['asset_name']  = "Asset Name is required.";
    if ($data['supplier'] === '')    $errors['supplier']    = "Supplier is required.";

    // === INSERT DATA ===
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO assets (
                category_id, asset_name, asset_status, description, supplier,
                warranty, location, assigned_user, serial_number, brand,
                manufacture_date, purchase_date, purchase_cost
            ) VALUES (
                :category_id, :asset_name, :asset_status, :description, :supplier,
                :warranty, :location, :assigned_user, :serial_number, :brand,
                :manufacture_date, :purchase_date, :purchase_cost
            )
        ");

        $stmt->execute($data);
        $id = $pdo->lastInsertId();

        header("Location: view_asset.php?id=$id&success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Asset | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">

<style>
#confirmModal .modal-dialog { max-width: 350px; }
</style>
</head>

<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
<h5 class="mb-4">ASSET MANAGEMENT &gt; Asset &gt; New Record</h5>

<form method="post" id="assetForm" novalidate>

<div class="card">
<div class="card-body">

<!-- ROW 1: Category & Status -->
<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Asset Category <span class="text-danger">*</span> :</label>
    <div class="col-sm-4">
        <select name="category_id" class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($data['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['category_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback"><?= $errors['category_id'] ?? '' ?></div>
    </div>

    <label class="col-sm-2 col-form-label">Asset Status :</label>
    <div class="col-sm-4">
        <select name="asset_status" class="form-select">
            <option value="">-- Select Status --</option>
            <option value="Available" <?= ($data['asset_status'] ?? '')=='Available' ? 'selected' : '' ?>>Available</option>
            <option value="In Use" <?= ($data['asset_status'] ?? '')=='In Use' ? 'selected' : '' ?>>In Use</option>
            <option value="Damaged" <?= ($data['asset_status'] ?? '')=='Damaged' ? 'selected' : '' ?>>Damaged</option>
            <option value="Maintenance" <?= ($data['asset_status'] ?? '')=='Maintenance' ? 'selected' : '' ?>>Maintenance</option>
        </select>
    </div>
</div>

<!-- ROW 2: Name & Brand -->
<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Asset Name/ Model <span class="text-danger">*</span> :</label>
    <div class="col-sm-4">
        <input type="text" name="asset_name" class="form-control <?= isset($errors['asset_name']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($data['asset_name'] ?? '') ?>">
        <div class="invalid-feedback"><?= $errors['asset_name'] ?? '' ?></div>
    </div>

    <label class="col-sm-2 col-form-label">Brand :</label>
    <div class="col-sm-4">
        <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($data['brand'] ?? '') ?>">
    </div>
</div>

<!-- ROW 3: Serial Number & Supplier -->
<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Serial Number (S/N) :</label>
    <div class="col-sm-4">
        <input type="text" name="serial_number" class="form-control" value="<?= htmlspecialchars($data['serial_number'] ?? '') ?>">
    </div>

    <label class="col-sm-2 col-form-label">Supplier <span class="text-danger">*</span> :</label>
    <div class="col-sm-4">
        <select name="supplier" class="form-select <?= isset($errors['supplier']) ? 'is-invalid' : '' ?>">
            <option value="">-- Select Supplier --</option>
            <?php foreach ($suppliers as $s): ?>
                <option value="<?= htmlspecialchars($s['supplier_name']) ?>" <?= ($data['supplier'] ?? '') == $s['supplier_name'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['supplier_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback"><?= $errors['supplier'] ?? '' ?></div>
    </div>
</div>

<!-- ROW 4: Purchase Date & Cost -->
<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Purchase Date :</label>
    <div class="col-sm-4">
        <input type="date" name="purchase_date" class="form-control" value="<?= htmlspecialchars($data['purchase_date'] ?? '') ?>">
    </div>

    <label class="col-sm-2 col-form-label">Purchase Cost (RM) :</label>
    <div class="col-sm-4">
        <input type="number" step="0.01" name="purchase_cost" class="form-control" value="<?= htmlspecialchars($data['purchase_cost'] ?? '') ?>">
    </div>
</div>

<!-- ROW 5: Manufacture Date & Warranty -->
<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Manufacture Date :</label>
    <div class="col-sm-4">
        <input type="date" name="manufacture_date" class="form-control" value="<?= htmlspecialchars($data['manufacture_date'] ?? '') ?>">
    </div>

    <label class="col-sm-2 col-form-label">Warranty :</label>
    <div class="col-sm-4">
        <input type="text" name="warranty" class="form-control" value="<?= htmlspecialchars($data['warranty'] ?? '') ?>">
    </div>
</div>

<!-- ROW 6: Location & Assigned User -->
<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Location :</label>
    <div class="col-sm-4">
        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($data['location'] ?? '') ?>">
    </div>

    <label class="col-sm-2 col-form-label">Assigned User :</label>
    <div class="col-sm-4">
        <input type="text" name="assigned_user" class="form-control" value="<?= htmlspecialchars($data['assigned_user'] ?? '') ?>">
    </div>
</div>

<!-- ROW 7: Description -->
<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Description :</label>
    <div class="col-sm-10">
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
    </div>
</div>

<!-- BUTTONS -->
<div class="text-end">
    <a href="index_asset.php" class="btn btn-secondary">Back</a>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModal">
        Save
    </button>
</div>

</div>
</div>
</form>
</div>

<!-- CONFIRM MODAL -->
<div class="modal fade" id="confirmModal">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-body text-center">
    <i class="bi bi-exclamation-circle fs-1 text-warning"></i>
    <p class="mt-3">Are you sure you want to save this asset?</p>
    <button class="btn btn-primary" id="confirmSave">Save</button>
    <button class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
</div>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('confirmSave').onclick = () => {
    document.getElementById('assetForm').submit();
};
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

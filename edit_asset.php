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
    header("Location: index_asset.php");
    exit();
}

$id = (int) $_GET['id'];
$errors = [];
$data = [];

/* =========================
   FETCH CATEGORIES
========================= */
$catStmt = $pdo->query("SELECT id, category_name FROM asset_categories ORDER BY category_name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   FETCH SUPPLIERS
========================= */
$supStmt = $pdo->query("SELECT supplier_name FROM suppliers ORDER BY supplier_name");
$suppliers = $supStmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   FETCH ASSET DATA
========================= */
$stmt = $pdo->prepare("SELECT * FROM assets WHERE id = ?");
$stmt->execute([$id]);
$asset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asset) {
    header("Location: index_asset.php");
    exit();
}

$data = $asset;

/* =========================
   HANDLE FORM SUBMIT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fields = [
        'category_id','asset_status','asset_name','brand',
        'serial_number','supplier','purchase_date','purchase_cost',
        'manufacture_date','warranty','location','assigned_user','description'
    ];

    foreach ($fields as $field) {
        $data[$field] = trim($_POST[$field] ?? '');
    }

    /* ===== VALIDATION ===== */
    if ($data['category_id'] === '') {
        $errors['category_id'] = "Asset Category is required.";
    }
    if ($data['asset_status'] === '') {
        $errors['asset_status'] = "Asset Status is required.";
    }
    if ($data['asset_name'] === '') {
        $errors['asset_name'] = "Asset Name / Model is required.";
    }

    if ($data['supplier'] === '') {
    $errors['supplier'] = "Supplier is required.";
}

    // Duplicate serial check ONLY if serial filled and different from current
    if (!empty($data['serial_number'])) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE serial_number = ? AND id != ?");
        $check->execute([$data['serial_number'], $id]);
        if ($check->fetchColumn() > 0) {
            $errors['serial_number'] = "Serial Number already exists.";
        }
    }

    /* ===== UPDATE ===== */
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE assets SET
                category_id = :category_id,
                asset_status = :asset_status,
                asset_name = :asset_name,
                brand = :brand,
                serial_number = :serial_number,
                supplier = :supplier,
                purchase_date = :purchase_date,
                purchase_cost = :purchase_cost,
                manufacture_date = :manufacture_date,
                warranty = :warranty,
                location = :location,
                assigned_user = :assigned_user,
                description = :description
            WHERE id = :id
        ");

        $stmt->execute([
            ':category_id'       => $data['category_id'],
            ':asset_status'      => $data['asset_status'],
            ':asset_name'        => $data['asset_name'],
            ':brand'             => $data['brand'] ?: null,
            ':serial_number'     => $data['serial_number'] ?: null,
            ':supplier'          => $data['supplier'] ?: null,
            ':purchase_date'     => $data['purchase_date'] ?: null,
            ':purchase_cost'     => $data['purchase_cost'] ?: null,
            ':manufacture_date'  => $data['manufacture_date'] ?: null,
            ':warranty'          => $data['warranty'] ?: null,
            ':location'          => $data['location'] ?: null,
            ':assigned_user'     => $data['assigned_user'] ?: null,
            ':description'       => $data['description'] ?: null,
            ':id'                => $id
        ]);

        header("Location: view_asset.php?id=$id&success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Asset | eAssets</title>
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
<h5>ASSET MANAGEMENT &gt; Assets &gt; Edit Record</h5>
<div class="card">
    <div class="card-body">
<form method="post" id="assetForm" novalidate>

<h6 class="mb-3 mt-3 fw-bold">Asset Information</h6>

<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Asset Code :</label>
    <div class="col-sm-4">
        <input type="text" name="asset_code" class="form-control" value="<?= htmlspecialchars($asset['asset_code']) ?>" readonly>
    </div>

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
</div>

<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Asset Status <span class="text-danger">*</span> :</label>
    <div class="col-sm-4">
        <select name="asset_status" class="form-select <?= isset($errors['asset_status']) ? 'is-invalid' : '' ?>">
            <option value="">-- Select Status --</option>
            <?php
            $statuses = ['Available','In Use','Damaged','Maintenance'];
            foreach ($statuses as $s):
            ?>
            <option value="<?= $s ?>" <?= ($data['asset_status'] ?? '') == $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback"><?= $errors['asset_status'] ?? '' ?></div>
    </div>

    <label class="col-sm-2 col-form-label">Asset Name / Model <span class="text-danger">*</span> :</label>
    <div class="col-sm-4">
        <input type="text" name="asset_name"
               class="form-control <?= isset($errors['asset_name']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($data['asset_name'] ?? '') ?>">
        <div class="invalid-feedback"><?= $errors['asset_name'] ?? '' ?></div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Brand :</label>
    <div class="col-sm-4">
        <input type="text" name="brand" class="form-control"
               value="<?= htmlspecialchars($data['brand'] ?? '') ?>">
    </div>

    <label class="col-sm-2 col-form-label">Serial Number :</label>
    <div class="col-sm-4">
        <input type="text" name="serial_number"
               class="form-control <?= isset($errors['serial_number']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($data['serial_number'] ?? '') ?>">
        <div class="invalid-feedback"><?= $errors['serial_number'] ?? '' ?></div>
    </div>
</div>

<h6 class="mb-3 mt-4 fw-bold">Purchase Information</h6>

<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Supplier <span class="text-danger">*</span> :</label>
<div class="col-sm-4">
    <select name="supplier" class="form-select <?= isset($errors['supplier']) ? 'is-invalid' : '' ?>" required>
        <option value="">-- Select Supplier --</option>
        <?php foreach ($suppliers as $s): ?>
            <option value="<?= htmlspecialchars($s['supplier_name']) ?>"
                <?= ($data['supplier'] ?? '') == $s['supplier_name'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['supplier_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="invalid-feedback">
        <?= $errors['supplier'] ?? '' ?>
    </div>
</div>


    <label class="col-sm-2 col-form-label">Purchase Date :</label>
    <div class="col-sm-4">
        <input type="date" name="purchase_date" class="form-control"
               value="<?= htmlspecialchars($data['purchase_date'] ?? '') ?>">
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Purchase Cost (RM) :</label>
    <div class="col-sm-4">
        <input type="number" step="0.01" name="purchase_cost" class="form-control"
               value="<?= htmlspecialchars($data['purchase_cost'] ?? '') ?>">
    </div>
</div>

<h6 class="mb-3 mt-4 fw-bold">Manufacture & Warranty</h6>

<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Manufacture Date :</label>
    <div class="col-sm-4">
        <input type="date" name="manufacture_date" class="form-control"
               value="<?= htmlspecialchars($data['manufacture_date'] ?? '') ?>">
    </div>

    <label class="col-sm-2 col-form-label">Warranty :</label>
    <div class="col-sm-4">
        <input type="text" name="warranty" class="form-control"
               value="<?= htmlspecialchars($data['warranty'] ?? '') ?>">
    </div>
</div>

<h6 class="mb-3 mt-4 fw-bold">Assignment Information</h6>

<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Location :</label>
    <div class="col-sm-4">
        <input type="text" name="location" class="form-control"
               value="<?= htmlspecialchars($data['location'] ?? '') ?>">
    </div>

    <label class="col-sm-2 col-form-label">Assigned User :</label>
    <div class="col-sm-4">
        <input type="text" name="assigned_user" class="form-control"
               value="<?= htmlspecialchars($data['assigned_user'] ?? '') ?>">
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Description :</label>
    <div class="col-sm-10">
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
    </div>
</div>

<div class="text-end">
    <a href="view_asset.php?id=<?= $id ?>" class="btn btn-secondary">Back</a>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModal">
        Save
    </button>
</div>

</form>
        </div>
        </div>
</div>

<!-- CONFIRM MODAL -->
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <i class="bi bi-exclamation-circle text-warning" style="font-size: 4rem;"></i>
        <p class="mt-3">Are you sure to save>?</p>

        <button type="button" class="btn btn-primary" id="confirmSave">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('confirmSave').addEventListener('click', function () {
    document.getElementById('assetForm').submit();
});
</script>

</body>
</html>

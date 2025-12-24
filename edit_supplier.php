<?php
session_start();
require_once 'config/database.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: config_supplier.php");
    exit();
}

$id = (int) $_GET['id'];

// Fetch supplier for pre-fill
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = :id");
$stmt->execute([':id' => $id]);
$supplier = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$supplier) {
    header("Location: config_supplier.php");
    exit();
}

$errors = [];
$supplier_name = $supplier['supplier_name'];
$address       = $supplier['address'] ?? '';
$phone         = $supplier['phone'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $address       = trim($_POST['address'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');

    // === VALIDATION ===
    if ($supplier_name === '') {
        $errors['supplier_name'] = "Supplier Name is required.";
    } elseif (strlen($supplier_name) < 3) {
        $errors['supplier_name'] = "Supplier Name must be at least 3 characters.";
    }

    // === DUPLICATE CHECK ===
    if (!isset($errors['supplier_name'])) {
        $check = $pdo->prepare(
            "SELECT COUNT(*) FROM suppliers WHERE supplier_name = :supplier_name AND id != :id"
        );
        $check->execute([':supplier_name' => $supplier_name, ':id' => $id]);

        if ($check->fetchColumn() > 0) {
            $errors['supplier_name'] = "Supplier Name already exists.";
        }
    }

    // === UPDATE DATA ===
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "UPDATE suppliers
             SET supplier_name = :supplier_name,
                 address       = :address,
                 phone         = :phone
             WHERE id = :id"
        );
        $stmt->execute([
            ':supplier_name' => $supplier_name,
            ':address'       => $address,
            ':phone'         => $phone,
            ':id'            => $id
        ]);

        header("Location: view_supplier.php?id=" . $id . "&success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Supplier | eAssets</title>
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

    <div class="mb-4">
        <h5>CONFIGURATION &gt; Supplier &gt; Update</h5>
    </div>

    <form method="post" id="editSupplierForm" novalidate>

        <!-- SUPPLIER NAME -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">
                Supplier Name <span class="text-danger">*</span> :
            </label>
            <div class="col-sm-10">
                <input type="text"
                       name="supplier_name"
                       class="form-control <?= isset($errors['supplier_name']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($supplier_name) ?>">

                <?php if (isset($errors['supplier_name'])): ?>
                    <div class="invalid-feedback">
                        <?= htmlspecialchars($errors['supplier_name']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ADDRESS -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">Address :</label>
            <div class="col-sm-10">
                <textarea name="address" class="form-control" rows="4"><?= htmlspecialchars($address) ?></textarea>
            </div>
        </div>

        <!-- PHONE -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">Phone :</label>
            <div class="col-sm-10">
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>">
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="row">
            <div class="col-sm-10 offset-sm-2 text-end">
                <a href="view_supplier.php?id=<?= $id ?>" class="btn btn-secondary">Back</a>

                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModal">
                    Save
                </button>
            </div>
        </div>
    </form>
</div>

<!-- CONFIRM MODAL -->
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <i class="bi bi-exclamation-circle fs-1 text-warning"></i>
        <p class="mt-3">Are you sure you want to save changes?</p>

        <button type="button" class="btn btn-primary" id="confirmSave">
            Save
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Back
        </button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Submit form after confirmation
document.getElementById('confirmSave').addEventListener('click', function () {
    document.getElementById('editSupplierForm').submit();
});

// Remove error highlight when typing
document.querySelector('input[name="supplier_name"]').addEventListener('input', function () {
    this.classList.remove('is-invalid');
});
</script>

</body>
</html>

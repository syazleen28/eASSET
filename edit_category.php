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
    header("Location: config_category.php");
    exit();
}

$id = (int) $_GET['id'];

// Fetch category for pre-fill
$stmt = $pdo->prepare("SELECT * FROM asset_categories WHERE id = :id");
$stmt->execute([':id' => $id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: config_category.php");
    exit();
}

$errors = [];
$category_name = $category['category_name'];
$description   = $category['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');
    $description   = trim($_POST['description'] ?? '');

    // === VALIDATION ===
    if ($category_name === '') {
        $errors['category_name'] = "Category Name is required.";
    } elseif (strlen($category_name) < 3) {
        $errors['category_name'] = "Category Name must be at least 3 characters.";
    }

    // === DUPLICATE CHECK ===
    if (!isset($errors['category_name'])) {
        $check = $pdo->prepare(
            "SELECT COUNT(*) FROM asset_categories WHERE category_name = :category_name AND id != :id"
        );
        $check->execute([':category_name' => $category_name, ':id' => $id]);

        if ($check->fetchColumn() > 0) {
            $errors['category_name'] = "Category Name already exists.";
        }
    }

    // === UPDATE DATA ===
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "UPDATE asset_categories
             SET category_name = :category_name,
                 description   = :description
             WHERE id = :id"
        );
        $stmt->execute([
            ':category_name' => $category_name,
            ':description'   => $description,
            ':id'            => $id
        ]);

        header("Location: view_category.php?id=" . $id . "&success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Asset Category | eAssets</title>
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
        <h5>CONFIGURATION &gt; Asset Category &gt; Update</h5>
    </div>

    <form method="post" id="editCategoryForm" novalidate>

        <!-- CATEGORY NAME -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">
                Category Name <span class="text-danger">*</span> :
            </label>
            <div class="col-sm-10">
                <input type="text"
                       name="category_name"
                       class="form-control <?= isset($errors['category_name']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($category_name) ?>">

                <?php if (isset($errors['category_name'])): ?>
                    <div class="invalid-feedback">
                        <?= htmlspecialchars($errors['category_name']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- DESCRIPTION -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">Description :</label>
            <div class="col-sm-10">
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($description) ?></textarea>
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="row">
            <div class="col-sm-10 offset-sm-2 text-end">
                <a href="view_category.php?id=<?= $id ?>" class="btn btn-secondary">Back</a>

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
    document.getElementById('editCategoryForm').submit();
});

// Remove error highlight when typing
document.querySelector('input[name="category_name"]').addEventListener('input', function () {
    this.classList.remove('is-invalid');
});
</script>

</body>
</html>

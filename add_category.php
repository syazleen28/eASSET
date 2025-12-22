<?php
session_start();
require_once 'config/database.php';

$errors = [];
$category_name = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $category_name = trim($_POST['category_name'] ?? '');
    $description   = trim($_POST['description'] ?? '');

    // Validation
    if ($category_name === '') {
        $errors['category_name'] = "Category Name is required.";
    } elseif (strlen($category_name) < 3) {
        $errors['category_name'] = "Category Name must be at least 3 characters.";
    }

    // Duplicate check
    if (!isset($errors['category_name'])) {
        $check = $pdo->prepare(
            "SELECT COUNT(*) FROM asset_categories WHERE category_name = :category_name"
        );
        $check->execute([':category_name' => $category_name]);

        if ($check->fetchColumn() > 0) {
            $errors['category_name'] = "Category Name already exists.";
        }
    }

    // Insert if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO asset_categories (category_name, description)
             VALUES (:category_name, :description)"
        );
        $stmt->execute([
            ':category_name' => $category_name,
            ':description'   => $description
        ]);

        $category_id = $pdo->lastInsertId();

        header("Location: view_category.php?id=$category_id&success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Asset Category | eAssets</title>
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
        <h4>CONFIGURATION &gt; Asset Category &gt; New Record</h4>
    </div>

    <form method="post" id="categoryForm">

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">
                Category Name <span class="text-danger">*</span> :
            </label>
            <div class="col-sm-10">
                <input type="text"
                       name="category_name"
                       class="form-control"
                       value="<?= htmlspecialchars($category_name) ?>">

                <!-- INLINE ERROR MESSAGE -->
                <?php if (isset($errors['category_name'])): ?>
                    <div style="color:red; font-size:13px;">
                        <?= htmlspecialchars($errors['category_name']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">Description :</label>
            <div class="col-sm-10">
                <textarea name="description"
                          class="form-control"
                          rows="4"><?= htmlspecialchars($description) ?></textarea>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-10 offset-sm-2 text-end">
                <a href="config_category.php" class="btn btn-secondary">Back</a>
                <button type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#confirmModal">
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
        <p class="mt-3">Are you sure to save?</p>

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

<script>
document.getElementById('confirmSave').addEventListener('click', function () {
    document.getElementById('categoryForm').submit();
});
</script>
<script>
document.querySelector('input[name="category_name"]').addEventListener('input', function () {
    this.classList.remove('is-invalid');
});
</script>

</body>
</html>

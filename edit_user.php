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
    header("Location: config_user.php");
    exit();
}

$id = (int) $_GET['id'];

// Fetch user for pre-fill
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: config_user.php");
    exit();
}

$errors = [];

$staff_name = $user['staff_name'];
$staff_id   = $user['staff_id'];
$user_id_db = $user['user_id'];
$email      = $user['email'];
$position   = $user['position'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $staff_name = trim($_POST['staff_name'] ?? '');
    $staff_id   = trim($_POST['staff_id'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $position   = trim($_POST['position'] ?? '');

    // === VALIDATION ===
    if ($staff_name === '') $errors['staff_name'] = "Staff Name is required.";
    if ($staff_id === '')   $errors['staff_id']   = "Staff ID is required.";
    if ($email === '')      $errors['email']      = "Email is required.";

    // === DUPLICATE CHECK (EMAIL) ===
    if (!isset($errors['email'])) {
        $check = $pdo->prepare(
            "SELECT COUNT(*) FROM users WHERE email = :email AND id != :id"
        );
        $check->execute([':email' => $email, ':id' => $id]);

        if ($check->fetchColumn() > 0) {
            $errors['email'] = "Email already exists.";
        }
    }

    // === UPDATE DATA ===
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE users SET
                staff_name = :staff_name,
                staff_id   = :staff_id,
                email      = :email,
                position   = :position
            WHERE id = :id
        ");

        $stmt->execute([
            ':staff_name' => $staff_name,
            ':staff_id'   => $staff_id,
            ':email'      => $email,
            ':position'   => $position,
            ':id'         => $id
        ]);

        header("Location: view_user.php?id=" . $id . "&success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit System User | eAssets</title>
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
        <h5>CONFIGURATION &gt; System User &gt; Update</h5>
    </div>

    <form method="post" id="editUserForm" novalidate>

        <!-- STAFF NAME -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">
                Staff Name <span class="text-danger">*</span> :
            </label>
            <div class="col-sm-10">
                <input type="text"
                       name="staff_name"
                       class="form-control <?= isset($errors['staff_name']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($staff_name) ?>">
                <?php if (isset($errors['staff_name'])): ?>
                    <div class="invalid-feedback"><?= $errors['staff_name'] ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- STAFF ID -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">
                Staff ID <span class="text-danger">*</span> :
            </label>
            <div class="col-sm-10">
                <input type="text"
                       name="staff_id"
                       class="form-control <?= isset($errors['staff_id']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($staff_id) ?>">
                <?php if (isset($errors['staff_id'])): ?>
                    <div class="invalid-feedback"><?= $errors['staff_id'] ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- USER ID (READONLY) -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">User ID :</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" value="<?= htmlspecialchars($user_id_db) ?>" readonly>
            </div>
        </div>

        <!-- EMAIL -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">
                Email <span class="text-danger">*</span> :
            </label>
            <div class="col-sm-10">
                <input type="email"
                       name="email"
                       class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($email) ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- POSITION -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">Position :</label>
            <div class="col-sm-10">
                <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($position) ?>">
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="row">
            <div class="col-sm-10 offset-sm-2 text-end">
                <a href="view_user.php?id=<?= $id ?>" class="btn btn-secondary">Back</a>

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

            <button type="button" class="btn btn-primary" id="confirmSave">Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
        </div>
    </div>
</div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Submit form after confirmation
document.getElementById('confirmSave').addEventListener('click', function () {
    document.getElementById('editUserForm').submit();
});
</script>

</body>
</html>

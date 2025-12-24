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

// Fetch user
$stmt = $pdo->prepare(
    "SELECT id, staff_name, staff_id, user_id, email, position, status 
     FROM users 
     WHERE id = :id"
);
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: config_user.php");
    exit();
}

// Success flag
$showSuccess = (isset($_GET['success']) && $_GET['success'] == 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View System User | eAssets</title>
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
        <h5>CONFIGURATION &gt; System User &gt; View</h5>
    </div>

    <div class="card">
        <div class="card-body">

            <!-- STAFF NAME -->
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Staff Name :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['staff_name']) ?>" readonly>
                </div>
            </div>

            <!-- STAFF ID -->
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Staff ID :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['staff_id']) ?>" readonly>
                </div>
            </div>

            <!-- USER ID -->
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">User ID :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['user_id']) ?>" readonly>
                </div>
            </div>

            <!-- EMAIL -->
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Email :</label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                </div>
            </div>

            <!-- POSITION -->
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Position :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['position']) ?>" readonly>
                </div>
            </div>

            <!-- STATUS -->
            

            <!-- ACTION BUTTONS -->
            <div class="text-end">
                <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-primary">Update</a>
                <button class="btn btn-danger" data-id="<?= $user['id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                <a href="config_user.php" class="btn btn-secondary">Back</a>
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
            <p class="mt-3">Are you sure to delete this user?</p>

            <form method="post" action="delete_user.php">
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
// When Delete button is clicked, pass the user ID to the modal
const deleteBtn = document.querySelector('button[data-bs-target="#deleteModal"]');
deleteBtn.addEventListener('click', () => {
    const id = deleteBtn.getAttribute('data-id');
    document.getElementById('deleteId').value = id;
});

// AUTO HIDE SUCCESS MESSAGE
setTimeout(() => {
    const alert = document.querySelector('.alert-success');
    if (alert) alert.style.display = 'none';
}, 3000);
</script>

</body>
</html>

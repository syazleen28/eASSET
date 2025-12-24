<?php
session_start();
require_once 'config/database.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
     header("Location: login.php");
     exit();
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$errors = [];
$success = '';
$staff_name = '';
$staff_id   = '';
$user_id    = '';
$email      = '';
$position   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_name = trim($_POST['staff_name'] ?? '');
    $staff_id   = trim($_POST['staff_id'] ?? '');
    $user_id    = trim($_POST['user_id'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $position   = trim($_POST['position'] ?? '');

    // Validation
    if ($staff_name === '') $errors['staff_name'] = "Staff Name is required.";
    if ($staff_id === '')   $errors['staff_id']   = "Staff ID is required.";
    if ($user_id === '')    $errors['user_id']    = "User ID is required.";
    if ($email === '')      $errors['email']      = "Email is required.";

    // Duplicate check
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = :user_id OR email = :email");
    $stmt->execute([':user_id' => $user_id, ':email' => $email]);
    if ($stmt->fetchColumn() > 0) $errors['user_id'] = "User ID or Email already exists.";

    if (empty($errors)) {
        // Generate activation token
        $activation_token = bin2hex(random_bytes(16));

        // Insert user with status 'pending'
        $stmt = $pdo->prepare("
            INSERT INTO users (staff_name, staff_id, email, position, user_id, status, activation_token) 
            VALUES (:staff_name, :staff_id, :email, :position, :user_id, 'pending', :token)
        ");
        $stmt->execute([
            ':staff_name' => $staff_name,
            ':staff_id'   => $staff_id,
            ':email'      => $email,
            ':position'   => $position,
            ':user_id'    => $user_id,
            ':token'      => $activation_token
        ]);

        // Send activation email
        $activation_link = "http://localhost/eASSET/activate.php?token=$activation_token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'nursyazleen28032003@gmail.com';
            $mail->Password   = 'ykhu ntiv ttcu vofu';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('your_email@gmail.com', 'eASSETS Admin');
            $mail->addAddress($email, $staff_name);

            $mail->isHTML(true);
            $mail->Subject = 'Activate your account';
            $mail->Body    = "Hello $staff_name,<br><br>
                              Please click the link to activate your account:<br>
                              <a href='$activation_link'>$activation_link</a><br><br>Thank you!";

            $mail->send();
            $success = "User registered successfully! Activation email sent to $email.";
        } catch (Exception $e) {
            $errors['mail'] = "Mailer Error: " . $mail->ErrorInfo;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add System User | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">

<style>
#confirmModal .modal-dialog { max-width: 350px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="mb-4">
        <h5>CONFIGURATION &gt; System User &gt; New Record</h5>
    </div>

    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach($errors as $e) echo htmlspecialchars($e)."<br>"; ?>
        </div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" id="userForm" novalidate>

        <!-- STAFF NAME -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">
                Staff Name <span class="text-danger">*</span> :
            </label>
            <div class="col-sm-10">
                <input type="text" name="staff_name"
                       class="form-control <?= isset($errors['staff_name']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($staff_name) ?>">
            </div>
        </div>

        <!-- STAFF ID -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">
                Staff ID <span class="text-danger">*</span> :
            </label>
            <div class="col-sm-10">
                <input type="text" name="staff_id"
                       id="staff_id"
                       class="form-control <?= isset($errors['staff_id']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($staff_id) ?>">
            </div>
        </div>

        <!-- USER ID -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">
                User ID <span class="text-danger">*</span> :
            </label>
            <div class="col-sm-10">
                <input type="text" name="user_id"
                       id="user_id"
                       class="form-control <?= isset($errors['user_id']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($user_id) ?>" readonly>
            </div>
        </div>

        <!-- EMAIL -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">
                Email <span class="text-danger">*</span> :
            </label>
            <div class="col-sm-10">
                <input type="email" name="email"
                       class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($email) ?>">
            </div>
        </div>

        <!-- POSITION -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">
                Position :
            </label>
            <div class="col-sm-10">
                <input type="text" name="position"
                       class="form-control"
                       value="<?= htmlspecialchars($position) ?>">
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="row">
            <div class="col-sm-10 offset-sm-2 text-end">
                <a href="config_user.php" class="btn btn-secondary">Back</a>
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
            <i class="bi bi-exclamation-circle text-warning" style="font-size: 4rem;"></i>
            <p class="mt-3">Are you sure you want to save?</p>
            <button type="button" class="btn btn-primary" id="confirmSave">Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
        </div>
    </div>
</div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('confirmSave').addEventListener('click', function () {
    document.getElementById('userForm').submit();
});

const staffInput = document.getElementById('staff_id');
const userInput  = document.getElementById('user_id');
staffInput.addEventListener('input', function() {
    userInput.value = staffInput.value;
});
</script>

</body>
</html>

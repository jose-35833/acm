<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ========================= ADMIN LOGIN =========================
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $adminResult = $stmt->get_result();

    if ($adminResult->num_rows > 0) {
        $admin = $adminResult->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['name'] = $admin['full_name'];
            $_SESSION['role'] = $admin['role'] ?? 'admin';
            header("Location: admin/index.php");
            exit;
        }
    }

    // ========================= MODERATOR LOGIN =========================
    $stmt = $conn->prepare("SELECT * FROM moderators WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $modResult = $stmt->get_result();

    if ($modResult->num_rows > 0) {
        $moderator = $modResult->fetch_assoc();
        if (password_verify($password, $moderator['password'])) {
            $_SESSION['user_id'] = $moderator['moderator_id'];
            $_SESSION['moderator_id'] = $moderator['moderator_id'];
            $_SESSION['email'] = $moderator['email'];
            $_SESSION['name'] = $moderator['full_name'];
            $_SESSION['role'] = 'moderator';
            header("Location: moderator/moderator_dashboard.php");
            exit;
        }
    }

    // ========================= MEMBER LOGIN =========================
    $stmt = $conn->prepare("SELECT * FROM members WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $memberResult = $stmt->get_result();

    if ($memberResult->num_rows > 0) {
        $member = $memberResult->fetch_assoc();

        // Check password and active status
        if (password_verify($password, $member['password'])) {
            if ($member['status'] !== 'active') {
                $error = "Your account is currently inactive. Please contact an admin.";
            } else {
                $_SESSION['user_id'] = $member['member_id'];
                $_SESSION['member_id'] = $member['member_id'];
                $_SESSION['email'] = $member['email'];
                $_SESSION['name'] = $member['full_name'];
                $_SESSION['role'] = 'member';
                header("Location: member/index.php");
                exit;
            }
        }
    }

    // If all failed
    if (empty($error)) {
        $error = "Invalid email or password.";
    }
}

include_once 'includes/header.php';
?>

<div class="bg-white p-4 shadow-sm rounded col-md-6 mx-auto mt-5">
    <h2 class="fw-bold mb-3 text-center text-dark">Login</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" id="email" class="form-control" required placeholder="you@example.com">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required placeholder="Enter your password">
        </div>

        <button type="submit" class="btn btn-warning text-dark fw-semibold w-100">Login</button>

        <p class="text-center mt-3">
            Don’t have an account? <a href="register.php" class="text-decoration-none text-primary">Register</a>
        </p>
    </form>
</div>

<?php include_once 'includes/footer.php'; ?>

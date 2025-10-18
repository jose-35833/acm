<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../includes/db.php';

// Redirect if not logged in or not a moderator
if (!isset($_SESSION['moderator_id'])) {
    header('Location: ../login.php');
    exit;
}

$moderator_id = (int) $_SESSION['moderator_id'];
$moderator = null;

if (isset($conn)) {
    $stmt = $conn->prepare('SELECT moderator_id, name, email, role FROM moderators WHERE moderator_id = ? LIMIT 1');
    $stmt->bind_param('i', $moderator_id);
    $stmt->execute();
    $moderator = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
}

if (!$moderator) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ChurchHub Moderator Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f6fa; margin: 0; padding: 0; }
.topbar { height: 60px; background: #fff; border-bottom: 1px solid #dee2e6; padding: 10px 20px; position: fixed; top: 0; left: 250px; right: 0; display: flex; justify-content: space-between; align-items: center; z-index: 1050; transition: left 0.3s ease; }
@media(max-width: 991.98px){ .topbar { left: 0; } }
</style>
</head>
<body>

<!-- Topbar -->
<div class="topbar d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
        <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary d-lg-none"><i class="bi bi-list"></i></button>
        <?php if (file_exists('../uploads/logo.png')): ?><img src="../uploads/logo.png" alt="Logo" style="height: 40px; margin-right: 10px;"><?php endif; ?>
        <h5 class="mb-0 fw-bold text-dark">Moderator Dashboard</h5>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="me-3 d-flex align-items-center">
            <?php $profile_img = "../uploads/profiles/moderator_$moderator_id.png"; if (file_exists($profile_img)): ?><img src="<?php echo $profile_img; ?>" alt="Profile" style="height: 30px; width: 30px; border-radius: 50%; margin-right: 5px;"><?php else: ?>👤<?php endif; ?>
            <?= htmlspecialchars($moderator['name']); ?>
        </span>
        <a href="../logout.php" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

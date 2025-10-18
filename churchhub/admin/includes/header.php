<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$user = null;
$user_name = '';

if (isset($_SESSION['admin_id'])) {
    $user_id = (int) $_SESSION['admin_id'];
    $table = 'admins';
    $id_field = 'admin_id';
    $name_field = 'full_name';
} elseif (isset($_SESSION['moderator_id'])) {
    $user_id = (int) $_SESSION['moderator_id'];
    $table = 'moderators';
    $id_field = 'moderator_id';
    $name_field = 'name';
} else {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

if (isset($conn)) {
    $stmt = $conn->prepare("SELECT $id_field, $name_field FROM $table WHERE $id_field = ? LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
}

if (!$user) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

$user_name = $user[$name_field];
$admin_id = $user_id; // For compatibility with existing code
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ChurchHub Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana,sans-serif; background:#f5f6fa; margin:0; padding:0; }
.topbar { height:60px; background:#fff; border-bottom:1px solid #dee2e6; padding:10px 20px; position:fixed; top:0; left:0; right:0; display:flex; justify-content:space-between; align-items:center; z-index:1050; }
.topbar h5 { margin:0; font-weight:bold; }
#sidebarToggle { display:none; }
@media(max-width:991.98px){ #sidebarToggle { display:inline-block; } }
.main-content { margin-left: 0; transition: margin-left 0.3s ease; }
@media(min-width:992px){ .main-content { margin-left: 250px; } }
</style>
</head>
<body>

<div class="topbar d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
        <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary"><i class="bi bi-list"></i></button>
        <?php if (file_exists('../uploads/logo.png')): ?><img src="../uploads/logo.png" alt="Logo" style="height: 40px; margin-right: 10px;"><?php endif; ?>
        <h5>Admin Dashboard</h5>
    </div>
    <div>
        <span class="me-3 d-flex align-items-center">
            <?php $profile_img = "../uploads/profiles/admin_$user_id.png"; if (file_exists($profile_img)): ?><img src="<?php echo $profile_img; ?>" alt="Profile" style="height: 30px; width: 30px; border-radius: 50%; margin-right: 5px;"><?php else: ?>👤<?php endif; ?>
            <?= htmlspecialchars($user_name); ?>
        </span>
        <a href="../logout.php" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<div class="main-content">
<?php include_once "sidebar.php"; ?>

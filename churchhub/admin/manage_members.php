<?php
include_once "includes/header.php";

// Ensure admin logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$ip = $_SERVER['REMOTE_ADDR'];

// =================== DEACTIVATE MEMBER ===================
if (isset($_GET['deactivate'])) {
    $member_id = intval($_GET['deactivate']);
    $stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();

    if ($member) {
        $conn->query("UPDATE members SET status = 'inactive' WHERE member_id = $member_id");
        $email = $member['email'];
        $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
                      VALUES ($admin_id, 'DEACTIVATE', 'members', $member_id, 'Deactivated member $email', '$ip')");
        header("Location: manage_members.php?msg=deactivated");
        exit;
    }
}

// =================== REACTIVATE MEMBER ===================
if (isset($_GET['reactivate'])) {
    $member_id = intval($_GET['reactivate']);
    $stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();

    if ($member) {
        $conn->query("UPDATE members SET status = 'active' WHERE member_id = $member_id");
        $email = $member['email'];
        $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
                      VALUES ($admin_id, 'REACTIVATE', 'members', $member_id, 'Reactivated member $email', '$ip')");
        header("Location: manage_members.php?msg=reactivated");
        exit;
    }
}

// =================== PROMOTE / DEMOTE ===================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $member_id = intval($_GET['id']);
    $action = $_GET['action'];

    $stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();

    if (!$member) {
        header("Location: manage_members.php?msg=not_found");
        exit;
    }

    $current_role = $member['role'];
    $email = $member['email'];
    $name = $member['full_name'];
    $password = $member['password'];

    if ($email == $_SESSION['email']) {
        header("Location: manage_members.php?msg=self_demotion");
        exit;
    }

    $new_role = $current_role;

    if ($action === 'promote') {
        if ($current_role === 'member') $new_role = 'moderator';
        elseif ($current_role === 'moderator') $new_role = 'admin';
    } elseif ($action === 'demote') {
        if ($current_role === 'admin') $new_role = 'moderator';
        elseif ($current_role === 'moderator') $new_role = 'member';
    }

    if ($new_role !== $current_role) {
        $stmt = $conn->prepare("UPDATE members SET role = ? WHERE member_id = ?");
        $stmt->bind_param("si", $new_role, $member_id);
        $stmt->execute();

        // Update corresponding tables
        if ($new_role === 'moderator') {
            $check = $conn->prepare("SELECT * FROM moderators WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            if (!$exists) {
                $insert = $conn->prepare("INSERT INTO moderators (name, email, password) VALUES (?, ?, ?)");
                $insert->bind_param("sss", $name, $email, $password);
                $insert->execute();
            }
        }

        if ($new_role === 'admin') {
            $check = $conn->prepare("SELECT * FROM admins WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            if (!$exists) {
                $insert = $conn->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, 'admin')");
                $insert->bind_param("sss", $name, $email, $password);
                $insert->execute();
            }
        }

        // Log the promotion/demotion
        $log = $conn->prepare("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
                               VALUES (?, 'UPDATE', 'members', ?, ?, ?)");
        $desc = "Changed role from $current_role to $new_role";
        $log->bind_param("iiss", $admin_id, $member_id, $desc, $ip);
        $log->execute();
    }

    header("Location: manage_members.php?msg=role_updated");
    exit;
}

// =================== FETCH MEMBERS ===================
$members = $conn->query("SELECT * FROM members ORDER BY created_at DESC");
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-dark"><i class="bi bi-people-fill"></i> Manage Members</h3>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?php
            switch ($_GET['msg']) {
                case 'deactivated': echo "✅ Member deactivated successfully!"; break;
                case 'reactivated': echo "✅ Member reactivated successfully!"; break;
                case 'role_updated': echo "✅ Member role updated successfully!"; break;
                case 'self_demotion': echo "⚠️ You cannot change your own role!"; break;
                case 'not_found': echo "❌ Member not found."; break;
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if ($members->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($row = $members->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= htmlspecialchars($row['full_name']); ?></td>
                                    <td><?= htmlspecialchars($row['email']); ?></td>
                                    <td><?= htmlspecialchars($row['phone']); ?></td>
                                    <td>
                                        <span class="badge <?= $row['role'] == 'admin' ? 'bg-danger' : ($row['role'] == 'moderator' ? 'bg-info text-dark' : 'bg-secondary'); ?>">
                                            <?= ucfirst($row['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $row['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?= ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?= date("M d, Y", strtotime($row['created_at'])); ?></td>
                                    <td class="d-flex flex-wrap gap-2">
                                        <a href="?action=promote&id=<?= $row['member_id']; ?>" 
                                           class="btn btn-sm btn-outline-success"
                                           onclick="return confirm('Promote this user?');">
                                           <i class="bi bi-arrow-up"></i> Promote
                                        </a>
                                        <a href="?action=demote&id=<?= $row['member_id']; ?>" 
                                           class="btn btn-sm btn-outline-warning"
                                           onclick="return confirm('Demote this user?');">
                                           <i class="bi bi-arrow-down"></i> Demote
                                        </a>
                                        <?php if ($row['status'] === 'active'): ?>
                                            <a href="?deactivate=<?= $row['member_id']; ?>" 
                                               onclick="return confirm('Deactivate this member?');"
                                               class="btn btn-sm btn-danger">
                                               <i class="bi bi-person-dash"></i> Deactivate
                                            </a>
                                        <?php else: ?>
                                            <a href="?reactivate=<?= $row['member_id']; ?>" 
                                               onclick="return confirm('Reactivate this member?');"
                                               class="btn btn-sm btn-success">
                                               <i class="bi bi-person-check"></i> Reactivate
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No members found in the system.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>

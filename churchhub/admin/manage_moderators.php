<?php
include_once "includes/header.php";

// Ensure admin logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$ip = $_SERVER['REMOTE_ADDR'];

// =================== DEACTIVATE MODERATOR ===================
if (isset($_GET['deactivate'])) {
    $moderator_id = intval($_GET['deactivate']);
    $stmt = $conn->prepare("SELECT * FROM moderators WHERE moderator_id = ?");
    $stmt->bind_param("i", $moderator_id);
    $stmt->execute();
    $moderator = $stmt->get_result()->fetch_assoc();

    if ($moderator) {
        $conn->query("UPDATE moderators SET status = 'inactive' WHERE moderator_id = $moderator_id");
        $email = $moderator['email'];
        $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
                      VALUES ($admin_id, 'DEACTIVATE', 'moderators', $moderator_id, 'Deactivated moderator $email', '$ip')");
        header("Location: manage_moderators.php?msg=deactivated");
        exit;
    }
}

// =================== REACTIVATE MODERATOR ===================
if (isset($_GET['reactivate'])) {
    $moderator_id = intval($_GET['reactivate']);
    $stmt = $conn->prepare("SELECT * FROM moderators WHERE moderator_id = ?");
    $stmt->bind_param("i", $moderator_id);
    $stmt->execute();
    $moderator = $stmt->get_result()->fetch_assoc();

    if ($moderator) {
        $conn->query("UPDATE moderators SET status = 'active' WHERE moderator_id = $moderator_id");
        $email = $moderator['email'];
        $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
                      VALUES ($admin_id, 'REACTIVATE', 'moderators', $moderator_id, 'Reactivated moderator $email', '$ip')");
        header("Location: manage_moderators.php?msg=reactivated");
        exit;
    }
}

// =================== FETCH MODERATORS ===================
$moderators = $conn->query("SELECT * FROM moderators ORDER BY created_at DESC");
?>

<div class="container-fluid" style="padding-top: 80px;"> <!-- add padding-top to prevent header overlap -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-dark"><i class="bi bi-people-fill"></i> Manage Moderators</h3>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?php
            switch ($_GET['msg']) {
                case 'deactivated': echo "✅ Moderator deactivated successfully!"; break;
                case 'reactivated': echo "✅ Moderator reactivated successfully!"; break;
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if ($moderators && $moderators->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($row = $moderators->fetch_assoc()): ?>
                                <?php
                                $name = $row['name'] ?? 'Unknown';
                                $status = $row['status'] ?? 'inactive';
                                ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= htmlspecialchars($name); ?></td>
                                    <td><?= htmlspecialchars($row['email'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge <?= $status === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?= ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td><?= isset($row['created_at']) ? date("M d, Y", strtotime($row['created_at'])) : '-'; ?></td>
                                    <td class="d-flex flex-wrap gap-2">
                                        <?php if ($status === 'active'): ?>
                                            <a href="?deactivate=<?= $row['moderator_id']; ?>"
                                               onclick="return confirm('Deactivate this moderator?');"
                                               class="btn btn-sm btn-danger">
                                               <i class="bi bi-person-dash"></i> Deactivate
                                            </a>
                                        <?php else: ?>
                                            <a href="?reactivate=<?= $row['moderator_id']; ?>"
                                               onclick="return confirm('Reactivate this moderator?');"
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
                <p class="text-muted">No moderators found in the system.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>

<?php
include_once 'includes/header.php';
include_once 'includes/sidebar.php';

// Optional: filter logs by table or admin
$filter_admin = $_GET['admin_id'] ?? '';
$filter_table = $_GET['table'] ?? '';

$query = "SELECT l.*, a.full_name AS admin_name 
          FROM admin_logs l 
          LEFT JOIN admins a ON l.admin_id = a.admin_id 
          WHERE 1=1";

if ($filter_admin) {
    $filter_admin = intval($filter_admin);
    $query .= " AND l.admin_id = $filter_admin";
}

if ($filter_table) {
    $filter_table = $conn->real_escape_string($filter_table);
    $query .= " AND l.table_name = '$filter_table'";
}

$query .= " ORDER BY l.timestamp DESC";

$logs = $conn->query($query);

// Fetch admins for filter dropdown
$admins_list = $conn->query("SELECT admin_id, full_name FROM admins");
?>

<div class="container-fluid px-4">
    <h3 class="fw-bold mb-4 text-dark border-bottom pb-2">Activity Logs</h3>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm p-3">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Filter by Admin</label>
                <select name="admin_id" class="form-select">
                    <option value="">All Admins</option>
                    <?php while ($a = $admins_list->fetch_assoc()): ?>
                        <option value="<?= $a['admin_id'] ?>" <?= ($filter_admin==$a['admin_id'])?'selected':'' ?>>
                            <?= htmlspecialchars($a['full_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Filter by Table</label>
                <input type="text" name="table" class="form-control" placeholder="Table name" value="<?= htmlspecialchars($filter_table) ?>">
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="view_logs.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">All Logs</h5>
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Record ID</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($logs->num_rows > 0):
                        $count = 1;
                        while ($log = $logs->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?= $count++; ?></td>
                            <td><?= htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?></td>
                            <td><?= htmlspecialchars($log['action']); ?></td>
                            <td><?= htmlspecialchars($log['table_name']); ?></td>
                            <td><?= htmlspecialchars($log['record_id']); ?></td>
                            <td><?= htmlspecialchars(substr($log['description'],0,60)); ?>...</td>
                            <td><?= htmlspecialchars($log['ip_address']); ?></td>
                            <td><?= $log['timestamp']; ?></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="8" class="text-center">No logs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>

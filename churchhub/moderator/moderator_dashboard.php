<?php
include_once "includes/mod_header.php";
include_once "includes/mod_sidebard.php";

// Fetch stats
$total_members = $conn->query("SELECT COUNT(*) AS total FROM members")->fetch_assoc()['total'];
$total_events = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()['total'];
$total_sermons = $conn->query("SELECT COUNT(*) AS total FROM sermons")->fetch_assoc()['total'];
$total_messages = $conn->query("SELECT COUNT(*) AS total FROM messages")->fetch_assoc()['total'];
$total_announcements = $conn->query("SELECT COUNT(*) AS total FROM announcements")->fetch_assoc()['total'];

// Fetch recent logs by moderator
$logs = $conn->query("
    SELECT admin_logs.*, moderators.name
    FROM admin_logs
    LEFT JOIN moderators ON admin_logs.admin_id = moderators.moderator_id
    WHERE admin_logs.admin_id = $moderator_id
    ORDER BY timestamp DESC
    LIMIT 6
");
?>

<div class="container-fluid" style="padding-top: 80px;">
    <div class="row mb-4">
        <h3 class="fw-bold text-dark mb-3"><i class="bi bi-speedometer2"></i> Dashboard Overview</h3>

        <!-- Stats Cards -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <h5>Total Members</h5>
                    <h3><?= $total_members; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body">
                    <h5>Events</h5>
                    <h3><?= $total_events; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 bg-warning text-dark">
                <div class="card-body">
                    <h5>Sermons</h5>
                    <h3><?= $total_sermons; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 bg-danger text-white">
                <div class="card-body">
                    <h5>Messages</h5>
                    <h3><?= $total_messages; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 bg-secondary text-white">
                <div class="card-body">
                    <h5>Announcements</h5>
                    <h3><?= $total_announcements; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Logs -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold">
            <i class="bi bi-activity text-warning"></i> Recent Moderator Activity
        </div>
        <div class="card-body">
            <?php if ($logs->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Moderator</th>
                                <th>Action</th>
                                <th>Table</th>
                                <th>Record ID</th>
                                <th>Description</th>
                                <th>Timestamp</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($log = $logs->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['name'] ?? 'Unknown'); ?></td>
                                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($log['action']); ?></span></td>
                                    <td><?= htmlspecialchars($log['table_name']); ?></td>
                                    <td><?= htmlspecialchars($log['record_id']); ?></td>
                                    <td><?= htmlspecialchars($log['description']); ?></td>
                                    <td><?= date("M d, Y H:i", strtotime($log['timestamp'])); ?></td>
                                    <td><?= htmlspecialchars($log['ip_address']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No recent moderator activity recorded.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4">
        <h4>Quick Actions</h4>
        <div class="d-flex flex-wrap gap-2">
            <a href="members.php" class="btn btn-primary">Manage Members</a>
            <a href="events.php" class="btn btn-success">Manage Events</a>
            <a href="sermons.php" class="btn btn-warning">Manage Sermons</a>
            <a href="messages.php" class="btn btn-info">View Messages</a>
            <a href="announcements.php" class="btn btn-secondary">Manage Announcements</a>
            <a href="profile.php" class="btn btn-dark">My Profile</a>
        </div>
    </div>
</div>

<?php include_once "includes/mod_footer.php"; ?>

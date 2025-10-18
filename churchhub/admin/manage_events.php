<?php
include_once "includes/header.php";

// --- Handle Delete Event ---
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    if ($stmt->execute()) {
        // Log deletion
        $conn->query("
            INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
            VALUES (
                '{$_SESSION['admin_id']}',
                'Delete',
                'events',
                '$event_id',
                'Deleted event record',
                '{$_SERVER['REMOTE_ADDR']}'
            )
        ");
        echo "<script>alert('Event deleted successfully'); window.location='manage_events.php';</script>";
    }
}

// --- Fetch All Events ---
$events = $conn->query("SELECT * FROM events ORDER BY event_date ASC");
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-dark"><i class="bi bi-calendar-event-fill"></i> Manage Events</h3>
        <a href="add_event.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New Event</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if ($events->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            while ($event = $events->fetch_assoc()):
                                // Get admin name who created it
                                $creator = $conn->query("SELECT full_name FROM admins WHERE admin_id={$event['created_by']}")->fetch_assoc();
                            ?>
                                <tr>
                                    <td><?= $count++; ?></td>
                                    <td><?= htmlspecialchars($event['title']); ?></td>
                                    <td><?= date("M d, Y", strtotime($event['event_date'])); ?></td>
                                    <td><?= htmlspecialchars($event['location']); ?></td>
                                    <td><?= $creator['full_name'] ?? 'Unknown'; ?></td>
                                    <td>
                                        <a href="edit_event.php?id=<?= $event['event_id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?delete=<?= $event['event_id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this event?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center">No events found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>

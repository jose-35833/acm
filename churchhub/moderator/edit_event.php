<?php
include_once "includes/mod_header.php";
include_once "includes/mod_sidebard.php";

$moderator_id = $_SESSION['moderator_id'];

if (!isset($_GET['id'])) {
    echo "<script>window.location='events.php';</script>";
    exit;
}

$event_id = intval($_GET['id']);
$event = $conn->query("SELECT * FROM events WHERE event_id = $event_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $location = trim($_POST['location']);

    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, location=? WHERE event_id=?");
    $stmt->bind_param("ssssi", $title, $description, $event_date, $location, $event_id);

    if ($stmt->execute()) {
        // Log update
        $conn->query("
            INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
            VALUES (
                '$moderator_id',
                'Edit',
                'events',
                '$event_id',
                'Updated event titled \"$title\"',
                '{$_SERVER['REMOTE_ADDR']}'
            )
        ");

        echo "<script>alert('Event updated successfully!'); window.location='events.php';</script>";
    } else {
        echo "<script>alert('Error updating event. Please try again.');</script>";
    }
}
?>

<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h3 class="fw-bold mb-3 text-dark"><i class="bi bi-pencil"></i> Edit Event</h3>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Event Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($event['title']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Event Description</label>
                    <textarea name="description" rows="5" class="form-control" required><?= htmlspecialchars($event['description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Event Date</label>
                    <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($event['event_date']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Location</label>
                    <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($event['location']); ?>" required>
                </div>

                <button type="submit" class="btn btn-warning text-dark px-4">Update Event</button>
                <a href="events.php" class="btn btn-secondary px-4">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include_once "includes/mod_footer.php"; ?>

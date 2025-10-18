<?php
include_once "includes/header.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $location = trim($_POST['location']);
    $created_by = $_SESSION['admin_id'];

    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, location, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $title, $description, $event_date, $location, $created_by);

    if ($stmt->execute()) {
        // Log creation
        $conn->query("
            INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
            VALUES (
                '$created_by',
                'Add',
                'events',
                '{$stmt->insert_id}',
                'Added new event titled \"$title\"',
                '{$_SERVER['REMOTE_ADDR']}'
            )
        ");

        echo "<script>alert('Event added successfully!'); window.location='manage_events.php';</script>";
    } else {
        echo "<script>alert('Error adding event. Please try again.');</script>";
    }
}
?>

<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h3 class="fw-bold mb-3 text-dark"><i class="bi bi-plus-circle"></i> Add New Event</h3>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Event Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Event Description</label>
                    <textarea name="description" rows="5" class="form-control" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Event Date</label>
                    <input type="date" name="event_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Location</label>
                    <input type="text" name="location" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success px-4">Add Event</button>
                <a href="manage_events.php" class="btn btn-secondary px-4">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>

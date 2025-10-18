<?php
include_once "includes/mod_header.php";
include_once "includes/mod_sidebard.php";

$moderator_id = $_SESSION['moderator_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $video_link = trim($_POST['video_link']);
    $posted_by = $moderator_id;

    $stmt = $conn->prepare("INSERT INTO sermons (title, description, video_link, posted_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $title, $description, $video_link, $posted_by);

    if ($stmt->execute()) {
        // Log action
        $conn->query("
            INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
            VALUES (
                '$posted_by',
                'Add',
                'sermons',
                '{$stmt->insert_id}',
                'Added sermon titled \"$title\"',
                '{$_SERVER['REMOTE_ADDR']}'
            )
        ");
        echo "<script>alert('Sermon added successfully!'); window.location='sermons.php';</script>";
    } else {
        echo "<script>alert('Error adding sermon. Please try again.');</script>";
    }
}
?>

<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h3 class="fw-bold mb-3 text-dark"><i class="bi bi-plus-circle"></i> Add New Sermon</h3>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Sermon Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Sermon Description</label>
                    <textarea name="description" rows="5" class="form-control" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">YouTube or Video Link</label>
                    <input type="url" name="video_link" class="form-control" placeholder="https://youtube.com/...">
                </div>

                <button type="submit" class="btn btn-success px-4">Add Sermon</button>
                <a href="sermons.php" class="btn btn-secondary px-4">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include_once "includes/mod_footer.php"; ?>

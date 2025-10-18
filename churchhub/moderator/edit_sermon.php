<?php
include_once "includes/mod_header.php";
include_once "includes/mod_sidebard.php";

$moderator_id = $_SESSION['moderator_id'];

if (!isset($_GET['id'])) {
    echo "<script>window.location='sermons.php';</script>";
    exit;
}

$sermon_id = intval($_GET['id']);
$sermon = $conn->query("SELECT * FROM sermons WHERE sermon_id = $sermon_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $video_link = trim($_POST['video_link']);

    $stmt = $conn->prepare("UPDATE sermons SET title=?, description=?, video_link=? WHERE sermon_id=?");
    $stmt->bind_param("sssi", $title, $description, $video_link, $sermon_id);

    if ($stmt->execute()) {
        // Log update
        $conn->query("
            INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
            VALUES (
                '$moderator_id',
                'Edit',
                'sermons',
                '$sermon_id',
                'Edited sermon titled \"$title\"',
                '{$_SERVER['REMOTE_ADDR']}'
            )
        ");
        echo "<script>alert('Sermon updated successfully!'); window.location='sermons.php';</script>";
    } else {
        echo "<script>alert('Error updating sermon. Please try again.');</script>";
    }
}
?>

<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h3 class="fw-bold mb-3 text-dark"><i class="bi bi-pencil"></i> Edit Sermon</h3>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Sermon Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($sermon['title']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Sermon Description</label>
                    <textarea name="description" rows="5" class="form-control" required><?= htmlspecialchars($sermon['description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Video Link</label>
                    <input type="url" name="video_link" class="form-control" value="<?= htmlspecialchars($sermon['video_link']); ?>">
                </div>

                <button type="submit" class="btn btn-warning text-dark px-4">Update Sermon</button>
                <a href="sermons.php" class="btn btn-secondary px-4">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include_once "includes/mod_footer.php"; ?>

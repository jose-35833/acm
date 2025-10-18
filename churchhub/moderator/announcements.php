<?php
include_once 'includes/mod_header.php';
include_once 'includes/mod_sidebard.php';

$moderator_id = $_SESSION['moderator_id'];

// --- Handle Add/Edit Announcement ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $announcement_id = $_POST['announcement_id'] ?? null;

    if ($announcement_id) {
        // Update existing announcement
        $stmt = $conn->prepare("UPDATE announcements SET title=?, message=? WHERE announcement_id=?");
        $stmt->bind_param("ssi", $title, $message, $announcement_id);
        $stmt->execute();

        // Log action
        $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
                      VALUES ($moderator_id, 'Edit', 'announcements', $announcement_id, 'Edited announcement: $title', '{$_SERVER['REMOTE_ADDR']}')");

    } else {
        // Add new announcement
        $stmt = $conn->prepare("INSERT INTO announcements (title, message, posted_by) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $message, $moderator_id);
        $stmt->execute();

        $new_id = $conn->insert_id;
        $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
                      VALUES ($moderator_id, 'Add', 'announcements', $new_id, 'Added new announcement: $title', '{$_SERVER['REMOTE_ADDR']}')");
    }

    echo "<script>alert('Announcement saved successfully'); window.location='announcements.php';</script>";
}

// --- Handle Delete ---
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM announcements WHERE announcement_id=$delete_id");

    $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
                  VALUES ($moderator_id, 'Delete', 'announcements', $delete_id, 'Deleted an announcement', '{$_SERVER['REMOTE_ADDR']}')");

    echo "<script>alert('Announcement deleted successfully'); window.location='announcements.php';</script>";
}

// --- Fetch All Announcements ---
$announcements = $conn->query("SELECT a.*, ad.full_name AS admin_name
                               FROM announcements a
                               LEFT JOIN admins ad ON a.posted_by = ad.admin_id
                               ORDER BY posted_at DESC");
?>

<div class="container-fluid px-4">
    <h3 class="fw-bold mb-4 text-dark border-bottom pb-2">Manage Announcements</h3>

    <!-- Add/Edit Announcement Form -->
    <form method="POST" class="bg-white p-4 shadow-sm rounded mb-4">
        <h5>Add / Edit Announcement</h5>
        <input type="hidden" name="announcement_id" value="<?= $_GET['edit'] ?? '' ?>">

        <?php
        $editData = ['title'=>'','message'=>''];
        if (isset($_GET['edit'])) {
            $id = intval($_GET['edit']);
            $editData = $conn->query("SELECT * FROM announcements WHERE announcement_id=$id")->fetch_assoc();
        }
        ?>

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editData['title']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea name="message" rows="4" class="form-control" required><?= htmlspecialchars($editData['message']); ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Save Announcement</button>
    </form>

    <!-- List Announcements -->
    <div class="bg-white p-3 shadow-sm rounded">
        <h5 class="mb-3">All Announcements</h5>
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Posted By</th>
                    <th>Posted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($announcements->num_rows > 0):
                    $count = 1;
                    while ($row = $announcements->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= $count++; ?></td>
                        <td><?= htmlspecialchars($row['title']); ?></td>
                        <td><?= substr($row['message'], 0, 60); ?>...</td>
                        <td><?= htmlspecialchars($row['admin_name']); ?></td>
                        <td><?= $row['posted_at']; ?></td>
                        <td>
                            <a href="?edit=<?= $row['announcement_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="?delete=<?= $row['announcement_id']; ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this announcement?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6" class="text-center">No announcements found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once 'includes/mod_footer.php'; ?>

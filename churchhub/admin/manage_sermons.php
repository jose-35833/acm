<?php
include_once 'includes/header.php';
include_once 'includes/sidebar.php';

// Handle Add / Edit / Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $video_link = trim($_POST['video_link']);
    $sermon_id = $_POST['sermon_id'] ?? null;
    $admin_id = $_SESSION['admin_id'];

    if ($sermon_id) {
        // Update existing sermon
        $stmt = $conn->prepare("UPDATE sermons SET title=?, description=?, video_link=? WHERE sermon_id=?");
        $stmt->bind_param("sssi", $title, $description, $video_link, $sermon_id);
        $stmt->execute();

        // Log action
        $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description) 
                      VALUES ($admin_id, 'Update', 'sermons', $sermon_id, 'Updated sermon: $title')");
    } else {
        // Add new sermon
        $stmt = $conn->prepare("INSERT INTO sermons (title, description, video_link, posted_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $description, $video_link, $admin_id);
        $stmt->execute();

        $new_id = $conn->insert_id;
        $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description)
                      VALUES ($admin_id, 'Add', 'sermons', $new_id, 'Added new sermon: $title')");
    }

    echo "<script>alert('Sermon saved successfully');window.location='manage_sermons.php';</script>";
}

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM sermons WHERE sermon_id=$delete_id");

    $admin_id = $_SESSION['admin_id'];
    $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description)
                  VALUES ($admin_id, 'Delete', 'sermons', $delete_id, 'Deleted a sermon record')");

    echo "<script>alert('Sermon deleted successfully');window.location='manage_sermons.php';</script>";
}
?>

<div class="container-fluid px-4">
    <h3 class="fw-bold mb-4 text-dark border-bottom pb-2">Manage Sermons</h3>

    <!-- Add / Edit Sermon Form -->
    <form method="POST" class="bg-white p-4 shadow-sm rounded mb-4">
        <h5>Add / Edit Sermon</h5>
        <input type="hidden" name="sermon_id" value="<?= $_GET['edit'] ?? '' ?>">

        <?php
        $editData = ['title'=>'','description'=>'','video_link'=>''];
        if (isset($_GET['edit'])) {
            $id = intval($_GET['edit']);
            $editData = $conn->query("SELECT * FROM sermons WHERE sermon_id=$id")->fetch_assoc();
        }
        ?>

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editData['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($editData['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Video Link (YouTube, Vimeo, etc.)</label>
            <input type="url" name="video_link" class="form-control" value="<?= htmlspecialchars($editData['video_link']) ?>">
        </div>

        <button type="submit" class="btn btn-success">Save Sermon</button>
    </form>

    <!-- List Sermons -->
    <div class="bg-white p-3 shadow-sm rounded">
        <h5 class="mb-3">All Sermons</h5>
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Video Link</th>
                    <th>Posted At</th>
                    <th>Posted By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT s.*, a.full_name AS admin_name 
                                        FROM sermons s 
                                        LEFT JOIN admins a ON s.posted_by = a.admin_id 
                                        ORDER BY s.posted_at DESC");
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= $row['sermon_id']; ?></td>
                        <td><?= htmlspecialchars($row['title']); ?></td>
                        <td><?= substr($row['description'], 0, 60); ?>...</td>
                        <td>
                            <?php if ($row['video_link']): ?>
                                <a href="<?= htmlspecialchars($row['video_link']); ?>" target="_blank">View</a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= $row['posted_at']; ?></td>
                        <td><?= htmlspecialchars($row['admin_name']); ?></td>
                        <td>
                            <a href="?edit=<?= $row['sermon_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="?delete=<?= $row['sermon_id']; ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this sermon?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="text-center">No sermons found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>

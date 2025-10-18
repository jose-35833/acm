<?php
include_once 'includes/mod_header.php';
include_once 'includes/mod_sidebard.php';

$moderator_id = $_SESSION['moderator_id'];

// Fetch moderator info
$moderator = $conn->query("SELECT * FROM moderators WHERE moderator_id=$moderator_id")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // leave blank if not changing

    if ($password) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE moderators SET full_name=?, email=?, password=? WHERE moderator_id=?");
        $stmt->bind_param("sssi", $full_name, $email, $password_hashed, $moderator_id);

        $log_desc = "Updated profile including password";
    } else {
        $stmt = $conn->prepare("UPDATE moderators SET full_name=?, email=? WHERE moderator_id=?");
        $stmt->bind_param("ssi", $full_name, $email, $moderator_id);

        $log_desc = "Updated profile (without password)";
    }

    $stmt->execute();

    // Log action
    $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
                  VALUES ($moderator_id, 'Update', 'moderators', $moderator_id, '$log_desc', '{$_SERVER['REMOTE_ADDR']}')");

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/profiles/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $target_file = $target_dir . "moderator_$moderator_id.png";
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file);
    }

    echo "<script>alert('Profile updated successfully'); window.location='profile.php';</script>";
}
?>

<div class="container-fluid px-4">
    <h3 class="fw-bold mb-4 text-dark border-bottom pb-2">My Profile</h3>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow-sm rounded">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($moderator['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($moderator['email']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password <small>(leave blank to keep current)</small></label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Profile Image</label>
            <input type="file" name="profile_image" class="form-control" accept="image/*">
            <?php $img_path = "uploads/profiles/moderator_$moderator_id.png"; if (file_exists($img_path)): ?>
                <img src="<?php echo $img_path; ?>" alt="Current Profile Image" style="max-width: 100px; margin-top: 10px;">
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-success">Update Profile</button>
    </form>
</div>

<?php include_once 'includes/mod_footer.php'; ?>

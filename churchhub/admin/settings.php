<?php
include_once 'includes/header.php';
include_once 'includes/sidebar.php';

// Fetch current settings
$settings = $conn->query("SELECT * FROM site_info LIMIT 1")->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $facebook = trim($_POST['facebook']);
    $instagram = trim($_POST['instagram']);
    $whatsapp = trim($_POST['whatsapp']);
    $about = trim($_POST['about']);
    $admin_id = $_SESSION['admin_id'];

    if ($settings) {
        // Update existing row
        $stmt = $conn->prepare("UPDATE site_info SET phone=?, email=?, address=?, facebook=?, instagram=?, whatsapp=?, content=? WHERE id=?");
        $stmt->bind_param("sssssssi", $phone, $email, $address, $facebook, $instagram, $whatsapp, $about, $settings['id']);
        $stmt->execute();

        // Log
        $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
                      VALUES ($admin_id, 'Update', 'site_info', {$settings['id']}, 'Updated site settings', '{$_SERVER['REMOTE_ADDR']}')");
    } else {
        // Insert new row
        $stmt = $conn->prepare("INSERT INTO site_info (phone, email, address, facebook, instagram, whatsapp, content) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $phone, $email, $address, $facebook, $instagram, $whatsapp, $about);
        $stmt->execute();

        $new_id = $conn->insert_id;
        $conn->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address)
                      VALUES ($admin_id, 'Add', 'site_info', $new_id, 'Added site settings', '{$_SERVER['REMOTE_ADDR']}')");
    }

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $target_file = $target_dir . "logo.png";
        move_uploaded_file($_FILES['logo']['tmp_name'], $target_file);
    }

    echo "<script>alert('Settings saved successfully'); window.location='settings.php';</script>";
}
?>

<div class="container-fluid px-4">
    <h3 class="fw-bold mb-4 text-dark border-bottom pb-2">Site Settings</h3>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow-sm rounded">
        <h5>General Info</h5>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($settings['phone'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($settings['email'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($settings['address'] ?? ''); ?>">
        </div>

        <h5 class="mt-4">Social Links</h5>
        <div class="mb-3">
            <label class="form-label">Facebook</label>
            <input type="url" name="facebook" class="form-control" value="<?= htmlspecialchars($settings['facebook'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Instagram</label>
            <input type="url" name="instagram" class="form-control" value="<?= htmlspecialchars($settings['instagram'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">WhatsApp</label>
            <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($settings['whatsapp'] ?? ''); ?>">
        </div>

        <h5 class="mt-4">About Us</h5>
        <div class="mb-3">
            <textarea name="about" class="form-control" rows="5"><?= htmlspecialchars($settings['content'] ?? ''); ?></textarea>
        </div>

        <h5 class="mt-4">Site Logo</h5>
        <div class="mb-3">
            <label class="form-label">Upload Logo Image</label>
            <input type="file" name="logo" class="form-control" accept="image/*">
            <?php if (file_exists('uploads/logo.png')): ?>
                <img src="uploads/logo.png" alt="Current Logo" style="max-width: 200px; margin-top: 10px;">
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-success">Save Settings</button>
    </form>
</div>

<?php include_once 'includes/footer.php'; ?>

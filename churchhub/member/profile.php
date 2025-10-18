<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit;
}

include_once '../includes/db.php';

$member_id = $_SESSION['member_id'];

// Fetch current member info
$member = $conn->query("SELECT * FROM members WHERE member_id=$member_id")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password']; // leave blank if not changing

    if ($password) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE members SET full_name=?, email=?, phone=?, password=? WHERE member_id=?");
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $password_hashed, $member_id);
    } else {
        $stmt = $conn->prepare("UPDATE members SET full_name=?, email=?, phone=? WHERE member_id=?");
        $stmt->bind_param("sssi", $full_name, $email, $phone, $member_id);
    }

    $stmt->execute();

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/profiles/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $target_file = $target_dir . "member_$member_id.png";
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file);
    }

    echo "<script>alert('Profile updated successfully'); window.location='profile.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Member Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background: #f0f2f5; }
.card { border-radius: 12px; }
</style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm p-4">
                <!-- Back Button -->
                <div class="mb-3">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
                </div>

                <h3 class="mb-4 text-center text-primary"><i class="bi bi-person-circle"></i> My Profile</h3>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($member['full_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($member['email']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($member['phone']); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password <small>(leave blank to keep current)</small></label>
                        <input type="password" name="password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="profile_image" class="form-control" accept="image/*">
                        <?php $img_path = "uploads/profiles/member_$member_id.png"; if (file_exists($img_path)): ?>
                            <img src="<?php echo $img_path; ?>" alt="Current Profile Image" style="max-width: 100px; margin-top: 10px;">
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>

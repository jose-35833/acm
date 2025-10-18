<?php
// update_admin_password.php
// PHP >= 5.5 recommended (password_hash available). PHP 7+ preferred.

// CONFIG - change these to match your DB
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';        // your DB password
$dbName = 'churchhub'; // your DB name

// The admin info you provided
$email = 'acm@gmail.com';
$plainPassword = 'acm@23';
$fullName = 'ACM Admin'; // optional, used only for new insert
$role = 'admin';         // optional

// 1) Connect
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_errno) {
    die("DB connect failed: (" . $conn->connect_errno . ") " . $conn->connect_error . PHP_EOL);
}

// 2) Hash the password
$hashed = password_hash($plainPassword, PASSWORD_DEFAULT);
if ($hashed === false) {
    die("Password hashing failed." . PHP_EOL);
}

// 3) Check if admin with that email exists
$sqlCheck = "SELECT admin_id FROM admins WHERE email = ?";
$stmt = $conn->prepare($sqlCheck);
if (!$stmt) {
    die("Prepare failed (check): " . $conn->error . PHP_EOL);
}

// bind_param requires variables (must be a variable, not a function call)
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // exists -> update password
    $stmt->bind_result($admin_id);
    $stmt->fetch();
    $stmt->close();

    $sqlUpdate = "UPDATE admins SET password = ?, updated_at = ? WHERE admin_id = ?";
    $stmt2 = $conn->prepare($sqlUpdate);
    if (!$stmt2) {
        die("Prepare failed (update): " . $conn->error . PHP_EOL);
    }
    $updatedAt = date('Y-m-d H:i:s');
    // types: s = string, s = string, i = integer
    $stmt2->bind_param('ssi', $hashed, $updatedAt, $admin_id);
    if (!$stmt2->execute()) {
        die("Update failed: " . $stmt2->error . PHP_EOL);
    }
    echo "Password updated for admin_id={$admin_id} (email={$email})." . PHP_EOL;
    $stmt2->close();
} else {
    // not exists -> insert new admin
    $stmt->close();

    $sqlInsert = "INSERT INTO admins (full_name, email, password, role, created_at, profile_image)
                  VALUES (?, ?, ?, ?, ?, ?)";
    $stmt3 = $conn->prepare($sqlInsert);
    if (!$stmt3) {
        die("Prepare failed (insert): " . $conn->error . PHP_EOL);
    }
    $createdAt = date('Y-m-d H:i:s');
    $profileImage = null; // or a filename if you have one
    // bind_param: s = string, s = string, s = string, s = string, s = string, s = string|null
    $stmt3->bind_param('ssssss', $fullName, $email, $hashed, $role, $createdAt, $profileImage);
    if (!$stmt3->execute()) {
        die("Insert failed: " . $stmt3->error . PHP_EOL);
    }
    echo "Inserted new admin with email={$email}. New admin_id=" . $stmt3->insert_id . PHP_EOL;
    $stmt3->close();
}

$conn->close();
?>

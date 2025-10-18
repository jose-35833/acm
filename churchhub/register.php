<?php
include_once 'includes/header.php';
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if email exists
    $stmt = $conn->prepare("SELECT member_id FROM members WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<div class='alert alert-danger text-center'>Email already registered. Try logging in.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO members (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $password);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success text-center'>Registration successful! You can now <a href='login.php'>login</a>.</div>";
        } else {
            echo "<div class='alert alert-danger text-center'>Error: " . $stmt->error . "</div>";
        }
    }
}
?>


<div class="bg-white p-4 shadow-sm rounded col-md-6 mx-auto">
    <h2 class="fw-bold mb-3 text-center">Member Registration</h2>

<form action="register.php" method="POST">
    <div class="mb-3">
        <label for="full_name" class="form-label">Full Name</label>
        <input type="text" name="full_name" id="full_name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" name="email" id="email" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="text" name="phone" id="phone" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Register</button>

    <p class="text-center mt-3">
            Already have an account? <a href="login.php">Login</a>
        </p>

</form>

</div>

<?php include_once 'includes/footer.php'; ?>

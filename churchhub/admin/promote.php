<?php
require_once "../includes/db.php";
session_start();

// Only admins can promote or demote
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']); // ID of the member being changed
$newRole = $_GET['to'];   // Target role (e.g. member, moderator, admin)

// Define valid roles to prevent hacking attempts
$allowed = ['member', 'moderator', 'admin'];

if (in_array($newRole, $allowed)) {
    // Update the member's role
    $stmt = $conn->prepare("UPDATE members SET role=? WHERE member_id=?");
    $stmt->bind_param("si", $newRole, $id);
    $stmt->execute();

    echo "<script>alert('User role updated successfully!');window.location='manage_members.php';</script>";
} else {
    echo "<script>alert('Invalid role!');window.location='manage_members.php';</script>";
}
?>

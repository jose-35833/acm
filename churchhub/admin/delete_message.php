<?php
include_once 'includes/header.php';
include_once 'includes/sidebar.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Check if message ID is provided
if (!isset($_GET['id'])) {
    echo "<script>alert('No message selected');window.location='messages.php';</script>";
    exit;
}

$message_id = intval($_GET['id']);

// Verify the message exists and belongs to this admin (as sender or receiver)
$check_stmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE message_id = ? 
    AND (sender_id = ? OR receiver_id = ?)
");
$check_stmt->bind_param("iii", $message_id, $admin_id, $admin_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Message not found or unauthorized access');window.location='messages.php';</script>";
    exit;
}

// Delete the message
$delete_stmt = $conn->prepare("DELETE FROM messages WHERE message_id = ?");
$delete_stmt->bind_param("i", $message_id);
$delete_stmt->execute();

// Log deletion
$log_desc = "Deleted message ID: $message_id";
$conn->query("
    INSERT INTO admin_logs (admin_id, action, table_name, description, ip_address)
    VALUES ($admin_id, 'Delete', 'messages', '$log_desc', '{$_SERVER['REMOTE_ADDR']}')
");

echo "<script>alert('Message deleted successfully');window.location='messages.php';</script>";
exit;
?>

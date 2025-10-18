<?php
session_start();
require_once "../includes/db.php";

// Ensure member is logged in
if (!isset($_SESSION["member_id"])) {
    header("Location: ../login.php");
    exit;
}

$member_id = $_SESSION["member_id"];
$message = "";
$success = "";

// Get message details
if (isset($_GET['id'])) {
    $message_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT m.*, a.name AS admin_name 
                            FROM messages m
                            LEFT JOIN admins a ON m.sender_id = a.admin_id
                            WHERE m.message_id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $msg = $result->fetch_assoc();
} else {
    $msg = null;
}

// Reply form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $receiver_id = intval($_POST["receiver_id"]);
    $reply_text = trim($_POST["reply_text"]);

    if (!empty($reply_text)) {
        $stmt = $conn->prepare("
            INSERT INTO messages (sender_id, receiver_id, content, sender_type, receiver_type, date_sent)
            VALUES (?, ?, ?, 'member', 'admin', NOW())
        ");
        $stmt->bind_param("iis", $member_id, $receiver_id, $reply_text);

        if ($stmt->execute()) {
            $success = "Reply sent successfully!";
        } else {
            $message = "Error sending reply: " . $stmt->error;
        }
    } else {
        $message = "Please write a reply message.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reply Message - ChurchHub Member</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background-color: #f0f2f5;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.card {
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.message-box {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #dee2e6;
    height: 300px;
    overflow-y: auto;
    margin-bottom: 15px;
}
.btn-back {
    background-color: #6c757d;
    color: white;
}
.btn-back:hover {
    background-color: #5a6268;
}
</style>
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-primary"><i class="bi bi-envelope-open"></i> Reply Message</h3>
        <a href="messages.php" class="btn btn-back"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <?php if ($msg): ?>
    <div class="card p-3">
        <h5 class="text-secondary mb-2"><i class="bi bi-person-circle"></i> From: <?= htmlspecialchars($msg['admin_name'] ?? 'Church Admin') ?></h5>
        <div class="message-box">
            <?= nl2br(htmlspecialchars($msg['content'])) ?>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($message): ?>
            <div class="alert alert-danger"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($msg['sender_id']) ?>">
            <div class="mb-3">
                <textarea name="reply_text" class="form-control" rows="4" placeholder="Write your reply..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Send Reply</button>
        </form>
    </div>
    <?php else: ?>
        <div class="alert alert-warning">No message selected.</div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
session_start();
require_once "../includes/db.php";

// Ensure member logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit;
}

$member_id = $_SESSION['member_id'];

/* ===============================
   HANDLE MESSAGE SENDING
   =============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $receiver_type = $_POST['receiver_type'];
    $content = trim($_POST['content']);

    if ($receiver_id > 0 && $content !== '') {
        $stmt = $conn->prepare("
            INSERT INTO messages (sender_id, sender_type, receiver_id, receiver_type, content)
            VALUES (?, 'member', ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $member_id, $receiver_id, $receiver_type, $content);
        $stmt->execute();
        echo "<script>alert('Message sent successfully');window.location='messages.php';</script>";
        exit;
    } else {
        echo "<script>alert('Please select a recipient and write a message');</script>";
    }
}

/* ===============================
   HANDLE BULK ACTIONS
   =============================== */
if (isset($_POST['action']) && isset($_POST['selected_msgs'])) {
    $ids = implode(",", array_map('intval', $_POST['selected_msgs']));
    $action = $_POST['action'];

    if ($action === 'delete') {
        $conn->query("DELETE FROM messages WHERE message_id IN ($ids) AND receiver_id=$member_id");
        echo "<script>alert('Selected messages deleted successfully');window.location='messages.php';</script>";
        exit;
    } elseif ($action === 'mark_read') {
        $conn->query("UPDATE messages SET status='read' WHERE message_id IN ($ids) AND receiver_id=$member_id");
        echo "<script>alert('Selected messages marked as read');window.location='messages.php';</script>";
        exit;
    }
}

/* ===============================
   HANDLE SINGLE DELETE / MARK AS READ
   =============================== */
if (isset($_GET['delete'])) {
    $msg_id = intval($_GET['delete']);
    $conn->query("DELETE FROM messages WHERE message_id=$msg_id AND receiver_id=$member_id");
    echo "<script>alert('Message deleted successfully');window.location='messages.php';</script>";
    exit;
}

if (isset($_GET['mark_read'])) {
    $msg_id = intval($_GET['mark_read']);
    $conn->query("UPDATE messages SET status='read' WHERE message_id=$msg_id AND receiver_id=$member_id");
    echo "<script>alert('Message marked as read');window.location='messages.php';</script>";
    exit;
}

/* ===============================
   FETCH RECEIVED MESSAGES
   =============================== */
$received = $conn->query("
    SELECT m.*, 
           CASE 
               WHEN m.sender_type='admin' THEN a.name
               WHEN m.sender_type='member' THEN mb.full_name
           END AS sender_name
    FROM messages m
    LEFT JOIN admins a ON m.sender_id = a.admin_id
    LEFT JOIN members mb ON m.sender_id = mb.member_id
    WHERE m.receiver_id = $member_id
    ORDER BY m.date_sent DESC
");

/* ===============================
   FETCH SENT MESSAGES
   =============================== */
$sent = $conn->query("
    SELECT m.*, 
           CASE 
               WHEN m.receiver_type='admin' THEN a.name
               WHEN m.receiver_type='member' THEN mb.full_name
           END AS receiver_name
    FROM messages m
    LEFT JOIN admins a ON m.receiver_id = a.admin_id
    LEFT JOIN members mb ON m.receiver_id = mb.member_id
    WHERE m.sender_id = $member_id
    ORDER BY m.date_sent DESC
");

/* ===============================
   FETCH RECIPIENT LIST
   =============================== */
$admins = $conn->query("SELECT admin_id, name FROM admins");
$members = $conn->query("SELECT member_id, full_name FROM members WHERE member_id != $member_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages - ChurchHub Member</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background-color: #f8f9fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.card {
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    border: none;
}
.message-box {
    max-height: 250px;
    overflow-y: auto;
    background: #fffef5;
    border: 1px solid #ffe6a7;
    border-radius: 10px;
    padding: 10px;
}
.message-box::-webkit-scrollbar {
    width: 6px;
}
.message-box::-webkit-scrollbar-thumb {
    background-color: #ffc107;
    border-radius: 10px;
}
.table th, .table td {
    vertical-align: middle !important;
}
.btn {
    transition: 0.2s ease;
}
.btn:hover {
    transform: scale(1.05);
}
</style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary"><i class="bi bi-chat-dots"></i> Messages</h3>
        <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <!-- Compose New Message -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="fw-bold text-success mb-3"><i class="bi bi-pencil-square"></i> Compose New Message</h5>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Recipient Type</label>
                        <select name="receiver_type" id="receiverType" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="admin">Admin</option>
                            <option value="member">Member</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Select Recipient</label>
                        <select name="receiver_id" id="receiverList" class="form-select" required>
                            <option value="">Select a recipient...</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Message</label>
                    <textarea name="content" class="form-control" rows="5" placeholder="Write your message..." required></textarea>
                </div>

                <button type="submit" class="btn btn-success"><i class="bi bi-send-fill"></i> Send Message</button>
            </form>
        </div>
    </div>

    <!-- Received Messages -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-inbox-fill"></i> Received Messages</h5>

            <form method="POST">
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <button name="action" value="mark_read" class="btn btn-warning btn-sm me-2">
                            <i class="bi bi-check2-all"></i> Mark as Read
                        </button>
                        <button name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected messages?');">
                            <i class="bi bi-trash-fill"></i> Delete Selected
                        </button>
                    </div>
                </div>

                <div style="max-height: 450px; overflow-y: auto;">
                    <table class="table table-hover align-middle">
                        <thead class="table-warning">
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>#</th>
                                <th>From</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($received->num_rows > 0) {
                                $count = 1;
                                while ($msg = $received->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td><input type='checkbox' name='selected_msgs[]' value='{$msg['message_id']}'></td>";
                                    echo "<td>{$count}</td>";
                                    echo "<td>" . htmlspecialchars($msg['sender_name'] ?? 'Unknown') . "</td>";
                                    echo "<td><div class='message-box'>" . nl2br(htmlspecialchars($msg['content'])) . "</div></td>";
                                    echo "<td><span class='badge " . ($msg['status'] == 'read' ? 'bg-success' : 'bg-secondary') . "'>" . ucfirst($msg['status']) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($msg['date_sent']) . "</td>";
                                    echo "<td>
                                        <a href='reply_message.php?id={$msg['message_id']}' class='btn btn-sm btn-primary me-1'><i class='bi bi-reply-fill'></i></a>
                                        <a href='messages.php?mark_read={$msg['message_id']}' class='btn btn-sm btn-warning me-1'><i class='bi bi-check2-all'></i></a>
                                        <a href='messages.php?delete={$msg['message_id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Delete this message?');\"><i class='bi bi-trash-fill'></i></a>
                                    </td>";
                                    echo "</tr>";
                                    $count++;
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center text-muted'>No received messages found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <!-- Sent Messages -->
    <div class="card">
        <div class="card-body">
            <h5 class="fw-bold text-success mb-3"><i class="bi bi-send-fill"></i> Sent Messages</h5>
            <div style="max-height: 450px; overflow-y: auto;">
                <table class="table table-hover align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>To</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date Sent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($sent->num_rows > 0) {
                            $count = 1;
                            while ($msg = $sent->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>{$count}</td>";
                                echo "<td>" . htmlspecialchars($msg['receiver_name'] ?? 'Unknown') . "</td>";
                                echo "<td><div class='message-box'>" . nl2br(htmlspecialchars($msg['content'])) . "</div></td>";
                                echo "<td><span class='badge bg-success'>Sent</span></td>";
                                echo "<td>" . htmlspecialchars($msg['date_sent']) . "</td>";
                                echo "</tr>";
                                $count++;
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-muted'>No sent messages found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JS to switch recipient list + select all -->
<script>
document.getElementById("selectAll").addEventListener("click", function() {
    const checkboxes = document.querySelectorAll("input[name='selected_msgs[]']");
    checkboxes.forEach(chk => chk.checked = this.checked);
});

const receiverType = document.getElementById('receiverType');
const receiverList = document.getElementById('receiverList');
const adminList = <?php
$adminData = [];
while ($a = $admins->fetch_assoc()) {
    $adminData[] = ["id" => $a['admin_id'], "name" => $a['name']];
}
echo json_encode($adminData);
?>;
const memberList = <?php
$memberData = [];
while ($m = $members->fetch_assoc()) {
    $memberData[] = ["id" => $m['member_id'], "name" => $m['full_name']];
}
echo json_encode($memberData);
?>;

receiverType.addEventListener('change', () => {
    receiverList.innerHTML = '<option value="">Select recipient...</option>';
    const selectedType = receiverType.value;
    const list = selectedType === 'admin' ? adminList : memberList;

    list.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.name;
        receiverList.appendChild(opt);
    });
});
</script>

</body>
</html>

<?php
include_once 'includes/mod_header.php';
include_once 'includes/mod_sidebard.php';

$moderator_id = $_SESSION['moderator_id'];

/* ===============================
   HANDLE SEND MESSAGE
   =============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject'])) {
    $subject = trim($_POST['subject']);
    $body = trim($_POST['body']);
    $receiver_id = intval($_POST['receiver_id']);

    if ($receiver_id === 0) {
        // Send to all members
        $members = $conn->query("SELECT member_id FROM members WHERE status='active'");
        $stmt = $conn->prepare("
            INSERT INTO messages (sender_id, receiver_id, receiver_type, subject, body)
            VALUES (?, ?, 'member', ?, ?)
        ");
        while ($m = $members->fetch_assoc()) {
            $stmt->bind_param("iiss", $moderator_id, $m['member_id'], $subject, $body);
            $stmt->execute();
        }
        $log_desc = "Sent message to all members: $subject";
    } else {
        // Send to one member
        $stmt = $conn->prepare("
            INSERT INTO messages (sender_id, receiver_id, receiver_type, subject, body)
            VALUES (?, ?, 'member', ?, ?)
        ");
        $stmt->bind_param("iiss", $moderator_id, $receiver_id, $subject, $body);
        $stmt->execute();
        $log_desc = "Sent message to member_id $receiver_id: $subject";
    }

    // Log action
    $conn->query("
        INSERT INTO admin_logs (admin_id, action, table_name, description, ip_address)
        VALUES ($moderator_id, 'Send', 'messages', '$log_desc', '{$_SERVER['REMOTE_ADDR']}')
    ");

    echo "<script>alert('Message sent successfully');window.location='messages.php';</script>";
    exit;
}

/* ===============================
   HANDLE MARK AS READ
   =============================== */
if (isset($_GET['mark_read'])) {
    $msg_id = intval($_GET['mark_read']);
    $conn->query("UPDATE messages SET status='read' WHERE message_id=$msg_id");
    echo "<script>alert('Message marked as read');window.location='messages.php';</script>";
    exit;
}

/* ===============================
   HANDLE DELETE
   =============================== */
if (isset($_GET['delete'])) {
    $msg_id = intval($_GET['delete']);
    $conn->query("DELETE FROM messages WHERE message_id=$msg_id");
    echo "<script>alert('Message deleted successfully');window.location='messages.php';</script>";
    exit;
}

/* ===============================
   FETCH RECEIVED MESSAGES
   =============================== */
$messages = $conn->query("
    SELECT m.*, mb.full_name AS member_name
    FROM messages m
    LEFT JOIN members mb ON m.sender_id = mb.member_id
    WHERE m.receiver_id = $moderator_id
    ORDER BY m.sent_at DESC
");
?>

<div class="container-fluid px-4">
    <!-- Header with View Sent Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark border-bottom pb-2">📩 Messages</h3>
        <a href="sent_messages.php" class="btn btn-warning fw-semibold">
            <i class="bi bi-send-check-fill"></i> View Sent
        </a>
    </div>

    <!-- Compose Message -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-send"></i> Send Message</h5>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Recipient</label>
                    <select name="receiver_id" class="form-select border-secondary" required>
                        <option value="0">All Members</option>
                        <?php
                        $members = $conn->query("SELECT member_id, full_name FROM members WHERE status='active'");
                        while ($m = $members->fetch_assoc()) {
                            echo "<option value='{$m['member_id']}'>" . htmlspecialchars($m['full_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Subject</label>
                    <input type="text" name="subject" class="form-control border-secondary" placeholder="Enter subject..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Message</label>
                    <textarea name="body" class="form-control border-secondary" rows="6" placeholder="Write your message..." required></textarea>
                </div>

                <button type="submit" class="btn btn-success px-4"><i class="bi bi-envelope-fill"></i> Send</button>
            </form>
        </div>
    </div>

    <!-- Received Messages -->
    <div class="card shadow-sm border-0">
        <div class="card-body" style="max-height: 850px; overflow-y: auto;">
            <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-inbox"></i> Messages Received</h5>
            <table class="table table-hover align-middle">
                <thead class="table-warning">
                    <tr>
                        <th>#</th>
                        <th>From</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Sent At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($messages->num_rows > 0) {
                        $count = 1;
                        while ($msg = $messages->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$count}</td>";
                            echo "<td>" . htmlspecialchars($msg['member_name'] ?? 'Unknown') . "</td>";
                            echo "<td><strong>" . htmlspecialchars($msg['subject']) . "</strong></td>";
                            echo "<td>
                                    <div class='message-box'>" . nl2br(htmlspecialchars($msg['body'])) . "</div>
                                  </td>";
                            echo "<td><span class='badge " . ($msg['status'] == 'read' ? 'bg-success' : 'bg-secondary') . "'>" . ucfirst($msg['status']) . "</span></td>";
                            echo "<td>" . htmlspecialchars($msg['sent_at']) . "</td>";
                            echo "<td>
                                <a href='reply_message.php?id={$msg['message_id']}' class='btn btn-sm btn-primary me-1'><i class='bi bi-reply-fill'></i></a>
                                <a href='messages.php?mark_read={$msg['message_id']}' class='btn btn-sm btn-warning me-1'><i class='bi bi-check2-all'></i></a>
                                <a href='messages.php?delete={$msg['message_id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Delete this message?');\"><i class='bi bi-trash-fill'></i></a>
                            </td>";
                            echo "</tr>";
                            $count++;
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center text-muted'>No messages found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* ===== Message Box Styling ===== */
.message-box {
    max-height: 350px; /* Increased height for better readability */
    overflow-y: auto;
    background: #fffef5;
    border: 1px solid #ffe6a7;
    border-radius: 10px;
    padding: 12px;
    color: #333;
    font-size: 0.95rem;
    line-height: 1.5;
}
.message-box::-webkit-scrollbar {
    width: 6px;
}
.message-box::-webkit-scrollbar-thumb {
    background-color: #ffc107;
    border-radius: 10px;
}

/* ===== Table Styling ===== */
.table th, .table td {
    vertical-align: middle !important;
}

/* ===== Buttons & Hover Effects ===== */
.btn-warning, .btn-success, .btn-primary, .btn-danger {
    transition: all 0.2s ease-in-out;
}
.btn-warning:hover, .btn-success:hover, .btn-primary:hover, .btn-danger:hover {
    transform: scale(1.05);
}
</style>

<?php include_once 'includes/mod_footer.php'; ?>

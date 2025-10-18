<?php
include_once 'includes/mod_header.php';
include_once 'includes/mod_sidebard.php';

$moderator_id = $_SESSION['moderator_id'];

/* ===============================
   HANDLE DELETE SENT MESSAGE
   =============================== */
if (isset($_GET['delete'])) {
    $msg_id = intval($_GET['delete']);
    $conn->query("DELETE FROM messages WHERE message_id=$msg_id AND sender_id=$moderator_id");
    echo "<script>alert('Sent message deleted successfully');window.location='sent_messages.php';</script>";
    exit;
}

/* ===============================
   FETCH SENT MESSAGES
   =============================== */
$messages = $conn->query("
    SELECT m.*, mb.full_name AS receiver_name
    FROM messages m
    LEFT JOIN members mb ON m.receiver_id = mb.member_id
    WHERE m.sender_id = $moderator_id
    ORDER BY m.sent_at DESC
");
?>

<div class="container-fluid px-4">
    <!-- Header with Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark border-bottom pb-2">📤 Sent Messages</h3>
        <a href="messages.php" class="btn btn-secondary fw-semibold">
            <i class="bi bi-arrow-left-circle"></i> Back to Inbox
        </a>
    </div>

    <!-- Sent Messages Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body" style="max-height: 850px; overflow-y: auto;">
            <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-send-check-fill"></i> Messages You Sent</h5>
            <table class="table table-hover align-middle">
                <thead class="table-warning">
                    <tr>
                        <th>#</th>
                        <th>To</th>
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
                            $receiver = $msg['receiver_name'] ?: 'All Members';
                            $shortBody = htmlspecialchars(substr($msg['body'], 0, 80));
                            $fullBody = nl2br(htmlspecialchars($msg['body']));
                            echo "
                            <tr>
                                <td>{$count}</td>
                                <td>" . htmlspecialchars($receiver) . "</td>
                                <td><strong>" . htmlspecialchars($msg['subject']) . "</strong></td>
                                <td>
                                    <div class='message-preview' onclick='toggleMessage(this)'>
                                        <div class='short-text'>{$shortBody}...</div>
                                        <div class='full-text d-none'>{$fullBody}</div>
                                    </div>
                                </td>
                                <td><span class='badge " . ($msg['status'] == 'read' ? 'bg-success' : 'bg-secondary') . "'>" . ucfirst($msg['status']) . "</span></td>
                                <td>" . htmlspecialchars($msg['sent_at']) . "</td>
                                <td>
                                    <a href='sent_messages.php?delete={$msg['message_id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Delete this sent message?');\">
                                        <i class='bi bi-trash-fill'></i>
                                    </a>
                                </td>
                            </tr>";
                            $count++;
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center text-muted'>No sent messages found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.message-preview {
    background: #fffef5;
    border: 1px solid #ffe6a7;
    border-radius: 10px;
    padding: 10px;
    color: #333;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}
.message-preview:hover {
    background: #fff5cc;
}
.full-text {
    margin-top: 10px;
    white-space: pre-line;
}
.short-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.message-preview.expanded {
    background: #fffdf0;
    border-left: 4px solid #ffc107;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.table th, .table td {
    vertical-align: middle !important;
}
.btn-secondary, .btn-danger {
    transition: all 0.2s ease-in-out;
}
.btn-secondary:hover, .btn-danger:hover {
    transform: scale(1.05);
}
</style>

<script>
function toggleMessage(element) {
    const shortText = element.querySelector('.short-text');
    const fullText = element.querySelector('.full-text');
    const isExpanded = element.classList.toggle('expanded');

    if (isExpanded) {
        shortText.classList.add('d-none');
        fullText.classList.remove('d-none');
    } else {
        shortText.classList.remove('d-none');
        fullText.classList.add('d-none');
    }
}
</script>

<?php include_once 'includes/mod_footer.php'; ?>

<?php
include_once 'includes/header.php';
include_once 'includes/sidebar.php';

$member_id = $_SESSION['member_id'];

// --- Fetch upcoming events (next 5) ---
$events = $conn->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");

// --- Fetch latest announcements (5) ---
$announcements = $conn->query("SELECT * FROM announcements ORDER BY posted_at DESC LIMIT 5");

// --- Fetch latest sermons (5) ---
$sermons = $conn->query("SELECT * FROM sermons ORDER BY posted_at DESC LIMIT 5");

// --- Fetch latest messages (5) ---
$messages = $conn->query("
    SELECT m.*, a.full_name AS sender_name 
    FROM messages m
    LEFT JOIN admins a ON m.sender_id = a.admin_id
    WHERE m.receiver_id = $member_id
    ORDER BY m.sent_at DESC
    LIMIT 5
");
?>

<div class="container-fluid px-4">
    <h3 class="fw-bold mb-4 text-dark border-bottom pb-2"><i class="bi bi-house-fill"></i> Dashboard</h3>

    <div class="row g-4">
        <!-- Upcoming Events -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <h6 class="card-title text-primary fw-bold"><i class="bi bi-calendar-event"></i> Upcoming Events</h6>
                    <?php if($events->num_rows>0): ?>
                        <ul class="list-unstyled mb-0">
                            <?php while($ev=$events->fetch_assoc()): ?>
                                <li class="mb-2">
                                    <strong><?= htmlspecialchars($ev['title']); ?></strong><br>
                                    <small><?= date("M d, Y", strtotime($ev['event_date'])); ?> - <?= htmlspecialchars($ev['location']); ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">No upcoming events.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Latest Announcements -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning fw-bold"><i class="bi bi-megaphone-fill"></i> Announcements</h6>
                    <?php if($announcements->num_rows>0): ?>
                        <ul class="list-unstyled mb-0">
                            <?php while($ann=$announcements->fetch_assoc()): ?>
                                <li class="mb-2">
                                    <strong><?= htmlspecialchars($ann['title']); ?></strong><br>
                                    <small><?= date("M d, Y", strtotime($ann['posted_at'])); ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">No announcements.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Latest Sermons -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="card-title text-success fw-bold"><i class="bi bi-book-half"></i> Latest Sermons</h6>
                    <?php if($sermons->num_rows>0): ?>
                        <ul class="list-unstyled mb-0">
                            <?php while($ser=$sermons->fetch_assoc()): ?>
                                <li class="mb-2">
                                    <strong><?= htmlspecialchars($ser['title']); ?></strong><br>
                                    <a href="<?= htmlspecialchars($ser['video_link']); ?>" target="_blank" class="text-decoration-none">
                                        Watch
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">No sermons yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100 border-start border-4 border-danger">
                <div class="card-body">
                    <h6 class="card-title text-danger fw-bold"><i class="bi bi-envelope-fill"></i> Messages</h6>
                    <?php if($messages->num_rows>0): ?>
                        <ul class="list-unstyled mb-0">
                            <?php while($msg=$messages->fetch_assoc()): ?>
                                <li class="mb-2">
                                    <strong><?= htmlspecialchars($msg['sender_name'] ?? 'Admin'); ?></strong><br>
                                    <small><?= htmlspecialchars(substr($msg['subject'],0,30)); ?>...</small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">No messages yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>

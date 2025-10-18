<?php
include_once 'includes/header.php';
include_once 'includes/sidebar.php';

$member_id = $_SESSION['member_id'];

// Fetch upcoming events
$events = $conn->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC");

// Handle joining event
if (isset($_GET['join'])) {
    $event_id = intval($_GET['join']);
    // Check if already joined
    $check = $conn->query("SELECT * FROM event_participants WHERE event_id=$event_id AND member_id=$member_id");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO event_participants (event_id, member_id) VALUES ($event_id, $member_id)");
        echo "<script>alert('You have joined the event!');window.location='my_events.php';</script>";
    } else {
        echo "<script>alert('You already joined this event.');window.location='my_events.php';</script>";
    }
}
?>

<h3 class="fw-bold mb-4">Upcoming Events</h3>

<div class="row">
<?php if($events->num_rows>0): ?>
    <?php while($event=$events->fetch_assoc()): 
        $joined = $conn->query("SELECT * FROM event_participants WHERE event_id={$event['event_id']} AND member_id=$member_id")->num_rows;
    ?>
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100" style="border-left: 5px solid #4e73df;">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($event['title']); ?></h5>
                <p class="card-text"><?= substr(htmlspecialchars($event['description']),0,80); ?>...</p>
                <p class="mb-1"><i class="bi bi-calendar-event"></i> <?= date("M d, Y", strtotime($event['event_date'])); ?></p>
                <p class="mb-3"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['location']); ?></p>
                <?php if($joined): ?>
                    <span class="badge bg-success">Joined</span>
                <?php else: ?>
                    <a href="?join=<?= $event['event_id']; ?>" class="btn btn-primary btn-sm">Join Event</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="text-muted">No upcoming events.</p>
<?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>

<?php include_once 'includes/header.php'; ?>

<h3 class="fw-bold mb-4 text-dark border-bottom pb-2">Upcoming Events</h3>
<div class="row">
<?php
$result = $conn->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC");
if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($row['title']); ?></h5>
                <p class="text-muted small mb-2"><?= date("F j, Y", strtotime($row['event_date'])); ?></p>
                <p class="card-text"><?= substr($row['description'], 0, 120); ?>...</p>
                <a href="#" class="btn btn-warning text-dark">Learn More</a>
            </div>
        </div>
    </div>
<?php endwhile; else: ?>
    <p>No upcoming events found.</p>
<?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>

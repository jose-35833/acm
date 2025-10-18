<?php
include_once 'includes/header.php';
include_once 'includes/sidebar.php';

// Fetch all announcements (latest first)
$announcements = $conn->query("SELECT * FROM announcements ORDER BY posted_at DESC");
?>

<h3 class="fw-bold mb-4">Church Announcements</h3>

<div class="row">
<?php if($announcements->num_rows>0): ?>
    <?php while($ann=$announcements->fetch_assoc()): ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100" style="border-left: 5px solid #f6c23e;">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($ann['title']); ?></h5>
                    <p class="card-text"><?= substr(htmlspecialchars($ann['content']),0,100); ?>...</p>
                    <p class="mb-0"><small class="text-muted">Posted on <?= date("M d, Y", strtotime($ann['posted_at'])); ?></small></p>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="text-muted">No announcements found.</p>
<?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>

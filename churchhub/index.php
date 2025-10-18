<?php include_once 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-8">
        <div class="p-4 bg-white shadow-sm rounded mb-4">
            <h2 class="fw-bold mb-3">Welcome to Church Hub</h2>
            <p>
                We are a Christ-centered community committed to spreading love, hope, and faith.
                Join us in worship, prayer, and service as we grow together spiritually and impact lives.
            </p>
            <a href="about.php" class="btn btn-warning text-dark fw-semibold mt-2">Learn More</a>
        </div>

        <div class="p-4 bg-white shadow-sm rounded mb-4">
            <h4 class="fw-bold">Upcoming Events</h4>
            <p>Stay tuned for our latest church gatherings and community events.</p>
            <a href="events.php" class="btn btn-outline-primary btn-sm">View Events</a>
        </div>

        <div class="p-4 bg-white shadow-sm rounded">
            <h4 class="fw-bold">Latest Sermons</h4>
            <p>Watch or read our most recent sermons and teachings from our ministers.</p>
            <a href="sermons.php" class="btn btn-outline-success btn-sm">View Sermons</a>
        </div>
    </div>

    <div class="col-md-4">
        <?php include_once 'includes/sidebar.php'; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>

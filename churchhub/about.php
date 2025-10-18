<?php include_once 'includes/header.php'; ?>

<?php
$about = $conn->query("SELECT content FROM site_info WHERE section='about' LIMIT 1")->fetch_assoc();
?>

<div class="bg-white p-4 shadow-sm rounded">
    <h2 class="fw-bold mb-3">About Us</h2>
    <p><?= nl2br($about['content'] ?? 'Welcome to Church Hub, a place where faith meets community.'); ?></p>
</div>

<?php include_once 'includes/footer.php'; ?>

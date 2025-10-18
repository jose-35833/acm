<?php include_once 'includes/header.php'; ?>

<?php
$contact = $conn->query("SELECT * FROM site_info WHERE section='contact' LIMIT 1")->fetch_assoc();
?>

<div class="card p-4 shadow-sm text-center">
    <h4 class="fw-bold mb-3">Get in Touch</h4>
    <p><i class="bi bi-telephone-fill text-warning"></i> <?= $contact['phone'] ?? '+254 700 000 000'; ?></p>
    <p><i class="bi bi-envelope-fill text-warning"></i> <?= $contact['email'] ?? 'info@churchhub.org'; ?></p>
    <p><i class="bi bi-geo-alt-fill text-warning"></i> <?= $contact['address'] ?? 'Nairobi, Kenya'; ?></p>

    <div class="d-flex justify-content-center mt-3">
        <a href="<?= $contact['facebook'] ?? '#'; ?>" class="me-3 text-dark"><i class="bi bi-facebook fs-4"></i></a>
        <a href="<?= $contact['instagram'] ?? '#'; ?>" class="me-3 text-dark"><i class="bi bi-instagram fs-4"></i></a>
        <a href="<?= $contact['whatsapp'] ?? '#'; ?>" class="text-dark"><i class="bi bi-whatsapp fs-4"></i></a>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>

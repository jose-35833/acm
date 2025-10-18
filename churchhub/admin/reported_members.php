<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$query = "
SELECT r.id AS report_id, r.status, r.report_date, 
       m.member_id, m.full_name AS member_name, m.email,
       modr.full_name AS moderator_name
FROM reports r
JOIN members m ON r.member_id = m.member_id
JOIN moderators modr ON r.moderator_id = modr.moderator_id
ORDER BY r.report_date DESC";

$reports = $conn->query($query);

include "includes/header.php";
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-dark"><i class="bi bi-flag-fill text-danger"></i> Reported Members</h3>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if ($reports->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Member</th>
                                <th>Email</th>
                                <th>Reported By</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($r = $reports->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($r['member_name']); ?></td>
                                <td><?= htmlspecialchars($r['email']); ?></td>
                                <td><?= htmlspecialchars($r['moderator_name']); ?></td>
                                <td><?= date("M d, Y", strtotime($r['report_date'])); ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $r['status'] == 'pending' ? 'bg-warning' : 
                                            ($r['status'] == 'warned' ? 'bg-info' : 
                                            ($r['status'] == 'suspended' ? 'bg-danger' : 'bg-success')); ?>">
                                        <?= ucfirst($r['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="take_action.php?id=<?= $r['report_id']; ?>&action=warn" class="btn btn-sm btn-info">Warn</a>
                                    <a href="take_action.php?id=<?= $r['report_id']; ?>&action=suspend" class="btn btn-sm btn-danger">Suspend</a>
                                    <a href="take_action.php?id=<?= $r['report_id']; ?>&action=clear" class="btn btn-sm btn-success">Clear</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No reports available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

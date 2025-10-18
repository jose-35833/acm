<?php
require_once '../includes/db.php';
$id = intval($_GET['id']);

$member = $conn->query("SELECT * FROM members WHERE member_id=$id")->fetch_assoc();
$logs = $conn->query("SELECT * FROM admin_logs WHERE description LIKE '%$id%' ORDER BY created_at DESC LIMIT 5");

if (!$member) {
    echo "<p class='text-danger text-center'>Profile not found.</p>";
    exit;
}
?>

<div class="row">
  <div class="col-md-5 text-center">
    <div class="bg-light rounded-4 p-3 shadow-sm">
      <?php $img_path = "../uploads/profiles/member_{$member['member_id']}.png"; if (file_exists($img_path)): ?>
        <img src="<?php echo $img_path; ?>" alt="Profile" style="width:90px; height:90px; border-radius:50%;">
      <?php else: ?>
        <i class="bi bi-person-circle text-primary" style="font-size: 90px;"></i>
      <?php endif; ?>
      <h5 class="fw-bold mt-2"><?= htmlspecialchars($member['full_name']) ?></h5>
      <p class="text-muted mb-1"><?= htmlspecialchars($member['email']) ?></p>
      <span class="badge bg-<?= $member['role'] === 'admin' ? 'danger' : 'primary' ?>"><?= ucfirst($member['role']) ?></span>
    </div>
  </div>

  <div class="col-md-7">
    <h6 class="fw-bold text-dark">Member Details</h6>
    <ul class="list-group mb-3">
      <li class="list-group-item"><strong>Phone:</strong> <?= htmlspecialchars($member['phone']) ?></li>
      <li class="list-group-item"><strong>Status:</strong> <?= htmlspecialchars($member['status']) ?></li>
      <li class="list-group-item"><strong>Joined:</strong> <?= htmlspecialchars($member['created_at']) ?></li>
    </ul>

    <h6 class="fw-bold text-dark">Recent Activity</h6>
    <ul class="list-group">
      <?php if ($logs->num_rows > 0) {
        while ($log = $logs->fetch_assoc()) {
          echo "<li class='list-group-item small'><i class='bi bi-clock-history text-warning'></i> " . htmlspecialchars($log['description']) . " <span class='text-muted float-end'>" . htmlspecialchars($log['created_at']) . "</span></li>";
        }
      } else {
        echo "<li class='list-group-item text-muted'>No recent actions logged.</li>";
      } ?>
    </ul>
  </div>
</div>

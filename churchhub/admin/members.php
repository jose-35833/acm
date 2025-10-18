<?php
include_once 'includes/header.php';
include_once 'includes/sidebar.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

/* ===============================
   HANDLE PROMOTE/DEMOTE ACTIONS
   =============================== */
if (isset($_POST['action_type']) && isset($_POST['member_id'])) {
    $member_id = intval($_POST['member_id']);
    $action = $_POST['action_type'];

    if ($action === 'promote') {
        $conn->query("UPDATE members SET role='admin' WHERE member_id=$member_id");
        $conn->query("
            INSERT INTO admin_logs (admin_id, action, table_name, description, ip_address)
            VALUES ($admin_id, 'Promote', 'members', 'Promoted member ID $member_id to admin', '{$_SERVER['REMOTE_ADDR']}')
        ");
        $message = "✅ Member promoted to admin successfully!";
    } elseif ($action === 'demote') {
        $conn->query("UPDATE members SET role='member' WHERE member_id=$member_id");
        $conn->query("
            INSERT INTO admin_logs (admin_id, action, table_name, description, ip_address)
            VALUES ($admin_id, 'Demote', 'members', 'Demoted admin ID $member_id back to member', '{$_SERVER['REMOTE_ADDR']}')
        ");
        $message = "⚠️ Admin demoted back to member successfully!";
    }

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0 show';
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.innerHTML = `<div class='d-flex'><div class='toast-body'>$message</div><button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast'></button></div>`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        });
    </script>";
}

/* ===============================
   FETCH MEMBERS & ADMINS
   =============================== */
$members = $conn->query("SELECT * FROM members WHERE role='member' ORDER BY full_name ASC");
$admins = $conn->query("SELECT * FROM members WHERE role='admin' ORDER BY full_name ASC");
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark border-bottom pb-2">👥 Manage Members & Admins</h3>
        <a href="add_member.php" class="btn btn-success fw-semibold">
            <i class="bi bi-person-plus-fill"></i> Add New Member
        </a>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="memberTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab">Members</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins" type="button" role="tab">Admins</button>
        </li>
    </ul>

    <div class="tab-content mt-4" id="memberTabsContent">
        <!-- Members TAB -->
        <div class="tab-pane fade show active" id="members" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-people-fill"></i> Members List</h5>
                    <div style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover align-middle text-center">
                            <thead class="table-warning">
                                <tr>
                                    <th>#</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($members->num_rows > 0) {
                                    $count = 1;
                                    while ($m = $members->fetch_assoc()) {
                                        $statusBadge = $m['status'] === 'active' ? 'bg-success' : 'bg-secondary';
                                ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= htmlspecialchars($m['full_name']) ?></td>
                                    <td><?= htmlspecialchars($m['email']) ?></td>
                                    <td><?= htmlspecialchars($m['phone']) ?></td>
                                    <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($m['status']) ?></span></td>
                                    <td><span class="badge bg-primary"><?= ucfirst($m['role']) ?></span></td>
                                    <td><?= htmlspecialchars($m['created_at']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info me-1 viewProfile" data-id="<?= $m['member_id'] ?>">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal" 
                                            data-member-id="<?= $m['member_id'] ?>" data-action="promote" 
                                            data-name="<?= htmlspecialchars($m['full_name']) ?>">
                                            <i class="bi bi-arrow-up-circle"></i> Promote
                                        </button>
                                    </td>
                                </tr>
                                <?php } } else { ?>
                                <tr><td colspan="8" class="text-muted">No members found.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admins TAB -->
        <div class="tab-pane fade" id="admins" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold text-danger mb-3"><i class="bi bi-person-badge-fill"></i> Admins List</h5>
                    <div style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover align-middle text-center">
                            <thead class="table-warning">
                                <tr>
                                    <th>#</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($admins->num_rows > 0) {
                                    $count = 1;
                                    while ($a = $admins->fetch_assoc()) {
                                        $statusBadge = $a['status'] === 'active' ? 'bg-success' : 'bg-secondary';
                                ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= htmlspecialchars($a['full_name']) ?></td>
                                    <td><?= htmlspecialchars($a['email']) ?></td>
                                    <td><?= htmlspecialchars($a['phone']) ?></td>
                                    <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($a['status']) ?></span></td>
                                    <td><span class="badge bg-danger"><?= ucfirst($a['role']) ?></span></td>
                                    <td><?= htmlspecialchars($a['created_at']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info me-1 viewProfile" data-id="<?= $a['member_id'] ?>">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#confirmModal" 
                                            data-member-id="<?= $a['member_id'] ?>" data-action="demote" 
                                            data-name="<?= htmlspecialchars($a['full_name']) ?>">
                                            <i class="bi bi-arrow-down-circle"></i> Demote
                                        </button>
                                    </td>
                                </tr>
                                <?php } } else { ?>
                                <tr><td colspan="8" class="text-muted">No admins found.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body text-center">
                    <input type="hidden" name="member_id" id="memberId">
                    <input type="hidden" name="action_type" id="actionType">
                    <p class="fs-5">Are you sure you want to <span id="actionText"></span> <strong id="memberName"></strong>?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Yes, Proceed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold"><i class="bi bi-person-circle"></i> Member Profile</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="profileDetails">
        <p class="text-center text-muted">Loading profile...</p>
      </div>
    </div>
  </div>
</div>

<script>
const confirmModal = document.getElementById('confirmModal');
confirmModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    document.getElementById('memberId').value = button.getAttribute('data-member-id');
    document.getElementById('actionType').value = button.getAttribute('data-action');
    document.getElementById('memberName').textContent = button.getAttribute('data-name');
    document.getElementById('actionText').textContent = button.getAttribute('data-action');
});

// AJAX Profile Modal
document.querySelectorAll('.viewProfile').forEach(btn => {
  btn.addEventListener('click', () => {
    const memberId = btn.dataset.id;
    fetch(`view_profile.php?id=${memberId}`)
      .then(res => res.text())
      .then(html => {
        document.getElementById('profileDetails').innerHTML = html;
        new bootstrap.Modal(document.getElementById('profileModal')).show();
      });
  });
});
</script>

<style>
.table th, .table td { vertical-align: middle !important; }
.btn { transition: 0.2s ease-in-out; }
.btn:hover { transform: scale(1.05); }
.modal-content { border-radius: 15px; }
.nav-tabs .nav-link.active {
    background-color: #ffc107 !important;
    color: #212529 !important;
    border-color: #ffc107 !important;
}
</style>

<?php include_once 'includes/footer.php'; ?>

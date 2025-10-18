<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center text-warning fw-bold mt-3">ChurchHub Moderator</h4>
    <hr class="text-secondary">

    <a href="moderator_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'moderator_dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="members.php" class=""><i class="bi bi-people"></i> Members</a>
    <a href="events.php" class=""><i class="bi bi-calendar-event"></i> Events</a>
    <a href="sermons.php" class=""><i class="bi bi-book"></i> Sermons</a>
    <a href="messages.php" class=""><i class="bi bi-envelope"></i> Messages</a>
    <a href="announcements.php" class=""><i class="bi bi-megaphone"></i> Announcements</a>
    <a href="profile.php" class=""><i class="bi bi-person"></i> Profile</a>
    <hr class="text-secondary">
    <a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div id="sidebarBackdrop" class="sidebar-backdrop d-lg-none"></div>

<style>
.sidebar { width: 250px; min-height: 100vh; background-color: #212529; color: #fff; position: fixed; top: 0; left: 0; padding-top: 70px; overflow-y: auto; transition: transform 0.3s ease; z-index: 1040; }
.sidebar a { display: block; color: #adb5bd; padding: 12px 20px; text-decoration: none; font-size: 0.95rem; }
.sidebar a.active, .sidebar a:hover { background-color: #ffc107; color: #000; }
.sidebar hr { margin: 0.5rem 0; border-color: #6c757d; }
.sidebar .menu-section { padding: 10px 20px 5px; font-size: 0.8rem; font-weight: 600; color: #6c757d; text-transform: uppercase; }
.sidebar.show { transform: translateX(0); }
.sidebar:not(.show) { transform: translateX(-100%); }
.sidebar-backdrop { display: none; position: fixed; top: 0; left: 0; height: 100vh; width: 100vw; background-color: rgba(0,0,0,0.5); z-index: 1030; }
@media(max-width: 991.98px){ .sidebar::-webkit-scrollbar { width: 6px; } .sidebar::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.3); border-radius: 3px; } }
</style>

<script>
(function(){
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.querySelector('.sidebar');
    var backdrop = document.getElementById('sidebarBackdrop');

    function openSidebar(){ sidebar.classList.add('show'); if(backdrop) backdrop.style.display='block'; document.body.style.overflow='hidden'; }
    function closeSidebar(){ sidebar.classList.remove('show'); if(backdrop) backdrop.style.display='none'; document.body.style.overflow=''; }

    toggle && toggle.addEventListener('click', function(){ sidebar.classList.contains('show') ? closeSidebar() : openSidebar(); });
    backdrop && backdrop.addEventListener('click', closeSidebar);
    window.addEventListener('resize', function(){ if(window.innerWidth > 991.98) closeSidebar(); });
})();
</script>

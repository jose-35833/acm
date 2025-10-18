<div class="sidebar">
    <h4 class="text-center text-warning fw-bold mt-3">ChurchHub Admin</h4>
    <hr class="text-secondary">
    <a href="index.php">Dashboard</a>
    <a href="manage_members.php">Members</a>
    <a href="manage_moderators.php">Moderators</a>
    <a href="manage_events.php">Events</a>
    <a href="manage_sermons.php">Sermons</a>
    <a href="messages.php">Messages</a>
    <a href="announcements.php">Announcements</a>
    <a href="settings.php">Settings</a>
    <a href="profile.php">Profile</a>
    <a href="view_logs.php">View Logs</a>
</div>
<div id="sidebarBackdrop" class="sidebar-backdrop d-lg-none"></div>

<style>
.sidebar { width:250px; background:#212529; color:#fff; position:fixed; top:0; left:0; height:100vh; padding-top:60px; overflow-y:auto; z-index:1051; transition:transform 0.3s ease; }
.sidebar a { display:block; color:#adb5bd; padding:12px 20px; text-decoration:none; }
.sidebar a:hover, .sidebar a.active { background:#ffc107; color:#000; }
.sidebar-backdrop { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1050; }
@media(max-width:991px){ .sidebar { transform:translateX(-100%); } }
@media(max-width:991px){ .sidebar.show { transform:translateX(0); } }
</style>

<script>
(function(){
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');

    function openSidebar(){ sidebar.classList.add('show'); backdrop.style.display='block'; document.body.style.overflow='hidden'; }
    function closeSidebar(){ sidebar.classList.remove('show'); backdrop.style.display='none'; document.body.style.overflow=''; }

    toggle && toggle.addEventListener('click', ()=>{ sidebar.classList.contains('show')?closeSidebar():openSidebar(); });
    backdrop && backdrop.addEventListener('click', closeSidebar);
    window.addEventListener('resize', ()=>{ if(window.innerWidth>=992){ closeSidebar(); } });

    // Highlight active link
    const current = window.location.pathname.split('/').pop();
    document.querySelectorAll('.sidebar a').forEach(a => {
        if (a.getAttribute('href') === current) {
            a.classList.add('active');
        }
    });
})();
</script>

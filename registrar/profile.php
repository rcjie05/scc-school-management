<?php
require_once '../php/config.php';
requireRole('registrar');

$conn = getDBConnection();

// Ensure avatar_url column exists
$_col = $conn->query("SHOW COLUMNS FROM `users` LIKE 'avatar_url'");
if ($_col && $_col->num_rows === 0) {
    $conn->query("ALTER TABLE `users` ADD COLUMN `avatar_url` VARCHAR(500) NULL DEFAULT NULL AFTER `status`");
}
$user_id = $_SESSION['user_id'];
$stmt    = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

$avatarUrl = !empty($user['avatar_url']) ? htmlspecialchars(getAvatarUrl($user['avatar_url'])) : null;
$initials  = strtoupper(substr($user['name'] ?? 'R', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="apple-touch-icon" href="../images/logo2.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Saint Cecilia College Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/themes.css">
    <style>
        <?php include '../php/avatar_styles.php'; ?>
        .setting-section { padding: 1.5rem; background: var(--background-main); border-radius: var(--radius-md); margin-bottom: 1rem; }
        .setting-label { font-weight: 600; margin-bottom: 0.5rem; display: block; }
        .setting-input { width: 100%; padding: 0.75rem; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); margin-bottom: 1rem; font-family: var(--font-main); font-size: 0.9rem; box-sizing: border-box; }
        .setting-input:focus { outline: none; border-color: var(--primary-purple); box-shadow: 0 0 0 3px rgba(61,107,159,0.1); }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <img src="../images/logo2.jpg" alt="SCC Logo" style="width:100%;height:100%;object-fit:cover;border-radius:var(--radius-md);">
            </div>
            <div class="logo-text">
                Saint Cecilia College
                <span>Saint Cecilia College</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="dashboard.php" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
                <a href="applications.php" class="nav-item"><span class="nav-icon">📋</span><span>Applications</span></a>
                <a href="manage_loads.php" class="nav-item"><span class="nav-icon">📚</span><span>Study Loads</span></a>
                <a href="grades.php" class="nav-item"><span class="nav-icon">🎓</span><span>Grades</span></a>
                <a href="add_drop_requests.php" class="nav-item"><span class="nav-icon">🔄</span><span>Add/Drop Requests</span></a>
                <a href="floorplan.php" class="nav-item"><span class="nav-icon">🗺️</span><span>Floor Plan</span></a>
                <a href="reports.php" class="nav-item"><span class="nav-icon">📈</span><span>Reports</span></a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">System</div>
                <a href="announcements.php" class="nav-item"><span class="nav-icon">📢</span><span>Announcements</span></a>
                <a href="feedback.php" class="nav-item"><span class="nav-icon">💬</span><span>Feedback</span></a>
                <a href="settings.php" class="nav-item"><span class="nav-icon">⚙️</span><span>Settings</span></a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">HR</div>
                <a href="leave_requests.php" class="nav-item"><span class="nav-icon">🏖️</span><span>Leave Requests</span></a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Account</div>
                <a href="profile.php" class="nav-item active"><span class="nav-icon">👤</span><span>My Profile</span></a>
                <a href="../php/logout.php" class="nav-item"><span class="nav-icon">🚪</span><span>Logout</span></a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <header class="page-header">
            <div class="header-title">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><span></span><span></span><span></span></button>
                <h1>My Profile</h1>
                <p class="page-subtitle">Manage your profile picture and account settings</p>
            </div>
        </header>

        <!-- Profile Picture -->
        <div class="content-card" style="margin-bottom:2rem;">
            <div class="card-header"><h2 class="card-title">Profile Picture</h2></div>
            <div class="avatar-upload-section">
                <div class="avatar-preview-wrap">
                    <div class="avatar-preview" id="avatarPreview">
                        <?php if ($avatarUrl): ?>
                            <img src="<?= $avatarUrl ?>?t=<?= time() ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <span class="avatar-initials"><?= $initials ?></span>
                        <?php endif; ?>
                    </div>
                    <label class="avatar-edit-btn" for="avatarFileInput" title="Change photo">✏️</label>
                </div>
                <div class="avatar-upload-info">
                    <p class="avatar-name"><?= htmlspecialchars($user['name']) ?></p>
                    <p class="avatar-role">Registrar</p>
                    <p class="avatar-hint">JPG, PNG, GIF or WEBP · Max 5MB</p>
                    <div class="avatar-actions">
                        <label for="avatarFileInput" class="btn btn-primary" style="cursor:pointer;">📷 Upload Photo</label>
                        <button class="btn btn-secondary" onclick="removeAvatar()" id="removeBtn" <?= $avatarUrl ? '' : 'style="display:none;"' ?>>🗑️ Remove</button>
                    </div>
                    <input type="file" id="avatarFileInput" accept="image/*" style="display:none;" onchange="uploadAvatar(this)">
                    <p class="avatar-status" id="avatarStatus"></p>
                </div>
            </div>
        </div>

        <!-- Profile Info -->
        <div class="content-card" style="margin-bottom:2rem;">
            <div class="card-header"><h2 class="card-title">Profile Information</h2></div>
            <form id="profileForm" onsubmit="saveProfile(event)">
                <div class="setting-section">
                    <label class="setting-label">Full Name</label>
                    <input type="text" class="setting-input" id="fullName" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                    <label class="setting-label">Email</label>
                    <input type="email" class="setting-input" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="content-card" style="margin-bottom:2rem;">
            <div class="card-header"><h2 class="card-title">Change Password</h2></div>
            <form id="passwordForm" onsubmit="changePassword(event)">
                <div class="setting-section">
                    <label class="setting-label">Current Password</label>
                    <input type="password" class="setting-input" id="currentPassword" required>
                    <label class="setting-label">New Password</label>
                    <input type="password" class="setting-input" id="newPassword" required minlength="6">
                    <label class="setting-label">Confirm New Password</label>
                    <input type="password" class="setting-input" id="confirmPassword" required minlength="6">
                    <button type="submit" class="btn btn-primary">🔒 Update Password</button>
                </div>
            </form>
        </div>

        <!-- Appearance / Theme -->
        <div class="content-card" style="margin-bottom:2rem;" data-theme-picker-card>
            <div class="card-header"><h2 class="card-title">🎨 Appearance</h2></div>
            <div class="setting-section">
                <p style="font-size:0.9rem;color:var(--text-secondary);margin-bottom:1.5rem;">Choose a color theme for your account. Your selection is saved to your profile.</p>
                <div class="inline-theme-grid" id="inlineThemePicker"></div>
            </div>
        </div>
    </main>
</div>

<script>
async function uploadAvatar(input) {
    const file = input.files[0];
    if (!file) return;
    const status = document.getElementById('avatarStatus');
    status.textContent = '⏳ Uploading...';
    status.style.color = 'var(--text-secondary)';
    const formData = new FormData();
    formData.append('avatar', file);
    try {
        const res  = await fetch('../php/api/upload_avatar.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            document.getElementById('avatarPreview').innerHTML = `<img src="${data.avatar_url}?t=${Date.now()}" style="width:100%;height:100%;object-fit:cover;" alt="">`;
            document.getElementById('removeBtn').style.display = '';
            status.textContent = '✅ Photo updated!';
            status.style.color = '#2E7A62';
        } else {
            status.textContent = '❌ ' + data.message;
            status.style.color = '#9A3A3A';
        }
    } catch(e) {
        status.textContent = '❌ Upload failed';
        status.style.color = '#9A3A3A';
    }
    input.value = '';
}

async function removeAvatar() {
    if (!confirm('Remove your profile picture?')) return;
    const res  = await fetch('../php/api/remove_avatar.php', { method: 'POST' });
    const data = await res.json();
    if (data.success) {
        document.getElementById('avatarPreview').innerHTML = `<span class="avatar-initials"><?= $initials ?></span>`;
        document.getElementById('removeBtn').style.display = 'none';
        document.getElementById('avatarStatus').textContent = '✅ Photo removed';
        document.getElementById('avatarStatus').style.color = '#2E7A62';
    }
}

async function saveProfile(e) {
    e.preventDefault();
    const data = {
        name:  document.getElementById('fullName').value,
        email: document.getElementById('email').value
    };
    const res    = await fetch('../php/api/registrar/update_profile.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(data) });
    const result = await res.json();
    alert(result.success ? '✅ Profile updated!' : '❌ ' + result.message);
}

async function changePassword(e) {
    e.preventDefault();
    if (document.getElementById('newPassword').value !== document.getElementById('confirmPassword').value) {
        alert('Passwords do not match!'); return;
    }
    const data = {
        current_password: document.getElementById('currentPassword').value,
        new_password:     document.getElementById('newPassword').value
    };
    const res    = await fetch('../php/api/registrar/change_password.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(data) });
    const result = await res.json();
    if (result.success) { alert('✅ Password changed!'); document.getElementById('passwordForm').reset(); }
    else alert('❌ ' + (result.message || 'Failed'));
}
</script>
<script>
(function() {
    var sidebar = document.querySelector('.sidebar');
    var saved = sessionStorage.getItem('sidebarScroll');
    if (saved) sidebar.scrollTop = parseInt(saved);
    document.querySelectorAll('.nav-item').forEach(function(link) {
        link.addEventListener('click', function() { sessionStorage.setItem('sidebarScroll', sidebar.scrollTop); });
    });
})();
</script>
<script src="../js/theme-switcher.js"></script>
<script>
(function() {
    var toggle   = document.getElementById('sidebarToggle');
    var sidebar  = document.querySelector('.sidebar');
    var overlay  = document.getElementById('sidebarOverlay');
    if (!toggle || !sidebar) return;

    function openSidebar() {
        sidebar.classList.add('active');
        overlay && overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay && overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function() {
        sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
    });
    overlay && overlay.addEventListener('click', closeSidebar);

    document.querySelectorAll('.nav-item').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) closeSidebar();
        });
    });
})();
</script>
<nav class="mobile-bottom-nav">
  <a href="dashboard.php" class="mobile-nav-item"><span class="mobile-nav-icon">🏠</span>Home</a>
  <a href="applications.php" class="mobile-nav-item"><span class="mobile-nav-icon">📋</span>Apps</a>
  <a href="manage_loads.php" class="mobile-nav-item"><span class="mobile-nav-icon">📚</span>Loads</a>
  <a href="grades.php" class="mobile-nav-item"><span class="mobile-nav-icon">🎓</span>Grades</a>
  <a href="reports.php" class="mobile-nav-item"><span class="mobile-nav-icon">📈</span>Reports</a>
</nav>
    <script src="../js/session-monitor.js"></script>
</body>
</html>

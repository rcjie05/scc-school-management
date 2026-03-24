<?php
require_once '../php/config.php';
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="apple-touch-icon" href="../images/logo2.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1E3352">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SCC Portal">
    <link rel="apple-touch-icon" href="../images/logo2.jpg">
    <title>System Settings - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/themes.css">
    <style>
        .setting-item { padding: 1.5rem; background: var(--background-main); border-radius: var(--radius-md); margin-bottom: 1rem; }
        .setting-label { font-weight: 600; margin-bottom: 0.5rem; }
        .setting-description { font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.75rem; }
        .setting-input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: var(--radius-md); }
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
                    <a href="users.php" class="nav-item"><span class="nav-icon">👥</span><span>User Management</span></a>
                    <a href="floorplan.php" class="nav-item"><span class="nav-icon">🗺️</span><span>Floor Plan</span></a>
                    <a href="buildings.php" class="nav-item"><span class="nav-icon">🏢</span><span>Buildings & Rooms</span></a>
                    <a href="departments.php" class="nav-item"><span class="nav-icon">🏛️</span><span>Departments</span></a>
                    <a href="courses.php" class="nav-item"><span class="nav-icon">🎓</span><span>Courses</span></a>
                    <a href="faculty.php" class="nav-item"><span class="nav-icon">👨‍🏫</span><span>Faculty Directory</span></a>
                    <a href="grades.php" class="nav-item"><span class="nav-icon">📝</span><span>Grades</span></a>
                    <a href="subjects.php" class="nav-item"><span class="nav-icon">📚</span><span>Subjects</span></a>
                    <a href="sections.php" class="nav-item"><span class="nav-icon">📁</span><span>Sections</span></a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">System</div>
                    <a href="announcements.php" class="nav-item"><span class="nav-icon">📢</span><span>Announcements</span></a>
                    <a href="audit_logs.php" class="nav-item"><span class="nav-icon">📋</span><span>Audit Logs</span></a>
                    <a href="recycle_bin.php" class="nav-item"><span class="nav-icon">🗑️</span><span>Recycle Bin</span></a>
                    <a href="feedback.php" class="nav-item"><span class="nav-icon">💬</span><span>Feedback</span></a>
                    <a href="account_settings.php" class="nav-item"><span class="nav-icon">👤</span><span>Profile Settings</span></a>
                    <a href="settings.php" class="nav-item active"><span class="nav-icon">⚙️</span><span>System Settings</span></a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <a href="../php/logout.php" class="nav-item"><span class="nav-icon">🚪</span><span>Logout</span></a>
                </div>
            </nav>
        </aside>
        
        <main class="main-content">
            <header class="page-header">
                <div class="header-title">
                    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><span></span><span></span><span></span></button>
                    <h1>System Settings</h1>
                    <p class="page-subtitle">Configure system-wide settings</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="saveSettings()">💾 Save Changes</button>
                </div>
            </header>
            
            <div class="content-card">
                <div id="settingsForm">Loading...</div>
            </div>
        </main>
    </div>

    <script>
        let currentSettings = {};

        async function loadSettings() {
            const response = await fetch('../php/api/admin/system_settings.php');
            const data = await response.json();
            
            if (data.success) {
                currentSettings = data.settings;
                displaySettings(data.settings);
            }
        }

        function displaySettings(settings) {
            const settingsMap = {
                'school_name': { label: 'School Name', type: 'text' },
                'current_semester': { 
                    label: 'Current Semester', 
                    type: 'select',
                    options: ['First Semester', 'Second Semester', 'Summer']
                },
                'current_school_year': { label: 'Current School Year', type: 'text' },
                'registration_open': { 
                    label: 'Student Registration', 
                    type: 'select',
                    options: [
                        { value: '1', label: 'Open' },
                        { value: '0', label: 'Closed' }
                    ]
                }
            };

            let html = '';
            for (const [key, config] of Object.entries(settingsMap)) {
                const setting = settings[key] || { value: '', description: '' };
                html += `
                    <div class="setting-item">
                        <div class="setting-label">${config.label}</div>
                        <div class="setting-description">${setting.description || ''}</div>
                `;
                
                if (config.type === 'select') {
                    html += `<select class="setting-input" data-key="${key}">`;
                    if (Array.isArray(config.options) && typeof config.options[0] === 'string') {
                        config.options.forEach(opt => {
                            html += `<option value="${opt}" ${setting.value === opt ? 'selected' : ''}>${opt}</option>`;
                        });
                    } else {
                        config.options.forEach(opt => {
                            html += `<option value="${opt.value}" ${setting.value == opt.value ? 'selected' : ''}>${opt.label}</option>`;
                        });
                    }
                    html += `</select>`;
                } else {
                    html += `<input type="${config.type}" class="setting-input" data-key="${key}" value="${setting.value}">`;
                }
                
                html += `</div>`;
            }

            document.getElementById('settingsForm').innerHTML = html;
        }

        async function saveSettings() {
            const inputs = document.querySelectorAll('.setting-input');
            const settings = {};
            
            inputs.forEach(input => {
                settings[input.dataset.key] = input.value;
            });

            const response = await fetch('../php/api/admin/system_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ settings })
            });

            const result = await response.json();
            if (result.success) {
                alert(result.message);
                loadSettings();
            } else {
                alert('Error: ' + result.message);
            }
        }

        loadSettings();
    </script>

    <script>
        (function() {
            var sidebar = document.querySelector('.sidebar');
            // Scroll active nav item into view
            var saved = sessionStorage.getItem('sidebarScroll');
            if (saved) sidebar.scrollTop = parseInt(saved);
            // Save scroll position before navigating away
            document.querySelectorAll('.nav-item').forEach(function(link) {
                link.addEventListener('click', function() {
                    sessionStorage.setItem('sidebarScroll', sidebar.scrollTop);
                });
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

        // Close sidebar when a nav link is clicked (mobile UX)
        document.querySelectorAll('.nav-item').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1024) closeSidebar();
            });
        });
    })();
    </script>
<script src="/js/pwa.js"></script>

<!-- Mobile Bottom Navigation -->
<nav class="mobile-bottom-nav">
  <a href="dashboard.php" class="mobile-nav-item "><span class="mobile-nav-icon">📊</span>Dashboard</a>
  <a href="users.php" class="mobile-nav-item "><span class="mobile-nav-icon">👥</span>Users</a>
  <a href="courses.php" class="mobile-nav-item "><span class="mobile-nav-icon">🎓</span>Courses</a>
  <a href="sections.php" class="mobile-nav-item "><span class="mobile-nav-icon">📁</span>Sections</a>
  <a href="announcements.php" class="mobile-nav-item "><span class="mobile-nav-icon">📢</span>More</a>
</nav>
    <script src="/js/session-monitor.js"></script>
</body>
</html>

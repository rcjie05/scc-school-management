<?php
require_once '../php/config.php';
requireLogin();
if ($_SESSION['role'] !== 'student') { header('Location: ../php/logout.php'); exit(); }
$fullName = $_SESSION['name'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="apple-touch-icon" href="../images/logo2.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Map - Student</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/themes.css">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/floor-styles.css">
    <style>
        body { background: var(--background-main) !important; padding: 0 !important; }
        .main-content { flex: 1; padding: 0; background: var(--background-main); }
        .floor-container { padding: 20px; max-width: 100%; }
        .floor-header { background: var(--background-card); padding: 20px 30px; border-bottom: 1px solid var(--border-color); margin-bottom: 20px; }
        .floor-header h1 { margin: 0; font-size: 24px; color: var(--text-primary); }
        .container.active { box-shadow: none; margin: 0; background: transparent; }

        .content {
            display: grid !important;
            grid-template-columns: 1fr 380px !important;
            gap: 32px !important;
            padding: 32px !important;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Hide admin-only controls */
        .controls { display: none !important; }

        .canvas-container {
            background: white !important;
            border-radius: 20px !important;
            padding: 24px !important;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 20px;
        }
        .canvas-wrapper { position: relative; }

        #floorPlan {
            border: 3px solid var(--border-color) !important;
            border-radius: 16px !important;
            display: block !important;
            margin: 0 auto !important;
            visibility: visible !important;
            background: white;
            max-width: 100%;
        }

        .zoom-controls {
            position: absolute; top: 20px; right: 20px;
            display: flex; flex-direction: column; gap: 8px; z-index: 10;
        }
        .zoom-btn {
            width: 40px; height: 40px;
            border: 2px solid var(--border-color); background: var(--background-card);
            border-radius: 8px; font-size: 20px; cursor: pointer;
            transition: all 0.2s;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; color: var(--text-secondary);
        }
        .zoom-btn:hover { background: var(--background-main); border-color: var(--primary-purple); color: var(--primary-purple); transform: scale(1.05); }

        .legend {
            background: var(--background-main); padding: 16px 20px;
            border-radius: 12px; border: 1px solid var(--border-color);
            display: flex; flex-wrap: wrap; gap: 20px; align-items: center;
        }
        .legend h4 { margin: 0; font-size: 14px; font-weight: 600; color: var(--text-primary); }
        .legend-items { display: flex; flex-wrap: wrap; gap: 16px; flex: 1; }
        .legend-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary); }
        .legend-color { width: 20px; height: 20px; border-radius: 4px; border: 2px solid rgba(0,0,0,0.1); flex-shrink: 0; }
        .legend-item span { font-weight: 500; white-space: nowrap; }

        /* My Classes panel integration */
        .my-classes-section {
            background: var(--background-main);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 16px 20px;
        }
        .my-classes-section h4 {
            margin: 0 0 12px;
            font-size: 14px; font-weight: 700; color: var(--text-primary);
        }
        .class-chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 10px; margin: 3px;
            background: var(--background-card); border: 1.5px solid var(--border-color);
            border-radius: 999px; font-size: 12px; font-weight: 600;
            color: var(--text-primary); cursor: pointer; transition: all 0.15s;
        }
        .class-chip:hover { border-color: var(--primary-purple, #5b4e9b); color: var(--primary-purple, #5b4e9b); background: rgba(61,107,159,0.08); }
        .class-chip.active { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.08); box-shadow: 0 0 0 3px rgba(239,68,68,0.15); }
        .class-chip.active .dot { background: #ef4444; }
        .class-chip .dot { width:8px;height:8px;border-radius:50%;background:var(--secondary-green);flex-shrink:0; }
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
                    <a href="schedule.php" class="nav-item"><span class="nav-icon">📅</span><span>My Schedule</span></a>
                    <a href="subjects.php" class="nav-item"><span class="nav-icon">📚</span><span>Study Load</span></a>
                    <a href="grades.php" class="nav-item"><span class="nav-icon">🎓</span><span>Grades</span></a>
                    <a href="calendar.php" class="nav-item"><span class="nav-icon">🗓️</span><span>Calendar</span></a>
                    <a href="floorplan.php" class="nav-item active"><span class="nav-icon">🗺️</span><span>Floor Plan</span></a>
                    <a href="faculty.php" class="nav-item"><span class="nav-icon">👨‍🏫</span><span>Faculty Directory</span></a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Support</div>
                    <a href="announcements.php" class="nav-item"><span class="nav-icon">📢</span><span>Announcements</span></a>
                    <a href="feedback.php" class="nav-item"><span class="nav-icon">💬</span><span>Feedback</span></a>
                    <a href="reenrollment.php" class="nav-item"><span class="nav-icon">🔁</span><span>Re-enrollment</span></a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <a href="profile.php" class="nav-item"><span class="nav-icon">👤</span><span>My Profile</span></a>
                    <a href="../php/logout.php" class="nav-item"><span class="nav-icon">🚪</span><span>Logout</span></a>
                </div>
            </nav>
        </aside>

    <main class="main-content">
        <div class="floor-header">
            <h1>🗺️ Campus Navigation Map</h1>
            <p style="margin: 5px 0 0 0; color: var(--text-secondary);">Browse available routes to help you navigate the campus</p>
        </div>

        <div class="floor-container">
            <div class="container active" id="mainApp">
                <div class="content" id="mainContent">

                    <!-- Canvas (LEFT) -->
                    <div class="canvas-container">
                        <div class="canvas-wrapper">
                            <canvas id="floorPlan" width="900" height="700"></canvas>
                            <div class="zoom-controls">
                                <button class="zoom-btn" onclick="zoomIn()" title="Zoom In">+</button>
                                <button class="zoom-btn" onclick="resetZoom()" title="Reset Zoom">⊙</button>
                                <button class="zoom-btn" onclick="zoomOut()" title="Zoom Out">−</button>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="legend">
                            <h4>🗺️ Legend</h4>
                            <div class="legend-items">
                                <div class="legend-item"><div class="legend-color" style="background:#F4D03F;"></div><span>Administrative</span></div>
                                <div class="legend-item"><div class="legend-color" style="background:#85C1E2;"></div><span>Classrooms</span></div>
                                <div class="legend-item"><div class="legend-color" style="background:#7DCEA0;"></div><span>Services</span></div>
                                <div class="legend-item"><div class="legend-color" style="background:#F1948A;"></div><span>Common Areas</span></div>
                                <div class="legend-item"><div class="legend-color" style="background:#FF6B6B;"></div><span>Route Path</span></div>
                                <div class="legend-item"><div class="legend-color" style="background:#4ECDC4;"></div><span>Waypoints</span></div>
                            </div>
                        </div>

                        <!-- My Classes quick-nav -->
                        <div class="my-classes-section" id="myClassesSection" style="display:none;">
                            <h4>📚 My Classes – Quick Navigate</h4>
                            <div id="myClassChips"></div>
                        </div>
                    </div>

                    <!-- Routes Panel (RIGHT) -->
                    <div class="saved-routes" id="studentRouteSelector">
                        <div class="control-section">
                            <h3>📚 Available Routes</h3>
                            <input type="text" class="input-field" id="studentRouteSearch"
                                placeholder="🔍 Search routes..."
                                oninput="filterStudentRoutes()"
                                style="margin-bottom: 15px;">
                            <p style="color: var(--gray-600); font-size: 0.95em; margin-bottom: 15px;">
                                Click on any route below to display it on the map
                            </p>
                        </div>
                        <div id="studentRoutesList">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                </svg>
                                <p><strong>No routes available</strong></p>
                                <p style="font-size:0.9em;margin-top:5px;">Check back later for available routes</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</div>

<!-- Success message element required by floor-script.js -->
<div id="successMessage" style="
    position:fixed;bottom:80px;left:50%;transform:translateX(-50%);
    background:var(--secondary-green);color:var(--text-white);padding:10px 24px;border-radius:999px;
    font-weight:600;font-size:0.9rem;box-shadow:0 4px 12px rgba(0,0,0,0.15);
    opacity:0;transition:opacity 0.3s;pointer-events:none;z-index:9999;
"></div>
<!-- Stubs for admin elements floor-script.js may reference -->
<div id="routeInfo" style="display:none;"></div>
<div id="routeDetails" style="display:none;"></div>
<div id="waypointList" style="display:none;"></div>

<style>
#successMessage.show { opacity:1 !important; }
</style>

<script>
window.currentUserRole = 'student';
window.canEditRoutes   = false;
window.userFullName    = '<?php echo htmlspecialchars($fullName); ?>';
</script>
<script src="../js/floor-script.js?v=<?php echo time(); ?>"></script>

<script>
// ── Load student's own classes and show quick-nav chips ───────────────────────
(async function loadMyClassChips() {
    try {
        const res  = await fetch('../php/api/student/get_schedule.php');
        const data = await res.json();
        if (!data.success || !data.classes || data.classes.length === 0) return;

        // Build unique subject+room list
        const seen = new Set();
        const chips = [];
        data.classes.forEach(cls => {
            const key = cls.subject_code + '|' + (cls.room || '');
            if (!seen.has(key)) {
                seen.add(key);
                chips.push({ code: cls.subject_code, name: cls.subject_name, room: cls.room || 'TBA' });
            }
        });

        if (chips.length === 0) return;

        const chipsEl = document.getElementById('myClassChips');
        chipsEl.innerHTML = chips.map(c => `
            <span class="class-chip" onclick="findRoomOnMap('${c.room.replace(/'/g,"\\'")}')">
                <span class="dot"></span>
                <strong>${c.code}</strong> – ${c.room}
            </span>
        `).join('');

        document.getElementById('myClassesSection').style.display = 'block';
    } catch(e) { /* silent fail */ }
})();

// Pin a room on the canvas when a class chip is clicked
function findRoomOnMap(roomName) {
    if (!roomName || roomName === 'TBA') return;

    const match = (typeof rooms !== 'undefined' ? rooms : []).find(r =>
        r.name.toLowerCase() === roomName.toLowerCase() ||
        r.name.toLowerCase().includes(roomName.toLowerCase()) ||
        roomName.toLowerCase().includes(r.name.toLowerCase())
    );

    if (!match) {
        const msg = document.getElementById('successMessage');
        if (msg) { msg.textContent = '\uD83D\uDD0D Room not found: ' + roomName; msg.classList.add('show'); setTimeout(() => msg.classList.remove('show'), 2500); }
        return;
    }

    // If same room clicked again — unpin
    if (typeof pinnedRoom !== 'undefined' && pinnedRoom && pinnedRoom.name === match.name) {
        unpinRoom();
        return;
    }

    // Pin the room (drop animation + glow)
    if (typeof pinRoom === 'function') pinRoom(match);

    // Highlight the active chip
    document.querySelectorAll('.class-chip').forEach(el => el.classList.remove('active'));
    const clicked = [...document.querySelectorAll('.class-chip')].find(el => el.textContent.includes(roomName));
    if (clicked) clicked.classList.add('active');
}

// Sidebar scroll memory
(function() {
    var sidebar = document.querySelector('.sidebar');
    var saved = sessionStorage.getItem('sidebarScroll');
    if (saved) sidebar.scrollTop = parseInt(saved);
    document.querySelectorAll('.nav-item').forEach(function(link) {
        link.addEventListener('click', function() {
            sessionStorage.setItem('sidebarScroll', sidebar.scrollTop);
        });
    });
})();
</script>

<?php include 'chatbot-widget.php'; ?>
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
    <script src="/js/session-monitor.js"></script>
</body>
</html>

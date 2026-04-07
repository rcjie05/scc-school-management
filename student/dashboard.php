<?php
require_once '../php/config.php';
$student_course = strtolower($_SESSION['course'] ?? '');
$_avatar_conn = getDBConnection();
$_col = $_avatar_conn->query("SHOW COLUMNS FROM `users` LIKE 'avatar_url'");
if ($_col && $_col->num_rows === 0) {
    $_avatar_conn->query("ALTER TABLE `users` ADD COLUMN `avatar_url` VARCHAR(500) NULL DEFAULT NULL AFTER `status`");
}
$_avatar_stmt = $_avatar_conn->prepare("SELECT name, avatar_url FROM users WHERE id = ?");
$_avatar_stmt->bind_param("i", $_SESSION['user_id']);
$_avatar_stmt->execute();
$_avatar_user = $_avatar_stmt->get_result()->fetch_assoc();
$_avatar_stmt->close();
$_avatar_conn->close();

$show_bsit_bg = (strpos($student_course, 'bsit') !== false || strpos($student_course, 'information technology') !== false);
$show_bshtm_bg = (strpos($student_course, 'bshtm') !== false || strpos($student_course, 'hospitality') !== false || strpos($student_course, 'tourism') !== false || strpos($student_course, 'htm') !== false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="apple-touch-icon" href="../images/logo2.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Saint Cecilia College</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/themes.css">
    <style>
        /* ── Weekly Schedule Widget ── */
        .sched-tabs {
            display: flex;
            gap: 0.35rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .sched-tab {
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            border: 1.5px solid var(--border-color);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            background: white;
            color: var(--text-secondary);
            transition: all 0.15s;
        }
        .sched-tab:hover { border-color: var(--primary-purple); color: var(--primary-purple); }
        .sched-tab.active {
            background: var(--primary-purple);
            border-color: var(--primary-purple);
            color: white;
        }
        .sched-tab.today-tab { position: relative; }
        .today-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: var(--secondary-green);
            border-radius: 50%;
            margin-left: 4px;
            vertical-align: middle;
        }
        .sched-day-panel { display: none; }
        .sched-day-panel.active { display: flex; flex-direction: column; gap: 0.75rem; }
        .sched-item {
            display: flex;
            align-items: stretch;
            gap: 0.9rem;
            background: var(--background-main, #f9fafb);
            border-radius: var(--radius-md);
            padding: 0.9rem 1rem;
            border-left: 4px solid var(--primary-purple);
            transition: box-shadow 0.15s;
        }
        .sched-item:hover { box-shadow: 0 2px 8px rgba(91,78,155,0.12); }
        .sched-time-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 62px;
            text-align: center;
        }
        .sched-time-start {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary-purple);
        }
        .sched-time-end {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        .sched-divider {
            width: 1px;
            background: var(--border-color);
            align-self: stretch;
        }
        .sched-info { flex: 1; min-width: 0; }
        .sched-subj {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 0.92rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sched-subj-code {
            font-size: 0.75rem;
            color: var(--primary-purple);
            font-weight: 600;
            margin-bottom: 0.15rem;
        }
        .sched-meta {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 0.3rem;
        }
        .sched-meta span {
            font-size: 0.78rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .no-class-msg {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .no-class-msg .nc-icon { font-size: 2.2rem; display: block; margin-bottom: 0.5rem; }

        /* ── Enrollment Card ── */
        .enroll-section { margin-top: 2rem; }
        .enroll-card {
            background: white;
            border-radius: var(--radius-lg, 14px);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        .enroll-card-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1.5px solid var(--border-color);
        }
        .enroll-card-header h2 {
            font-size: 1rem; font-weight: 700;
            color: var(--text-primary);
            display: flex; align-items: center; gap: 0.5rem;
        }
        /* Blocked state */
        .enroll-blocked { padding: 2.5rem 2rem; text-align: center; }
        .blocked-icon { font-size: 3.5rem; margin-bottom: 1rem; display: block; }
        .blocked-badge {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: rgba(184,92,92,0.12); color: var(--secondary-pink);
            border: 1px solid rgba(184,92,92,0.3); border-radius: 999px;
            padding: 0.35rem 1rem; font-size: 0.78rem; font-weight: 700;
            letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 1rem;
        }
        .blocked-title { font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; }
        .blocked-desc {
            font-size: 0.875rem; color: var(--text-secondary);
            max-width: 480px; margin: 0 auto 1.5rem; line-height: 1.7;
        }
        .blocked-info-box {
            background: rgba(212,169,106,0.12); border: 1px solid rgba(212,169,106,0.35);
            border-radius: 10px; padding: 1rem 1.25rem;
            max-width: 420px; margin: 0 auto; text-align: left;
        }
        .blocked-info-box strong { display: block; font-size: 0.82rem; color: var(--text-primary); margin-bottom: 0.4rem; }
        .blocked-info-box ul { padding-left: 1.2rem; font-size: 0.82rem; color: var(--text-secondary); line-height: 1.8; }
        .blocked-contact { margin-top: 1.25rem; font-size: 0.82rem; color: var(--text-secondary); }
        .blocked-contact a { color: var(--primary-purple); font-weight: 600; text-decoration: none; }
        /* Open state */
        .enroll-open-notice {
            background: rgba(90,158,138,0.08); border-bottom: 1.5px solid rgba(90,158,138,0.3);
            padding: 0.85rem 1.5rem;
            display: flex; align-items: center; gap: 0.75rem;
            font-size: 0.85rem; color: var(--secondary-green);
        }
        .enroll-open-notice strong { font-weight: 700; }
        .enroll-frame { width: 100%; height: 700px; border: none; display: block; }
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
                    <a href="dashboard.php" class="nav-item active"><span class="nav-icon">📊</span><span>Dashboard</span></a>
                    <a href="schedule.php" class="nav-item"><span class="nav-icon">📅</span><span>My Schedule</span></a>
                    <a href="subjects.php" class="nav-item"><span class="nav-icon">📚</span><span>Study Load</span></a>
                    <a href="grades.php" class="nav-item"><span class="nav-icon">🎓</span><span>Grades</span></a>
                    <a href="calendar.php" class="nav-item"><span class="nav-icon">🗓️</span><span>Calendar</span></a>
                    <a href="floorplan.php" class="nav-item"><span class="nav-icon">🗺️</span><span>Floor Plan</span></a>
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
            <header class="page-header">
                <div class="header-title">
                    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><span></span><span></span><span></span></button>
                    <h1>Welcome to Saint Cecilia College</h1>
                </div>
                <div class="header-actions">
                    <div class="school-year-badge">
                        <span>📚</span>
                        <span>School Year 2024 - 2025</span>
                    </div>
                    <a href="profile.php" style="text-decoration:none;">
                    <div class="user-profile">
                        <div class="user-avatar" id="userAvatar"><?php
$_avatar_url = !empty($_avatar_user['avatar_url']) ? htmlspecialchars(getAvatarUrl($_avatar_user['avatar_url'])) : null;
$_initials   = strtoupper(substr($_avatar_user['name'] ?? '?', 0, 1));
?>
<?php if ($_avatar_url): ?>
<img src="<?= $_avatar_url ?>?t=<?= time() ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;" alt="">
<?php else: ?>
<?= $_initials ?>
<?php endif; ?></div>
                        <div class="user-info">
                            <div class="user-name" id="userName">Student Name</div>
                            <div class="user-role">Student</div>
                        </div>
                    </div>
                    </a>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card purple">
                    <div class="stat-header"><div class="stat-icon">🏫</div></div>
                    <div class="stat-label">Enrollment Status</div>
                    <div class="stat-value" id="enrollmentStatus">Pending</div>
                </div>
                <div class="stat-card pink">
                    <div class="stat-header"><div class="stat-icon">📚</div></div>
                    <div class="stat-label">Enrolled Subjects</div>
                    <div class="stat-value" id="enrolledSubjects">0</div>
                </div>
                <div class="stat-card yellow">
                    <div class="stat-header"><div class="stat-icon">⏰</div></div>
                    <div class="stat-label">Total Units</div>
                    <div class="stat-value" id="totalUnits">0</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-header"><div class="stat-icon">📊</div></div>
                    <div class="stat-label">Current GPA</div>
                    <div class="stat-value" id="currentGPA">—</div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Weekly Schedule -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">📅 My Schedule</h2>
                        <a href="schedule.php" class="view-all-btn">Full View</a>
                    </div>
                    <!-- Day tabs -->
                    <div class="sched-tabs" id="schedTabs"></div>
                    <!-- Day panels -->
                    <div id="schedPanels">
                        <p style="text-align:center;color:var(--text-secondary);padding:2rem;">Loading schedule...</p>
                    </div>
                </div>

                <!-- Recent Announcements -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">Announcements</h2>
                        <a href="announcements.php" class="view-all-btn">View All</a>
                    </div>
                    <div id="announcements">
                        <p style="text-align:center;color:var(--text-secondary);padding:2rem;">Loading announcements...</p>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="content-card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">Academic Calendar</h2>
                </div>
                <div class="calendar-container">
                    <div class="calendar-header">
                        <button class="btn btn-secondary" onclick="changeMonth(-1)">← Previous</button>
                        <h3 id="currentMonth">February 2025</h3>
                        <button class="btn btn-secondary" onclick="changeMonth(1)">Next →</button>
                    </div>
                    <div class="calendar-grid" id="calendarGrid"></div>
                </div>
            </div>

            <!-- Enrollment Section -->
            <div class="enroll-section">
                <div class="enroll-card">
                    <div class="enroll-card-header">
                        <h2>📋 School Year Enrollment</h2>
                        <span style="font-size:0.78rem;color:var(--text-secondary);">SY 2025–2026</span>
                    </div>

                    <?php
                    // Fetch fresh student status from DB
                    $enroll_conn = getDBConnection();
                    $enroll_stmt = $enroll_conn->prepare("SELECT status, name, course, year_level FROM users WHERE id = ?");
                    $enroll_stmt->bind_param("i", $_SESSION['user_id']);
                    $enroll_stmt->execute();
                    $enroll_user = $enroll_stmt->get_result()->fetch_assoc();
                    $enroll_stmt->close();
                    $enroll_conn->close();
                    $student_status = strtolower($enroll_user['status'] ?? 'pending');
                    $is_returnee = in_array($student_status, ['active', 'enrolled', 'approved']);
                    ?>

                    <?php if ($is_returnee): ?>
                    <!-- RETURNEE: Can re-enroll online via dedicated page -->
                    <div class="enroll-blocked">
                        <span class="blocked-icon">🎓</span>
                        <div style="display:inline-flex;align-items:center;gap:.4rem;background:rgba(61,107,159,0.12);color:var(--primary-purple-dark);border:1px solid rgba(61,107,159,0.3);border-radius:999px;padding:.35rem 1rem;font-size:.78rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:1rem;">
                            ✅ Active Student — Returnee
                        </div>
                        <div class="blocked-title">Ready to re-enroll for the next school year?</div>
                        <p class="blocked-desc">
                            As a <strong>returning student</strong>, you can re-enroll online. Your personal
                            information will be pre-filled from our records — just confirm your details,
                            choose your new year level and section, and submit.
                        </p>
                        <a href="reenrollment.php"
                           style="display:inline-flex;align-items:center;gap:.5rem;background:var(--background-sidebar);color:var(--text-white);padding:.85rem 2rem;border-radius:999px;font-weight:700;font-size:.9rem;text-decoration:none;margin-bottom:1.5rem;transition:background .2s;"
                           onmouseover="this.style.background='var(--background-sidebar-hover)'"
                           onmouseout="this.style.background='var(--background-sidebar)'">
                            🔄 Start Re-Enrollment
                        </a>
                        <div class="blocked-info-box">
                            <strong>📋 What you'll need:</strong>
                            <ul>
                                <li>Previous semester's <strong>Report Card / Grades</strong></li>
                                <li>Your valid <strong>School ID</strong></li>
                                <li>Settle any outstanding <strong>fees</strong> with Accounting</li>
                            </ul>
                        </div>
                        <div class="blocked-contact">
                            Questions? Contact the Registrar at
                            <a href="tel:032-326-3677">(032) 326-3677</a> or
                            <a href="mailto:info@stcecilia.edu.ph">info@stcecilia.edu.ph</a>
                        </div>
                    </div>

                    <?php else: ?>
                    <!-- OPEN: New / pending / inactive student can submit enrollment -->
                    <div class="enroll-open-notice">
                        <span style="font-size:1.1rem;">✅</span>
                        <div><strong>Enrollment is open.</strong> Complete all 5 steps of the form below to submit your application.</div>
                    </div>
                    <iframe
                        src="../enrollment.html"
                        class="enroll-frame"
                        title="Student Enrollment Form"
                        loading="lazy"
                    ></iframe>
                    <?php endif; ?>

                </div>
            </div>
        </main>
    </div>

    <script>
        const DAYS = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        let todayName = '';

        async function loadUserData() {
            try {
                const response = await fetch('../php/api/student/get_dashboard_data.php');
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('[Dashboard] API returned non-JSON (session expired or redirect?):', text.substring(0, 300));
                    return;
                }
                const data = await response.json();
                console.log('[Dashboard] API response:', JSON.stringify(data.stats));
                if (data.success) {
                    document.getElementById('userName').textContent = data.user.name;
                    const avatarEl = document.getElementById('userAvatar');
                    if (data.user.avatar_url) {
                        avatarEl.innerHTML = '<img src="' + data.user.avatar_url + '?t=' + Date.now() + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">';
                    } else {
                        avatarEl.textContent = (data.user.name || '?').charAt(0).toUpperCase();
                    }
                    document.getElementById('enrollmentStatus').textContent = data.stats.enrollment_status || 'Unknown';
                    document.getElementById('enrolledSubjects').textContent = (data.stats.enrolled_subjects !== undefined) ? data.stats.enrolled_subjects : '0';
                    document.getElementById('totalUnits').textContent       = (data.stats.total_units !== undefined)       ? data.stats.total_units       : '0';
                    document.getElementById('currentGPA').textContent       = data.stats.gpa || '—';
                    todayName = data.today || new Date().toLocaleDateString('en-US', {weekday:'long'});
                    buildScheduleWidget(data.all_schedule || data.schedule || []);
                    loadAnnouncements(data.announcements);
                } else {
                    console.error('[Dashboard] API error:', data.message);
                }
            } catch (error) {
                console.error('[Dashboard] Fetch error:', error);
            }
        }

        function buildScheduleWidget(allSchedule) {
            // Group by day
            const byDay = {};
            DAYS.forEach(d => byDay[d] = []);
            allSchedule.forEach(item => {
                if (byDay[item.day]) byDay[item.day].push(item);
            });

            // Only show days that have classes, always include today
            const daysWithClasses = DAYS.filter(d => byDay[d].length > 0 || d === todayName);

            const tabsEl   = document.getElementById('schedTabs');
            const panelsEl = document.getElementById('schedPanels');
            tabsEl.innerHTML   = '';
            panelsEl.innerHTML = '';

            if (daysWithClasses.length === 0) {
                panelsEl.innerHTML = '<div class="no-class-msg"><span class="nc-icon">📭</span>No schedule found. Make sure your study load is finalized.</div>';
                return;
            }

            daysWithClasses.forEach((day, idx) => {
                const isToday = day === todayName;
                const shortDay = day.substring(0, 3);

                // Tab
                const tab = document.createElement('button');
                tab.className = 'sched-tab' + (isToday ? ' today-tab' : '');
                tab.dataset.day = day;
                tab.innerHTML = shortDay + (isToday ? '<span class="today-dot"></span>' : '');
                tab.onclick = () => switchDay(day);
                tabsEl.appendChild(tab);

                // Panel
                const panel = document.createElement('div');
                panel.className = 'sched-day-panel';
                panel.id = 'panel-' + day;
                panel.innerHTML = renderDayClasses(byDay[day], day);
                panelsEl.appendChild(panel);
            });

            // Activate today or first day
            const activateDay = daysWithClasses.includes(todayName) ? todayName : daysWithClasses[0];
            switchDay(activateDay);
        }

        function switchDay(day) {
            document.querySelectorAll('.sched-tab').forEach(t => {
                t.classList.toggle('active', t.dataset.day === day);
            });
            document.querySelectorAll('.sched-day-panel').forEach(p => {
                p.classList.toggle('active', p.id === 'panel-' + day);
            });
        }

        function renderDayClasses(classes, day) {
            const isToday = day === todayName;
            if (!classes || classes.length === 0) {
                return `<div class="no-class-msg">
                    <span class="nc-icon">🎉</span>
                    No classes ${isToday ? 'today' : 'on ' + day}
                </div>`;
            }

            return classes.map(c => `
                <div class="sched-item">
                    <div class="sched-time-col">
                        <div class="sched-time-start">${esc(c.start_time)}</div>
                        <div class="sched-time-end">${esc(c.end_time)}</div>
                    </div>
                    <div class="sched-divider"></div>
                    <div class="sched-info">
                        <div class="sched-subj-code">${esc(c.subject_code)}</div>
                        <div class="sched-subj">${esc(c.subject_name)}</div>
                        <div class="sched-meta">
                            <span>👨‍🏫 ${esc(c.teacher_name)}</span>
                            <span>📍 ${esc(c.room)}</span>
                            ${c.section && c.section !== 'TBA' ? `<span>🏷️ ${esc(c.section)}</span>` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function loadAnnouncements(announcements) {
            const container = document.getElementById('announcements');

            if (!announcements || announcements.length === 0) {
                container.innerHTML = '<p style="text-align:center;color:var(--text-secondary);padding:2rem;">No announcements</p>';
                return;
            }

            let html = '<div style="display:flex;flex-direction:column;gap:1rem;">';
            announcements.forEach(item => {
                html += `
                    <div style="padding:1rem;background:var(--background-main);border-radius:var(--radius-md);">
                        <div style="font-weight:700;color:var(--text-primary);margin-bottom:0.25rem;">${esc(item.title)}</div>
                        <div style="font-size:0.875rem;color:var(--text-secondary);margin-bottom:0.5rem;">${esc(item.content)}</div>
                        <div style="font-size:0.75rem;color:var(--text-light);">${esc(item.date)}</div>
                    </div>`;
            });
            html += '</div>';
            container.innerHTML = html;
        }

        function esc(str) {
            return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }

        // Calendar
        let currentDate = new Date();

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            document.getElementById('currentMonth').textContent =
                currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const calendarGrid = document.getElementById('calendarGrid');
            let html = '';
            ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(d => {
                html += `<div style="text-align:center;font-weight:700;padding:0.5rem;color:var(--text-secondary);">${d}</div>`;
            });
            for (let i = 0; i < firstDay; i++) html += '<div></div>';
            const today = new Date();
            for (let day = 1; day <= daysInMonth; day++) {
                const isToday = day === today.getDate() && month === today.getMonth() && year === today.getFullYear();
                html += `<div class="calendar-day ${isToday ? 'active' : ''}">${day}</div>`;
            }
            calendarGrid.innerHTML = html;
        }

        function changeMonth(direction) {
            currentDate.setMonth(currentDate.getMonth() + direction);
            renderCalendar();
        }

        loadUserData();
        renderCalendar();
    </script>

    <script>
        (function() {
            var sidebar = document.querySelector('.sidebar');
            // Always reset scroll to top on dashboard (entry point after login)
            sessionStorage.removeItem('sidebarScroll');
            sidebar.scrollTop = 0;
            // Save scroll position before navigating away
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
<nav class="mobile-bottom-nav">
  <a href="dashboard.php" class="mobile-nav-item active"><span class="mobile-nav-icon">🏠</span>Home</a>
  <a href="subjects.php" class="mobile-nav-item"><span class="mobile-nav-icon">📚</span>Subjects</a>
  <a href="schedule.php" class="mobile-nav-item"><span class="mobile-nav-icon">📅</span>Schedule</a>
  <a href="grades.php" class="mobile-nav-item"><span class="mobile-nav-icon">📊</span>Grades</a>
  <a href="profile.php" class="mobile-nav-item"><span class="mobile-nav-icon">👤</span>Profile</a>
</nav>
    <script src="../js/session-monitor.js"></script>
</body>
</html>

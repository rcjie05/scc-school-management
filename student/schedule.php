<?php
require_once '../php/config.php';

// ── Dynamic school name & school year ────────────────────────────────
$_sn_conn = getDBConnection();
$_sn_res  = $_sn_conn ? $_sn_conn->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('school_name','current_school_year')") : false;
$school_name = 'My School';
$current_school_year = '----';
if ($_sn_res) { while ($_sn_row = $_sn_res->fetch_assoc()) { if ($_sn_row['setting_key']==='school_name') $school_name=$_sn_row['setting_value']; if ($_sn_row['setting_key']==='current_school_year') $current_school_year=$_sn_row['setting_value']; } }
// ──────────────────────────────────────────────────────────────────────
requireRole('student');
$student_course = strtolower($_SESSION['course'] ?? '');
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
    <title>My Schedule - <?= htmlspecialchars($school_name) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/mobile-fix.css">
    <link rel="stylesheet" href="../css/themes.css">
    <style>
        /* ── Timetable grid ── */
        .timetable-wrap { overflow-x: auto; }
        .timetable {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
            font-size: .85rem;
        }
        .timetable th {
            background: var(--primary-purple);
            color: var(--text-white);
            padding: .65rem .5rem;
            text-align: center;
            font-weight: 700;
            white-space: nowrap;
        }
        .timetable td {
            border: 1px solid var(--border-color);
            vertical-align: middle;
            padding: 0;
            height: 30px;
        }
        .timetable .time-col {
            background: var(--background-main);
            font-weight: 600;
            font-size: .72rem;
            color: var(--text-secondary);
            text-align: center;
            padding: .2rem .4rem;
            white-space: nowrap;
            width: 90px;
        }
        .timetable .empty-cell { background: var(--background-main); }

        .sched-cell {
            background: rgba(61,107,159,0.1);
            border-left: 3px solid var(--primary-purple);
            padding: .45rem .55rem;
            height: 100%;
            box-sizing: border-box;
            cursor: default;
        }
        .sched-cell .sc-subj { font-weight: 700; font-size: .8rem; color: var(--primary-purple-dark); }
        .sched-cell .sc-time { font-size: .72rem; color: var(--primary-purple); margin-top: .1rem; }
        .sched-cell .sc-room { font-size: .72rem; color: var(--secondary-blue); }
        .sched-cell .sc-tchr { font-size: .7rem;  color: var(--text-secondary); margin-top: .1rem; }

        /* day color variants */
        .sched-cell.mon { background: rgba(61,107,159,0.12); border-color:var(--primary-purple); }
        .sched-cell.tue { background: rgba(184,92,92,0.1); border-color:var(--secondary-pink); }
        .sched-cell.wed { background: rgba(90,158,138,0.12); border-color:var(--secondary-green); }
        .sched-cell.thu { background: rgba(212,169,106,0.15); border-color:var(--secondary-yellow); }
        .sched-cell.fri { background: rgba(91,141,184,0.12); border-color:var(--secondary-blue); }
        .sched-cell.sat { background: rgba(184,92,92,0.07); border-color:var(--secondary-pink); }
        .sched-cell.mon .sc-subj { color:var(--primary-purple-dark); }
        .sched-cell.tue .sc-subj { color:var(--secondary-pink); }
        .sched-cell.wed .sc-subj { color:var(--secondary-green); }
        .sched-cell.thu .sc-subj { color:var(--text-primary); }
        .sched-cell.fri .sc-subj { color:var(--primary-purple-dark); }
        .sched-cell.sat .sc-subj { color:var(--secondary-pink); }

        /* ── Class list cards ── */
        .class-card {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            background: var(--background-card);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--primary-purple);
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            margin-bottom: .75rem;
        }
        .class-card h3 { margin: 0 0 .3rem; font-size: 1rem; }
        .class-card p  { margin: .15rem 0; font-size: .83rem; color: var(--text-secondary); }
        .section-pill {
            background: var(--primary-purple);
            color: var(--text-white);
            padding: .3rem .85rem;
            border-radius: 1rem;
            font-weight: 700;
            font-size: .8rem;
            white-space: nowrap;
        }

        /* empty state */
        .empty-sched {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
        .empty-sched .ei { font-size: 3rem; margin-bottom: .75rem; }
    </style>
        </head>
<body>

    <div class="page-wrapper">
                <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="logo-icon">
                    <img src="../images/logo2.jpg" alt="SCC Logo" id="sidebarLogoImg" style="width:100%;height:100%;object-fit:cover;border-radius:var(--radius-md);">
                </div>
                <div class="logo-text">
                    <span id="sidebarSchoolName"><?= htmlspecialchars($school_name) ?></span>
                    <span>Student Portal</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="dashboard.php" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
                    <a href="schedule.php" class="nav-item active"><span class="nav-icon">📅</span><span>My Schedule</span></a>
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
                    <h1>My Class Schedule</h1>
                <p class="page-subtitle" id="sectionLabel">Loading…</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="window.print()">🖨️ Print</button>
            </div>
        </header>

        <!-- Timetable -->
        <div class="content-card" style="margin-bottom:1.5rem;">
            <div class="card-header">
                <h2 class="card-title">Weekly Timetable</h2>
            </div>
            <div class="timetable-wrap" style="padding:1rem;">
                <table class="timetable">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                            <th>Saturday</th>
                        </tr>
                    </thead>
                    <tbody id="timetableBody">
                        <tr><td colspan="7" style="text-align:center;padding:2rem;">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Class list -->
        <div class="content-card">
            <div class="card-header"><h2 class="card-title">Class List</h2></div>
            <div style="padding:1rem;" id="classList">
                <div style="text-align:center;padding:2rem;color:var(--text-secondary);">Loading…</div>
            </div>
        </div>
    </main>
</div>

<script>
const DAYS    = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
const DAY_CSS = { Monday:'mon', Tuesday:'tue', Wednesday:'wed', Thursday:'thu', Friday:'fri', Saturday:'sat' };

// 30-minute slots from 7:00 AM to 9:00 PM (7.0 to 21.0, step 0.5)
const SLOTS = [];
for (let t = 7; t <= 21; t += 0.5) SLOTS.push(t);

function fmtSlot(t) {
    const h = Math.floor(t);
    const m = t % 1 === 0.5 ? '30' : '00';
    if (h === 0  && m === '00') return '12:00 AM';
    if (h === 12 && m === '00') return '12:00 PM';
    const period = h < 12 ? 'AM' : 'PM';
    const disp   = h <= 12 ? h : h - 12;
    return disp + ':' + m + ' ' + period;
}

// Convert "HH:MM" time string to decimal hour (e.g. "08:30" -> 8.5)
function timeToDecimal(str) {
    if (!str) return null;
    const parts = str.split(':');
    return parseInt(parts[0]) + (parseInt(parts[1]) >= 30 ? 0.5 : 0);
}

// Convert "HH:MM" to the slot key it belongs to (rounded down to nearest 0.5)
function timeToSlot(str) {
    if (!str) return null;
    const parts = str.split(':');
    const h = parseInt(parts[0]);
    const m = parseInt(parts[1]);
    return h + (m >= 30 ? 0.5 : 0);
}

async function loadSchedule() {
    try {
        const res  = await fetch('../php/api/student/get_schedule.php');
        const data = await res.json();

        if (!data.success) {
            showEmpty();
            return;
        }

        renderTimetable(data.schedule || []);
        renderClassList(data.classes  || []);

    } catch (err) {
        console.error(err);
        showEmpty();
    }
}

/* ── Timetable grid ── */
function renderTimetable(schedule) {
    const tbody = document.getElementById('timetableBody');

    if (!schedule.length) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-sched"><div class="ei">📅</div><p>No schedule found.<br><small>Make sure your study load is assigned to a section with schedules set up.</small></p></div></td></tr>';
        return;
    }

    // Build lookup: day -> startSlot -> { ...class, rowspan }
    const lookup = {};
    const blockedCells = {}; // day -> Set of slot decimals that are "consumed" by a rowspan

    schedule.forEach(s => {
        if (!lookup[s.day]) lookup[s.day] = {};
        if (!blockedCells[s.day]) blockedCells[s.day] = new Set();
        const startSlot = s.start_time ? timeToSlot(s.start_time) : s.hour;
        const endSlot   = s.end_time   ? timeToSlot(s.end_time)   : (s.hour + 1);
        const rowspan   = Math.max(1, Math.round((endSlot - startSlot) / 0.5));
        lookup[s.day][startSlot] = { ...s, startSlot, endSlot, rowspan };
        // Mark all slots after the first as blocked (they'll be skipped in render)
        for (let t = startSlot + 0.5; t < endSlot; t = Math.round((t + 0.5) * 10) / 10) {
            blockedCells[s.day].add(t);
        }
    });

    // Format a decimal slot as 12h range label: "hh:mm-hh:mm" (e.g. "07:30-08:00", "12:30-01:00")
    function fmtRange(t) {
        function to12(h) { return h === 0 ? 12 : h > 12 ? h - 12 : h; }
        function pad(n)  { return String(n).padStart(2, '0'); }
        const h1 = Math.floor(t);
        const m1 = t % 1 === 0.5 ? 30 : 0;
        const t2 = Math.round((t + 0.5) * 10) / 10;
        const h2 = Math.floor(t2);
        const m2 = t2 % 1 === 0.5 ? 30 : 0;
        return pad(to12(h1)) + ':' + pad(m1) + '-' + pad(to12(h2)) + ':' + pad(m2);
    }

    let html = '';
    SLOTS.forEach(t => {
        html += '<tr>';
        html += '<td class="time-col">' + fmtRange(t) + '</td>';
        DAYS.forEach(day => {
            if (!blockedCells[day]) blockedCells[day] = new Set();
            if (blockedCells[day].has(t)) return; // skip — covered by a rowspan above

            const entry = lookup[day] && lookup[day][t] ? lookup[day][t] : null;
            if (entry) {
                const css = DAY_CSS[day] || '';
                html += '<td rowspan="' + entry.rowspan + '" style="padding:0;vertical-align:top;">' +
                    '<div class="sched-cell ' + css + '" style="height:100%;box-sizing:border-box;">' +
                    '  <div class="sc-subj">' + esc(entry.subject_code) + '</div>' +
                    '  <div class="sc-time">' + esc(entry.start_fmt) + ' – ' + esc(entry.end_fmt) + '</div>' +
                    (entry.room    ? '<div class="sc-room">🚪 ' + esc(entry.room)    + '</div>' : '') +
                    (entry.teacher ? '<div class="sc-tchr">👨‍🏫 ' + esc(entry.teacher) + '</div>' : '') +
                    '</div></td>';
            } else {
                html += '<td class="empty-cell"></td>';
            }
        });
        html += '</tr>';
    });
    tbody.innerHTML = html;
}

/* ── Class list ── */
function renderClassList(classes) {
    const el = document.getElementById('classList');

    if (!classes.length) {
        el.innerHTML = '<div class="empty-sched"><div class="ei">📚</div><p>No classes in your study load yet.</p></div>';
        return;
    }

    // Update header subtitle
    const sec = classes[0].section;
    if (sec && sec !== 'TBA') {
        document.getElementById('sectionLabel').textContent = 'Section: ' + sec;
    } else {
        document.getElementById('sectionLabel').textContent = '';
    }

    el.innerHTML = classes.map(c => {
        return '<div class="class-card">' +
            '<div style="flex:1;">' +
            '  <h3>' + esc(c.subject_name) + '</h3>' +
            '  <p><strong>Code:</strong> ' + esc(c.subject_code) + ' &nbsp;|&nbsp; <strong>Units:</strong> ' + esc(c.units) + '</p>' +
            '  <p><strong>Teacher:</strong> ' + esc(c.teacher_name) + '</p>' +
            (c.schedule ? '<p>📅 ' + esc(c.schedule) + '</p>' : '') +
            (c.room     ? '<p>🚪 ' + esc(c.room)     + '</p>' : '') +
            '</div>' +
            '<div style="flex-shrink:0;">' +
            '  <div class="section-pill">' + esc(c.section || 'TBA') + '</div>' +
            '</div>' +
            '</div>';
    }).join('');
}

function showEmpty() {
    document.getElementById('timetableBody').innerHTML =
        '<tr><td colspan="7"><div class="empty-sched"><div class="ei">⚠️</div><p>Could not load schedule. Please try again.</p></div></td></tr>';
    document.getElementById('classList').innerHTML = '';
}

function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadSchedule();
</script>
    <script>
        (function() {
            var sidebar = document.querySelector('.sidebar');
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
  <a href="dashboard.php" class="mobile-nav-item"><span class="mobile-nav-icon">🏠</span>Home</a>
  <a href="subjects.php" class="mobile-nav-item"><span class="mobile-nav-icon">📚</span>Subjects</a>
  <a href="schedule.php" class="mobile-nav-item active"><span class="mobile-nav-icon">📅</span>Schedule</a>
  <a href="grades.php" class="mobile-nav-item"><span class="mobile-nav-icon">📊</span>Grades</a>
  <a href="profile.php" class="mobile-nav-item"><span class="mobile-nav-icon">👤</span>Profile</a>
</nav>
    <script src="../js/session-monitor.js"></script>
    <script src="../js/apply-branding.js"></script>
</body>
</html>
<?php require_once '../php/config.php'; requireRole('student');
$student_course = strtolower($_SESSION['course'] ?? '');
$show_bsit_bg = (strpos($student_course, 'bsit') !== false || strpos($student_course, 'information technology') !== false);
$show_bshtm_bg = (strpos($student_course, 'bshtm') !== false || strpos($student_course, 'hospitality') !== false || strpos($student_course, 'tourism') !== false || strpos($student_course, 'htm') !== false); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades - Saint Cecilia College</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/themes.css">
        </head>
<body>
    <div class="page-wrapper">
                <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="logo-icon">SCC</div>
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
                    <a href="grades.php" class="nav-item active"><span class="nav-icon">🎓</span><span>Grades</span></a>
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
                <div class="header-title"><h1>My Grades</h1></div>
                <div class="header-actions"><button class="btn btn-primary" onclick="window.print()">🖨️ Print</button></div>
            </header>
            
            <div class="stats-grid">
                <div class="stat-card purple"><div class="stat-icon">📊</div><div class="stat-label">Current GPA</div><div class="stat-value" id="gpa">—</div></div>
                <div class="stat-card green"><div class="stat-icon">✅</div><div class="stat-label">Passed Subjects</div><div class="stat-value" id="passed">0</div></div>
                <div class="stat-card yellow"><div class="stat-icon">📚</div><div class="stat-label">Total Units Earned</div><div class="stat-value" id="units">0</div></div>
            </div>
            
            <div class="content-card" style="margin-top: 2rem;">
                <div class="card-header"><h2 class="card-title">Academic Performance</h2>
                    <select id="semesterFilter" class="form-select" style="width: 200px;"><option>First Semester</option><option>Second Semester</option></select>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Subject Code</th><th>Subject Name</th><th>Units</th><th>Midterm</th><th>Final</th><th>Remarks</th></tr></thead>
                        <tbody id="gradesTable"><tr><td colspan="6" style="text-align:center;padding:2rem;">Loading...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script>
        async function loadGrades() {
            const response = await fetch('../php/api/student/get_grades.php');
            const data = await response.json();
            if (data.success) {
                document.getElementById('gpa').textContent = data.stats.gpa || '—';
                document.getElementById('passed').textContent = data.stats.passed;
                document.getElementById('units').textContent = data.stats.units;
                let html = '';
                data.grades.forEach(g => {
                    const remarkText = g.remarks ? g.remarks.toUpperCase() : null;
                    const remark = remarkText === 'PASSED' ? '<span class="status-badge status-approved">PASSED</span>' :
                                   remarkText === 'FAILED' ? '<span class="status-badge status-rejected">FAILED</span>' :
                                   remarkText ? `<span class="status-badge">${remarkText}</span>` : '—';
                    html += `<tr><td><strong>${g.subject_code}</strong></td><td>${g.subject_name}</td><td>${g.units}</td>
                             <td>${g.midterm || '—'}</td><td>${g.final || '—'}</td><td>${remark}</td></tr>`;
                });
                document.getElementById('gradesTable').innerHTML = html || '<tr><td colspan="6" style="text-align:center;padding:2rem;">No grades available</td></tr>';
            }
        }
        loadGrades();
    </script>
    <script>
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
</body>
</html>
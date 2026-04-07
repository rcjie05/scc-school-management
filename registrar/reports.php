<?php
require_once '../php/config.php';
requireRole('registrar');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="apple-touch-icon" href="../images/logo2.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Registrar Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/themes.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Sidebar -->
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
                    <a href="reports.php" class="nav-item active"><span class="nav-icon">📈</span><span>Reports</span></a>
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
                    <a href="profile.php" class="nav-item"><span class="nav-icon">👤</span><span>My Profile</span></a>
                    <a href="../php/logout.php" class="nav-item"><span class="nav-icon">🚪</span><span>Logout</span></a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="page-header">
                <div class="header-title">
                    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><span></span><span></span><span></span></button>
                    <h1>Reports & Analytics</h1>
                    <p class="page-subtitle">Generate and view various reports</p>
                </div>
            </header>
            
            <!-- Report Options -->
            <div class="content-grid" style="margin-bottom: 2rem;">
                <div class="content-card" style="cursor: pointer;" onclick="generateReport('enrollment')">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                        <h3>Enrollment Report</h3>
                        <p style="color: var(--text-secondary); font-size: 0.875rem;">View enrollment statistics by course, year level, and semester</p>
                    </div>
                </div>
                
                <div class="content-card" style="cursor: pointer;" onclick="generateReport('grades')">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🎓</div>
                        <h3>Grades Summary</h3>
                        <p style="color: var(--text-secondary); font-size: 0.875rem;">View grade distribution and performance statistics</p>
                    </div>
                </div>
                
                <div class="content-card" style="cursor: pointer;" onclick="generateReport('applications')">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                        <h3>Applications Report</h3>
                        <p style="color: var(--text-secondary); font-size: 0.875rem;">View application statistics and approval rates</p>
                    </div>
                </div>
            </div>
            
            <!-- Report Display Area -->
            <div class="content-card" id="reportContainer" style="display: none;">
                <div class="card-header">
                    <h2 class="card-title" id="reportTitle">Report</h2>
                    <button class="btn btn-primary" onclick="exportReport()">📥 Export PDF</button>
                </div>
                <div id="reportContent">
                    <!-- Report content will be loaded here -->
                </div>
            </div>
        </main>
    </div>

    <script>
        async function generateReport(type) {
            document.getElementById('reportContainer').style.display = 'block';
            
            let reportTitle = '';
            let reportContent = 'Loading report...';
            
            switch(type) {
                case 'enrollment':
                    reportTitle = 'Enrollment Report';
                    await loadEnrollmentReport();
                    break;
                case 'grades':
                    reportTitle = 'Grades Summary Report';
                    await loadGradesReport();
                    break;
                case 'applications':
                    reportTitle = 'Applications Report';
                    await loadApplicationsReport();
                    break;
            }
            
            document.getElementById('reportTitle').textContent = reportTitle;
            
            // Scroll to report
            document.getElementById('reportContainer').scrollIntoView({ behavior: 'smooth' });
        }

        async function loadEnrollmentReport() {
            const response = await fetch('../php/api/registrar/get_enrollment_report.php');
            const data = await response.json();
            
            if (data.success) {
                let html = `
                    <div style="padding: 2rem;">
                        <h3>Enrollment Statistics</h3>
                        <div class="stats-grid" style="margin: 2rem 0;">
                            <div class="stat-card purple">
                                <div class="stat-label">Total Students</div>
                                <div class="stat-value">${data.stats.total_students}</div>
                            </div>
                            <div class="stat-card green">
                                <div class="stat-label">Fully Enrolled</div>
                                <div class="stat-value">${data.stats.fully_enrolled}</div>
                            </div>
                            <div class="stat-card yellow">
                                <div class="stat-label">Pending Enrollment</div>
                                <div class="stat-value">${data.stats.pending_enrollment}</div>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 2rem;">Enrollment by Course</h4>
                        <table class="data-table" style="margin-top: 1rem;">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Total Students</th>
                                    <th>1st Year</th>
                                    <th>2nd Year</th>
                                    <th>3rd Year</th>
                                    <th>4th Year</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.by_course.forEach(course => {
                    html += `
                        <tr>
                            <td><strong>${course.course}</strong></td>
                            <td>${course.total}</td>
                            <td>${course.year_1 || 0}</td>
                            <td>${course.year_2 || 0}</td>
                            <td>${course.year_3 || 0}</td>
                            <td>${course.year_4 || 0}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                document.getElementById('reportContent').innerHTML = html;
            } else {
                document.getElementById('reportContent').innerHTML = '<p style="text-align: center; padding: 2rem;">Error loading report</p>';
            }
        }

        async function loadGradesReport() {
            const response = await fetch('../php/api/registrar/get_grades_report.php');
            const data = await response.json();
            
            if (data.success) {
                let html = `
                    <div style="padding: 2rem;">
                        <h3>Grades Distribution</h3>
                        <div class="stats-grid" style="margin: 2rem 0;">
                            <div class="stat-card green">
                                <div class="stat-label">Passed</div>
                                <div class="stat-value">${data.stats.passed}</div>
                            </div>
                            <div class="stat-card pink">
                                <div class="stat-label">Failed</div>
                                <div class="stat-value">${data.stats.failed}</div>
                            </div>
                            <div class="stat-card purple">
                                <div class="stat-label">Incomplete</div>
                                <div class="stat-value">${data.stats.incomplete}</div>
                            </div>
                            <div class="stat-card yellow">
                                <div class="stat-label">Average Grade</div>
                                <div class="stat-value">${data.stats.average_grade}</div>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 2rem;">Performance by Course</h4>
                        <table class="data-table" style="margin-top: 1rem;">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Students</th>
                                    <th>Passed</th>
                                    <th>Failed</th>
                                    <th>Pass Rate</th>
                                    <th>Avg Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.by_course.forEach(course => {
                    const passRate = course.total > 0 ? ((course.passed / course.total) * 100).toFixed(1) : 0;
                    html += `
                        <tr>
                            <td><strong>${course.course}</strong></td>
                            <td>${course.total}</td>
                            <td>${course.passed}</td>
                            <td>${course.failed}</td>
                            <td>${passRate}%</td>
                            <td>${course.avg_grade}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                document.getElementById('reportContent').innerHTML = html;
            } else {
                document.getElementById('reportContent').innerHTML = '<p style="text-align: center; padding: 2rem;">Error loading report</p>';
            }
        }

        async function loadApplicationsReport() {
            const response = await fetch('../php/api/registrar/get_applications_report.php');
            const data = await response.json();
            
            if (data.success) {
                let html = `
                    <div style="padding: 2rem;">
                        <h3>Applications Statistics</h3>
                        <div class="stats-grid" style="margin: 2rem 0;">
                            <div class="stat-card purple">
                                <div class="stat-label">Total Applications</div>
                                <div class="stat-value">${data.stats.total}</div>
                            </div>
                            <div class="stat-card green">
                                <div class="stat-label">Approved</div>
                                <div class="stat-value">${data.stats.approved}</div>
                            </div>
                            <div class="stat-card pink">
                                <div class="stat-label">Rejected</div>
                                <div class="stat-value">${data.stats.rejected}</div>
                            </div>
                            <div class="stat-card yellow">
                                <div class="stat-label">Pending</div>
                                <div class="stat-value">${data.stats.pending}</div>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 2rem;">Applications by Course</h4>
                        <table class="data-table" style="margin-top: 1rem;">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Total</th>
                                    <th>Approved</th>
                                    <th>Rejected</th>
                                    <th>Pending</th>
                                    <th>Approval Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.by_course.forEach(course => {
                    const approvalRate = course.total > 0 ? ((course.approved / course.total) * 100).toFixed(1) : 0;
                    html += `
                        <tr>
                            <td><strong>${course.course}</strong></td>
                            <td>${course.total}</td>
                            <td>${course.approved}</td>
                            <td>${course.rejected}</td>
                            <td>${course.pending}</td>
                            <td>${approvalRate}%</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                document.getElementById('reportContent').innerHTML = html;
            } else {
                document.getElementById('reportContent').innerHTML = '<p style="text-align: center; padding: 2rem;">Error loading report</p>';
            }
        }

        function exportReport() {
            alert('Export to PDF functionality will be implemented soon.');
            // This would typically call a server-side PDF generation API
        }
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
  <a href="applications.php" class="mobile-nav-item"><span class="mobile-nav-icon">📋</span>Apps</a>
  <a href="manage_loads.php" class="mobile-nav-item"><span class="mobile-nav-icon">📚</span>Loads</a>
  <a href="grades.php" class="mobile-nav-item"><span class="mobile-nav-icon">🎓</span>Grades</a>
  <a href="reports.php" class="mobile-nav-item active"><span class="mobile-nav-icon">📈</span>Reports</a>
</nav>
    <script src="../js/session-monitor.js"></script>
</body>
</html>

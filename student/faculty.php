<?php
require_once '../php/config.php';
requireRole('student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="apple-touch-icon" href="../images/logo2.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Directory - Student Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/themes.css">
    <style>
        .filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-bar input,
        .filter-bar select {
            padding: 0.65rem 1rem;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            font-family: inherit;
            background: white;
            transition: border-color 0.2s;
        }
        .filter-bar input { flex: 1; min-width: 200px; }
        .filter-bar input:focus,
        .filter-bar select:focus { outline: none; border-color: var(--primary-purple); }

        .faculty-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
        }
        .faculty-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1.5px solid var(--border-color);
            transition: box-shadow 0.2s, border-color 0.2s, transform 0.15s;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            cursor: pointer;
        }
        .faculty-card:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--primary-purple);
            transform: translateY(-2px);
        }
        .faculty-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-pink));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }
        .faculty-header {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .faculty-name {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-primary);
            margin: 0;
        }
        .faculty-role {
            font-size: 0.8rem;
            color: var(--text-secondary);
            text-transform: capitalize;
        }
        .faculty-info {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .faculty-info span {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .dept-badge {
            display: inline-block;
            padding: 0.2rem 0.65rem;
            background: rgba(91,78,155,0.1);
            color: var(--primary-purple);
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .view-profile-hint {
            font-size: 0.78rem;
            color: var(--primary-purple);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            margin-top: 0.2rem;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
            grid-column: 1 / -1;
        }
        .empty-state span { font-size: 3rem; display: block; margin-bottom: 1rem; }
        .count-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        /* ---- Profile Modal ---- */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: white;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 580px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            animation: modalIn 0.22s ease;
        }
        @keyframes modalIn {
            from { transform: translateY(18px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        .modal-header {
            padding: 1.75rem 1.75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            border-bottom: 1.5px solid var(--border-color);
            position: relative;
        }
        .modal-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-pink));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.9rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--background-main);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            transition: background 0.2s;
        }
        .modal-close:hover { background: var(--border-color); }
        .modal-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.25rem;
        }
        .modal-role-line {
            font-size: 0.82rem;
            color: var(--text-secondary);
            text-transform: capitalize;
        }
        .modal-body {
            padding: 1.5rem 1.75rem 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 1.4rem;
        }
        .modal-section-title {
            font-size: 0.73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--primary-purple);
            margin-bottom: 0.65rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .modal-info-row {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
            padding: 0.22rem 0;
        }
        .modal-info-row .icon { flex-shrink: 0; width: 1.1rem; text-align: center; }
        .specialty-item {
            background: var(--background-main);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0.65rem 0.9rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.45rem;
        }
        .subj-code { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
        .subj-name { font-size: 0.78rem; color: var(--text-secondary); margin-top: 0.1rem; }
        .primary-badge {
            background: rgba(91,78,155,0.12);
            color: var(--primary-purple);
            font-size: 0.69rem;
            font-weight: 700;
            padding: 0.18rem 0.55rem;
            border-radius: 999px;
            white-space: nowrap;
        }
        .prof-level {
            font-size: 0.75rem;
            color: var(--text-secondary);
            white-space: nowrap;
        }
        .sched-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }
        .sched-table th {
            text-align: left;
            padding: 0.4rem 0.55rem;
            color: var(--text-secondary);
            font-weight: 600;
            border-bottom: 1.5px solid var(--border-color);
        }
        .sched-table td {
            padding: 0.45rem 0.55rem;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }
        .modal-empty {
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-style: italic;
        }
        .modal-loading {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
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
                    <a href="floorplan.php" class="nav-item"><span class="nav-icon">🗺️</span><span>Floor Plan</span></a>
                    <a href="faculty.php" class="nav-item active"><span class="nav-icon">👨‍🏫</span><span>Faculty Directory</span></a>
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
                    <h1>Faculty Directory</h1>
                    <p class="page-subtitle">Browse teachers and staff — click a card to view their profile</p>
                </div>
            </header>

            <div class="content-card">
                <div class="card-header">
                    <div class="filter-bar">
                        <input type="text" id="searchInput" placeholder="🔍 Search by name or email..." oninput="filterFaculty()">
                        <select id="deptFilter" onchange="filterFaculty()">
                            <option value="">All Departments</option>
                        </select>
                    </div>
                </div>
                <div class="count-label" id="countLabel" style="padding: 0 1rem;"></div>
                <div class="faculty-grid" id="facultyGrid" style="padding: 1rem;">
                    <p style="text-align:center; color:var(--text-secondary);">Loading...</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Profile Modal -->
    <div class="modal-overlay" id="profileModal" onclick="handleOverlayClick(event)">
        <div class="modal-box">
            <div id="modalContent">
                <div class="modal-loading">Loading profile...</div>
            </div>
        </div>
    </div>

    <script>
        let allFaculty = [];

        async function loadFaculty() {
            const res = await fetch('../php/api/student/get_faculty.php');
            const data = await res.json();
            if (!data.success) return;

            allFaculty = data.faculty;

            // Populate department filter
            const deptFilter = document.getElementById('deptFilter');
            data.departments.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d; opt.textContent = d;
                deptFilter.appendChild(opt);
            });

            renderFaculty(allFaculty);
        }

        function filterFaculty() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const dept = document.getElementById('deptFilter').value;
            const filtered = allFaculty.filter(f => {
                const matchSearch = !search ||
                    (f.name || '').toLowerCase().includes(search) ||
                    (f.email || '').toLowerCase().includes(search);
                const matchDept = !dept || f.department === dept;
                return matchSearch && matchDept;
            });
            renderFaculty(filtered);
        }

        function makeAvatar(url, name, cssClass) {
            const initials = (name || '?').split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
            if (url) {
                return `<div class="${cssClass}" style="background:none;padding:0;overflow:hidden;">
                    <img src="${url}" alt="${esc(name)}"
                         style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;"
                         onerror="this.style.display='none';this.parentNode.style.background='linear-gradient(135deg,var(--primary-purple),var(--secondary-pink))';this.parentNode.innerHTML='${initials}';">
                </div>`;
            }
            return `<div class="${cssClass}">${initials}</div>`;
        }

        function renderFaculty(list) {
            const grid = document.getElementById('facultyGrid');
            const count = document.getElementById('countLabel');
            count.textContent = `Showing ${list.length} faculty member${list.length !== 1 ? 's' : ''}`;

            if (list.length === 0) {
                grid.innerHTML = `<div class="empty-state"><span>👨‍🏫</span>No faculty members found.</div>`;
                return;
            }

            grid.innerHTML = list.map(f => {
                const dept = f.department ? `<span class="dept-badge">${esc(f.department)}</span>` : '';
                const email = f.email ? `<span>✉️ ${esc(f.email)}</span>` : '';
                const office = f.office_location ? `<span>📍 ${esc(f.office_location)}</span>` : '';
                const hours = f.office_hours ? `<span>🕐 ${esc(f.office_hours)}</span>` : '';
                return `
                    <div class="faculty-card" onclick="openProfile(${f.id})">
                        <div class="faculty-header">
                            ${makeAvatar(f.avatar_url, f.name, 'faculty-avatar')}
                            <div>
                                <p class="faculty-name">${esc(f.name || 'Unknown')}</p>
                                <span class="faculty-role">${esc(f.role || '')}</span>
                            </div>
                        </div>
                        ${dept}
                        <div class="faculty-info">
                            ${email}${office}${hours}
                        </div>
                        <div class="view-profile-hint">👁️ View Profile</div>
                    </div>`;
            }).join('');
        }

        function esc(str) {
            return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }

        async function openProfile(id) {
            document.getElementById('profileModal').classList.add('active');
            document.getElementById('modalContent').innerHTML = '<div class="modal-loading">⏳ Loading profile...</div>';

            try {
                const res = await fetch(`../php/api/student/get_faculty_profile.php?id=${id}`);
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Failed to load');
                renderModal(data);
            } catch(e) {
                document.getElementById('modalContent').innerHTML = `<div class="modal-loading">❌ Could not load profile.</div>`;
            }
        }

        function renderModal(data) {
            const f = data.faculty;
            const roleIcon = f.role === 'teacher' ? '👨‍🏫' : '🗂️';

            // Contact info rows
            let contactRows = '';
            if (f.email) contactRows += `<div class="modal-info-row"><span class="icon">✉️</span><span>${esc(f.email)}</span></div>`;
            if (f.department) contactRows += `<div class="modal-info-row"><span class="icon">🏢</span><span>${esc(f.department)}</span></div>`;
            if (f.office_location) contactRows += `<div class="modal-info-row"><span class="icon">📍</span><span>${esc(f.office_location)}</span></div>`;
            if (f.office_hours) contactRows += `<div class="modal-info-row"><span class="icon">🕐</span><span>Office Hours: ${esc(f.office_hours)}</span></div>`;
            if (!contactRows) contactRows = '<p class="modal-empty">No contact info available.</p>';

            // Specialties (teachers only)
            let specialtiesHtml = '';
            if (f.role === 'teacher') {
                let specItems = '';
                if (data.specialties && data.specialties.length > 0) {
                    specItems = data.specialties.map(s => `
                        <div class="specialty-item">
                            <div>
                                <div class="subj-code">${esc(s.subject_code)} — ${esc(s.subject_name)}</div>
                                <div class="subj-name">${s.units ? s.units + ' units' : ''}${s.course ? ' · ' + esc(s.course) : ''}${s.year_level ? ' Yr ' + esc(s.year_level) : ''}</div>
                            </div>
                            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.25rem;">
                                ${s.is_primary == 1 ? '<span class="primary-badge">Primary</span>' : ''}
                                ${s.proficiency_level ? `<span class="prof-level">${esc(s.proficiency_level)}</span>` : ''}
                            </div>
                        </div>`).join('');
                } else {
                    specItems = '<p class="modal-empty">No specialties listed.</p>';
                }
                specialtiesHtml = `
                    <div>
                        <div class="modal-section-title">📖 Subject Specialties</div>
                        ${specItems}
                    </div>`;
            }

            // Classes/Schedule
            let schedHtml = '';
            if (data.classes && data.classes.length > 0) {
                const rows = data.classes.map(c => `
                    <tr>
                        <td>${esc(c.day_of_week)}</td>
                        <td>${esc(c.start_time)} – ${esc(c.end_time)}</td>
                        <td>${esc(c.subject_code)}<br><small style="color:var(--text-secondary)">${esc(c.subject_name)}</small></td>
                        <td>${esc(c.section_name)}</td>
                        <td>${esc(c.room || '—')}</td>
                    </tr>`).join('');
                schedHtml = `
                    <div>
                        <div class="modal-section-title">📅 Current Classes</div>
                        <div style="overflow-x:auto;">
                        <table class="sched-table">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Subject</th>
                                    <th>Section</th>
                                    <th>Room</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                        </div>
                    </div>`;
            } else {
                schedHtml = `
                    <div>
                        <div class="modal-section-title">📅 Current Classes</div>
                        <p class="modal-empty">No class schedule available.</p>
                    </div>`;
            }

            document.getElementById('modalContent').innerHTML = `
                <div class="modal-header">
                    ${makeAvatar(f.avatar_url, f.name, 'modal-avatar')}
                    <div>
                        <div class="modal-name">${esc(f.name || 'Unknown')}</div>
                        <div class="modal-role-line">${roleIcon} ${esc(f.role)}</div>
                    </div>
                    <button class="modal-close" onclick="closeProfile()">✕</button>
                </div>
                <div class="modal-body">
                    <div>
                        <div class="modal-section-title">📋 Contact Information</div>
                        ${contactRows}
                    </div>
                    ${specialtiesHtml}
                    ${schedHtml}
                </div>`;
        }

        function closeProfile() {
            document.getElementById('profileModal').classList.remove('active');
        }

        function handleOverlayClick(e) {
            if (e.target === document.getElementById('profileModal')) closeProfile();
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeProfile();
        });

        loadFaculty();
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
  <a href="schedule.php" class="mobile-nav-item"><span class="mobile-nav-icon">📅</span>Schedule</a>
  <a href="grades.php" class="mobile-nav-item"><span class="mobile-nav-icon">📊</span>Grades</a>
  <a href="profile.php" class="mobile-nav-item"><span class="mobile-nav-icon">👤</span>Profile</a>
</nav>
    <script src="../js/session-monitor.js"></script>
</body>
</html>

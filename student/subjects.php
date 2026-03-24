<?php
require_once '../php/config.php';
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
    <title>Study Load - Saint Cecilia College</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/themes.css">
    <style>
        .section-banner {
            background: linear-gradient(135deg, var(--background-sidebar), var(--primary-purple));
            border-radius: var(--radius-lg);
            padding: 1.5rem 2rem;
            color: var(--text-white);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .section-banner .sb-icon { font-size: 2.5rem; }
        .section-banner .sb-name { font-size: 1.4rem; font-weight: 800; }
        .section-banner .sb-meta { font-size: .88rem; opacity: .85; margin-top: .3rem; }
        .section-banner.no-section { background: linear-gradient(135deg, var(--text-secondary), var(--text-light)); }

        /* Tabs */
        .tab-bar { display: flex; gap: 0; border-bottom: 2px solid var(--border-color); margin-bottom: 1.5rem; }
        .tab-btn {
            padding: .65rem 1.4rem;
            font-size: .92rem;
            font-weight: 600;
            border: none;
            background: none;
            cursor: pointer;
            color: var(--text-secondary);
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all .2s;
            position: relative;
        }
        .tab-btn.active { color: var(--primary-purple); border-bottom-color: var(--primary-purple); }
        .tab-btn:hover:not(.active) { color: var(--text-primary); background: var(--background-main); }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
        .badge-count {
            display: inline-block;
            background: var(--secondary-pink);
            color: var(--text-white);
            font-size: .68rem;
            font-weight: 700;
            padding: .1rem .42rem;
            border-radius: 10px;
            margin-left: .35rem;
            vertical-align: middle;
        }

        /* Request status badges */
        .req-pending  { background: rgba(212,169,106,0.2); color: var(--text-primary); }
        .req-approved { background: rgba(90,158,138,0.2); color: var(--secondary-green); }
        .req-rejected { background: rgba(184,92,92,0.15); color: var(--secondary-pink); }
        .req-add  { background: rgba(61,107,159,0.15); color: var(--primary-purple-dark); }
        .req-drop { background: rgba(184,92,92,0.12); color: var(--secondary-pink); }
        .req-badge { padding: .2rem .6rem; border-radius: 1rem; font-size: .75rem; font-weight: 700; }

        /* Subject row actions */
        .drop-btn {
            padding: .25rem .7rem;
            font-size: .78rem;
            border: 1.5px solid var(--secondary-pink);
            background: transparent;
            color: var(--secondary-pink);
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            transition: all .2s;
        }
        .drop-btn:hover { background: var(--secondary-pink); color: var(--text-white); }
        .pending-badge {
            display: inline-flex; align-items: center; gap: .35rem;
            font-size: .72rem; font-weight: 700;
            color: #92400e;
            background: #fef3c7;
            border: 1.5px solid #fbbf24;
            padding: .25rem .65rem;
            border-radius: 999px;
            white-space: nowrap;
        }
        .pending-badge::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #f59e0b;
            display: inline-block;
            animation: pendingPulse 1.4s ease-in-out infinite;
        }
        @keyframes pendingPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: .4; transform: scale(.7); }
        }

        /* Add subject card */
        .add-subject-card {
            padding: 1rem 1.25rem;
            background: var(--background-main);
            border-radius: var(--radius-md);
            border: 1.5px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: .5rem;
            transition: border-color .2s;
        }
        .add-subject-card:hover { border-color: var(--primary-purple); }
        .add-btn {
            padding: .3rem .85rem;
            font-size: .78rem;
            background: var(--primary-purple);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .add-btn:hover { opacity: .85; }
        .add-btn:disabled { opacity: .5; cursor: not-allowed; }

        /* Modal */
        .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; }
        .modal.active { display:flex; }
        .modal-content { background:var(--background-card); padding:2rem; border-radius:var(--radius-lg); width:90%; max-width:480px; }
        .modal-title { font-size:1.15rem; font-weight:700; margin-bottom:1rem; }
        .modal-subject { background:var(--background-main); border-radius:var(--radius-md); padding:.75rem 1rem; margin-bottom:1rem; }
        .modal-subject .code { font-weight:700; color:var(--primary-purple); }
        .modal-subject .name { font-size:.9rem; color:var(--text-secondary); margin-top:.2rem; }
        .reason-label { font-weight:600; font-size:.88rem; margin-bottom:.4rem; display:block; }
        .reason-input { width:100%; padding:.65rem .85rem; border:1.5px solid var(--border-color); border-radius:var(--radius-md); font-size:.92rem; resize:vertical; min-height:80px; font-family:inherit; box-sizing:border-box; }
        .reason-input:focus { outline:none; border-color:var(--primary-purple); }
        .modal-actions { display:flex; gap:.75rem; margin-top:1.25rem; }
        .modal-actions button { flex:1; padding:.65rem; border:none; border-radius:var(--radius-md); font-weight:700; cursor:pointer; font-size:.92rem; }
        .btn-cancel { background:var(--background-main); color:var(--text-primary); }
        .btn-submit-add  { background:var(--primary-purple); color:white; }
        .btn-submit-drop { background:var(--secondary-pink); color:var(--text-white); }

        .req-row { padding:.75rem 1rem; border-radius:var(--radius-md); background:var(--background-main); margin-bottom:.5rem; }
        .req-row-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem; margin-bottom:.3rem; }
        .req-note { font-size:.8rem; color:var(--text-secondary); margin-top:.25rem; padding:.35rem .6rem; background:var(--background-main); border-radius:var(--radius-sm); }
        /* Toast notification */
        #scc-toast {
            position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999;
            display: flex; align-items: flex-start; gap: .85rem;
            background: var(--background-card); border-radius: var(--radius-lg);
            box-shadow: 0 8px 32px rgba(0,0,0,0.15); padding: 1rem 1.25rem;
            max-width: 360px; min-width: 260px;
            transform: translateX(120%); transition: transform .35s cubic-bezier(.4,0,.2,1);
            border-left: 4px solid var(--primary-purple);
        }
        #scc-toast.show { transform: translateX(0); }
        #scc-toast.toast-success { border-left-color: #22c55e; }
        #scc-toast.toast-error   { border-left-color: #ef4444; }
        #scc-toast.toast-warning { border-left-color: #f59e0b; }
        .toast-icon { font-size: 1.4rem; flex-shrink: 0; margin-top: .05rem; }
        .toast-body { flex: 1; }
        .toast-title { font-weight: 700; font-size: .92rem; color: var(--text-primary); margin-bottom: .15rem; }
        .toast-msg   { font-size: .83rem; color: var(--text-secondary); line-height: 1.45; }
        .toast-close { background: none; border: none; font-size: 1.1rem; color: var(--text-secondary); cursor: pointer; padding: 0; flex-shrink: 0; }
        .toast-close:hover { color: var(--text-primary); }
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
                    <a href="subjects.php" class="nav-item active"><span class="nav-icon">📚</span><span>Study Load</span></a>
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
                    <h1>My Study Load</h1>
                <p class="page-subtitle">Your enrolled subjects and add/drop requests</p>
            </div>
        </header>

        <!-- Section Banner -->
        <div id="sectionBanner"></div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card purple">
                <div class="stat-icon">📚</div>
                <div class="stat-label">Total Subjects</div>
                <div class="stat-value" id="totalSubjects">0</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon">⚡</div>
                <div class="stat-label">Total Units</div>
                <div class="stat-value" id="totalUnits">0</div>
            </div>
            <div class="stat-card yellow">
                <div class="stat-icon">📊</div>
                <div class="stat-label">Load Status</div>
                <div class="stat-value" id="loadStatus" style="font-size:1.1rem;">—</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tab-bar" style="margin-top:1.5rem;">
            <button class="tab-btn active" onclick="switchTab('enrolled')">📋 Enrolled Subjects</button>
            <button class="tab-btn" onclick="switchTab('add')">➕ Add Subject</button>
            <button class="tab-btn" id="requestsTab" onclick="switchTab('requests')">📝 My Requests <span id="pendingBadge" class="badge-count" style="display:none;"></span></button>
        </div>

        <!-- Tab: Enrolled Subjects -->
        <div class="tab-panel active" id="panel-enrolled">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Enrolled Subjects</h2>
                    <p style="font-size:.82rem;color:var(--text-secondary);margin-top:.25rem;">Click "Drop" to submit a drop request for any subject.</p>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Subject Name</th>
                                <th>Units</th>
                                <th>Teacher</th>
                                <th>Schedule</th>
                                <th>Room</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="subjectsTable">
                            <tr><td colspan="7" style="text-align:center;padding:2rem;">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Add Subject -->
        <div class="tab-panel" id="panel-add">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Available Subjects to Add</h2>
                    <p style="font-size:.82rem;color:var(--text-secondary);margin-top:.25rem;">Subjects in your section not yet in your study load. Requests are reviewed by the registrar.</p>
                </div>
                <div id="availableSubjects" style="padding:1rem;">Loading…</div>
            </div>
        </div>

        <!-- Tab: My Requests -->
        <div class="tab-panel" id="panel-requests">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">My Add/Drop Requests</h2>
                </div>
                <div id="requestsList" style="padding:1rem;">Loading…</div>
            </div>
        </div>
    </main>
</div>

<!-- Reason Modal -->
<!-- Toast Notification -->
<div id="scc-toast">
    <div class="toast-icon" id="toast-icon">ℹ️</div>
    <div class="toast-body">
        <div class="toast-title" id="toast-title">Notice</div>
        <div class="toast-msg"   id="toast-msg"></div>
    </div>
    <button class="toast-close" onclick="hideToast()">✕</button>
</div>

<div class="modal" id="reasonModal">
    <div class="modal-content">
        <div class="modal-title" id="modalTitle">Submit Request</div>
        <div class="modal-subject">
            <div class="code" id="modalSubjectCode"></div>
            <div class="name" id="modalSubjectName"></div>
        </div>
        <label class="reason-label">Reason <span style="color:var(--secondary-pink);">*</span></label>
        <textarea class="reason-input" id="reasonInput" placeholder="Please explain your reason for this request…"></textarea>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <button id="submitBtn" onclick="submitRequest()">Submit</button>
        </div>
    </div>
</div>

<script>
let pendingRequest = null;  // { subject_id, request_type, subject_code, subject_name }
let pendingSubjectIds = new Set(); // subject IDs with pending drop requests

function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    event.currentTarget.classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
    if (tab === 'requests') loadRequests();
    if (tab === 'add') loadAvailable();
}

// ── Load Enrolled Subjects ──────────────────────────────
async function loadStudyLoad() {
    try {
        const res  = await fetch('../php/api/student/get_study_load.php');
        const data = await res.json();
        if (!data.success) return;

        // Section banner
        const banner = document.getElementById('sectionBanner');
        if (data.section) {
            const s = data.section;
            banner.innerHTML = `
                <div class="section-banner">
                    <div class="sb-icon">📁</div>
                    <div>
                        <div class="sb-name">${s.section_name} <span style="opacity:.7;font-size:.9rem;">(${s.section_code})</span></div>
                        <div class="sb-meta">${s.course || ''} · ${s.year_level || ''} · ${s.semester || ''} · ${s.school_year || ''}</div>
                    </div>
                </div>`;
        } else {
            banner.innerHTML = `
                <div class="section-banner no-section">
                    <div class="sb-icon">📁</div>
                    <div>
                        <div class="sb-name">No Section Assigned</div>
                        <div class="sb-meta">Please contact the registrar to have a section assigned to you.</div>
                    </div>
                </div>`;
        }

        document.getElementById('totalSubjects').textContent = data.stats.total_subjects;
        document.getElementById('totalUnits').textContent    = data.stats.total_units;
        document.getElementById('loadStatus').textContent    = data.stats.status;

        // Load pending drop requests first to mark them
        await loadPendingDropIds();

        if (!data.subjects.length) {
            document.getElementById('subjectsTable').innerHTML =
                '<tr><td colspan="7" style="text-align:center;padding:2rem;">No subjects assigned yet</td></tr>';
            return;
        }

        document.getElementById('subjectsTable').innerHTML = data.subjects.map(s => {
            const hasPending = pendingSubjectIds.has(String(s.subject_id));
            const actionCell = hasPending
                ? `<span class="pending-badge">Drop Pending</span>`
                : `<button class="drop-btn" data-subject-id="${s.subject_id}" onclick="openModal(${s.subject_id},'drop','${esc(s.subject_code)}','${esc(s.subject_name)}')">Drop</button>`;
            return `
            <tr>
                <td><strong>${s.subject_code}</strong></td>
                <td>${s.subject_name}</td>
                <td>${s.units}</td>
                <td>${s.teacher || '<em style="color:var(--text-light)">TBA</em>'}</td>
                <td>${s.schedule || '<em style="color:var(--text-light)">TBA</em>'}</td>
                <td>${s.room || '<em style="color:var(--text-light)">TBA</em>'}</td>
                <td>${actionCell}</td>
            </tr>`;
        }).join('');

    } catch (err) { console.error(err); }
}

async function loadPendingDropIds() {
    try {
        const res  = await fetch('../php/api/student/get_add_drop_requests.php');
        const data = await res.json();
        if (!data.success) return;
        pendingSubjectIds.clear();
        let pendingCount = 0;
        data.requests.forEach(r => {
            if (r.status === 'pending') {
                pendingCount++;
                // Store as both string and int to avoid type mismatch
                if (r.request_type === 'drop' && r.subject_id != null) {
                    pendingSubjectIds.add(String(r.subject_id));
                }
            }
        });
        const badge = document.getElementById('pendingBadge');
        if (pendingCount > 0) { badge.style.display = ''; badge.textContent = pendingCount; }
        else badge.style.display = 'none';
    } catch(e) { console.error('loadPendingDropIds error:', e); }
}

// ── Load Available Subjects ────────────────────────────
async function loadAvailable() {
    const container = document.getElementById('availableSubjects');
    container.innerHTML = 'Loading…';
    try {
        const res  = await fetch('../php/api/student/get_available_subjects.php');
        const data = await res.json();
        if (!data.subjects || data.subjects.length === 0) {
            container.innerHTML = '<p style="text-align:center;color:var(--text-secondary);padding:2rem;">No additional subjects available in your section, or all section subjects are already in your load.</p>';
            return;
        }
        container.innerHTML = data.subjects.map(s => `
            <div class="add-subject-card">
                <div>
                    <strong>${s.subject_code}</strong> — ${s.subject_name}
                    <div style="font-size:.8rem;color:var(--text-secondary);margin-top:.2rem;">
                        ⚡ ${s.units} units
                        ${s.teacher ? ' · 👤 ' + s.teacher : ''}
                        ${s.schedule ? ' · 📅 ' + s.schedule : ''}
                    </div>
                </div>
                <button class="add-btn" onclick="openModal(${s.id},'add','${esc(s.subject_code)}','${esc(s.subject_name)}')">➕ Add</button>
            </div>`).join('');
    } catch(e) {
        container.innerHTML = '<p style="text-align:center;color:var(--text-secondary);padding:2rem;">Failed to load subjects.</p>';
    }
}

// ── Load My Requests ───────────────────────────────────
async function loadRequests() {
    const container = document.getElementById('requestsList');
    container.innerHTML = 'Loading…';
    try {
        const res  = await fetch('../php/api/student/get_add_drop_requests.php');
        const data = await res.json();

        if (!data.requests || data.requests.length === 0) {
            container.innerHTML = '<p style="text-align:center;color:var(--text-secondary);padding:2rem;">No requests yet. Use the tabs above to add or drop subjects.</p>';
            return;
        }

        container.innerHTML = data.requests.map(r => `
            <div class="req-row">
                <div class="req-row-header">
                    <div>
                        <strong>${r.subject_code}</strong> — ${r.subject_name}
                        <span class="req-badge req-${r.request_type}" style="margin-left:.4rem;">${r.request_type.toUpperCase()}</span>
                    </div>
                    <div style="display:flex;gap:.5rem;align-items:center;">
                        <span class="req-badge req-${r.status}">${r.status.charAt(0).toUpperCase()+r.status.slice(1)}</span>
                        <span style="font-size:.75rem;color:var(--text-secondary);">${r.created_at}</span>
                    </div>
                </div>
                <div style="font-size:.82rem;color:var(--text-secondary);">Reason: ${r.reason}</div>
                ${r.registrar_note ? `<div class="req-note">📝 Registrar note: ${r.registrar_note}</div>` : ''}
                ${r.reviewed_at ? `<div style="font-size:.75rem;color:var(--text-secondary);margin-top:.25rem;">Reviewed ${r.reviewed_at}${r.reviewed_by_name ? ' by ' + r.reviewed_by_name : ''}</div>` : ''}
            </div>`).join('');

    } catch(e) {
        container.innerHTML = '<p style="text-align:center;color:var(--text-secondary);padding:2rem;">Failed to load requests.</p>';
    }
}

// ── Modal ──────────────────────────────────────────────
function openModal(subject_id, request_type, subject_code, subject_name) {
    pendingRequest = { subject_id, request_type, subject_code, subject_name };
    document.getElementById('modalTitle').textContent   = request_type === 'add' ? '➕ Request to Add Subject' : '🗑️ Request to Drop Subject';
    document.getElementById('modalSubjectCode').textContent = subject_code;
    document.getElementById('modalSubjectName').textContent = subject_name;
    document.getElementById('reasonInput').value = '';
    const btn = document.getElementById('submitBtn');
    btn.textContent = request_type === 'add' ? 'Submit Add Request' : 'Submit Drop Request';
    btn.className   = request_type === 'add' ? 'btn-submit-add' : 'btn-submit-drop';
    document.getElementById('reasonModal').classList.add('active');
    document.getElementById('reasonInput').focus();
}

function closeModal() {
    document.getElementById('reasonModal').classList.remove('active');
    pendingRequest = null;
}

async function submitRequest() {
    if (!pendingRequest) return;
    const reason = document.getElementById('reasonInput').value.trim();
    if (!reason) { showToast('Please enter a reason before submitting.', 'warning'); return; }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Submitting…';

    try {
        const res  = await fetch('../php/api/student/submit_add_drop.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ subject_id: pendingRequest.subject_id, request_type: pendingRequest.request_type, reason })
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            // Instantly swap the Drop button to pending badge without reload
            const subjectId = pendingRequest.subject_id;
            if (pendingRequest.request_type === 'drop') {
                const dropBtn = document.querySelector(`.drop-btn[data-subject-id="${subjectId}"]`);
                if (dropBtn) {
                    const badge = document.createElement('span');
                    badge.className = 'pending-badge';
                    badge.textContent = 'Drop Pending';
                    dropBtn.replaceWith(badge);
                }
                pendingSubjectIds.add(String(subjectId));
            }
            closeModal();
            loadAvailable();
            // Update the pending count badge on the tab
            await loadPendingDropIds();
        }
    } catch(e) {
        showToast('Failed to submit request. Please try again.', 'error');
    } finally {
        btn.disabled = false;
    }
}

function esc(str) { return (str || '').replace(/'/g, "\'").replace(/"/g, '&quot;'); }

let _toastTimer;
function showToast(msg, type = 'info') {
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const titles = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Notice' };
    const el = document.getElementById('scc-toast');
    document.getElementById('toast-icon').textContent  = icons[type]  || icons.info;
    document.getElementById('toast-title').textContent = titles[type] || titles.info;
    document.getElementById('toast-msg').textContent   = msg;
    el.className = `show toast-${type}`;
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(hideToast, 4500);
}
function hideToast() {
    document.getElementById('scc-toast').classList.remove('show');
}

// Close modal on backdrop click
document.getElementById('reasonModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

loadStudyLoad();
</script>
    <script>
        (function() {
            var sidebar = document.querySelector('.sidebar');
            var activeItem = sidebar.querySelector('.nav-item.active');
            if (activeItem) {
                // Scroll only within the sidebar, not the whole page
                const itemTop = activeItem.offsetTop;
                const sidebarHeight = sidebar.clientHeight;
                const itemHeight = activeItem.clientHeight;
                sidebar.scrollTop = itemTop - (sidebarHeight / 2) + (itemHeight / 2);
            } else {
                var saved = sessionStorage.getItem('sidebarScroll');
                if (saved) sidebar.scrollTop = parseInt(saved);
            }
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
    <script src="/js/session-monitor.js"></script>
</body>
</html>
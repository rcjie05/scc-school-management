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
    <title>Help & FAQ - Student Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/themes.css">
    <style>
        .chat-wrapper {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 130px);
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        /* Quick topic chips */
        .topic-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f1f1;
            background: #fafafa;
        }
        .topic-chip {
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            border: 1.5px solid #e5e7eb;
            background: white;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .topic-chip:hover {
            border-color: var(--primary-purple);
            color: var(--primary-purple);
            background: #f5f3ff;
        }

        /* Messages area */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .chat-messages::-webkit-scrollbar { width: 4px; }
        .chat-messages::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

        /* Message bubbles */
        .msg {
            display: flex;
            gap: 0.65rem;
            align-items: flex-end;
            max-width: 78%;
        }
        .msg.user { align-self: flex-end; flex-direction: row-reverse; }
        .msg.bot  { align-self: flex-start; }

        .msg-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .msg.bot  .msg-avatar { background: linear-gradient(135deg, var(--primary-purple), var(--secondary-pink)); }
        .msg.user .msg-avatar { background: #e5e7eb; }

        .msg-bubble {
            padding: 0.75rem 1rem;
            border-radius: 18px;
            font-size: 0.9rem;
            line-height: 1.55;
            max-width: 100%;
        }
        .msg.bot  .msg-bubble {
            background: #f3f4f6;
            color: var(--text-primary);
            border-bottom-left-radius: 4px;
        }
        .msg.user .msg-bubble {
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-pink));
            color: white;
            border-bottom-right-radius: 4px;
        }
        .msg-bubble ul {
            margin: 0.5rem 0 0;
            padding-left: 1.25rem;
        }
        .msg-bubble ul li { margin-bottom: 0.25rem; }
        .msg-bubble a {
            color: inherit;
            text-decoration: underline;
            opacity: 0.85;
        }

        /* Typing indicator */
        .typing-indicator .msg-bubble {
            background: #f3f4f6;
            padding: 0.75rem 1rem;
        }
        .typing-dots { display: flex; gap: 4px; align-items: center; height: 18px; }
        .typing-dots span {
            width: 7px; height: 7px;
            background: #9ca3af;
            border-radius: 50%;
            animation: bounce 1.2s infinite ease-in-out;
        }
        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes bounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-6px); }
        }

        /* Input bar */
        .chat-input-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-top: 1px solid #f1f1f1;
            background: white;
        }
        .chat-input {
            flex: 1;
            padding: 0.7rem 1.1rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 999px;
            font-size: 0.925rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }
        .chat-input:focus { border-color: var(--primary-purple); }
        .chat-send-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-pink));
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: opacity 0.2s;
        }
        .chat-send-btn:hover { opacity: 0.88; }
        .chat-send-btn:disabled { opacity: 0.4; cursor: not-allowed; }

        /* Bot header bar */
        .bot-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f1f1;
        }
        .bot-header-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-pink));
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }
        .bot-header-info { flex: 1; }
        .bot-header-name { font-weight: 700; font-size: 0.95rem; color: var(--text-primary); }
        .bot-header-status { font-size: 0.78rem; color: #10b981; display: flex; align-items: center; gap: 4px; }
        .status-dot { width: 7px; height: 7px; background: #10b981; border-radius: 50%; }
        .btn-clear {
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            border: 1.5px solid #e5e7eb;
            background: white;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-clear:hover { border-color: #ef4444; color: #ef4444; }
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
                    <h1>Help & FAQ</h1>
                <p class="header-subtitle">Ask anything about enrollment, grades, schedules, and more</p>
            </div>
        </header>

        <div class="chat-wrapper">
            <!-- Bot header -->
            <div class="bot-header">
                <div class="bot-header-avatar">🤖</div>
                <div class="bot-header-info">
                    <div class="bot-header-name">OL Assistant</div>
                    <div class="bot-header-status"><span class="status-dot"></span> Online</div>
                </div>
                <button class="btn-clear" onclick="clearChat()">🗑 Clear chat</button>
            </div>

            <!-- Quick topic chips -->
            <div class="topic-chips">
                <button class="topic-chip" onclick="sendQuick('How do I enroll?')">📋 Enrollment</button>
                <button class="topic-chip" onclick="sendQuick('What are the enrollment requirements?')">📄 Requirements</button>
                <button class="topic-chip" onclick="sendQuick('How do I check my grades?')">🎓 Grades</button>
                <button class="topic-chip" onclick="sendQuick('How do I view my schedule?')">📅 Schedule</button>
                <button class="topic-chip" onclick="sendQuick('What are the school fees?')">💰 Tuition & Fees</button>
                <button class="topic-chip" onclick="sendQuick('How do I contact my professor?')">👨‍🏫 Faculty</button>
                <button class="topic-chip" onclick="sendQuick('What are the important school dates?')">📆 School Calendar</button>
                <button class="topic-chip" onclick="sendQuick('How do I submit feedback?')">💬 Feedback</button>
            </div>

            <!-- Messages -->
            <div class="chat-messages" id="chatMessages">
                <!-- Welcome message -->
                <div class="msg bot">
                    <div class="msg-avatar">🤖</div>
                    <div class="msg-bubble">
                        Hi there! 👋 I'm the <strong>OL Assistant</strong>, your school help guide.<br><br>
                        I can answer questions about enrollment, grades, schedules, school fees, and more. Pick a topic above or just type your question below!
                    </div>
                </div>
            </div>

            <!-- Input bar -->
            <div class="chat-input-bar">
                <input type="text" class="chat-input" id="chatInput" placeholder="Ask me anything…" autocomplete="off" />
                <button class="chat-send-btn" id="sendBtn" onclick="sendMessage()">➤</button>
            </div>
        </div>
    </main>
</div>

<script>
// ─── Knowledge Base ───────────────────────────────────────────────────────────
const KB = [
    {
        patterns: ['enroll', 'enrollment', 'how to enroll', 'register', 'sign up', 'admission'],
        answer: `Here's how to enroll at Saint Cecilia College:
<ul>
<li><strong>Step 1 – Pre-enrollment:</strong> Fill out the online pre-enrollment form on the school website or visit the registrar's office.</li>
<li><strong>Step 2 – Submit requirements</strong> (see the Requirements topic).</li>
<li><strong>Step 3 – Assessment:</strong> Proceed to the cashier for tuition assessment.</li>
<li><strong>Step 4 – Payment:</strong> Pay the required fees (downpayment accepted).</li>
<li><strong>Step 5 – Confirmation:</strong> Receive your enrollment confirmation and student ID.</li>
</ul>
For assistance, visit the Registrar's Office (Room 101) or call <strong>(032) 555-0100</strong>.`
    },
    {
        patterns: ['requirement', 'documents', 'needed', 'bring', 'submit', 'papers'],
        answer: `The following documents are required for enrollment:
<ul>
<li>📄 Original and photocopy of Form 138 (Report Card)</li>
<li>📄 PSA Birth Certificate (original + 2 photocopies)</li>
<li>📸 2×2 ID photos (4 pieces, white background)</li>
<li>📋 Accomplished enrollment form</li>
<li>💳 Valid government-issued ID or Barangay Certificate</li>
<li>📄 Certificate of Good Moral Character (for transferees)</li>
<li>📄 Transcript of Records (for transferees or college students)</li>
</ul>
<strong>Note:</strong> Original documents must be presented for verification.`
    },
    {
        patterns: ['grade', 'check grade', 'view grade', 'gpa', 'grade report', 'how do i check'],
        answer: `To check your grades:
<ul>
<li>Go to <a href="grades.php">🎓 Grades</a> from the sidebar menu.</li>
<li>Your grades are shown per subject with midterm and final scores.</li>
<li>Overall GPA is computed and displayed at the top.</li>
</ul>
If you notice a discrepancy, contact your subject teacher or visit the Registrar's Office within <strong>7 days</strong> of grade posting.`
    },
    {
        patterns: ['schedule', 'timetable', 'class schedule', 'when is my class', 'view schedule'],
        answer: `To view your class schedule:
<ul>
<li>Click <a href="schedule.php">📅 My Schedule</a> in the sidebar.</li>
<li>Your schedule is displayed as a weekly timetable (7 AM – 9 PM).</li>
<li>You can print it using the 🖨️ Print button.</li>
</ul>
For changes to your schedule, you must submit a <strong>change of schedule form</strong> to the Registrar within the first 2 weeks of the semester.`
    },
    {
        patterns: ['fee', 'tuition', 'payment', 'how much', 'cost', 'price', 'downpayment', 'installment'],
        answer: `Tuition and fee information:
<ul>
<li><strong>BSIT:</strong> ₱18,500 per semester (approx.)</li>
<li><strong>BSHTM:</strong> ₱16,000 per semester (approx.)</li>
<li>Miscellaneous fees: ₱2,500–₱3,500 depending on course</li>
</ul>
Payment options:
<ul>
<li>💵 Full payment (5% discount applies)</li>
<li>📆 Installment: 50% downpayment + 2 installments</li>
<li>🏦 GCash, bank transfer, or over-the-counter at the cashier</li>
</ul>
For exact amounts, visit the <strong>Cashier's Office (Room 102)</strong>.`
    },
    {
        patterns: ['professor', 'teacher', 'faculty', 'contact teacher', 'instructor', 'email teacher'],
        answer: `To find and contact faculty members:
<ul>
<li>Go to <a href="faculty.php">👨‍🏫 Faculty Directory</a> from the sidebar.</li>
<li>Search by name, department, or subject.</li>
<li>Faculty emails and consultation schedules are listed there.</li>
</ul>
You may also visit the <strong>Faculty Room (Room 201)</strong> during office hours, typically <strong>7:30 AM – 5:00 PM, Monday–Friday</strong>.`
    },
    {
        patterns: ['calendar', 'school date', 'holiday', 'semester', 'midterm', 'final exam', 'exam week', 'schedule of exam'],
        answer: `Important school dates for AY 2024–2025:
<ul>
<li>📅 <strong>1st Semester:</strong> August 12 – December 20, 2024</li>
<li>📅 <strong>Midterm Exams:</strong> October 7–11, 2024</li>
<li>📅 <strong>Final Exams:</strong> December 9–13, 2024</li>
<li>📅 <strong>2nd Semester:</strong> January 13 – May 23, 2025</li>
<li>📅 <strong>Midterm Exams:</strong> March 3–7, 2025</li>
<li>📅 <strong>Final Exams:</strong> May 12–16, 2025</li>
</ul>
Check the <a href="announcements.php">📢 Announcements</a> page for the latest updates.`
    },
    {
        patterns: ['feedback', 'complaint', 'suggestion', 'concern', 'report', 'how to submit'],
        answer: `To submit feedback or concerns:
<ul>
<li>Go to <a href="feedback.php">💬 Feedback</a> from the sidebar.</li>
<li>Fill in the subject and your message.</li>
<li>You can track the status of your feedback (Pending, In Progress, Resolved).</li>
</ul>
For urgent concerns, you may also visit the <strong>Guidance Office (Room 105)</strong> or the <strong>Dean's Office (Room 201)</strong>.`
    },
    {
        patterns: ['id', 'student id', 'lost id', 'replace id', 'identification'],
        answer: `For student ID concerns:
<ul>
<li><strong>First issuance:</strong> Included in enrollment. Claim at the Registrar after 3–5 working days.</li>
<li><strong>Lost/damaged ID:</strong> Submit a written request at the Registrar with an Affidavit of Loss.</li>
<li><strong>Replacement fee:</strong> ₱150</li>
</ul>
Processing time is <strong>3–5 working days</strong> from submission.`
    },
    {
        patterns: ['withdraw', 'withdrawal', 'dropping', 'drop subject', 'leave of absence', 'loa'],
        answer: `For withdrawal or dropping of subjects:
<ul>
<li>Get a <strong>withdrawal/dropping form</strong> from the Registrar's Office.</li>
<li>Have it signed by your subject teacher, Dean, and Registrar.</li>
<li>Submit within the <strong>official dropping period</strong> (first 4 weeks of semester).</li>
<li>Late dropping may result in a grade of <strong>W (Withdrawn)</strong> on your record.</li>
</ul>
For Leave of Absence (LOA), submit a letter of request to the Dean's Office at least <strong>2 weeks in advance</strong>.`
    },
    {
        patterns: ['scholarship', 'scholar', 'financial aid', 'discount', 'subsidy'],
        answer: `Available scholarships and financial assistance:
<ul>
<li>🏛 <strong>Government:</strong> CHED UniFAST, TESDA, DSWD Scholarship</li>
<li>🏫 <strong>School-based:</strong> Academic Excellence Award (top 3 per course per year)</li>
<li>🏅 <strong>Athletic/Special talent</strong> scholarships</li>
<li>💼 <strong>Working student program</strong> (discounted tuition in exchange for service hours)</li>
</ul>
Visit the <strong>Scholarship Office (Room 103)</strong> or ask at the Registrar for requirements and application deadlines.`
    },
    {
        patterns: ['wifi', 'internet', 'password', 'network', 'connect'],
        answer: `School Wi-Fi access:
<ul>
<li>Network: <strong>OL-SmartSchool-Student</strong></li>
<li>Password: Request at the IT Department (Room 301) with your student ID.</li>
<li>Wi-Fi is available in all classrooms, library, and canteen areas.</li>
</ul>
For connectivity issues, contact the <strong>IT Support Desk (Room 301)</strong>, open Monday–Friday, 8 AM–5 PM.`
    },
    {
        patterns: ['library', 'book', 'borrow', 'librar'],
        answer: `Library services:
<ul>
<li>📚 Located at the <strong>2nd floor, Building B</strong></li>
<li>Hours: <strong>Monday–Friday, 7:30 AM – 6:00 PM</strong></li>
<li>Students may borrow up to <strong>3 books</strong> for 3 days.</li>
<li>Overdue fine: <strong>₱5 per book per day</strong></li>
<li>E-library access is available via your student portal credentials.</li>
</ul>`
    },
    {
        patterns: ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'sup', 'start'],
        answer: `Hello! 👋 How can I help you today? Feel free to ask about:
<ul>
<li>📋 Enrollment process & requirements</li>
<li>🎓 Grades and schedules</li>
<li>💰 Tuition fees and scholarships</li>
<li>👨‍🏫 Faculty and contacts</li>
<li>📆 School calendar</li>
</ul>
Just type your question or tap a topic button at the top!`
    },
    {
        patterns: ['thank', 'thanks', 'salamat', 'ty'],
        answer: `You're welcome! 😊 Is there anything else I can help you with? Don't hesitate to ask!`
    },
    {
        patterns: ['bye', 'goodbye', 'see you', 'ok bye'],
        answer: `Take care! 👋 Feel free to come back anytime you have questions. Good luck with your studies! 🎓`
    }
];

// ─── Matching ─────────────────────────────────────────────────────────────────
function findAnswer(input) {
    const lower = input.toLowerCase().trim();
    for (const entry of KB) {
        for (const pattern of entry.patterns) {
            if (lower.includes(pattern)) return entry.answer;
        }
    }
    return null;
}

// ─── DOM helpers ──────────────────────────────────────────────────────────────
function scrollToBottom() {
    const msgs = document.getElementById('chatMessages');
    msgs.scrollTop = msgs.scrollHeight;
}

function appendMessage(type, html) {
    const msgs = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'msg ' + type;
    const icon = type === 'bot' ? '🤖' : '👤';
    div.innerHTML = `<div class="msg-avatar">${icon}</div><div class="msg-bubble">${html}</div>`;
    msgs.appendChild(div);
    scrollToBottom();
    return div;
}

function showTyping() {
    const msgs = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'msg bot typing-indicator';
    div.id = 'typingIndicator';
    div.innerHTML = `<div class="msg-avatar">🤖</div><div class="msg-bubble"><div class="typing-dots"><span></span><span></span><span></span></div></div>`;
    msgs.appendChild(div);
    scrollToBottom();
}

function removeTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
}

// ─── Core send ────────────────────────────────────────────────────────────────
function sendMessage() {
    const input = document.getElementById('chatInput');
    const text = input.value.trim();
    if (!text) return;

    input.value = '';
    document.getElementById('sendBtn').disabled = true;

    appendMessage('user', escapeHtml(text));
    showTyping();

    // Simulate a short delay
    setTimeout(() => {
        removeTyping();

        const answer = findAnswer(text);
        if (answer) {
            appendMessage('bot', answer);
        } else {
            appendMessage('bot', `I'm sorry, I don't have information on that yet. 😕<br><br>
For specific concerns, you can:
<ul>
<li>Visit the <strong>Registrar's Office (Room 101)</strong></li>
<li>Submit a concern via <a href="feedback.php">💬 Feedback</a></li>
<li>Call the school at <strong>(032) 555-0100</strong></li>
</ul>`);
        }

        document.getElementById('sendBtn').disabled = false;
        document.getElementById('chatInput').focus();
    }, 700 + Math.random() * 400);
}

function sendQuick(text) {
    document.getElementById('chatInput').value = text;
    sendMessage();
}

function clearChat() {
    if (!confirm('Clear the chat history?')) return;
    const msgs = document.getElementById('chatMessages');
    msgs.innerHTML = '';
    appendMessage('bot', `Chat cleared! 🗑️ How can I help you? Feel free to type your question or tap a topic above.`);
}

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Enter key support
document.getElementById('chatInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});
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
    <script src="/js/session-monitor.js"></script>
</body>
</html>

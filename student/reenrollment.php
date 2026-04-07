<?php
require_once '../php/config.php';

if (!isLoggedIn() || !hasRole('student')) {
    header('Location: ../login.html');
    exit();
}

// Fetch student's existing data
$conn = getDBConnection();

$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, u.student_id, u.course, u.year_level, u.status, u.avatar_url,
           ed.dob, ed.sex, ed.civil_status, ed.nationality, ed.place_of_birth,
           ed.mobile_number, ed.home_address, ed.father_name, ed.mother_name,
           ed.guardian_name, ed.emergency_contact_name, ed.emergency_contact_relation,
           ed.emergency_contact_phone, ed.semester, ed.school_year
    FROM users u
    LEFT JOIN enrollment_details ed ON ed.user_id = u.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if ($student) {
    $student['avatar_url'] = getAvatarUrl($student['avatar_url'] ?? null);
}

if (!$student) {
    header('Location: dashboard.php');
    exit();
}

// Only active/enrolled students can re-enroll
$status = strtolower($student['status'] ?? '');
$is_returnee = in_array($status, ['active', 'enrolled', 'approved']);
if (!$is_returnee) {
    header('Location: dashboard.php');
    exit();
}

// Parse name into parts (stored as "Last, First Middle" or "First Last")
$full_name = $student['name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../images/logo2.jpg">
    <link rel="apple-touch-icon" href="../images/logo2.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Re-Enrollment Form — Student Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --red:    #8b0000;
            --red2:   #6b0000;
            --gold:   #c8a951;
            --gold2:  #d4b865;
            --dark:   #1a0000;
            --gray:   #64748b;
            --border: #e8ddd0;
            --bg:     #fdf8f4;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
        }

        /* ── Top Banner ── */
        .top-banner {
            background: var(--red);
            color: white;
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 2rem;
            border-bottom: 3px solid var(--gold);
            box-shadow: 0 2px 12px rgba(139,0,0,.3);
        }
        .brand { display: flex; align-items: center; gap: .75rem; }
        .brand-icon {
            width: 38px; height: 38px; border-radius: 8px;
            background: var(--gold); color: var(--red);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Playfair Display', serif; font-weight: 800; font-size: .9rem;
        }
        .brand-name { font-family: 'Playfair Display', serif; font-size: 1rem; }
        .brand-name small { display: block; font-family: 'DM Sans', sans-serif; font-size: .7rem; opacity: .7; font-weight: 400; }
        .banner-back {
            display: inline-flex; align-items: center; gap: .4rem;
            color: rgba(255,255,255,0.8); font-size: .82rem; text-decoration: none;
            padding: .4rem .85rem; border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.3);
            transition: color .2s, border-color .2s, background .2s;
        }
        .banner-back:hover { color: white; border-color: var(--gold); background: rgba(200,169,81,.15); }

        /* ── Page Layout ── */
        .page-body {
            max-width: 960px; margin: 2.5rem auto;
            padding: 0 1.5rem 4rem;
            display: grid;
            grid-template-columns: 270px 1fr;
            gap: 2rem;
            align-items: start;
        }

        /* ── Left Panel ── */
        .student-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 16px rgba(139,0,0,.1);
            margin-bottom: 1.25rem;
            border: 1px solid var(--border);
        }
        .student-card-top {
            background: var(--red);
            padding: 1.5rem 1.25rem;
            text-align: center;
        }
        .student-avatar {
            width: 68px; height: 68px; border-radius: 50%;
            background: var(--gold); color: var(--red);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; font-weight: 800;
            margin: 0 auto .75rem;
            border: 3px solid rgba(255,255,255,.3);
            overflow: hidden;
        }
        .student-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .student-name { color: white; font-weight: 700; font-size: .95rem; margin-bottom: .25rem; }
        .student-id { color: var(--gold); font-size: .75rem; font-family: monospace; }
        .student-card-body { padding: 1rem; }
        .info-row {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding: .5rem 0; border-bottom: 1px solid #f5ece4; font-size: .8rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row .lbl { color: var(--gray); font-weight: 600; flex-shrink: 0; }
        .info-row .val { color: var(--dark); font-weight: 600; text-align: right; font-size: .78rem; }

        .returnee-badge {
            background: #fff8e7; color: var(--red);
            border: 1px solid var(--gold); border-radius: 999px;
            font-size: .72rem; font-weight: 700; letter-spacing: .5px;
            text-transform: uppercase; padding: .25rem .85rem;
            display: inline-block; margin-bottom: 1rem;
        }

        /* Steps sidebar */
        .steps-card {
            background: white; border-radius: 14px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 16px rgba(139,0,0,.07);
            overflow: hidden; margin-bottom: 1.25rem;
        }
        .steps-card-header {
            background: #fdf3e7; padding: .6rem 1rem;
            font-size: .65rem; font-weight: 800; letter-spacing: 1.5px;
            text-transform: uppercase; color: var(--red);
            border-bottom: 1px solid var(--border);
        }
        .step-list { list-style: none; padding: .5rem; display: flex; flex-direction: column; gap: .2rem; }
        .step-li {
            display: flex; align-items: center; gap: .75rem;
            padding: .6rem .85rem; border-radius: 10px; cursor: default;
            transition: background .2s;
        }
        .step-li.active { background: #fff8e7; }
        .step-li.done { background: #f0fdf4; }
        .step-dot {
            width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: .72rem; font-weight: 800;
            border: 2px solid var(--border); color: var(--gray); background: white;
            transition: all .25s;
        }
        .step-li.done .step-dot { background: #16a34a; border-color: #16a34a; color: white; }
        .step-li.active .step-dot { background: var(--red); border-color: var(--red); color: white; }
        .step-lbl { font-size: .82rem; font-weight: 500; color: var(--gray); }
        .step-li.active .step-lbl { color: var(--red); font-weight: 700; }
        .step-li.done .step-lbl { color: #16a34a; }

        .note-box {
            background: #fff8e7; border: 1px solid var(--gold);
            border-radius: 12px; padding: 1rem;
            font-size: .78rem; color: #6b3a00; line-height: 1.6;
        }
        .note-box strong { display: block; margin-bottom: .3rem; color: var(--red); }

        /* ── Form Card ── */
        .form-card {
            background: white; border-radius: 16px;
            box-shadow: 0 4px 24px rgba(139,0,0,.1);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .progress-wrap { height: 5px; background: #f5ece4; }
        .progress-fill {
            height: 100%; background: linear-gradient(90deg, var(--red), var(--gold));
            border-radius: 0 3px 3px 0;
            transition: width .4s ease;
        }

        .step-content { display: none; }
        .step-content.active { display: block; }

        .step-header {
            background: var(--red); padding: 1.4rem 1.75rem;
            display: flex; align-items: center; gap: 1rem; color: white;
            border-bottom: 3px solid var(--gold);
        }
        .step-badge {
            width: 38px; height: 38px; border-radius: 50%;
            background: var(--gold); color: var(--red);
            font-family: 'Playfair Display', serif;
            font-weight: 800; font-size: 1.1rem;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .step-header h2 { font-size: 1rem; font-weight: 700; }
        .step-header p { font-size: .75rem; opacity: .75; margin-top: .1rem; }

        .form-body { padding: 1.75rem; }

        .prefill-notice {
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 8px; padding: .75rem 1rem;
            font-size: .8rem; color: #1e40af;
            margin-bottom: 1.5rem; display: flex; align-items: center; gap: .5rem;
        }

        .field-group { margin-bottom: 1.2rem; }
        .field-label {
            display: block; font-size: .72rem; font-weight: 700;
            color: var(--dark); text-transform: uppercase; letter-spacing: .6px;
            margin-bottom: .4rem;
        }
        .field-label .req { color: var(--red); margin-left: .2rem; }
        .field-input, .field-select, .field-textarea {
            width: 100%; padding: .75rem 1rem;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-family: 'DM Sans', sans-serif; font-size: .88rem;
            color: var(--dark); background: white;
            transition: border-color .2s, box-shadow .2s;
            appearance: none;
        }
        .field-input:focus, .field-select:focus, .field-textarea:focus {
            outline: none; border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(139,0,0,.1);
        }
        .field-input.prefilled { background: #fdfaf7; color: #4a3728; }
        .field-textarea { resize: vertical; min-height: 80px; }
        .field-select-wrap { position: relative; }
        .field-select-wrap::after {
            content: '▾'; position: absolute; right: 1rem; top: 50%;
            transform: translateY(-50%); color: var(--gray); pointer-events: none;
        }
        .field-hint { font-size: .73rem; color: #94a3b8; margin-top: .3rem; }
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .three-col { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }

        .section-divider {
            font-size: .66rem; font-weight: 800; letter-spacing: 1.5px;
            text-transform: uppercase; color: var(--red);
            display: flex; align-items: center; gap: .75rem;
            margin: 1.5rem 0 1rem;
        }
        .section-divider::before, .section-divider::after {
            content: ''; flex: 1; height: 1px; background: #f0e4d8;
        }

        .btn-row { display: flex; align-items: center; justify-content: space-between; margin-top: 1.75rem; gap: 1rem; }
        .btn-primary {
            background: var(--red); color: white; border: none; cursor: pointer;
            padding: .8rem 2rem; border-radius: 999px;
            font-family: 'DM Sans', sans-serif; font-weight: 700; font-size: .88rem;
            transition: background .2s; display: inline-flex; align-items: center; gap: .4rem;
        }
        .btn-primary:hover { background: var(--red2); }
        .btn-primary.gold { background: var(--gold); color: var(--red); }
        .btn-primary.gold:hover { background: var(--gold2); }
        .btn-secondary {
            background: white; color: var(--gray);
            border: 1.5px solid var(--border); cursor: pointer;
            padding: .78rem 1.5rem; border-radius: 999px;
            font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: .85rem;
            transition: border-color .2s, color .2s;
        }
        .btn-secondary:hover { border-color: var(--red); color: var(--red); }

        /* Review step */
        .review-section { margin-bottom: 1.5rem; }
        .review-section h4 {
            font-size: .7rem; font-weight: 800; letter-spacing: 1.2px;
            text-transform: uppercase; color: var(--red);
            margin-bottom: .75rem; padding-bottom: .4rem;
            border-bottom: 2px solid #f0e4d8;
        }
        .review-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .4rem; }
        .review-item { padding: .5rem .75rem; background: #fdfaf7; border-radius: 6px; border: 1px solid #f0e4d8; }
        .review-label { font-size: .63rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--gray); }
        .review-val { font-size: .85rem; font-weight: 600; color: var(--dark); margin-top: .1rem; }

        /* Success */
        .success-card { text-align: center; padding: 3rem 2rem; }
        .success-icon { font-size: 4rem; display: block; margin-bottom: 1rem; }
        .success-ref {
            display: inline-block;
            background: #fff8e7; color: var(--red);
            border: 1px solid var(--gold); border-radius: 8px;
            padding: .6rem 1.25rem; font-family: monospace;
            font-size: .95rem; font-weight: 700; margin: 1rem 0;
        }
        .success-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--red); margin-bottom: .5rem; }
        .success-desc { font-size: .88rem; color: var(--gray); max-width: 420px; margin: 0 auto 1.5rem; line-height: 1.7; }

        /* Alert */
        .alert { padding: .85rem 1rem; border-radius: 8px; font-size: .83rem; margin-bottom: 1rem; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #fff8e7; color: var(--red); border: 1px solid var(--gold); }

        @media (max-width: 768px) {
            .page-body { grid-template-columns: 1fr; }
            .two-col, .three-col { grid-template-columns: 1fr; }
            .review-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Top Banner -->
<div class="top-banner">
    <div class="brand">
        <div class="brand-icon">SCC</div>
        <div class="brand-name">St. Cecilia's College-Cebu
            <small>Returnee Re-Enrollment Portal</small>
        </div>
    </div>
    <a href="dashboard.php" class="banner-back">← Back to Dashboard</a>
</div>

<div class="page-body">

    <!-- LEFT: Info Panel -->
    <aside class="info-panel">

        <!-- Student Card -->
        <div class="student-card">
            <div class="student-card-top">
                <div class="student-avatar">
                    <?php if (!empty($student['avatar_url'])): ?>
                        <img src="<?= htmlspecialchars($student['avatar_url']) ?>" alt="Avatar">
                    <?php else: ?>
                        <?= strtoupper(substr($student['name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="student-name"><?= htmlspecialchars($student['name']) ?></div>
                <div class="student-id"><?= htmlspecialchars($student['student_id']) ?></div>
            </div>
            <div class="student-card-body">
                <div class="info-row">
                    <span class="lbl">Course</span>
                    <span class="val"><?= htmlspecialchars($student['course'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="lbl">Year Level</span>
                    <span class="val"><?= htmlspecialchars($student['year_level'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="lbl">Status</span>
                    <span class="val" style="color:#16a34a;font-weight:700;"><?= ucfirst($student['status']) ?></span>
                </div>
                <div class="info-row">
                    <span class="lbl">Email</span>
                    <span class="val" style="font-size:.72rem;"><?= htmlspecialchars($student['email']) ?></span>
                </div>
            </div>
        </div>

        <div class="returnee-badge">🔄 Returnee Enrollment</div>

        <!-- Steps -->
        <div class="steps-card">
            <div class="steps-card-header">Enrollment Steps</div>
            <ul class="step-list" id="stepList">
                <li class="step-li active" data-step="1">
                    <div class="step-dot">1</div>
                    <span class="step-lbl">Confirm Details</span>
                </li>
                <li class="step-li" data-step="2">
                    <div class="step-dot">2</div>
                    <span class="step-lbl">Academic Update</span>
                </li>
                <li class="step-li" data-step="3">
                    <div class="step-dot">3</div>
                    <span class="step-lbl">Review & Submit</span>
                </li>
            </ul>
        </div>

        <div class="note-box">
            <strong>📋 Returnee Requirements</strong>
            Bring your previous semester's Report Card (Form 138), your valid School ID, and settle any outstanding fees before or after submission.
        </div>
    </aside>

    <!-- RIGHT: Form -->
    <div class="form-card">
        <div class="progress-wrap">
            <div class="progress-fill" id="progressFill" style="width:33%"></div>
        </div>

        <div id="alertBox"></div>

        <!-- ══ STEP 1: Confirm Personal Details ══ -->
        <div class="step-content active" id="step1">
            <div class="step-header">
                <div class="step-badge">1</div>
                <div>
                    <h2>Confirm Your Details</h2>
                    <p>Review and update your personal information if needed</p>
                </div>
            </div>
            <div class="form-body">
                <div class="prefill-notice">
                    ℹ️ Your information has been pre-filled from our records. Please update anything that has changed.
                </div>

                <div class="section-divider">Full Name</div>
                <div class="field-group">
                    <label class="field-label">Full Name <span class="req">*</span></label>
                    <input type="text" class="field-input prefilled" id="fullName"
                           value="<?= htmlspecialchars($student['name']) ?>" required>
                </div>

                <div class="section-divider">Personal Details</div>
                <div class="two-col">
                    <div class="field-group">
                        <label class="field-label">Date of Birth</label>
                        <input type="date" class="field-input prefilled" id="dob"
                               value="<?= htmlspecialchars($student['dob'] ?? '') ?>">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Sex</label>
                        <div class="field-select-wrap">
                            <select class="field-select" id="sex">
                                <option value="">Select</option>
                                <option value="Male" <?= ($student['sex']??'')==='Male'?'selected':'' ?>>Male</option>
                                <option value="Female" <?= ($student['sex']??'')==='Female'?'selected':'' ?>>Female</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="two-col">
                    <div class="field-group">
                        <label class="field-label">Civil Status</label>
                        <div class="field-select-wrap">
                            <select class="field-select" id="civilStatus">
                                <option value="Single" <?= ($student['civil_status']??'')==='Single'?'selected':'' ?>>Single</option>
                                <option value="Married" <?= ($student['civil_status']??'')==='Married'?'selected':'' ?>>Married</option>
                                <option value="Widowed" <?= ($student['civil_status']??'')==='Widowed'?'selected':'' ?>>Widowed</option>
                                <option value="Separated" <?= ($student['civil_status']??'')==='Separated'?'selected':'' ?>>Separated</option>
                            </select>
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Nationality</label>
                        <input type="text" class="field-input prefilled" id="nationality"
                               value="<?= htmlspecialchars($student['nationality'] ?? 'Filipino') ?>">
                    </div>
                </div>

                <div class="section-divider">Contact & Address</div>
                <div class="field-group">
                    <label class="field-label">Mobile Number <span class="req">*</span></label>
                    <input type="tel" class="field-input prefilled" id="mobileNumber"
                           value="<?= htmlspecialchars($student['mobile_number'] ?? '') ?>"
                           placeholder="09XX-XXX-XXXX" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Home Address <span class="req">*</span></label>
                    <textarea class="field-textarea prefilled" id="homeAddress" required><?= htmlspecialchars($student['home_address'] ?? '') ?></textarea>
                </div>

                <div class="btn-row">
                    <div></div>
                    <button class="btn-primary gold" onclick="goStep(2)">Next: Academic Update →</button>
                </div>
            </div>
        </div>

        <!-- ══ STEP 2: Academic Update ══ -->
        <div class="step-content" id="step2">
            <div class="step-header">
                <div class="step-badge">2</div>
                <div>
                    <h2>Academic Update</h2>
                    <p>Select your new year level, section, and semester for re-enrollment</p>
                </div>
            </div>
            <div class="form-body">

                <div class="section-divider">Student ID</div>
                <div class="field-group">
                    <label class="field-label">LRN / Student ID</label>
                    <input type="text" class="field-input prefilled" id="studentId"
                           value="<?= htmlspecialchars($student['student_id']) ?>" readonly>
                    <div class="field-hint">Your Student ID cannot be changed.</div>
                </div>

                <div class="section-divider">Enrollment Type</div>
                <div class="field-group">
                    <label class="field-label">Enrollment Type</label>
                    <input type="text" class="field-input prefilled" value="Returnee" readonly>
                    <div class="field-hint">Set automatically for returning students.</div>
                </div>

                <div class="section-divider">Program & Year</div>
                <div class="two-col">
                    <div class="field-group">
                        <label class="field-label">Course / Program <span class="req">*</span></label>
                        <input type="text" class="field-input prefilled" id="courseDisplay"
                               value="<?= htmlspecialchars($student['course'] ?? '') ?>" readonly>
                        <div class="field-hint">Contact the Registrar to change your course.</div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">New Year Level <span class="req">*</span></label>
                        <div class="field-select-wrap">
                            <select class="field-select" id="yearLevel" required onchange="fetchSections()">
                                <option value="">Select</option>
                                <?php
                                $levels = ['1st Year','2nd Year','3rd Year','4th Year'];
                                $currentLevel = $student['year_level'] ?? '';
                                foreach ($levels as $lvl) {
                                    $sel = ($lvl === $currentLevel) ? 'selected' : '';
                                    echo "<option value=\"$lvl\" $sel>$lvl</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="two-col">
                    <div class="field-group">
                        <label class="field-label">Semester <span class="req">*</span></label>
                        <div class="field-select-wrap">
                            <select class="field-select" id="semester" required onchange="fetchSections()">
                                <option value="">Select</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">School Year</label>
                        <input type="text" class="field-input" id="schoolYear"
                               placeholder="e.g. 2025-2026" value="2025-2026">
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Section <span class="req">*</span></label>
                    <div class="field-select-wrap">
                        <select class="field-select" id="sectionId" required>
                            <option value="">— Select year level & semester first —</option>
                        </select>
                    </div>
                </div>

                <div class="btn-row">
                    <button class="btn-secondary" onclick="goStep(1)">← Back</button>
                    <button class="btn-primary gold" onclick="goStep(3)">Next: Review & Submit →</button>
                </div>
            </div>
        </div>

        <!-- ══ STEP 3: Review & Submit ══ -->
        <div class="step-content" id="step3">
            <div class="step-header">
                <div class="step-badge">3</div>
                <div>
                    <h2>Review & Submit</h2>
                    <p>Check all information before submitting your re-enrollment</p>
                </div>
            </div>
            <div class="form-body">
                <div id="reviewContent"></div>

                <div style="margin:1.5rem 0;padding:1rem;background:#fff8e7;border:1px solid var(--gold);border-radius:10px;font-size:.82rem;color:#6b3a00;line-height:1.7;">
                    <strong style="display:block;margin-bottom:.25rem;color:var(--red);">📋 Declaration</strong>
                    By submitting this form, I certify that all information provided is true and accurate.
                    I understand that my re-enrollment is subject to verification and approval by the Registrar's Office.
                </div>

                <div id="alertBox2"></div>

                <div class="btn-row">
                    <button class="btn-secondary" onclick="goStep(2)">← Back</button>
                    <button class="btn-primary gold" id="submitBtn" onclick="submitReenrollment()">
                        ✅ Submit Re-Enrollment
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ SUCCESS ══ -->
        <div class="step-content" id="stepSuccess">
            <div class="success-card">
                <span class="success-icon">🎉</span>
                <div class="success-title">Re-Enrollment Submitted!</div>
                <div class="success-ref" id="refNumber">ENR-2025-00000</div>
                <p class="success-desc">
                    Your re-enrollment application has been received and is now pending review by the Registrar's Office.
                    You will be notified once your enrollment is approved. Processing takes 1–3 business days.
                </p>
                <a href="dashboard.php"
                   style="display:inline-block;background:var(--red);color:white;padding:.8rem 2rem;border-radius:999px;text-decoration:none;font-weight:700;font-size:.88rem;">
                    ← Return to Dashboard
                </a>
            </div>
        </div>

    </div><!-- /form-card -->
</div><!-- /page-body -->

<script>
let currentStep = 1;
const totalSteps = 3;
const courseValue = <?= json_encode($student['course'] ?? '') ?>;

function goStep(step) {
    // Validate current step before proceeding
    if (step > currentStep) {
        if (!validateStep(currentStep)) return;
    }

    document.getElementById('step' + currentStep).classList.remove('active');
    currentStep = step;
    document.getElementById('step' + currentStep).classList.add('active');

    // Update sidebar steps
    document.querySelectorAll('.step-li').forEach(li => {
        const s = parseInt(li.dataset.step);
        li.classList.toggle('active', s === currentStep);
        li.classList.toggle('done', s < currentStep);
    });
    document.querySelectorAll('.step-dot').forEach((dot, i) => {
        if (i + 1 < currentStep) dot.textContent = '✓';
        else dot.textContent = i + 1;
    });

    // Progress bar
    const pct = Math.round((currentStep / totalSteps) * 100);
    document.getElementById('progressFill').style.width = pct + '%';

    if (step === 3) buildReview();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateStep(step) {
    if (step === 1) {
        if (!document.getElementById('fullName').value.trim()) {
            alert('Please enter your full name.'); return false;
        }
        if (!document.getElementById('mobileNumber').value.trim()) {
            alert('Please enter your mobile number.'); return false;
        }
        if (!document.getElementById('homeAddress').value.trim()) {
            alert('Please enter your home address.'); return false;
        }
    }
    if (step === 2) {
        if (!document.getElementById('yearLevel').value) {
            alert('Please select your year level.'); return false;
        }
        if (!document.getElementById('semester').value) {
            alert('Please select a semester.'); return false;
        }
        if (!document.getElementById('sectionId').value) {
            alert('Please select a section.'); return false;
        }
    }
    return true;
}

function buildReview() {
    const html = `
        <div class="review-section">
            <h4>Personal Information</h4>
            <div class="review-grid">
                <div class="review-item"><div class="review-label">Full Name</div><div class="review-val">${esc(v('fullName'))}</div></div>
                <div class="review-item"><div class="review-label">Student ID</div><div class="review-val">${esc(v('studentId'))}</div></div>
                <div class="review-item"><div class="review-label">Date of Birth</div><div class="review-val">${esc(v('dob')) || '—'}</div></div>
                <div class="review-item"><div class="review-label">Sex</div><div class="review-val">${esc(v('sex')) || '—'}</div></div>
                <div class="review-item"><div class="review-label">Mobile</div><div class="review-val">${esc(v('mobileNumber'))}</div></div>
                <div class="review-item"><div class="review-label">Nationality</div><div class="review-val">${esc(v('nationality'))}</div></div>
            </div>
            <div class="review-item" style="margin-top:.4rem;">
                <div class="review-label">Home Address</div>
                <div class="review-val">${esc(v('homeAddress'))}</div>
            </div>
        </div>
        <div class="review-section">
            <h4>Academic Details</h4>
            <div class="review-grid">
                <div class="review-item"><div class="review-label">Enrollment Type</div><div class="review-val" style="color:var(--red);font-weight:700;">Returnee</div></div>
                <div class="review-item"><div class="review-label">Course</div><div class="review-val">${esc(courseValue)}</div></div>
                <div class="review-item"><div class="review-label">Year Level</div><div class="review-val">${esc(v('yearLevel'))}</div></div>
                <div class="review-item"><div class="review-label">Semester</div><div class="review-val">${esc(v('semester'))}</div></div>
                <div class="review-item"><div class="review-label">School Year</div><div class="review-val">${esc(v('schoolYear'))}</div></div>
                <div class="review-item"><div class="review-label">Section</div><div class="review-val">${esc(document.getElementById('sectionId').options[document.getElementById('sectionId').selectedIndex]?.text || '—')}</div></div>
            </div>
        </div>
    `;
    document.getElementById('reviewContent').innerHTML = html;
}

function v(id) {
    const el = document.getElementById(id);
    return el ? el.value : '';
}
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Fetch sections from API
async function fetchSections() {
    const course = courseValue;
    const yearLevel = v('yearLevel');
    const semester = v('semester');
    if (!course || !yearLevel || !semester) return;

    const sel = document.getElementById('sectionId');
    sel.innerHTML = '<option value="">Loading…</option>';

    try {
        const url = `../php/api/get_sections.php?course=${encodeURIComponent(course)}&year_level=${encodeURIComponent(yearLevel)}&semester=${encodeURIComponent(semester)}`;
        const res = await fetch(url);
        const data = await res.json();
        if (data.success && data.sections.length > 0) {
            sel.innerHTML = '<option value="">— Select a section —</option>';
            data.sections.forEach(s => {
                sel.innerHTML += `<option value="${s.id}">${s.section_name} (${s.enrolled_count ?? 0}/${s.max_students ?? '∞'} students)</option>`;
            });
        } else {
            sel.innerHTML = '<option value="">No sections available yet</option>';
        }
    } catch (e) {
        sel.innerHTML = '<option value="">Error loading sections</option>';
    }
}

async function submitReenrollment() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Submitting…';

    const payload = {
        full_name:    v('fullName'),
        dob:          v('dob'),
        sex:          v('sex'),
        civil_status: v('civilStatus'),
        nationality:  v('nationality'),
        mobile_number: v('mobileNumber'),
        home_address: v('homeAddress'),
        year_level:   v('yearLevel'),
        semester:     v('semester'),
        school_year:  v('schoolYear'),
        section_id:   v('sectionId'),
        enrollment_type: 'Returnee',
    };

    try {
        const res = await fetch('../php/api/student/reenroll.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('step3').classList.remove('active');
            document.getElementById('stepSuccess').classList.add('active');
            document.getElementById('refNumber').textContent = data.ref || 'ENR-2025-00000';
        } else {
            document.getElementById('alertBox2').innerHTML =
                `<div class="alert alert-error">⚠️ ${data.message}</div>`;
            btn.disabled = false;
            btn.textContent = '✅ Submit Re-Enrollment';
        }
    } catch (e) {
        document.getElementById('alertBox2').innerHTML =
            `<div class="alert alert-error">⚠️ Network error. Please try again.</div>`;
        btn.disabled = false;
        btn.textContent = '✅ Submit Re-Enrollment';
    }
}
</script>
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

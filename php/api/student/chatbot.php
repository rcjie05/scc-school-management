<?php
require_once '../../config.php';
header('Content-Type: application/json');

// Auth checks - return JSON instead of redirecting
if (!isLoggedIn()) {
    echo json_encode(array('reply' => 'Your session has expired. Please log in again.'));
    exit();
}
if (!hasRole('student')) {
    echo json_encode(array('reply' => 'Access denied. This assistant is for students only.'));
    exit();
}

$conn    = getDBConnection();
$user_id = $_SESSION['user_id'];

if (!$conn) {
    echo json_encode(array('reply' => 'Sorry, I could not connect to the database right now. Please try again.'));
    exit();
}

// ── Load floor plan routes ────────────────────────────────────────────────────
function getRoutes($conn) {
    $routes = array();
    $stmt = $conn->prepare("SELECT id, name, description, start_room, end_room, waypoints FROM floor_plan_routes WHERE visible_to_students = 1 ORDER BY name ASC");
    if (!$stmt) return $routes;
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $wp = $row['waypoints'] ? $row['waypoints'] : '[]';
        $wpArr = json_decode($wp, true);
        if (!is_array($wpArr)) $wpArr = array();
        $routes[] = array(
            'id'          => $row['id'],
            'name'        => $row['name'],
            'description' => $row['description'],
            'startRoom'   => $row['start_room'],
            'endRoom'     => $row['end_room'],
            'waypoints'   => count($wpArr)
        );
    }
    $stmt->close();
    return $routes;
}

// ── Load all rooms ────────────────────────────────────────────────────────────
function getRooms($conn) {
    $rooms = array();
    $stmt = $conn->prepare("
        SELECT r.id, r.room_number, r.room_type, r.floor, r.capacity,
               r.purpose, r.x_pos, r.y_pos, r.width, r.height,
               b.building_name
        FROM rooms r
        JOIN buildings b ON b.id = r.building_id
        ORDER BY b.building_name, r.floor, r.room_number
    ");
    if (!$stmt) return $rooms;
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $onMap = ($row['x_pos'] !== null);
        $cx = $onMap ? (int)$row['x_pos'] + (int)round($row['width'] / 2) : null;
        $cy = $onMap ? (int)$row['y_pos'] + (int)round($row['height'] / 2) : null;
        $rooms[] = array(
            'id'       => (int)$row['id'],
            'name'     => $row['room_number'],
            'type'     => $row['room_type'],
            'floor'    => $row['floor'],
            'capacity' => $row['capacity'],
            'purpose'  => $row['purpose'],
            'building' => $row['building_name'],
            'on_map'   => $onMap,
            'centerX'  => $cx,
            'centerY'  => $cy,
        );
    }
    $stmt->close();
    return $rooms;
}

// ── Load routes with waypoint data ───────────────────────────────────────────
function getRoutesWithWaypoints($conn) {
    $routes = array();
    $stmt = $conn->prepare("SELECT id, name, description, start_room, end_room, waypoints FROM floor_plan_routes WHERE visible_to_students = 1 ORDER BY name ASC");
    if (!$stmt) return $routes;
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $wp = $row['waypoints'] ? $row['waypoints'] : '[]';
        $wpData = json_decode($wp, true);
        if (!is_array($wpData)) $wpData = array();
        $routes[] = array(
            'id'           => $row['id'],
            'name'         => $row['name'],
            'description'  => $row['description'],
            'startRoom'    => $row['start_room'],
            'endRoom'      => $row['end_room'],
            'waypointData' => $wpData,
            'waypointCount'=> count($wpData),
        );
    }
    $stmt->close();
    return $routes;
}

// ── Find room by fuzzy name ───────────────────────────────────────────────────
function findRoom($rooms, $query) {
    $q = strtolower(trim($query));
    foreach ($rooms as $r) {
        if (strtolower($r['name']) === $q) return $r;
    }
    foreach ($rooms as $r) {
        if (strpos(strtolower($r['name']), $q) !== false) return $r;
    }
    foreach ($rooms as $r) {
        if (strpos($q, strtolower($r['name'])) !== false) return $r;
    }
    return null;
}

// ── Build room directions reply ───────────────────────────────────────────────
function buildRoomDirectionsReply($msg, $rooms, $routes) {
    $msg = strtolower(trim($msg));
    $strip = array('how to get to','how do i get to','where is','find room','navigate to',
                   'directions to','route to','go to','location of','saan ang','saan',
                   'paano pumunta sa','paano pumunta','how to go to','how to reach',
                   'how do i find','take me to','bring me to','find my way to',
                   'where can i find','i need to go to','i want to go to',
                   'punta sa','pupunta sa','pumunta sa','the');
    $query = $msg;
    foreach ($strip as $s) {
        $query = str_replace($s, '', $query);
    }
    $query = trim($query, " \t\n\r.,?!");

    $room = findRoom($rooms, $query);
    if (!$room) {
        $nameList = array();
        $slice = array_slice($rooms, 0, 10);
        foreach ($slice as $r) {
            $nameList[] = $r['name'];
        }
        $list = implode(', ', $nameList);
        return "I couldn't find a room matching \"<strong>" . htmlspecialchars($query) . "</strong>\". Try asking: <em>\"Where is the Registrar?\"</em><br>Known rooms include: <em>" . $list . "</em>...<br>Or visit the <a href='floorplan.php'>Floor Plan</a>!";
    }

    $floor  = $room['floor']    ? "Floor " . $room['floor'] : '';
    $cap    = $room['capacity'] ? $room['capacity'] . " pax" : '';
    $parts  = array_filter(array($room['building'], $floor, $cap));
    $detail = implode(' - ', $parts);

    $reply = "<strong>" . $room['name'] . "</strong>";
    if ($detail) $reply .= "<br><span style='color:#6b7280;font-size:.9em;'>" . $detail . "</span>";
    if ($room['purpose']) $reply .= "<br><br>Purpose: <em>" . $room['purpose'] . "</em>";

    $matched = array();
    foreach ($routes as $r) {
        if (stripos($r['startRoom'], $room['name']) !== false ||
            stripos($r['endRoom'],   $room['name']) !== false ||
            stripos($room['name'],   $r['startRoom']) !== false ||
            stripos($room['name'],   $r['endRoom'])   !== false) {
            $matched[] = $r;
        }
    }

    if (!empty($matched)) {
        $reply .= "<br><br><strong>How to get there:</strong>";
        foreach ($matched as $route) {
            $reply .= "<br><br><strong>Route: " . $route['name'] . "</strong>";
            if ($route['description']) $reply .= "<br><em>" . $route['description'] . "</em>";
            $reply .= "<br>From: <strong>" . $route['startRoom'] . "</strong> to <strong>" . $route['endRoom'] . "</strong>";
            $wc = $route['waypointCount'];
            $reply .= "<br>Follow the marked path (" . $wc . " waypoint" . ($wc !== 1 ? 's' : '') . ") on the floor map.";
        }
        $reply .= "<br><br><a href='floorplan.php'>Open Floor Plan</a> to see the route on the map!";
    } elseif ($room['on_map']) {
        $reply .= "<br><br>This room is visible on the <a href='floorplan.php'>Campus Floor Plan</a>.";
    } else {
        $reply .= "<br><br>This room is in <strong>" . $room['building'] . "</strong>" . ($floor ? ", " . $floor : '') . ".<br>Visit the <a href='floorplan.php'>Floor Plan</a> or ask at the Registrar's Office for directions.";
    }
    return $reply;
}

// ── Load available courses ────────────────────────────────────────────────────
function getCourses($conn) {
    $courses = array();
    $stmt = $conn->prepare("SELECT course_name, course_code, description, status FROM courses WHERE status = 'active' ORDER BY course_name");
    if (!$stmt) return $courses;
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $courses[] = $row;
    }
    $stmt->close();
    return $courses;
}

// ── Load student data ─────────────────────────────────────────────────────────
function getStudentData($conn, $user_id) {
    $data = array();

    // Profile
    $stmt = $conn->prepare("SELECT name, email, student_id, course, year_level, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $data['profile'] = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Load count
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT sl.id) as subject_count, COALESCE(SUM(s.units), 0) as total_units FROM study_loads sl JOIN subjects s ON sl.subject_id = s.id WHERE sl.student_id = ? AND sl.status = 'finalized'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $data['load'] = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Grades
    $stmt = $conn->prepare("SELECT s.subject_code, s.subject_name, s.units, g.midterm_grade, g.final_grade, g.remarks FROM grades g JOIN subjects s ON g.subject_id = s.id WHERE g.student_id = ? ORDER BY s.subject_code");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data['grades'] = array();
    $totalGrade = 0; $gradeCount = 0;
    while ($row = $res->fetch_assoc()) {
        $data['grades'][] = $row;
        if ($row['final_grade']) { $totalGrade += $row['final_grade']; $gradeCount++; }
    }
    $data['gpa'] = $gradeCount > 0 ? number_format($totalGrade / $gradeCount, 2) : null;
    $stmt->close();

    // Schedule
    $has_sec_sched = $conn->query("SHOW TABLES LIKE 'section_schedules'")->num_rows > 0;
    $has_sec_subj  = $conn->query("SHOW TABLES LIKE 'section_subjects'")->num_rows > 0;
    $has_sl_sec    = $conn->query("SHOW COLUMNS FROM `study_loads` LIKE 'section_id'")->num_rows > 0;

    if ($has_sec_sched && $has_sec_subj && $has_sl_sec) {
        $sql = "SELECT s.subject_code, s.subject_name, sec.section_name, u.name AS teacher_name, sc.day_of_week, sc.start_time, sc.end_time, sc.room, sc.building FROM study_loads sl JOIN subjects s ON sl.subject_id = s.id LEFT JOIN sections sec ON sl.section_id = sec.id LEFT JOIN users u ON sl.teacher_id = u.id LEFT JOIN section_subjects ss ON ss.section_id = sl.section_id AND ss.subject_id = sl.subject_id LEFT JOIN section_schedules sc ON sc.section_subject_id = ss.id WHERE sl.student_id = ? ORDER BY sc.start_time";
    } else {
        $sql = "SELECT s.subject_code, s.subject_name, sl.section AS section_name, u.name AS teacher_name, sch.day_of_week, sch.start_time, sch.end_time, sch.room, sch.building FROM study_loads sl JOIN subjects s ON sl.subject_id = s.id LEFT JOIN schedules sch ON sch.study_load_id = sl.id LEFT JOIN users u ON sl.teacher_id = u.id WHERE sl.student_id = ? ORDER BY sch.day_of_week, sch.start_time";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data['schedule'] = array();
    while ($row = $res->fetch_assoc()) {
        if (empty($row['day_of_week'])) continue;
        $roomStr = trim(($row['building'] ? $row['building'] . ' - ' : '') . ($row['room'] ? $row['room'] : ''), ' -');
        $data['schedule'][] = array(
            'subject_code' => $row['subject_code'],
            'subject_name' => $row['subject_name'],
            'section'      => $row['section_name'] ? $row['section_name'] : 'TBA',
            'teacher'      => $row['teacher_name']  ? $row['teacher_name']  : 'TBA',
            'day'          => $row['day_of_week'],
            'start'        => $row['start_time'] ? date('g:i A', strtotime($row['start_time'])) : 'TBA',
            'end'          => $row['end_time']   ? date('g:i A', strtotime($row['end_time']))   : 'TBA',
            'room'         => $roomStr ? $roomStr : 'TBA',
        );
    }
    $stmt->close();

    // Announcements
    $stmt = $conn->prepare("SELECT title, content, DATE_FORMAT(created_at, '%M %d, %Y') as date FROM announcements WHERE target_audience IN ('all','students') ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $res = $stmt->get_result();
    $data['announcements'] = array();
    while ($row = $res->fetch_assoc()) $data['announcements'][] = $row;
    $stmt->close();

    // Section
    $data['section'] = null;
    $sec_stmt = $conn->prepare("SELECT s.section_name, s.section_code, s.course, s.year_level, s.semester, s.school_year, s.room, s.building FROM users u JOIN sections s ON u.section_id = s.id WHERE u.id = ?");
    if ($sec_stmt) {
        $sec_stmt->bind_param('i', $user_id);
        $sec_stmt->execute();
        $data['section'] = $sec_stmt->get_result()->fetch_assoc();
        $sec_stmt->close();
    }

    // Feedback
    $data['feedback'] = array();
    $fb_stmt = $conn->prepare("SELECT subject, message, status, response, DATE_FORMAT(created_at, '%M %d, %Y') as date FROM feedback WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    if ($fb_stmt) {
        $fb_stmt->bind_param('i', $user_id);
        $fb_stmt->execute();
        $fb_res = $fb_stmt->get_result();
        while ($row = $fb_res->fetch_assoc()) $data['feedback'][] = $row;
        $fb_stmt->close();
    }

    return $data;
}

// ── Intent matching ───────────────────────────────────────────────────────────
function matchIntent($msg) {
    $m = strtolower(trim($msg));
    $intents = array(
        'greeting'          => array('hello','hi','hey','good morning','good afternoon','good evening','sup','start','kamusta'),
        'name'              => array('my name','what is my name','who am i','pangalan'),
        'student_id'        => array('student id','my id','id number','school id'),
        'course'            => array('my course','what course','what am i taking','program','degree'),
        'courses_available' => array('available courses','list of courses','what courses','courses offered','programs offered','available programs','what programs','how many courses','courses available','what can i take','anong course','course list','all courses'),
        'year_level'        => array('year level','what year','year am i','grade level'),
        'email'             => array('my email','email address','email ko'),
        'status'            => array('enrollment status','my status','am i enrolled'),
        'grades'            => array('grade','grades','my grade','check grade','gpa','score','result','how did i do','passed','failed'),
        'schedule'          => array('schedule','timetable','class schedule','when is my class','my classes','what class'),
        'subjects'          => array('subjects','my subjects','enrolled in','study load','units','how many units','how many subjects'),
        'today'             => array('today','ngayon','class today','schedule today'),
        'today_rooms'       => array('room today','where today','locate today','find today','rooms today','my room today','classroom today','where are my classes today','where is my class today','find my class today','locate my class','where should i go'),
        'teacher'           => array('teacher','professor','instructor','faculty','who teaches','sino teacher'),
        'announcements'     => array('announcement','news','latest','updates','notice'),
        'feedback_check'    => array('my feedback','check feedback','feedback status','my complaint','my concern','feedback ko','did they reply','my ticket'),
        'feedback_submit'   => array('submit feedback','send feedback','how to submit feedback','how to give feedback','how to complain','send concern','mag feedback'),
        'enroll_how'        => array('how to enroll','enrollment process','how do i enroll','how to register'),
        'requirements'      => array('requirement','documents needed','what to bring','what to submit'),
        'fees'              => array('tuition fee','school fee','how much','payment','cost','price','tuition','bayad'),
        'calendar'          => array('school calendar','important dates','exam date','when is exam','final exam'),
        'scholarship'       => array('scholarship','financial aid','scholar'),
        'library'           => array('library','book','borrow'),
        'wifi'              => array('wifi','internet','password','network'),
        'student_id_lost'   => array('lost id','replace id','lost my id'),
        'withdraw'          => array('withdraw','dropping','drop subject','leave of absence','loa'),
        'thanks'            => array('thank','thanks','salamat','ty','thank you'),
        'bye'               => array('bye','goodbye','see you','cya'),
        'profile'           => array('my profile','my information','my info','about me'),
        'floorplan_all'     => array('floor plan','floorplan','campus map','show routes','available routes','all routes'),
        'floorplan_find'    => array('how to get to','how do i get to','where is','find room','navigate to','directions to','route to','location of','saan ang','saan','paano pumunta'),
        'floorplan_my'      => array('where is my class','find my class','my classroom','my room','find my room'),
        'section'           => array('my section','what section','section am i in','section name','block','what block','my block','section code','school year','semester'),
    );

    foreach ($intents as $intent => $keywords) {
        foreach ($keywords as $kw) {
            if (strpos($m, $kw) !== false) return $intent;
        }
    }
    return 'unknown';
}

// ── Build reply ───────────────────────────────────────────────────────────────
function buildReply($intent, $d, $msg) {
    $p         = $d['profile'];
    $name      = isset($p['name']) ? $p['name'] : 'Student';
    $nameParts = explode(' ', $name);
    $firstName = $nameParts[0];

    switch ($intent) {

        case 'greeting':
            return "Hi, <strong>" . $firstName . "</strong>! I'm your School Assistant. I can tell you about your grades, schedule, subjects, announcements, and more. What do you need?";

        case 'profile':
        case 'name':
            return "Your name on record is <strong>" . $p['name'] . "</strong>.";

        case 'student_id':
            return "Your student ID is <strong>" . $p['student_id'] . "</strong>.";

        case 'course':
            return "You are enrolled in <strong>" . $p['course'] . "</strong>.";

        case 'year_level':
            return "You are currently in <strong>Year " . $p['year_level'] . "</strong>.";

        case 'email':
            return "Your registered email is <strong>" . $p['email'] . "</strong>.";

        case 'status':
            $s = ucfirst(isset($p['status']) ? $p['status'] : 'unknown');
            return "Your enrollment status is: <strong>" . $s . "</strong>.";

        case 'subjects':
            $count = isset($d['load']['subject_count']) ? $d['load']['subject_count'] : 0;
            $units = isset($d['load']['total_units'])   ? $d['load']['total_units']   : 0;
            if ($count == 0) return "You don't have any finalized enrolled subjects yet.";
            $seen = array(); $list = '';
            foreach ($d['schedule'] as $sub) {
                if (!isset($seen[$sub['subject_code']])) {
                    $seen[$sub['subject_code']] = true;
                    $list .= "<li><strong>" . $sub['subject_code'] . "</strong> - " . $sub['subject_name'] . "</li>";
                }
            }
            return "You are enrolled in <strong>" . $count . " subject(s)</strong> totaling <strong>" . $units . " units</strong>:<ul>" . $list . "</ul>";

        case 'grades':
            if (empty($d['grades'])) return "No grades have been posted yet. Check back later!";
            $rows = '';
            foreach ($d['grades'] as $g) {
                $mid   = isset($g['midterm_grade']) && $g['midterm_grade'] ? $g['midterm_grade'] : '-';
                $fin   = isset($g['final_grade'])   && $g['final_grade']   ? $g['final_grade']   : '-';
                $rem   = isset($g['remarks']) ? $g['remarks'] : '';
                $badge = ($rem === 'Passed') ? ' checkmark' : (($rem === 'Failed') ? ' x' : '');
                $rows .= "<li><strong>" . $g['subject_code'] . "</strong> " . $g['subject_name'] . " - Midterm: " . $mid . " | Final: " . $fin . $badge . "</li>";
            }
            $gpa = $d['gpa'] ? " Your GPA is <strong>" . $d['gpa'] . "</strong>." : '';
            return "Here are your grades:" . $gpa . "<ul>" . $rows . "</ul>";

        case 'today':
        case 'schedule':
            if (empty($d['schedule'])) return "No schedule found. Please contact the Registrar.";
            $today = date('l');
            $byDay = array();
            foreach ($d['schedule'] as $s) {
                $byDay[$s['day']][] = $s;
            }
            if ($intent === 'today') {
                if (empty($byDay[$today])) return "You have no classes today (<strong>" . $today . "</strong>). Enjoy your day!";
                $rows = '';
                foreach ($byDay[$today] as $s) {
                    $roomInfo = $s['room'] && $s['room'] !== 'TBA' ? $s['room'] : 'TBA';
                    // Try to match room to floor plan
                    $rooms   = isset($d['rooms']) ? $d['rooms'] : array();
                    $matched = findRoom($rooms, $roomInfo);
                    $roomLink = '';
                    if ($matched) {
                        $detail = array();
                        if ($matched['building']) $detail[] = $matched['building'];
                        if ($matched['floor'])    $detail[] = 'Floor ' . $matched['floor'];
                        $roomLink = ' <span style="color:#6b7280;font-size:.85em;">(' . implode(', ', $detail) . ')</span> <a href="floorplan.php" style="font-size:.82em;">📍 Map</a>';
                    }
                    $rows .= "<li><strong>" . $s['subject_code'] . "</strong> " . $s['subject_name'] . "<br><span style='font-size:.88em;'>🕐 " . $s['start'] . " - " . $s['end'] . " &nbsp;|&nbsp; 🚪 " . $roomInfo . $roomLink . "</span></li>";
                }
                return "Your classes for today (<strong>" . $today . "</strong>):<ul>" . $rows . "</ul>";
            }
            $order = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
            $out = "Your weekly schedule:<br>";
            foreach ($order as $day) {
                if (empty($byDay[$day])) continue;
                $out .= "<br><strong>" . $day . "</strong><ul>";
                foreach ($byDay[$day] as $s) {
                    $out .= "<li><strong>" . $s['subject_code'] . "</strong> " . $s['subject_name'] . " - " . $s['start'] . "-" . $s['end'] . " | " . $s['room'] . " | " . $s['teacher'] . "</li>";
                }
                $out .= "</ul>";
            }
            return $out;

        case 'today_rooms':
            $sched = isset($d['schedule']) ? $d['schedule'] : array();
            $rooms = isset($d['rooms'])    ? $d['rooms']    : array();
            $routes = isset($d['routes_full']) ? $d['routes_full'] : array();
            $today = date('l');
            $todaySched = array();
            foreach ($sched as $s) {
                if ($s['day'] === $today) $todaySched[] = $s;
            }
            if (empty($sched))      return "You don't have any subjects enrolled yet.";
            if (empty($todaySched)) return "You have no classes today (<strong>" . $today . "</strong>). Enjoy your free day!";

            $reply = "Here are your classroom locations for today (<strong>" . $today . "</strong>):<br><br>";
            foreach ($todaySched as $s) {
                $roomName = $s['room'] && $s['room'] !== 'TBA' ? $s['room'] : null;
                $reply .= "<strong>" . $s['subject_code'] . "</strong> - " . $s['subject_name'] . "<br>";
                $reply .= "<span style='font-size:.88em;'>🕐 " . $s['start'] . " - " . $s['end'] . "</span><br>";

                if (!$roomName) {
                    $reply .= "<span style='font-size:.88em;color:#ef4444;'>🚪 Room not assigned yet</span><br><br>";
                    continue;
                }

                // Look up room details from floor plan data
                $roomData = findRoom($rooms, $roomName);
                if ($roomData) {
                    $details = array();
                    if ($roomData['building']) $details[] = "🏢 " . $roomData['building'];
                    if ($roomData['floor'])    $details[] = "Floor " . $roomData['floor'];
                    if ($roomData['type'])     $details[] = $roomData['type'];
                    $reply .= "<span style='font-size:.88em;'>🚪 <strong>" . $roomName . "</strong> — " . implode(' | ', $details) . "</span><br>";
                    if ($roomData['purpose'])  $reply .= "<span style='font-size:.82em;color:#6b7280;'>" . $roomData['purpose'] . "</span><br>";

                    // Check if there's a navigation route to this room
                    $routeFound = null;
                    foreach ($routes as $r) {
                        if (stripos($r['endRoom'], $roomName) !== false || stripos($roomName, $r['endRoom']) !== false) {
                            $routeFound = $r; break;
                        }
                    }
                    if ($routeFound) {
                        $reply .= "<span style='font-size:.82em;'>🗺️ Route available: <em>" . $routeFound['name'] . "</em> — <a href='floorplan.php'>Open Floor Plan</a></span><br>";
                    } elseif ($roomData['on_map']) {
                        $reply .= "<span style='font-size:.82em;'>📍 Visible on <a href='floorplan.php'>Floor Plan</a></span><br>";
                    }
                } else {
                    $reply .= "<span style='font-size:.88em;'>🚪 <strong>" . $roomName . "</strong></span><br>";
                    $reply .= "<span style='font-size:.82em;color:#6b7280;'>Visit the <a href='floorplan.php'>Floor Plan</a> to locate this room.</span><br>";
                }
                $reply .= "<br>";
            }
            return $reply;

        case 'teacher':
            if (empty($d['schedule'])) return "No teacher info found. Please contact the Registrar.";
            $seen = array(); $rows = '';
            foreach ($d['schedule'] as $s) {
                if (!isset($seen[$s['subject_code']])) {
                    $seen[$s['subject_code']] = true;
                    $rows .= "<li><strong>" . $s['subject_code'] . "</strong> - " . $s['teacher'] . "</li>";
                }
            }
            return "Your subject teachers:<ul>" . $rows . "</ul>See the <strong>Faculty Directory</strong> in the sidebar for contact info.";

        case 'announcements':
            if (empty($d['announcements'])) return "No announcements at the moment. Check back later!";
            $rows = '';
            foreach ($d['announcements'] as $a) {
                $preview = strlen($a['content']) > 100 ? substr($a['content'], 0, 100) . '...' : $a['content'];
                $rows .= "<li><strong>" . $a['title'] . "</strong> <small>(" . $a['date'] . ")</small><br><span style='font-size:.9em;'>" . $preview . "</span></li>";
            }
            return "Latest announcements:<ul>" . $rows . "</ul>";

        case 'section':
            $sec = isset($d['section']) ? $d['section'] : null;
            if (!$sec) return "You don't seem to be assigned to a section yet. Please contact the Registrar.";
            $roomLine = '';
            if (!empty($sec['room'])) {
                $r = trim(($sec['building'] ? $sec['building'] . ' - ' : '') . $sec['room'], ' -');
                $roomLine = "<li><strong>Room:</strong> " . $r . "</li>";
            }
            return "Your section details:<ul><li><strong>Section:</strong> " . $sec['section_name'] . "</li><li><strong>Code:</strong> " . $sec['section_code'] . "</li><li><strong>Course:</strong> " . $sec['course'] . "</li><li><strong>Year Level:</strong> Year " . $sec['year_level'] . "</li><li><strong>Semester:</strong> " . $sec['semester'] . "</li><li><strong>School Year:</strong> " . $sec['school_year'] . "</li>" . $roomLine . "</ul>";

        case 'feedback_check':
            $fb = isset($d['feedback']) ? $d['feedback'] : array();
            if (empty($fb)) return "You haven't submitted any feedback yet. Submit one via <a href='feedback.php'>Feedback</a> in the sidebar.";
            $rows = '';
            foreach ($fb as $f) {
                $status = ucfirst(str_replace('_', ' ', $f['status']));
                $rep    = $f['response'] ? "<br><em>Response: " . substr($f['response'], 0, 80) . (strlen($f['response']) > 80 ? '...' : '') . "</em>" : '';
                $rows  .= "<li><strong>" . $f['subject'] . "</strong> - " . $status . " <small>(" . $f['date'] . ")</small>" . $rep . "</li>";
            }
            return "You have <strong>" . count($fb) . " feedback submission(s)</strong>:<ul>" . $rows . "</ul>View details in <a href='feedback.php'>Feedback</a>.";

        case 'feedback_submit':
            return "To submit feedback:<ul><li>Click <strong>Feedback</strong> in the sidebar.</li><li>Fill in Subject and Message, then click Submit.</li><li>Track your status: Pending, In Progress, or Resolved.</li></ul>For urgent matters, visit the <strong>Guidance Office - Room 105</strong>.";

        case 'enroll_how':
            return "How to enroll at <strong>St. Cecilia's College-Cebu</strong>:<br><br><strong>New Students:</strong><ul><li>Visit the Registrar's Office or fill out the online pre-enrollment form.</li><li>Submit required documents for verification.</li><li>Take the College Entrance Test (CET).</li><li>Get course/section assignment from the Registrar.</li><li>Pay fees at the Cashier's Office.</li><li>Receive your Class Card and Student ID.</li></ul><strong>Returning Students:</strong><ul><li>Submit pre-enrollment form online or at the Registrar.</li><li>Secure clearance (Library, Cashier, previous teachers).</li><li>Get your study load, pay fees, and present receipt to finalize.</li></ul>Registrar's Office - Ground Floor, Main Building | (032) 255-5148 | Mon-Fri 7:30AM-5PM";

        case 'requirements':
            return "Enrollment requirements at <strong>St. Cecilia's College-Cebu</strong>:<br><br><strong>All Students:</strong><ul><li>Accomplished Enrollment Form</li><li>2x2 ID photos (4 pieces, white background)</li><li>Valid government-issued ID</li></ul><strong>New Freshmen:</strong><ul><li>Original Form 138 + photocopy</li><li>PSA Birth Certificate (original + 2 copies)</li><li>Certificate of Good Moral Character</li><li>Senior High School Diploma</li></ul><strong>Transferees:</strong><ul><li>Official Transcript of Records</li><li>Honorable Dismissal</li><li>Certificate of Good Moral Character</li></ul>Contact Registrar: (032) 255-5148";

        case 'fees':
            return "Tuition and fees at <strong>St. Cecilia's College-Cebu</strong> (AY 2024-2025):<br><br>Tuition: ~P650-P750 per unit<br><br><strong>Miscellaneous Fees:</strong><ul><li>Registration: P500</li><li>Library: P300</li><li>Computer/Tech: P500</li><li>Student Activities: P400</li><li>Medical/Dental: P250</li></ul><strong>Payment Options:</strong><ul><li>Full payment (5% discount)</li><li>Installment: 50% down + 2 payments</li><li>GCash / Online Banking / Over-the-counter</li></ul>Cashier's Office - Ground Floor | Mon-Fri 7:30AM-4:30PM";

        case 'calendar':
            return "Academic Calendar <strong>AY 2024-2025</strong>:<br><br><strong>1st Semester 2024:</strong><ul><li>Enrollment: July 15 - August 9</li><li>Classes Begin: August 12</li><li>Midterm Exams: October 7-11</li><li>Final Exams: December 9-13</li></ul><strong>2nd Semester 2025:</strong><ul><li>Enrollment: January 6-10</li><li>Classes Begin: January 13</li><li>Midterm Exams: March 3-7</li><li>Final Exams: May 12-16</li></ul>Check <strong>Announcements</strong> for the latest updates.";

        case 'scholarship':
            return "Available scholarships:<ul><li>CHED UniFAST, TESDA, DSWD</li><li>Academic Excellence Award</li><li>Athletic / Special Talent scholarships</li><li>Working Student Program</li></ul>Visit the <strong>Scholarship Office - Room 103</strong> for requirements.";

        case 'library':
            return "Library services:<ul><li>Location: 2nd Floor, Building B</li><li>Hours: Mon-Fri, 7:30 AM - 6:00 PM</li><li>Borrow up to <strong>3 books</strong> for 3 days</li><li>Overdue fine: P5 per book per day</li></ul>";

        case 'wifi':
            return "School Wi-Fi:<ul><li>Network: <strong>OL-SmartSchool-Student</strong></li><li>Password: Request at IT Dept. (Room 301) with your student ID.</li></ul>IT Support - Room 301, Mon-Fri 8AM-5PM";

        case 'student_id_lost':
            return "Lost or damaged ID:<ul><li>Submit a written request + Affidavit of Loss at the Registrar.</li><li>Replacement fee: <strong>P150</strong></li><li>Processing time: 3-5 working days</li></ul>";

        case 'withdraw':
            return "Withdrawal or dropping a subject:<ul><li>Get the form from the Registrar's Office.</li><li>Have it signed by your teacher, Dean, and Registrar.</li><li>Submit within the <strong>first 4 weeks</strong> of the semester.</li><li>Late drops result in a grade of <strong>W (Withdrawn)</strong>.</li></ul>For Leave of Absence, submit a letter to the Dean's Office 2 weeks in advance.";

        case 'thanks':
            return "You're welcome, <strong>" . $firstName . "</strong>! Is there anything else I can help you with?";

        case 'bye':
            return "Take care, <strong>" . $firstName . "</strong>! Good luck with your studies!";

        case 'floorplan_all':
            $routes = isset($d['routes']) ? $d['routes'] : array();
            if (empty($routes)) return "No navigation routes have been set up yet. Visit the <a href='floorplan.php'>Campus Map</a> to explore!";
            $rows = '';
            foreach ($routes as $r) {
                $desc = $r['description'] ? " - <em>" . $r['description'] . "</em>" : '';
                $rows .= "<li><strong>" . $r['name'] . "</strong>: " . $r['startRoom'] . " to " . $r['endRoom'] . $desc . "</li>";
            }
            return "There are <strong>" . count($routes) . " available route(s)</strong>:<ul>" . $rows . "</ul>Open the <a href='floorplan.php'>Floor Plan</a> and click a route to see it on the map!";

        case 'floorplan_find':
        case 'room_info':
            $rooms  = isset($d['rooms'])       ? $d['rooms']       : array();
            $routes = isset($d['routes_full']) ? $d['routes_full'] : array();
            return buildRoomDirectionsReply($msg, $rooms, $routes);

        case 'floorplan_my':
            $sched  = isset($d['schedule'])    ? $d['schedule']    : array();
            $rooms  = isset($d['rooms'])        ? $d['rooms']       : array();
            $routes = isset($d['routes_full'])  ? $d['routes_full'] : array();
            $today  = date('l');
            if (empty($sched)) return "You don't have any classes yet. Once enrolled, I can help you find your classrooms on the <a href='floorplan.php'>Floor Plan</a>!";

            // Separate today vs other days
            $todaySched = array(); $otherSched = array(); $seen = array();
            foreach ($sched as $s) {
                $key = $s['subject_code'] . $s['room'];
                if (isset($seen[$key])) continue;
                $seen[$key] = true;
                if ($s['day'] === $today) $todaySched[] = $s;
                else $otherSched[] = $s;
            }

            $reply = '';
            if (!empty($todaySched)) {
                $reply .= "<strong>Today (" . $today . "):</strong><ul>";
                foreach ($todaySched as $s) {
                    $roomData = findRoom($rooms, $s['room']);
                    $loc = '';
                    if ($roomData) {
                        $parts = array();
                        if ($roomData['building']) $parts[] = $roomData['building'];
                        if ($roomData['floor'])    $parts[] = 'Floor ' . $roomData['floor'];
                        $loc = ' <span style="color:#6b7280;font-size:.85em;">(' . implode(', ', $parts) . ')</span>';
                    }
                    $mapLink = ($roomData && $roomData['on_map']) ? ' <a href="floorplan.php" style="font-size:.82em;">📍 Map</a>' : '';
                    $reply .= "<li><strong>" . $s['subject_code'] . "</strong> — 🚪 " . $s['room'] . $loc . $mapLink . "<br><small>" . $s['start'] . "-" . $s['end'] . "</small></li>";
                }
                $reply .= "</ul>";
            }
            if (!empty($otherSched)) {
                $reply .= "<strong>Other days:</strong><ul>";
                foreach ($otherSched as $s) {
                    $reply .= "<li><strong>" . $s['subject_code'] . "</strong> " . $s['subject_name'] . " — 🚪 " . $s['room'] . " <small>(" . $s['day'] . ")</small></li>";
                }
                $reply .= "</ul>";
            }
            $reply .= "Open the <a href='floorplan.php'>Floor Plan</a> to see your rooms on the campus map!";
            return $reply;

        case 'courses_available':
            $courses = isset($d['courses']) ? $d['courses'] : array();
            if (empty($courses)) return "I couldn't retrieve the course list. Please visit the <strong>Registrar's Office</strong> for available programs. Tel: (032) 255-5148";
            $rows = '';
            foreach ($courses as $c) {
                $code = $c['course_code'] ? " (" . $c['course_code'] . ")" : '';
                $rows .= "<li><strong>" . $c['course_name'] . "</strong>" . $code . "</li>";
            }
            return "St. Cecilia's College-Cebu offers <strong>" . count($courses) . " active program(s)</strong>:<ul>" . $rows . "</ul>Visit the Registrar's Office for program details.";

        default:
            return "Sorry, I don't have info on that yet.<br><br>You can:<ul><li>Visit the <strong>Registrar's Office</strong></li><li>Submit a concern via <a href='feedback.php'>Feedback</a></li><li>Call <strong>(032) 555-0100</strong></li></ul>";
    }
}

// ── Main ──────────────────────────────────────────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true);
$msg   = isset($input['message']) ? trim($input['message']) : '';

if ($msg === '') {
    echo json_encode(array('reply' => 'Please type a message.'));
    exit();
}

$data                = getStudentData($conn, $user_id);
$data['routes']      = getRoutes($conn);
$data['routes_full'] = getRoutesWithWaypoints($conn);
$data['rooms']       = getRooms($conn);
$data['courses']     = getCourses($conn);

$intent = matchIntent($msg);
$reply  = buildReply($intent, $data, $msg);

echo json_encode(array('reply' => $reply, 'intent' => $intent));
$conn->close();
?>

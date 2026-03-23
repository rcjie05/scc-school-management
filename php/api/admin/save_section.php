<?php
require_once '../../config.php';
requireRole('admin');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$section_id   = $data['section_id']   ?? null;
$section_name = trim($data['section_name'] ?? '');
$section_code = trim($data['section_code'] ?? '');
$course       = trim($data['course']       ?? '');
$year_level   = trim($data['year_level']   ?? '');
$semester     = trim($data['semester']     ?? '');
$school_year  = trim($data['school_year']  ?? '');
$max_students = (int)($data['max_students'] ?? 40);
$room         = trim($data['room']         ?? '');
$building     = trim($data['building']     ?? '');
$adviser_id   = !empty($data['adviser_id']) ? (int)$data['adviser_id'] : null;
$status       = $data['status']            ?? 'active';

if (!$section_name || !$section_code) {
    echo json_encode(['success' => false, 'message' => 'Section name and code are required']);
    exit();
}

if ($section_id) {
    // Update
    $stmt = $conn->prepare("UPDATE sections SET section_name=?, section_code=?, course=?, year_level=?, semester=?, school_year=?, max_students=?, room=?, building=?, adviser_id=?, status=? WHERE id=?");
    $stmt->bind_param('ssssssiisssi', $section_name, $section_code, $course, $year_level, $semester, $school_year, $max_students, $room, $building, $adviser_id, $status, $section_id);
    
    if ($stmt->execute()) {
        logAction($conn, $_SESSION['user_id'], "Updated section: $section_code", 'sections', $section_id);
        echo json_encode(['success' => true, 'message' => 'Section updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update: ' . $conn->error]);
    }
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO sections (section_name, section_code, course, year_level, semester, school_year, max_students, room, building, adviser_id, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('ssssssiisss', $section_name, $section_code, $course, $year_level, $semester, $school_year, $max_students, $room, $building, $adviser_id, $status);
    
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        logAction($conn, $_SESSION['user_id'], "Created section: $section_code", 'sections', $new_id);
        echo json_encode(['success' => true, 'message' => 'Section created successfully', 'section_id' => $new_id]);
    } else {
        $err = $conn->error;
        if (strpos($err, 'Duplicate entry') !== false) {
            echo json_encode(['success' => false, 'message' => "Section code '$section_code' already exists"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create: ' . $err]);
        }
    }
}

$stmt->close();
$conn->close();
?>

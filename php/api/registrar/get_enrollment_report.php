<?php
require_once '../../config.php';
requireRole('registrar');

header('Content-Type: application/json');

try {
    // Get overall stats
    $statsStmt = $pdo->query("SELECT 
        COUNT(DISTINCT s.id) as total_students,
        SUM(CASE WHEN s.enrollment_status = 'enrolled' THEN 1 ELSE 0 END) as fully_enrolled,
        SUM(CASE WHEN s.enrollment_status = 'pending' THEN 1 ELSE 0 END) as pending_enrollment
        FROM students s");
    $stats = $statsStmt->fetch();
    
    // Get enrollment by course
    $courseStmt = $pdo->query("SELECT 
        s.course,
        COUNT(*) as total,
        SUM(CASE WHEN s.year_level = '1st Year' THEN 1 ELSE 0 END) as year_1,
        SUM(CASE WHEN s.year_level = '2nd Year' THEN 1 ELSE 0 END) as year_2,
        SUM(CASE WHEN s.year_level = '3rd Year' THEN 1 ELSE 0 END) as year_3,
        SUM(CASE WHEN s.year_level = '4th Year' THEN 1 ELSE 0 END) as year_4
        FROM students s
        GROUP BY s.course
        ORDER BY total DESC");
    $by_course = $courseStmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'by_course' => $by_course
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error generating enrollment report: ' . $e->getMessage()
    ]);
}
?>

<?php
require_once '../../config.php';
requireRole('registrar');

header('Content-Type: application/json');

try {
    // Get overall stats
    $statsStmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM student_applications");
    $stats = $statsStmt->fetch();
    
    // Get applications by course
    $courseStmt = $pdo->query("SELECT 
        course,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM student_applications
        GROUP BY course
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
        'message' => 'Error generating applications report: ' . $e->getMessage()
    ]);
}
?>

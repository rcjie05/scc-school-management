<?php
require_once '../../config.php';
requireRole('registrar');

header('Content-Type: application/json');

try {
    // Get overall stats
    $statsStmt = $pdo->query("SELECT 
        SUM(CASE WHEN g.remarks = 'Passed' THEN 1 ELSE 0 END) as passed,
        SUM(CASE WHEN g.remarks = 'Failed' THEN 1 ELSE 0 END) as failed,
        SUM(CASE WHEN g.remarks = 'Incomplete' OR g.remarks IS NULL THEN 1 ELSE 0 END) as incomplete,
        ROUND(AVG(CASE WHEN g.final_grade IS NOT NULL THEN g.final_grade ELSE g.midterm_grade END), 2) as average_grade
        FROM grades g");
    $stats = $statsStmt->fetch();
    
    // Get performance by course
    $courseStmt = $pdo->query("SELECT 
        s.course,
        COUNT(DISTINCT g.id) as total,
        SUM(CASE WHEN g.remarks = 'Passed' THEN 1 ELSE 0 END) as passed,
        SUM(CASE WHEN g.remarks = 'Failed' THEN 1 ELSE 0 END) as failed,
        ROUND(AVG(CASE WHEN g.final_grade IS NOT NULL THEN g.final_grade ELSE g.midterm_grade END), 2) as avg_grade
        FROM grades g
        JOIN students s ON g.student_id = s.id
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
        'message' => 'Error generating grades report: ' . $e->getMessage()
    ]);
}
?>

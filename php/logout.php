<?php
require_once 'config.php';

if (isLoggedIn()) {
    $conn = getDBConnection();
    if ($conn) {
        logAction($conn, $_SESSION['user_id'], 'User logged out');
        $conn->close();
    }
}

session_destroy();
header('Location: ../login.html');
exit();
?>

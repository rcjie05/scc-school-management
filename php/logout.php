<?php
require_once 'config.php';

// Log the logout before destroying session
if (isLoggedIn()) {
    $conn = getDBConnection();
    if ($conn) {
        logAction($conn, $_SESSION['user_id'], 'User logged out');
        $conn->close();
    }
}

// Destroy session completely
destroySession();

// Redirect to login
header('Location: ' . BASE_URL . '/login.html');
exit();
?>

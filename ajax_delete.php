<?php
// Turn off error display (but log them)
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Include your config
require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/json');

try {
    // Verify this is an AJAX request
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        throw new Exception("Invalid request");
    }

    if (!isset($_POST['ower_id']) || !is_numeric($_POST['ower_id'])) {
        throw new Exception("Invalid ower ID");
    }

    $ower_id = intval($_POST['ower_id']);
    
    $stmt = $conn->prepare("DELETE FROM other_owers WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $ower_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

if (isset($stmt)) $stmt->close();
exit();
?>
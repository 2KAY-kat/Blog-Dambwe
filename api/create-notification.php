<?php
require_once '../config/database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Log incoming data
error_log("Notification Data: " . print_r($data, true));

// Validate required fields
if (!isset($data['recipient_id'], $data['sender_id'], $data['type'], $data['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    error_log("Missing fields in notification data");
    exit;
}

try {
    // Don't create notification if sender is recipient
    if ($data['sender_id'] == $data['recipient_id']) {
        echo json_encode(['success' => true, 'message' => 'Skipped self-notification']);
        exit;
    }

    $query = "INSERT INTO notifications (recipient_id, sender_id, post_id, comment_id, type, message) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt === false) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($connection));
    }

    $post_id = isset($data['post_id']) ? $data['post_id'] : null;
    $comment_id = isset($data['comment_id']) ? $data['comment_id'] : null;

    mysqli_stmt_bind_param($stmt, "iiisss", 
        $data['recipient_id'],
        $data['sender_id'],
        $post_id,
        $comment_id,
        $data['type'],
        $data['message']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to execute statement: ' . mysqli_stmt_error($stmt));
    }

    echo json_encode([
        'success' => true, 
        'notification_id' => mysqli_insert_id($connection),
        'debug' => [
            'recipient_id' => $data['recipient_id'],
            'sender_id' => $data['sender_id'],
            'post_id' => $post_id,
            'type' => $data['type']
        ]
    ]);

} catch (Exception $e) {
    error_log("Notification Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

mysqli_stmt_close($stmt);

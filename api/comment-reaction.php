<?php
require '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user-id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Please login to interact']);
        exit;
    }

    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT);
    $type = htmlspecialchars(trim($_POST['type'] ?? ''), ENT_QUOTES, 'UTF-8');
    $user_id = $_SESSION['user-id'];
    
    if (empty($type) || !in_array($type, ['like', 'dislike'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid reaction type']);
        exit;
    }

    // Check for existing reaction
    $check_query = "SELECT type FROM comment_reactions 
                   WHERE comment_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $comment_id, $user_id);
    mysqli_stmt_execute($stmt);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($existing) {
        if ($existing['type'] === $type) {
            // Remove reaction
            $query = "DELETE FROM comment_reactions WHERE comment_id = ? AND user_id = ?";
            $params = [$comment_id, $user_id];
            $types = "ii";
        } else {
            // Update reaction
            $query = "UPDATE comment_reactions SET type = ? WHERE comment_id = ? AND user_id = ?";
            $params = [$type, $comment_id, $user_id];
            $types = "sii";
        }
    } else {
        // New reaction
        $query = "INSERT INTO comment_reactions (comment_id, user_id, type) VALUES (?, ?, ?)";
        $params = [$comment_id, $user_id, $type];
        $types = "iis";
    }

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (mysqli_stmt_execute($stmt)) {
        // Get updated counts
        $counts_query = "SELECT 
            (SELECT COUNT(*) FROM comment_reactions WHERE comment_id = ? AND type = 'like') as likes,
            (SELECT COUNT(*) FROM comment_reactions WHERE comment_id = ? AND type = 'dislike') as dislikes";
        
        $stmt = mysqli_prepare($connection, $counts_query);
        mysqli_stmt_bind_param($stmt, "ii", $comment_id, $comment_id);
        mysqli_stmt_execute($stmt);
        $counts = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        echo json_encode([
            'success' => true,
            'counts' => $counts,
            'state' => $existing && $existing['type'] === $type ? 'removed' : 'added'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update reaction']);
    }
}
?>

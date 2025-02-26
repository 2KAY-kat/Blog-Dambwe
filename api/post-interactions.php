<?php
require '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user-id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Please login to interact']);
        exit;
    }

    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    $type = htmlspecialchars(trim($_POST['type'] ?? ''), ENT_QUOTES, 'UTF-8');
    $user_id = $_SESSION['user-id'];
    
    if (empty($type) || !in_array($type, ['like', 'dislike'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid interaction type']);
        exit;
    }

    try {
        mysqli_begin_transaction($connection);

        // Delete any existing reactions from the old system
        $delete_old = "DELETE FROM likes_dislikes WHERE post_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($connection, $delete_old);
        mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
        mysqli_stmt_execute($stmt);

        // Process the new reaction
        $check_query = "SELECT type FROM post_reactions WHERE post_id = ? AND user_id = ? FOR UPDATE";
        $stmt = mysqli_prepare($connection, $check_query);
        mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
        mysqli_stmt_execute($stmt);
        $existing_reaction = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($existing_reaction) {
            if ($existing_reaction['type'] === $type) {
                $query = "DELETE FROM post_reactions WHERE post_id = ? AND user_id = ?";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
                $state = 'removed';
            } else {
                $query = "UPDATE post_reactions SET type = ? WHERE post_id = ? AND user_id = ?";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, "sii", $type, $post_id, $user_id);
                $state = 'added';
            }
        } else {
            $query = "INSERT INTO post_reactions (post_id, user_id, type) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, "iis", $post_id, $user_id, $type);
            $state = 'added';
        }

        mysqli_stmt_execute($stmt);

        // Get updated counts and recent reactors
        $counts_query = "SELECT 
            (SELECT COUNT(*) FROM post_reactions WHERE post_id = ? AND type = 'like') as likes_count,
            (SELECT COUNT(*) FROM post_reactions WHERE post_id = ? AND type = 'dislike') as dislikes_count,
            (SELECT COUNT(*) FROM post_reactions WHERE post_id = ?) as total_count";
        
        $stmt = mysqli_prepare($connection, $counts_query);
        mysqli_stmt_bind_param($stmt, "iii", $post_id, $post_id, $post_id);
        mysqli_stmt_execute($stmt);
        $counts = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        // Get recent reactors
        $recent_users_query = "SELECT u.id, u.firstname, u.lastname 
                              FROM users u 
                              JOIN post_reactions pr ON u.id = pr.user_id 
                              WHERE pr.post_id = ? 
                              ORDER BY pr.created_at DESC 
                              LIMIT 2";

        $stmt = mysqli_prepare($connection, $recent_users_query);
        mysqli_stmt_bind_param($stmt, "i", $post_id);
        mysqli_stmt_execute($stmt);
        $recent_result = mysqli_stmt_get_result($stmt);

        $recent_users = [];
        while ($user = mysqli_fetch_assoc($recent_result)) {
            $recent_users[] = $user;
        }

        $counts['recent_users'] = $recent_users;

        mysqli_commit($connection);
        
        echo json_encode([
            'success' => true,
            'counts' => $counts,
            'state' => $state
        ]);
    } catch (Exception $e) {
        mysqli_rollback($connection);
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update reaction']);
    }
}
?>
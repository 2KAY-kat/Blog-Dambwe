<?php
require '../config/database.php';
require '../helpers/format_time.php';

header('Content-Type: application/json');

if (!isset($_GET['post_id']) || !isset($_GET['type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

$post_id = filter_var($_GET['post_id'], FILTER_SANITIZE_NUMBER_INT);
$type = filter_var($_GET['type'], FILTER_SANITIZE_SPECIAL_CHARS);

try {
    // Get total count first
    $count_query = "SELECT COUNT(*) as count FROM post_reactions WHERE post_id = ? AND type = ?";
    $stmt = mysqli_prepare($connection, $count_query);
    mysqli_stmt_bind_param($stmt, "is", $post_id, $type);
    mysqli_stmt_execute($stmt);
    $total_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];

    // Get reactions with user details
    $query = "SELECT u.id, u.firstname, u.lastname, u.avatar, pr.created_at 
              FROM post_reactions pr 
              JOIN users u ON u.id = pr.user_id 
              WHERE pr.post_id = ? AND pr.type = ?
              ORDER BY pr.created_at DESC";

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "is", $post_id, $type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $reactions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['reaction_time'] = timeAgo($row['created_at']);
        unset($row['created_at']);
        $reactions[] = $row;
    }

    echo json_encode([
        'success' => true,
        'reactions' => $reactions,
        'total_count' => $total_count,
        'type' => $type
    ]);

} catch (Exception $e) {
    error_log("Error in get-reactions.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching reactions',
        'error' => $e->getMessage()
    ]);
}

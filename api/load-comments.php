<?php
require '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT);
$user_id = isset($_SESSION['user-id']) ? $_SESSION['user-id'] : 0;

// Get post reactions along with comments
$query = "SELECT c.*, u.firstname, u.lastname, u.avatar,
          (SELECT COUNT(*) FROM post_reactions WHERE post_id = c.post_id AND type = 'like') as post_likes_count,
          (SELECT COUNT(*) FROM post_reactions WHERE post_id = c.post_id AND type = 'dislike') as post_dislikes_count,
          (SELECT type FROM post_reactions WHERE post_id = c.post_id AND user_id = ?) as user_reaction
          FROM comments c
          JOIN users u ON c.user_id = u.id
          WHERE c.post_id = ?
          ORDER BY c.created_at DESC";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$response = [
    'comments' => [],
    'post_reactions' => [
        'likes_count' => 0,
        'dislikes_count' => 0,
        'user_reaction' => null
    ]
];

$first_row = true;
while ($row = mysqli_fetch_assoc($result)) {
    if ($first_row) {
        $response['post_reactions'] = [
            'likes_count' => $row['post_likes_count'],
            'dislikes_count' => $row['post_dislikes_count'],
            'user_reaction' => $row['user_reaction']
        ];
        $first_row = false;
    }
    
    // Remove post reaction data from comment object
    unset($row['post_likes_count']);
    unset($row['post_dislikes_count']);
    unset($row['user_reaction']);
    
    $response['comments'][] = $row;
}

echo json_encode($response);
?>

<?php
require '../config/database.php';

// Migrate old likes/dislikes to new post_reactions table
$migrate_query = "INSERT INTO post_reactions (post_id, user_id, type)
                 SELECT post_id, user_id,
                 CASE 
                    WHEN like_value = 1 THEN 'like'
                    WHEN like_value = -1 THEN 'dislike'
                 END as type
                 FROM likes_dislikes
                 WHERE like_value IN (1, -1)
                 ON DUPLICATE KEY UPDATE type = VALUES(type)";

mysqli_query($connection, $migrate_query);

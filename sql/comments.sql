DROP TABLE IF EXISTS comments;

CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    comment_text TEXT NOT NULL,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
);



MariaDB:3306/blog/		http://localhost/phpmyadmin/index.php?route=/database/sql&db=blog
Your SQL query has been executed successfully.

DESCRIBE comments;



id	int(11)	NO	PRI	NULL	auto_increment	
post_id	int(11)	NO	MUL	NULL		
user_id	int(11)	NO	MUL	NULL		
comment_text	text	NO		NULL		
parent_id	int(11)	YES	MUL	NULL		
date_time	datetime	NO		current_timestamp()		


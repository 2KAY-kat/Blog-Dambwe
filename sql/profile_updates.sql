-- First create/update users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS username VARCHAR(50) UNIQUE,
ADD COLUMN IF NOT EXISTS display_name VARCHAR(100),
ADD COLUMN IF NOT EXISTS bio TEXT;

-- Drop existing user_profiles table if it exists
DROP TABLE IF EXISTS user_profiles;

-- Create user_profiles table with proper structure
CREATE TABLE user_profiles (
    user_id INT PRIMARY KEY,
    theme_color VARCHAR(7) DEFAULT '#ffffff',
    privacy_setting ENUM('public', 'private', 'followers') DEFAULT 'public',
    social_links JSON NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
);

-- Insert default profiles for existing users
INSERT IGNORE INTO user_profiles (user_id, theme_color, privacy_setting, social_links)
SELECT id, '#ffffff', 'public', '{}' FROM users;

-- Create user_badges table
CREATE TABLE user_badges (
    user_id INT,
    badges JSON,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create profile_reactions table
CREATE TABLE profile_reactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    profile_id INT,
    reactor_id INT,
    reaction_type VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES users(id),
    FOREIGN KEY (reactor_id) REFERENCES users(id)
);

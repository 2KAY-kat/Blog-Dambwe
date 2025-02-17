<?php
require '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $title = filter_var($data['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    if ($title) {
        // Check if category already exists
        $check_query = "SELECT id FROM categories WHERE title='$title'";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Category already exists'
            ]);
            exit;
        }
        
        $query = "INSERT INTO categories (title) VALUES ('$title')";
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'id' => mysqli_insert_id($connection),
                'title' => $title
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => mysqli_error($connection)
            ]);
        }
    }
}

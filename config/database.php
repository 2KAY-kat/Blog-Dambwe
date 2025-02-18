<?php
require 'constants.php';

// Create connection
$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (mysqli_errno($connection)) {
    die(mysqli_connect_error());
}

// Function to check if a column exists in a table
function columnExists($connection, $table, $column) {
    $query = "SHOW COLUMNS FROM $table LIKE '$column'";
    $result = mysqli_query($connection, $query);
    return (mysqli_num_rows($result) > 0);
}

// Add cover_photo column if it doesn't exist
if (!columnExists($connection, 'users', 'cover_photo')) {
    $addColumnQuery = "ALTER TABLE users ADD COLUMN cover_photo VARCHAR(255) NULL";
    mysqli_query($connection, $addColumnQuery);
}
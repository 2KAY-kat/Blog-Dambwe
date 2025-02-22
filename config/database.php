<?php
require 'constants.php';

//connection to db

$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if(mysqli_error($connection)) {
    die(mysqli_error($connection));
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
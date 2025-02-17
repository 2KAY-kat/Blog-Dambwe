<?php
function timeAgo($datetime) {  // Changed function name from format_time to timeAgo
    $timestamp = strtotime($datetime);
    $current_time = time();
    $time_difference = $current_time - $timestamp;

    if ($time_difference < 60) {
        return "Just now";
    } elseif ($time_difference < 3600) {
        $minutes = floor($time_difference / 60);
        return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
    } elseif ($time_difference < 86400) {
        $hours = floor($time_difference / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($time_difference < 604800) {
        $days = floor($time_difference / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    } else {
        return date("M d, Y - H:i", $timestamp);
    }
}

// Add format_time as an alias for timeAgo for backward compatibility
function format_time($datetime) {
    return timeAgo($datetime);
}

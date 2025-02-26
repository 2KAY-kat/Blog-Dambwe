<?php
require '../config/database.php';
require '../helpers/format_time.php';

// Test configuration
$test_post_id = 11;
$test_users = [
    ['id' => 1, 'firstname' => 'Test', 'lastname' => 'User1', 'type' => 'like'],
    ['id' => 2, 'firstname' => 'Test', 'lastname' => 'User2', 'type' => 'dislike']
];

function runTest($name, $callback) {
    echo "\n🧪 Testing: $name\n";
    try {
        $result = $callback();
        echo "✅ PASSED\n";
        return true;
    } catch (Exception $e) {
        echo "❌ FAILED: {$e->getMessage()}\n";
        return false;
    }
}

// Start Tests
echo "🚀 Starting Post Reactions Page Tests\n";
echo "=====================================\n";

$totalTests = 0;
$passedTests = 0;

// Test 1: Check if post exists
$totalTests++;
$result = runTest("Verify post exists", function() use ($test_post_id, $connection) {
    $query = "SELECT id, title FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $test_post_id);
    mysqli_stmt_execute($stmt);
    $post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if (!$post) {
        throw new Exception("Test post not found");
    }
    echo "Found post: " . $post['title'] . "\n";
    return true;
});
if ($result) $passedTests++;

// Test 2: Check likes count
$totalTests++;
$result = runTest("Verify likes count", function() use ($test_post_id, $connection) {
    $query = "SELECT COUNT(*) as count FROM post_reactions WHERE post_id = ? AND type = 'like'";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $test_post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    echo "Found {$result['count']} likes\n";
    return true;
});
if ($result) $passedTests++;

// Test 3: Check dislikes count
$totalTests++;
$result = runTest("Verify dislikes count", function() use ($test_post_id, $connection) {
    $query = "SELECT COUNT(*) as count FROM post_reactions WHERE post_id = ? AND type = 'dislike'";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $test_post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    echo "Found {$result['count']} dislikes\n";
    return true;
});
if ($result) $passedTests++;

// Test 4: Check reaction timestamps are formatted correctly
$totalTests++;
$result = runTest("Verify reaction timestamps", function() use ($test_post_id, $connection) {
    $query = "SELECT created_at FROM post_reactions WHERE post_id = ? LIMIT 1";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $test_post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if ($result) {
        $formatted_time = timeAgo($result['created_at']);
        echo "Formatted time: $formatted_time\n";
        
        if (empty($formatted_time)) {
            throw new Exception("Time formatting failed");
        }
    }
    return true;
});
if ($result) $passedTests++;

// Test 5: Test API Response Structure
$totalTests++;
$result = runTest("Verify API response structure", function() use ($test_post_id) {
    // Test likes
    $likes_url = "http://localhost/Blog-Dambwe/api/get-reactions.php?post_id=$test_post_id&type=like";
    $likes_response = json_decode(file_get_contents($likes_url), true);
    
    if (!isset($likes_response['success'], $likes_response['reactions'], $likes_response['total_count'])) {
        throw new Exception("Invalid API response structure for likes");
    }
    
    // Test dislikes
    $dislikes_url = "http://localhost/Blog-Dambwe/api/get-reactions.php?post_id=$test_post_id&type=dislike";
    $dislikes_response = json_decode(file_get_contents($dislikes_url), true);
    
    if (!isset($dislikes_response['success'], $dislikes_response['reactions'], $dislikes_response['total_count'])) {
        throw new Exception("Invalid API response structure for dislikes");
    }
    
    echo "API responses properly structured\n";
    return true;
});
if ($result) $passedTests++;

// Test 6: Verify user details in reactions
$totalTests++;
$result = runTest("Verify user details in reactions", function() use ($test_post_id, $connection) {
    $query = "SELECT u.firstname, u.lastname, u.avatar, pr.type 
              FROM post_reactions pr 
              JOIN users u ON pr.user_id = u.id 
              WHERE pr.post_id = ? 
              LIMIT 1";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $test_post_id);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if ($user) {
        if (empty($user['firstname']) || empty($user['lastname'])) {
            throw new Exception("Missing user name information");
        }
        echo "User details verified for: {$user['firstname']} {$user['lastname']}\n";
    }
    return true;
});
if ($result) $passedTests++;

// Test Summary
echo "\n📊 Test Summary\n";
echo "=====================================\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100) . "%\n";
?>

<style>
    body {
        font-family: monospace;
        white-space: pre;
        padding: 20px;
        background: #1e1e1e;
        color: #fff;
    }
    .success { color: #4CAF50; }
    .error { color: #f44336; }
</style>

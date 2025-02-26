<?php
require '../config/database.php';


// Test configuration
$test_post_id = 15; // Use your existing post ID
$test_user_id = 1;  // Use an existing user ID
$_SESSION['user-id'] = $test_user_id; // Simulate logged in user

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

function assertEquals($expected, $actual, $message = "") {
    if ($expected !== $actual) {
        throw new Exception("Expected: $expected, Got: $actual. $message");
    }
}

// Clean up test data
function cleanupTestReactions($post_id, $user_id) {
    global $connection;
    $query = "DELETE FROM post_reactions WHERE post_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
    mysqli_stmt_execute($stmt);
}

// Start Tests
echo "🚀 Starting Reactions System Tests\n";
echo "=================================\n";

$totalTests = 0;
$passedTests = 0;

// Test 1: Check if we can add a like
$totalTests++;
$result = runTest("Adding a like", function() use ($test_post_id, $test_user_id) {
    global $connection;
    cleanupTestReactions($test_post_id, $test_user_id);
    
    $query = "INSERT INTO post_reactions (post_id, user_id, type) VALUES (?, ?, 'like')";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "ii", $test_post_id, $test_user_id);
    mysqli_stmt_execute($stmt);
    
    // Verify like was added
    $check_query = "SELECT COUNT(*) as count FROM post_reactions WHERE post_id = ? AND user_id = ? AND type = 'like'";
    $stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $test_post_id, $test_user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    assertEquals(1, $result['count'], "Like should be added");
});
if ($result) $passedTests++;

// Test 2: Check if we can switch from like to dislike
$totalTests++;
$result = runTest("Switching from like to dislike", function() use ($test_post_id, $test_user_id) {
    global $connection;
    
    // Add a like first
    cleanupTestReactions($test_post_id, $test_user_id);
    $query = "INSERT INTO post_reactions (post_id, user_id, type) VALUES (?, ?, 'like')";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "ii", $test_post_id, $test_user_id);
    mysqli_stmt_execute($stmt);
    
    // Switch to dislike
    $query = "UPDATE post_reactions SET type = 'dislike' WHERE post_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "ii", $test_post_id, $test_user_id);
    mysqli_stmt_execute($stmt);
    
    // Verify the switch
    $check_query = "SELECT type FROM post_reactions WHERE post_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $test_post_id, $test_user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    assertEquals('dislike', $result['type'], "Reaction should be switched to dislike");
});
if ($result) $passedTests++;

// Test 3: Check if we can remove a reaction
$totalTests++;
$result = runTest("Removing a reaction", function() use ($test_post_id, $test_user_id) {
    global $connection;
    
    // Add a reaction first
    cleanupTestReactions($test_post_id, $test_user_id);
    $query = "INSERT INTO post_reactions (post_id, user_id, type) VALUES (?, ?, 'like')";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "ii", $test_post_id, $test_user_id);
    mysqli_stmt_execute($stmt);
    
    // Remove the reaction
    $query = "DELETE FROM post_reactions WHERE post_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "ii", $test_post_id, $test_user_id);
    mysqli_stmt_execute($stmt);
    
    // Verify removal
    $check_query = "SELECT COUNT(*) as count FROM post_reactions WHERE post_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $test_post_id, $test_user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    assertEquals(0, $result['count'], "Reaction should be removed");
});
if ($result) $passedTests++;

// Test 4: Check reaction counts
$totalTests++;
$result = runTest("Checking reaction counts", function() use ($test_post_id) {
    global $connection;
    
    $query = "SELECT 
        (SELECT COUNT(*) FROM post_reactions WHERE post_id = ? AND type = 'like') as likes_count,
        (SELECT COUNT(*) FROM post_reactions WHERE post_id = ? AND type = 'dislike') as dislikes_count";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "ii", $test_post_id, $test_post_id);
    mysqli_stmt_execute($stmt);
    $counts = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if (!isset($counts['likes_count']) || !isset($counts['dislikes_count'])) {
        throw new Exception("Count queries failed");
    }
    
    echo "Current likes: {$counts['likes_count']}, dislikes: {$counts['dislikes_count']}\n";
    return true;
});
if ($result) $passedTests++;

// Test 5: Test API endpoint
$totalTests++;
$result = runTest("Testing API endpoint", function() use ($test_post_id) {
    $url = "http://localhost/Blog-Dambwe/api/get-reactions.php?post_id=$test_post_id&type=likes";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    if (!isset($data['success']) || !$data['success']) {
        throw new Exception("API request failed");
    }
    
    if (!isset($data['total_count']) || !isset($data['reactions'])) {
        throw new Exception("API response missing required fields");
    }
    
    echo "API returned {$data['total_count']} reactions\n";
    return true;
});
if ($result) $passedTests++;

// Clean up after tests
cleanupTestReactions($test_post_id, $test_user_id);

// Test Summary
echo "\n📊 Test Summary\n";
echo "=================================\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100) . "%\n";
?>

<!-- Add visual output styling -->
<style>
    body {
        font-family: monospace;
        white-space: pre;
        padding: 20px;
        background: #1e1e1e;
        color: #fff;
    }
</style>

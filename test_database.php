<?php
// Test database connection and table existence
$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Database Connection Test</h2>";
    echo "<p style='color: green;'>âœ… Database connection successful!</p>";
    
    // Check if tables exist
    $tables = ['registrations', 'contact_messages', 'newsletter_subscribers', 'admin_users'];
    
    echo "<h3>Table Status:</h3>";
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>âœ… Table '$table' exists with $count records</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>âŒ Table '$table' does not exist or has issues: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test inserting a sample contact message
    echo "<h3>Testing Contact Form:</h3>";
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute(['Test User', 'test@example.com', 'Test Subject', 'This is a test message']);
        echo "<p style='color: green;'>âœ… Sample contact message inserted successfully!</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>âŒ Failed to insert contact message: " . $e->getMessage() . "</p>";
    }
    
    // Test inserting a sample newsletter subscription
    echo "<h3>Testing Newsletter Form:</h3>";
    try {
        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW())");
        $stmt->execute(['newsletter@example.com']);
        echo "<p style='color: green;'>âœ… Sample newsletter subscription inserted successfully!</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>âŒ Failed to insert newsletter subscription: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>Next Steps:</h3>";
    echo "<p>1. If tables don't exist, run <a href='setup_database.php'>setup_database.php</a></p>";
    echo "<p>2. Test the contact form on your website</p>";
    echo "<p>3. Check the admin panel at <a href='admin_complete.php'>admin_complete.php</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Database Connection Failed</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials in the PHP files.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="icon" href="assets/images/logo/logo.svg" type="image/svg+xml">
    <title>TechvReach Database Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h2, h3 { color: #08503F; }
        p { margin: 10px 0; }
        a { color: #0A6765; }
    </style>
</head>
<body>
    <h1>TechvReach Database Test Results</h1>
</body>
</html>








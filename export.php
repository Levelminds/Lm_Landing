<?php
// Database configuration - Update these with your Hostinger database details
$servername = 'localhost';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';
$dbname = 'u420143207_LM_landing';


try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="levelminds_registrations_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, ['ID', 'Email', 'Role', 'Registration Date']);
    
    // Get all registrations
    $stmt = $pdo->query("SELECT * FROM registrations ORDER BY created_at DESC");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['email'],
            $row['role'],
            $row['created_at']
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>









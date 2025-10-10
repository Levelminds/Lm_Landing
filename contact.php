<?php
require __DIR__ . '/mailer.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!is_array($input)) {
        if (!empty($_POST)) {
            $input = $_POST;
        } else {
            parse_str($raw, $input);
        }
    }
    if (!is_array($input)) {
        throw new Exception('Invalid request input');
    }

    $required = ['name', 'email', 'message'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }

    $name = trim($input['name']);
    $email = trim($input['email']);
    $subject = isset($input['subject']) ? trim($input['subject']) : '';
    $message = trim($input['message']);
    $phone = isset($input['phone']) ? trim($input['phone']) : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $email, $subject, $message]);

    $emailSubject = 'New LevelMinds contact form submission';
    $emailBody = "Name: $name\n"
        . "Email: $email\n"
        . "Phone: " . ($phone !== '' ? $phone : 'Not provided') . "\n"
        . "Subject: " . ($subject !== '' ? $subject : 'Not provided') . "\n\n"
        . "Message:\n$message\n";

    $emailSent = sendLevelMindsMail($emailSubject, $emailBody, $email, $name);

    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully! We will get back to you soon.',
        'email_sent' => $emailSent,
        'id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>




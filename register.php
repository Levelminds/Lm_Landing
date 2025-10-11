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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
        throw new Exception('Invalid input');
    }

    $required_fields = ['name', 'email', 'mobile', 'role'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }

    $name = trim($input['name']);
    $email = trim($input['email']);
    $mobile = trim($input['mobile']);
    $role = trim($input['role']);
    $message = isset($input['message']) ? trim($input['message']) : '';
    $specialization = isset($input['specialization']) ? trim($input['specialization']) : '';
    $company = $role === 'business' ? trim($input['company'] ?? '') : '';
    $technician_count = $role === 'business' ? trim($input['technician_count'] ?? '') : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if (!preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $mobile)) {
        throw new Exception('Invalid mobile number format');
    }

    $stmt = $pdo->prepare('SELECT id FROM registrations WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Email already registered');
    }

    $stmt = $pdo->prepare('INSERT INTO registrations (name, email, mobile, role, specialization, company, technician_count, message, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$name, $email, $mobile, $role, $specialization, $company, $technician_count, $message]);

    $subject = 'New LevelMinds registration';
    $body = "A new registration has been submitted on levelminds.in:\n\n" .
            "Name: $name\n" .
            "Email: $email\n" .
            "Mobile: $mobile\n" .
            "Role: $role\n" .
            ($specialization ? "Specialization: $specialization\n" : '') .
            ($company ? "Company: $company\n" : '') .
            ($technician_count ? "Technician count: $technician_count\n" : '') .
            ($message ? "\nMessage:\n$message\n" : '');
    $emailSent = sendLevelMindsMail($subject, $body, $email, $name);

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! We will contact you soon.',
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




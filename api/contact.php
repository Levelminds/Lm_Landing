<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../mailer.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $pdo = lm_db();

    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!is_array($input)) {
        if (!empty($_POST)) {
            $input = $_POST;
        } else {
            parse_str($raw ?: '', $input);
        }
    }

    if (!is_array($input)) {
        throw new InvalidArgumentException('Invalid request input');
    }

    $required = ['name', 'email', 'message'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new InvalidArgumentException("Field '{$field}' is required");
        }
    }

    $name = trim((string) $input['name']);
    $email = trim((string) $input['email']);
    $subject = isset($input['subject']) ? trim((string) $input['subject']) : '';
    $message = trim((string) $input['message']);
    $phone = isset($input['phone']) ? trim((string) $input['phone']) : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Invalid email format');
    }

    $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([$name, $email, $subject, $message]);

    $emailSubject = 'New LevelMinds contact form submission';
    $emailBody = "Name: {$name}\n" .
        "Email: {$email}\n" .
        'Phone: ' . ($phone !== '' ? $phone : 'Not provided') . "\n" .
        'Subject: ' . ($subject !== '' ? $subject : 'Not provided') . "\n\n" .
        "Message:\n{$message}\n";

    $emailSent = sendLevelMindsMail($emailSubject, $emailBody, $email, $name);

    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully! We will get back to you soon.',
        'email_sent' => $emailSent,
    ]);
} catch (Throwable $exception) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => $exception->getMessage(),
    ]);
}

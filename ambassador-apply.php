<?php
require __DIR__ . '/mailer.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!is_array($data)) {
    parse_str($input, $data);
}

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$college = trim($data['college'] ?? '');
$linkedin = trim($data['linkedin'] ?? '');
$message = trim($data['message'] ?? '');

if ($name === '' || $email === '' || $college === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Name, email, and college are required.']);
    exit;
}

$recipient = getenv('AMBASSADOR_RECIPIENT') ?: 'support@levelminds.in';
$subject = 'New Campus Ambassador Application';

$body = "A new ambassador application has been submitted:\n\n" .
    "Name: $name\n" .
    "Email: $email\n" .
    ($phone ? "Phone: $phone\n" : '') .
    "College: $college\n" .
    ($linkedin ? "LinkedIn: $linkedin\n" : '') .
    "Message:\n$message\n";

$emailSent = sendLevelMindsMail($subject, $body, $email, $name, $recipient, 'LevelMinds');

if ($emailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thanks! We received your application and will be in touch soon.',
        'email_sent' => true
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to send email right now. Please try again later.',
        'email_sent' => false
    ]);
}
?>

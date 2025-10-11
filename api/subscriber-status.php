<?php
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        throw new Exception('Invalid payload', 400);
    }

    $email = isset($input['email']) ? trim($input['email']) : '';
    if ($email === '') {
        throw new Exception('Email address is required.', 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please provide a valid email address.', 400);
    }

    $dsn = 'mysql:host=localhost;dbname=u420143207_LM_landing;charset=utf8mb4';
    $user = 'u420143207_lmlanding';
    $pass = 'Levelminds@2024';

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->prepare('SELECT status FROM newsletter_subscribers WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $status = $stmt->fetchColumn();

    $isActive = is_string($status) && strtolower($status) === 'active';

    echo json_encode([
        'ok' => true,
        'active' => $isActive,
    ]);
} catch (Exception $e) {
    $code = $e->getCode();
    if ($code < 400 || $code >= 600) {
        $code = 500;
    }
    http_response_code($code);
    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage(),
    ]);
}

<?php
require __DIR__ . '/mailer.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';
$notifyEmail = 'support@levelminds.in';

function wantsJsonResponse(): bool
{
    $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
    $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
    $requestedWith = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');

    return strpos($accept, 'application/json') !== false
        || strpos($contentType, 'application/json') !== false
        || $requestedWith === 'xmlhttprequest';
}

function resolveRedirectTarget(): ?string
{
    $candidate = $_POST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? null;
    if ($candidate === null) {
        return null;
    }

    $candidate = trim($candidate);
    if ($candidate === '') {
        return null;
    }

    $parts = parse_url($candidate);
    if ($parts === false) {
        return null;
    }

    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (isset($parts['host']) && $parts['host'] !== '' && strcasecmp($parts['host'], $host) !== 0) {
        return null;
    }

    if (isset($parts['scheme']) && $parts['scheme'] !== '' && !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
        return null;
    }

    return $candidate;
}

function withNewsletterStatus(string $url, string $status): string
{
    $fragment = '';
    $hashPos = strpos($url, '#');
    if ($hashPos !== false) {
        $fragment = substr($url, $hashPos);
        $url = substr($url, 0, $hashPos);
    }

    $parts = parse_url($url);
    if ($parts === false) {
        $parts = [];
    }

    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }
    $query['newsletter'] = $status;
    $queryString = http_build_query($query);

    $result = '';

    if (!empty($parts['scheme'])) {
        $result .= $parts['scheme'] . '://';
    }

    if (!empty($parts['user'])) {
        $result .= $parts['user'];
        if (!empty($parts['pass'])) {
            $result .= ':' . $parts['pass'];
        }
        $result .= '@';
    }

    if (!empty($parts['host'])) {
        $result .= $parts['host'];
    }

    if (!empty($parts['port'])) {
        $result .= ':' . $parts['port'];
    }

    if (isset($parts['path'])) {
        $result .= $parts['path'];
    }

    if ($queryString !== '') {
        $result .= '?' . $queryString;
    }

    return $result . $fragment;
}

function mapStatusFromCode(int $code): string
{
    switch ($code) {
        case 409:
            return 'exists';
        case 422:
            return 'invalid';
        default:
            return 'error';
    }
}

$wantsJson = wantsJsonResponse();
$response = [];
$statusParam = 'error';
$httpStatus = 500;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!is_array($input) || empty($input)) {
        if (!empty($_POST)) {
            $input = $_POST;
        } else {
            parse_str($raw, $input);
        }
    }

    if (!is_array($input) || empty($input['email'])) {
        throw new Exception('Email is required', 422);
    }

    $email = trim($input['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format', 422);
    }

    $stmt = $pdo->prepare('SELECT id FROM newsletter_subscribers WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Email already subscribed to newsletter', 409);
    }

    $stmt = $pdo->prepare('INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW())');
    $stmt->execute([$email]);

    $subject = 'New newsletter subscriber';
    $body = "A new subscriber has joined the LevelMinds newsletter.\n\nEmail: $email\nSubscribed: " . date('Y-m-d H:i:s');
    $emailSent = sendLevelMindsMail($subject, $body, $email, $email, $notifyEmail, 'LevelMinds');

    $response = [
        'success' => true,
        'message' => 'Successfully subscribed to newsletter!',
        'email_sent' => $emailSent,
    ];
    $statusParam = 'success';
    $httpStatus = 200;

} catch (PDOException $e) {
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'email_sent' => false,
    ];
    $statusParam = 'error';
    $httpStatus = 500;

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'email_sent' => false,
    ];
    $statusParam = mapStatusFromCode($e->getCode());
    $httpStatus = $e->getCode() >= 400 ? $e->getCode() : 400;
}

$redirectTarget = resolveRedirectTarget();

if (!$wantsJson && $redirectTarget) {
    header('Location: ' . withNewsletterStatus($redirectTarget, $statusParam), true, 303);
    exit;
}

http_response_code($httpStatus);
header('Content-Type: application/json');
echo json_encode($response);
?>




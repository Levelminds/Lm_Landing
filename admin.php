<?php
session_start();

$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($login === '' || $pass === '') {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = :login OR email = :login LIMIT 1');
            $stmt->execute(['login' => $login]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                $hash = $admin['password_hash'];
                $valid = false;

                if (strlen($hash) === 60 && password_verify($pass, $hash)) {
                    $valid = true;
                }

                if (!$valid && strlen($hash) === 64 && hash('sha256', $pass) === $hash) {
                    $valid = true;
                }

                if ($valid) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];
                    header('Location: dashboard.php');
                    exit;
                }
            }

            $error = 'Invalid username or password.';
        } catch (PDOException $e) {
            $error = 'Unable to connect to the database. Please update your credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LevelMinds Admin Login</title>
  <link rel="icon" href="assets/images/logo/logo.svg" type="image/svg+xml">
  <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body {
      background: radial-gradient(circle at top, rgba(60,141,255,0.18), transparent 60%), #f5f7fb;
      font-family: 'Public Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-card {
      background: #ffffff;
      border-radius: 20px;
      box-shadow: 0 32px 80px rgba(15, 46, 91, 0.12);
      width: 100%;
      max-width: 420px;
      padding: 36px 32px;
    }
    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
    }
    .brand span {
      font-size: 1.2rem;
      font-weight: 700;
      color: #0F1D3B;
    }
    .form-control {
      border-radius: 12px;
      padding: 12px 14px;
    }
    .btn-primary {
      background: #3C8DFF;
      border-color: #3C8DFF;
      border-radius: 12px;
      padding: 12px;
      font-weight: 600;
    }
    .btn-primary:hover {
      background: #1E6AE1;
      border-color: #1E6AE1;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="brand">
      <img src="assets/images/logo/logo.svg" alt="LevelMinds" height="42">
      <span>LevelMinds Admin</span>
    </div>
    <h1 class="h4 mb-3" style="font-weight: 700; color: #0F1D3B;">Welcome back</h1>
    <p class="text-muted mb-4">Sign in to manage blog content and track platform performance.</p>

    <?php if ($error): ?>
      <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label class="form-label">Username or Email</label>
        <input type="text" class="form-control" name="username" required>
      </div>
      <div class="mb-4">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Log In</button>
    </form>
  </div>

  <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>










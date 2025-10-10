<?php
// Complete admin panel for TechvReach with all forms management
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

// Simple authentication
if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['login'])) {
        $admin_username = $_POST['username'] ?? '';
        $admin_password = $_POST['password'] ?? '';

        if ($admin_username === 'support@levelminds.in' && $admin_password === 'Adminpassword@Levelminds@2023') {
            $_SESSION['admin_logged_in'] = true;
        } else {
            $error = 'Invalid credentials';
        }
    }

    if (!isset($_SESSION['admin_logged_in'])) {
        // Show login form
        ?>
        <!DOCTYPE html>
        <html>
        <head>
  <link rel="icon" href="assets/images/logo/logo.svg" type="image/svg+xml">
            <title>TechvReach Admin Login</title>
            <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container">
                <div class="row justify-content-center mt-5">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Admin Login</h4>
                            </div>
                            <div class="card-body">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label>Username</label>
                                        <input type="text" name="username" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                                </form>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        Default credentials:<br>
                                        Username: support@levelminds.in<br>
                                        Password: Adminpassword@Levelminds@2023
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div data-global-footer></div>
    <script src="assets/js/footer.js"></script>
</body>
        </html>
        <?php
        exit;
    }
}
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get current view
    $current_view = $_GET['view'] ?? 'registrations';
    
    // Handle various status updates
    if (isset($_POST['update_status'])) {
        $id = $_POST['registration_id'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE registrations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        $success_message = "Registration status updated successfully!";
    }
    
    if (isset($_POST['update_contact_status'])) {
        $id = $_POST['contact_id'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        $success_message = "Contact message status updated successfully!";
    }
    
    if (isset($_POST['unsubscribe_newsletter'])) {
        $id = $_POST['subscriber_id'];
        
        $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        
        $success_message = "Subscriber unsubscribed successfully!";
    }
    
    // Get data based on current view
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    if ($current_view === 'registrations') {
        // Registrations logic (existing)
        $filter_role = $_GET['role'] ?? '';
        $filter_status = $_GET['status'] ?? '';
        
        $where_conditions = [];
        $params = [];
        
        if ($filter_role) {
            $where_conditions[] = "role = ?";
            $params[] = $filter_role;
        }
        
        if ($filter_status) {
            $where_conditions[] = "status = ?";
            $params[] = $filter_status;
        }
        
        $where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        $count_sql = "SELECT COUNT(*) FROM registrations $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetchColumn();
        
        $sql = "SELECT * FROM registrations $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } elseif ($current_view === 'contacts') {
        // Contact messages logic
        $filter_status = $_GET['status'] ?? '';
        
        $where_conditions = [];
        $params = [];
        
        if ($filter_status) {
            $where_conditions[] = "status = ?";
            $params[] = $filter_status;
        }
        
        $where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        $count_sql = "SELECT COUNT(*) FROM contact_messages $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetchColumn();
        
        $sql = "SELECT * FROM contact_messages $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } elseif ($current_view === 'newsletter') {
        // Newsletter subscribers logic
        $filter_status = $_GET['status'] ?? '';
        
        $where_conditions = [];
        $params = [];
        
        if ($filter_status) {
            $where_conditions[] = "status = ?";
            $params[] = $filter_status;
        }
        
        $where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        $count_sql = "SELECT COUNT(*) FROM newsletter_subscribers $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetchColumn();
        
        $sql = "SELECT * FROM newsletter_subscribers $where_clause ORDER BY subscribed_at DESC LIMIT $per_page OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $total_pages = ceil($total_records / $per_page);
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="icon" href="assets/images/logo/logo.svg" type="image/svg+xml">
    <title>TechvReach Admin Panel</title>
    <link href="assets/vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendors/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .table th { font-size: 0.9rem; }
        .table td { font-size: 0.85rem; vertical-align: middle; }
        .form-select-sm { font-size: 0.8rem; }
        .nav-tabs .nav-link { color: #495057; }
        .nav-tabs .nav-link.active { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">
                <i class="bi bi-gear-fill me-2"></i>TechvReach Admin Panel
            </span>
            <a href="?logout=1" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </nav>
    
    <div class="container mt-4">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_view === 'registrations' ? 'active' : ''; ?>" href="?view=registrations">
                    <i class="bi bi-people me-1"></i>Registrations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_view === 'contacts' ? 'active' : ''; ?>" href="?view=contacts">
                    <i class="bi bi-envelope me-1"></i>Contact Messages
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_view === 'newsletter' ? 'active' : ''; ?>" href="?view=newsletter">
                    <i class="bi bi-newspaper me-1"></i>Newsletter Subscribers
                </a>
            </li>
        </ul>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <?php if ($current_view === 'registrations'): ?>
                    <h2><i class="bi bi-people me-2"></i>Registrations Management</h2>
                    
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <input type="hidden" name="view" value="registrations">
                        <div class="col-md-3">
                            <select name="role" class="form-select">
                                <option value="">All Roles</option>
                                <option value="technician" <?php echo ($_GET['role'] ?? '') === 'technician' ? 'selected' : ''; ?>>Technician</option>
                                <option value="business" <?php echo ($_GET['role'] ?? '') === 'business' ? 'selected' : ''; ?>>Business</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="contacted" <?php echo ($_GET['status'] ?? '') === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                <option value="approved" <?php echo ($_GET['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo ($_GET['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-info fs-6">
                                <i class="bi bi-graph-up me-1"></i>Total: <?php echo $total_records; ?> registrations
                            </span>
                        </div>
                    </form>
                    
                    <!-- Registrations Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Role</th>
                                    <th>Details</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $reg): ?>
                                <tr>
                                    <td><strong>#<?php echo $reg['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($reg['name']); ?></td>
                                    <td>
                                        <div>
                                            <a href="mailto:<?php echo htmlspecialchars($reg['email']); ?>" class="text-decoration-none">
                                                <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($reg['email']); ?>
                                            </a>
                                        </div>
                                        <div>
                                            <a href="tel:<?php echo htmlspecialchars($reg['mobile']); ?>" class="text-decoration-none">
                                                <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($reg['mobile']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $reg['role'] === 'technician' ? 'primary' : 'success'; ?>">
                                            <i class="bi bi-<?php echo $reg['role'] === 'technician' ? 'tools' : 'building'; ?> me-1"></i>
                                            <?php echo ucfirst($reg['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><strong>Specialization:</strong><br><?php echo htmlspecialchars($reg['specialization']); ?></small>
                                        <?php if ($reg['role'] === 'business'): ?>
                                            <br><small>
                                                <strong>Company:</strong> <?php echo htmlspecialchars($reg['company']); ?><br>
                                                <strong>Technicians:</strong> <?php echo htmlspecialchars($reg['technician_count']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($reg['message']): ?>
                                            <small title="<?php echo htmlspecialchars($reg['message']); ?>">
                                                <?php echo htmlspecialchars(substr($reg['message'], 0, 50)); ?>
                                                <?php if (strlen($reg['message']) > 50): ?>...<?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">No message</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $reg['status'] === 'pending' ? 'warning' : 
                                                ($reg['status'] === 'approved' ? 'success' : 
                                                ($reg['status'] === 'contacted' ? 'info' : 'danger')); 
                                        ?>">
                                            <?php echo ucfirst($reg['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo date('M j, Y', strtotime($reg['created_at'])); ?><br>
                                            <?php echo date('H:i', strtotime($reg['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="registration_id" value="<?php echo $reg['id']; ?>">
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 120px;">
                                                <option value="pending" <?php echo $reg['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="contacted" <?php echo $reg['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                                <option value="approved" <?php echo $reg['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="rejected" <?php echo $reg['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                
                <?php elseif ($current_view === 'contacts'): ?>
                    <h2><i class="bi bi-envelope me-2"></i>Contact Messages</h2>
                    
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <input type="hidden" name="view" value="contacts">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="new" <?php echo ($_GET['status'] ?? '') === 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="read" <?php echo ($_GET['status'] ?? '') === 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="replied" <?php echo ($_GET['status'] ?? '') === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                <option value="closed" <?php echo ($_GET['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                        </div>
                        <div class="col-md-7 text-end">
                            <span class="badge bg-info fs-6">
                                <i class="bi bi-envelope me-1"></i>Total: <?php echo $total_records; ?> messages
                            </span>
                        </div>
                    </form>
                    
                    <!-- Contact Messages Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $contact): ?>
                                <tr>
                                    <td><strong>#<?php echo $contact['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($contact['email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($contact['subject']): ?>
                                            <small><?php echo htmlspecialchars($contact['subject']); ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">No subject</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small title="<?php echo htmlspecialchars($contact['message']); ?>">
                                            <?php echo htmlspecialchars(substr($contact['message'], 0, 100)); ?>
                                            <?php if (strlen($contact['message']) > 100): ?>...<?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $contact['status'] === 'new' ? 'danger' : 
                                                ($contact['status'] === 'read' ? 'warning' : 
                                                ($contact['status'] === 'replied' ? 'info' : 'success')); 
                                        ?>">
                                            <?php echo ucfirst($contact['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo date('M j, Y', strtotime($contact['created_at'])); ?><br>
                                            <?php echo date('H:i', strtotime($contact['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 100px;">
                                                <option value="new" <?php echo $contact['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                                <option value="read" <?php echo $contact['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                                <option value="replied" <?php echo $contact['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                                <option value="closed" <?php echo $contact['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                            </select>
                                            <input type="hidden" name="update_contact_status" value="1">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                
                <?php elseif ($current_view === 'newsletter'): ?>
                    <h2><i class="bi bi-newspaper me-2"></i>Newsletter Subscribers</h2>
                    
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <input type="hidden" name="view" value="newsletter">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="unsubscribed" <?php echo ($_GET['status'] ?? '') === 'unsubscribed' ? 'selected' : ''; ?>>Unsubscribed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                        </div>
                        <div class="col-md-7 text-end">
                            <span class="badge bg-info fs-6">
                                <i class="bi bi-newspaper me-1"></i>Total: <?php echo $total_records; ?> subscribers
                            </span>
                        </div>
                    </form>
                    
                    <!-- Newsletter Subscribers Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Subscribed Date</th>
                                    <th>Unsubscribed Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $subscriber): ?>
                                <tr>
                                    <td><strong>#<?php echo $subscriber['id']; ?></strong></td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($subscriber['email']); ?>" class="text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($subscriber['email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $subscriber['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($subscriber['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo date('M j, Y', strtotime($subscriber['subscribed_at'])); ?><br>
                                            <?php echo date('H:i', strtotime($subscriber['subscribed_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($subscriber['unsubscribed_at']): ?>
                                            <small>
                                                <?php echo date('M j, Y', strtotime($subscriber['unsubscribed_at'])); ?><br>
                                                <?php echo date('H:i', strtotime($subscriber['unsubscribed_at'])); ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($subscriber['status'] === 'active'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="subscriber_id" value="<?php echo $subscriber['id']; ?>">
                                                <button type="submit" name="unsubscribe_newsletter" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Are you sure you want to unsubscribe this user?')">
                                                    <i class="bi bi-x-circle me-1"></i>Unsubscribe
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <small class="text-muted">Unsubscribed</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($data)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="text-muted">No data found</h4>
                        <p class="text-muted">No records match your current filters.</p>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?view=<?php echo $current_view; ?>&page=<?php echo $page - 1; ?>&<?php echo http_build_query($_GET); ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?view=<?php echo $current_view; ?>&page=<?php echo $i; ?>&<?php echo http_build_query($_GET); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?view=<?php echo $current_view; ?>&page=<?php echo $page + 1; ?>&<?php echo http_build_query($_GET); ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="assets/vendors/bootstrap/bootstrap.bundle.min.js"></script>
    <div data-global-footer></div>
    <script src="assets/js/footer.js"></script>
</body>
</html>

<?php
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_complete.php');
    exit;
}
?>















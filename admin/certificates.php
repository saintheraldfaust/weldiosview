<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirectToLogin();
}

// Handle certificate operations
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create_certificate':
                    $student_id = (int)$_POST['student_id'];
                    $certificate_number = sanitize($_POST['certificate_number']);
                    
                    // Check if certificate number already exists
                    $checkStmt = $pdo->prepare("SELECT id FROM certificates WHERE certificate_number = ?");
                    $checkStmt->execute([$certificate_number]);
                    if ($checkStmt->fetch()) {
                        throw new Exception("Certificate number '{$certificate_number}' already exists. Each certificate must have a unique number.");
                    }
                    $programme_type = sanitize($_POST['programme_type']);
                    $programme_title = sanitize($_POST['programme_title']);
                    $department = sanitize($_POST['department']);
                    $class_of_degree = sanitize($_POST['class_of_degree']);
                    $year_of_graduation = sanitize($_POST['year_of_graduation']);
                    $issue_date = sanitize($_POST['issue_date']);
                    $profile_url = generateProfileUrl();
                    
                    // Handle file upload
                    $file_path = null;
                    if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] == 0) {
                        $upload_dir = '../uploads/certificates/';
                        $file_name = uniqid() . '_' . basename($_FILES['certificate_file']['name']);
                        $full_path = $upload_dir . $file_name;
                        if (!move_uploaded_file($_FILES['certificate_file']['tmp_name'], $full_path)) {
                            throw new Exception("Failed to upload file.");
                        }
                        // Store path relative to root directory (without ../)
                        $file_path = 'uploads/certificates/' . $file_name;
                    }

                    // Handle image upload
                    $image_path = null;
                    if (isset($_FILES['certificate_image']) && $_FILES['certificate_image']['error'] == 0) {
                        $upload_dir_image = '../uploads/certificate_images/';
                        $image_name = uniqid() . '_' . basename($_FILES['certificate_image']['name']);
                        $full_path_image = $upload_dir_image . $image_name;
                        if (!move_uploaded_file($_FILES['certificate_image']['tmp_name'], $full_path_image)) {
                            throw new Exception("Failed to upload certificate image.");
                        }
                        // Store path relative to root directory (without ../)
                        $image_path = 'uploads/certificate_images/' . $image_name;
                    }

                    $stmt = $pdo->prepare("INSERT INTO certificates (student_id, certificate_number, programme_type, programme_title, department, class_of_degree, year_of_graduation, issue_date, profile_url, file_path, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
                    $stmt->execute([$student_id, $certificate_number, $programme_type, $programme_title, $department, $class_of_degree, $year_of_graduation, $issue_date, $profile_url, $file_path, $image_path]);
                    
                    $profile_full_url = BASE_URL . 'profile.php?id=' . $profile_url;
                    $message = 'Certificate created successfully! Profile URL: ' . $profile_full_url;
                    $message_type = 'success';
                    break;
                    
                case 'update_certificate_status':
                    $certificate_id = (int)$_POST['certificate_id'];
                    $status = sanitize($_POST['status']);
                    
                    $stmt = $pdo->prepare("UPDATE certificates SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $certificate_id]);
                    
                    $message = 'Certificate status updated successfully!';
                    $message_type = 'success';
                    break;
                    
                case 'delete_certificate':
                    $certificate_id = (int)$_POST['certificate_id'];
                    $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
                    $stmt->execute([$certificate_id]);
                    
                    $message = 'Certificate deleted successfully!';
                    $message_type = 'success';
                    break;
            }
        }
    } catch (Exception $e) {
        $message = 'Operation failed: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Get filter parameters
$student_filter = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search_filter = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$show_without_certs = isset($_GET['show_without_certs']) ? (bool)$_GET['show_without_certs'] : false;

// Get all certificates with student information
try {
    $pdo = getDBConnection();
    
    $whereClause = "WHERE 1=1";
    $params = [];
    
    if ($student_filter > 0) {
        $whereClause .= " AND c.student_id = ?";
        $params[] = $student_filter;
    }
    
    if (!empty($status_filter)) {
        $whereClause .= " AND c.status = ?";
        $params[] = $status_filter;
    }
    
    if (!empty($search_filter)) {
        $whereClause .= " AND (s.surname LIKE ? OR s.first_name LIKE ? OR c.certificate_number LIKE ? OR s.matriculation_number LIKE ? OR s.registration_number LIKE ?)";
        $search_param = '%' . $search_filter . '%';
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    }
    
    if ($show_without_certs) {
        // Show students without certificates instead
        $search_clause = "";
        $search_params = [];
        if (!empty($search_filter)) {
            $search_clause = " AND (s.surname LIKE ? OR s.first_name LIKE ? OR s.matriculation_number LIKE ? OR s.registration_number LIKE ?)";
            $search_param = '%' . $search_filter . '%';
            $search_params = [$search_param, $search_param, $search_param, $search_param];
        }
        
        $stmt = $pdo->prepare("
            SELECT s.id, s.surname, s.first_name, s.middle_name, s.matriculation_number, s.registration_number, s.email, s.created_at
            FROM students s 
            LEFT JOIN certificates c ON s.id = c.student_id 
            WHERE c.id IS NULL $search_clause
            ORDER BY s.surname, s.first_name
        ");
        $stmt->execute($search_params);
        $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   s.surname, s.first_name, s.middle_name, s.matriculation_number, s.registration_number, s.email
            FROM certificates c 
            JOIN students s ON c.student_id = s.id 
            $whereClause
            ORDER BY c.created_at DESC
        ");
        $stmt->execute($params);
        $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get all students for dropdown
    $stmt = $pdo->query("SELECT id, surname, first_name, middle_name, matriculation_number, registration_number FROM students ORDER BY surname, first_name");
    $all_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get students without certificates
    $stmt = $pdo->query("
        SELECT s.id, s.surname, s.first_name, s.middle_name, s.matriculation_number, s.registration_number 
        FROM students s 
        LEFT JOIN certificates c ON s.id = c.student_id 
        WHERE c.id IS NULL 
        ORDER BY s.surname, s.first_name
    ");
    $students_without_certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $stmt = $pdo->query("SELECT COUNT(*) FROM certificates");
    $total_certificates = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM certificates WHERE status = 'active'");
    $active_certificates = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM certificates WHERE status = 'revoked'");
    $revoked_certificates = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM certificates WHERE YEAR(created_at) = YEAR(CURDATE())");
    $certificates_this_year = $stmt->fetchColumn();
    
} catch (Exception $e) {
    $certificates = [];
    $all_students = [];
    $students_without_certificates = [];
    $total_certificates = 0;
    $active_certificates = 0;
    $revoked_certificates = 0;
    $certificates_this_year = 0;
}

// Helper function to get student display ID (matriculation or registration number)
function getStudentDisplayId($student) {
    if (!empty($student['matriculation_number'])) {
        return $student['matriculation_number'];
    } elseif (!empty($student['registration_number'])) {
        return $student['registration_number'];
    }
    return 'No ID';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates Management - weldios university Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            background: var(--sidebar-bg);
            color: white;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-logo {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin: 0.25rem 1rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--sidebar-hover);
            color: white;
            transform: translateX(4px);
        }

        .nav-link i {
            width: 20px;
            margin-right: 1rem;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
        }

        .top-bar {
            background: white;
            border-radius: 16px;
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-primary { background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; }
        .stat-success { background: linear-gradient(135deg, var(--success-color), #059669); color: white; }
        .stat-danger { background: linear-gradient(135deg, var(--danger-color), #dc2626); color: white; }
        .stat-warning { background: linear-gradient(135deg, var(--warning-color), #d97706); color: white; }

        .content-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .filters-bar {
            background: #f8fafc;
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-add {
            background: linear-gradient(135deg, var(--success-color), #059669);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .table-container {
            overflow-x: auto;
        }

        .table th {
            background: #f8fafc;
            border: none;
            padding: 1rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid #e5e7eb;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-revoked { background: #fee2e2; color: #991b1b; }
        .badge-suspended { background: #fef3c7; color: #92400e; }

        .badge-undergraduate { background: #dbeafe; color: #1e40af; }
        .badge-graduate { background: #d1fae5; color: #065f46; }
        .badge-diploma { background: #fef3c7; color: #92400e; }
        .badge-certificate { background: #e0e7ff; color: #3730a3; }

        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border-bottom: none;
            padding: 2rem;
        }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
        }

        .qr-preview {
            text-align: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .certificate-preview {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 2px solid var(--primary-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 4px;
        }

        .qr-modal .modal-body {
            text-align: center;
            padding: 2rem;
        }

        .profile-url-display {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.5rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            margin: 1rem 0;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h5 class="mb-0">weldios university Admin</h5>
            <small class="opacity-75">Certificate Management</small>
        </div>
        
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="students.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    Students
                </a>
            </div>
            <div class="nav-item">
                <a href="certificates.php" class="nav-link active">
                    <i class="fas fa-certificate"></i>
                    Certificates
                </a>
            </div>
            <div class="nav-item">
                <a href="<?php echo BASE_URL; ?>" class="nav-link" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    View Portal
                </a>
            </div>
            <div class="nav-item mt-4">
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h2 class="h4 mb-0">Certificates Management</h2>
                <small class="text-muted">Create and manage student certificates</small>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="me-3">
                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                    <small class="text-muted">Administrator</small>
                </div>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-primary">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo $total_certificates; ?></h3>
                <p class="text-muted mb-0">Total Certificates</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo $active_certificates; ?></h3>
                <p class="text-muted mb-0">Active Certificates</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-danger">
                    <i class="fas fa-ban"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo $revoked_certificates; ?></h3>
                <p class="text-muted mb-0">Revoked</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-warning">
                    <i class="fas fa-calendar"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo $certificates_this_year; ?></h3>
                <p class="text-muted mb-0"><?php echo date('Y'); ?> Certificates</p>
            </div>

            <?php if (count($students_without_certificates) > 0): ?>
            <div class="stat-card" style="background: linear-gradient(135deg, #fef3c7, #fde68a);">
                <div class="stat-icon" style="background: var(--warning-color); color: white;">
                    <i class="fas fa-user-clock"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo count($students_without_certificates); ?></h3>
                <p class="text-muted mb-0">Students Need Certificates</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Certificates Table -->
        <div class="content-card">
            <div class="card-header">
                <div>
                    <h5 class="mb-0">Certificate Records</h5>
                    <small class="text-muted">Manage all issued certificates</small>
                </div>
                <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#createCertificateModal">
                    <i class="fas fa-plus me-2"></i>Create Certificate
                </button>
            </div>
            
            <!-- Filters -->
            <div class="filters-bar">
                <form method="GET" class="d-flex gap-3 align-items-center flex-wrap">
                    <div>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search students, cert numbers..." 
                               value="<?php echo htmlspecialchars($search_filter); ?>" style="width: 250px;">
                    </div>
                    <div>
                        <select name="student_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Students</option>
                            <?php foreach ($all_students as $student): ?>
                                <option value="<?php echo $student['id']; ?>" <?php echo $student_filter == $student['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['surname'] . ', ' . $student['first_name'] . ' (' . getStudentDisplayId($student) . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="revoked" <?php echo $status_filter === 'revoked' ? 'selected' : ''; ?>>Revoked</option>
                            <option value="suspended" <?php echo $status_filter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                    <div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_without_certs" id="showWithoutCerts" 
                                   value="1" <?php echo $show_without_certs ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <label class="form-check-label" for="showWithoutCerts">
                                Students without certificates
                            </label>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                    <?php if ($student_filter > 0 || !empty($status_filter) || !empty($search_filter) || $show_without_certs): ?>
                        <a href="certificates.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <?php if ($show_without_certs): ?>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            <?php else: ?>
                                <th>Certificate #</th>
                                <th>Student</th>
                                <th>Programme</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Profile URL</th>
                                <th>Issue Date</th>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $certificate): ?>
                        <tr>
                            <?php if ($show_without_certs): ?>
                                <td>
                                    <strong><?php echo htmlspecialchars($certificate['surname'] . ', ' . $certificate['first_name']); ?></strong>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo htmlspecialchars(getStudentDisplayId($certificate)); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($certificate['email'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y', strtotime($certificate['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <button class="btn btn-success btn-sm" onclick="createCertificateForStudent(<?php echo $certificate['id']; ?>, '<?php echo htmlspecialchars($certificate['first_name'] . ' ' . $certificate['surname']); ?>')">
                                        <i class="fas fa-certificate"></i> Create Certificate
                                    </button>
                                </td>
                            <?php else: ?>
                                <td>
                                    <strong class="text-primary"><?php echo htmlspecialchars($certificate['certificate_number']); ?></strong>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($certificate['surname'] . ', ' . $certificate['first_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars(getStudentDisplayId($certificate)); ?></small>
                                    </div>
                                </td>
                            <td>
                                <div>
                                    <span class="badge badge-<?php echo $certificate['programme_type']; ?>">
                                        <?php echo htmlspecialchars(ucfirst($certificate['programme_type'])); ?>
                                    </span>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($certificate['programme_title']); ?></small>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($certificate['department']); ?></td>
                            <td><?php echo htmlspecialchars($certificate['year_of_graduation']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $certificate['status']; ?>">
                                    <?php echo htmlspecialchars(ucfirst($certificate['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <div style="max-width: 200px;">
                                    <small class="text-muted d-block">
                                        <?php 
                                        $profile_url = BASE_URL . 'profile.php?id=' . $certificate['profile_url'];
                                        echo htmlspecialchars($certificate['profile_url']); 
                                        ?>
                                    </small>
                                    <div class="mt-1">
                                        <button class="btn btn-outline-secondary btn-xs me-1" 
                                                onclick="copyToClipboard('<?php echo $profile_url; ?>')" 
                                                title="Copy Profile URL">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo date('M j, Y', strtotime($certificate['issue_date'])); ?>
                                </small>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>profile.php?id=<?php echo $certificate['profile_url']; ?>" 
                                   class="btn btn-outline-primary btn-sm me-1" target="_blank" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-outline-warning btn-sm me-1" 
                                        onclick="updateStatus(<?php echo $certificate['id']; ?>, '<?php echo $certificate['status']; ?>')" title="Update Status">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" 
                                        onclick="deleteCertificate(<?php echo $certificate['id']; ?>, '<?php echo htmlspecialchars($certificate['certificate_number']); ?>')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php if (!empty($certificate['file_path'])): ?>
                                    <a href="<?php echo BASE_URL . $certificate['file_path']; ?>" 
                                       class="btn btn-outline-success btn-sm" target="_blank" title="View Certificate PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($certificates)): ?>
                        <tr>
                            <td colspan="<?php echo $show_without_certs ? '5' : '9'; ?>" class="text-center py-5">
                                <?php if ($show_without_certs): ?>
                                    <i class="fas fa-users text-muted mb-3" style="font-size: 3rem;"></i>
                                    <p class="text-muted">All students have certificates! Great job.</p>
                                <?php else: ?>
                                    <i class="fas fa-certificate text-muted mb-3" style="font-size: 3rem;"></i>
                                    <p class="text-muted">No certificates found. Create your first certificate to get started.</p>
                                    <?php if (count($students_without_certificates) > 0): ?>
                                        <p class="text-muted">You have <?php echo count($students_without_certificates); ?> students waiting for certificates.</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Certificate Modal -->
    <div class="modal fade" id="createCertificateModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Certificate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create_certificate">
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Student *</label>
                                <select class="form-select" name="student_id" id="studentSelect" required onchange="updateCertificatePreview()">
                                    <option value="">Choose a student</option>
                                    <?php foreach ($all_students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($student['surname'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']); ?>"
                                                data-id="<?php echo htmlspecialchars(getStudentDisplayId($student)); ?>">
                                            <?php echo htmlspecialchars($student['surname'] . ', ' . $student['first_name'] . ' (' . getStudentDisplayId($student) . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (count($students_without_certificates) > 0): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <?php echo count($students_without_certificates); ?> students don't have certificates yet
                                    </small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Certificate Number *</label>
                                <input type="text" class="form-control" name="certificate_number" 
                                       value="<?php echo generateCertificateNumber(); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Programme Type *</label>
                                <select class="form-select" name="programme_type" required onchange="updateCertificatePreview()">
                                    <option value="">Select Programme Type</option>
                                    <option value="undergraduate">Undergraduate Degree</option>
                                    <option value="diploma">Diploma</option>
                                    <option value="graduate">Graduate Degree</option>
                                    <option value="certificate">Certificate</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Year of Graduation *</label>
                                <input type="number" class="form-control" name="year_of_graduation" 
                                       min="2000" max="<?php echo date('Y') + 5; ?>" value="<?php echo date('Y'); ?>" required onchange="updateCertificatePreview()">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Issue Date *</label>
                                <input type="date" class="form-control" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Programme Title *</label>
                            <input type="text" class="form-control" name="programme_title" 
                                   placeholder="e.g., Bachelor of Science (Computer Science)" required onchange="updateCertificatePreview()">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Department/Course *</label>
                                <input type="text" class="form-control" name="department" required onchange="updateCertificatePreview()">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Class of Degree (Optional)</label>
                                <input type="text" class="form-control" name="class_of_degree" 
                                       placeholder="e.g., First Class, Distinction" onchange="updateCertificatePreview()">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="certificate_file" class="form-label">Certificate PDF (Optional)</label>
                                <input class="form-control" type="file" id="certificate_file" name="certificate_file" accept=".pdf">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="certificate_image" class="form-label">Certificate Image (Optional)</label>
                                <input class="form-control" type="file" id="certificate_image" name="certificate_image" accept="image/png, image/jpeg">
                            </div>
                        </div>

                        <!-- Certificate Preview -->
                        <div class="certificate-preview" id="certificatePreview" style="display: none;">
                            <h6 class="text-center mb-3"><i class="fas fa-eye me-2"></i>Certificate Preview</h6>
                            <div class="text-center">
                                <h5 class="text-primary mb-3">weldios university</h5>
                                <p class="mb-2">This is to certify that</p>
                                <h4 class="text-dark mb-3" id="previewStudentName">Student Name</h4>
                                <p class="mb-2">has successfully completed the programme of study for</p>
                                <h5 class="text-primary mb-3" id="previewProgrammeTitle">Programme Title</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <small><strong>Department:</strong> <span id="previewDepartment">Department</span></small>
                                    </div>
                                    <div class="col-6">
                                        <small><strong>Year:</strong> <span id="previewYear">Year</span></small>
                                    </div>
                                </div>
                                <div id="previewClassOfDegree" style="display: none;">
                                    <small><strong>Class of Degree:</strong> <span id="previewClass">Class</span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Certificate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Certificate Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_certificate_status">
                    <input type="hidden" name="certificate_id" id="statusCertificateId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Certificate Status</label>
                            <select class="form-select" name="status" id="certificateStatus" required>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="revoked">Revoked</option>
                            </select>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> Suspended or revoked certificates will not appear in verification results.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete certificate <strong id="certificateNumber"></strong>?</p>
                    <p class="text-muted">This action cannot be undone and will remove the certificate from all verification systems.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_certificate">
                        <input type="hidden" name="certificate_id" id="deleteCertificateId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        function updateCertificatePreview() {
            const studentSelect = document.getElementById('studentSelect');
            const selectedOption = studentSelect.options[studentSelect.selectedIndex];
            const programmeTitle = document.querySelector('input[name="programme_title"]').value;
            const department = document.querySelector('input[name="department"]').value;
            const year = document.querySelector('input[name="year_of_graduation"]').value;
            const classOfDegree = document.querySelector('input[name="class_of_degree"]').value;
            
            const preview = document.getElementById('certificatePreview');
            
            if (selectedOption.value && programmeTitle && department && year) {
                document.getElementById('previewStudentName').textContent = selectedOption.dataset.name || 'Student Name';
                document.getElementById('previewProgrammeTitle').textContent = programmeTitle;
                document.getElementById('previewDepartment').textContent = department;
                document.getElementById('previewYear').textContent = year;
                
                if (classOfDegree) {
                    document.getElementById('previewClass').textContent = classOfDegree;
                    document.getElementById('previewClassOfDegree').style.display = 'block';
                } else {
                    document.getElementById('previewClassOfDegree').style.display = 'none';
                }
                
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

        function createCertificateForStudent(studentId, studentName) {
            // Pre-select the student in the create certificate modal
            document.getElementById('studentSelect').value = studentId;
            updateCertificatePreview();
            
            // Show the modal
            new bootstrap.Modal(document.getElementById('createCertificateModal')).show();
        }

        function updateStatus(id, currentStatus) {
            document.getElementById('statusCertificateId').value = id;
            document.getElementById('certificateStatus').value = currentStatus;
            new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
        }

        function deleteCertificate(id, certificateNumber) {
            document.getElementById('certificateNumber').textContent = certificateNumber;
            document.getElementById('deleteCertificateId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function copyToClipboard(text) {
            // Method 1: Modern Clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopySuccess();
                }).catch(function(err) {
                    console.error('Clipboard API failed: ', err);
                    fallbackCopyTextToClipboard(text);
                });
            } else {
                // Method 2: Fallback for older browsers or non-secure contexts
                fallbackCopyTextToClipboard(text);
            }
        }
        
        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            
            // Avoid scrolling to bottom
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            textArea.style.opacity = "0";
            
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopySuccess();
                } else {
                    showCopyError(text);
                }
            } catch (err) {
                console.error('Fallback copy failed: ', err);
                showCopyError(text);
            }
            
            document.body.removeChild(textArea);
        }
        
        function showCopySuccess() {
            const btn = event?.target?.closest('button');
            if (btn) {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check text-success"></i> Copied!';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-secondary', 'btn-outline-primary');
                
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            }
            
            // Also show a toast notification
            showToast('URL copied to clipboard!', 'success');
        }
        
        function showCopyError(text) {
            // Create a modal with the URL for manual copying
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Copy URL Manually</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Please copy the URL manually:</p>
                            <div class="form-group">
                                <input type="text" class="form-control" value="${text}" readonly onclick="this.select()">
                            </div>
                            <small class="text-muted">Click the input field to select all text, then press Ctrl+C to copy.</small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
            
            // Remove modal from DOM when hidden
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
        }
        
        function showToast(message, type = 'info') {
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            // Add to page
            let toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '1060';
                document.body.appendChild(toastContainer);
            }
            
            toastContainer.appendChild(toast);
            const bootstrapToast = new bootstrap.Toast(toast);
            bootstrapToast.show();
            
            // Remove toast from DOM when hidden
            toast.addEventListener('hidden.bs.toast', function() {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            });
        }



        // Auto-populate fields based on common patterns
        document.querySelector('select[name="programme_type"]').addEventListener('change', function() {
            const type = this.value;
            const titleField = document.querySelector('input[name="programme_title"]');
            
            if (type === 'undergraduate') {
                titleField.placeholder = 'e.g., Bachelor of Science (Computer Science)';
            } else if (type === 'graduate') {
                titleField.placeholder = 'e.g., Master of Science (Data Science)';
            } else if (type === 'diploma') {
                titleField.placeholder = 'e.g., Advanced Diploma in Engineering';
            } else if (type === 'certificate') {
                titleField.placeholder = 'e.g., Certificate in Digital Marketing';
            }
        });

        // Show success message after certificate creation
        <?php if ($message_type === 'success' && strpos($message, 'created') !== false): ?>
        setTimeout(() => {
            const alertElement = document.querySelector('.alert-success');
            if (alertElement) {
                alertElement.innerHTML += '<br><small><i class="fas fa-info-circle me-1"></i>Each certificate now has a unique profile URL that can be used for verification and QR code generation.</small>';
            }
        }, 100);
        <?php endif; ?>
    </script>
</body>
</html>
<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirectToLogin();
}

// Handle student operations
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_student':
                    $certificate_number = sanitize($_POST['certificate_number']);
                    $surname = sanitize($_POST['surname']);
                    $first_name = sanitize($_POST['first_name']);
                    $middle_name = sanitize($_POST['middle_name']);
                    $programme_type = sanitize($_POST['programme_type']);
                    $programme_title = sanitize($_POST['programme_title']);
                    $department = sanitize($_POST['department']);
                    $class_of_degree = sanitize($_POST['class_of_degree']);
                    $year_of_graduation = sanitize($_POST['year_of_graduation']);
                    $matriculation_number = sanitize($_POST['matriculation_number']);
                    $profile_url = generateProfileUrl();
                    
                    $stmt = $pdo->prepare("INSERT INTO students (certificate_number, surname, first_name, middle_name, programme_type, programme_title, department, class_of_degree, year_of_graduation, matriculation_number, profile_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$certificate_number, $surname, $first_name, $middle_name, $programme_type, $programme_title, $department, $class_of_degree, $year_of_graduation, $matriculation_number, $profile_url]);
                    
                    $message = 'Student added successfully!';
                    $message_type = 'success';
                    break;
                    
                case 'delete_student':
                    $student_id = (int)$_POST['student_id'];
                    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
                    $stmt->execute([$student_id]);
                    
                    $message = 'Student deleted successfully!';
                    $message_type = 'success';
                    break;
            }
        }
    } catch (Exception $e) {
        $message = 'Operation failed: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Get statistics and recent activity
try {
    $pdo = getDBConnection();
    
    // Get statistics
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $total_students = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM certificates");
    $total_certificates = $stmt->fetchColumn();
    
    $current_year = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE year_of_graduation = ?");
    $stmt->execute([$current_year]);
    $current_year_graduates = $stmt->fetchColumn();
    
    // Get recent certificates
    $stmt = $pdo->query("
        SELECT c.*, s.surname, s.first_name, s.middle_name, s.matriculation_number
        FROM certificates c 
        JOIN students s ON c.student_id = s.id 
        ORDER BY c.created_at DESC 
        LIMIT 5
    ");
    $recent_certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get students without certificates
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM students s 
        LEFT JOIN certificates c ON s.id = c.student_id 
        WHERE c.id IS NULL
    ");
    $students_without_certificates = $stmt->fetchColumn();
    
} catch (Exception $e) {
    $total_students = 0;
    $total_certificates = 0;
    $current_year_graduates = 0;
    $recent_certificates = [];
    $students_without_certificates = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - weldios university Portal</title>
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
            margin: 0;
            padding: 0;
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

        .table {
            margin: 0;
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

        .badge-undergraduate { background: #dbeafe; color: #1e40af; }
        .badge-graduate { background: #d1fae5; color: #065f46; }
        .badge-diploma { background: #fef3c7; color: #92400e; }
        .badge-certificate { background: #e0e7ff; color: #3730a3; }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: 6px;
            font-weight: 500;
        }

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

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
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

        .logout-btn {
            background: var(--danger-color);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
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
            <small class="opacity-75">Student Management</small>
        </div>
        
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link active">
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
                <a href="certificates.php" class="nav-link">
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
                <h2 class="h4 mb-0">Dashboard</h2>
                <small class="text-muted">Manage student records and certificates</small>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="me-3">
                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                    <small class="text-muted">Administrator</small>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-primary">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo $total_students; ?></h3>
                <p class="text-muted mb-0">Total Students</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-success">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo $current_year_graduates; ?></h3>
                <p class="text-muted mb-0"><?php echo $current_year; ?> Graduates</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-warning">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo $total_certificates; ?></h3>
                <p class="text-muted mb-0">Certificates Issued</p>
            </div>
            
            <?php if ($students_without_certificates > 0): ?>
            <div class="stat-card" style="background: linear-gradient(135deg, #fef3c7, #fde68a);">
                <div class="stat-icon" style="background: var(--warning-color); color: white;">
                    <i class="fas fa-user-clock"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo $students_without_certificates; ?></h3>
                <p class="text-muted mb-0">Need Certificates</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="content-card">
            <div class="card-header">
                <div>
                    <h5 class="mb-0">Quick Actions</h5>
                    <small class="text-muted">Common administrative tasks</small>
                </div>
            </div>
            <div class="p-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="students.php" class="btn btn-outline-primary w-100 p-3">
                            <i class="fas fa-users fa-2x mb-2 d-block"></i>
                            <h6>Manage Students</h6>
                            <small class="text-muted">Register and manage student records</small>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="certificates.php" class="btn btn-outline-success w-100 p-3">
                            <i class="fas fa-certificate fa-2x mb-2 d-block"></i>
                            <h6>Issue Certificates</h6>
                            <small class="text-muted">Create and manage certificates</small>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="<?php echo BASE_URL; ?>" target="_blank" class="btn btn-outline-info w-100 p-3">
                            <i class="fas fa-search fa-2x mb-2 d-block"></i>
                            <h6>Verification Portal</h6>
                            <small class="text-muted">Test certificate verification</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="content-card mt-4">
            <div class="card-header">
                <div>
                    <h5 class="mb-0">Recent Certificates</h5>
                    <small class="text-muted">Recently issued certificates</small>
                </div>
                <a href="certificates.php" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye me-1"></i>View All
                </a>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Certificate #</th>
                            <th>Student Name</th>
                            <th>Programme</th>
                            <th>Department</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Issued</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_certificates as $certificate): ?>
                        <tr>
                            <td>
                                <strong class="text-primary"><?php echo htmlspecialchars($certificate['certificate_number']); ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($certificate['surname'] . ', ' . $certificate['first_name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($certificate['matriculation_number']); ?></small>
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
                                <small class="text-muted">
                                    <?php echo date('M j, Y', strtotime($certificate['created_at'])); ?>
                                </small>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>profile.php?id=<?php echo $certificate['profile_url']; ?>" 
                                   class="btn btn-outline-primary btn-sm me-1" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="certificates.php" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-cog"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($recent_certificates)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-certificate text-muted mb-3" style="font-size: 3rem;"></i>
                                <p class="text-muted">No certificates issued yet.</p>
                                <a href="certificates.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create First Certificate
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dashboard functionality can be added here
    </script>
</body>
</html>
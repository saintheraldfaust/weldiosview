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
                    $surname = sanitize($_POST['surname']);
                    $first_name = sanitize($_POST['first_name']);
                    $middle_name = sanitize($_POST['middle_name']);
                    $matriculation_number = sanitize($_POST['matriculation_number']);
                    $email = sanitize($_POST['email']);
                    $phone = sanitize($_POST['phone']);
                    $address = sanitize($_POST['address']);
                    $date_of_birth = sanitize($_POST['date_of_birth']);
                    $gender = sanitize($_POST['gender']);
                    $nationality = sanitize($_POST['nationality']);
                    
                    $stmt = $pdo->prepare("INSERT INTO students (surname, first_name, middle_name, matriculation_number, email, phone, address, date_of_birth, gender, nationality, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
                    $stmt->execute([$surname, $first_name, $middle_name, $matriculation_number, $email, $phone, $address, $date_of_birth, $gender, $nationality]);
                    
                    $message = 'Student registered successfully!';
                    $message_type = 'success';
                    break;
                    
                case 'edit_student':
                    $student_id = (int)$_POST['student_id'];
                    $surname = sanitize($_POST['surname']);
                    $first_name = sanitize($_POST['first_name']);
                    $middle_name = sanitize($_POST['middle_name']);
                    $matriculation_number = sanitize($_POST['matriculation_number']);
                    $email = sanitize($_POST['email']);
                    $phone = sanitize($_POST['phone']);
                    $address = sanitize($_POST['address']);
                    $date_of_birth = sanitize($_POST['date_of_birth']);
                    $gender = sanitize($_POST['gender']);
                    $nationality = sanitize($_POST['nationality']);
                    $status = sanitize($_POST['status']);
                    
                    $stmt = $pdo->prepare("UPDATE students SET surname=?, first_name=?, middle_name=?, matriculation_number=?, email=?, phone=?, address=?, date_of_birth=?, gender=?, nationality=?, status=? WHERE id=?");
                    $stmt->execute([$surname, $first_name, $middle_name, $matriculation_number, $email, $phone, $address, $date_of_birth, $gender, $nationality, $status, $student_id]);
                    
                    $message = 'Student updated successfully!';
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

// Get all students with certificate count
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT s.*, 
               COUNT(c.id) as certificate_count,
               MAX(c.created_at) as last_certificate_date
        FROM students s 
        LEFT JOIN certificates c ON s.id = c.student_id 
        GROUP BY s.id 
        ORDER BY s.created_at DESC
    ");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $total_students = count($students);
    $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'");
    $active_students = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'graduated'");
    $graduated_students = $stmt->fetchColumn();
    
} catch (Exception $e) {
    $students = [];
    $total_students = 0;
    $active_students = 0;
    $graduated_students = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Management - weldios university Portal</title>
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
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .badge-graduated { background: #dbeafe; color: #1e40af; }

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

        .certificate-count {
            display: inline-flex;
            align-items: center;
            background: #f0f9ff;
            color: #0369a1;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
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
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="students.php" class="nav-link active">
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
                <h2 class="h4 mb-0">Students Management</h2>
                <small class="text-muted">Register and manage student records</small>
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
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo $total_students; ?></h3>
                <p class="text-muted mb-0">Total Students</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-success">
                    <i class="fas fa-user-check"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo $active_students; ?></h3>
                <p class="text-muted mb-0">Active Students</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-warning">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="h2 mb-1"><?php echo $graduated_students; ?></h3>
                <p class="text-muted mb-0">Graduated</p>
            </div>
        </div>

        <!-- Students Table -->
        <div class="content-card">
            <div class="card-header">
                <div>
                    <h5 class="mb-0">Student Records</h5>
                    <small class="text-muted">Manage all registered students</small>
                </div>
                <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <i class="fas fa-plus me-2"></i>Register Student
                </button>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student Details</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Certificates</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($student['surname'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($student['matriculation_number']); ?></small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <?php if (!empty($student['email'])): ?>
                                        <small><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($student['email']); ?></small><br>
                                    <?php endif; ?>
                                    <?php if (!empty($student['phone'])): ?>
                                        <small><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($student['phone']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $student['status']; ?>">
                                    <?php echo htmlspecialchars(ucfirst($student['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="certificate-count">
                                    <i class="fas fa-certificate me-1"></i>
                                    <?php echo $student['certificate_count']; ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo date('M j, Y', strtotime($student['created_at'])); ?>
                                </small>
                            </td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm me-1" 
                                        onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="certificates.php?student_id=<?php echo $student['id']; ?>" 
                                   class="btn btn-outline-success btn-sm me-1" title="Manage Certificates">
                                    <i class="fas fa-certificate"></i>
                                </a>
                                <button class="btn btn-outline-danger btn-sm" 
                                        onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['surname']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-users text-muted mb-3" style="font-size: 3rem;"></i>
                                <p class="text-muted">No students found. Register your first student to get started.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Register New Student</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_student">
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Surname *</label>
                                <input type="text" class="form-control" name="surname" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Matriculation Number *</label>
                                <input type="text" class="form-control" name="matriculation_number" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="2"></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nationality</label>
                                <input type="text" class="form-control" name="nationality" value="Nigerian">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Register Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editStudentForm">
                    <input type="hidden" name="action" value="edit_student">
                    <input type="hidden" name="student_id" id="editStudentId">
                    <div class="modal-body p-4">
                        <!-- Same form fields as add, but populated with existing data -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Surname *</label>
                                <input type="text" class="form-control" name="surname" id="editSurname" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" id="editFirstName" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name" id="editMiddleName">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Matriculation Number *</label>
                                <input type="text" class="form-control" name="matriculation_number" id="editMatriculationNumber" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender" id="editGender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth" id="editDateOfBirth">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="editEmail">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" id="editPhone">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="2" id="editAddress"></textarea>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Nationality</label>
                                <input type="text" class="form-control" name="nationality" id="editNationality">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="editStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="graduated">Graduated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Student</button>
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
                    <p>Are you sure you want to delete the record for <strong id="studentName"></strong>?</p>
                    <p class="text-muted">This action will also delete all associated certificates and cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_student">
                        <input type="hidden" name="student_id" id="deleteStudentId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editStudent(student) {
            document.getElementById('editStudentId').value = student.id;
            document.getElementById('editSurname').value = student.surname || '';
            document.getElementById('editFirstName').value = student.first_name || '';
            document.getElementById('editMiddleName').value = student.middle_name || '';
            document.getElementById('editMatriculationNumber').value = student.matriculation_number || '';
            document.getElementById('editEmail').value = student.email || '';
            document.getElementById('editPhone').value = student.phone || '';
            document.getElementById('editAddress').value = student.address || '';
            document.getElementById('editDateOfBirth').value = student.date_of_birth || '';
            document.getElementById('editGender').value = student.gender || '';
            document.getElementById('editNationality').value = student.nationality || '';
            document.getElementById('editStatus').value = student.status || 'active';
            
            new bootstrap.Modal(document.getElementById('editStudentModal')).show();
        }

        function deleteStudent(id, name) {
            document.getElementById('studentName').textContent = name;
            document.getElementById('deleteStudentId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
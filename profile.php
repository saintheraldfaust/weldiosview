<?php
require_once 'config/config.php';

// Get profile ID from URL
$profile_id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

if (empty($profile_id)) {
    header('Location: ' . BASE_URL);
    exit();
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM certificate_verification WHERE profile_url = ?");
    $stmt->execute([$profile_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        header('Location: ' . BASE_URL);
        exit();
    }
    
} catch (Exception $e) {
    header('Location: ' . BASE_URL);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['surname']); ?> - Student Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --gradient-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--gradient-bg);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="0,0 1000,0 1000,100 0,80"/></svg>') bottom;
            background-size: cover;
        }

        .student-avatar {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            border: 4px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1;
        }

        .profile-body {
            padding: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .info-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .info-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1.125rem;
            color: #111827;
            font-weight: 700;
            line-height: 1.4;
        }

        .certificate-section {
            background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
            border-radius: 20px;
            padding: 2rem;
            border: 2px solid var(--success-color);
            margin-top: 2rem;
            text-align: center;
        }

        .certificate-badge {
            display: inline-flex;
            align-items: center;
            background: var(--success-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.125rem;
        }

        .verification-info {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .back-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            position: absolute;
            top: 2rem;
            left: 2rem;
            z-index: 2;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
        }

        .print-button {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            margin-top: 1rem;
        }

        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        @media print {
            body {
                background: white;
            }
            .back-button, .print-button {
                display: none;
            }
            .profile-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .info-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .info-card:nth-child(1) { animation-delay: 0.1s; }
        .info-card:nth-child(2) { animation-delay: 0.2s; }
        .info-card:nth-child(3) { animation-delay: 0.3s; }
        .info-card:nth-child(4) { animation-delay: 0.4s; }
        .info-card:nth-child(5) { animation-delay: 0.5s; }
        .info-card:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <a href="<?php echo BASE_URL; ?>" class="back-button">
                    <i class="fas fa-arrow-left me-2"></i>Back to Verification
                </a>
                
                <div class="student-avatar">
                    <i class="fas fa-user-graduate"></i>
                </div>
                
                <h1 class="h2 mb-2"><?php echo htmlspecialchars($student['surname'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']); ?></h1>
                <p class="mb-0 opacity-75"><?php echo htmlspecialchars($student['programme_title']); ?></p>
                <p class="small opacity-75"><?php echo htmlspecialchars($student['department']); ?></p>
            </div>

            <div class="profile-body">
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="info-label">Certificate Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['certificate_number']); ?></div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="info-label">Matriculation Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['matriculation_number']); ?></div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="info-label">Programme Type</div>
                        <div class="info-value"><?php echo htmlspecialchars(ucfirst($student['programme_type'])); ?></div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="info-label">Department</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['department']); ?></div>
                    </div>

                    <?php if (!empty($student['class_of_degree'])): ?>
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="info-label">Class of Degree</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['class_of_degree']); ?></div>
                    </div>
                    <?php endif; ?>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="info-label">Year of Graduation</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['year_of_graduation']); ?></div>
                    </div>
                </div>

                <div class="certificate-section">
                    <div class="certificate-badge">
                        <i class="fas fa-check-circle me-2"></i>
                        Certificate Verified
                    </div>
                    
                    <h4 class="text-success mb-3">Authentic Certificate</h4>
                    <p class="text-muted mb-0">
                        This certificate has been verified as authentic and is issued by Weldios University. 
                        The information displayed above is accurate and can be trusted for official purposes.
                    </p>

                    <div class="verification-info">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Profile Generated:</small>
                                <strong><?php echo date('F j, Y', strtotime($student['created_at'])); ?></strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Last Updated:</small>
                                <strong><?php echo date('F j, Y', strtotime($student['updated_at'])); ?></strong>
                            </div>
                        </div>
                    </div>

                    <button onclick="window.print()" class="print-button">
                        <i class="fas fa-print me-2"></i>Print Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
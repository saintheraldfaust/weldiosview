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
    $stmt = $pdo->prepare("
        SELECT 
            c.certificate_number, c.programme_type, c.programme_title, c.department, 
            c.class_of_degree, c.year_of_graduation, c.profile_url, c.issue_date, c.status as certificate_status, c.file_path, c.image_path,
            s.surname, s.first_name, s.middle_name, s.matriculation_number, s.email, s.phone,
            s.date_of_birth, s.gender, s.nationality, s.address, s.status as student_status
        FROM certificates c 
        JOIN students s ON c.student_id = s.id 
        WHERE c.profile_url = ? AND c.status = 'active'
    ");
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

// Helper function to safely display data
function safeDisplay($value, $placeholder = 'Not available') {
    return htmlspecialchars(!empty($value) && $value !== '0000-00-00' ? $value : $placeholder);
}

// Helper function to safely display dates
function safeDisplayDate($date, $format = 'F j, Y', $placeholder = 'Not available') {
    if (!empty($date) && $date !== '0000-00-00' && $date !== null) {
        $timestamp = strtotime($date);
        if ($timestamp !== false && $timestamp > 0) {
            return htmlspecialchars(date($format, $timestamp));
        }
    }
    return $placeholder;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['surname']); ?> - Student Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
            background-color: #ffffff;
            color: #000000;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 2rem;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #666666;
            font-size: 1rem;
        }

        .back-button {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #2563eb;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border: 1px solid #2563eb;
            background: #ffffff;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .back-button:hover {
            background: #2563eb;
            color: #ffffff;
        }

        .profile-container {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            margin: 0 auto 1rem auto;
            border-radius: 50%;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }

        .profile-id {
            color: #666666;
            font-size: 0.9rem;
        }

        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .data-item {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
            transition: all 0.2s ease;
        }

        .data-item:hover {
            background: #fafafa;
        }

        .data-label {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #666666;
            margin-bottom: 0.5rem;
        }

        .data-value {
            font-size: 1rem;
            font-weight: 500;
            color: #000000;
            word-wrap: break-word;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-active {
            background: #000000;
            color: #ffffff;
        }

        .status-completed {
            background: #666666;
            color: #ffffff;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
            border-radius: 6px;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            color: #ffffff;
            text-decoration: none;
        }

        .btn-secondary {
            background: #ffffff;
            color: #2563eb;
            border: 1px solid #2563eb;
            border-radius: 6px;
        }

        .btn-secondary:hover {
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
        }

        .qr-section {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            margin: 2rem 0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .qr-code-container {
            display: inline-block;
            padding: 1rem;
            background: #ffffff;
            border: 1px solid #e5e5e5;
            margin: 1rem 0;
        }

        .certificate-section {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .certificate-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .certificate-card {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
            text-align: center;
        }

        .certificate-title {
            font-size: 1rem;
            font-weight: 600;
            color: #000000;
            margin-bottom: 0.5rem;
        }

        .certificate-date {
            font-size: 0.9rem;
            color: #666666;
        }

        .certificate-image-section {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .certificate-image-container {
            width: 100%;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid #e5e5e5;
        }

        .certificate-image-container img {
            width: 100%;
            display: block;
            pointer-events: none; /* Disables mouse events like click and drag */
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .data-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }

            .back-button {
                position: static;
                transform: none;
                margin-bottom: 1rem;
            }

            .header {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="<?php echo BASE_URL; ?>" class="back-button">← Back to Verification</a>
            <h1>Student Profile</h1>
            <p>Certificate verification details</p>
        </div>

        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="assets/weldioslogo.png" alt="weldios university Logo">
                </div>
                <div class="profile-name"><?php 
                    $fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['surname'] ?? ''));
                    echo safeDisplay($fullName, 'Name Not Available'); 
                ?></div>
                <div class="profile-id">Student ID: <?php echo safeDisplay($student['matriculation_number'] ?? '', 'ID Not Available'); ?></div>
                <div style="margin-top: 1rem;">
                    <span class="status-badge status-active">Verified Certificate</span>
                </div>
            </div>

            <div class="section-title">Personal Information</div>
            <div class="data-grid">
                <div class="data-item">
                    <div class="data-label">Full Name</div>
                    <div class="data-value"><?php 
                        $fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['middle_name'] ?? '') . ' ' . ($student['surname'] ?? ''));
                        echo safeDisplay($fullName); 
                    ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Date of Birth</div>
                    <div class="data-value"><?php echo safeDisplayDate($student['date_of_birth'] ?? null); ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Gender</div>
                    <div class="data-value"><?php echo safeDisplay(ucfirst($student['gender'] ?? '')); ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Nationality</div>
                    <div class="data-value"><?php echo safeDisplay($student['nationality'] ?? ''); ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Email</div>
                    <div class="data-value"><?php echo safeDisplay($student['email'] ?? ''); ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Phone</div>
                    <div class="data-value"><?php echo safeDisplay($student['phone'] ?? ''); ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Matriculation Number</div>
                    <div class="data-value"><?php echo safeDisplay($student['matriculation_number'] ?? ''); ?></div>
                </div>
            </div>
        </div>

        <div class="certificate-section">
            <div class="section-title">Academic Credentials</div>

            <div class="data-grid">
                <div class="data-item">
                    <div class="data-label">Certificate Number</div>
                    <div class="data-value"><?php echo safeDisplay($student['certificate_number'] ?? ''); ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Programme Type</div>
                    <div class="data-value"><?php echo safeDisplay(ucwords($student['programme_type'] ?? '')); ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Programme Title</div>
                    <div class="data-value"><?php echo safeDisplay($student['programme_title'] ?? ''); ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Department</div>
                    <div class="data-value"><?php echo safeDisplay($student['department'] ?? ''); ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Class of Degree</div>
                    <div class="data-value"><?php echo safeDisplay($student['class_of_degree'] ?? ''); ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Year of Graduation</div>
                    <div class="data-value"><?php echo safeDisplay($student['year_of_graduation'] ?? ''); ?></div>
                </div>
                <div class="data-item">
                    <div class="data-label">Issue Date</div>
                    <div class="data-value"><?php echo safeDisplayDate($student['issue_date'] ?? null); ?></div>
                </div>
            </div>
        </div>

        <?php if (!empty($student['image_path'])): ?>
        <div class="certificate-image-section">
            <div class="section-title">Certificate Image</div>
            <div class="certificate-image-container">
                <img src="<?php echo BASE_URL . substr($student['image_path'], 3); ?>" alt="Certificate Image">
            </div>
        </div>
        <?php endif; ?>

        <div class="qr-section">
            <div class="section-title">Certificate Verification QR Code</div>
            <p style="color: #666666; margin-bottom: 1.5rem;">Scan this QR code to verify this certificate</p>
            <div class="qr-code-container">
                <div id="qrcode"></div>
            </div>
            <div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <button onclick="copyProfileUrl('<?php echo BASE_URL . 'profile.php?id=' . $student['profile_url']; ?>')" class="btn btn-secondary">
                    Copy Profile URL
                </button>
                <button onclick="window.print()" class="btn btn-primary">
                    Print Certificate
                </button>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem; padding: 2rem; border-top: 1px solid #e5e5e5;">
            <p style="color: #666666; font-size: 0.9rem;">
                This certificate has been digitally verified by weldios university<br>
                Generated on <?php echo date('F j, Y \a\t g:i A'); ?>
            </p>
        </div>
    </div>

    <!-- Modern JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // QR Code generation
        document.addEventListener('DOMContentLoaded', function() {
            try {
                if (typeof QRCode !== 'undefined') {
                    const qrContainer = document.getElementById('qrcode');
                    if (qrContainer) {
                        qrContainer.style.textAlign = 'center';
                        const qr = new QRCode(qrContainer, {
                            text: '<?php echo BASE_URL . 'profile.php?id=' . $student['profile_url']; ?>',
                            width: 200,
                            height: 200,
                            colorDark: 'hsl(28 25 23)',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    }
                }
            } catch (error) {
                console.error('QR Code generation error:', error);
            }
        });

        // Copy URL function
        function copyProfileUrl(url) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(function() {
                    showCopySuccess();
                }).catch(function(err) {
                    fallbackCopyTextToClipboard(url);
                });
            } else {
                fallbackCopyTextToClipboard(url);
            }
        }
        
        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
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
                    alert('Failed to copy URL. Please copy manually: ' + text);
                }
            } catch (err) {
                alert('Failed to copy URL. Please copy manually: ' + text);
            }
            
            document.body.removeChild(textArea);
        }
        
        function showCopySuccess() {
            const btn = event?.target?.closest('button');
            if (btn) {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i data-lucide="check"></i> Copied!';
                lucide.createIcons();
                
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    lucide.createIcons();
                }, 2000);
            }
        }

        // Print styles
        const printStyles = `
            @media print {
                body {
                    background: white !important;
                    color: black !important;
                }
                .qr-section {
                    page-break-inside: avoid;
                }
                .btn {
                    display: none !important;
                }
                .card {
                    border: 1px solid #ccc !important;
                    box-shadow: none !important;
                }
            }
        `;
        
        const styleSheet = document.createElement("style");
        styleSheet.innerText = printStyles;
        document.head.appendChild(styleSheet);
    </script>
</body>
</html>
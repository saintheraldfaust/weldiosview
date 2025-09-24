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
            color: #000000;
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
            color: #000000;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e5e5;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .back-button:hover {
            background: #f5f5f5;
        }

        .profile-container {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            padding: 2rem;
            margin-bottom: 2rem;
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
            background: #000000;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 auto 1rem auto;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #000000;
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
            background: #000000;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #333333;
            color: #ffffff;
            text-decoration: none;
        }

        .btn-secondary {
            background: #ffffff;
            color: #000000;
            border: 1px solid #e5e5e5;
        }

        .btn-secondary:hover {
            background: #f5f5f5;
            text-decoration: none;
            color: #000000;
        }

        .qr-section {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            padding: 2rem;
            text-align: center;
            margin: 2rem 0;
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
            padding: 2rem;
            margin: 2rem 0;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #000000;
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

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-banner"></div>
            <div class="profile-info">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['surname'], 0, 1)); ?>
                </div>
                <div>
                    <h1 class="h1"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['surname']); ?></h1>
                    <p class="text-muted">Student at Weldios Institution</p>
                    <div style="margin: 1rem 0;">
                        <span class="status-badge status-active">
                            <i data-lucide="check-circle"></i>
                            Verified Certificate
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="h3" style="margin: 0; color: white;">
                    <i data-lucide="user" style="width: 20px; height: 20px; margin-right: 0.5rem;"></i>
                    Personal Information
                </h2>
            </div>
            <div class="card-content">
                <div class="data-grid">
                    <div class="data-item">
                        <div class="data-label">Full Name</div>
                        <div class="data-value"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['other_names'] . ' ' . $student['surname']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Date of Birth</div>
                        <div class="data-value"><?php echo htmlspecialchars(date('F j, Y', strtotime($student['date_of_birth']))); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Gender</div>
                        <div class="data-value"><?php echo htmlspecialchars(ucfirst($student['gender'])); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Nationality</div>
                        <div class="data-value"><?php echo htmlspecialchars($student['nationality']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">State of Origin</div>
                        <div class="data-value"><?php echo htmlspecialchars($student['state_of_origin']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Matriculation Number</div>
                        <div class="data-value"><?php echo htmlspecialchars($student['matriculation_number']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Information -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2 class="h3" style="margin: 0; color: white;">
                    <i data-lucide="graduation-cap" style="width: 20px; height: 20px; margin-right: 0.5rem;"></i>
                    Academic Credentials
                </h2>
            </div>
            <div class="card-content">
                <div class="data-grid">
                    <div class="data-item">
                        <div class="data-label">Certificate Number</div>
                        <div class="data-value"><?php echo htmlspecialchars($student['certificate_number']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Programme Type</div>
                        <div class="data-value"><?php echo htmlspecialchars(ucwords($student['programme_type'])); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Programme Title</div>
                        <div class="data-value"><?php echo htmlspecialchars($student['programme_title']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Department</div>
                        <div class="data-value"><?php echo htmlspecialchars($student['department']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Class of Degree</div>
                        <div class="data-value"><?php echo htmlspecialchars($student['class_of_degree']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Year of Graduation</div>
                        <div class="data-value"><?php echo htmlspecialchars($student['year_of_graduation']); ?></div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Issue Date</div>
                        <div class="data-value"><?php echo htmlspecialchars(date('F j, Y', strtotime($student['issue_date']))); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="qr-section">
            <h3 class="h3">Certificate Verification QR Code</h3>
            <p class="text-muted">Scan this QR code to verify this certificate</p>
            <div class="qr-code-container">
                <div id="qrcode"></div>
            </div>
            <div style="margin-top: 1rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <button onclick="copyProfileUrl('<?php echo BASE_URL . 'profile.php?id=' . $student['profile_url']; ?>')" class="btn btn-secondary">
                    <i data-lucide="copy"></i>
                    Copy Profile URL
                </button>
                <button onclick="window.print()" class="btn btn-primary">
                    <i data-lucide="printer"></i>
                    Print Certificate
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 3rem; padding: 2rem; border-top: 1px solid hsl(var(--border));">
            <p class="text-muted text-sm">
                This certificate has been digitally verified by Weldios Institution<br>
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
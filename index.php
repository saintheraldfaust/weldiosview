<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification - Weldios</title>
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
            --dark-color: #1f2937;
            --light-color: #f8fafc;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }

        .container-fluid {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verification-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
            border: none;
        }

        .school-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: var(--primary-color);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 2rem;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(248, 250, 252, 0.8);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
            background: white;
        }

        .btn-verify {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        .btn-verify:active {
            transform: translateY(0);
        }

        .result-card {
            margin-top: 2rem;
            border-radius: 16px;
            border: none;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .valid-result {
            border-left: 5px solid var(--success-color);
            background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
        }

        .invalid-result {
            border-left: 5px solid var(--danger-color);
            background: linear-gradient(135deg, #fef2f2, #fef7f7);
        }

        .student-info {
            display: grid;
            gap: 1rem;
        }

        .info-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1rem;
            color: #111827;
            font-weight: 600;
        }

        .qr-section {
            text-align: center;
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            border: 2px dashed var(--primary-color);
        }

        #qrcode {
            display: inline-block;
            margin: 10px 0;
        }

        #qrcode canvas,
        #qrcode img {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .footer-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .footer-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            margin: 0 1rem;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .result-card {
            animation: fadeInUp 0.5s ease;
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 1rem;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(37, 99, 235, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="verification-card">
            <div class="card-header">
                <div class="school-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="h3 mb-0">Weldios University</h1>
                <p class="mb-0 opacity-75">Certificate Verification Portal</p>
            </div>

            <div class="card-body">
                <form id="verificationForm" method="POST">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="certificateNumber" name="certificate_number" 
                               placeholder="Enter Certificate Number" required>
                        <label for="certificateNumber">
                            <i class="fas fa-certificate me-2"></i>Certificate Number
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-verify">
                        <i class="fas fa-search me-2"></i>Verify Certificate
                    </button>
                </form>

                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p class="mt-2 text-muted">Verifying certificate...</p>
                </div>

                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['certificate_number'])) {
                    require_once 'config/config.php';
                    
                    $certificate_number = sanitize($_POST['certificate_number']);
                    
                    try {
                        $pdo = getDBConnection();
                        $stmt = $pdo->prepare("SELECT * FROM certificate_verification WHERE certificate_number = ?");
                        $stmt->execute([$certificate_number]);
                        $student = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($student) {
                            // Valid certificate
                            $profile_url = BASE_URL . 'profile.php?id=' . $student['profile_url'];
                            echo '<div class="result-card valid-result">';
                            echo '<div class="card-body">';
                            echo '<div class="d-flex align-items-center mb-3">';
                            echo '<i class="fas fa-check-circle text-success me-3" style="font-size: 2rem;"></i>';
                            echo '<div>';
                            echo '<h5 class="mb-1 text-success">Certificate Verified ✓</h5>';
                            echo '<p class="mb-0 text-muted">This certificate is authentic and valid</p>';
                            echo '</div>';
                            echo '</div>';
                            
                            echo '<div class="student-info">';
                            echo '<div class="info-item">';
                            echo '<div class="info-label">Student Name</div>';
                            echo '<div class="info-value">' . htmlspecialchars($student['surname'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) . '</div>';
                            echo '</div>';
                            
                            echo '<div class="info-item">';
                            echo '<div class="info-label">Programme</div>';
                            echo '<div class="info-value">' . htmlspecialchars($student['programme_title']) . '</div>';
                            echo '</div>';
                            
                            echo '<div class="info-item">';
                            echo '<div class="info-label">Department</div>';
                            echo '<div class="info-value">' . htmlspecialchars($student['department']) . '</div>';
                            echo '</div>';
                            
                            if (!empty($student['class_of_degree'])) {
                                echo '<div class="info-item">';
                                echo '<div class="info-label">Class of Degree</div>';
                                echo '<div class="info-value">' . htmlspecialchars($student['class_of_degree']) . '</div>';
                                echo '</div>';
                            }
                            
                            echo '<div class="info-item">';
                            echo '<div class="info-label">Year of Graduation</div>';
                            echo '<div class="info-value">' . htmlspecialchars($student['year_of_graduation']) . '</div>';
                            echo '</div>';
                            
                            echo '<div class="info-item">';
                            echo '<div class="info-label">Matriculation Number</div>';
                            echo '<div class="info-value">' . htmlspecialchars($student['matriculation_number']) . '</div>';
                            echo '</div>';
                            echo '</div>';
                            
                            echo '<div class="qr-section">';
                            echo '<h6 class="mb-3"><i class="fas fa-qrcode me-2"></i>Profile QR Code</h6>';
                            echo '<div id="qrcode"></div>';
                            echo '<p class="mt-3 small text-muted">Scan this QR code to view the complete student profile</p>';
                            echo '<div class="mt-2">';
                            echo '<a href="' . $profile_url . '" class="btn btn-outline-primary btn-sm me-2" target="_blank">';
                            echo '<i class="fas fa-external-link-alt me-2"></i>View Full Profile';
                            echo '</a>';
                            echo '<button class="btn btn-outline-secondary btn-sm" onclick="copyProfileUrl(\'' . $profile_url . '\')" title="Copy Profile URL">';
                            echo '<i class="fas fa-copy me-1"></i>Copy URL';
                            echo '</button>';
                            echo '</div>';
                            echo '</div>';
                            
                            echo '</div>';
                            echo '</div>';
                            
                            // Include QR code generation with reliable library
                            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
                            echo '<script>';
                            echo 'document.addEventListener("DOMContentLoaded", function() {';
                            echo '    try {';
                            echo '        if (typeof QRCode !== "undefined") {';
                            echo '            const qrContainer = document.getElementById("qrcode");';
                            echo '            if (qrContainer) {';
                            echo '                qrContainer.style.textAlign = "center";';
                            echo '                qrContainer.style.padding = "10px";';
                            echo '                const qr = new QRCode(qrContainer, {';
                            echo '                    text: "' . $profile_url . '",';
                            echo '                    width: 150,';
                            echo '                    height: 150,';
                            echo '                    colorDark: "#2563eb",';
                            echo '                    colorLight: "#ffffff",';
                            echo '                    correctLevel: QRCode.CorrectLevel.M';
                            echo '                });';
                            echo '                console.log("QR Code generated successfully for profile");';
                            echo '            }';
                            echo '        } else {';
                            echo '            console.error("QRCode library not loaded");';
                            echo '            document.getElementById("qrcode").innerHTML = "<p class=\"text-muted small\">QR Code could not be generated</p>";';
                            echo '        }';
                            echo '    } catch (error) {';
                            echo '        console.error("QR Code generation error:", error);';
                            echo '        document.getElementById("qrcode").innerHTML = "<p class=\"text-muted small\">QR Code generation failed</p>";';
                            echo '    }';
                            echo '});';
                            echo '</script>';
                            
                        } else {
                            // Invalid certificate
                            echo '<div class="result-card invalid-result">';
                            echo '<div class="card-body text-center">';
                            echo '<i class="fas fa-times-circle text-danger mb-3" style="font-size: 3rem;"></i>';
                            echo '<h5 class="text-danger">Certificate Not Found</h5>';
                            echo '<p class="text-muted">The certificate number "' . htmlspecialchars($certificate_number) . '" was not found in our database.</p>';
                            echo '<p class="small text-muted mt-3">Please verify the certificate number and try again, or contact the institution for assistance.</p>';
                            echo '</div>';
                            echo '</div>';
                        }
                        
                    } catch (Exception $e) {
                        echo '<div class="result-card invalid-result">';
                        echo '<div class="card-body text-center">';
                        echo '<i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 3rem;"></i>';
                        echo '<h5 class="text-warning">Verification Error</h5>';
                        echo '<p class="text-muted">An error occurred while verifying the certificate. Please try again later.</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>

                <div class="footer-links">
                    <a href="admin/login.php"><i class="fas fa-user-shield me-1"></i>Admin Login</a>
                    <a href="#"><i class="fas fa-info-circle me-1"></i>About</a>
                    <a href="#"><i class="fas fa-envelope me-1"></i>Contact</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('verificationForm').addEventListener('submit', function() {
            document.getElementById('loading').classList.add('show');
        });

        // Auto-format certificate number input
        document.getElementById('certificateNumber').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            e.target.value = value;
        });

        // Copy URL function for profile links
        function copyProfileUrl(url) {
            // Method 1: Modern Clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(function() {
                    showCopySuccess();
                }).catch(function(err) {
                    console.error('Clipboard API failed: ', err);
                    fallbackCopyTextToClipboard(url);
                });
            } else {
                // Method 2: Fallback for older browsers
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
                console.error('Fallback copy failed: ', err);
                alert('Failed to copy URL. Please copy manually: ' + text);
            }
            
            document.body.removeChild(textArea);
        }
        
        function showCopySuccess() {
            const btn = event?.target?.closest('button');
            if (btn) {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check text-success me-1"></i>Copied!';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-secondary');
                
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            }
        }
    </script>
</body>
</html>
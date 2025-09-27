<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification - weldios university</title>
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
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e5e5;
        }

        .logo {
            width: 80px;
            height: auto;
            margin: 0 auto 1.5rem;
        }

        .title {
            font-size: 2rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 1rem;
            color: #666666;
        }

        .form {
            background-color: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #000000;
            margin-bottom: 0.5rem;
        }

        .input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            font-size: 1rem;
            font-family: inherit;
            background-color: #ffffff;
            color: #000000;
            transition: border-color 0.2s ease;
        }

        .input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .input::placeholder {
            color: #999999;
        }

        .button {
            width: 100%;
            padding: 0.875rem;
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .button:hover {
            background-color: #1d4ed8;
        }

        .button:disabled {
            background-color: #999999;
            cursor: not-allowed;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 1rem;
            color: #666666;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #e5e5e5;
            border-top: 2px solid #000000;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .result {
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            margin-top: 2rem;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .result.success {
            border-color: #2563eb;
        }

        .result.error {
            border-color: #ef4444;
        }

        .result-header {
            padding: 1.5rem;
            text-align: center;
        }

        .result.success .result-header {
            background-color: #eff6ff;
            color: #2563eb;
        }

        .result.error .result-header {
            background-color: #fef2f2;
            color: #dc2626;
        }

        .result-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .result-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .result-subtitle {
            font-size: 0.875rem;
            opacity: 0.8;
        }

        .result-body {
            padding: 2rem;
        }

        .student-info {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e5e5;
        }

        .student-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }

        .student-program {
            font-size: 1rem;
            color: #666666;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-item {
            background-color: #f9f9f9;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            padding: 1rem;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #666666;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 0.875rem;
            font-weight: 500;
            color: #000000;
        }

        .qr-section {
            background-color: #f9f9f9;
            border: 2px dashed #e5e5e5;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            margin: 2rem 0;
        }

        .qr-title {
            font-size: 1rem;
            font-weight: 600;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }

        .qr-subtitle {
            font-size: 0.875rem;
            color: #666666;
            margin-bottom: 1.5rem;
        }

        #qrcode {
            display: inline-block;
            margin: 1rem 0;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .button-secondary {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #e5e5e5;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s ease;
        }

        .button-secondary:hover {
            background-color: #f9f9f9;
            text-decoration: none;
            color: #000000;
        }

        .footer {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e5e5;
        }

        .footer a {
            color: #666666;
            text-decoration: none;
            font-size: 0.875rem;
            margin: 0 1rem;
        }

        .footer a:hover {
            color: #000000;
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }
            
            .form {
                padding: 1.5rem;
            }
            
            .result-body {
                padding: 1.5rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .button-group {
                flex-direction: column;
            }
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
            background: #2563eb;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            cursor: pointer;
        }

        .btn-verify:hover {
            background: #1d4ed8;
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
    <div class="container">
        <div class="header">
            <img src="assets/weldioslogo.png" alt="weldios university Logo" class="logo">
            <h1 class="title">weldios university</h1>
            <p class="subtitle">Certificate Verification Portal</p>
        </div>

        <div class="form">
            <form id="verificationForm" method="POST">
                <div class="form-group">
                    <label for="certificateNumber" class="label">Certificate Number</label>
                    <input 
                        type="text" 
                        class="input" 
                        id="certificateNumber" 
                        name="certificate_number" 
                        placeholder="Enter certificate number (e.g., WLD/2024/001)" 
                        required
                    >
                </div>
                
                <button type="submit" class="button" id="verifyBtn">
                    Verify Certificate
                </button>
            </form>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                Verifying certificate...
            </div>
        </div>

                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['certificate_number'])) {
                    require_once 'config/config.php';
                    
                    // Helper function to safely display data
                    function safeDisplay($value, $placeholder = 'Not available') {
                        return htmlspecialchars(!empty($value) && $value !== '0000-00-00' ? $value : $placeholder);
                    }
                    
                    $certificate_number = sanitize($_POST['certificate_number']);
                    
                    try {
                        $pdo = getDBConnection();
                        $stmt = $pdo->prepare("
                            SELECT 
                                c.certificate_number, c.programme_type, c.programme_title, c.department, 
                                c.class_of_degree, c.year_of_graduation, c.profile_url, c.issue_date, c.status as certificate_status,
                                s.surname, s.first_name, s.middle_name, s.matriculation_number, s.email, s.phone
                            FROM certificates c 
                            JOIN students s ON c.student_id = s.id 
                            WHERE c.certificate_number = ? AND c.status = 'active'
                        ");
                        $stmt->execute([$certificate_number]);
                        $student = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($student) {
                            // Valid certificate
                            $profile_url = BASE_URL . 'profile.php?id=' . $student['profile_url'];
                            echo '<div class="result success">';
                            echo '<div class="result-header">';
                            echo '<div class="result-icon">✓</div>';
                            echo '<h3 class="result-title">Certificate Verified</h3>';
                            echo '<p class="result-subtitle">This certificate is authentic and valid</p>';
                            echo '</div>';
                            
                            echo '<div class="result-body">';
                            echo '<div class="student-info">';
                            echo '<h4 class="student-name">';
                            $fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['surname'] ?? ''));
                            echo safeDisplay($fullName, 'Name Not Available');
                            echo '</h4>';
                            echo '<p class="student-program">';
                            echo safeDisplay($student['programme_title'] ?? '');
                            echo '</p>';
                            echo '</div>';
                            
                            echo '<div class="info-grid">';
                            echo '<div class="info-item">';
                            echo '<div class="info-label">Certificate Number</div>';
                            echo '<div class="info-value">' . safeDisplay($student['certificate_number'] ?? '') . '</div>';
                            echo '</div>';
                            
                            echo '<div class="info-item">';
                            echo '<div class="info-label">Department</div>';
                            echo '<div class="info-value">' . safeDisplay($student['department'] ?? '') . '</div>';
                            echo '</div>';
                            
                            echo '<div class="info-item">';
                            echo '<div class="info-label">Class of Degree</div>';
                            echo '<div class="info-value">' . safeDisplay($student['class_of_degree'] ?? '') . '</div>';
                            echo '</div>';
                            
                            echo '<div class="info-item">';
                            echo '<div class="info-label">Year of Graduation</div>';
                            echo '<div class="info-value">' . safeDisplay($student['year_of_graduation'] ?? '') . '</div>';
                            echo '</div>';
                            
                            echo '<div class="info-item">';
                            echo '<div class="info-label">Matriculation Number</div>';
                            echo '<div class="info-value">' . safeDisplay($student['matriculation_number'] ?? '') . '</div>';
                            echo '</div>';
                            echo '</div>';
                            
                            echo '<div class="qr-section">';
                            echo '<h4 class="qr-title">Verification QR Code</h4>';
                            echo '<p class="qr-subtitle">Scan to view complete certificate details</p>';
                            echo '<div id="qrcode"></div>';
                            echo '<div class="button-group">';
                            echo '<a href="' . $profile_url . '" class="button-secondary" target="_blank">';
                            echo 'View Full Profile';
                            echo '</a>';
                            echo '<button class="button-secondary" onclick="copyProfileUrl(\'' . $profile_url . '\')" title="Copy Profile URL">';
                            echo 'Copy URL';
                            echo '</button>';
                            echo '</div>';
                            echo '</div>';
                            
                            echo '</div>';
                            echo '</div>';
                            
                            // Clean QR code generation
                            echo '<script>';
                            echo 'document.addEventListener("DOMContentLoaded", function() {';
                            echo '    setTimeout(function() {';
                            echo '        try {';
                            echo '            if (typeof QRCode !== "undefined") {';
                            echo '                const qrContainer = document.getElementById("qrcode");';
                            echo '                if (qrContainer) {';
                            echo '                    const qr = new QRCode(qrContainer, {';
                            echo '                        text: "' . $profile_url . '",';
                            echo '                        width: 160,';
                            echo '                        height: 160,';
                            echo '                        colorDark: "#000000",';
                            echo '                        colorLight: "#ffffff",';
                            echo '                        correctLevel: QRCode.CorrectLevel.M';
                            echo '                    });';
                            echo '                }';
                            echo '            } else {';
                            echo '                document.getElementById("qrcode").innerHTML = "<p style=\"color: #666;\">QR Code unavailable</p>";';
                            echo '            }';
                            echo '        } catch (error) {';
                            echo '            console.error("QR Code generation error:", error);';
                            echo '            document.getElementById("qrcode").innerHTML = "<p style=\"color: #666;\">QR Code generation failed</p>";';
                            echo '        }';
                            echo '    }, 500);';
                            echo '});';
                            echo '</script>';
                            
                        } else {
                            // Invalid certificate
                            echo '<div class="result error">';
                            echo '<div class="result-header">';
                            echo '<div class="result-icon">✗</div>';
                            echo '<h3 class="result-title">Certificate Not Found</h3>';
                            echo '<p class="result-subtitle">This certificate could not be verified</p>';
                            echo '</div>';
                            echo '<div class="result-body">';
                            echo '<p style="text-align: center; margin-bottom: 1rem; color: #666;">The certificate number "' . htmlspecialchars($certificate_number) . '" was not found in our database.</p>';
                            echo '<p style="text-align: center; font-size: 0.875rem; color: #666;">Please verify the certificate number and try again, or contact the institution for assistance.</p>';
                            echo '</div>';
                            echo '</div>';
                        }
                        
                    } catch (Exception $e) {
                        echo '<div class="result error">';
                        echo '<div class="result-header">';
                        echo '<div class="result-icon">⚠</div>';
                        echo '<h3 class="result-title">Verification Error</h3>';
                        echo '<p class="result-subtitle">An error occurred during verification</p>';
                        echo '</div>';
                        echo '<div class="result-body">';
                        echo '<p style="text-align: center; color: #666;">An error occurred while verifying the certificate. Please try again later.</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>

        <!-- <div class="footer">
            <a href="admin/login.php">Admin Login</a>
            <a href="#">About</a>
            <a href="#">Contact</a>
        </div> -->
    </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        // Form handling
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('verifyBtn');
            const loading = document.getElementById('loading');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Verifying...';
            loading.classList.add('show');
        });

        // Auto-format certificate number input
        document.getElementById('certificateNumber').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9\/]/g, '');
            e.target.value = value;
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
                const originalText = btn.textContent;
                btn.textContent = 'Copied!';
                btn.style.backgroundColor = '#22c55e';
                
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.backgroundColor = '';
                }, 2000);
            }
        }
    </script>
</body>
</html>
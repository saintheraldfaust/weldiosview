<?php
require_once '../config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Weldios Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --danger-color: #ef4444;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            border: none;
        }

        .admin-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .card-body {
            padding: 2.5rem;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(248, 250, 252, 0.8);
            height: auto;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
            background: white;
        }

        .btn-login {
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
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2, #fef7f7);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .back-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            text-decoration: underline;
            color: var(--secondary-color);
        }

        .security-notice {
            background: linear-gradient(135deg, #eff6ff, #f0f9ff);
            border: 1px solid rgba(37, 99, 235, 0.2);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            text-align: center;
        }

        .floating-label {
            position: relative;
        }

        .floating-label label {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            background: transparent;
            padding: 0 0.5rem;
            color: #6b7280;
            transition: all 0.3s ease;
            pointer-events: none;
            font-size: 1rem;
        }

        .floating-label input:focus + label,
        .floating-label input:not(:placeholder-shown) + label {
            top: 0;
            font-size: 0.875rem;
            color: var(--primary-color);
            background: white;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            animation: slideInUp 0.6s ease;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card-header">
            <div class="admin-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1 class="h4 mb-0">Admin Portal</h1>
            <p class="mb-0 opacity-75">Weldios University</p>
        </div>

        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Username" required>
                    <label for="username">
                        <i class="fas fa-user me-2"></i>Username
                    </label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Password" required>
                    <label for="password">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>

            <div class="security-notice">
                <i class="fas fa-shield-alt text-primary me-2"></i>
                <small class="text-muted">
                    Authorized access only. All activities are logged and monitored.
                </small>
            </div>

            <div class="back-link">
                <a href="<?php echo BASE_URL; ?>">
                    <i class="fas fa-arrow-left me-2"></i>Back to Verification Portal
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
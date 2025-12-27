<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

$session = new SessionManager();
$functions = new NovaCloudFunctions();

if ($session->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$token = $_GET['token'] ?? '';
$error = '';
$success = false;
$passwordStrength = 0;
$passwordSuggestions = [];

// Verify token
if ($token) {
    $db = Database::getInstance();
    $sql = "SELECT user_id FROM password_resets 
            WHERE token = ? AND expires_at > NOW() AND used = FALSE";
    $result = $db->query($sql, [$token]);
    
    // If DB check fails, allow a session-verified fallback
    $sessionVerified = $_SESSION['reset_verified'] ?? false;
    $sessionUserId = $_SESSION['reset_user_id'] ?? null;
    $sessionToken = $_SESSION['reset_token'] ?? null;

    $allowReset = false;
    $userId = null;

    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $userId = $data['user_id'];
        $allowReset = true;
    } else {
        // Fallback: if session indicates the user was verified via security question, allow reset
        if (!empty($sessionVerified) && (!empty($sessionUserId) || (!empty($sessionToken) && hash_equals($sessionToken, $token)))) {
            $userId = $sessionUserId ?: null;
            $allowReset = true;
        } else {
            $error = 'Invalid or expired reset token';
        }
    }

    if ($allowReset) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($password !== $confirmPassword) {
                $error = 'Passwords do not match';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters';
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $error = 'Password must contain at least one uppercase letter';
            } elseif (!preg_match('/[a-z]/', $password)) {
                $error = 'Password must contain at least one lowercase letter';
            } elseif (!preg_match('/[0-9]/', $password)) {
                $error = 'Password must contain at least one number';
            } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
                $error = 'Password must contain at least one special character';
            } else {
                // Update password (use session user id if DB didn't return one)
                if (empty($userId) && !empty($sessionUserId)) {
                    $userId = $sessionUserId;
                }

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $db->query($sql, [$hashedPassword, $userId]);

                // Mark token as used if it exists in DB (use either GET token or session token)
                $tokenToMark = $token ?: $sessionToken;
                if (!empty($tokenToMark)) {
                    $sql = "UPDATE password_resets SET used = TRUE WHERE token = ?";
                    $db->query($sql, [$tokenToMark]);
                }

                // Clear session reset flags
                unset($_SESSION['reset_verified'], $_SESSION['reset_token'], $_SESSION['reset_user_id']);

                $success = true;
            }
        }
    }
} else {
    $error = 'No reset token provided';
}

// Calculate password strength (for display purposes)
if (isset($_POST['password'])) {
    $password = $_POST['password'];
    $passwordStrength = 0;
    $passwordSuggestions = [];
    
    // Length checks
    if (strlen($password) >= 8) $passwordStrength += 20;
    if (strlen($password) >= 12) $passwordStrength += 10;
    
    // Character type checks
    if (preg_match('/[A-Z]/', $password)) $passwordStrength += 20;
    if (preg_match('/[a-z]/', $password)) $passwordStrength += 20;
    if (preg_match('/[0-9]/', $password)) $passwordStrength += 15;
    if (preg_match('/[^A-Za-z0-9]/', $password)) $passwordStrength += 15;
    
    // Suggestions
    if (!preg_match('/[A-Z]/', $password)) $passwordSuggestions[] = 'Add an uppercase letter';
    if (!preg_match('/[a-z]/', $password)) $passwordSuggestions[] = 'Add a lowercase letter';
    if (!preg_match('/[0-9]/', $password)) $passwordSuggestions[] = 'Add a number';
    if (!preg_match('/[^A-Za-z0-9]/', $password)) $passwordSuggestions[] = 'Add a special character';
    if (strlen($password) < 12) $passwordSuggestions[] = 'Make it longer (12+ characters)';
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | NovaCloud</title>
    <?php
    $fav = 'http://localhost//myproject/NovaCloudV2/favicon.png';
    echo '<link rel="icon" type="image/png" href="' . htmlspecialchars($fav) . '">';
    echo '<link rel="apple-touch-icon" href="' . htmlspecialchars($fav) . '">';
    ?>
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .floating-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .circle-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
        }

        .circle-2 {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 1200px;
            display: flex;
            box-shadow: var(--card-shadow);
            border-radius: 24px;
            overflow: hidden;
            background: white;
            min-height: 700px;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-left {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .auth-left::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            animation: moveBackground 20s linear infinite;
        }

        @keyframes moveBackground {
            from { transform: translate(0, 0); }
            to { transform: translate(-50%, -50%); }
        }

        .auth-left-content {
            position: relative;
            z-index: 1;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--primary);
            font-weight: bold;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .welcome-title {
            font-size: 42px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
            background: linear-gradient(90deg, #fff, rgba(255,255,255,0.8));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-subtitle {
            font-size: 18px;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 40px;
            font-weight: 300;
        }

        .features {
            list-style: none;
            margin-top: 40px;
        }

        .features li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .feature-icon {
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-form-container {
            width: 100%;
            max-width: 400px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .form-subtitle {
            color: var(--gray);
            font-size: 16px;
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 20px;
            right: 20px;
            height: 3px;
            background: var(--light-gray);
            z-index: 1;
        }

        .progress-bar {
            position: absolute;
            top: 15px;
            left: 20px;
            height: 3px;
            background: var(--primary);
            z-index: 2;
            transition: var(--transition);
            width: 100%;
        }

        .step {
            position: relative;
            z-index: 3;
            text-align: center;
            flex: 1;
        }

        .step-icon {
            width: 30px;
            height: 30px;
            margin: 0 auto 10px;
            border-radius: 50%;
            background: var(--light-gray);
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
        }

        .step.active .step-icon {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        .step.completed .step-icon {
            background: var(--success);
            color: white;
        }

        .step-label {
            font-size: 12px;
            color: var(--gray);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .step.active .step-label {
            color: var(--primary);
            font-weight: 600;
        }

        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.3s ease;
            border: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            color: white;
        }

        .alert-success {
            background: linear-gradient(135deg, #38b000, #2d9e00);
            color: white;
        }

        .alert-info {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            font-size: 16px;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-control::placeholder {
            color: #adb5bd;
        }

        /* Password Strength Indicator */
        .password-strength-container {
            margin-top: 12px;
        }

        .strength-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
            color: var(--gray);
        }

        .strength-bar {
            height: 6px;
            border-radius: 3px;
            background: var(--light-gray);
            overflow: hidden;
            margin-bottom: 8px;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: var(--transition);
        }

        .strength-text {
            font-size: 12px;
            font-weight: 600;
        }

        .strength-weak .strength-fill {
            background: linear-gradient(90deg, #ff6b6b, #ff4757);
            width: 25%;
        }
        .strength-weak .strength-text {
            color: #ff4757;
        }

        .strength-fair .strength-fill {
            background: linear-gradient(90deg, #ffa502, #ff7b00);
            width: 50%;
        }
        .strength-fair .strength-text {
            color: #ff7b00;
        }

        .strength-good .strength-fill {
            background: linear-gradient(90deg, #2ed573, #1dd1a1);
            width: 75%;
        }
        .strength-good .strength-text {
            color: #1dd1a1;
        }

        .strength-strong .strength-fill {
            background: linear-gradient(90deg, #1e90ff, #3742fa);
            width: 100%;
        }
        .strength-strong .strength-text {
            color: #3742fa;
        }

        .strength-suggestions {
            margin-top: 12px;
            font-size: 12px;
            color: var(--gray);
        }

        .strength-suggestions ul {
            list-style: none;
            padding-left: 0;
        }

        .strength-suggestions li {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .strength-suggestions .valid {
            color: var(--success);
        }

        .strength-suggestions .invalid {
            color: var(--danger);
        }

        .strength-suggestions i {
            font-size: 10px;
        }

        /* Password Requirements */
        .password-requirements {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.05), rgba(114, 9, 183, 0.05));
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
            border-left: 4px solid var(--primary);
        }

        .password-requirements h4 {
            color: var(--dark);
            margin-bottom: 12px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .password-requirements ul {
            list-style: none;
            padding-left: 0;
        }

        .password-requirements li {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 13px;
            color: var(--gray);
        }

        .password-requirements li i {
            color: var(--success);
            font-size: 12px;
        }

        /* Password Visibility Toggle */
        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
        }

        /* Buttons */
        .btn-primary {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
        }

        .btn-secondary {
            width: 100%;
            padding: 18px;
            background: var(--light-gray);
            color: var(--dark);
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 12px;
        }

        .btn-secondary:hover {
            background: #e2e6ea;
            border-color: #dae0e5;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .form-actions .btn-primary,
        .form-actions .btn-secondary {
            flex: 1;
        }

        /* Success State */
        .success-state {
            text-align: center;
            padding: 40px 0;
            animation: fadeIn 0.6s ease;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--success), #38b000);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 36px;
            color: white;
        }

        .success-message {
            font-size: 18px;
            color: var(--dark);
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .success-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .success-actions .btn-primary {
            max-width: 200px;
        }

        /* Footer Links */
        .footer-links {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: var(--gray);
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            margin: 0 8px;
        }

        @media (max-width: 992px) {
            .auth-wrapper {
                flex-direction: column;
                max-width: 500px;
                min-height: auto;
            }
            
            .auth-left {
                padding: 40px 30px;
            }
            
            .welcome-title {
                font-size: 32px;
            }
            
            .progress-steps {
                padding: 0 10px;
            }
            
            .progress-steps::before,
            .progress-bar {
                left: 10px;
                right: 10px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .auth-wrapper {
                border-radius: 16px;
            }
            
            .auth-left,
            .auth-right {
                padding: 30px 20px;
            }
            
            .welcome-title {
                font-size: 28px;
            }
            
            .form-title {
                font-size: 24px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .step-label {
                font-size: 10px;
            }
            
            .success-actions {
                flex-direction: column;
            }
            
            .success-actions .btn-primary {
                max-width: 100%;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="floating-bg">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
    </div>
    
    <div class="auth-wrapper">
        <div class="auth-left">
            <div class="auth-left-content">
                <div class="logo">
                    <div class="logo-icon">NC</div>
                    <div class="logo-text">NovaCloud</div>
                </div>
                
                <h1 class="welcome-title">Secure Your Account</h1>
                <p class="welcome-subtitle">Create a strong, unique password to protect your NovaCloud account and data.</p>
                
                <ul class="features">
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <span>Strong encryption for your password</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-shield-check"></i>
                        </div>
                        <span>Real-time password strength analysis</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <span>Immediate access after reset</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <span>Automatically sync across devices</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="auth-right">
            <div class="auth-form-container">
                <div class="form-header">
                    <h2 class="form-title">Reset Password</h2>
                    <p class="form-subtitle">Step 3: Create your new password</p>
                </div>
                
                <!-- Progress Steps -->
                <div class="progress-steps">
                    <div class="progress-bar" style="width: 100%"></div>
                    <div class="step completed">
                        <div class="step-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="step-label">Verify Account</div>
                    </div>
                    <div class="step completed">
                        <div class="step-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="step-label">Security Check</div>
                    </div>
                    <div class="step active">
                        <div class="step-icon">3</div>
                        <div class="step-label">New Password</div>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        <?php if ($error == 'Invalid or expired reset token'): ?>
                            <div style="margin-top: 10px;">
                                <a href="forgot-password.php" style="color: white; text-decoration: underline;">
                                    <i class="fas fa-redo"></i> Request a new reset link
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-state">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <h3>Password Reset Successful!</h3>
                        <p class="success-message">
                            Your password has been successfully updated. You can now log in to your account with your new password.
                        </p>
                        <div class="success-actions">
                            <a href="auth.php" class="btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login Now
                            </a>
                            <a href="dashboard.php" class="btn-secondary">
                                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                            </a>
                        </div>
                    </div>
                <?php elseif (!$error && $token && $allowReset): ?>
                    <div class="password-requirements">
                        <h4><i class="fas fa-shield-alt"></i> Password Requirements</h4>
                        <ul>
                            <li><i class="fas fa-check-circle"></i> At least 8 characters long</li>
                            <li><i class="fas fa-check-circle"></i> One uppercase letter (A-Z)</li>
                            <li><i class="fas fa-check-circle"></i> One lowercase letter (a-z)</li>
                            <li><i class="fas fa-check-circle"></i> One number (0-9)</li>
                            <li><i class="fas fa-check-circle"></i> One special character (!@#$%^&*)</li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="" id="resetForm">
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-key"></i> New Password
                            </label>
                            <div class="password-wrapper">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    class="form-control" 
                                    placeholder="Enter your new password"
                                    minlength="8"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="toggle-password" id="togglePassword1">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength-container" id="passwordStrength">
                                <div class="strength-label">
                                    <span>Password Strength</span>
                                    <span class="strength-text">Weak</span>
                                </div>
                                <div class="strength-bar">
                                    <div class="strength-fill"></div>
                                </div>
                                <div class="strength-suggestions" id="strengthSuggestions">
                                    <ul>
                                        <li class="invalid"><i class="fas fa-times"></i> At least 8 characters</li>
                                        <li class="invalid"><i class="fas fa-times"></i> One uppercase letter</li>
                                        <li class="invalid"><i class="fas fa-times"></i> One lowercase letter</li>
                                        <li class="invalid"><i class="fas fa-times"></i> One number</li>
                                        <li class="invalid"><i class="fas fa-times"></i> One special character</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-key"></i> Confirm Password
                            </label>
                            <div class="password-wrapper">
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    required 
                                    class="form-control" 
                                    placeholder="Confirm your new password"
                                    minlength="8"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="toggle-password" id="togglePassword2">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" style="margin-top: 8px; font-size: 13px; display: none;">
                                <span class="invalid"><i class="fas fa-times"></i> Passwords don't match</span>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Reset Password
                            </button>
                            <a href="auth.php" class="btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
                
                <?php if (!$success && !$allowReset && $error && $error !== 'Invalid or expired reset token'): ?>
                    <div class="help-section" style="margin-top: 24px;">
                        <p><i class="fas fa-life-ring"></i> Need assistance?</p>
                        <a href="contact.php">
                            <i class="fas fa-headset"></i> Contact Support Team
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="footer-links">
                    <a href="privacy.php">Privacy Policy</a> • 
                    <a href="terms.php">Terms of Service</a> • 
                    <a href="help.php">Help Center</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword1 = document.getElementById('togglePassword1');
            const togglePassword2 = document.getElementById('togglePassword2');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            
            function setupTogglePassword(button, input) {
                if (button && input) {
                    button.addEventListener('click', function() {
                        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                        input.setAttribute('type', type);
                        this.innerHTML = type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
                    });
                }
            }
            
            setupTogglePassword(togglePassword1, passwordInput);
            setupTogglePassword(togglePassword2, confirmPasswordInput);
            
            // Password strength indicator
            const passwordStrength = document.getElementById('passwordStrength');
            const strengthFill = document.querySelector('.strength-fill');
            const strengthText = document.querySelector('.strength-text');
            const suggestions = document.querySelectorAll('#strengthSuggestions li');
            
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;
                    
                    // Length checks
                    if (password.length >= 8) {
                        strength += 20;
                        updateSuggestion(0, true);
                    } else {
                        updateSuggestion(0, false);
                    }
                    if (password.length >= 12) strength += 10;
                    
                    // Character type checks
                    if (/[A-Z]/.test(password)) {
                        strength += 20;
                        updateSuggestion(1, true);
                    } else {
                        updateSuggestion(1, false);
                    }
                    
                    if (/[a-z]/.test(password)) {
                        strength += 20;
                        updateSuggestion(2, true);
                    } else {
                        updateSuggestion(2, false);
                    }
                    
                    if (/[0-9]/.test(password)) {
                        strength += 15;
                        updateSuggestion(3, true);
                    } else {
                        updateSuggestion(3, false);
                    }
                    
                    if (/[^A-Za-z0-9]/.test(password)) {
                        strength += 15;
                        updateSuggestion(4, true);
                    } else {
                        updateSuggestion(4, false);
                    }
                    
                    // Update strength bar
                    const width = Math.min(strength, 100);
                    strengthFill.style.width = width + '%';
                    
                    // Update color and text
                    passwordStrength.className = 'password-strength-container';
                    if (strength < 40) {
                        passwordStrength.classList.add('strength-weak');
                        strengthText.textContent = 'Weak';
                    } else if (strength < 70) {
                        passwordStrength.classList.add('strength-fair');
                        strengthText.textContent = 'Fair';
                    } else if (strength < 90) {
                        passwordStrength.classList.add('strength-good');
                        strengthText.textContent = 'Good';
                    } else {
                        passwordStrength.classList.add('strength-strong');
                        strengthText.textContent = 'Strong';
                    }
                    
                    // Check password match
                    checkPasswordMatch();
                });
            }
            
            function updateSuggestion(index, isValid) {
                if (suggestions[index]) {
                    suggestions[index].className = isValid ? 'valid' : 'invalid';
                    suggestions[index].innerHTML = (isValid ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>') + suggestions[index].innerHTML.substring(suggestions[index].innerHTML.indexOf('>') + 1);
                }
            }
            
            // Check password match
            function checkPasswordMatch() {
                const passwordMatch = document.getElementById('passwordMatch');
                const matchSpan = passwordMatch.querySelector('span');
                
                if (passwordInput && confirmPasswordInput) {
                    const password = passwordInput.value;
                    const confirmPassword = confirmPasswordInput.value;
                    
                    if (confirmPassword === '') {
                        passwordMatch.style.display = 'none';
                        return;
                    }
                    
                    if (password === confirmPassword) {
                        passwordMatch.style.display = 'block';
                        matchSpan.className = 'valid';
                        matchSpan.innerHTML = '<i class="fas fa-check"></i> Passwords match';
                    } else {
                        passwordMatch.style.display = 'block';
                        matchSpan.className = 'invalid';
                        matchSpan.innerHTML = '<i class="fas fa-times"></i> Passwords don\'t match';
                    }
                }
            }
            
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', checkPasswordMatch);
            }
            
            // Form validation
            const resetForm = document.getElementById('resetForm');
            if (resetForm) {
                resetForm.addEventListener('submit', function(e) {
                    const password = passwordInput.value;
                    const confirmPassword = confirmPasswordInput.value;
                    const submitBtn = this.querySelector('button[type="submit"]');
                    
                    // Check password strength
                    const hasUpper = /[A-Z]/.test(password);
                    const hasLower = /[a-z]/.test(password);
                    const hasNumber = /[0-9]/.test(password);
                    const hasSpecial = /[^A-Za-z0-9]/.test(password);
                    
                    if (password.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 8 characters long');
                        passwordInput.focus();
                        return false;
                    }
                    
                    if (!hasUpper) {
                        e.preventDefault();
                        alert('Password must contain at least one uppercase letter');
                        passwordInput.focus();
                        return false;
                    }
                    
                    if (!hasLower) {
                        e.preventDefault();
                        alert('Password must contain at least one lowercase letter');
                        passwordInput.focus();
                        return false;
                    }
                    
                    if (!hasNumber) {
                        e.preventDefault();
                        alert('Password must contain at least one number');
                        passwordInput.focus();
                        return false;
                    }
                    
                    if (!hasSpecial) {
                        e.preventDefault();
                        alert('Password must contain at least one special character');
                        passwordInput.focus();
                        return false;
                    }
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Passwords do not match');
                        confirmPasswordInput.focus();
                        return false;
                    }
                    
                    // Add loading state
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting Password...';
                        submitBtn.disabled = true;
                    }
                    
                    return true;
                });
            }
            
            // Input focus effects
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
            
            // Auto-focus password input
            if (passwordInput && !<?php echo $success ? 'true' : 'false'; ?>) {
                setTimeout(() => passwordInput.focus(), 300);
            }
        });
    </script>
</body>
</html>
<?php include 'includes/footer.php'; ?>
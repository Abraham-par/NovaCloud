<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

$session = new SessionManager();
$functions = new NovaCloudFunctions();

if ($session->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$step = $_GET['step'] ?? 'email';
$error = '';
$success = '';
$email = '';
$security_question = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($step == 'email') {
        $identity = trim($_POST['identity'] ?? '');

        if ($identity === '') {
            $error = 'Please enter your username or email address';
        } else {
            $db = Database::getInstance();
            $sql = "SELECT id, username, email FROM users WHERE email = ? OR username = ? LIMIT 1";
            $result = $db->query($sql, [$identity, $identity]);

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $email = $user['email'];
                
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $sql = "INSERT INTO password_resets (user_id, token, expires_at, used) VALUES (?, ?, ?, FALSE)";
                    $insertRes = $db->query($sql, [$user['id'], $token, $expires]);

                    if ($insertRes === false) {
                        // Insert failed; set error and do not redirect
                        $error = 'Unable to initiate reset. Please try again later.';
                    } else {
                        // Verify token exists before redirecting
                        $check = $db->query("SELECT id FROM password_resets WHERE token = ? LIMIT 1", [$token]);
                        if ($check && $check->num_rows > 0) {
                            $_SESSION['reset_token'] = $token;
                            $_SESSION['reset_user_id'] = $user['id'];
                            $_SESSION['reset_email'] = $email;

                            // Log token creation for debugging (local dev only)
                            error_log('[NovaCloud] Created password reset token for user_id=' . $user['id'] . ' token=' . $token);

                            header('Location: forgot-password.php?step=security&token=' . $token);
                            exit();
                        } else {
                            $error = 'Unable to generate a valid reset token. Please try again.';
                            error_log('[NovaCloud] Failed to verify inserted reset token for user_id=' . $user['id']);
                        }
                    }
            } else {
                $error = 'User not found. Please check your username or email.';
            }
        }
    }
    
    if ($step == 'security') {
        $token = $_POST['token'] ?? '';
        $answer = trim($_POST['security_answer'] ?? '');

        if (empty($token)) {
            $error = 'Missing security token';
        } else {
            $db = Database::getInstance();
            $sql = "SELECT pr.user_id, pr.expires_at, pr.used, u.security_question, u.security_answer FROM password_resets pr
                    JOIN users u ON pr.user_id = u.id
                    WHERE pr.token = ? LIMIT 1";
            $result = $db->query($sql, [$token]);

            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                $security_question = $data['security_question'];
                $expiresAt = $data['expires_at'];
                $usedFlag = (int)$data['used'];

                $nowTs = time();
                $expiresTs = $expiresAt ? strtotime($expiresAt) : 0;

                if ($usedFlag === 1) {
                    $error = 'This reset token has already been used. Please start again.';
                } elseif ($expiresTs > 0 && $expiresTs < $nowTs) {
                    $error = 'Reset token expired. Please start the process again.';
                } elseif (strtolower(trim($answer)) == strtolower(trim($data['security_answer']))) {
                    $_SESSION['reset_verified'] = true;
                    $_SESSION['reset_user_id'] = $data['user_id'];

                    header('Location: reset-password.php?token=' . $token);
                    exit();
                } else {
                    $error = 'Incorrect answer. Please try again.';
                }
            } else {
                $error = 'Invalid security token.';
            }
        }
    }
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | NovaCloud</title>
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

        .security-question-display {
            background: var(--light);
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            padding: 20px;
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 16px;
            font-style: italic;
        }

        .email-preview {
            background: var(--light);
            border: 2px dashed var(--success);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            text-align: center;
            color: var(--dark);
            font-weight: 500;
        }

        .email-preview .email {
            color: var(--primary);
            font-weight: 600;
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

        /* Alternative Methods */
        .alternative-methods {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--light-gray);
            text-align: center;
        }

        .alternative-methods p {
            color: var(--gray);
            margin-bottom: 16px;
            font-size: 14px;
        }

        .btn-outline {
            padding: 12px 24px;
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        /* Help Section */
        .help-section {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: var(--gray);
            padding-top: 24px;
            border-top: 1px solid var(--light-gray);
        }

        .help-section a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .help-section a:hover {
            text-decoration: underline;
        }

        /* Instructions */
        .instructions {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.1));
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            border-left: 4px solid var(--primary);
        }

        .instructions p {
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .instructions ul {
            padding-left: 20px;
            color: var(--gray);
            font-size: 13px;
        }

        .instructions li {
            margin-bottom: 4px;
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
                
                <h1 class="welcome-title">Recover Your Account</h1>
                <p class="welcome-subtitle">Follow these steps to securely reset your password and regain access to your NovaCloud account.</p>
                
                <ul class="features">
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span>Secure account verification</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <span>Two-step verification process</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span>Expires in 60 minutes for security</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <span>Immediate password reset</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="auth-right">
            <div class="auth-form-container">
                <div class="form-header">
                    <h2 class="form-title">Reset Password</h2>
                    <p class="form-subtitle">Follow these steps to recover your account</p>
                </div>
                
                <!-- Progress Steps -->
                <div class="progress-steps">
                    <div class="progress-bar" style="width: <?php echo $step == 'email' ? '0%' : ($step == 'security' ? '50%' : '100%'); ?>"></div>
                    <div class="step <?php echo $step == 'email' ? 'active' : 'completed'; ?>">
                        <div class="step-icon">1</div>
                        <div class="step-label">Verify Account</div>
                    </div>
                    <div class="step <?php echo $step == 'security' ? 'active' : ($step == 'reset' ? 'completed' : ''); ?>">
                        <div class="step-icon">2</div>
                        <div class="step-label">Security Check</div>
                    </div>
                    <div class="step <?php echo $step == 'reset' ? 'active' : ''; ?>">
                        <div class="step-icon">3</div>
                        <div class="step-label">New Password</div>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($step == 'email'): ?>
                    <div class="instructions">
                        <p><i class="fas fa-info-circle"></i> Please enter your username or email address to begin the password reset process.</p>
                        <ul>
                            <li>We'll verify your account details</li>
                            <li>You'll need to answer your security question</li>
                            <li>Reset link expires in 60 minutes</li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="" id="forgotForm">
                        <div class="form-group">
                            <label for="identity">
                                <i class="fas fa-user"></i> Username or Email Address
                            </label>
                            <input 
                                type="text" 
                                id="identity" 
                                name="identity" 
                                required 
                                class="form-control" 
                                placeholder="Enter your username or email"
                                autocomplete="username"
                                value="<?php echo isset($_POST['identity']) ? htmlspecialchars($_POST['identity']) : ''; ?>"
                            >
                        </div>
                        
                        <button type="submit" class="btn-primary" id="submitBtn">
                            <i class="fas fa-arrow-right"></i> Continue to Security Check
                        </button>
                        
                        <a href="auth.php" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </a>
                    </form>
                    
                <?php elseif ($step == 'security'): 
                    $token = $_GET['token'] ?? '';
                    
                    if (empty($token)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Missing security token. Please start the process again.
                        </div>
                        <a href="forgot-password.php?step=email" class="btn-primary">
                            <i class="fas fa-redo"></i> Start Over
                        </a>
                    <?php else:
                        $db = Database::getInstance();
                        // Fetch token row and user info, then validate in PHP to avoid SQL NOW() timezone issues
                        $sql = "SELECT pr.expires_at, pr.used, u.security_question, u.email FROM password_resets pr 
                                JOIN users u ON pr.user_id = u.id 
                                WHERE pr.token = ? LIMIT 1";
                        $result = $db->query($sql, [$token]);

                        if ($result && $result->num_rows > 0):
                            $data = $result->fetch_assoc();
                            $security_question = $data['security_question'];
                            $email = $data['email'];
                            $expiresAt = $data['expires_at'];
                            $usedFlag = (int)$data['used'];

                            // Validate used flag and expiration in PHP
                            $nowTs = time();
                            $expiresTs = $expiresAt ? strtotime($expiresAt) : 0;
                            if ($usedFlag === 1) {
                                $error = 'This reset token has already been used. Please start again.';
                                error_log('[NovaCloud] Token used: ' . $token);
                            } elseif ($expiresTs > 0 && $expiresTs < $nowTs) {
                                $error = 'Reset token expired. Please start the process again.';
                                error_log('[NovaCloud] Token expired: ' . $token . ' expires_at=' . $expiresAt);
                            } else {
                                // valid token — render the security question form below
                            }
                    ?>
                            <div class="instructions">
                                <p><i class="fas fa-shield-alt"></i> Please answer your security question to verify your identity.</p>
                                <p>This adds an extra layer of security to protect your account.</p>
                            </div>
                            
                            <?php if ($email): ?>
                                <div class="email-preview">
                                    <i class="fas fa-envelope"></i> Verification email sent to: 
                                    <span class="email"><?php echo htmlspecialchars(maskEmail($email)); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="securityForm">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                
                                <div class="form-group">
                                    <label>Your Security Question</label>
                                    <div class="security-question-display">
                                        <i class="fas fa-question-circle"></i> 
                                        <?php echo htmlspecialchars($security_question); ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="security_answer">
                                        <i class="fas fa-key"></i> Your Answer
                                    </label>
                                    <input 
                                        type="text" 
                                        id="security_answer" 
                                        name="security_answer" 
                                        required 
                                        class="form-control" 
                                        placeholder="Enter your answer"
                                        autocomplete="off"
                                    >
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn-primary" id="verifyBtn">
                                        <i class="fas fa-check-circle"></i> Verify & Continue
                                    </button>
                                    <a href="forgot-password.php?step=email" class="btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Go Back
                                    </a>
                                </div>
                            </form>
                        <?php else:
                            // Attempt fallback: check session token if available (helps when redirect lost GET param)
                            $fallbackToken = $_SESSION['reset_token'] ?? '';
                            $found = false;
                            if ($fallbackToken) {
                                $chk = $db->query("SELECT u.security_question, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = FALSE LIMIT 1", [$fallbackToken]);
                                if ($chk && $chk->num_rows > 0) {
                                    $row = $chk->fetch_assoc();
                                    $security_question = $row['security_question'];
                                    $email = $row['email'];
                                    $token = $fallbackToken; // use session token
                                    $found = true;
                                } else {
                                    error_log('[NovaCloud] Fallback session token present but not found/valid: ' . $fallbackToken);
                                }
                            }

                            if ($found): ?>
                                <div class="instructions">
                                    <p><i class="fas fa-shield-alt"></i> Please answer your security question to verify your identity.</p>
                                    <p>This adds an extra layer of security to protect your account.</p>
                                </div>
                                <?php if ($email): ?>
                                    <div class="email-preview">
                                        <i class="fas fa-envelope"></i> Verification email sent to: 
                                        <span class="email"><?php echo htmlspecialchars(maskEmail($email)); ?></span>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" action="" id="securityForm">
                                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                    <div class="form-group">
                                        <label>Your Security Question</label>
                                        <div class="security-question-display">
                                            <i class="fas fa-question-circle"></i> 
                                            <?php echo htmlspecialchars($security_question); ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="security_answer">
                                            <i class="fas fa-key"></i> Your Answer
                                        </label>
                                        <input 
                                            type="text" 
                                            id="security_answer" 
                                            name="security_answer" 
                                            required 
                                            class="form-control" 
                                            placeholder="Enter your answer"
                                            autocomplete="off"
                                        >
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn-primary" id="verifyBtn">
                                            <i class="fas fa-check-circle"></i> Verify & Continue
                                        </button>
                                        <a href="forgot-password.php?step=email" class="btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Go Back
                                        </a>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Invalid or expired security token. Please start the process again.
                                </div>
                                <a href="forgot-password.php?step=email" class="btn-primary">
                                    <i class="fas fa-redo"></i> Start Over
                                </a>

                                <?php
                                // Local diagnostic output (only on localhost) to help debug token issues
                                $host = $_SERVER['HTTP_HOST'] ?? '';
                                if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
                                    $getToken = htmlspecialchars($_GET['token'] ?? '');
                                    $sessToken = htmlspecialchars($_SESSION['reset_token'] ?? '');
                                    echo "<div style='margin-top:16px;padding:12px;border:1px solid #eee;background:#fff;'>";
                                    echo "<strong>Debug (local only):</strong><br>GET token: <code>" . $getToken . "</code><br>Session token: <code>" . $sessToken . "</code><br>";
                                    // Check DB existence for GET token and session token
                                    if ($getToken) {
                                        $r1 = $db->query("SELECT id, expires_at, used FROM password_resets WHERE token = ? LIMIT 1", [$getToken]);
                                        if ($r1 && $r1->num_rows > 0) {
                                            $row1 = $r1->fetch_assoc();
                                            echo "GET token row: id=" . $row1['id'] . " expires_at=" . $row1['expires_at'] . " used=" . $row1['used'] . "<br>";
                                        } else {
                                            echo "GET token row: not found<br>";
                                        }
                                    }
                                    if ($sessToken && $sessToken !== $getToken) {
                                        $r2 = $db->query("SELECT id, expires_at, used FROM password_resets WHERE token = ? LIMIT 1", [$sessToken]);
                                        if ($r2 && $r2->num_rows > 0) {
                                            $row2 = $r2->fetch_assoc();
                                            echo "Session token row: id=" . $row2['id'] . " expires_at=" . $row2['expires_at'] . " used=" . $row2['used'] . "<br>";
                                        } else {
                                            echo "Session token row: not found<br>";
                                        }
                                    }
                                    echo "</div>";
                                }
                                ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Help Section -->
                <div class="help-section">
                    <p><i class="fas fa-life-ring"></i> Need assistance?</p>
                    <a href="contact.php">
                        <i class="fas fa-headset"></i> Contact Support Team
                    </a>
                </div>
                
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
            // Form validation and loading states
            const forms = ['forgotForm', 'securityForm'];
            
            forms.forEach(formId => {
                const form = document.getElementById(formId);
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const submitBtn = this.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                            submitBtn.disabled = true;
                        }
                    });
                }
            });
            
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
            
            // Auto-focus first input
            const firstInput = document.querySelector('input[type="text"], input[type="email"]');
            if (firstInput) {
                firstInput.focus();
            }
        });
        
        function maskEmail(email) {
            const parts = email.split('@');
            if (parts[0].length > 2) {
                const masked = parts[0].substring(0, 2) + '***' + parts[0].substring(parts[0].length - 1);
                return masked + '@' + parts[1];
            }
            return email;
        }
    </script>
</body>
</html>
<?php include 'includes/footer.php'; ?>
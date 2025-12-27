<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

$session = new SessionManager();
$functions = new NovaCloudFunctions();

if ($session->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = false;
$formData = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and collect form data
    $formData = [
        'username' => htmlspecialchars(trim($_POST['username'] ?? '')),
        'email' => htmlspecialchars(trim($_POST['email'] ?? '')),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'full_name' => htmlspecialchars(trim($_POST['full_name'] ?? '')),
        'security_question' => htmlspecialchars($_POST['security_question'] ?? ''),
        'security_answer' => htmlspecialchars(trim($_POST['security_answer'] ?? '')),
        'accept_terms' => isset($_POST['accept_terms']) ? true : false,
        'marketing_consent' => isset($_POST['marketing_consent']) ? true : false
    ];
    
    // Validation
    $errors = [];
    
    if (!$formData['accept_terms']) {
        $errors[] = 'You must accept the Terms and Conditions';
    }
    
    if (strlen($formData['password']) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/[A-Z]/', $formData['password'])) {
        $errors[] = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[a-z]/', $formData['password'])) {
        $errors[] = 'Password must contain at least one lowercase letter';
    } elseif (!preg_match('/[0-9]/', $formData['password'])) {
        $errors[] = 'Password must contain at least one number';
    }
    
    if ($formData['password'] !== $formData['confirm_password']) {
        $errors[] = 'Passwords do not match';
    }
    
    if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($errors)) {
        if ($functions->registerUser($formData)) {
            $success = true;
        } else {
            $errors[] = 'Registration failed. Username or email might already exist.';
        }
    }
    
    $error = implode('<br>', $errors);
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | NovaCloud</title>
    <?php
    if (file_exists(__DIR__ . '/favicon.png')) {
        $fav = (defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/favicon.png' : '/favicon.png');
        echo '<link rel="icon" type="image/png" href="' . htmlspecialchars($fav) . '">';
        echo '<link rel="apple-touch-icon" href="' . htmlspecialchars($fav) . '">';
    } elseif (file_exists(__DIR__ . '/favicon.ico')) {
        $fav = (defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/favicon.ico' : '/favicon.ico');
        echo '<link rel="icon" type="image/x-icon" href="' . htmlspecialchars($fav) . '">';
    } else {
        echo '<link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">';
    }
    ?>
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #38b000;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --danger: #f72585;
            --warning: #ff9e00;
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
            animation: float 20s infinite ease-in-out;
        }

        .circle-1 {
            width: 300px;
            height: 300px;
            top: 10%;
            right: 10%;
            animation-delay: 0s;
        }

        .circle-2 {
            width: 200px;
            height: 200px;
            bottom: 20%;
            left: 5%;
            animation-delay: 5s;
        }

        .circle-3 {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 20%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(120deg); }
            66% { transform: translateY(20px) rotate(240deg); }
        }

        .auth-wrapper {
            width: 100%;
            max-width: 1400px;
            display: flex;
            box-shadow: var(--card-shadow);
            border-radius: 24px;
            overflow: hidden;
            background: white;
            min-height: 800px;
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
            flex: 1.2;
            padding: 60px 40px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            overflow-y: auto;
        }

        .auth-form-container {
            width: 100%;
            max-width: 500px;
            margin-top: 20px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
            position: relative;
            display: inline-block;
        }

        .form-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .form-subtitle {
            color: var(--gray);
            font-size: 16px;
            margin-top: 20px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            color: white;
            border: none;
        }

        .alert-success {
            background: linear-gradient(135deg, #38b000, #2d9100);
            color: white;
            border: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .label-with-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-icon {
            color: var(--gray);
            cursor: help;
            font-size: 12px;
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

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 16px;
            padding-right: 50px;
        }

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
            padding: 5px;
        }

        .password-strength {
            height: 4px;
            background: var(--light-gray);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .strength-meter {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: var(--transition);
        }

        .strength-weak { background: #ff4757; width: 25%; }
        .strength-fair { background: #ffa502; width: 50%; }
        .strength-good { background: #2ed573; width: 75%; }
        .strength-strong { background: #38b000; width: 100%; }

        .strength-text {
            font-size: 12px;
            color: var(--gray);
            margin-top: 4px;
            text-align: right;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }

        .checkbox-group input[type="checkbox"] {
            margin-top: 3px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
            flex-shrink: 0;
        }

        .checkbox-group label {
            font-weight: normal;
            font-size: 14px;
            line-height: 1.5;
            color: var(--dark);
            cursor: pointer;
            margin: 0;
        }

        .checkbox-group a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .checkbox-group a:hover {
            text-decoration: underline;
        }

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
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .auth-links {
            text-align: center;
            margin: 30px 0;
            padding-top: 30px;
            border-top: 1px solid var(--light-gray);
        }

        .auth-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .auth-links a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .password-requirements {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
            font-size: 13px;
        }

        .password-requirements h4 {
            margin-bottom: 8px;
            color: var(--dark);
            font-size: 14px;
        }

        .password-requirements ul {
            list-style: none;
            padding-left: 0;
        }

        .password-requirements li {
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .requirement-met {
            color: var(--success);
        }

        .requirement-unmet {
            color: var(--gray);
        }

        .form-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: var(--gray);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
        }

        @media (max-width: 1200px) {
            .auth-wrapper {
                flex-direction: column;
                max-width: 800px;
                min-height: auto;
            }
            
            .auth-left {
                padding: 40px 30px;
            }
            
            .welcome-title {
                font-size: 32px;
            }
            
            .auth-right {
                padding: 40px 30px;
                align-items: center;
                max-height: none;
            }
            
            .auth-form-container {
                margin-top: 0;
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
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
            counter-reset: step;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--light-gray);
            z-index: 1;
        }

        .step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }

        .step-number {
            width: 32px;
            height: 32px;
            background: white;
            border: 2px solid var(--light-gray);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 600;
            color: var(--gray);
            transition: var(--transition);
        }

        .step.active .step-number {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .step-label {
            font-size: 12px;
            color: var(--gray);
        }

        .step.active .step-label {
            color: var(--primary);
            font-weight: 600;
        }
        
        /* Fix for large screens - ensure form is properly aligned */
        @media (min-width: 1201px) {
            .auth-right {
                align-items: flex-start;
                padding-top: 80px;
                padding-bottom: 80px;
            }
            
            .auth-form-container {
                max-height: none;
                overflow-y: visible;
            }
            
            .auth-wrapper {
                height: auto;
                min-height: 800px;
                max-height: 900px;
            }
        }
        
        /* Make sure form is scrollable on large screens if needed */
        .auth-right {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Ensure form container doesn't have fixed height */
        .auth-form-container {
            min-height: 0;
        }
        
        /* Improve form spacing */
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="floating-bg">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
        <div class="bg-circle circle-3"></div>
    </div>
    
    <div class="auth-wrapper">
        <div class="auth-left">
            <div class="auth-left-content">
                <div class="logo">
                    <div class="logo-icon">NC</div>
                    <div class="logo-text">NovaCloud</div>
                </div>
                
                <h1 class="welcome-title">Join NovaCloud Today</h1>
                <p class="welcome-subtitle">Create your account to unlock the full potential of cloud computing with enterprise-grade security and performance.</p>
                
                <ul class="features">
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <span>Get started in minutes</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span>Enterprise security & compliance</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <span>24/7 dedicated support</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span>Advanced analytics dashboard</span>
                    </li>
                </ul>
                
                <div style="margin-top: 40px; padding: 20px; background: rgba(255,255,255,0.1); border-radius: 12px;">
                    <h4 style="margin-bottom: 10px; font-size: 16px;">Already have an account?</h4>
                    <a href="auth.php" style="color: white; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-sign-in-alt"></i> Sign in to your account
                    </a>
                </div>
            </div>
        </div>
        
        <div class="auth-right">
            <div class="auth-form-container">
                <div class="form-header">
                    <h2 class="form-title" data-key="register_title">Create Account</h2>
                    <p class="form-subtitle">Fill in your details to get started</p>
                    
                    <div class="progress-steps">
                        <div class="step active">
                            <div class="step-number">1</div>
                            <div class="step-label">Account Info</div>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-label">Security</div>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-label">Complete</div>
                        </div>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <div>
                            <strong>Registration successful!</strong><br>
                            Welcome to NovaCloud. You can now <a href="auth.php" data-key="login_now" style="color: white; text-decoration: underline; font-weight: 600;">login to your account</a>
                        </div>
                    </div>
                <?php else: ?>
                
                <form method="POST" action="" id="registerForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="full_name" data-key="full_name">
                                <i class="fas fa-user"></i> Full Name *
                            </label>
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                required 
                                class="form-control" 
                                placeholder="John Doe"
                                value="<?php echo htmlspecialchars($formData['full_name'] ?? ''); ?>"
                                autocomplete="name"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="username" data-key="username">
                                <i class="fas fa-at"></i> Username *
                            </label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required 
                                class="form-control" 
                                placeholder="johndoe"
                                value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>"
                                autocomplete="username"
                            >
                            <small style="color: var(--gray); font-size: 12px; display: block; margin-top: 4px;">
                                Minimum 3 characters, letters, numbers and underscores only
                            </small>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="email" data-key="email">
                                <i class="fas fa-envelope"></i> Email Address *
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                class="form-control" 
                                placeholder="john@example.com"
                                value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                                autocomplete="email"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="password" data-key="password" class="label-with-info">
                                <i class="fas fa-lock"></i> Password *
                                <i class="fas fa-info-circle info-icon" title="Password requirements: 8+ chars, uppercase, lowercase, number"></i>
                            </label>
                            <div class="password-wrapper">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    class="form-control" 
                                    placeholder="••••••••"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="toggle-password" data-target="password">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-meter" id="passwordStrength"></div>
                            </div>
                            <div class="strength-text" id="strengthText">Password strength</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" data-key="confirm_password">
                                <i class="fas fa-lock"></i> Confirm Password *
                            </label>
                            <div class="password-wrapper">
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    required 
                                    class="form-control" 
                                    placeholder="••••••••"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="toggle-password" data-target="confirm_password">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <div class="password-requirements">
                                <h4><i class="fas fa-list-check"></i> Password Requirements</h4>
                                <ul>
                                    <li id="req-length" class="requirement-unmet">
                                        <i class="far fa-circle"></i> At least 8 characters
                                    </li>
                                    <li id="req-uppercase" class="requirement-unmet">
                                        <i class="far fa-circle"></i> One uppercase letter
                                    </li>
                                    <li id="req-lowercase" class="requirement-unmet">
                                        <i class="far fa-circle"></i> One lowercase letter
                                    </li>
                                    <li id="req-number" class="requirement-unmet">
                                        <i class="far fa-circle"></i> One number
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="security_question" data-key="security_question">
                                <i class="fas fa-question-circle"></i> Security Question *
                            </label>
                            <select name="security_question" id="security_question" class="form-control" required>
                                <option value="" data-key="select_question" <?php echo empty($formData['security_question']) ? 'selected' : ''; ?>>Select a security question</option>
                                <option value="What is your mother's maiden name?" <?php echo ($formData['security_question'] ?? '') === "What is your mother's maiden name?" ? 'selected' : ''; ?> data-key="question1">What is your mother's maiden name?</option>
                                <option value="What was your first pet's name?" <?php echo ($formData['security_question'] ?? '') === "What was your first pet's name?" ? 'selected' : ''; ?> data-key="question2">What was your first pet's name?</option>
                                <option value="What city were you born in?" <?php echo ($formData['security_question'] ?? '') === "What city were you born in?" ? 'selected' : ''; ?> data-key="question3">What city were you born in?</option>
                                <option value="What is your favorite teacher's name?" <?php echo ($formData['security_question'] ?? '') === "What is your favorite teacher's name?" ? 'selected' : ''; ?> data-key="question4">What is your favorite teacher's name?</option>
                                <option value="What was the make of your first car?" <?php echo ($formData['security_question'] ?? '') === "What was the make of your first car?" ? 'selected' : ''; ?> data-key="question5">What was the make of your first car?</option>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="security_answer" data-key="security_answer">
                                <i class="fas fa-key"></i> Security Answer *
                            </label>
                            <input 
                                type="text" 
                                id="security_answer" 
                                name="security_answer" 
                                required 
                                class="form-control" 
                                placeholder="Enter your answer"
                                value="<?php echo htmlspecialchars($formData['security_answer'] ?? ''); ?>"
                            >
                            <small style="color: var(--gray); font-size: 12px; display: block; margin-top: 4px;">
                                This will be used to verify your identity if you forget your password
                            </small>
                        </div>
                        
                        <div class="form-group full-width">
                            <div class="checkbox-group">
                                <input 
                                    type="checkbox" 
                                    id="accept_terms" 
                                    name="accept_terms" 
                                    required
                                    <?php echo !empty($formData['accept_terms']) ? 'checked' : ''; ?>
                                >
                                <label for="accept_terms">
                                    I agree to the <a href="terms.php" id="termsLink" target="_blank">Terms and Conditions</a> and <a href="privacy.php" id="privacyLink" target="_blank">Privacy Policy</a> *
                                </label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input 
                                    type="checkbox" 
                                    id="marketing_consent" 
                                    name="marketing_consent"
                                    <?php echo !empty($formData['marketing_consent']) ? 'checked' : ''; ?>
                                >
                                <label for="marketing_consent">
                                    I want to receive updates, newsletters, and marketing emails from NovaCloud
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary" data-key="register_button">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
                
                <div class="auth-links">
                    <a href="auth.php" data-key="already_account">
                        <i class="fas fa-sign-in-alt"></i> Already have an account? Sign in
                    </a>
                </div>
                
                <div class="form-footer">
                    By creating an account, you agree to our terms and acknowledge you have read our privacy policy.
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div id="termsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 16px; max-width: 600px; max-height: 80vh; overflow-y: auto; margin: 20px;">
            <h3 style="margin-bottom: 20px;">Terms and Conditions</h3>
            <div style="line-height: 1.6; margin-bottom: 20px;">
                <p><strong>Last Updated: <?php echo date('F j, Y'); ?></strong></p>
                <p>By creating an account with NovaCloud, you agree to the following terms and conditions:</p>
                
                <h4>1. Account Responsibility</h4>
                <p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>
                
                <h4>2. Acceptable Use</h4>
                <p>You agree not to use the service for any illegal activities or to violate any laws in your jurisdiction.</p>
                
                <h4>3. Service Availability</h4>
                <p>We strive to maintain 99.9% uptime but do not guarantee uninterrupted service.</p>
                
                <h4>4. Data Protection</h4>
                <p>We implement industry-standard security measures to protect your data, but cannot guarantee absolute security.</p>
                
                <h4>5. Termination</h4>
                <p>We reserve the right to suspend or terminate accounts that violate our terms.</p>
                
                <h4>6. Changes to Terms</h4>
                <p>We may update these terms periodically. Continued use of the service constitutes acceptance of updated terms.</p>
            </div>
            <button onclick="closeModal('termsModal')" style="background: var(--primary); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600;">I Understand</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure form is properly displayed on large screens
            function adjustFormForLargeScreens() {
                const authRight = document.querySelector('.auth-right');
                const authFormContainer = document.querySelector('.auth-form-container');
                const authWrapper = document.querySelector('.auth-wrapper');
                
                if (window.innerWidth > 1200) {
                    // For large screens, make sure the form container is properly positioned
                    authRight.style.alignItems = 'flex-start';
                    authRight.style.paddingTop = '80px';
                    authRight.style.paddingBottom = '80px';
                    authFormContainer.style.maxHeight = 'none';
                    authFormContainer.style.overflowY = 'visible';
                    authWrapper.style.maxHeight = '900px';
                    
                    // Ensure we can see all form fields
                    authRight.scrollTop = 0;
                }
            }
            
            // Run on load and on resize
            adjustFormForLargeScreens();
            window.addEventListener('resize', adjustFormForLargeScreens);
            
            // Scroll to top of form on large screens
            if (window.innerWidth > 1200) {
                setTimeout(() => {
                    document.querySelector('.auth-right').scrollTop = 0;
                }, 100);
            }
            
            // Password toggle functionality
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
            
            // Password strength checker
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            const requirements = {
                length: document.getElementById('req-length'),
                uppercase: document.getElementById('req-uppercase'),
                lowercase: document.getElementById('req-lowercase'),
                number: document.getElementById('req-number')
            };
            
            function checkPasswordStrength(password) {
                let strength = 0;
                
                // Check length
                if (password.length >= 8) {
                    strength += 25;
                    requirements.length.classList.remove('requirement-unmet');
                    requirements.length.classList.add('requirement-met');
                    requirements.length.innerHTML = '<i class="fas fa-check-circle"></i> At least 8 characters';
                } else {
                    requirements.length.classList.remove('requirement-met');
                    requirements.length.classList.add('requirement-unmet');
                    requirements.length.innerHTML = '<i class="far fa-circle"></i> At least 8 characters';
                }
                
                // Check uppercase
                if (/[A-Z]/.test(password)) {
                    strength += 25;
                    requirements.uppercase.classList.remove('requirement-unmet');
                    requirements.uppercase.classList.add('requirement-met');
                    requirements.uppercase.innerHTML = '<i class="fas fa-check-circle"></i> One uppercase letter';
                } else {
                    requirements.uppercase.classList.remove('requirement-met');
                    requirements.uppercase.classList.add('requirement-unmet');
                    requirements.uppercase.innerHTML = '<i class="far fa-circle"></i> One uppercase letter';
                }
                
                // Check lowercase
                if (/[a-z]/.test(password)) {
                    strength += 25;
                    requirements.lowercase.classList.remove('requirement-unmet');
                    requirements.lowercase.classList.add('requirement-met');
                    requirements.lowercase.innerHTML = '<i class="fas fa-check-circle"></i> One lowercase letter';
                } else {
                    requirements.lowercase.classList.remove('requirement-met');
                    requirements.lowercase.classList.add('requirement-unmet');
                    requirements.lowercase.innerHTML = '<i class="far fa-circle"></i> One lowercase letter';
                }
                
                // Check number
                if (/[0-9]/.test(password)) {
                    strength += 25;
                    requirements.number.classList.remove('requirement-unmet');
                    requirements.number.classList.add('requirement-met');
                    requirements.number.innerHTML = '<i class="fas fa-check-circle"></i> One number';
                } else {
                    requirements.number.classList.remove('requirement-met');
                    requirements.number.classList.add('requirement-unmet');
                    requirements.number.innerHTML = '<i class="far fa-circle"></i> One number';
                }
                
                // Update strength bar and text
                strengthBar.className = 'strength-meter';
                if (strength === 0) {
                    strengthText.textContent = 'Enter a password';
                } else if (strength <= 25) {
                    strengthBar.classList.add('strength-weak');
                    strengthText.textContent = 'Weak';
                    strengthText.style.color = '#ff4757';
                } else if (strength <= 50) {
                    strengthBar.classList.add('strength-fair');
                    strengthText.textContent = 'Fair';
                    strengthText.style.color = '#ffa502';
                } else if (strength <= 75) {
                    strengthBar.classList.add('strength-good');
                    strengthText.textContent = 'Good';
                    strengthText.style.color = '#2ed573';
                } else {
                    strengthBar.classList.add('strength-strong');
                    strengthText.textContent = 'Strong';
                    strengthText.style.color = '#38b000';
                }
            }
            
            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
            
            // Form validation
            const registerForm = document.getElementById('registerForm');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    const password = document.getElementById('password');
                    const confirmPassword = document.getElementById('confirm_password');
                    const acceptTerms = document.getElementById('accept_terms');
                    const username = document.getElementById('username');
                    
                    // Username validation
                    if (username.value.length < 3) {
                        e.preventDefault();
                        alert('Username must be at least 3 characters long.');
                        username.focus();
                        return false;
                    }
                    
                    if (!/^[a-zA-Z0-9_]+$/.test(username.value)) {
                        e.preventDefault();
                        alert('Username can only contain letters, numbers, and underscores.');
                        username.focus();
                        return false;
                    }
                    
                    // Check passwords match
                    if (password.value !== confirmPassword.value) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                        confirmPassword.focus();
                        return false;
                    }
                    
                    // Check terms accepted
                    if (!acceptTerms.checked) {
                        e.preventDefault();
                        alert('You must accept the Terms and Conditions to continue.');
                        return false;
                    }
                    
                    // Add loading state
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                        submitBtn.disabled = true;
                    }
                    
                    return true;
                });
            }
            
            // Terms & Privacy link behavior: follow real hrefs when present, otherwise show fallback modal/alert
            var termsLink = document.getElementById('termsLink');
            if (termsLink) {
                termsLink.addEventListener('click', function(e) {
                    var href = this.getAttribute('href');
                    if (!href || href === '#') {
                        e.preventDefault();
                        document.getElementById('termsModal').style.display = 'flex';
                    }
                    // if href points to a real file, allow default navigation (opens in new tab due to target="_blank")
                });
            }

            var privacyLink = document.getElementById('privacyLink');
            if (privacyLink) {
                privacyLink.addEventListener('click', function(e) {
                    var href = this.getAttribute('href');
                    if (!href || href === '#') {
                        e.preventDefault();
                        alert('Privacy Policy: We value your privacy and protect your data according to GDPR regulations.');
                    }
                    // otherwise follow the link normally
                });
            }
            
            // Confirm password real-time validation
            const confirmPasswordInput = document.getElementById('confirm_password');
            confirmPasswordInput.addEventListener('input', function() {
                const password = document.getElementById('password').value;
                if (this.value && password !== this.value) {
                    this.style.borderColor = 'var(--danger)';
                    this.style.boxShadow = '0 0 0 3px rgba(247, 37, 133, 0.1)';
                } else if (this.value) {
                    this.style.borderColor = 'var(--success)';
                    this.style.boxShadow = '0 0 0 3px rgba(56, 176, 0, 0.1)';
                } else {
                    this.style.borderColor = 'var(--light-gray)';
                    this.style.boxShadow = 'none';
                }
            });
            
            // Username validation
            const usernameInput = document.getElementById('username');
            usernameInput.addEventListener('input', function() {
                const username = this.value;
                if (username.length < 3) {
                    this.style.borderColor = 'var(--danger)';
                } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                    this.style.borderColor = 'var(--warning)';
                } else {
                    this.style.borderColor = 'var(--success)';
                }
            });
            
            // Full name validation
            const fullNameInput = document.getElementById('full_name');
            fullNameInput.addEventListener('input', function() {
                if (this.value.trim().length < 2) {
                    this.style.borderColor = 'var(--danger)';
                } else {
                    this.style.borderColor = 'var(--success)';
                }
            });
            
            // Initialize validation on page load
            if (fullNameInput.value) fullNameInput.dispatchEvent(new Event('input'));
            if (usernameInput.value) usernameInput.dispatchEvent(new Event('input'));
            if (passwordInput.value) passwordInput.dispatchEvent(new Event('input'));
        });
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('termsModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</body>
</html>
<?php include 'includes/footer.php'; ?>
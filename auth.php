<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

$session = new SessionManager();
$functions = new NovaCloudFunctions();

if ($session->isLoggedIn()) {
    // Redirect admins to admin dashboard
    if ($session->isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = $functions->authenticateUser($username, $password);
    
    if ($user) {
        $session->login($user['id'], $user['username'], $user['user_type']);
        // Redirect based on role
        if (($user['user_type'] ?? '') === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | NovaCloud</title>
    <?php
    // Explicit absolute favicon URL for local dev environment
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
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --danger: #f72585;
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

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            text-align: center;
            font-weight: 500;
            animation: shake 0.5s ease;
            border: none;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

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
            margin-top: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .auth-links {
            display: flex;
            justify-content: space-between;
            margin: 24px 0 40px;
            padding-top: 24px;
            border-top: 1px solid var(--light-gray);
        }

        .auth-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
        }

        .auth-links a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .social-divider {
            text-align: center;
            position: relative;
            margin: 32px 0;
            color: var(--gray);
            font-size: 14px;
        }

        .social-divider::before,
        .social-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: var(--light-gray);
        }

        .social-divider::before {
            left: 0;
        }

        .social-divider::after {
            right: 0;
        }

        .social-login {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-social {
            padding: 16px;
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            background: white;
            color: var(--dark);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 15px;
        }

        .btn-social:hover {
            border-color: var(--primary);
            background: var(--light);
        }

        .btn-social.google::before {
            content: 'G';
            background: #DB4437;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .btn-social.facebook::before {
            content: 'f';
            background: #4267B2;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-family: Arial, sans-serif;
        }

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
            
            .auth-links {
                flex-direction: column;
                gap: 12px;
                text-align: center;
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
                
                <h1 class="welcome-title">Welcome Back</h1>
                <p class="welcome-subtitle">Sign in to access your NovaCloud dashboard and manage your cloud resources efficiently.</p>
                
                <ul class="features">
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span>Enterprise-grade security</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <span>Lightning-fast performance</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <span>Real-time synchronization</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-cloud"></i>
                        </div>
                        <span>Seamless cloud integration</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="auth-right">
            <div class="auth-form-container">
                <div class="form-header">
                    <h2 class="form-title" data-key="login_title">Login</h2>
                    <p class="form-subtitle">Enter your credentials to continue</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <div class="form-group">
                        <label for="username" data-key="username_email">
                            <i class="fas fa-user"></i> Username or Email
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            class="form-control" 
                            placeholder="Enter your username or email"
                            autocomplete="username"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password" data-key="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                class="form-control" 
                                placeholder="Enter your password"
                                autocomplete="current-password"
                            >
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary" data-key="login_button">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
                
                <div class="auth-links">
                    <a href="register.php" data-key="register_link">
                        <i class="fas fa-user-plus"></i> Create Account
                    </a>
                    <a href="forgot-password.php" data-key="forgot_password">
                        <i class="fas fa-key"></i> Forgot Password?
                    </a>
                </div>
                
                <div class="social-divider">Or continue with</div>
                
                <div class="social-login">
                    <button type="button" class="btn-social google" data-key="continue_google">
                        Continue with Google
                    </button>
                    <button type="button" class="btn-social facebook" data-key="continue_facebook">
                        Continue with Facebook
                    </button>
                </div>
                
                <div class="footer-links">
                    <a href="privacy.php">Privacy Policy</a> • 
                    <a href="terms.php">Terms of Service</a> • 
                    <a href="help.php">Support</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
                });
            }
            
            // Form validation
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    const username = document.getElementById('username');
                    const password = document.getElementById('password');
                    
                    if (username && password) {
                        if (!username.value.trim()) {
                            e.preventDefault();
                            username.focus();
                            return false;
                        }
                        
                        if (!password.value.trim()) {
                            e.preventDefault();
                            password.focus();
                            return false;
                        }
                    }
                    
                    // Add loading state
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
                        submitBtn.disabled = true;
                    }
                    
                    return true;
                });
            }
            
            // Social login buttons
            const socialButtons = document.querySelectorAll('.btn-social');
            socialButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.classList.contains('google') ? 'Google' : 'Facebook';
                    alert(`${type} login integration would be implemented here.`);
                });
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
        });
    </script>
</body>
</html>
<?php include 'includes/footer.php'; ?>
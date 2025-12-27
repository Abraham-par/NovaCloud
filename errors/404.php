<?php
require_once __DIR__ . '/../includes/config.php';
http_response_code(404);
$pageTitle = "404 - Page Not Found | NovaCloud";
include __DIR__ . '/../includes/header.php';

$requestedUrl = $_SERVER['REQUEST_URI'] ?? '';
$referrer = $_SERVER['HTTP_REFERER'] ?? '';

// Smart suggestions
function getSmartSuggestions($url) {
    $suggestions = [];
    $urlLower = strtolower($url);
    
    // Map common typos to correct pages
    $commonTypos = [
        'dashbord' => 'dashboard.php',
        'authnticate' => 'auth.php',
        'log-in' => 'auth.php',
        'signin' => 'auth.php',
        'regster' => 'register.php',
        'frgot' => 'forgot-password.php',
        'resetpass' => 'reset-password.php',
        'profle' => 'profile.php',
        'setings' => 'settings.php'
    ];
    
    foreach ($commonTypos as $typo => $correct) {
        if (strpos($urlLower, $typo) !== false) {
            $suggestions[] = [
                'icon' => 'fa-spell-check',
                'title' => 'Possible Typo Detected',
                'message' => "Did you mean <strong>" . str_replace('.php', '', $correct) . "</strong>?",
                'link' => '../' . $correct,
                'linkText' => 'Go to ' . str_replace('.php', '', $correct)
            ];
            break;
        }
    }
    
    // Check for missing extension
    if (!preg_match('/\.php$/', $urlLower) && !preg_match('/\.(css|js|jpg|png|gif|ico)$/', $urlLower)) {
        $baseUrl = preg_replace('/\?.*$/', '', $urlLower);
        if ($baseUrl && !in_array($baseUrl, ['/', '/index'])) {
            $suggestions[] = [
                'icon' => 'fa-file-code',
                'title' => 'Missing File Extension',
                'message' => "This site uses .php extensions. Try adding .php to the URL.",
                'link' => '../' . trim($baseUrl, '/') . '.php',
                'linkText' => 'Try with .php'
            ];
        }
    }
    
    // Default suggestions
    if (empty($suggestions)) {
        $suggestions[] = [
            'icon' => 'fa-home',
            'title' => 'Return to Homepage',
            'message' => 'Start fresh from the main page',
            'link' => SITE_URL . 'index.php',
            'linkText' => 'Go Home'
        ];
        
        $suggestions[] = [
            'icon' => 'fa-sign-in-alt',
            'title' => 'Login Page',
            'message' => 'Access your NovaCloud account',
            'link' => SITE_URL . 'auth.php',
            'linkText' => 'Go to Login'
        ];
        
        $suggestions[] = [
            'icon' => 'fa-search',
            'title' => 'Search NovaCloud',
            'message' => 'Find what you\'re looking for',
            'link' => SITE_URL . 'admin/api/search.php',
            'linkText' => 'Search Site'
        ];
    }
    
    return $suggestions;
}

$suggestions = getSmartSuggestions($requestedUrl);

// Popular pages
 $popularPages = [
    ['title' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => SITE_URL . 'dashboard.php'],
    ['title' => 'Login', 'icon' => 'fa-sign-in-alt', 'url' => SITE_URL . 'auth.php'],
    ['title' => 'Register', 'icon' => 'fa-user-plus', 'url' => SITE_URL . 'register.php'],
    ['title' => 'Forgot Password', 'icon' => 'fa-key', 'url' => SITE_URL . 'forgot-password.php'],
    ['title' => 'Profile', 'icon' => 'fa-user', 'url' => SITE_URL . 'profile.php'],
    ['title' => 'Settings', 'icon' => 'fa-cog', 'url' => SITE_URL . 'settings.php'],
    ['title' => 'Help Center', 'icon' => 'fa-question-circle', 'url' => SITE_URL . 'help.php'],
    ['title' => 'Contact', 'icon' => 'fa-headset', 'url' => SITE_URL . 'contact.php']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --gradient-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: var(--gradient-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
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
            top: -150px;
            right: -150px;
            animation-delay: 0s;
        }

        .circle-2 {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
            animation-delay: -5s;
        }

        .circle-3 {
            width: 150px;
            height: 150px;
            top: 20%;
            left: 10%;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        .error-wrapper {
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

        .error-left {
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

        .error-left::before {
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

        .error-left-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
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

        .error-illustration {
            width: 250px;
            height: 250px;
            margin: 0 auto 40px;
            position: relative;
            animation: bounce 3s infinite ease-in-out;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .error-illustration svg {
            width: 100%;
            height: 100%;
        }

        .error-features {
            list-style: none;
            margin-top: 40px;
            text-align: left;
            padding: 0 20px;
        }

        .error-features li {
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

        .error-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-content {
            width: 100%;
            max-width: 500px;
        }

        .error-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .error-code {
            font-size: 120px;
            font-weight: 900;
            background: linear-gradient(135deg, var(--danger), var(--warning));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1;
            margin-bottom: 10px;
        }

        .error-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .error-subtitle {
            color: var(--gray);
            font-size: 16px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 500;
            animation: fadeIn 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(247, 37, 133, 0.1), rgba(247, 37, 133, 0.05));
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .requested-url {
            background: var(--light);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 30px;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 14px;
            color: var(--dark);
            word-break: break-all;
            border: 2px dashed var(--light-gray);
        }

        .requested-url span {
            color: var(--primary);
            font-weight: 600;
        }

        .suggestions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .suggestion-card {
            background: var(--light);
            border-radius: 16px;
            padding: 24px;
            transition: var(--transition);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .suggestion-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .suggestion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .suggestion-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-bottom: 16px;
        }

        .suggestion-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .suggestion-message {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .suggestion-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
        }

        .suggestion-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .popular-pages {
            margin-top: 40px;
        }

        .popular-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            text-align: center;
        }

        .pages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
        }

        .page-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: var(--light);
            border-radius: 12px;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .page-link:hover {
            background: white;
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.15);
        }

        .page-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .page-title {
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 16px;
            margin-top: 32px;
        }

        .btn-primary {
            flex: 1;
            padding: 18px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
        }

        .btn-secondary {
            flex: 1;
            padding: 18px;
            background: var(--light-gray);
            color: var(--dark);
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-secondary:hover {
            background: #e2e6ea;
            border-color: #dae0e5;
        }

        .search-box {
            margin: 32px 0;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 18px 20px 18px 50px;
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            font-size: 16px;
            transition: var(--transition);
            background: white;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 18px;
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
            .error-wrapper {
                flex-direction: column;
                max-width: 600px;
                min-height: auto;
            }
            
            .error-left {
                padding: 40px 30px;
            }
            
            .error-right {
                padding: 40px 30px;
            }
            
            .error-code {
                font-size: 80px;
            }
            
            .error-title {
                font-size: 24px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .error-wrapper {
                border-radius: 16px;
            }
            
            .error-left,
            .error-right {
                padding: 30px 20px;
            }
            
            .error-illustration {
                width: 180px;
                height: 180px;
            }
            
            .suggestions-grid {
                grid-template-columns: 1fr;
            }
            
            .pages-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Pulse animation for error code */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .error-code {
            animation: pulse 2s infinite ease-in-out;
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
    
    <div class="error-wrapper">
        <div class="error-left">
            <div class="error-left-content">
                <div class="logo">
                    <div class="logo-icon">NC</div>
                    <div class="logo-text">NovaCloud</div>
                </div>
                
                <div class="error-illustration">
                    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="100" cy="100" r="80" fill="white" stroke="#4361ee" stroke-width="4"/>
                        <circle cx="70" cy="70" r="10" fill="#4361ee"/>
                        <circle cx="130" cy="70" r="10" fill="#4361ee"/>
                        <path d="M70 130 Q100 150 130 130" stroke="#4361ee" stroke-width="4" fill="none" stroke-linecap="round"/>
                        <line x1="40" y1="40" x2="60" y2="60" stroke="#f72585" stroke-width="8" stroke-linecap="round"/>
                        <line x1="140" y1="40" x2="160" y2="60" stroke="#f72585" stroke-width="8" stroke-linecap="round"/>
                        <line x1="40" y1="160" x2="60" y2="140" stroke="#f72585" stroke-width="8" stroke-linecap="round"/>
                        <line x1="140" y1="160" x2="160" y2="140" stroke="#f72585" stroke-width="8" stroke-linecap="round"/>
                    </svg>
                </div>
                
                <h1 style="font-size: 42px; font-weight: 800; margin-bottom: 20px; opacity: 0.9;">Oops!</h1>
                <p style="font-size: 18px; opacity: 0.8; line-height: 1.6; max-width: 400px; margin: 0 auto;">
                    Don't worry, we've got your back. Here are some helpful features to get you back on track.
                </p>
                
                <ul class="error-features">
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <span>Smart page suggestions</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <span>Quick navigation shortcuts</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span>Secure error reporting</span>
                    </li>
                    <li>
                        <div class="feature-icon">
                            <i class="fas fa-compass"></i>
                        </div>
                        <span>Popular page directory</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="error-right">
            <div class="error-content">
                <div class="error-header">
                    <div class="error-code">404</div>
                    <h2 class="error-title" data-key="page_not_found">Page Not Found</h2>
                    <p class="error-subtitle">The requested page could not be located</p>
                </div>
                
                <?php if ($requestedUrl): ?>
                    <div class="requested-url">
                        <i class="fas fa-link" style="color: var(--primary); margin-right: 8px;"></i>
                        Requested URL: <span><?php echo htmlspecialchars($requestedUrl); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>We couldn't find the page you're looking for. Try one of the options below.</span>
                </div>
                
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="siteSearch" placeholder="Search NovaCloud...">
                </div>
                
                <div class="suggestions-grid">
                    <?php foreach ($suggestions as $suggestion): ?>
                        <div class="suggestion-card">
                            <div class="suggestion-icon">
                                <i class="fas <?php echo $suggestion['icon']; ?>"></i>
                            </div>
                            <h4 class="suggestion-title"><?php echo htmlspecialchars($suggestion['title']); ?></h4>
                            <p class="suggestion-message"><?php echo $suggestion['message']; ?></p>
                            <a href="<?php echo htmlspecialchars($suggestion['link']); ?>" class="suggestion-link">
                                <?php echo htmlspecialchars($suggestion['linkText']); ?>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="popular-pages">
                    <h4 class="popular-title">Popular Pages</h4>
                    <div class="pages-grid">
                        <?php foreach ($popularPages as $page): ?>
                            <a href="<?php echo htmlspecialchars($page['url']); ?>" class="page-link">
                                <div class="page-icon">
                                    <i class="fas <?php echo $page['icon']; ?>"></i>
                                </div>
                                <div class="page-title"><?php echo htmlspecialchars($page['title']); ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="../index.php" class="btn-primary">
                        <i class="fas fa-home"></i> Go to Homepage
                    </a>
                    <a href="javascript:history.back()" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Go Back
                    </a>
                </div>
                
                <div class="footer-links">
                    <a href="../privacy.php">Privacy Policy</a> • 
                    <a href="../terms.php">Terms of Service</a> • 
                    <a href="../help.php">Help Center</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('siteSearch');
            
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const query = this.value.trim();
                    if (query) {
                        // In a real app, this would search your site
                        window.location.href = '../search.php?q=' + encodeURIComponent(query);
                    }
                }
            });
            
            // Animate suggestion cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 100);
                    }
                });
            }, observerOptions);
            
            // Observe all suggestion cards
            document.querySelectorAll('.suggestion-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
            
            // Observe page links
            document.querySelectorAll('.page-link').forEach(link => {
                link.style.opacity = '0';
                link.style.transform = 'scale(0.9)';
                link.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                observer.observe(link);
            });
            
            // Button hover effects
            document.querySelectorAll('.btn-primary, .btn-secondary').forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                button.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = '';
                    }
                });
            });
            
            // Page link hover effects
            document.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.05)';
                });
                
                link.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Auto-focus search on desktop
            if (window.innerWidth > 768) {
                setTimeout(() => {
                    searchInput.focus();
                }, 800);
            }
            
            // Add ripple effect to buttons
            document.querySelectorAll('.btn-primary, .btn-secondary, .page-link').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.3);
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                    `;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                .btn-primary, .btn-secondary, .page-link {
                    position: relative;
                    overflow: hidden;
                }
            `;
            document.head.appendChild(style);
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Alt + H for Home
                if (e.altKey && e.key === 'h') {
                    e.preventDefault();
                    window.location.href = '../index.php';
                }
                // Alt + B for Back
                else if (e.altKey && e.key === 'b') {
                    e.preventDefault();
                    window.history.back();
                }
                // / for search focus
                else if (e.key === '/' && !e.ctrlKey && !e.altKey && !e.metaKey) {
                    e.preventDefault();
                    searchInput.focus();
                }
                // Escape to blur
                else if (e.key === 'Escape' && document.activeElement === searchInput) {
                    searchInput.blur();
                }
            });
            
            // Show keyboard shortcut hint
            searchInput.placeholder = 'Search NovaCloud... (Press / to focus)';
            
            // Remove hint after first focus
            searchInput.addEventListener('focus', function() {
                this.placeholder = 'Search NovaCloud...';
            });
        });
    </script>
</body>
</html>
<?php include __DIR__ . '/../includes/footer.php'; ?>
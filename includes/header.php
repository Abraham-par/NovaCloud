<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security headers
header('Content-Type: text/html; charset=utf-8');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Load helper functions
if (file_exists(__DIR__ . '/helpers.php')) {
    require_once __DIR__ . '/helpers.php';
}

// Set default language
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}

// Get current language
$currentLang = $_SESSION['language'];
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>" dir="<?php echo in_array($currentLang, ['ar', 'he']) ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5"> <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <meta name="server-language" content="<?php echo htmlspecialchars($_SESSION['language'] ?? DEFAULT_LANGUAGE); ?>">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - NovaCloud' : 'NovaCloud'; ?></title>
    
        <?php
        // Compute an absolute favicon URL based on current host and script path.
        // This ensures the browser requests the favicon from the correct project path.
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        // For scripts under subfolders, ensure basePath points to project folder, not includes/
        if (basename(__DIR__) === 'includes') {
            // dirname of includes is project root
            $projectPath = rtrim(dirname(__DIR__), '/\\');
            // convert filesystem path to URL path by removing document root
            $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
            $fsPath = str_replace('\\', '/', $projectPath);
            $urlPath = $docRoot && strpos($fsPath, $docRoot) === 0 ? substr($fsPath, strlen($docRoot)) : $basePath;
            $urlPath = '/' . ltrim($urlPath, '/');
        } else {
            $urlPath = $basePath ?: '';
        }

        $absolutePng = $scheme . '://' . $host . rtrim($urlPath, '/') . '/favicon.png';
        $absoluteIco = $scheme . '://' . $host . rtrim($urlPath, '/') . '/favicon.ico';

        // Output absolute links; browser will fetch these URLs directly.
        // Prefer PNG if the file exists on disk.
        if (file_exists(__DIR__ . '/../favicon.png')) {
            echo '<link rel="icon" type="image/png" href="' . htmlspecialchars($absolutePng) . '">';
            echo '<link rel="apple-touch-icon" href="' . htmlspecialchars($absolutePng) . '">';
        } elseif (file_exists(__DIR__ . '/../favicon.ico')) {
            echo '<link rel="icon" type="image/x-icon" href="' . htmlspecialchars($absoluteIco) . '">';
        } else {
            echo '<link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">';
        }
        ?>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#4f46e5',
                    }
                }
            }
        }
    </script>
    
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        
        /* Smooth Scrolling */
        html { scroll-behavior: smooth; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Loader Styles */
        .loader-wrapper {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: #ffffff;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
        }
        .loader-hidden {
            opacity: 0;
            visibility: hidden;
        }
    </style>
</head>
<body class="text-gray-800 antialiased">

    <div id="globalLoader" class="loader-wrapper">
        <div class="text-center">
            <i class="fas fa-cloud text-5xl text-indigo-600 animate-bounce mb-4"></i>
            <h2 class="text-xl font-bold text-gray-800 tracking-wider">NovaCloud</h2>
        </div>
    </div>

    <div class="fixed top-5 right-5 z-50 flex flex-col gap-3 w-full max-w-sm pointer-events-none">
        
        <?php if (isset($_SESSION['warning'])): ?>
            <div class="pointer-events-auto bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded shadow-lg flex items-start animate__animated animate__fadeInRight alert-dismissible">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700"><?php echo $_SESSION['warning']; unset($_SESSION['warning']); ?></p>
                </div>
                <button onclick="this.parentElement.remove()" class="ml-auto text-yellow-400 hover:text-yellow-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="pointer-events-auto bg-green-50 border-l-4 border-green-400 p-4 rounded shadow-lg flex items-start animate__animated animate__fadeInRight alert-dismissible">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                </div>
                <button onclick="this.parentElement.remove()" class="ml-auto text-green-400 hover:text-green-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="pointer-events-auto bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-lg flex items-start animate__animated animate__fadeInRight alert-dismissible">
                <div class="flex-shrink-0">
                    <i class="fas fa-times-circle text-red-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                </div>
                <button onclick="this.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Remove Loader on Page Load
        window.addEventListener('load', function() {
            const loader = document.getElementById('globalLoader');
            setTimeout(() => {
                loader.classList.add('loader-hidden');
            }, 500); // Small delay for smoothness
        });

        // Auto-dismiss PHP Alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert-dismissible');
            if(alerts.length > 0) {
                setTimeout(() => {
                    alerts.forEach(el => {
                        el.classList.remove('animate__fadeInRight');
                        el.classList.add('animate__fadeOutRight');
                        setTimeout(() => el.remove(), 500);
                    });
                }, 5000);
            }
        });
    </script>
<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

$session = new SessionManager();
$functions = new NovaCloudFunctions();

$session->requireLogin();
$userId = $session->getUserId();
$username = $session->getUsername();
$isAdmin = $session->isAdmin();

// Load current user
$db = Database::getInstance();
$result = $db->query('SELECT * FROM users WHERE id = ?', [$userId]);
$user = $result ? $result->fetch_assoc() : [];

// Get user stats
$stats = $functions->getUserStats($userId);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        $data = [
            'language' => $_POST['language'] ?? ($user['language'] ?? 'en'),
            'theme' => $_POST['theme'] ?? ($user['theme'] ?? 'light'),
            'notification_preferences' => $_POST['notification_preferences'] ?? ($user['notification_preferences'] ?? '')
        ];

        if ($functions->updateProfile($userId, $data)) {
            $success = 'Settings updated successfully.';
            $_SESSION['language'] = $data['language'];
        } else {
            $error = 'Failed to update settings.';
        }
    }

    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            if ($functions->changePassword($userId, $current, $new)) {
                $success = 'Password changed successfully.';
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }

    // Reload user
    $result = $db->query('SELECT * FROM users WHERE id = ?', [$userId]);
    $user = $result ? $result->fetch_assoc() : $user;
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - NovaCloud</title>
    
    <!-- Tailwind CSS with Custom Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        },
                        gradient: {
                            start: '#667eea',
                            end: '#764ba2',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'slide-in': 'slide-in 0.3s ease-out',
                        'fade-in': 'fade-in 0.5s ease-out',
                    },
                    keyframes: {
                        'slide-in': {
                            '0%': { transform: 'translateX(-20px)', opacity: 0 },
                            '100%': { transform: 'translateX(0)', opacity: 1 },
                        },
                        'fade-in': {
                            '0%': { opacity: 0 },
                            '100%': { opacity: 1 },
                        },
                    }
                }
            }
        }
    </script>
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.7);
            --shadow-glow: 0 20px 40px rgba(102, 126, 234, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f6f8ff 0%, #f0f4ff 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        /* Glass Morphism */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-glow);
        }

        .gradient-bg {
            background: var(--primary-gradient);
        }

        .gradient-text {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        /* Hover Effects */
        .hover-lift {
            transition: all 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.15);
        }

        /* Background Patterns */
        .bg-grid-pattern {
            background-image: 
                linear-gradient(to right, rgba(102, 126, 234, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(102, 126, 234, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        /* Form Elements */
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        </style>
</head>
<body class="bg-gray-50 overflow-hidden">
    
    <!-- Main Layout -->
    <div class="flex h-screen">
        
        <!-- Sidebar - Glass Design (Same as Dashboard) -->
        <aside class="w-20 lg:w-64 bg-white/90 backdrop-blur-xl border-r border-gray-100/50 flex flex-col transition-all duration-300 z-20 hover:w-64 group">
            
            <!-- Logo -->
            <div class="p-6 border-b border-gray-100/50 flex items-center justify-center lg:justify-start">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl gradient-bg flex items-center justify-center shadow-lg">
                        <i class="fas fa-cloud text-white text-xl"></i>
                    </div>
                    <div class="hidden lg:block group-hover:block">
                        <h1 class="text-xl font-bold text-gray-800">NovaCloud</h1>
                        <p class="text-xs text-gray-500">Premium Cloud</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-6 px-3 lg:px-6">
                <div class="space-y-2">
                    <?php $current = basename($_SERVER['SCRIPT_NAME']); ?>
                    <a href="dashboard.php" class="flex items-center gap-4 p-3 rounded-xl <?php echo $current==='dashboard.php' ? 'bg-gradient-to-r from-blue-50 to-purple-50 text-primary-600 border border-blue-100 shadow-sm' : 'text-gray-600 hover:bg-gray-50/50 hover:text-gray-900'; ?> transition-all">
                        <i class="fas fa-home text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Dashboard</span>
                    </a>

                    <a href="profile.php" class="flex items-center gap-4 p-3 rounded-xl <?php echo $current==='profile.php' ? 'bg-gradient-to-r from-blue-50 to-purple-50 text-primary-600 border border-blue-100 shadow-sm' : 'text-gray-600 hover:bg-gray-50/50 hover:text-gray-900'; ?> transition-all">
                        <i class="fas fa-user-circle text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Profile</span>
                    </a>

                    <a href="#" id="uploadTriggerSidebar" class="flex items-center gap-4 p-3 rounded-xl text-gray-600 hover:bg-gray-50/50 hover:text-gray-900 transition-all">
                        <i class="fas fa-cloud-upload-alt text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Upload</span>
                    </a>

                    <a href="about.php" class="flex items-center gap-4 p-3 rounded-xl <?php echo $current==='about.php' ? 'bg-gradient-to-r from-blue-50 to-purple-50 text-primary-600 border border-blue-100 shadow-sm' : 'text-gray-600 hover:bg-gray-50/50 hover:text-gray-900'; ?> transition-all">
                        <i class="fas fa-info-circle text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">About</span>
                    </a>

                    <a href="settings.php" class="flex items-center gap-4 p-3 rounded-xl <?php echo $current==='settings.php' ? 'bg-gradient-to-r from-blue-50 to-purple-50 text-primary-600 border border-blue-100 shadow-sm' : 'text-gray-600 hover:bg-gray-50/50 hover:text-gray-900'; ?> transition-all">
                        <i class="fas fa-cog text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Settings</span>
                    </a>

                    <a href="notifications.php" class="flex items-center gap-4 p-3 rounded-xl <?php echo $current==='notifications.php' ? 'bg-gradient-to-r from-blue-50 to-purple-50 text-primary-600 border border-blue-100 shadow-sm relative' : 'text-gray-600 hover:bg-gray-50/50 hover:text-gray-900 relative'; ?> transition-all">
                        <i class="fas fa-bell text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Notifications</span>
                        <span class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 rounded-full text-xs text-white flex items-center justify-center animate-pulse">3</span>
                    </a>

                    <?php if ($isAdmin): ?>
                    <a href="admin/dashboard.php" class="flex items-center gap-4 p-3 rounded-xl text-gray-600 hover:bg-gray-50/50 hover:text-gray-900 transition-all">
                        <i class="fas fa-crown text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Admin</span>
                    </a>
                    <?php endif; ?>

                    <div class="pt-4 border-t border-gray-100/50 mt-4">
                        <a href="logout.php" class="flex items-center gap-4 p-3 rounded-xl text-red-500 hover:bg-red-50/50 transition-all">
                            <i class="fas fa-sign-out-alt text-lg w-6"></i>
                            <span class="hidden lg:block group-hover:block font-medium">Logout</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- User Profile -->
            <div class="p-4 border-t border-gray-100/50 bg-white/50">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full gradient-bg flex items-center justify-center text-white font-bold text-sm">
                            <?php echo strtoupper(substr($username, 0, 1)); ?>
                        </div>
                        <?php if ($isAdmin): ?>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full border-2 border-white flex items-center justify-center">
                            <i class="fas fa-crown text-[8px] text-white"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="hidden lg:block group-hover:block overflow-hidden">
                        <p class="text-sm font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($username); ?></p>
                        <p class="text-xs text-gray-500"><?php echo $isAdmin ? 'Administrator' : 'Premium User'; ?></p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Header - Glass Effect -->
            <header class="h-20 bg-white/80 backdrop-blur-xl border-b border-gray-100/50 flex items-center justify-between px-6 lg:px-10 z-10">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <span>Settings</span>
                        <span class="gradient-text">Management</span>
                        <span class="text-2xl">⚙️</span>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Customize your NovaCloud experience</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Settings Icon -->
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <i class="fas fa-cog text-primary-600"></i>
                    </div>

                    <!-- Notifications -->
                    <button class="relative w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
                        <i class="fas fa-bell text-gray-600"></i>
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">2</span>
                    </button>
                </div>
            </header>

            <!-- Content Area with Pattern Background -->
            <div class="flex-1 overflow-y-auto bg-grid-pattern p-6 lg:p-10">
                
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Account Status -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Account Status</p>
                                <h3 class="text-xl font-bold text-gray-800">Active</h3>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl">
                                <i class="fas fa-shield-check text-xl text-green-500"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <i class="fas fa-calendar-check"></i>
                            <span>Member since <?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?></span>
                        </div>
                    </div>

                    <!-- Storage Usage -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Storage Used</p>
                                <h3 class="text-xl font-bold text-gray-800"><?php echo formatBytes($stats['total_size'] ?? 0); ?></h3>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl">
                                <i class="fas fa-database text-xl text-blue-500"></i>
                            </div>
                        </div>
                        <div class="relative pt-2">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full gradient-bg rounded-full" style="width: <?php echo min(100, (($stats['total_size'] ?? 0) / (100 * 1024 * 1024 * 1024)) * 100); ?>%"></div>
                            </div>
                            <span class="text-xs text-gray-500 absolute right-0 -top-5"><?php echo number_format(min(100, (($stats['total_size'] ?? 0) / (100 * 1024 * 1024 * 1024)) * 100), 1); ?>%</span>
                        </div>
                    </div>

                    <!-- Security Score -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Security Score</p>
                                <h3 class="text-xl font-bold text-gray-800">Strong</h3>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl">
                                <i class="fas fa-lock text-xl text-amber-500"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-green-600">
                            <i class="fas fa-check-circle"></i>
                            <span>2FA Not Enabled</span>
                        </div>
                    </div>
                </div>

                <?php if ($success): ?>
                <div class="mb-6">
                    <div class="glass-card rounded-2xl border-l-4 border-green-500 bg-green-50/50">
                        <div class="p-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-green-800"><?php echo $success; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="mb-6">
                    <div class="glass-card rounded-2xl border-l-4 border-red-500 bg-red-50/50">
                        <div class="p-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                <i class="fas fa-exclamation-circle text-red-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-800"><?php echo $error; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Settings Sections Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Preferences Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-50 to-purple-50 flex items-center justify-center">
                                <i class="fas fa-sliders-h text-primary-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">Preferences</h3>
                                <p class="text-sm text-gray-500">Customize your experience</p>
                            </div>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="save_settings" value="1">
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                                    <select name="language" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all form-select">
                                        <option value="en" <?php echo ($user['language'] ?? 'en') == 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="am" <?php echo ($user['language'] ?? 'en') == 'am' ? 'selected' : ''; ?>>አማርኛ</option>
                                        <option value="om" <?php echo ($user['language'] ?? 'en') == 'om' ? 'selected' : ''; ?>>Afaan Oromoo</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
                                    <select name="theme" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all form-select">
                                        <option value="light" <?php echo ($user['theme'] ?? 'light') == 'light' ? 'selected' : ''; ?>>Light Theme</option>
                                        <option value="auto" <?php echo ($user['theme'] ?? 'light') == 'auto' ? 'selected' : ''; ?>>Auto (System)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Notification Preferences</label>
                                    <div class="space-y-3">
                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50/50 transition-all cursor-pointer">
                                            <input type="checkbox" name="notification_preferences[]" value="email" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500" checked>
                                            <div>
                                                <p class="text-sm font-medium text-gray-800">Email Notifications</p>
                                                <p class="text-xs text-gray-500">Receive important updates via email</p>
                                            </div>
                                        </label>
                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50/50 transition-all cursor-pointer">
                                            <input type="checkbox" name="notification_preferences[]" value="push" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500" checked>
                                            <div>
                                                <p class="text-sm font-medium text-gray-800">Push Notifications</p>
                                                <p class="text-xs text-gray-500">Real-time updates in your browser</p>
                                            </div>
                                        </label>
                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50/50 transition-all cursor-pointer">
                                            <input type="checkbox" name="notification_preferences[]" value="security" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500" checked>
                                            <div>
                                                <p class="text-sm font-medium text-gray-800">Security Alerts</p>
                                                <p class="text-xs text-gray-500">Important security notifications</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="w-full py-3 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md flex items-center justify-center gap-2">
                                    <i class="fas fa-save"></i>
                                    Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center">
                                <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">Security</h3>
                                <p class="text-sm text-gray-500">Protect your account</p>
                            </div>
                        </div>

                        <!-- Two-Factor Authentication -->
                        <div class="mb-6 p-4 rounded-xl border border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Two-Factor Authentication</p>
                                    <p class="text-xs text-gray-500">Add an extra layer of security</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-600">Disabled</span>
                            </div>
                            <button type="button" class="w-full py-2 px-4 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                Enable 2FA
                            </button>
                        </div>

                        <!-- Change Password Form -->
                        <form method="POST">
                            <input type="hidden" name="change_password" value="1">
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                    <div class="relative">
                                        <input type="password" name="current_password" required 
                                               class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all">
                                        <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                    <div class="relative">
                                        <input type="password" name="new_password" required 
                                               class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all">
                                        <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                    <div class="relative">
                                        <input type="password" name="confirm_password" required 
                                               class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all">
                                        <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Password must be at least 8 characters long</span>
                                </div>

                                <button type="submit" class="w-full py-3 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md flex items-center justify-center gap-2">
                                    <i class="fas fa-key"></i>
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Storage Management Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-cyan-50 flex items-center justify-center">
                                <i class="fas fa-hdd text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">Storage Management</h3>
                                <p class="text-sm text-gray-500">Manage your cloud storage</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="p-4 rounded-xl bg-gray-50">
                                <p class="text-sm text-gray-700 mb-2">Current Usage</p>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-gray-800"><?php echo formatBytes($stats['total_size'] ?? 0); ?></span>
                                    <span class="text-sm text-gray-500"><?php echo number_format(min(100, (($stats['total_size'] ?? 0) / (100 * 1024 * 1024 * 1024)) * 100), 1); ?>% used</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full gradient-bg rounded-full" style="width: <?php echo min(100, (($stats['total_size'] ?? 0) / (100 * 1024 * 1024 * 1024)) * 100); ?>%"></div>
                                </div>
                            </div>

                            <p class="text-sm text-gray-600">Free up space by permanently deleting all your uploaded files.</p>
                            
                            <div class="mt-4">
                                <button id="clearStorageBtn" class="w-full py-3 px-4 bg-amber-50 text-amber-700 rounded-xl text-sm font-medium hover:bg-amber-100 transition-colors border border-amber-200 flex items-center justify-center gap-2">
                                    <i class="fas fa-trash-alt"></i>
                                    Clear My Storage
                                </button>
                                <span id="clearStorageStatus" class="block text-center text-xs text-gray-500 mt-2"></span>
                            </div>

                            <div class="flex items-center gap-2 text-xs text-gray-500 mt-4">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>This action cannot be undone. All files will be permanently deleted.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Account Settings Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-50 to-pink-50 flex items-center justify-center">
                                <i class="fas fa-user-cog text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">Account Settings</h3>
                                <p class="text-sm text-gray-500">Manage your account</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Data Export -->
                            <div class="p-4 rounded-xl border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Export Your Data</p>
                                        <p class="text-xs text-gray-500">Download all your files and information</p>
                                    </div>
                                    <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                                        Export
                                    </button>
                                </div>
                            </div>

                            <!-- Account Deactivation -->
                            <div class="p-4 rounded-xl border border-gray-200">
                                <div class="mb-3">
                                    <p class="text-sm font-medium text-gray-800">Deactivate Account</p>
                                    <p class="text-xs text-gray-500">Temporarily disable your account</p>
                                </div>
                                <button id="deactivateBtn" class="w-full py-2 px-4 bg-red-50 text-red-600 rounded-xl text-sm font-medium hover:bg-red-100 transition-colors border border-red-200 flex items-center justify-center gap-2">
                                    <i class="fas fa-user-slash"></i>
                                    Deactivate Account
                                </button>
                            </div>

                            <!-- Legal Links -->
                            <div class="mt-6">
                                <p class="text-sm font-medium text-gray-700 mb-3">Legal Documents</p>
                                <div class="space-y-2">
                                    <a href="privacy.php" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                                <i class="fas fa-shield-alt text-blue-500 text-sm"></i>
                                            </div>
                                            <span class="text-sm text-gray-700">Privacy Policy</span>
                                        </div>
                                        <i class="fas fa-chevron-right text-gray-400"></i>
                                    </a>
                                    <a href="terms.php" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                                                <i class="fas fa-file-contract text-green-500 text-sm"></i>
                                            </div>
                                            <span class="text-sm text-gray-700">Terms of Service</span>
                                        </div>
                                        <i class="fas fa-chevron-right text-gray-400"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Success Toast -->
    <div id="toast" class="fixed bottom-4 right-4 z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-4 max-w-sm">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800" id="toastMessage">Action completed successfully!</p>
                    <p class="text-xs text-gray-500 mt-1">Just now</p>
                </div>
                <button onclick="hideToast()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Show Password Toggle
        document.querySelectorAll('input[type="password"] + button').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                } else {
                    input.type = 'password';
                    icon.className = 'fas fa-eye';
                }
            });
        });

        // Clear Storage
        document.addEventListener('DOMContentLoaded', function() {
            const clearBtn = document.getElementById('clearStorageBtn');
            const clearStatus = document.getElementById('clearStorageStatus');

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    if (!confirm('⚠️ This will permanently delete ALL your uploaded files. This action cannot be undone. Are you absolutely sure?')) return;
                    
                    clearBtn.disabled = true;
                    clearBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';
                    
                    fetch('api/clear-storage.php', { method: 'POST' })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                const freed = res.freed_bytes || 0;
                                showToast('Cleared storage. Freed ' + formatBytes(freed), 'success');
                                clearStatus.textContent = 'Freed ' + formatBytes(freed);
                                clearBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Clear My Storage';
                                clearBtn.disabled = false;
                                // Reload page after 2 seconds
                                setTimeout(() => window.location.reload(), 2000);
                            } else {
                                showToast(res.error || 'Failed to clear storage', 'error');
                                clearBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Clear My Storage';
                                clearBtn.disabled = false;
                            }
                        }).catch(err => {
                            console.error(err);
                            showToast('Network error while clearing storage', 'error');
                            clearBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Clear My Storage';
                            clearBtn.disabled = false;
                        });
                });
            }

            // Deactivate Account
            const deactivateBtn = document.getElementById('deactivateBtn');
            if (deactivateBtn) {
                deactivateBtn.addEventListener('click', function() {
                    const modal = document.createElement('div');
                    modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4';
                    modal.innerHTML = `
                        <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm" onclick="this.parentElement.remove()"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                            <div class="text-center mb-6">
                                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-red-50 mb-4">
                                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Deactivate Account</h3>
                                <p class="text-sm text-gray-500">We're sorry to see you go. Please tell us why you are leaving (optional):</p>
                            </div>
                            
                            <textarea id="deact_reason" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 mb-4 focus:ring-2 focus:ring-red-500 outline-none transition-all" 
                                      placeholder="Reason (optional)" rows="3"></textarea>
                            
                            <div class="space-y-3 mb-6">
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50/50 transition-all cursor-pointer">
                                    <input type="checkbox" id="confirm_understand" class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500">
                                    <span class="text-sm text-gray-700">I understand this will disable my account</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50/50 transition-all cursor-pointer">
                                    <input type="checkbox" id="confirm_delete" class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500">
                                    <span class="text-sm text-gray-700">I understand this action cannot be undone</span>
                                </label>
                            </div>
                            
                            <div class="flex gap-3">
                                <button id="deact_cancel" class="flex-1 py-3 px-4 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                    Cancel
                                </button>
                                <button id="deact_confirm" class="flex-1 py-3 px-4 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md">
                                    Deactivate Account
                                </button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);

                    document.getElementById('deact_cancel').addEventListener('click', function() {
                        modal.remove();
                    });

                    document.getElementById('deact_confirm').addEventListener('click', function() {
                        const confirm1 = document.getElementById('confirm_understand').checked;
                        const confirm2 = document.getElementById('confirm_delete').checked;
                        const reason = document.getElementById('deact_reason').value.trim();

                        if (!confirm1 || !confirm2) {
                            showToast('Please confirm both statements before deactivating', 'error');
                            return;
                        }

                        const btn = this;
                        const originalHTML = btn.innerHTML;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                        btn.disabled = true;

                        fetch('api/deactivate-account.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ confirm_understand: true, confirm_delete: true, reason: reason })
                        }).then(r => r.json()).then(res => {
                            if (res.success) {
                                showToast('Account deactivated. Redirecting...', 'success');
                                setTimeout(() => { window.location.href = 'index.php'; }, 1500);
                            } else {
                                showToast(res.error || 'Failed to deactivate account', 'error');
                                btn.innerHTML = originalHTML;
                                btn.disabled = false;
                            }
                        }).catch(err => {
                            console.error(err);
                            showToast('Network error while deactivating', 'error');
                            btn.innerHTML = originalHTML;
                            btn.disabled = false;
                        });
                    });
                });
            }
        });

        // Format bytes function
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Toast Notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const toastElement = toast ? toast : (() => {
                // Create toast if it doesn't exist
                const newToast = document.createElement('div');
                newToast.id = 'toast';
                newToast.className = 'fixed bottom-4 right-4 z-50 hidden';
                newToast.innerHTML = `
                    <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-4 max-w-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800"></p>
                                <p class="text-xs text-gray-500 mt-1">Just now</p>
                            </div>
                            <button class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(newToast);
                return newToast;
            })();

            toastMessage.textContent = message;
            const icon = toastElement.querySelector('.w-10');
            const iconElement = toastElement.querySelector('.w-10 i') || document.createElement('i');
            
            if (!toastElement.querySelector('.w-10 i')) {
                icon.appendChild(iconElement);
            }

            if (type === 'error') {
                icon.className = 'w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center';
                iconElement.className = 'fas fa-exclamation-circle text-red-600';
            } else if (type === 'warning') {
                icon.className = 'w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center';
                iconElement.className = 'fas fa-exclamation-triangle text-amber-600';
            } else {
                icon.className = 'w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center';
                iconElement.className = 'fas fa-check text-green-600';
            }

            toastElement.classList.remove('hidden');

            // Close button
            const closeBtn = toastElement.querySelector('button');
            closeBtn.onclick = () => toastElement.classList.add('hidden');

            // Auto hide after 5 seconds
            setTimeout(() => {
                toastElement.classList.add('hidden');
            }, 5000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.classList.add('hidden');
            }
        }

        // Initialize tooltips
        document.querySelectorAll('[title]').forEach(el => {
            el.addEventListener('mouseenter', function(e) {
                const title = this.getAttribute('title');
                if (title) {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'absolute z-50 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-sm -translate-x-1/2 -translate-y-full';
                    tooltip.textContent = title;
                    tooltip.style.left = '50%';
                    tooltip.style.top = '-8px';
                    this.appendChild(tooltip);
                    this.setAttribute('title', '');
                }
            });
            
            el.addEventListener('mouseleave', function(e) {
                const tooltip = this.querySelector('div');
                if (tooltip) {
                    this.removeChild(tooltip);
                    this.setAttribute('title', tooltip.textContent);
                }
            });
        });
    </script>

<?php include 'includes/footer.php'; ?>
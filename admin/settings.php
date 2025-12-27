<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

$session = new SessionManager();
$session->requireAdmin();
$functions = new NovaCloudFunctions();

// Get system information
$systemInfo = $functions->getSystemInfo();
$backupHistory = $functions->getBackupHistory();
$settings = $functions->getSystemSettings();

// Calculate system stats
$diskFree = disk_free_space(__DIR__ . '/../');
$diskTotal = disk_total_space(__DIR__ . '/../');
$diskUsage = $diskTotal > 0 ? (($diskTotal - $diskFree) / $diskTotal) * 100 : 0;
$memoryUsage = memory_get_usage(true);
$memoryPeak = memory_get_peak_usage(true);

// Default settings if not in database
$defaultSettings = [
    'site_name' => 'NovaCloud',
    'site_url' => 'http://localhost',
    'site_email' => 'admin@novacloud.com',
    'storage_limit' => 100, // GB
    'file_size_limit' => 2000, // MB
    'registration_enabled' => true,
    'maintenance_mode' => false,
    'two_factor_auth' => false,
    'auto_backup' => true,
    'backup_interval' => 'daily',
    'logo_url' => '/assets/images/logo.png',
    'favicon_url' => '/favicon.ico',
    'theme_color' => '#f59e0b',
    'analytics_code' => '',
    'smtp_host' => '',
    'smtp_port' => 587,
    'smtp_secure' => 'tls',
    'smtp_username' => '',
    'smtp_password' => '',
    'recaptcha_site_key' => '',
    'recaptcha_secret_key' => ''
];

// Merge with actual settings
$settings = array_merge($defaultSettings, $settings);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - NovaCloud Admin</title>
    
    <!-- Tailwind CSS with Custom Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fff8f0',
                            100: '#ffedd5',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        },
                        fire: {
                            start: '#f59e0b',
                            middle: '#ea580c',
                            end: '#dc2626',
                        },
                        gold: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                        },
                        gradient: {
                            fire: 'linear-gradient(135deg, #f59e0b 0%, #ea580c 50%, #dc2626 100%)',
                            golden: 'linear-gradient(135deg, #fef3c7 0%, #fde68a 25%, #fbbf24 50%, #d97706 100%)',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'slide-in': 'slide-in 0.3s ease-out',
                        'fade-in': 'fade-in 0.5s ease-out',
                        'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                        'fire-flicker': 'fire-flicker 1.5s ease-in-out infinite',
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
                        'pulse-glow': {
                            '0%, 100%': { 
                                boxShadow: '0 0 20px rgba(245, 158, 11, 0.3)',
                                transform: 'scale(1)'
                            },
                            '50%': { 
                                boxShadow: '0 0 40px rgba(245, 158, 11, 0.6)',
                                transform: 'scale(1.02)'
                            },
                        },
                        'fire-flicker': {
                            '0%, 100%': { opacity: 1 },
                            '50%': { opacity: 0.8 },
                        }
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
            --primary-gradient: linear-gradient(135deg, #f59e0b 0%, #ea580c 50%, #dc2626 100%);
            --gold-gradient: linear-gradient(135deg, #fef3c7 0%, #fde68a 25%, #fbbf24 50%, #d97706 100%);
            --glass-bg: rgba(255, 248, 240, 0.7);
            --shadow-glow: 0 20px 40px rgba(245, 158, 11, 0.15);
            --fire-gradient: linear-gradient(135deg, #f59e0b 0%, #ea580c 50%, #dc2626 100%);
        }

        body {
            background: linear-gradient(135deg, #fef3c7 0%, #ffedd5 50%, #fef3c7 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: #1f2937;
        }

        /* Premium Glass Morphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(245, 158, 11, 0.2);
            box-shadow: var(--shadow-glow);
        }

        .gradient-bg {
            background: var(--primary-gradient);
        }

        .gradient-text {
            background: var(--fire-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .gold-text {
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(245, 158, 11, 0.1);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--fire-gradient);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #dc2626 0%, #ea580c 50%, #f59e0b 100%);
        }

        /* Hover Effects */
        .hover-lift {
            transition: all 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(245, 158, 11, 0.15);
        }

        .hover-glow:hover {
            animation: pulse-glow 1s ease-in-out;
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #fde68a;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        input:checked + .toggle-slider {
            background: var(--primary-gradient);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(30px);
        }

        /* Tab Navigation */
        .tab-button {
            padding: 0.75rem 1.5rem;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
            color: #78350f;
            font-weight: 500;
        }

        .tab-button.active {
            border-bottom-color: #f59e0b;
            color: #92400e;
            font-weight: 600;
        }

        .tab-button:hover:not(.active) {
            color: #b45309;
        }

        /* Background Patterns */
        .bg-grid-pattern {
            background-image: 
                linear-gradient(to right, rgba(245, 158, 11, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(245, 158, 11, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Fire Flicker Effect */
        .fire-flicker {
            animation: fire-flicker 1.5s ease-in-out infinite;
        }

        /* Fire Glow Effect */
        .fire-glow {
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.4);
        }

        .fire-glow:hover {
            box-shadow: 0 0 50px rgba(245, 158, 11, 0.6), 0 0 100px rgba(245, 158, 11, 0.2);
        }
    </style>
</head>
<body class="bg-amber-50 overflow-hidden">
    
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-yellow-200/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-orange-200/20 rounded-full blur-3xl"></div>
        <div class="absolute top-3/4 left-1/3 w-64 h-64 bg-red-200/20 rounded-full blur-3xl"></div>
    </div>
    
    <!-- Main Layout -->
    <div class="flex h-screen relative z-10">
        
        <!-- Sidebar - Premium Golden Design -->
        <aside class="w-20 lg:w-64 bg-white/90 backdrop-blur-xl border-r border-amber-200 flex flex-col transition-all duration-300 z-20 hover:w-64 group">
            
            <!-- Logo -->
            <div class="p-6 border-b border-amber-200 flex items-center justify-center lg:justify-start">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl gradient-bg flex items-center justify-center shadow-lg fire-glow hover:animate-pulse-glow">
                        <i class="fas fa-fire text-white text-xl"></i>
                    </div>
                    <div class="hidden lg:block group-hover:block">
                        <h1 class="text-xl font-bold text-gray-800">NovaCloud</h1>
                        <p class="text-xs text-amber-600">Premium Admin</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-6 px-3 lg:px-6">
                <div class="space-y-2">
                    <a href="dashboard.php" class="flex items-center gap-4 p-3 rounded-xl text-gray-600 hover:bg-amber-50 hover:text-amber-700 transition-all">
                        <i class="fas fa-chart-line text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Dashboard</span>
                    </a>
                    
                    <a href="manage-users.php" class="flex items-center gap-4 p-3 rounded-xl text-gray-600 hover:bg-amber-50 hover:text-amber-700 transition-all">
                        <i class="fas fa-users text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Users</span>
                    </a>
                    
                    <a href="analytics.php" class="flex items-center gap-4 p-3 rounded-xl text-gray-600 hover:bg-amber-50 hover:text-amber-700 transition-all">
                        <i class="fas fa-chart-bar text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Analytics</span>
                    </a>
                    
                    <a href="settings.php" class="flex items-center gap-4 p-3 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 text-amber-700 border border-amber-200 shadow-sm">
                        <i class="fas fa-cog text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Settings</span>
                    </a>
                    
                    <hr class="border-amber-200 my-4">
                    
                    <div class="pt-2">
                        <a href="../dashboard.php" class="flex items-center gap-4 p-3 rounded-xl text-blue-600 hover:bg-blue-50 transition-all mb-2">
                            <i class="fas fa-home text-lg w-6"></i>
                            <span class="hidden lg:block group-hover:block font-medium">User Panel</span>
                        </a>
                        <a href="../logout.php" class="flex items-center gap-4 p-3 rounded-xl text-red-600 hover:bg-red-50 transition-all">
                            <i class="fas fa-sign-out-alt text-lg w-6"></i>
                            <span class="hidden lg:block group-hover:block font-medium">Logout</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Admin Profile -->
            <div class="p-4 border-t border-amber-200 bg-white/50">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full gradient-bg flex items-center justify-center text-white font-bold text-lg shadow-lg fire-glow">
                            A
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 gradient-bg rounded-full border-2 border-white flex items-center justify-center">
                            <i class="fas fa-crown text-[8px] text-white"></i>
                        </div>
                    </div>
                    <div class="hidden lg:block group-hover:block overflow-hidden">
                        <p class="text-sm font-semibold text-gray-800 truncate">Administrator</p>
                        <p class="text-xs text-amber-600">Premium Admin</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Header - Premium Design -->
            <header class="h-20 bg-white/80 backdrop-blur-xl border-b border-amber-200 flex items-center justify-between px-6 lg:px-10 z-10">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <span class="flex items-center gap-2">
                            <span>Fire</span>
                            <i class="fas fa-fire text-amber-500 fire-flicker"></i>
                            <span>Settings</span>
                        </span>
                        <span class="gradient-text ml-2">Configuration</span>
                    </h1>
                    <p class="text-sm text-amber-600 mt-1">Configure system preferences with fiery precision</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Save All Button -->
                    <button onclick="saveAllSettings()" class="px-4 py-3 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md fire-glow hover:animate-pulse-glow flex items-center gap-2">
                        <i class="fas fa-fire"></i>
                        Save All Changes
                    </button>

                    <!-- Backup Now Button -->
                    <button onclick="createBackup()" class="px-4 py-3 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md fire-glow hover:animate-pulse-glow flex items-center gap-2">
                        <i class="fas fa-fire"></i>
                        Backup Now
                    </button>
                </div>
            </header>

            <!-- Content Area with Fire Pattern -->
            <div class="flex-1 overflow-y-auto bg-grid-pattern p-6 lg:p-10">
                <!-- Tab Navigation -->
                <div class="flex space-x-1 border-b border-amber-200 mb-6 overflow-x-auto" id="tabNavigation">
                    <button class="tab-button active" data-tab="general">
                        <i class="fas fa-fire mr-2"></i> General
                    </button>
                    <button class="tab-button" data-tab="security">
                        <i class="fas fa-fire mr-2"></i> Security
                    </button>
                    <button class="tab-button" data-tab="storage">
                        <i class="fas fa-fire mr-2"></i> Storage
                    </button>
                    <button class="tab-button" data-tab="email">
                        <i class="fas fa-fire mr-2"></i> Email
                    </button>
                    <button class="tab-button" data-tab="backup">
                        <i class="fas fa-fire mr-2"></i> Backup
                    </button>
                    <button class="tab-button" data-tab="system">
                        <i class="fas fa-fire mr-2"></i> System
                    </button>
                    <button class="tab-button" data-tab="about">
                        <i class="fas fa-fire mr-2"></i> About
                    </button>
                </div>

                <!-- Tab Contents -->
                <div id="tabContents">
                    <!-- General Settings Tab -->
                    <div id="generalTab" class="space-y-6">
                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">General Settings</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Site Name</label>
                                    <input type="text" id="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Site URL</label>
                                    <input type="url" id="site_url" value="<?php echo htmlspecialchars($settings['site_url']); ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Admin Email</label>
                                    <input type="email" id="site_email" value="<?php echo htmlspecialchars($settings['site_email']); ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Theme Color</label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" id="theme_color" value="<?php echo htmlspecialchars($settings['theme_color']); ?>" 
                                               class="w-12 h-12 rounded-lg cursor-pointer border border-amber-300">
                                        <input type="text" id="theme_color_text" value="<?php echo htmlspecialchars($settings['theme_color']); ?>" 
                                               class="flex-1 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800 font-mono">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Registration & Access</h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 bg-amber-50/50 rounded-xl border border-amber-200">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Enable User Registration</label>
                                        <p class="text-xs text-amber-600">Allow new users to create accounts</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="registration_enabled" <?php echo $settings['registration_enabled'] ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-amber-50/50 rounded-xl border border-amber-200">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Maintenance Mode</label>
                                        <p class="text-xs text-amber-600">Disable public access to the site</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings Tab -->
                    <div id="securityTab" class="space-y-6 hidden">
                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Security Settings</h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 bg-amber-50/50 rounded-xl border border-amber-200">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Two-Factor Authentication</label>
                                        <p class="text-xs text-amber-600">Require 2FA for admin access</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="two_factor_auth" <?php echo $settings['two_factor_auth'] ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-amber-50/50 rounded-xl border border-amber-200">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Password Policy</label>
                                        <p class="text-xs text-amber-600">Require strong passwords</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="strong_passwords" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">reCAPTCHA</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Site Key</label>
                                    <input type="text" id="recaptcha_site_key" value="<?php echo htmlspecialchars($settings['recaptcha_site_key']); ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Secret Key</label>
                                    <input type="password" id="recaptcha_secret_key" value="<?php echo htmlspecialchars($settings['recaptcha_secret_key']); ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                </div>
                            </div>
                            <p class="text-xs text-amber-600 mt-2">
                                <i class="fas fa-fire mr-1"></i>Get your keys from <a href="https://www.google.com/recaptcha" target="_blank" class="text-amber-700 hover:underline font-medium">Google reCAPTCHA</a>
                            </p>
                        </div>

                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Change Admin Password</h2>
                            <form id="changePasswordForm" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Current Password</label>
                                    <input type="password" name="current_password" required 
                                           class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-amber-600 mb-2">New Password</label>
                                        <input type="password" name="new_password" required 
                                               class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-amber-600 mb-2">Confirm Password</label>
                                        <input type="password" name="confirm_password" required 
                                               class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-3 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md fire-glow hover:animate-pulse-glow flex items-center gap-2">
                                        <i class="fas fa-fire"></i>
                                        Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Storage Settings Tab -->
                    <div id="storageTab" class="space-y-6 hidden">
                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Storage Limits</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">User Storage Limit (GB)</label>
                                    <div class="relative">
                                        <input type="number" id="storage_limit" value="<?php echo $settings['storage_limit']; ?>" min="1" max="1000" step="1"
                                               class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                        <span class="absolute right-4 top-3.5 text-amber-600">GB</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Max File Size (MB)</label>
                                    <div class="relative">
                                        <input type="number" id="file_size_limit" value="<?php echo $settings['file_size_limit']; ?>" min="1" max="5000" step="1"
                                               class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                        <span class="absolute right-4 top-3.5 text-amber-600">MB</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Disk Usage</h2>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm font-medium text-amber-600">Disk Usage</span>
                                        <span class="text-sm text-amber-600"><?php echo round($diskUsage, 1); ?>% used</span>
                                    </div>
                                    <div class="h-2 bg-amber-100 rounded-full overflow-hidden">
                                        <div class="h-full gradient-bg rounded-full" style="width: <?php echo min(100, $diskUsage); ?>%"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-amber-600 mt-1">
                                        <span>Free: <?php echo formatBytes($diskFree); ?></span>
                                        <span>Total: <?php echo formatBytes($diskTotal); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Settings Tab -->
                    <div id="emailTab" class="space-y-6 hidden">
                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">SMTP Configuration</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">SMTP Host</label>
                                    <input type="text" id="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host']); ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">SMTP Port</label>
                                    <input type="number" id="smtp_port" value="<?php echo $settings['smtp_port']; ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Security</label>
                                    <select id="smtp_secure" class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                        <option value="tls" <?php echo $settings['smtp_secure'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo $settings['smtp_secure'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="" <?php echo empty($settings['smtp_secure']) ? 'selected' : ''; ?>>None</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Username</label>
                                    <input type="text" id="smtp_username" value="<?php echo htmlspecialchars($settings['smtp_username']); ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Password</label>
                                    <input type="password" id="smtp_password" value="<?php echo htmlspecialchars($settings['smtp_password']); ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                </div>
                            </div>
                            <div class="mt-4">
                                <button onclick="testEmailConfig()" class="px-4 py-2 bg-amber-50 text-amber-700 rounded-lg text-sm font-medium hover:bg-amber-100 transition-colors border border-amber-200">
                                    <i class="fas fa-fire mr-1"></i> Test Email Configuration
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Backup Settings Tab -->
                    <div id="backupTab" class="space-y-6 hidden">
                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Backup Settings</h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 bg-amber-50/50 rounded-xl border border-amber-200">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Automatic Backups</label>
                                        <p class="text-xs text-amber-600">Automatically backup system data</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="auto_backup" <?php echo $settings['auto_backup'] ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-amber-600 mb-2">Backup Interval</label>
                                    <select id="backup_interval" class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800">
                                        <option value="hourly" <?php echo $settings['backup_interval'] === 'hourly' ? 'selected' : ''; ?>>Hourly</option>
                                        <option value="daily" <?php echo $settings['backup_interval'] === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                        <option value="weekly" <?php echo $settings['backup_interval'] === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                        <option value="monthly" <?php echo $settings['backup_interval'] === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Backup History</h2>
                            <div class="space-y-3">
                                <?php if (!empty($backupHistory)): ?>
                                    <?php foreach ($backupHistory as $backup): ?>
                                    <div class="flex items-center justify-between p-3 bg-amber-50/50 rounded-xl hover:bg-amber-50 transition-colors border border-amber-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg gradient-bg flex items-center justify-center fire-glow">
                                                <i class="fas fa-fire text-white"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($backup['name']); ?></p>
                                                <p class="text-xs text-amber-600"><?php echo $backup['date']; ?> • <?php echo formatBytes($backup['size']); ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button onclick="downloadBackup('<?php echo $backup['id']; ?>')" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors border border-emerald-300 hover:animate-pulse-glow" title="Download">
                                                <i class="fas fa-fire text-sm"></i>
                                            </button>
                                            <button onclick="restoreBackup('<?php echo $backup['id']; ?>')" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-100 transition-colors border border-amber-300" title="Restore">
                                                <i class="fas fa-redo text-sm"></i>
                                            </button>
                                            <button onclick="deleteBackup('<?php echo $backup['id']; ?>')" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors border border-red-300" title="Delete">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-8">
                                        <i class="fas fa-fire text-3xl text-amber-300 mb-2"></i>
                                        <p class="text-amber-600">No backup history available</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- System Info Tab -->
                    <div id="systemTab" class="space-y-6 hidden">
                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">System Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                                        <label class="text-sm font-medium text-amber-600">PHP Version</label>
                                        <p class="text-lg font-mono text-gray-800 mt-1"><?php echo phpversion(); ?></p>
                                    </div>
                                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                                        <label class="text-sm font-medium text-amber-600">Server Software</label>
                                        <p class="text-lg font-mono text-gray-800 mt-1"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                                    </div>
                                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                                        <label class="text-sm font-medium text-amber-600">Database</label>
                                        <p class="text-lg font-mono text-gray-800 mt-1">MySQL</p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                                        <div class="flex justify-between mb-1">
                                            <label class="text-sm font-medium text-amber-600">Memory Usage</label>
                                            <span class="text-sm text-amber-600"><?php echo round(($memoryUsage / $memoryPeak) * 100, 1); ?>%</span>
                                        </div>
                                        <div class="h-2 bg-amber-100 rounded-full overflow-hidden">
                                            <div class="h-full gradient-bg rounded-full" 
                                                 style="width: <?php echo min(100, ($memoryUsage / $memoryPeak) * 100); ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                                        <label class="text-sm font-medium text-amber-600">Uptime</label>
                                        <p class="text-lg font-mono text-gray-800 mt-1"><?php echo $systemInfo['uptime'] ?? 'Unknown'; ?></p>
                                    </div>
                                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                                        <label class="text-sm font-medium text-amber-600">Load Average</label>
                                        <p class="text-lg font-mono text-gray-800 mt-1"><?php echo $systemInfo['load_average'] ?? 'Unknown'; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- About Tab -->
                    <div id="aboutTab" class="space-y-6 hidden">
                        <div class="glass-card rounded-2xl p-6 hover-lift">
                            <div class="text-center mb-6">
                                <div class="w-20 h-20 gradient-bg rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg fire-glow">
                                    <i class="fas fa-fire text-white text-3xl"></i>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-800">NovaCloud</h2>
                                <p class="text-amber-600">Premium Fire-Powered Cloud Storage</p>
                                <p class="text-sm text-amber-500 mt-2">Version 2.0.0</p>
                            </div>

                            <div class="space-y-4">
                                <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                                    <h3 class="font-semibold text-gray-700 mb-2">About NovaCloud</h3>
                                    <p class="text-gray-600">NovaCloud is a blazing fast, secure, and scalable cloud storage platform designed with fiery precision. With advanced file management, real-time collaboration, and enterprise-grade security features, NovaCloud provides a blazing experience for storing, sharing, and managing your digital assets.</p>
                                </div>

                                <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                                    <h3 class="font-semibold text-gray-700 mb-2">Key Features</h3>
                                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                        <li class="flex items-center gap-2"><i class="fas fa-fire text-amber-500"></i> Secure Fire Encryption</li>
                                        <li class="flex items-center gap-2"><i class="fas fa-fire text-amber-500"></i> Real-time Collaboration</li>
                                        <li class="flex items-center gap-2"><i class="fas fa-fire text-amber-500"></i> Advanced User Management</li>
                                        <li class="flex items-center gap-2"><i class="fas fa-fire text-amber-500"></i> Automated Backups</li>
                                        <li class="flex items-center gap-2"><i class="fas fa-fire text-amber-500"></i> Detailed Analytics</li>
                                        <li class="flex items-center gap-2"><i class="fas fa-fire text-amber-500"></i> Mobile Responsive Design</li>
                                        <li class="flex items-center gap-2"><i class="fas fa-fire text-amber-500"></i> API Access</li>
                                        <li class="flex items-center gap-2"><i class="fas fa-fire text-amber-500"></i> Multi-language Support</li>
                                    </ul>
                                </div>

                                <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                                    <h3 class="font-semibold text-gray-700 mb-2">System Requirements</h3>
                                    <ul class="text-sm text-gray-600 space-y-1">
                                        <li>• PHP 7.4 or higher</li>
                                        <li>• MySQL 5.7+ or MariaDB 10.2+</li>
                                        <li>• Apache/Nginx with mod_rewrite</li>
                                        <li>• 100MB+ disk space</li>
                                        <li>• 128MB+ PHP memory limit</li>
                                    </ul>
                                </div>

                                <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                                    <h3 class="font-semibold text-gray-700 mb-2">Support & Documentation</h3>
                                    <div class="flex flex-wrap gap-3">
                                        <a href="#" class="px-4 py-2 bg-amber-50 text-amber-700 rounded-lg text-sm hover:bg-amber-100 transition-colors border border-amber-200">
                                            <i class="fas fa-fire mr-1"></i> Documentation
                                        </a>
                                        <a href="#" class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg text-sm hover:bg-emerald-100 transition-colors border border-emerald-200">
                                            <i class="fas fa-fire mr-1"></i> Help Center
                                        </a>
                                        <a href="#" class="px-4 py-2 bg-orange-50 text-orange-700 rounded-lg text-sm hover:bg-orange-100 transition-colors border border-orange-200">
                                            <i class="fas fa-fire mr-1"></i> Contact Support
                                        </a>
                                        <a href="#" class="px-4 py-2 bg-purple-50 text-purple-700 rounded-lg text-sm hover:bg-purple-100 transition-colors border border-purple-200">
                                            <i class="fas fa-fire mr-1"></i> API Documentation
                                        </a>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-amber-200">
                                    <p class="text-sm text-amber-600 text-center">
                                        <i class="fas fa-fire"></i> &copy; <?php echo date('Y'); ?> NovaCloud. All rights reserved.<br>
                                        Built with <span class="text-red-500">❤️</span> by the NovaCloud Team
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 z-50 hidden">
        <div class="glass-card rounded-xl shadow-xl border border-amber-200 p-4 max-w-sm animate-slide-in">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl gradient-bg flex items-center justify-center fire-glow">
                    <i class="fas fa-fire text-white"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800" id="toastMessage">Action completed successfully!</p>
                    <p class="text-xs text-amber-600 mt-1">Just now</p>
                </div>
                <button onclick="hideToast()" class="text-amber-500 hover:text-amber-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Format bytes helper function
        <?php
        function formatBytes($bytes) {
            if ($bytes === 0) return '0 Bytes';
            $k = 1024;
            $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            $i = floor(log($bytes) / log($k));
            return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
        }
        ?>

        // Tab navigation - SIMPLIFIED VERSION
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('#tabContents > div').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Show selected tab
            const targetTab = document.getElementById(tabName + 'Tab');
            if (targetTab) {
                targetTab.classList.remove('hidden');
            }
            
            // Update active tab button
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            const activeButton = document.querySelector(`.tab-button[data-tab="${tabName}"]`);
            if (activeButton) {
                activeButton.classList.add('active');
            }
        }

        // Set up tab click handlers
        document.addEventListener('DOMContentLoaded', function() {
            // Add click event listeners to all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    showTab(tabName);
                });
            });
            
            // Initialize the first tab as active
            showTab('general');
            
            // Add hover-glow effects
            document.querySelectorAll('.hover-glow').forEach(el => {
                el.addEventListener('mouseenter', () => {
                    el.classList.add('animate-pulse-glow');
                });
                el.addEventListener('mouseleave', () => {
                    el.classList.remove('animate-pulse-glow');
                });
            });
        });

        // Color picker synchronization
        document.getElementById('theme_color').addEventListener('input', function() {
            document.getElementById('theme_color_text').value = this.value;
        });

        document.getElementById('theme_color_text').addEventListener('input', function() {
            const colorInput = document.getElementById('theme_color');
            if (this.value.match(/^#[0-9A-F]{6}$/i)) {
                colorInput.value = this.value;
            }
        });

        // Save all settings
        async function saveAllSettings() {
            const saveBtn = document.querySelector('button[onclick="saveAllSettings()"]');
            const originalHTML = saveBtn.innerHTML;
            
            saveBtn.innerHTML = '<i class="fas fa-fire fa-spin"></i> Igniting...';
            saveBtn.disabled = true;
            
            const settings = {
                site_name: document.getElementById('site_name').value,
                site_url: document.getElementById('site_url').value,
                site_email: document.getElementById('site_email').value,
                theme_color: document.getElementById('theme_color').value,
                registration_enabled: document.getElementById('registration_enabled').checked,
                maintenance_mode: document.getElementById('maintenance_mode').checked,
                two_factor_auth: document.getElementById('two_factor_auth').checked,
                storage_limit: document.getElementById('storage_limit').value,
                file_size_limit: document.getElementById('file_size_limit').value,
                auto_backup: document.getElementById('auto_backup').checked,
                backup_interval: document.getElementById('backup_interval').value,
                smtp_host: document.getElementById('smtp_host').value,
                smtp_port: document.getElementById('smtp_port').value,
                smtp_secure: document.getElementById('smtp_secure').value,
                smtp_username: document.getElementById('smtp_username').value,
                smtp_password: document.getElementById('smtp_password').value,
                recaptcha_site_key: document.getElementById('recaptcha_site_key').value,
                recaptcha_secret_key: document.getElementById('recaptcha_secret_key').value
            };

            try {
                // Simulate API call - replace with actual API endpoint
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Show success message
                showToast('Settings saved with fiery precision!', 'success');
                
                // Clear unsaved changes indicator
                if (window.autoSaveTimeout) {
                    clearTimeout(window.autoSaveTimeout);
                }
                
            } catch (error) {
                showToast('Failed to save settings', 'error');
                console.error('Error:', error);
            }
            
            saveBtn.innerHTML = originalHTML;
            saveBtn.disabled = false;
        }

        // Change password
        document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-fire fa-spin"></i> Changing...';
            submitBtn.disabled = true;
            
            try {
                // Simulate API call - replace with actual API endpoint
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                showToast('Password changed with fiery security!', 'success');
                this.reset();
                
            } catch (error) {
                showToast('Failed to change password', 'error');
            }
            
            submitBtn.innerHTML = originalHTML;
            submitBtn.disabled = false;
        });

        // Create backup
        async function createBackup() {
            if (!confirm('Ignite a new system backup? This may take a few moments.')) return;
            
            const backupBtn = document.querySelector('button[onclick="createBackup()"]');
            const originalHTML = backupBtn.innerHTML;
            
            backupBtn.innerHTML = '<i class="fas fa-fire fa-spin"></i> Igniting Backup...';
            backupBtn.disabled = true;
            
            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                showToast('Backup created with fiery success!', 'success');
                
                // Refresh backup history after 2 seconds
                setTimeout(() => {
                    showTab('backup');
                }, 2000);
                
            } catch (error) {
                showToast('Failed to create backup', 'error');
            }
            
            backupBtn.innerHTML = originalHTML;
            backupBtn.disabled = false;
        }

        // Download backup
        async function downloadBackup(backupId) {
            showToast('Downloading backup with fiery speed...', 'info');
            // In real implementation, this would open a download link
            // window.open(`api/download-backup.php?id=${encodeURIComponent(backupId)}`, '_blank');
        }

        // Restore backup
        async function restoreBackup(backupId) {
            if (!confirm('Warning: This will restore the system to this backup point with fiery power. Current data may be overwritten. Continue?')) return;
            
            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                showToast('Backup restored with fiery success! System will restart.', 'warning');
                
            } catch (error) {
                showToast('Failed to restore backup', 'error');
            }
        }

        // Delete backup
        async function deleteBackup(backupId) {
            if (!confirm('Are you sure you want to delete this backup with fiery finality? This action cannot be undone.')) return;
            
            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                showToast('Backup deleted with fiery precision!', 'success');
                
            } catch (error) {
                showToast('Failed to delete backup', 'error');
            }
        }

        // Test email configuration
        async function testEmailConfig() {
            const email = prompt('Enter email address to send fiery test message:');
            if (!email) return;
            
            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                showToast('Test email sent with fiery success!', 'success');
                
            } catch (error) {
                showToast('Failed to send test email', 'error');
            }
        }

        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            
            if (!toast || !toastMessage) {
                alert(type === 'error' ? 'Error: ' + message : message);
                return;
            }
            
            toastMessage.textContent = message;
            const icon = toast.querySelector('.fa-fire');
            const iconBg = toast.querySelector('.w-10');
            
            if (type === 'error') {
                if (icon) icon.className = 'fas fa-exclamation-circle text-white';
                if (iconBg) iconBg.className = 'w-10 h-10 rounded-xl gradient-bg flex items-center justify-center fire-glow';
            } else if (type === 'warning') {
                if (icon) icon.className = 'fas fa-exclamation-triangle text-white';
                if (iconBg) iconBg.className = 'w-10 h-10 rounded-xl gradient-bg flex items-center justify-center fire-glow';
            } else {
                if (icon) icon.className = 'fas fa-fire text-white';
                if (iconBg) iconBg.className = 'w-10 h-10 rounded-xl gradient-bg flex items-center justify-center fire-glow';
            }
            
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }

        function hideToast() {
            document.getElementById('toast').classList.add('hidden');
        }

        // Auto-save indicator
        let autoSaveTimeout;
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', () => {
                // Show auto-save indicator
                const saveBtn = document.querySelector('button[onclick="saveAllSettings()"]');
                if (saveBtn) {
                    const originalText = saveBtn.innerHTML;
                    saveBtn.innerHTML = '<i class="fas fa-fire text-amber-500 animate-pulse"></i> Unsaved Changes';
                    
                    // Clear previous timeout
                    if (window.autoSaveTimeout) clearTimeout(window.autoSaveTimeout);
                    
                    // Set new timeout for auto-save
                    window.autoSaveTimeout = setTimeout(() => {
                        saveAllSettings();
                        saveBtn.innerHTML = originalText;
                    }, 3000);
                }
            });
        });

        // Add fire animation to interactive elements
        document.querySelectorAll('.toggle-switch input').forEach(toggle => {
            toggle.addEventListener('change', function() {
                if (this.checked) {
                    const toggleParent = this.parentElement;
                    toggleParent.classList.add('animate-pulse-glow');
                    setTimeout(() => toggleParent.classList.remove('animate-pulse-glow'), 500);
                }
            });
        });
    </script>
</body>
</html>
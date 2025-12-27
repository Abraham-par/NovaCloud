<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

$session = new SessionManager();
$functions = new NovaCloudFunctions();

$session->requireLogin();

$userId = $session->getUserId();
$username = $session->getUsername();
$isAdmin = $session->isAdmin();

// Get user data
$db = Database::getInstance();
$sql = "SELECT * FROM users WHERE id = ?";
$result = $db->query($sql, [$userId]);
$user = $result ? $result->fetch_assoc() : [];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $data = [
        'full_name' => $_POST['full_name'],
        'language' => $_POST['language']
    ];
    
    if ($functions->updateProfile($userId, $data)) {
        $_SESSION['success'] = 'Profile updated successfully';
        $_SESSION['language'] = $data['language'];
        header('Location: profile.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update profile';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = 'New passwords do not match';
    } else {
        if ($functions->changePassword($userId, $currentPassword, $newPassword)) {
            $_SESSION['success'] = 'Password changed successfully';
        } else {
            $_SESSION['error'] = 'Current password is incorrect';
        }
    }
    header('Location: profile.php');
    exit();
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;
        
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $uploadDir = 'assets/images/profiles/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = $userId . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Update database
                $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
                $db->query($sql, [$fileName, $userId]);
                
                $_SESSION['success'] = 'Profile picture updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to upload profile picture';
            }
        } else {
            $_SESSION['error'] = 'Invalid file type or size too large (max 5MB)';
        }
    }
    header('Location: profile.php');
    exit();
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - NovaCloud</title>
    
    <!-- Tailwind CSS -->
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
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                        'slide-in': 'slide-in 0.3s ease-out',
                        'fade-in': 'fade-in 0.5s ease-out',
                        'bounce-in': 'bounce-in 0.5s ease-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        'pulse-glow': {
                            '0%, 100%': { opacity: 1 },
                            '50%': { opacity: 0.7 },
                        },
                        'slide-in': {
                            '0%': { transform: 'translateX(-20px)', opacity: 0 },
                            '100%': { transform: 'translateX(0)', opacity: 1 },
                        },
                        'fade-in': {
                            '0%': { opacity: 0 },
                            '100%': { opacity: 1 },
                        },
                        'bounce-in': {
                            '0%': { transform: 'scale(0.9)', opacity: 0 },
                            '70%': { transform: 'scale(1.05)', opacity: 1 },
                            '100%': { transform: 'scale(1)', opacity: 1 },
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
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

        .neon-glow {
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
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

        .hover-lift {
            transition: all 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.15);
        }

        /* Pulse animation for notifications */
        @keyframes pulse-ring {
            0% { transform: scale(0.8); opacity: 0.8; }
            100% { transform: scale(2); opacity: 0; }
        }

        .pulse-ring {
            position: relative;
        }
        .pulse-ring::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid currentColor;
            animation: pulse-ring 2s infinite;
        }

        /* Smooth transitions */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.3s ease;
        }

        /* File upload animation */
        @keyframes upload-progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        .upload-progress {
            animation: upload-progress 2s ease-in-out;
        }

        
    </style>
</head>
<body class="bg-gray-50 overflow-hidden">
    
    <!-- Main Layout -->
    <div class="flex h-screen">
        
        <!-- Sidebar - Glass Design -->
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
                </div>

                <div class="pt-4 border-t border-gray-100/50 mt-4">
                    <a href="logout.php" class="flex items-center gap-4 p-3 rounded-xl text-red-500 hover:bg-red-50/50 transition-all">
                        <i class="fas fa-sign-out-alt text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Logout</span>
                    </a>
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

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Header -->
            <header class="h-20 bg-white/80 backdrop-blur-xl border-b border-gray-100/50 flex items-center justify-between px-6 lg:px-10 z-10">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <span>Profile</span>
                        <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Manage your account settings and preferences</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
                        <i class="fas fa-moon text-gray-600"></i>
                    </button>

                    <!-- Help -->
                    <button class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
                        <i class="fas fa-question text-gray-600"></i>
                    </button>
                </div>
            </header>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-6 lg:p-10">
                <div class="max-w-7xl mx-auto">
                    
                    <!-- Success/Error Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                    <div class="mb-6 glass-card rounded-2xl p-4 border-l-4 border-green-500 animate-bounce-in">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Success!</p>
                                <p class="text-sm text-gray-600"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 glass-card rounded-2xl p-4 border-l-4 border-red-500 animate-bounce-in">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                                <i class="fas fa-exclamation text-red-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Oops!</p>
                                <p class="text-sm text-gray-600"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- User Profile Card -->
                    <div class="glass-card rounded-2xl p-8 mb-8 animate-slide-in">
                        <div class="flex flex-col md:flex-row items-center gap-8">
                            <!-- Profile Picture -->
                            <div class="relative group">
                                <div class="w-32 h-32 rounded-2xl overflow-hidden border-4 border-white shadow-xl relative">
                                    <img src="assets/images/profiles/<?php echo $user['profile_picture'] ?? 'default.png'; ?>" 
                                         alt="Profile" 
                                         class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                                </div>
                                
                                <!-- Upload Button -->
                                <form method="POST" enctype="multipart/form-data" class="absolute -bottom-2 -right-2">
                                    <input type="file" name="profile_picture" id="profilePictureInput" accept="image/*" class="hidden">
                                    <label for="profilePictureInput" class="w-12 h-12 rounded-full gradient-bg flex items-center justify-center text-white shadow-lg hover:shadow-xl cursor-pointer transition-all hover:scale-105 neon-glow">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                </form>
                                
                                <!-- Level Badge -->
                                <div class="absolute -top-3 -left-3 px-3 py-1.5 bg-gradient-to-r from-yellow-400 to-amber-500 rounded-full text-xs font-bold text-white shadow-md flex items-center gap-1">
                                    <i class="fas fa-star"></i>
                                    <span>Pro</span>
                                </div>
                            </div>

                            <!-- User Info -->
                            <div class="flex-1 text-center md:text-left">
                                <div class="flex items-center justify-center md:justify-start gap-3 mb-2">
                                    <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($user['full_name'] ?? $username); ?></h1>
                                    <?php if ($isAdmin): ?>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold flex items-center gap-1">
                                        <i class="fas fa-crown"></i> Admin
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-gray-500 mb-4">
                                    <i class="fas fa-at text-primary-500 mr-1"></i>
                                    @<?php echo htmlspecialchars($username); ?>
                                    <span class="mx-2">•</span>
                                    <i class="far fa-envelope text-primary-500 mr-1"></i>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </p>
                                
                                <div class="flex flex-wrap items-center gap-3 mb-6">
                                    <div class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-full text-sm font-medium flex items-center gap-2">
                                        <i class="fas fa-user-clock"></i>
                                        Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                                    </div>
                                    <div class="px-3 py-1.5 bg-green-50 text-green-600 rounded-full text-sm font-medium flex items-center gap-2">
                                        <i class="fas fa-sign-in-alt"></i>
                                        Last login: <?php echo $user['last_login'] ? date('M j, H:i', strtotime($user['last_login'])) : 'Never'; ?>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap gap-3">
                                    <button onclick="printProfile()" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium flex items-center gap-2">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                    <button onclick="exportProfile()" class="px-4 py-2 gradient-bg text-white rounded-xl hover:shadow-lg transition-all shadow-md text-sm font-medium flex items-center gap-2">
                                        <i class="fas fa-download"></i> Export Data
                                    </button>
                                    <button onclick="shareProfile()" class="px-4 py-2 bg-purple-50 text-purple-600 rounded-xl hover:bg-purple-100 transition-colors text-sm font-medium flex items-center gap-2">
                                        <i class="fas fa-share-alt"></i> Share Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Navigation -->
                    <div class="flex flex-wrap gap-2 mb-8 bg-white/50 p-1 rounded-2xl shadow-sm border border-gray-200/50 w-fit animate-slide-in" style="animation-delay: 0.1s">
                        <button class="tab-btn active px-6 py-3 rounded-xl text-sm font-medium transition-all duration-300 gradient-bg text-white shadow-md" data-tab="profile-info">
                            <i class="fas fa-user-circle mr-2"></i> Personal Info
                        </button>
                        <button class="tab-btn px-6 py-3 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50/50 hover:text-primary-600 transition-all duration-300" data-tab="security">
                            <i class="fas fa-shield-alt mr-2"></i> Security
                        </button>
                        <button class="tab-btn px-6 py-3 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50/50 hover:text-primary-600 transition-all duration-300" data-tab="activity">
                            <i class="fas fa-history mr-2"></i> Activity
                        </button>
                        <button class="tab-btn px-6 py-3 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50/50 hover:text-primary-600 transition-all duration-300" data-tab="preferences">
                            <i class="fas fa-sliders-h mr-2"></i> Preferences
                        </button>
                    </div>

                    <!-- Tab Contents -->
                    <div id="profile-info" class="tab-content active animate-fade-in">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Personal Info Form -->
                            <div class="lg:col-span-2 glass-card rounded-2xl p-8 hover-lift">
                                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100/50">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center">
                                        <i class="fas fa-user-edit text-xl text-primary-600"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-800">Personal Information</h2>
                                        <p class="text-sm text-gray-500">Update your personal details</p>
                                    </div>
                                </div>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="update_profile" value="1">
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                <i class="far fa-user text-primary-500 mr-2"></i>Full Name
                                            </label>
                                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" 
                                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all hover:border-gray-300">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                <i class="fas fa-at text-primary-500 mr-2"></i>Username
                                            </label>
                                            <input type="text" value="<?php echo htmlspecialchars($username); ?>" 
                                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50/50 text-gray-500 cursor-not-allowed" disabled>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                <i class="far fa-envelope text-primary-500 mr-2"></i>Email
                                            </label>
                                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50/50 text-gray-500 cursor-not-allowed" disabled>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                <i class="fas fa-language text-primary-500 mr-2"></i>Language
                                            </label>
                                            <select name="language" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 outline-none hover:border-gray-300">
                                                <option value="en" <?php echo ($user['language'] ?? 'en') == 'en' ? 'selected' : ''; ?>>🇺🇸 English</option>
                                                <option value="am" <?php echo ($user['language'] ?? 'en') == 'am' ? 'selected' : ''; ?>>🇪🇹 አማርኛ</option>
                                                <option value="om" <?php echo ($user['language'] ?? 'en') == 'om' ? 'selected' : ''; ?>>🇪🇹 Afaan Oromoo</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-3 pt-2">
                                        <button type="submit" class="px-6 py-3 gradient-bg text-white rounded-xl hover:shadow-lg transition-all shadow-md text-sm font-medium flex items-center gap-2">
                                            <i class="fas fa-save"></i> Save Changes
                                        </button>
                                        <button type="button" onclick="resetForm()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-medium flex items-center gap-2">
                                            <i class="fas fa-redo"></i> Reset
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Stats & Storage -->
                            <div class="space-y-6">
                                <!-- Account Stats -->
                                <div class="glass-card rounded-2xl p-6 hover-lift">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center">
                                            <i class="fas fa-chart-line text-lg text-primary-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Account Stats</h3>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <div>
                                                    <span class="block text-xl font-bold text-gray-800"><?php echo $functions->getUserStats($userId)['total_files'] ?? 0; ?></span>
                                                    <span class="text-xs text-gray-500">Files Uploaded</span>
                                                </div>
                                            </div>
                                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-600 rounded-full">+12%</span>
                                        </div>
                                        
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                                                    <i class="fas fa-share-alt"></i>
                                                </div>
                                                <div>
                                                    <span class="block text-xl font-bold text-gray-800"><?php echo $functions->getUserStats($userId)['shared_files'] ?? 0; ?></span>
                                                    <span class="text-xs text-gray-500">Files Shared</span>
                                                </div>
                                            </div>
                                            <span class="text-xs px-2 py-1 bg-green-100 text-green-600 rounded-full">Active</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Storage Usage -->
                                <div class="glass-card rounded-2xl p-6 hover-lift relative overflow-hidden">
                                    <!-- Background Gradient -->
                                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 rounded-full bg-gradient-to-br from-primary-500 to-purple-500 opacity-10"></div>
                                    
                                    <div class="relative">
                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center">
                                                <i class="fas fa-database text-lg text-primary-600"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-gray-800">Storage Usage</h3>
                                                <p class="text-xs text-gray-500">Your cloud storage space</p>
                                            </div>
                                        </div>
                                        
                                        <?php
                                        $stats = $functions->getUserStats($userId);
                                        $used = $stats['total_size'] ?? 0;
                                        $total = 5 * 1024 * 1024 * 1024;
                                        $percent = $total > 0 ? min(100, ($used / $total) * 100) : 0;
                                        ?>
                                        
                                        <div class="mb-3">
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="font-medium text-gray-700"><?php echo formatBytes($used); ?> used</span>
                                                <span class="text-gray-500"><?php echo formatBytes($total); ?> total</span>
                                            </div>
                                            <div class="w-full h-2.5 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full gradient-bg rounded-full transition-all duration-1000 ease-out" style="width: <?php echo $percent; ?>%"></div>
                                            </div>
                                        </div>
                                        
                                        <a href="pricing.php" class="block w-full py-3 gradient-bg text-white rounded-xl text-center text-sm font-bold hover:shadow-lg transition-all shadow-md flex items-center justify-center gap-2">
                                            <i class="fas fa-rocket"></i> Upgrade Storage
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Tab -->
                    <div id="security" class="tab-content animate-fade-in hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Change Password -->
                            <div class="glass-card rounded-2xl p-8 hover-lift">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-50 to-pink-50 flex items-center justify-center">
                                        <i class="fas fa-key text-xl text-red-600"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-800">Change Password</h2>
                                        <p class="text-sm text-gray-500">Update your password for better security</p>
                                    </div>
                                </div>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="change_password" value="1">
                                    <div class="space-y-4">
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                <i class="fas fa-lock text-primary-500 mr-2"></i>Current Password
                                            </label>
                                            <input type="password" name="current_password" required 
                                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all hover:border-gray-300">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                <i class="fas fa-key text-primary-500 mr-2"></i>New Password
                                            </label>
                                            <input type="password" name="new_password" required pattern=".{8,}" 
                                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all hover:border-gray-300">
                                            <p class="text-xs text-gray-400 mt-1">Minimum 8 characters</p>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                <i class="fas fa-check-circle text-primary-500 mr-2"></i>Confirm Password
                                            </label>
                                            <input type="password" name="confirm_password" required 
                                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all hover:border-gray-300">
                                        </div>
                                    </div>
                                    <button type="submit" class="mt-6 w-full py-3 bg-gray-800 text-white rounded-xl hover:bg-gray-900 transition text-sm font-medium flex items-center justify-center gap-2">
                                        <i class="fas fa-shield-alt"></i> Update Password
                                    </button>
                                </form>
                            </div>

                            <!-- Two-Factor Authentication -->
                            <div class="space-y-6">
                                <div class="glass-card rounded-2xl p-8 hover-lift">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-3">
                                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 flex items-center justify-center">
                                                    <i class="fas fa-shield-alt text-xl text-amber-600"></i>
                                                </div>
                                                <div>
                                                    <h2 class="text-xl font-bold text-gray-800">Two-Factor Authentication</h2>
                                                    <p class="text-sm text-gray-500">Add an extra layer of security</p>
                                                </div>
                                            </div>
                                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 mb-4">
                                                <i class="fas fa-times-circle mr-1"></i> Status: Disabled
                                            </div>
                                        </div>
                                    </div>
                                    <button class="mt-4 w-full py-3 gradient-bg text-white rounded-xl hover:shadow-lg transition-all shadow-md text-sm font-medium flex items-center justify-center gap-2">
                                        <i class="fas fa-qrcode"></i> Enable 2FA
                                    </button>
                                </div>

                                <!-- Security Question -->
                                <div class="glass-card rounded-2xl p-8 hover-lift">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-50 to-emerald-50 flex items-center justify-center">
                                            <i class="fas fa-question-circle text-xl text-green-600"></i>
                                        </div>
                                        <div>
                                            <h2 class="text-xl font-bold text-gray-800">Security Question</h2>
                                            <p class="text-sm text-gray-500">Set up a recovery question</p>
                                        </div>
                                    </div>
                                    
                                    <form method="POST" action="update-security-question.php">
                                        <div class="space-y-3">
                                            <select name="security_question" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 outline-none hover:border-gray-300 text-sm">
                                                <option value="" disabled selected>Select a security question...</option>
                                                <option value="maiden_name" <?php echo ($user['security_question'] ?? '') == 'maiden_name' ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                                                <option value="pet_name" <?php echo ($user['security_question'] ?? '') == 'pet_name' ? 'selected' : ''; ?>>What was your first pet's name?</option>
                                                <option value="city_born" <?php echo ($user['security_question'] ?? '') == 'city_born' ? 'selected' : ''; ?>>In which city were you born?</option>
                                            </select>
                                            <input type="text" name="security_answer" placeholder="Your answer" 
                                                   value="<?php echo htmlspecialchars($user['security_answer'] ?? ''); ?>" 
                                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-primary-500 outline-none hover:border-gray-300">
                                        </div>
                                        <button type="submit" class="mt-4 w-full py-3 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition text-sm font-medium">
                                            Save Security Answer
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Login Activity -->
                            <div class="lg:col-span-2 glass-card rounded-2xl p-8 hover-lift">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-50 to-blue-50 flex items-center justify-center">
                                        <i class="fas fa-history text-xl text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-800">Login Activity</h2>
                                        <p class="text-sm text-gray-500">Recent devices and locations</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 hover:bg-gray-50/30 rounded-xl transition-colors">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                                <i class="fas fa-desktop text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Chrome on Windows</p>
                                                <p class="text-xs text-gray-500">Addis Ababa, Ethiopia • Active now</p>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-bold rounded-full flex items-center gap-1">
                                            <i class="fas fa-circle text-[8px]"></i> Current
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between p-4 hover:bg-gray-50/30 rounded-xl transition-colors">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                                                <i class="fas fa-mobile-alt text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Safari on iPhone</p>
                                                <p class="text-xs text-gray-500">Addis Ababa, Ethiopia • 2 days ago</p>
                                            </div>
                                        </div>
                                        <button class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors text-sm font-medium flex items-center gap-2">
                                            <i class="fas fa-ban"></i> Revoke
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Tab -->
                    <div id="activity" class="tab-content animate-fade-in hidden">
                        <div class="glass-card rounded-2xl p-8 hover-lift">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-50 to-pink-50 flex items-center justify-center">
                                    <i class="fas fa-chart-bar text-xl text-purple-600"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800">Activity Timeline</h2>
                                    <p class="text-sm text-gray-500">Your recent actions and events</p>
                                </div>
                            </div>
                            
                            <div class="activity-timeline space-y-6">
                                <!-- Timeline items will be loaded here -->
                                <div class="text-center py-12">
                                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center mx-auto mb-4 shadow-inner">
                                        <i class="fas fa-spinner fa-spin text-3xl text-gray-300"></i>
                                    </div>
                                    <p class="text-gray-500">Loading activity timeline...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preferences Tab -->
                    <div id="preferences" class="tab-content animate-fade-in hidden">
                        <div class="glass-card rounded-2xl p-8 hover-lift">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 flex items-center justify-center">
                                    <i class="fas fa-sliders-h text-xl text-amber-600"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800">Notifications & Preferences</h2>
                                    <p class="text-sm text-gray-500">Customize your experience</p>
                                </div>
                            </div>
                            
                            <form id="notificationSettings" class="space-y-6">
                                <div class="flex items-center justify-between p-4 hover:bg-gray-50/30 rounded-xl transition-colors">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">Email Notifications</h4>
                                        <p class="text-xs text-gray-500">Receive summaries and alerts via email</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-500"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-4 hover:bg-gray-50/30 rounded-xl transition-colors">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">File Upload Alerts</h4>
                                        <p class="text-xs text-gray-500">Notify when large files are uploaded</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-500"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-4 hover:bg-gray-50/30 rounded-xl transition-colors">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">Profile Visibility</h4>
                                        <p class="text-xs text-gray-500">Make your profile visible to other users</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-500"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-4 hover:bg-gray-50/30 rounded-xl transition-colors">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">Dark Mode</h4>
                                        <p class="text-xs text-gray-500">Enable dark theme for better visibility</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" id="darkModeToggle">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-800"></div>
                                    </label>
                                </div>
                            </form>
                            
                            <div class="mt-8 flex justify-end gap-3">
                                <button onclick="resetPreferences()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-medium flex items-center gap-2">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                                <button onclick="savePreferences()" class="px-6 py-3 gradient-bg text-white rounded-xl hover:shadow-lg transition-all shadow-md text-sm font-medium flex items-center gap-2">
                                    <i class="fas fa-save"></i> Save Preferences
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-5 right-5 z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl border border-gray-100 p-4 max-w-sm animate-slide-in">
            <div class="flex items-start gap-3">
                <div id="toastIcon" class="w-10 h-10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-info-circle text-white"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800" id="toastMessage">Notification</p>
                    <p class="text-xs text-gray-500 mt-1" id="toastTime">Just now</p>
                </div>
                <button onclick="hideToast()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Tab Switching
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Reset all buttons
                tabBtns.forEach(b => {
                    b.classList.remove('active', 'gradient-bg', 'text-white', 'shadow-md');
                    b.classList.add('text-gray-600', 'hover:bg-gray-50/50');
                });
                
                // Hide all content
                tabContents.forEach(c => {
                    c.classList.add('hidden');
                    c.classList.remove('active');
                });

                // Activate clicked button
                btn.classList.add('active', 'gradient-bg', 'text-white', 'shadow-md');
                btn.classList.remove('text-gray-600', 'hover:bg-gray-50/50');

                // Show corresponding content
                const tabId = btn.getAttribute('data-tab');
                const content = document.getElementById(tabId);
                content.classList.remove('hidden');
                content.classList.add('active', 'animate-fade-in');
                
                // Load activity timeline if needed
                if(tabId === 'activity') {
                    setTimeout(() => {
                        loadActivityTimeline();
                    }, 300);
                }
            });
        });

        // Profile Picture Upload
        const profilePicInput = document.getElementById('profilePictureInput');
        profilePicInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                showToast('Uploading profile picture...', 'info');
                // Create loading animation
                const form = this.closest('form');
                const originalButton = form.querySelector('label');
                const originalHTML = originalButton.innerHTML;
                originalButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                originalButton.classList.add('cursor-not-allowed');
                
                setTimeout(() => {
                    form.submit();
                }, 1500);
            }
        });

        // Reset Form
        function resetForm() {
            if (confirm('Are you sure you want to reset all changes?')) {
                document.querySelector('#profile-info form').reset();
                showToast('Form reset successfully', 'success');
            }
        }

        // Action Buttons
        function printProfile() { 
            showToast('Opening print dialog...', 'info');
            setTimeout(() => window.print(), 1000);
        }
        
        function exportProfile() { 
            showToast('Preparing data export...', 'info');
            setTimeout(() => {
                showToast('Data exported successfully!', 'success');
                // In a real app, this would trigger a download
                const link = document.createElement('a');
                link.href = '#';
                link.download = 'profile-export.json';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }, 2000);
        }
        
        function shareProfile() {
            showToast('Profile sharing feature coming soon!', 'info');
        }

        // Load Activity Timeline
        function loadActivityTimeline() {
            const timelineContainer = document.querySelector('.activity-timeline');
            
            // Simulate API delay
            setTimeout(() => {
                const activities = [
                    { 
                        icon: 'fa-lock', 
                        color: 'text-green-500', 
                        bg: 'bg-green-100', 
                        title: 'Password Changed', 
                        time: '2 hours ago', 
                        desc: 'You updated your security password.',
                        highlight: true
                    },
                    { 
                        icon: 'fa-file-upload', 
                        color: 'text-blue-500', 
                        bg: 'bg-blue-100', 
                        title: 'File Uploaded', 
                        time: 'Yesterday, 14:30', 
                        desc: 'project_report.pdf (2.4MB) uploaded successfully.'
                    },
                    { 
                        icon: 'fa-cog', 
                        color: 'text-purple-500', 
                        bg: 'bg-purple-100', 
                        title: 'Settings Updated', 
                        time: '3 days ago', 
                        desc: 'Changed notification preferences.'
                    },
                    { 
                        icon: 'fa-share-alt', 
                        color: 'text-amber-500', 
                        bg: 'bg-amber-100', 
                        title: 'File Shared', 
                        time: '1 week ago', 
                        desc: 'Shared design_resources.zip with 3 colleagues.'
                    },
                    { 
                        icon: 'fa-user-plus', 
                        color: 'text-pink-500', 
                        bg: 'bg-pink-100', 
                        title: 'Account Verified', 
                        time: '2 weeks ago', 
                        desc: 'Email verification completed successfully.'
                    }
                ];

                timelineContainer.innerHTML = activities.map(act => `
                    <div class="relative pl-10 pb-6 group">
                        <div class="absolute left-0 top-0 w-8 h-8 rounded-full ${act.bg} ${act.color} flex items-center justify-center border-4 border-white shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas ${act.icon} text-sm"></i>
                        </div>
                        <div class="ml-2">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-sm font-bold text-gray-800">${act.title}</h4>
                                ${act.highlight ? '<span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-medium rounded-full">Security</span>' : ''}
                            </div>
                            <p class="text-xs text-gray-500 mb-1">${act.time}</p>
                            <p class="text-sm text-gray-600">${act.desc}</p>
                        </div>
                    </div>
                `).join('');
                
                // Add connecting lines
                const items = timelineContainer.querySelectorAll('.relative');
                items.forEach((item, index) => {
                    if (index < items.length - 1) {
                        const line = document.createElement('div');
                        line.className = 'absolute left-4 top-8 w-0.5 h-full bg-gray-200';
                        item.appendChild(line);
                    }
                });
                
            }, 800);
        }

        // Toast Notification System
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const toastTime = document.getElementById('toastTime');
            const toastIcon = document.getElementById('toastIcon');
            
            // Set message
            toastMessage.textContent = message;
            toastTime.textContent = 'Just now';
            
            // Set icon and color based on type
            toastIcon.className = 'w-10 h-10 rounded-xl flex items-center justify-center';
            
            switch(type) {
                case 'success':
                    toastIcon.classList.add('bg-green-500');
                    toastIcon.innerHTML = '<i class="fas fa-check text-white"></i>';
                    break;
                case 'error':
                    toastIcon.classList.add('bg-red-500');
                    toastIcon.innerHTML = '<i class="fas fa-exclamation text-white"></i>';
                    break;
                case 'warning':
                    toastIcon.classList.add('bg-amber-500');
                    toastIcon.innerHTML = '<i class="fas fa-exclamation-triangle text-white"></i>';
                    break;
                default:
                    toastIcon.classList.add('bg-primary-500');
                    toastIcon.innerHTML = '<i class="fas fa-info-circle text-white"></i>';
            }
            
            // Show toast with animation
            toast.classList.remove('hidden');
            toast.classList.add('animate-slide-in');
            
            // Auto hide after 4 seconds
            setTimeout(hideToast, 4000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('hidden');
            toast.classList.remove('animate-slide-in');
        }

        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.addEventListener('click', () => {
            const icon = themeToggle.querySelector('i');
            if (icon.classList.contains('fa-moon')) {
                icon.className = 'fas fa-sun text-amber-500';
                document.documentElement.setAttribute('data-theme', 'dark');
                showToast('Dark mode enabled', 'success');
            } else {
                icon.className = 'fas fa-moon text-gray-600';
                document.documentElement.setAttribute('data-theme', 'light');
                showToast('Light mode enabled', 'success');
            }
        });

        // Dark Mode Toggle in Preferences
        const darkModeToggle = document.getElementById('darkModeToggle');
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                showToast('Dark mode enabled', 'success');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                showToast('Light mode enabled', 'success');
            }
        });

        // Save Preferences
        function savePreferences() {
            showToast('Preferences saved successfully!', 'success');
        }

        function resetPreferences() {
            if (confirm('Reset all preferences to default?')) {
                document.querySelectorAll('#notificationSettings input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                document.getElementById('darkModeToggle').checked = false;
                showToast('Preferences reset to default', 'success');
            }
        }

        // Initialize tooltips
        document.querySelectorAll('[title]').forEach(el => {
            el.addEventListener('mouseenter', function(e) {
                const title = this.getAttribute('title');
                if (title) {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'absolute z-50 px-3 py-1.5 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-lg -translate-x-1/2 -translate-y-full';
                    tooltip.textContent = title;
                    tooltip.style.left = '50%';
                    tooltip.style.top = '-10px';
                    tooltip.style.minWidth = '120px';
                    tooltip.style.textAlign = 'center';
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

        // Sidebar hover effect
        const sidebar = document.querySelector('aside');
        sidebar.addEventListener('mouseenter', () => {
            sidebar.classList.add('w-64');
        });
        
        sidebar.addEventListener('mouseleave', () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('w-64');
            }
        });

        // Animate cards on load
        document.addEventListener('DOMContentLoaded', () => {
            // Add floating animation to random cards
            document.querySelectorAll('.glass-card').forEach((card, index) => {
                if (index % 4 === 0) {
                    card.classList.add('floating');
                    card.style.animationDelay = `${index * 0.3}s`;
                }
            });
            
            // Initialize with profile tab
            loadActivityTimeline();
        });

        // Add some visual effects
        document.querySelectorAll('input, select, textarea').forEach(el => {
            el.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-primary-200', 'rounded-xl');
            });
            
            el.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-primary-200', 'rounded-xl');
            });
        });

        // Password strength indicator (example)
        document.querySelector('input[name="new_password"]')?.addEventListener('input', function(e) {
            const password = e.target.value;
            const strength = document.getElementById('passwordStrength');
            
            if (!strength) {
                const strengthMeter = document.createElement('div');
                strengthMeter.id = 'passwordStrength';
                strengthMeter.className = 'mt-2 text-xs';
                this.parentElement.appendChild(strengthMeter);
            }
            
            let strengthText = '';
            let strengthColor = '';
            
            if (password.length === 0) {
                strengthText = '';
            } else if (password.length < 6) {
                strengthText = 'Weak';
                strengthColor = 'text-red-500';
            } else if (password.length < 10) {
                strengthText = 'Medium';
                strengthColor = 'text-amber-500';
            } else {
                strengthText = 'Strong';
                strengthColor = 'text-green-500';
            }
            
            document.getElementById('passwordStrength').innerHTML = strengthText ? 
                `<span class="${strengthColor} font-medium">Strength: ${strengthText}</span>` : '';
        });
    </script>

</body>
</html>

<?php include 'includes/footer.php'; ?>
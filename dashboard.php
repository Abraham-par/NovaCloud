<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

$session = new SessionManager();
$functions = new NovaCloudFunctions();

$session->requireLogin();
if (!$session->isLoggedIn()) {
    http_response_code(403);
    include __DIR__ . '/errors/403.php';
    exit();
}

$userId = $session->getUserId();
$username = $session->getUsername();
$isAdmin = $session->isAdmin();

// Get user files
$search = $_GET['search'] ?? '';
$files = $functions->getUserFiles($userId, $search);

// Get user stats
$stats = $functions->getUserStats($userId);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileId = $functions->uploadFile($userId, $_FILES['file']);
        if ($fileId) {
            header('Location: dashboard.php?upload=success');
            exit();
        }
    }
}

// Handle file deletion
if (isset($_GET['delete'])) {
    if ($functions->deleteFile($_GET['delete'], $userId)) {
        header('Location: dashboard.php?delete=success');
        exit();
    }
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NovaCloud</title>
    
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
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.15);
        }

        /* File Icon Colors */
        .file-icon-pdf { color: #ef4444; }
        .file-icon-image { color: #10b981; }
        .file-icon-video { color: #8b5cf6; }
        .file-icon-doc { color: #3b82f6; }
        .file-icon-archive { color: #f59e0b; }
        .file-icon-audio { color: #ec4899; }
        .file-icon-code { color: #6366f1; }

        /* Neon Glow */
        .neon-glow {
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
        }

        /* Background Patterns */
        .bg-grid-pattern {
            background-image: 
                linear-gradient(to right, rgba(102, 126, 234, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(102, 126, 234, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        /* Sidebar Gradient */
        .sidebar-gradient {
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        }

        /* Smooth Transitions */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        
        /* Disable all animations */
        * {
            animation: none !important;
        }
        
        /* Fade in effect for modals (kept for functionality) */
        .fade-in {
            opacity: 0;
            animation: none;
        }
        
        .fade-in.show {
            opacity: 1;
        }
        
        /* Slide in effect (kept for functionality) */
        .slide-in {
            transform: translateY(20px);
            opacity: 0;
            animation: none;
        }
        
        .slide-in.show {
            transform: translateY(0);
            opacity: 1;
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
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
                    <a href="dashboard.php" class="flex items-center gap-4 p-3 rounded-xl bg-gradient-to-r from-blue-50 to-purple-50 text-primary-600 border border-blue-100 shadow-sm">
                        <i class="fas fa-home text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Dashboard</span>
                    </a>
                    
                    <a href="profile.php" class="flex items-center gap-4 p-3 rounded-xl text-gray-600 hover:bg-gray-50/50 hover:text-gray-900 transition-all">
                        <i class="fas fa-user-circle text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Profile</span>
                    </a>
                    
                    <a href="#" id="uploadTriggerSidebar" class="flex items-center gap-4 p-3 rounded-xl text-gray-600 hover:bg-gray-50/50 hover:text-gray-900 transition-all">
                        <i class="fas fa-cloud-upload-alt text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Upload</span>
                    </a>
                    
                    <a href="about.php" class="flex items-center gap-4 p-3 rounded-xl text-gray-600 hover:bg-gray-50/50 hover:text-gray-900 transition-all">
                        <i class="fas fa-info-circle text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">About</span>
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
                        <span>Welcome back,</span>
                        <span class="gradient-text"><?php echo htmlspecialchars(explode(' ', $username)[0]); ?></span>
                        <span class="text-2xl">👋</span>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Manage your files and storage</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <form method="GET" class="relative">
                        <input type="text" name="search" 
                               placeholder="Search files..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="w-72 pl-12 pr-4 py-3 rounded-2xl border-0 bg-gray-50/80 focus:bg-white focus:ring-2 focus:ring-primary-200 focus:ring-offset-2 transition-all text-sm outline-none shadow-sm">
                        <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                    </form>

                    <!-- Upload Button - Floating Action -->
                    <button id="uploadBtn" class="relative">
                        <div class="w-14 h-14 gradient-bg rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all hover:scale-105 neon-glow">
                            <i class="fas fa-cloud-upload-alt text-white text-xl"></i>
                        </div>
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                            <i class="fas fa-plus"></i>
                        </div>
                    </button>

                    <!-- Notifications -->
                    <button class="relative w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center hover:bg-gray-100 transition-colors">
                        <i class="fas fa-bell text-gray-600"></i>
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">3</span>
                    </button>
                </div>
            </header>

            <!-- Content Area with Pattern Background -->
            <div class="flex-1 overflow-y-auto bg-grid-pattern p-6 lg:p-10">
                
                <!-- Stats Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Storage Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Storage Used</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo formatBytes($stats['total_size'] ?? 0); ?></h3>
                                <p class="text-xs text-gray-400 mt-1">of 100GB total</p>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl">
                                <i class="fas fa-hdd text-xl text-primary-500"></i>
                            </div>
                        </div>
                        <div class="relative pt-2">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full gradient-bg rounded-full" style="width: <?php echo min(100, (($stats['total_size'] ?? 0) / (100 * 1024 * 1024 * 1024)) * 100); ?>%"></div>
                            </div>
                            <span class="text-xs text-gray-500 absolute right-0 -top-5"><?php echo number_format(min(100, (($stats['total_size'] ?? 0) / (100 * 1024 * 1024 * 1024)) * 100), 1); ?>%</span>
                        </div>
                    </div>

                    <!-- Files Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Total Files</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_files'] ?? 0; ?></h3>
                                <p class="text-xs text-green-500 mt-1 flex items-center gap-1">
                                    <i class="fas fa-arrow-up"></i> 12% this month
                                </p>
                            </div>
                            <div class="relative">
                                <div class="p-3 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl">
                                    <i class="fas fa-file-alt text-xl text-green-500"></i>
                                </div>
                                <div class="absolute -top-2 -right-2 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-plus text-white text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
                            <i class="fas fa-clock"></i>
                            <span>Last upload: <?php echo $stats['last_upload'] ? date('M d', strtotime($stats['last_upload'])) : 'Never'; ?></span>
                        </div>
                    </div>

                    <!-- Shared Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Shared Files</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['shared_files'] ?? 0; ?></h3>
                                <p class="text-xs text-blue-500 mt-1">With 3 collaborators</p>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl">
                                <i class="fas fa-share-alt text-xl text-purple-500"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex -space-x-2">
                            <?php for($i = 0; $i < 3; $i++): ?>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 border-2 border-white"></div>
                            <?php endfor; ?>
                            <div class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center">
                                <span class="text-xs font-bold text-gray-600">+<?php echo max(0, (($stats['shared_with'] ?? 3) - 3)); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Quick Actions</p>
                                <h3 class="text-xl font-bold text-gray-800">Manage Files</h3>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl">
                                <i class="fas fa-bolt text-xl text-amber-500"></i>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button onclick="showUpload()" class="py-2 px-3 bg-primary-50 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-100 transition-colors">
                                <i class="fas fa-upload mr-1"></i> Upload
                            </button>
                            <button class="py-2 px-3 bg-purple-50 text-purple-600 rounded-lg text-sm font-medium hover:bg-purple-100 transition-colors">
                                <i class="fas fa-share mr-1"></i> Share
                            </button>
                            <button class="py-2 px-3 bg-green-50 text-green-600 rounded-lg text-sm font-medium hover:bg-green-100 transition-colors">
                                <i class="fas fa-folder-plus mr-1"></i> New Folder
                            </button>
                            <button class="py-2 px-3 bg-amber-50 text-amber-600 rounded-lg text-sm font-medium hover:bg-amber-100 transition-colors">
                                <i class="fas fa-sort mr-1"></i> Sort
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Upload Area - Hidden by Default -->
                <div id="uploadForm" class="hidden mb-8">
                    <div class="glass-card rounded-2xl border-2 border-dashed border-primary-200 p-8 text-center relative overflow-hidden">
                        <!-- Background Pattern -->
                        <div class="absolute inset-0 opacity-5">
                            <div class="absolute top-0 left-0 w-32 h-32 bg-gradient-to-br from-primary-500 to-purple-500 rounded-full -translate-x-16 -translate-y-16"></div>
                            <div class="absolute bottom-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full translate-x-16 translate-y-16"></div>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" class="relative z-10 max-w-md mx-auto">
                            <div class="mb-6">
                                <div class="w-20 h-20 gradient-bg rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <i class="fas fa-cloud-upload-alt text-white text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Upload Your Files</h3>
                                <p class="text-sm text-gray-500">Drag & drop or click to browse</p>
                            </div>
                            
                            <div class="mb-6">
                                <input type="file" name="file" id="fileInput" required 
                                       class="hidden" multiple>
                                <label for="fileInput" class="block w-full py-12 px-4 border-2 border-dashed border-gray-300 rounded-2xl cursor-pointer hover:border-primary-300 transition-colors bg-white/50">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-3"></i>
                                    <p class="text-gray-600">Click to select files</p>
                                    <p class="text-sm text-gray-400 mt-2">Maximum file size: 2GB</p>
                                </label>
                            </div>
                            
                            <div id="fileList" class="mb-6 text-left hidden">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Selected Files:</h4>
                                <div class="space-y-2 max-h-40 overflow-y-auto"></div>
                            </div>
                            
                            <div class="flex items-center justify-center gap-4 mb-6">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="is_public" id="is_public" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                                    <label for="is_public" class="text-sm text-gray-600 cursor-pointer">Make Public</label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="encrypt" id="encrypt" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500" checked>
                                    <label for="encrypt" class="text-sm text-gray-600 cursor-pointer">Encrypt File</label>
                                </div>
                            </div>
                            
                            <div class="flex gap-3 justify-center">
                                <button type="button" id="cancelUpload" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition-colors shadow-sm">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-3 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md flex items-center gap-2">
                                    <i class="fas fa-upload"></i>
                                    Start Upload
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Files Section -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">My Files</h2>
                        <p class="text-sm text-gray-500">All your uploaded files in one place</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">
                            <i class="fas fa-sort-amount-down mr-2"></i> Sort
                        </button>
                        <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>
                        <div class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl">
                            <i class="fas fa-th-large text-gray-400"></i>
                            <i class="fas fa-list text-primary-500"></i>
                        </div>
                    </div>
                </div>

                <?php if (empty($files)): ?>
                    <!-- Empty State -->
                    <div class="glass-card rounded-2xl p-12 text-center border-2 border-dashed border-gray-200">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center mx-auto mb-6 shadow-inner">
                            <i class="fas fa-folder-open text-4xl text-gray-300"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 mb-2">No files yet</h3>
                        <p class="text-gray-500 mb-6 max-w-md mx-auto">Upload your first file to get started with NovaCloud Premium</p>
                        <button onclick="showUpload()" class="px-6 py-3 gradient-bg text-white rounded-xl font-medium hover:shadow-lg transition-all shadow-md">
                            <i class="fas fa-cloud-upload-alt mr-2"></i> Upload First File
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Files Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($files as $file): 
                            $fileType = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
                            $iconClass = 'fas fa-file';
                            $colorClass = 'file-icon-doc';
                            
                            if (in_array($fileType, ['pdf'])) {
                                $iconClass = 'fas fa-file-pdf';
                                $colorClass = 'file-icon-pdf';
                            } elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                                $iconClass = 'fas fa-file-image';
                                $colorClass = 'file-icon-image';
                            } elseif (in_array($fileType, ['mp4', 'avi', 'mov', 'wmv'])) {
                                $iconClass = 'fas fa-file-video';
                                $colorClass = 'file-icon-video';
                            } elseif (in_array($fileType, ['zip', 'rar', '7z', 'tar'])) {
                                $iconClass = 'fas fa-file-archive';
                                $colorClass = 'file-icon-archive';
                            }
                        ?>
                        <div class="group glass-card rounded-2xl border border-gray-100 hover-lift overflow-hidden flex flex-col">
                            <!-- File Header -->
                            <div class="h-40 relative overflow-hidden bg-gradient-to-br from-gray-50 to-white group-hover:from-primary-50/30 transition-all duration-300">
                                <!-- Background Pattern -->
                                <div class="absolute inset-0 opacity-10">
                                    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-primary-500 to-purple-500 rounded-full translate-x-10 -translate-y-10"></div>
                                </div>
                                
                                <div class="relative h-full flex flex-col items-center justify-center p-6">
                                    <div class="w-16 h-16 rounded-2xl bg-white shadow-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                                        <i class="<?php echo $iconClass; ?> text-3xl <?php echo $colorClass; ?>"></i>
                                    </div>
                                    <span class="absolute top-3 right-3 text-xs font-semibold px-2 py-1 rounded-full bg-white/80 text-gray-600 border border-gray-200">
                                        <?php echo strtoupper($fileType); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- File Info -->
                            <div class="p-5 flex-1">
                                <h4 class="font-semibold text-gray-800 truncate mb-2 group-hover:text-primary-600 transition-colors" title="<?php echo htmlspecialchars($file['filename']); ?>">
                                    <?php echo htmlspecialchars($file['filename']); ?>
                                </h4>
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-database"></i>
                                        <?php echo formatBytes($file['file_size']); ?>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="far fa-calendar"></i>
                                        <?php echo date('M d, Y', strtotime($file['uploaded_at'])); ?>
                                    </span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1 text-xs">
                                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                        <span class="text-gray-500">Uploaded</span>
                                    </div>
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center gap-1">
                                        <?php if ($file['is_public']): ?>
                                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-600">
                                            <i class="fas fa-globe-americas mr-1"></i> Public
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- File Actions -->
                            <div class="px-5 py-4 border-t border-gray-100/50 bg-white/50 flex justify-between items-center">
                                <a href="download.php?file=<?php echo $file['id']; ?>" 
                                   class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors tooltip" 
                                   title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                
                                <button type="button" data-action="share" data-file-id="<?php echo $file['id']; ?>" onclick="openShareModal(<?php echo $file['id']; ?>)"
                                        class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center hover:bg-purple-100 transition-colors tooltip" 
                                        title="Share">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                                
                                <button type="button" data-action="preview" data-file-id="<?php echo $file['id']; ?>" onclick="previewFile(<?php echo $file['id']; ?>)"
                                        class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-100 transition-colors tooltip" 
                                        title="Preview">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <a href="?delete=<?php echo $file['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this file?')"
                                   class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-100 transition-colors tooltip" 
                                   title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-8 flex items-center justify-between">
                        <p class="text-sm text-gray-500">Showing <?php echo count($files); ?> of <?php echo $stats['total_files'] ?? 0; ?> files</p>
                        <div class="flex items-center gap-2">
                            <button class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="w-10 h-10 rounded-xl gradient-bg text-white flex items-center justify-center">1</button>
                            <button class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">2</button>
                            <button class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">3</button>
                            <button class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Share Modal -->
    <div id="shareModal" class="fixed inset-0 z-50 hidden fade-in" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity"></div>

        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 overflow-hidden transform transition-all slide-in">
                <!-- Modal Decoration -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary-500 to-purple-500 rounded-full -translate-y-16 translate-x-16 opacity-10"></div>
                
                <div class="relative">
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-primary-50 to-purple-50 mb-4">
                            <i class="fas fa-share text-primary-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Share File</h3>
                        <p class="text-sm text-gray-500">Share this file with others by entering their usernames</p>
                    </div>

                    <form id="shareForm">
                        <input type="hidden" id="fileToShare">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Usernames (comma separated)</label>
                            <div class="relative">
                                <input type="text" id="shareUsers" 
                                       class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all bg-gray-50/50"
                                       placeholder="user1, user2, user3">
                                <i class="fas fa-users absolute left-4 top-3.5 text-gray-400"></i>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">They will receive access to download this file</p>
                        </div>
                        <div class="mb-4 flex items-center gap-3">
                            <input type="checkbox" id="makeLinkCheckbox" class="w-4 h-4 text-primary-600 rounded border-gray-300" />
                            <label for="makeLinkCheckbox" class="text-sm text-gray-700">Create public share link (anyone with link can download)</label>
                        </div>
                        
                        <div class="flex gap-3">
                            <button type="button" onclick="closeModal()" 
                                    class="flex-1 py-3 px-4 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="flex-1 py-3 px-4 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md flex items-center justify-center gap-2">
                                <i class="fas fa-share"></i>
                                Share Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="toast" class="fixed bottom-4 right-4 z-50 hidden slide-in">
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
        // File Upload Handling (defensive: ensure elements exist)
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const fileListContainer = fileList ? fileList.querySelector('.space-y-2') : null;

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                    if (fileList) fileList.classList.remove('hidden');
                    if (fileListContainer) fileListContainer.innerHTML = '';
                
                Array.from(files).forEach(file => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'flex items-center justify-between p-2 bg-gray-50 rounded-lg';
                    fileItem.innerHTML = `
                        <div class="flex items-center gap-2">
                            <i class="fas fa-file text-gray-400"></i>
                            <span class="text-sm text-gray-700 truncate max-w-xs">${file.name}</span>
                        </div>
                        <span class="text-xs text-gray-500">${formatFileSize(file.size)}</span>
                    `;
                        if (fileListContainer) fileListContainer.appendChild(fileItem);
                });
            } else {
                    if (fileList) fileList.classList.add('hidden');
            }
            });
        }

        // Upload Form Toggle
        function showUpload() {
            const uploadForm = document.getElementById('uploadForm');
            uploadForm.classList.remove('hidden');
            uploadForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function hideUpload() {
            const uploadForm = document.getElementById('uploadForm');
            uploadForm.classList.add('hidden');
            if (fileInput) fileInput.value = '';
            if (fileList) fileList.classList.add('hidden');
        }

        const uploadBtnEl = document.getElementById('uploadBtn');
        const uploadTriggerSidebarEl = document.getElementById('uploadTriggerSidebar');
        const cancelUploadEl = document.getElementById('cancelUpload');
        if (uploadBtnEl) uploadBtnEl.addEventListener('click', showUpload);
        if (uploadTriggerSidebarEl) uploadTriggerSidebarEl.addEventListener('click', (e) => { e.preventDefault(); showUpload(); });
        if (cancelUploadEl) cancelUploadEl.addEventListener('click', hideUpload);

        // File Size Formatter
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Share Modal Functions
        const shareModal = document.getElementById('shareModal');
        const fileToShareInput = document.getElementById('fileToShare');

        function openShareModal(fileId) {
            // Find elements at call time to be robust
            const modalEl = document.getElementById('shareModal');
            const fileToShareEl = document.getElementById('fileToShare');
            const usersEl = document.getElementById('shareUsers');
            const makeLinkEl = document.getElementById('makeLinkCheckbox');

            if (fileToShareEl) fileToShareEl.value = fileId;

            if (!modalEl) {
                alert('Share modal not available.');
                return;
            }

            // Show modal (explicit style + Tailwind hidden removal)
            modalEl.classList.remove('hidden');
            modalEl.classList.add('show');
            modalEl.querySelector('.slide-in').classList.add('show');
            document.body.style.overflow = 'hidden';

            // Reset fields
            if (usersEl) usersEl.value = '';
            if (makeLinkEl) makeLinkEl.checked = false;

            // Focus the users input if present
            setTimeout(() => { if (usersEl) usersEl.focus(); }, 50);
        }

        function closeModal() {
            const modalEl = document.getElementById('shareModal');
            if (modalEl) {
                modalEl.classList.add('hidden');
                modalEl.classList.remove('show');
                modalEl.querySelector('.slide-in').classList.remove('show');
                modalEl.style.display = 'none';
            }
            const usersEl = document.getElementById('shareUsers');
            if (usersEl) usersEl.value = '';
            document.body.style.overflow = 'auto';
        }

        // Share Form Submission
        document.getElementById('shareForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileIdRaw = fileToShareInput ? fileToShareInput.value : '';
            const fileId = parseInt(fileIdRaw, 10) || 0;
            const users = document.getElementById('shareUsers').value.trim();
            const makeLink = document.getElementById('makeLinkCheckbox').checked;
            const submitBtn = this.querySelector('button[type="submit"]');
            
            if (fileId <= 0) {
                showToast('Invalid file selected for sharing', 'error');
                return;
            }

            if (!users && !makeLink) {
                showToast('Please enter at least one username or enable public link', 'error');
                return;
            }
            
            // Loading state
            const originalHTML = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sharing...';
            submitBtn.disabled = true;
            
            // If user only wants a public link (no usernames), use a simple GET endpoint as a reliable fallback.
            if (makeLink && !users) {
                fetch('api/create-share-link.php?file=' + encodeURIComponent(fileId))
                    .then(async (r) => {
                        const text = await r.text();
                        let json = null;
                        try { json = JSON.parse(text); } catch (e) {
                            console.error('Invalid JSON from create-share-link:', text);
                            showToast('Server returned unexpected response. See console.', 'error');
                            throw e;
                        }
                        if (!json.success) {
                            showToast(json.error || 'Failed to create share link', 'error');
                            throw new Error('create-share-link failed');
                        }
                        // Show link dialog
                        setTimeout(() => {
                            const div = document.createElement('div');
                            div.className = 'fixed inset-0 z-50 flex items-center justify-center';
                            div.innerHTML = `
                                <div style="background:#fff;padding:20px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.12);max-width:600px;width:100%;text-align:center;">
                                    <h3 style="margin-bottom:8px">Share Link</h3>
                                    <input id="_share_link_input" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px" value="${json.share_link}" readonly />
                                    <div style="margin-top:12px;display:flex;gap:8px;justify-content:center">
                                        <button id="_copy_link_btn" class="px-4 py-2 bg-indigo-600 text-white rounded">Copy Link</button>
                                        <button id="_close_link_btn" class="px-4 py-2 border rounded">Close</button>
                                    </div>
                                </div>
                            `;
                            document.body.appendChild(div);
                            document.getElementById('_copy_link_btn').addEventListener('click', function() {
                                navigator.clipboard && navigator.clipboard.writeText(json.share_link);
                                this.textContent = 'Copied';
                            });
                            document.getElementById('_close_link_btn').addEventListener('click', function() {
                                div.remove();
                            });
                        }, 200);
                        closeModal();
                    }).catch(err => {
                        console.error('Create share link failed:', err);
                    }).finally(() => {
                        submitBtn.innerHTML = originalHTML;
                        submitBtn.disabled = false;
                    });

                return;
            }

            // Call API
            fetch('api/share-file.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ file_id: fileId, users: users, make_link: makeLink })
            }).then(async (r) => {
                const ct = r.headers.get('content-type') || '';
                const text = await r.text();

                if (!r.ok) {
                    console.error('Share API returned status', r.status, text);
                    showToast('Server error while sharing', 'error');
                    throw new Error('Server error');
                }

                if (ct.indexOf('application/json') === -1) {
                    // Non-JSON response (likely HTML error page) — log for debugging and show friendly message
                    console.error('Non-JSON response from share API:', text);
                    showToast('Server returned unexpected response. Check console for details.', 'error');
                    throw new Error('Invalid JSON response');
                }

                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON from share API:', text, e);
                    showToast('Invalid server response while sharing', 'error');
                    throw e;
                }
            }).then(res => {
                    if (res.success) {
                        if (res.share_link) {
                            showToast('Share link created', 'success');
                            // Show a small copyable dialog
                            setTimeout(() => {
                                const div = document.createElement('div');
                                div.className = 'fixed inset-0 z-50 flex items-center justify-center';
                                div.innerHTML = `
                                    <div style="background:#fff;padding:20px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.12);max-width:600px;width:100%;text-align:center;">
                                        <h3 style="margin-bottom:8px">Share Link</h3>
                                        <input id="_share_link_input" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px" value="${res.share_link}" readonly />
                                        <div style="margin-top:12px;display:flex;gap:8px;justify-content:center">
                                            <button id="_copy_link_btn" class="px-4 py-2 bg-indigo-600 text-white rounded">Copy Link</button>
                                            <button id="_close_link_btn" class="px-4 py-2 border rounded">Close</button>
                                        </div>
                                    </div>
                                `;
                                document.body.appendChild(div);
                                document.getElementById('_copy_link_btn').addEventListener('click', function() {
                                    navigator.clipboard && navigator.clipboard.writeText(res.share_link);
                                    this.textContent = 'Copied';
                                });
                                document.getElementById('_close_link_btn').addEventListener('click', function() {
                                    div.remove();
                                });
                            }, 300);
                        } else {
                            showToast('File shared successfully!', 'success');
                        }
                        closeModal();
                    } else {
                        showToast(res.error || 'Failed to share file', 'error');
                    }
            }).catch(err => {
                console.error('Share request failed:', err);
            }).finally(() => {
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = false;
            });
        });

        // Toast Notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');

            if (!toast || !toastMessage) {
                // Fallback if toast DOM not present
                console.warn('Toast element missing, fallback to alert');
                if (type === 'error') {
                    alert('Error: ' + message);
                } else {
                    alert(message);
                }
                return;
            }

            // Set message and color based on type
            toastMessage.textContent = message;
            const icon = toast.querySelector('.fa-check') || toast.querySelector('i');
            const iconBg = toast.querySelector('.w-10');

            if (type === 'error') {
                if (icon) icon.className = 'fas fa-exclamation-circle text-red-600';
                if (iconBg) iconBg.className = 'w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center';
            } else if (type === 'warning') {
                if (icon) icon.className = 'fas fa-exclamation-triangle text-amber-600';
                if (iconBg) iconBg.className = 'w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center';
            } else {
                if (icon) icon.className = 'fas fa-check text-green-600';
                if (iconBg) iconBg.className = 'w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center';
            }

            toast.classList.remove('hidden');
            toast.classList.add('show');

            // Auto hide after 5 seconds
            setTimeout(hideToast, 5000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('hidden');
            toast.classList.remove('show');
        }

        // Preview File Function
        function previewFile(fileId) {
            showToast('Preview feature coming soon!', 'info');
        }

        // Delegated click handlers for share/preview buttons (reliable when elements are rendered dynamically)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-action]');
            if (!btn) return;
            const action = btn.getAttribute('data-action');
            const fileId = btn.getAttribute('data-file-id');

            if (action === 'share') {
                // open share modal
                if (typeof openShareModal === 'function') openShareModal(fileId);
            } else if (action === 'preview') {
                if (typeof previewFile === 'function') previewFile(fileId);
            }
        });

        // Close modal on Escape key (only if shareModal exists)
        if (shareModal) {
            document.addEventListener('keydown', function(event) {
                if (event.key === "Escape" && !shareModal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            // Click outside modal to close
            shareModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
        }

        // Initialize tooltips
        document.querySelectorAll('.tooltip').forEach(el => {
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

        // Update storage progress animation
        document.addEventListener('DOMContentLoaded', () => {
            const progressBar = document.querySelector('.h-full.gradient-bg');
            if (progressBar) {
                setTimeout(() => {
                    progressBar.style.transition = 'width 1s ease-out';
                }, 100);
            }
        });

        // Handle file drag and drop
        const dropZone = document.querySelector('label[for="fileInput"]');
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('border-primary-400', 'bg-primary-50/30');
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-primary-400', 'bg-primary-50/30');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
        }
    </script>

<?php include 'includes/footer.php'; ?>
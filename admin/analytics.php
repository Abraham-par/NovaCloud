<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

$session = new SessionManager();
$session->requireAdmin();
$functions = new NovaCloudFunctions();
$stats = $functions->getSystemStats();
$storage = $functions->getStorageOverview();
$users = $functions->getAllUsers();
$activities = $functions->getRecentActivities(50);

// Calculate additional statistics
$totalUsers = count($users);
$activeUsers = count(array_filter($users, fn($u) => ($u['account_status'] ?? '') === 'active'));
$inactiveUsers = count(array_filter($users, fn($u) => ($u['account_status'] ?? '') === 'inactive'));
$suspendedUsers = count(array_filter($users, fn($u) => ($u['account_status'] ?? '') === 'suspended'));

// Get daily signups for last 7 days
$signupsLast7Days = [];
$today = date('Y-m-d');
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $count = count(array_filter($users, fn($u) => date('Y-m-d', strtotime($u['created_at'])) === $date));
    $signupsLast7Days[] = [
        'date' => date('M d', strtotime($date)),
        'count' => $count
    ];
}

// Get file type distribution
$fileTypes = [];
if (isset($storage['file_types'])) {
    $fileTypes = $storage['file_types'];
} else {
    // Fallback calculation if not provided by function
    $allFiles = $functions->getAllFiles();
    $tempTypes = [];
    foreach ($allFiles as $file) {
        $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
        $tempTypes[$ext] = ($tempTypes[$ext] ?? 0) + 1;
    }
    arsort($tempTypes);
    $fileTypes = array_slice($tempTypes, 0, 6);
}

// Get storage usage trend
$storageTrend = [];
$totalStorage = 0;
if (is_array($storage)) {
    if (isset($storage['total_size'])) {
        $totalStorage = (int)$storage['total_size'];
    }
}

// Get user activity for last 24 hours
$recentLogins = array_filter($activities, fn($a) => 
    stripos($a['description'], 'login') !== false || 
    stripos($a['description'], 'logged in') !== false
);
$recentLogins = array_slice($recentLogins, 0, 5);

// Get top users by storage
$topUsersByStorage = [];
foreach ($users as $user) {
    $topUsersByStorage[] = [
        'username' => $user['username'],
        'storage' => $user['storage_used'] ?? 0,
        'files' => $user['total_files'] ?? 0
    ];
}
usort($topUsersByStorage, fn($a, $b) => $b['storage'] - $a['storage']);
$topUsersByStorage = array_slice($topUsersByStorage, 0, 5);

// Helper function for formatting bytes
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < 4) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - NovaCloud Admin</title>
    
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
                        'progress': 'progress 1.5s ease-in-out',
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
                        'progress': {
                            '0%': { width: '0%' },
                            '100%': { width: 'var(--target-width)' }
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
    
    <!-- Chart.js for graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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

        .gradient-gold {
            background: var(--gold-gradient);
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

        /* Progress bars */
        .progress-bar {
            height: 6px;
            background: #ffedd5;
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
            position: absolute;
            left: 0;
            top: 0;
            background: var(--fire-gradient);
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
                    
                    <a href="analytics.php" class="flex items-center gap-4 p-3 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 text-amber-700 border border-amber-200 shadow-sm">
                        <i class="fas fa-chart-bar text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Analytics</span>
                    </a>
                    
                    <a href="settings.php" class="flex items-center gap-4 p-3 rounded-xl text-gray-600 hover:bg-amber-50 hover:text-amber-700 transition-all">
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
                            <span>Analytics</span>
                        </span>
                        <span class="gradient-text ml-2">Insights</span>
                    </h1>
                    <p class="text-sm text-amber-600 mt-1">Monitor system performance with fiery precision</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Date Range Selector -->
                    <div class="relative">
                        <select id="dateRange" class="w-48 pl-4 pr-10 py-3 rounded-2xl border-0 bg-amber-50 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all text-sm outline-none shadow-sm appearance-none text-gray-800">
                            <option value="7">Last 7 days</option>
                            <option value="30">Last 30 days</option>
                            <option value="90">Last 90 days</option>
                            <option value="365">Last year</option>
                        </select>
                        <i class="fas fa-calendar-alt absolute right-4 top-3.5 text-amber-500 pointer-events-none"></i>
                    </div>

                    <!-- Export Button -->
                    <button onclick="exportAnalytics()" class="px-4 py-3 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md fire-glow hover:animate-pulse-glow flex items-center gap-2">
                        <i class="fas fa-fire"></i>
                        Export Report
                    </button>

                    <!-- Refresh Button -->
                    <button onclick="refreshAnalytics()" class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center hover:bg-amber-100 transition-colors border border-amber-200">
                        <i class="fas fa-redo text-amber-600"></i>
                    </button>
                </div>
            </header>

            <!-- Content Area with Fire Pattern -->
            <div class="flex-1 overflow-y-auto bg-grid-pattern p-6 lg:p-10">
                <!-- Key Metrics Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Users Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-amber-600 mb-1">Total Users</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $totalUsers; ?></h3>
                                <p class="text-xs text-amber-500 mt-1 flex items-center gap-1">
                                    <i class="fas fa-fire"></i> 
                                    <?php 
                                        $yesterday = count(array_filter($users, fn($u) => date('Y-m-d', strtotime($u['created_at'])) === date('Y-m-d', strtotime('-1 day'))));
                                        echo $yesterday > 0 ? "+{$yesterday} yesterday" : "No new signups yesterday";
                                    ?>
                                </p>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl border border-amber-200">
                                <i class="fas fa-users text-xl text-amber-500"></i>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1">
                                <div class="w-2 h-2 rounded-full gradient-bg"></div>
                                <span class="text-amber-600"><?php echo $activeUsers; ?> active</span>
                            </div>
                            <div class="text-amber-600"><?php echo round(($activeUsers / max($totalUsers, 1)) * 100); ?>%</div>
                        </div>
                    </div>

                    <!-- Total Files Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-amber-600 mb-1">Total Files</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo (int)($stats['files'] ?? 0); ?></h3>
                                <p class="text-xs text-amber-600 mt-1">
                                    <?php 
                                        $avgFiles = $totalUsers > 0 ? round((int)($stats['files'] ?? 0) / $totalUsers, 1) : 0;
                                        echo "<i class='fas fa-fire mr-1'></i> Avg {$avgFiles} files/user";
                                    ?>
                                </p>
                            </div>
                            <div class="relative">
                                <div class="p-3 bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl border border-emerald-200">
                                    <i class="fas fa-file-alt text-xl text-emerald-500"></i>
                                </div>
                                <div class="absolute -top-2 -right-2 w-6 h-6 gradient-bg rounded-full flex items-center justify-center fire-glow">
                                    <i class="fas fa-plus text-white text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="h-1 bg-amber-100 rounded-full overflow-hidden">
                                <div class="h-full gradient-bg rounded-full" style="width: <?php echo min(100, ((int)($stats['files'] ?? 0) / 10000) * 100); ?>%"></div>
                            </div>
                            <span class="text-xs text-amber-600">of 10,000 file capacity</span>
                        </div>
                    </div>

                    <!-- Storage Usage Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-amber-600 mb-1">Storage Used</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo formatBytes($totalStorage); ?></h3>
                                <p class="text-xs text-amber-600 mt-1">of 500GB total</p>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl border border-orange-200">
                                <i class="fas fa-hdd text-xl text-amber-500"></i>
                            </div>
                        </div>
                        <div class="relative pt-2">
                            <div class="h-2 bg-amber-100 rounded-full overflow-hidden">
                                <div class="h-full gradient-bg rounded-full" style="width: <?php echo min(100, ($totalStorage / (500 * 1024 * 1024 * 1024)) * 100); ?>%"></div>
                            </div>
                            <span class="text-xs text-amber-600 absolute right-0 -top-5"><?php echo number_format(min(100, ($totalStorage / (500 * 1024 * 1024 * 1024)) * 100), 1); ?>%</span>
                        </div>
                    </div>

                    <!-- Active Countries Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-amber-600 mb-1">Active Countries</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo (int)($stats['countries'] ?? 0); ?></h3>
                                <p class="text-xs text-amber-600 mt-1">
                                    <i class="fas fa-fire mr-1"></i> Global reach
                                </p>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border border-purple-200">
                                <i class="fas fa-globe-americas text-xl text-purple-500"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2 text-xs text-amber-600">
                            <i class="fas fa-fire"></i>
                            <span>Worldwide distribution</span>
                        </div>
                    </div>
                </div>

                <!-- Charts and Analytics Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Signups Chart -->
                    <div class="glass-card rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">User Signups</h2>
                                <p class="text-sm text-amber-600">Daily new users (last 7 days)</p>
                            </div>
                            <button onclick="toggleChartType('signupsChart')" class="px-3 py-1 bg-amber-50 text-amber-700 rounded-lg text-sm hover:bg-amber-100 transition-colors border border-amber-200">
                                <i class="fas fa-fire mr-1"></i> Toggle
                            </button>
                        </div>
                        <div class="h-64">
                            <canvas id="signupsChart"></canvas>
                        </div>
                    </div>

                    <!-- User Status Distribution -->
                    <div class="glass-card rounded-2xl p-6">
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-gray-800">User Status Distribution</h2>
                            <p class="text-sm text-amber-600">Current account status breakdown</p>
                        </div>
                        <div class="h-64">
                            <canvas id="userStatusChart"></canvas>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            <div class="text-center p-2 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg border border-emerald-200">
                                <div class="text-lg font-bold text-emerald-700"><?php echo $activeUsers; ?></div>
                                <div class="text-xs text-emerald-600">Active</div>
                            </div>
                            <div class="text-center p-2 bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg border border-amber-200">
                                <div class="text-lg font-bold text-amber-700"><?php echo $inactiveUsers; ?></div>
                                <div class="text-xs text-amber-600">Inactive</div>
                            </div>
                            <div class="text-center p-2 bg-gradient-to-br from-red-50 to-red-100 rounded-lg border border-red-200">
                                <div class="text-lg font-bold text-red-700"><?php echo $suspendedUsers; ?></div>
                                <div class="text-xs text-red-600">Suspended</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Analytics Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- File Type Distribution -->
                    <div class="glass-card rounded-2xl p-6">
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-gray-800">File Types</h2>
                            <p class="text-sm text-amber-600">Most common file extensions</p>
                        </div>
                        <div class="space-y-4">
                            <?php 
                            $totalFiles = (int)($stats['files'] ?? 0);
                            foreach ($fileTypes as $type => $count): 
                                $percentage = $totalFiles > 0 ? ($count / $totalFiles) * 100 : 0;
                            ?>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-fire mr-2 text-amber-500"></i>
                                        <?php echo strtoupper($type); ?>
                                    </span>
                                    <span class="text-sm text-amber-600"><?php echo $count; ?> (<?php echo round($percentage, 1); ?>%)</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Recent Logins -->
                    <div class="glass-card rounded-2xl p-6">
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-gray-800">Recent Logins</h2>
                            <p class="text-sm text-amber-600">Last 5 user login activities</p>
                        </div>
                        <div class="space-y-3">
                            <?php foreach ($recentLogins as $login): ?>
                            <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-emerald-50/50 to-emerald-50/30 rounded-xl border border-emerald-200">
                                <div class="w-8 h-8 rounded-full gradient-bg flex items-center justify-center fire-glow">
                                    <i class="fas fa-fire text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-800 truncate"><?php echo htmlspecialchars($login['description']); ?></p>
                                    <p class="text-xs text-amber-600"><?php echo $login['time']; ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($recentLogins)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-fire text-3xl text-amber-300 mb-2"></i>
                                <p class="text-amber-600">No recent logins</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Top Users by Storage -->
                    <div class="glass-card rounded-2xl p-6">
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-gray-800">Top Storage Users</h2>
                            <p class="text-sm text-amber-600">Users consuming most storage</p>
                        </div>
                        <div class="space-y-4">
                            <?php foreach ($topUsersByStorage as $index => $user): 
                                $percentage = $totalStorage > 0 ? ($user['storage'] / $totalStorage) * 100 : 0;
                            ?>
                            <div class="flex items-center justify-between p-3 bg-amber-50/50 rounded-xl hover:bg-amber-50 transition-colors border border-amber-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full gradient-bg flex items-center justify-center text-white font-bold text-sm">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 truncate max-w-[120px]"><?php echo htmlspecialchars($user['username']); ?></p>
                                        <p class="text-xs text-amber-600"><?php echo $user['files']; ?> files</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-800"><?php echo formatBytes($user['storage']); ?></p>
                                    <p class="text-xs text-amber-600"><?php echo round($percentage, 1); ?>%</p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($topUsersByStorage)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-fire text-3xl text-amber-300 mb-2"></i>
                                <p class="text-amber-600">No user data available</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- System Performance Section -->
                <div class="mt-8 glass-card rounded-2xl p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-800">System Performance</h2>
                        <p class="text-sm text-amber-600">Storage and file analytics</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Largest Files -->
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-4">
                                <i class="fas fa-fire mr-2 text-amber-500"></i>Largest Files
                            </h3>
                            <div class="space-y-3">
                                <?php foreach (($storage['largest_files'] ?? []) as $index => $file): ?>
                                <div class="flex items-center justify-between p-3 bg-amber-50/50 rounded-xl hover:bg-amber-50 transition-colors border border-amber-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg gradient-bg flex items-center justify-center fire-glow">
                                            <i class="fas fa-fire text-white"></i>
                                        </div>
                                        <div class="max-w-[200px]">
                                            <p class="text-sm font-medium text-gray-800 truncate" title="<?php echo htmlspecialchars($file['original_name'] ?? $file['filename'] ?? 'file'); ?>">
                                                <?php echo htmlspecialchars($file['original_name'] ?? $file['filename'] ?? 'file'); ?>
                                            </p>
                                            <p class="text-xs text-amber-600">by <?php echo htmlspecialchars($file['username'] ?? 'Unknown'); ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-gray-800"><?php echo formatBytes((int)($file['file_size'] ?? 0)); ?></p>
                                        <p class="text-xs text-amber-600"><?php echo date('M d', strtotime($file['uploaded_at'] ?? 'now')); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($storage['largest_files'] ?? [])): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-fire text-3xl text-amber-300 mb-2"></i>
                                    <p class="text-amber-600">No file data available</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Storage Overview -->
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-4">
                                <i class="fas fa-fire mr-2 text-amber-500"></i>Storage Overview
                            </h3>
                            <div class="space-y-4">
                                <?php if (isset($storage['by_type']) && is_array($storage['by_type'])): ?>
                                    <?php foreach ($storage['by_type'] as $type => $typeData): 
                                        $typePercentage = $totalStorage > 0 ? ($typeData['size'] / $totalStorage) * 100 : 0;
                                    ?>
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700">
                                                <?php echo ucfirst($type); ?> files
                                            </span>
                                            <span class="text-sm text-amber-600">
                                                <?php echo formatBytes($typeData['size']); ?> (<?php echo round($typePercentage, 1); ?>%)
                                            </span>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $typePercentage; ?>%"></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-8">
                                        <i class="fas fa-fire text-3xl text-amber-300 mb-2"></i>
                                        <p class="text-amber-600">No storage data available</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="analyticsToast" class="fixed bottom-4 right-4 z-50"></div>

    <script>
        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Signups Chart with fire gradient
            const signupsCtx = document.getElementById('signupsChart').getContext('2d');
            
            // Create fire gradient for line chart
            const fireGradient = signupsCtx.createLinearGradient(0, 0, 0, 400);
            fireGradient.addColorStop(0, 'rgba(245, 158, 11, 0.8)');
            fireGradient.addColorStop(0.5, 'rgba(234, 88, 12, 0.6)');
            fireGradient.addColorStop(1, 'rgba(220, 38, 38, 0.4)');
            
            const fireGradientFill = signupsCtx.createLinearGradient(0, 0, 0, 400);
            fireGradientFill.addColorStop(0, 'rgba(245, 158, 11, 0.2)');
            fireGradientFill.addColorStop(1, 'rgba(220, 38, 38, 0.1)');
            
            const signupsChart = new Chart(signupsCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($signupsLast7Days, 'date')); ?>,
                    datasets: [{
                        label: 'New Users',
                        data: <?php echo json_encode(array_column($signupsLast7Days, 'count')); ?>,
                        borderColor: fireGradient,
                        backgroundColor: fireGradientFill,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(245, 158, 11, 0.1)'
                            },
                            ticks: {
                                color: '#78350f',
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(245, 158, 11, 0.1)'
                            },
                            ticks: {
                                color: '#78350f'
                            }
                        }
                    }
                }
            });

            // User Status Chart with fire colors
            const statusCtx = document.getElementById('userStatusChart').getContext('2d');
            const userStatusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Inactive', 'Suspended'],
                    datasets: [{
                        data: [<?php echo $activeUsers; ?>, <?php echo $inactiveUsers; ?>, <?php echo $suspendedUsers; ?>],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 10,
                        hoverBackgroundColor: [
                            '#10b981',
                            '#f59e0b',
                            '#ef4444'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 11
                                },
                                color: '#78350f'
                            }
                        }
                    },
                    cutout: '65%'
                }
            });

            // Store chart instances for toggling
            window.charts = {
                signups: signupsChart,
                userStatus: userStatusChart
            };

            // Animate progress bars
            document.querySelectorAll('.progress-fill').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.transition = 'width 1s ease-out';
                    bar.style.width = width;
                }, 100);
            });
        });

        // Toggle chart type
        function toggleChartType(chartId) {
            const chartMap = {
                'signupsChart': window.charts.signups
            };
            
            const chart = chartMap[chartId];
            if (chart) {
                chart.config.type = chart.config.type === 'line' ? 'bar' : 'line';
                chart.update();
                showToast('Chart view toggled!', 'success');
            }
        }

        // Export analytics
        function exportAnalytics() {
            const data = {
                timestamp: new Date().toISOString(),
                totalUsers: <?php echo $totalUsers; ?>,
                activeUsers: <?php echo $activeUsers; ?>,
                totalFiles: <?php echo (int)($stats['files'] ?? 0); ?>,
                storageUsed: <?php echo $totalStorage; ?>,
                countries: <?php echo (int)($stats['countries'] ?? 0); ?>,
                signupsLast7Days: <?php echo json_encode($signupsLast7Days); ?>
            };
            
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `fire-analytics-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            showToast('Fire Analytics exported successfully!', 'success');
        }

        // Refresh analytics
        function refreshAnalytics() {
            const refreshBtn = document.querySelector('.fa-redo');
            refreshBtn.classList.add('fa-spin');
            showToast('Igniting fresh analytics data...', 'info');
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // Date range change
        document.getElementById('dateRange').addEventListener('change', function() {
            showToast(`Loading fire data for last ${this.value} days...`, 'info');
            // In a real implementation, you would fetch new data based on date range
            setTimeout(() => {
                const url = new URL(window.location);
                url.searchParams.set('days', this.value);
                window.location.href = url.toString();
            }, 500);
        });

        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('analyticsToast');
            
            const bgColor = type === 'error' ? 'gradient-bg' : 
                           type === 'warning' ? 'gradient-bg' : 
                           'gradient-bg';
            
            const icon = type === 'error' ? 'exclamation-circle' : 
                        type === 'warning' ? 'exclamation-triangle' : 
                        'fire';
            
            toast.innerHTML = `
                <div class="glass-card rounded-xl shadow-xl border border-amber-200 p-4 max-w-sm animate-slide-in">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl ${bgColor} flex items-center justify-center fire-glow">
                            <i class="fas fa-${icon} text-white"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">${message}</p>
                            <p class="text-xs text-amber-600 mt-1">Just now</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-amber-500 hover:text-amber-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            setTimeout(() => {
                if (toast && toast.firstChild) {
                    toast.firstChild.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                    setTimeout(() => {
                        if (toast.firstChild) {
                            toast.removeChild(toast.firstChild);
                        }
                    }, 300);
                }
            }, 5000);
        }

        // Add hover effects to interactive elements
        document.querySelectorAll('.hover-glow').forEach(el => {
            el.addEventListener('mouseenter', () => {
                el.classList.add('animate-pulse-glow');
            });
            el.addEventListener('mouseleave', () => {
                el.classList.remove('animate-pulse-glow');
            });
        });
    </script>
</body>
</html>
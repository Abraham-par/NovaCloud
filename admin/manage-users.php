<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

$session = new SessionManager();
$session->requireAdmin();
$functions = new NovaCloudFunctions();
$users = $functions->getAllUsers();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - NovaCloud Admin</title>
    
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

        /* Status Badges */
        .badge-active {
            background: linear-gradient(135deg, rgba(21, 128, 61, 0.2) 0%, rgba(21, 128, 61, 0.1) 100%);
            color: #065f46;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        .badge-inactive {
            background: rgba(229, 231, 235, 0.7);
            color: #374151;
            border: 1px solid rgba(209, 213, 219, 0.5);
        }
        .badge-suspended {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.2) 0%, rgba(153, 27, 27, 0.1) 100%);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.3);
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

        /* Premium Table Styling */
        .premium-table th {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(234, 88, 12, 0.05) 100%);
            border-bottom: 2px solid rgba(245, 158, 11, 0.3);
        }

        .premium-table tr:hover {
            background: rgba(245, 158, 11, 0.05);
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
                    
                    <a href="manage-users.php" class="flex items-center gap-4 p-3 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 text-amber-700 border border-amber-200 shadow-sm">
                        <i class="fas fa-users text-lg w-6"></i>
                        <span class="hidden lg:block group-hover:block font-medium">Users</span>
                    </a>
                    
                    <a href="analytics.php" class="flex items-center gap-4 p-3 rounded-xl text-gray-600 hover:bg-amber-50 hover:text-amber-700 transition-all">
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
                            <span>Users</span>
                        </span>
                        <span class="gradient-text ml-2">Management</span>
                    </h1>
                    <p class="text-sm text-amber-600 mt-1">Manage user accounts with fiery precision</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <div class="relative">
                        <input type="text" id="userSearch" 
                               placeholder="Search users..." 
                               class="w-72 pl-12 pr-4 py-3 rounded-2xl border-0 bg-amber-50 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all text-sm outline-none shadow-sm text-gray-800 placeholder-amber-500/70">
                        <i class="fas fa-search absolute left-4 top-3.5 text-amber-500"></i>
                    </div>

                    <!-- Export Button -->
                    <form method="post" action="dashboard.php" class="inline-block">
                        <input type="hidden" name="action" value="export_users">
                        <button type="submit" class="px-4 py-3 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md fire-glow hover:animate-pulse-glow flex items-center gap-2">
                            <i class="fas fa-download"></i>
                            Export CSV
                        </button>
                    </form>

                    <!-- Add User Button -->
                    <button onclick="showAddUserModal()" class="px-4 py-3 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md fire-glow hover:animate-pulse-glow flex items-center gap-2">
                        <i class="fas fa-user-plus"></i>
                        Add User
                    </button>
                </div>
            </header>

            <!-- Content Area with Fire Pattern -->
            <div class="flex-1 overflow-y-auto bg-grid-pattern p-6 lg:p-10">
                <!-- Stats Summary -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="glass-card rounded-2xl p-4 flex items-center gap-4 hover-lift">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center border border-amber-200">
                            <i class="fas fa-users text-amber-500 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-amber-600">Total Users</p>
                            <p class="text-xl font-bold text-gray-800"><?php echo count($users); ?></p>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-2xl p-4 flex items-center gap-4 hover-lift">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center border border-emerald-200">
                            <i class="fas fa-user-check text-emerald-500 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-amber-600">Active</p>
                            <p class="text-xl font-bold text-gray-800">
                                <?php echo count(array_filter($users, fn($u) => ($u['account_status'] ?? '') === 'active')); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-2xl p-4 flex items-center gap-4 hover-lift">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center border border-amber-200">
                            <i class="fas fa-user-slash text-amber-500 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-amber-600">Inactive</p>
                            <p class="text-xl font-bold text-gray-800">
                                <?php echo count(array_filter($users, fn($u) => ($u['account_status'] ?? '') === 'inactive')); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-2xl p-4 flex items-center gap-4 hover-lift">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center border border-red-200">
                            <i class="fas fa-user-times text-red-500 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-amber-600">Suspended</p>
                            <p class="text-xl font-bold text-gray-800">
                                <?php echo count(array_filter($users, fn($u) => ($u['account_status'] ?? '') === 'suspended')); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="glass-card rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-amber-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">User Accounts</h2>
                                <p class="text-sm text-amber-600">Click on any user to view detailed information</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button onclick="bulkAction('activate_all')" class="px-3 py-2 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium hover:bg-emerald-100 transition-colors border border-emerald-200">
                                    <i class="fas fa-fire mr-1"></i> Activate All
                                </button>
                                <button onclick="bulkAction('deactivate_all')" class="px-3 py-2 bg-amber-50 text-amber-700 rounded-xl text-sm font-medium hover:bg-amber-100 transition-colors border border-amber-200">
                                    <i class="fas fa-pause mr-1"></i> Deactivate All
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full premium-table">
                            <thead>
                                <tr class="border-b border-amber-100">
                                    <th class="py-4 px-6 text-left">
                                        <input type="checkbox" id="selectAll" class="rounded bg-amber-50 border-amber-300 text-amber-500">
                                    </th>
                                    <th class="py-4 px-6 text-left text-sm font-medium text-amber-600">User</th>
                                    <th class="py-4 px-6 text-left text-sm font-medium text-amber-600">Email</th>
                                    <th class="py-4 px-6 text-left text-sm font-medium text-amber-600">Status</th>
                                    <th class="py-4 px-6 text-left text-sm font-medium text-amber-600">Last Seen</th>
                                    <th class="py-4 px-6 text-left text-sm font-medium text-amber-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): 
                                    $last = $user['last_login'] ?? null;
                                    $lastText = '—';
                                    if (!empty($last)) {
                                        $ts = is_numeric($last) ? (int)$last : strtotime($last);
                                        if ($ts) $lastText = date('Y-m-d H:i', $ts);
                                        else $lastText = htmlspecialchars((string)$last);
                                    }
                                    
                                    $statusClass = '';
                                    $status = $user['account_status'] ?? 'unknown';
                                    if ($status === 'active') $statusClass = 'badge-active';
                                    elseif ($status === 'suspended') $statusClass = 'badge-suspended';
                                    else $statusClass = 'badge-inactive';
                                ?>
                                <tr class="border-b border-amber-50 hover:bg-amber-50/50 transition-colors cursor-pointer user-row" 
                                    data-user-id="<?php echo (int)$user['id']; ?>">
                                    <td class="py-4 px-6">
                                        <input type="checkbox" class="user-checkbox rounded bg-amber-50 border-amber-300 text-amber-500" value="<?php echo (int)$user['id']; ?>">
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full gradient-bg flex items-center justify-center text-white font-bold shadow-md">
                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($user['username']); ?></p>
                                                <p class="text-xs text-amber-600">ID: <?php echo (int)$user['id']; ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="text-gray-800"><?php echo htmlspecialchars($user['email']); ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                            <i class="fas fa-<?php echo $status === 'active' ? 'check-circle' : ($status === 'suspended' ? 'ban' : 'pause-circle'); ?> mr-1"></i>
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="text-gray-800"><?php echo $lastText; ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <form method="post" action="dashboard.php" class="inline-block" onsubmit="return confirm('Ignite user status change?')">
                                                <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-100 transition-colors border border-amber-300 hover:animate-pulse-glow" title="Toggle Status">
                                                    <i class="fas fa-fire text-sm"></i>
                                                </button>
                                            </form>
                                            <form method="post" action="dashboard.php" class="inline-block" onsubmit="return confirm('Suspend this user?')">
                                                <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                                <input type="hidden" name="action" value="suspend">
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors border border-red-300" title="Suspend">
                                                    <i class="fas fa-ban text-sm"></i>
                                                </button>
                                            </form>
                                            <button onclick="showMessageModal(<?php echo (int)$user['id']; ?>)" 
                                                    class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center hover:bg-orange-100 transition-colors border border-orange-300" title="Send Message">
                                                <i class="fas fa-envelope text-sm"></i>
                                            </button>
                                            <button onclick="impersonateUser(<?php echo (int)$user['id']; ?>)" 
                                                    class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors border border-emerald-300 hover:animate-pulse-glow" title="Impersonate">
                                                <i class="fas fa-user-secret text-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-amber-100">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-amber-600">Showing <?php echo count($users); ?> user accounts</p>
                            <div class="flex items-center gap-2">
                                <button class="px-3 py-2 bg-amber-50 text-amber-700 rounded-xl text-sm font-medium hover:bg-amber-100 transition-colors border border-amber-300">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="px-3 py-2 bg-gradient-to-br from-amber-50 to-orange-50 text-amber-700 rounded-xl text-sm font-medium border border-amber-300">1</button>
                                <button class="px-3 py-2 bg-amber-50 text-amber-700 rounded-xl text-sm font-medium hover:bg-amber-100 transition-colors border border-amber-300">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- User Details Modal -->
    <div id="userModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity animate-fade-in" onclick="closeUserModal()"></div>

        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative glass-card rounded-2xl shadow-2xl max-w-4xl w-full p-6 overflow-hidden transform transition-all animate-slide-in">
                <!-- Modal Decoration -->
                <div class="absolute top-0 right-0 w-32 h-32 gradient-bg rounded-full -translate-y-16 translate-x-16 opacity-10 fire-flicker"></div>
                
                <div class="relative">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900" id="modalUsername">User Details</h2>
                            <p class="text-sm text-amber-600">Detailed user information and activity</p>
                        </div>
                        <button onclick="closeUserModal()" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors border border-amber-300 flex items-center justify-center">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div id="modalBody" class="max-h-[60vh] overflow-y-auto pr-2">
                        <div class="text-center py-8">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center mx-auto mb-4 border border-amber-200">
                                <i class="fas fa-spinner fa-spin text-amber-500 text-2xl"></i>
                            </div>
                            <p class="text-amber-600">Loading user details...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity animate-fade-in" onclick="closeMessageModal()"></div>

        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 overflow-hidden transform transition-all animate-slide-in">
                <div class="relative">
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 mb-4">
                            <i class="fas fa-fire text-amber-500 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Send Message</h3>
                        <p class="text-sm text-amber-600">Send a fiery message to this user</p>
                    </div>

                    <form id="messageForm">
                        <input type="hidden" id="targetUserId">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-amber-600 mb-2">Subject</label>
                            <input type="text" name="subject" required 
                                   class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800 placeholder-amber-500/70"
                                   placeholder="Enter subject">
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-amber-600 mb-2">Message</label>
                            <textarea name="message" rows="4" required 
                                      class="w-full px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all text-gray-800 placeholder-amber-500/70 resize-none"
                                      placeholder="Enter your fiery message..."></textarea>
                        </div>
                        
                        <div class="flex gap-3">
                            <button type="button" onclick="closeMessageModal()" 
                                    class="flex-1 py-3 px-4 border border-amber-200 rounded-xl text-sm font-medium text-amber-700 bg-white hover:bg-amber-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="flex-1 py-3 px-4 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-lg fire-glow hover:animate-pulse-glow flex items-center justify-center gap-2">
                                <i class="fas fa-fire"></i>
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
        // Admin functionality
        const Admin = {
            async performAction(action, data = {}) {
                const form = new FormData();
                form.append('action', action);
                form.append('ajax', '1');
                
                for (const [key, value] of Object.entries(data)) {
                    form.append(key, value);
                }
                
                try {
                    const response = await fetch('dashboard.php', {
                        method: 'POST',
                        body: form
                    });
                    return await response.json();
                } catch (error) {
                    return { success: false, message: 'Network error' };
                }
            },
            
            showToast(message, type = 'success') {
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
            },
            
            confirmAction(message) {
                return confirm(message);
            }
        };
        
        let currentMessageUserId = null;
        
        async function fetchUserDetails(id) {
            try {
                const res = await fetch('api/user-details.php?user_id=' + encodeURIComponent(id));
                return await res.json();
            } catch (error) {
                return { success: false, message: 'Failed to load user details' };
            }
        }
        
        function openUserModal(content, username = 'User Details') {
            document.getElementById('modalUsername').textContent = username;
            document.getElementById('modalBody').innerHTML = content;
            document.getElementById('userModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function showMessageModal(userId) {
            currentMessageUserId = userId;
            document.getElementById('targetUserId').value = userId;
            document.getElementById('messageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMessageModal() {
            document.getElementById('messageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('messageForm').reset();
        }
        
        async function sendMessage(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-fire fa-spin"></i> Igniting...';
            submitBtn.disabled = true;
            
            const result = await Admin.performAction('send_message', {
                user_id: currentMessageUserId,
                subject: formData.get('subject'),
                message: formData.get('message')
            });
            
            if (result.success) {
                Admin.showToast('Message fired successfully!');
                closeMessageModal();
            } else {
                Admin.showToast(result.message, 'error');
            }
            
            submitBtn.innerHTML = originalHTML;
            submitBtn.disabled = false;
        }
        
        async function impersonateUser(userId) {
            if (!Admin.confirmAction('Impersonate this user? You will be logged in as them.')) return;
            
            const result = await Admin.performAction('impersonate', { user_id: userId });
            if (result.success) {
                window.location.href = '../dashboard.php';
            }
        }
        
        async function bulkAction(action) {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            const userIds = Array.from(checkboxes).map(cb => cb.value);
            
            if (userIds.length === 0) {
                Admin.showToast('Please select users first', 'warning');
                return;
            }
            
            if (!Admin.confirmAction(`${action === 'activate_all' ? 'Ignite' : 'Cool down'} ${userIds.length} users?`)) return;
            
            const result = await Admin.performAction('bulk_action', {
                bulk_action: action,
                user_ids: userIds
            });
            
            Admin.showToast(result.message, result.success ? 'success' : 'error');
            
            if (result.success) {
                setTimeout(() => location.reload(), 1000);
            }
        }
        
        function showAddUserModal() {
            Admin.showToast('Fire User Creation feature coming soon!', 'info');
        }
        
        function hideToast() {
            document.getElementById('toast').classList.add('hidden');
        }
        
        // User row click handler
        document.querySelectorAll('.user-row').forEach(row => {
            row.addEventListener('click', async (e) => {
                // Don't trigger if clicking on checkbox or action buttons
                if (e.target.closest('input') || e.target.closest('button') || e.target.closest('form')) {
                    return;
                }
                
                const id = row.getAttribute('data-user-id');
                const username = row.querySelector('.font-medium').textContent;
                
                openUserModal(`
                    <div class="text-center py-8">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center mx-auto mb-4 border border-amber-200">
                            <i class="fas fa-spinner fa-spin text-amber-500 text-2xl"></i>
                        </div>
                        <p class="text-amber-600">Loading user details...</p>
                    </div>
                `, username);
                
                try {
                    const data = await fetchUserDetails(id);
                    if (!data.success) {
                        openUserModal(`
                            <div class="text-center py-8">
                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center mx-auto mb-4 border border-red-200">
                                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                                </div>
                                <p class="text-red-600">${data.message || 'Failed to load user details'}</p>
                            </div>
                        `, username);
                        return;
                    }
                    
                    const u = data.user;
                    let html = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-amber-50 rounded-xl p-4 border border-amber-200">
                                <h4 class="font-medium text-gray-700 mb-2">Basic Information</h4>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-amber-600">Username:</span>
                                        <p class="font-medium">${u.username || '—'}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-amber-600">Email:</span>
                                        <p class="font-medium">${u.email || '—'}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-amber-600">Full Name:</span>
                                        <p class="font-medium">${u.full_name || '—'}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-amber-50 rounded-xl p-4 border border-amber-200">
                                <h4 class="font-medium text-gray-700 mb-2">Account Details</h4>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-amber-600">Status:</span>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium ${u.account_status === 'active' ? 'badge-active' : 
                                            (u.account_status === 'suspended' ? 'badge-suspended' : 'badge-inactive')}">
                                            <i class="fas fa-${u.account_status === 'active' ? 'check-circle' : (u.account_status === 'suspended' ? 'ban' : 'pause-circle')} mr-1"></i>
                                            ${u.account_status ? u.account_status.charAt(0).toUpperCase() + u.account_status.slice(1) : '—'}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-sm text-amber-600">Created:</span>
                                        <p class="font-medium">${u.created_at || '—'}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-amber-600">Last Login:</span>
                                        <p class="font-medium">${u.last_login || '—'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    if (u.last_login_ip) {
                        html += `
                            <div class="bg-amber-50 rounded-xl p-4 mb-6 border border-amber-200">
                                <h4 class="font-medium text-gray-700 mb-2">Security Information</h4>
                                <div>
                                    <span class="text-sm text-amber-600">Last Login IP:</span>
                                    <p class="font-medium font-mono">${u.last_login_ip}</p>
                                </div>
                            </div>
                        `;
                    }
                    
                    html += `<h4 class="font-medium text-gray-700 mb-3">Recent Activity</h4>`;
                    
                    if (data.activities && data.activities.length) {
                        html += '<div class="space-y-2 max-h-60 overflow-y-auto pr-2">';
                        data.activities.slice(0, 10).forEach(a => {
                            const t = a.created_at ? a.created_at : '';
                            if (a.type === 'login') {
                                html += `
                                    <div class="p-3 bg-gradient-to-r from-emerald-50 to-emerald-50/50 rounded-xl border border-emerald-200">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-fire text-emerald-500"></i>
                                            <span class="font-medium">Login</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">From ${a.ip || 'unknown IP'}</p>
                                        <p class="text-xs text-amber-600 mt-1">${t}</p>
                                    </div>
                                `;
                            } else {
                                html += `
                                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-fire text-amber-500"></i>
                                            <span class="font-medium">${a.action || 'Activity'}</span>
                                        </div>
                                        <p class="text-xs text-amber-600 mt-1">${t}</p>
                                    </div>
                                `;
                            }
                        });
                        html += '</div>';
                    } else {
                        html += '<div class="text-center py-8 bg-amber-50 rounded-xl border border-amber-200">';
                        html += '<i class="fas fa-fire text-3xl text-amber-300 mb-3"></i>';
                        html += '<p class="text-amber-600">No recent activity found.</p>';
                        html += '</div>';
                    }
                    
                    openUserModal(html, username);
                } catch (err) {
                    console.error('Error:', err);
                    openUserModal(`
                        <div class="text-center py-8">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center mx-auto mb-4 border border-red-200">
                                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                            </div>
                            <p class="text-red-600">Error fetching user details</p>
                        </div>
                    `, username);
                }
            });
        });
        
        // Select all checkbox
        document.getElementById('selectAll')?.addEventListener('change', function(e) {
            document.querySelectorAll('.user-checkbox').forEach(cb => {
                cb.checked = e.target.checked;
            });
        });
        
        // Search functionality
        document.getElementById('userSearch')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.user-row');
            
            rows.forEach(row => {
                const username = row.querySelector('.font-medium').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(3) p').textContent.toLowerCase();
                
                if (username.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Form submission
        document.getElementById('messageForm')?.addEventListener('submit', sendMessage);
        
        // Close modals on Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeUserModal();
                closeMessageModal();
            }
        });
        
        // Close modals on overlay click
        document.getElementById('userModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserModal();
            }
        });
        
        document.getElementById('messageModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMessageModal();
            }
        });

        // Fire animation for active elements
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
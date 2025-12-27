<?php
declare(strict_types=1);
require_once '../includes/session.php';
require_once '../includes/functions.php';

class AdminDashboardPro {
    private SessionManager $session;
    private NovaCloudFunctions $functions;
    private array $data = [];
    
    public function __construct() {
        $this->session = new SessionManager();
        $this->functions = new NovaCloudFunctions();
        $this->session->requireAdmin();
        $this->handleRequests();
        $this->loadDashboardData();
    }
    
    private function handleRequests(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processRequest();
        }
    }
    
    private function processRequest(): void {
        $action = $_POST['action'] ?? '';
        $userId = (int)($_POST['user_id'] ?? 0);
        if (!$action || !$userId) {
            $resp = ['success' => false, 'message' => 'Missing parameters'];
            if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode($resp);
                exit;
            }
            header('Location: ?status=error&msg=' . urlencode($resp['message']));
            exit;
        }

        $response = $this->executeAdminAction($action, $userId, $_POST);

        if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        header('Location: ?status=' . ($response['success'] ? 'success' : 'error') . '&msg=' . urlencode($response['message']));
        exit;
    }
    
    private function executeAdminAction(string $action, int $userId, array $data): array {
        try {
            switch ($action) {
                case 'toggle_status':
                    $user = $this->functions->getUser($userId);
                    $newStatus = $user['account_status'] === 'active' ? 'inactive' : 'active';
                    $this->functions->updateUserStatus($userId, $newStatus);
                    return ['success' => true, 'message' => "User status updated to {$newStatus}"];
                    
                case 'suspend':
                    $this->functions->updateUserStatus($userId, 'suspended');
                    $this->functions->logAction("User #{$userId} suspended", 'admin_action');
                    return ['success' => true, 'message' => 'User suspended'];

                case 'deactivate':
                    $this->functions->updateUserStatus($userId, 'inactive');
                    $this->functions->logAction("User #{$userId} deactivated", 'admin_action');
                    return ['success' => true, 'message' => 'User deactivated'];
                    
                case 'impersonate':
                    $this->functions->createImpersonationSession($userId);
                    header('Location: ../dashboard.php');
                    exit;
                    
                case 'send_message':
                    $subject = htmlspecialchars($data['subject'] ?? 'System Notification');
                    $message = htmlspecialchars($data['message'] ?? '');
                    $this->functions->sendAdminMessage($userId, $subject, $message);
                    return ['success' => true, 'message' => 'Message sent'];
                    
                case 'bulk_action':
                    $userIds = array_map('intval', $data['user_ids'] ?? []);
                    $bulkAction = $data['bulk_action'] ?? '';
                    $count = $this->processBulkAction($bulkAction, $userIds);
                    return ['success' => true, 'message' => "{$count} users updated"];
                    
                case 'export_users':
                    $this->exportUsersCSV();
                    exit;
                    
                case 'search':
                    $this->data['users'] = $this->functions->searchUsers($data['query'] ?? '');
                    return ['success' => true, 'message' => 'Search completed'];
                    
                default:
                    throw new Exception('Invalid action');
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function processBulkAction(string $action, array $userIds): int {
        $count = 0;
        foreach ($userIds as $id) {
            if ($action === 'activate_all') {
                $this->functions->updateUserStatus($id, 'active');
                $count++;
            } elseif ($action === 'deactivate_all') {
                $this->functions->updateUserStatus($id, 'inactive');
                $count++;
            }
        }
        return $count;
    }
    
    private function exportUsersCSV(): void {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Username', 'Email', 'Status', 'Last Login', 'Storage Used']);
        
        foreach ($this->data['users'] as $user) {
            fputcsv($output, [
                $user['id'],
                $user['username'],
                $user['email'],
                $user['account_status'],
                $user['last_login'],
                $this->formatBytes($user['storage_used'] ?? 0)
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    private function loadDashboardData(): void {
        $this->data = [
            'users' => $this->functions->getAllUsers(),
            'stats' => $this->functions->getSystemStats(),
            'activities' => $this->functions->getRecentActivities(10),
            'alerts' => $this->functions->getSystemAlerts(),
            'storage' => $this->functions->getStorageOverview(),
        ];
        // Compute totals
        $storageTotal = 0;
        if (is_array($this->data['storage'])) {
            if (isset($this->data['storage']['total_size'])) {
                $storageTotal = (int)$this->data['storage']['total_size'];
            } elseif (!empty($this->data['storage'])) {
                foreach ($this->data['storage'] as $s) {
                    if (is_array($s)) {
                        $storageTotal += (int)($s['storage_used'] ?? ($s['storage_used_mb'] * 1024 * 1024 ?? 0));
                    }
                }
            }
        }

        $this->data['totals'] = [
            'users' => count($this->data['users']),
            'active' => count(array_filter($this->data['users'], fn($u) => $u['account_status'] === 'active')),
            'storage' => $storageTotal,
            'today' => count(array_filter($this->data['users'], 
                fn($u) => date('Y-m-d') === date('Y-m-d', strtotime($u['created_at']))
            )),
        ];
    }
    
    private function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < 4) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    public function render(): void {
        ?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NovaCloud</title>
    
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
                        'float': 'float 6s ease-in-out infinite',
                        'slide-in': 'slide-in 0.3s ease-out',
                        'fade-in': 'fade-in 0.5s ease-out',
                        'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                        'fire-flicker': 'fire-flicker 1.5s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
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

        .gold-glass {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(234, 88, 12, 0.05) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(245, 158, 11, 0.3);
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
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(245, 158, 11, 0.2), 0 0 100px rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.4);
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

        /* Golden Border */
        .golden-border {
            position: relative;
        }

        .golden-border::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1px;
            background: var(--gold-gradient);
            -webkit-mask: 
                linear-gradient(#fff 0 0) content-box, 
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }

        /* Fire Glow Effect */
        .fire-glow {
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.4);
        }

        .fire-glow:hover {
            box-shadow: 0 0 50px rgba(245, 158, 11, 0.6), 0 0 100px rgba(245, 158, 11, 0.2);
        }

        /* Elegant Dividers */
        .divider-gold {
            height: 2px;
            background: var(--gold-gradient);
            border: none;
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
                    <a href="dashboard.php" class="flex items-center gap-4 p-3 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 text-amber-700 border border-amber-200 shadow-sm">
                        <i class="fas fa-fire text-lg w-6"></i>
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
                            <span>Dashboard</span>
                        </span>
                        <span class="gradient-text ml-2">Premium</span>
                    </h1>
                    <p class="text-sm text-amber-600 mt-1">Monitor & control with fiery precision</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <div class="relative">
                        <input type="text" id="adminSearch" 
                               placeholder="Search users..." 
                               class="w-72 pl-12 pr-4 py-3 rounded-2xl border-0 bg-amber-50 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all text-sm outline-none shadow-sm text-gray-800 placeholder-amber-500/70">
                        <i class="fas fa-search absolute left-4 top-3.5 text-amber-500"></i>
                    </div>

                    <!-- Export Button -->
                    <button onclick="showExportModal()" class="px-4 py-3 gradient-bg text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all shadow-md fire-glow hover:animate-pulse-glow flex items-center gap-2">
                        <i class="fas fa-download"></i>
                        Export
                    </button>

                    <!-- Notifications -->
                    <button class="relative w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center hover:bg-amber-100 transition-colors border border-amber-200">
                        <i class="fas fa-bell text-amber-600"></i>
                        <span class="absolute -top-1 -right-1 w-5 h-5 gradient-bg rounded-full text-xs text-white flex items-center justify-center shadow-md"><?php echo count($this->data['alerts'] ?? []); ?></span>
                    </button>
                </div>
            </header>

            <!-- Content Area with Fire Pattern -->
            <div class="flex-1 overflow-y-auto bg-grid-pattern p-6 lg:p-10">
                
                <!-- Stats Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Users Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-amber-600 mb-1">Total Users</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $this->data['totals']['users']; ?></h3>
                                <p class="text-xs text-amber-500 mt-1">Registered accounts</p>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl border border-amber-200">
                                <i class="fas fa-users text-xl text-amber-500"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-amber-600">
                            <i class="fas fa-fire"></i>
                            <span>+12% from last month</span>
                        </div>
                    </div>

                    <!-- Active Users Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-amber-600 mb-1">Active Users</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $this->data['totals']['active']; ?></h3>
                                <p class="text-xs text-amber-500 mt-1"><?php echo round($this->data['totals']['active'] / max($this->data['totals']['users'], 1) * 100); ?>% active rate</p>
                            </div>
                            <div class="relative">
                                <div class="p-3 bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl border border-emerald-200">
                                    <i class="fas fa-user-check text-xl text-emerald-500"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2 text-xs text-amber-600">
                            <i class="fas fa-fire text-amber-500"></i>
                            <span>System is blazing</span>
                        </div>
                    </div>

                    <!-- New Today Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-amber-600 mb-1">New Today</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $this->data['totals']['today']; ?></h3>
                                <p class="text-xs text-amber-600 mt-1">Registered today</p>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-orange-50 to-red-50 rounded-xl border border-orange-200">
                                <i class="fas fa-user-plus text-xl text-orange-500"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="h-1 bg-amber-100 rounded-full overflow-hidden">
                                <div class="h-full gradient-bg rounded-full" style="width: <?php echo min(100, ($this->data['totals']['today'] / max($this->data['totals']['users'], 1)) * 100); ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Storage Card -->
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-amber-600 mb-1">Storage Used</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $this->formatBytes($this->data['totals']['storage']); ?></h3>
                                <p class="text-xs text-amber-500 mt-1">Across all accounts</p>
                            </div>
                            <div class="p-3 bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl border border-amber-200">
                                <i class="fas fa-hdd text-xl text-amber-500"></i>
                            </div>
                        </div>
                        <div class="relative pt-2">
                            <div class="h-2 bg-amber-100 rounded-full overflow-hidden">
                                <div class="h-full gradient-bg rounded-full" style="width: <?php echo min(100, ($this->data['totals']['storage'] / (500 * 1024 * 1024 * 1024)) * 100); ?>%"></div>
                            </div>
                            <span class="text-xs text-amber-500 absolute right-0 -top-5">of 500GB total</span>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Users Table -->
                    <div class="lg:col-span-2">
                        <div class="glass-card rounded-2xl p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800">Recent Users</h2>
                                    <p class="text-sm text-amber-600">Manage user accounts and permissions</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button onclick="bulkAction('activate_all')" class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium hover:bg-emerald-100 transition-colors border border-emerald-200">
                                        <i class="fas fa-fire mr-1"></i> Activate All
                                    </button>
                                    <button onclick="bulkAction('deactivate_all')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition-colors border border-gray-300">
                                        <i class="fas fa-pause mr-1"></i> Deactivate All
                                    </button>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full premium-table">
                                    <thead>
                                        <tr>
                                            <th class="pb-3 text-left text-sm font-medium text-amber-600 px-4">
                                                <input type="checkbox" id="selectAll" class="rounded bg-amber-50 border-amber-300 text-amber-500">
                                            </th>
                                            <th class="pb-3 text-left text-sm font-medium text-amber-600 px-4">User</th>
                                            <th class="pb-3 text-left text-sm font-medium text-amber-600 px-4">Status</th>
                                            <th class="pb-3 text-left text-sm font-medium text-amber-600 px-4">Storage</th>
                                            <th class="pb-3 text-left text-sm font-medium text-amber-600 px-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usersTable">
                                        <?php foreach (array_slice($this->data['users'], 0, 8) as $user): ?>
                                        <tr class="border-b border-amber-100 hover:bg-amber-50/50">
                                            <td class="py-4 px-4">
                                                <input type="checkbox" class="user-checkbox rounded bg-amber-50 border-amber-300 text-amber-500" value="<?php echo $user['id']; ?>">
                                            </td>
                                            <td class="py-4 px-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-full gradient-bg flex items-center justify-center text-white font-bold shadow-md">
                                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($user['username']); ?></p>
                                                        <p class="text-xs text-amber-600"><?php echo htmlspecialchars($user['email']); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-4">
                                                <span class="px-3 py-1 rounded-full text-xs font-medium <?php 
                                                    echo $user['account_status'] === 'active' ? 'badge-active' : 
                                                           ($user['account_status'] === 'suspended' ? 'badge-suspended' : 'badge-inactive');
                                                ?>">
                                                    <i class="fas fa-<?php echo $user['account_status'] === 'active' ? 'check-circle' : ($user['account_status'] === 'suspended' ? 'ban' : 'pause-circle'); ?> mr-1"></i>
                                                    <?php echo ucfirst($user['account_status']); ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 font-medium text-gray-800">
                                                <?php echo $this->formatBytes($user['storage_used'] ?? 0); ?>
                                            </td>
                                            <td class="py-4 px-4">
                                                <div class="flex items-center gap-2">
                                                    <button onclick="toggleUserStatus(<?php echo $user['id']; ?>)" 
                                                            class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-100 transition-colors border border-amber-300 hover:animate-pulse-glow" 
                                                            title="<?php echo $user['account_status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="fas <?php echo $user['account_status'] === 'active' ? 'fa-fire' : 'fa-fire'; ?> text-sm"></i>
                                                    </button>
                                                    <button onclick="suspendUser(<?php echo $user['id']; ?>)" 
                                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors border border-red-300" 
                                                            title="Suspend">
                                                        <i class="fas fa-ban text-sm"></i>
                                                    </button>
                                                    <button onclick="showMessageModal(<?php echo $user['id']; ?>)" 
                                                            class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center hover:bg-orange-100 transition-colors border border-orange-300" 
                                                            title="Message">
                                                        <i class="fas fa-envelope text-sm"></i>
                                                    </button>
                                                    <button onclick="impersonateUser(<?php echo $user['id']; ?>)" 
                                                            class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors border border-emerald-300 hover:animate-pulse-glow" 
                                                            title="Impersonate">
                                                        <i class="fas fa-user-secret text-sm"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-6 flex items-center justify-between">
                                <p class="text-sm text-amber-600">Showing 8 of <?php echo count($this->data['users']); ?> users</p>
                                <a href="manage-users.php" class="text-amber-600 hover:text-amber-700 text-sm font-medium flex items-center gap-1 hover-glow">
                                    View All Users
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div class="space-y-6">
                        <!-- System Status -->
                        <div class="glass-card rounded-2xl p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">System Status</h2>
                            <div class="space-y-4">
                                <?php
                                $rawStats = $this->data['stats'] ?? [];
                                $statsList = [];
                                if (isset($rawStats['users']) && isset($rawStats['files'])) {
                                    $statsList = [
                                        ['name' => 'Users', 'value' => (int)$rawStats['users']],
                                        ['name' => 'Files', 'value' => (int)$rawStats['files']],
                                        ['name' => 'Countries', 'value' => (int)$rawStats['countries']],
                                    ];
                                } elseif (is_array($rawStats)) {
                                    $statsList = $rawStats;
                                }

                                $maxVal = 1;
                                foreach ($statsList as $s) { $maxVal = max($maxVal, $s['value'] ?? 0); }

                                foreach ($statsList as $stat):
                                    $val = (int)($stat['value'] ?? 0);
                                    $percent = $maxVal > 0 ? round(($val / $maxVal) * 100, 2) : 0;
                                ?>
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm text-amber-600"><?php echo htmlspecialchars($stat['name'] ?? 'Stat'); ?></span>
                                        <span class="text-sm font-medium text-gray-800"><?php echo $val; ?></span>
                                    </div>
                                    <div class="h-2 bg-amber-100 rounded-full overflow-hidden">
                                        <div class="h-full gradient-bg rounded-full" style="width: <?php echo $percent; ?>%"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="glass-card rounded-2xl p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
                            <div class="grid grid-cols-2 gap-3">
                                <button onclick="window.location.href='manage-users.php?action=add'" 
                                        class="p-3 bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl text-amber-700 hover:from-amber-100 hover:to-amber-200 transition-all border border-amber-200 flex flex-col items-center justify-center hover-glow">
                                    <i class="fas fa-user-plus text-lg mb-2"></i>
                                    <span class="text-sm font-medium">Add User</span>
                                </button>
                                <button onclick="Admin.performAction('export_users')" 
                                        class="p-3 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl text-emerald-700 hover:from-emerald-100 hover:to-emerald-200 transition-all border border-emerald-200 flex flex-col items-center justify-center hover-glow">
                                    <i class="fas fa-download text-lg mb-2"></i>
                                    <span class="text-sm font-medium">Export</span>
                                </button>
                                <button onclick="window.location.href='backup.php'" 
                                        class="p-3 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl text-orange-700 hover:from-orange-100 hover:to-orange-200 transition-all border border-orange-200 flex flex-col items-center justify-center hover-glow">
                                    <i class="fas fa-save text-lg mb-2"></i>
                                    <span class="text-sm font-medium">Backup</span>
                                </button>
                                <button onclick="showBroadcastModal()" 
                                        class="p-3 bg-gradient-to-br from-red-50 to-red-100 rounded-xl text-red-700 hover:from-red-100 hover:to-red-200 transition-all border border-red-200 flex flex-col items-center justify-center hover-glow">
                                    <i class="fas fa-fire text-lg mb-2"></i>
                                    <span class="text-sm font-medium">Broadcast</span>
                                </button>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="glass-card rounded-2xl p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Activity</h2>
                            <div class="space-y-4">
                                <?php foreach ($this->data['activities'] as $activity): ?>
                                <div class="flex items-start gap-3 pb-3 border-b border-amber-100 last:border-0">
                                    <div class="w-2 h-2 rounded-full gradient-bg mt-2 fire-flicker"></div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800"><?php echo htmlspecialchars($activity['description']); ?></p>
                                        <p class="text-xs text-amber-600 mt-1"><?php echo $activity['time']; ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity animate-fade-in" onclick="closeModal()"></div>

        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 overflow-hidden transform transition-all animate-slide-in">
                <!-- Modal Decoration -->
                <div class="absolute top-0 right-0 w-32 h-32 gradient-bg rounded-full -translate-y-16 translate-x-16 opacity-10 fire-flicker"></div>
                
                <div class="relative">
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 mb-4">
                            <i class="fas fa-fire text-amber-500 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Send Message</h3>
                        <p class="text-sm text-amber-600">Send a fire-powered message</p>
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
                            <button type="button" onclick="closeModal()" 
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

    <!-- Notification Toast -->
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
                    const response = await fetch('', {
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
        
        let currentUserId = null;
        
        function showMessageModal(userId) {
            currentUserId = userId;
            document.getElementById('targetUserId').value = userId;
            document.getElementById('messageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        async function sendMessage(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-fire fa-spin"></i> Igniting...';
            submitBtn.disabled = true;
            
            const result = await Admin.performAction('send_message', {
                user_id: currentUserId,
                subject: formData.get('subject'),
                message: formData.get('message')
            });
            
            if (result.success) {
                Admin.showToast('Message fired successfully!');
                closeModal();
                e.target.reset();
            } else {
                Admin.showToast(result.message, 'error');
            }
            
            submitBtn.innerHTML = originalHTML;
            submitBtn.disabled = false;
        }
        
        async function toggleUserStatus(userId) {
            if (!Admin.confirmAction('Ignite user status change?')) return;
            
            const result = await Admin.performAction('toggle_status', { user_id: userId });
            Admin.showToast(result.message, result.success ? 'success' : 'error');
            
            if (result.success) {
                setTimeout(() => location.reload(), 1000);
            }
        }
        
        async function suspendUser(userId) {
            if (!Admin.confirmAction('Suspend this user?')) return;
            
            const result = await Admin.performAction('suspend', { user_id: userId });
            Admin.showToast(result.message, result.success ? 'success' : 'error');
            
            if (result.success) {
                setTimeout(() => location.reload(), 1000);
            }
        }
        
        async function impersonateUser(userId) {
            if (!Admin.confirmAction('Impersonate this user?')) return;
            
            const result = await Admin.performAction('impersonate', { user_id: userId });
            if (result.redirect) {
                window.location.href = result.redirect;
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
        
        async function searchUsers(query) {
            if (query.length < 2) return;
            
            const result = await Admin.performAction('search', { query: query });
            if (result.success) {
                // In a real implementation, you would update the table here
                console.log('Search completed');
            }
        }
        
        function showExportModal() {
            if (Admin.confirmAction('Export all users to CSV?')) {
                Admin.performAction('export_users');
            }
        }
        
        function showBroadcastModal() {
            Admin.showToast('Fire Broadcast feature coming soon!', 'info');
        }
        
        function closeModal() {
            document.getElementById('messageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function hideToast() {
            document.getElementById('toast').classList.add('hidden');
        }
        
        // Select all checkbox
        document.getElementById('selectAll')?.addEventListener('change', function(e) {
            document.querySelectorAll('.user-checkbox').forEach(cb => {
                cb.checked = e.target.checked;
            });
        });
        
        // Search functionality
        document.getElementById('adminSearch')?.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                searchUsers(this.value);
            }
        });
        
        // Form submission
        document.getElementById('messageForm')?.addEventListener('submit', sendMessage);
        
        // Close modal on Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeModal();
            }
        });
        
        // URL status message
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const msg = urlParams.get('msg');
        
        if (msg) {
            Admin.showToast(decodeURIComponent(msg), status || 'success');
        }

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
        <?php
    }
}

// Initialize dashboard
$dashboard = new AdminDashboardPro();
$dashboard->render();
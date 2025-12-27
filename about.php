<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

$session = new SessionManager();
$functions = new NovaCloudFunctions();

$isLoggedIn = $session->isLoggedIn();

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-key="site_name">NovaCloud - About Us</title>
    
    <!-- Same CSS dependencies as index.php -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8fafc;
        }

        /* Navbar Styles (Same as index.php) */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.15);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
            font-weight: 800;
            color: #4f46e5;
        }

        .nav-logo i {
            font-size: 2rem;
        }

        .logo-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-link {
            text-decoration: none;
            color: #4b5563;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            position: relative;
            padding: 5px 0;
        }

        .nav-link:hover {
            color: #4f46e5;
        }

        .nav-link.active {
            color: #4f46e5;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .language-selector select {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            color: #4b5563;
            font-size: 0.9rem;
            cursor: pointer;
            outline: none;
            transition: all 0.3s ease;
        }

        .language-selector select:hover {
            border-color: #4f46e5;
        }

        .auth-buttons {
            display: flex;
            gap: 15px;
        }

        .btn-login, .btn-register {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-login {
            color: #4f46e5;
            border: 2px solid #e0e7ff;
            background: transparent;
        }

        .btn-login:hover {
            background: #e0e7ff;
            border-color: #4f46e5;
        }

        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .user-menu {
            position: relative;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            position: relative;
        }

        .admin-badge {
            position: absolute;
            bottom: -2px;
            right: -2px;
            background: #10b981;
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            border: 2px solid white;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            padding: 10px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .user-menu:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .user-dropdown a:hover {
            background: #f3f4f6;
            color: #4f46e5;
        }

        .user-dropdown a i {
            width: 20px;
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
        }

        .hamburger {
            display: block;
            width: 25px;
            height: 3px;
            background: #4f46e5;
            margin: 5px 0;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .nav-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s ease;
        }

        /* About Page Custom Styles */
        .gradient-text {
            background: linear-gradient(135deg, #0891b2 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .bounce {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .marquee {
            animation: marquee 30s linear infinite;
        }
        
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        
        .typewriter {
            overflow: hidden;
            border-right: 3px solid #0891b2;
            white-space: nowrap;
            animation: typing 3.5s steps(40, end), blink 0.75s step-end infinite;
        }
        
        @keyframes typing {
            from { width: 0 }
            to { width: 100% }
        }
        
        @keyframes blink {
            from, to { border-color: transparent }
            50% { border-color: #0891b2 }
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(8, 145, 178, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 20px;
                gap: 20px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .nav-menu.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }
            
            .nav-links {
                flex-direction: column;
                width: 100%;
            }
            
            .nav-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .mobile-toggle {
                display: block;
            }
            
            .mobile-toggle.active .hamburger:nth-child(1) {
                transform: rotate(45deg) translate(6px, 6px);
            }
            
            .mobile-toggle.active .hamburger:nth-child(2) {
                opacity: 0;
            }
            
            .mobile-toggle.active .hamburger:nth-child(3) {
                transform: rotate(-45deg) translate(6px, -6px);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation (Same as index.php) -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-cloud-upload-alt"></i>
                <span class="logo-text" data-key="site_name">NovaCloud</span>
            </div>
            
            <div class="nav-menu">
                <div class="nav-links">
                    <a href="index.php" class="nav-link" data-key="home">Home</a>
                    
                    <a href="about.php" class="nav-link active" data-key="about">About</a>
                    <a href="help.php" class="nav-link" data-key="help">Help</a>
                    
                    <a href="index.php#faq" class="nav-link" data-key="faq">FAQ</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="nav-link dashboard-link" data-key="dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="nav-actions">
                    <div class="language-selector">
                        <select id="languageSelect" class="language-dropdown">
                            <option value="en" data-key="english">🇺🇸 English</option>
                            <option value="am" data-key="amharic">🇪🇹 አማርኛ</option>
                            <option value="om" data-key="oromo">🇪🇹 Afaan Oromoo</option>
                        </select>
                    </div>
                    
                    <?php if ($isLoggedIn): ?>
                        <div class="user-menu">
                            <div class="user-avatar">
                                <i class="fas fa-user-circle"></i>
                                <?php if ($session->isAdmin()): ?>
                                    <span class="admin-badge"><i class="fas fa-shield-alt"></i></span>
                                <?php endif; ?>
                            </div>
                            <div class="user-dropdown">
                                <a href="profile.php" data-key="profile"><i class="fas fa-user"></i> Profile</a>
                                <?php if ($session->isAdmin()): ?>
                                    <a href="admin/dashboard.php" data-key="admin_panel"><i class="fas fa-cog"></i> Admin Panel</a>
                                <?php endif; ?>
                                <a href="logout.php" data-key="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="auth.php" class="btn-login" data-key="login_button">
                                <i class="fas fa-sign-in-alt"></i> Sign In
                            </a>
                            <a href="register.php" class="btn-register" data-key="register_button">
                                <i class="fas fa-user-plus"></i> Get Started
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <button class="mobile-toggle">
                <span class="hamburger"></span>
                <span class="hamburger"></span>
                <span class="hamburger"></span>
            </button>
        </div>
        
        <div class="nav-progress"></div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="min-h-screen flex items-center justify-center px-6 pt-24">
        <div class="container mx-auto text-center">
            <!-- Badge -->
            <div class="inline-block px-4 py-2 bg-cyan-100 text-cyan-700 rounded-full mb-8 font-semibold">
                🚀 INTERNET PROGRAMMING II PROJECT
            </div>
            
            <!-- Main Heading -->
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6">
                <span class="block text-slate-900">Data Beyond</span>
                <span class="gradient-text">Boundaries</span>
            </h1>
            
            <!-- Typewriter Text -->
            <div class="h-12 mb-6">
                <p id="typewriter" class="text-xl md:text-2xl text-cyan-600 font-semibold typewriter inline-block">
                    Secure Cloud Storage
                </p>
            </div>
            
            <!-- Description -->
            <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto mb-10">
                Welcome to <span class="font-bold text-cyan-600">NovaCloud</span>. 
                A revolutionary cloud storage platform engineered at Ambo University.
            </p>
            
            <!-- Buttons -->
            <div class="flex justify-center gap-4 flex-wrap">
                <a href="register.php" class="px-6 py-3 bg-cyan-600 text-white rounded-xl font-semibold hover:bg-cyan-700 transition-colors shadow-lg hover:shadow-xl">
                    Start Free Trial
                </a>
                <a href="#features" class="px-6 py-3 bg-white text-slate-700 border border-slate-200 rounded-xl font-semibold hover:border-cyan-600 transition-colors shadow hover:shadow-md">
                    Learn More
                </a>
            </div>
            
            <!-- Stats -->
            <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8 max-w-3xl mx-auto">
                <div class="text-center">
                    <div class="text-3xl font-bold text-cyan-600 mb-2">50+</div>
                    <div class="text-slate-600">PB Stored</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-cyan-600 mb-2">99.9%</div>
                    <div class="text-slate-600">Uptime</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-cyan-600 mb-2">25+</div>
                    <div class="text-slate-600">Countries</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-cyan-600 mb-2">10ms</div>
                    <div class="text-slate-600">Latency</div>
                </div>
            </div>
            
            <!-- Scroll Indicator -->
            <div class="mt-12">
                <svg class="w-8 h-8 text-cyan-600 mx-auto bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </section>

    <!-- Tech Stack -->
    <section class="py-12 bg-white border-y border-slate-200 overflow-hidden">
        <div class="marquee whitespace-nowrap flex gap-16">
            <div class="flex gap-16">
                <span class="text-xl font-bold text-slate-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    HTML
                </span>
                <span class="text-xl font-bold text-slate-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8 4s8-1.79 8-4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                    SQL
                </span>
                <span class="text-xl font-bold text-slate-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                    PHP
                </span>
                <span class="text-xl font-bold text-slate-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    CDN
                </span>
                <span class="text-xl font-bold text-slate-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    JS
                </span>
            </div>
            <div class="flex gap-16">
                <span class="text-xl font-bold text-slate-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    REACT
                </span>
                <span class="text-xl font-bold text-slate-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8 4s8-1.79 8-4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                    CSS
                </span>
                <span class="text-xl font-bold text-slate-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                    .HTACCESS
                </span>
                <span class="text-xl font-bold text-slate-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    MARKUP
                </span>
                <span class="text-xl font-bold text-slate-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    XAMPP
                </span>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-900 mb-4">
                    Why <span class="gradient-text">NovaCloud?</span>
                </h2>
                <p class="text-slate-600 text-lg">
                    Redefining data storage with modern architecture.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-slate-100 card-hover">
                    <div class="w-14 h-14 bg-cyan-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Ironclad Security</h3>
                    <p class="text-slate-600">AES-256 bit encryption ensures your data remains yours. Privacy by design.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-slate-100 card-hover">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Lightning Speed</h3>
                    <p class="text-slate-600">Edge computing nodes deliver your content with minimal latency globally.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-slate-100 card-hover">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Global Scalability</h3>
                    <p class="text-slate-600">Infrastructure that scales automatically to handle petabytes of data.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Developer Profile -->
    <section id="about" class="py-20 bg-slate-50">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <!-- Profile Image -->
                <div class="md:w-1/2">
                    <div class="relative">
                        <div class="w-72 h-72 md:w-96 md:h-96 bg-gradient-to-br from-cyan-200 to-purple-200 rounded-full flex items-center justify-center mx-auto float">
                            <div class="w-64 h-64 md:w-80 md:h-80 bg-white rounded-full flex items-center justify-center shadow-2xl">
                                <svg class="w-32 h-32 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        <!-- Badges -->
                        <div class="absolute -top-4 -left-4 bg-white px-4 py-2 rounded-xl shadow-lg border border-slate-100">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                </svg>
                                <span class="font-bold text-slate-900">Ambo University</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Info -->
                <div class="md:w-1/2">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-purple-100 text-purple-700 rounded-full mb-6 font-semibold">
                        THE ARCHITECT
                    </div>
                    
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-900 mb-6">
                        Abraham Mekonnen
                    </h2>
                    
                    <p class="text-lg text-slate-600 mb-8 leading-relaxed">
                        A visionary student at <span class="font-bold">Ambo University</span>, 
                        Abraham crafted NovaCloud as a testament to the power of modern web technologies. 
                        Designed for the <span class="text-cyan-600 font-semibold">Internet Programming II</span> project.
                    </p>
                    
                    <!-- Info Cards -->
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="p-4 bg-white rounded-xl border-l-4 border-cyan-500 shadow">
                            <p class="text-sm text-slate-500">University</p>
                            <p class="font-bold text-lg">Ambo University</p>
                        </div>
                        <div class="p-4 bg-white rounded-xl border-l-4 border-purple-500 shadow">
                            <p class="text-sm text-slate-500">Course</p>
                            <p class="font-bold text-lg">Internet Programming II</p>
                        </div>
                    </div>
                    
                    <!-- Skills -->
                    <div class="flex flex-wrap gap-3 mb-8">
                        <span class="px-4 py-2 bg-white text-slate-700 rounded-full border border-slate-200 font-medium shadow-sm">HTML</span>
                        <span class="px-4 py-2 bg-white text-slate-700 rounded-full border border-slate-200 font-medium shadow-sm">CSS</span>
                        <span class="px-4 py-2 bg-white text-slate-700 rounded-full border border-slate-200 font-medium shadow-sm">JS</span>
                        <span class="px-4 py-2 bg-white text-slate-700 rounded-full border border-slate-200 font-medium shadow-sm">PHP</span>
                        <span class="px-4 py-2 bg-white text-slate-700 rounded-full border border-slate-200 font-medium shadow-sm">sql</span>
                        <span class="px-4 py-2 bg-white text-slate-700 rounded-full border border-slate-200 font-medium shadow-sm">CDN</span>
                    </div>
                    
                    <!-- CTA -->
                    <div class="flex gap-4">
                        <button class="px-6 py-3 bg-cyan-600 text-white rounded-xl font-semibold hover:bg-cyan-700 transition-colors">
                            View Portfolio
                        </button>
                        <button class="px-6 py-3 bg-white text-slate-700 border border-slate-200 rounded-xl font-semibold hover:border-cyan-600 transition-colors">
                            Contact
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-6">
        <div class="container mx-auto">
            <div class="bg-gradient-to-r from-cyan-600 to-purple-600 p-8 md:p-12 rounded-3xl text-center text-white max-w-4xl mx-auto">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-6">
                    Ready to experience the future?
                </h2>
                <p class="text-cyan-100 text-lg mb-8 max-w-2xl mx-auto">
                    Join thousands of users who trust NovaCloud for their data needs.
                </p>
                <a href="register.php" class="px-8 py-4 bg-white text-cyan-600 rounded-full font-bold text-lg hover:bg-slate-100 transition-colors shadow-lg hover:shadow-xl inline-block">
                    Get Started Now
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white py-12 border-t border-slate-200 mt-12">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-6 md:mb-0">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4 4 0 003 15z" />
                        </svg>
                        <span class="text-xl font-bold text-slate-900">NovaCloud</span>
                    </div>
                    <p class="text-slate-500">
                        © 2024 Abraham Mekonnen. Ambo University.
                    </p>
                </div>
                <div class="flex gap-6">
                    <a href="#" class="text-slate-400 hover:text-cyan-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </a>
                    <a href="#" class="text-slate-400 hover:text-cyan-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </a>
                    <a href="#" class="text-slate-400 hover:text-cyan-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                </div>
            </div>
            
            <div class="text-center mt-8 pt-8 border-t border-slate-200">
                <p class="text-slate-400 text-sm">
                    This project was created for educational purposes as part of the Internet Programming II course at Ambo University.
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Navbar and interaction scripts (same as index.php)
        document.addEventListener('DOMContentLoaded', function() {
            // Sticky navbar on scroll
            const navbar = document.querySelector('.navbar');
            const navProgress = document.querySelector('.nav-progress');
            
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                
                // Progress bar
                const winHeight = window.innerHeight;
                const docHeight = document.documentElement.scrollHeight;
                const scrollTop = window.pageYOffset;
                const trackLength = docHeight - winHeight;
                const progress = (scrollTop / trackLength) * 100;
                navProgress.style.width = progress + '%';
            });
            
            // Mobile menu toggle
            const mobileToggle = document.querySelector('.mobile-toggle');
            const navMenu = document.querySelector('.nav-menu');
            
            if (mobileToggle) {
                mobileToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    mobileToggle.classList.toggle('active');
                });
            }
            
            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                        
                        // Close mobile menu if open
                        if (navMenu) {
                            navMenu.classList.remove('active');
                        }
                        if (mobileToggle) {
                            mobileToggle.classList.remove('active');
                        }
                    }
                });
            });

            // Typewriter effect
            const typewriter = document.getElementById('typewriter');
            if (typewriter) {
                const text = "Secure Cloud Storage";
                let i = 0;
                const typing = setInterval(() => {
                    if (i <= text.length) {
                        typewriter.textContent = text.substring(0, i);
                        i++;
                    } else {
                        clearInterval(typing);
                    }
                }, 100);
            }

            // Language switcher functionality
            const languageSelect = document.getElementById('languageSelect');
            if (languageSelect) {
                languageSelect.addEventListener('change', function() {
                    const selectedLang = this.value;
                    // Implement language switching logic here
                    console.log('Selected language:', selectedLang);
                });
            }

            // Add hover effects to all cards
            document.querySelectorAll('.card-hover').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Simple animation for stats
            const stats = document.querySelectorAll('.text-3xl.font-bold');
            stats.forEach(stat => {
                const target = parseInt(stat.textContent);
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        stat.textContent = target + (stat.textContent.includes('%') ? '%' : '+');
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(current) + (stat.textContent.includes('%') ? '%' : '+');
                    }
                }, 50);
            });

            console.log('NovaCloud About page loaded successfully!');
        });
    </script>

    <!-- React Components Container -->
    <div id="react-components"></div>

    <!-- Scripts -->
    <script src="assets/js/language-switcher.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script type="text/babel">
        // React Components (if needed)
        class NotificationBell extends React.Component {
            state = {
                count: 3,
                notifications: [
                    { id: 1, message: 'File uploaded successfully', time: '2 min ago' },
                    { id: 2, message: 'Storage almost full', time: '1 hour ago' },
                    { id: 3, message: 'New feature available', time: '1 day ago' }
                ]
            };
            
            render() {
                return React.createElement('div', {className: 'notification-bell'},
                    React.createElement('i', {className: 'fas fa-bell'}),
                    this.state.count > 0 && 
                    React.createElement('span', {className: 'notification-count'}, this.state.count)
                );
            }
        }

        // Initialize React Components
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('react-components');
            if (container) {
                ReactDOM.render(React.createElement(NotificationBell), container);
            }
        });
    </script>
</body>
</html>
<?php include 'includes/footer.php'; ?>
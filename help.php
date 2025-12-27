<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

$session = new SessionManager();
$functions = new NovaCloudFunctions();

$isLoggedIn = $session->isLoggedIn();
$username = $isLoggedIn ? $session->getUsername() : null;
$isAdmin = $session->isAdmin();

// Gemini API Key
$geminiApiKey = "AIzaSyDMkGbQrnQbIYGAWwR7sXfLUa_n1gbk6DA";

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaCloud - Help & AI Assistant</title>
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
        }

        /* Simplified Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 0 20px;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
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
            gap: 30px;
        }

        .nav-link {
            text-decoration: none;
            color: #4b5563;
            font-weight: 500;
            font-size: 1rem;
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

        /* Chat Styles */
        .chat-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .message {
            max-width: 80%;
            margin-bottom: 16px;
            padding: 12px 16px;
            border-radius: 18px;
            animation: fadeIn 0.3s ease;
        }

        .user-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .ai-message {
            background: white;
            color: #374151;
            border: 1px solid #e5e7eb;
            margin-right: auto;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .quick-questions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .quick-question {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .quick-question:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: #9ca3af;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-8px); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(8, 145, 178, 0.1);
        }

        /* Mobile Responsive */
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
            
            .auth-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-login, .btn-register {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
    <!-- Static favicon link (loads favicon.png from project root) -->
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="apple-touch-icon" href="favicon.png">
    <?php
    if (file_exists(__DIR__ . '/favicon.png')) {
        $fav = (defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/favicon.png' : '/favicon.png');
        echo '<link rel="icon" type="image/png" href="' . htmlspecialchars($fav) . '">';
        echo '<link rel="apple-touch-icon" href="' . htmlspecialchars($fav) . '">';
    } elseif (file_exists(__DIR__ . '/favicon.ico')) {
        $fav = (defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/favicon.ico' : '/favicon.ico');
        echo '<link rel="icon" type="image/x-icon" href="' . htmlspecialchars($fav) . '">';
    } else {
        echo '<link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">';
    }
    ?>
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-cloud-upload-alt"></i>
                <span class="logo-text">NovaCloud</span>
            </div>
            
            <div class="nav-menu">
                <div class="nav-links">
                    <a href="index.php" class="nav-link" data-key="home">Home</a>
                    <a href="about.php" class="nav-link" data-key="about">About</a>
                    <a href="help.php" class="nav-link active" data-key="help">Help</a>
                    <a href="#faq" class="nav-link" data-key="faq">FAQ</a>
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

    <!-- Main Content -->
    <div class="pt-24 chat-container">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <div class="inline-block px-4 py-2 bg-cyan-100 text-cyan-700 rounded-full mb-6 font-semibold animate__animated animate__pulse">
                🤖 NOVACLOUD AI ASSISTANT
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                Need Help with <span class="text-cyan-600">NovaCloud?</span>
            </h1>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                I'm NovaCloud AI, powered by Google Gemini. Ask me anything about our platform!
            </p>
            <div class="mt-4 text-sm text-gray-500">
                <i class="fas fa-bolt mr-1"></i> Instant responses powered by AI
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Chat Interface -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-6 card-hover">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-r from-cyan-500 to-purple-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-robot text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-xl">NovaCloud AI Assistant</h3>
                            <p class="text-gray-500 text-sm">Always ready to help</p>
                        </div>
                        <div class="ml-auto">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-gray-500">Online</span>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div id="chatBox" class="h-[400px] overflow-y-auto mb-6 p-4 bg-gray-50 rounded-lg space-y-4">
                        <!-- Initial message -->
                        <div class="message ai-message">
                            <div class="font-semibold text-cyan-600 mb-1">NovaCloud AI</div>
                            Hello! I'm **NovaCloud AI**, your intelligent assistant for our cloud storage platform. I can help you with:
                            <ul class="mt-2 ml-4 space-y-1">
                                <li>• How to use NovaCloud features</li>
                                <li>• Account setup and management</li>
                                <li>• File uploading and sharing</li>
                                <li>• Understanding storage plans</li>
                                <li>• Technical support and guidance</li>
                            </ul>
                            <div class="mt-3 text-sm text-gray-500">
                                Try asking: "How do I upload files?" or "Who created NovaCloud?"
                            </div>
                        </div>
                    </div>

                    <!-- Quick Questions -->
                    <div class="mb-6">
                        <p class="text-gray-600 mb-3">Quick questions:</p>
                        <div class="quick-questions">
                            <button class="quick-question" onclick="askQuickQuestion('Who created NovaCloud?')">
                                👨‍💻 Creator
                            </button>
                            <button class="quick-question" onclick="askQuickQuestion('How do I upload files?')">
                                📤 Upload files
                            </button>
                            <button class="quick-question" onclick="askQuickQuestion('How to share files?')">
                                🔗 Share files
                            </button>
                            <button class="quick-question" onclick="askQuickQuestion('What is NovaCloud?')">
                                🤔 What is this?
                            </button>
                            <button class="quick-question" onclick="askQuickQuestion('Is it free?')">
                                💰 Pricing
                            </button>
                            <button class="quick-question" onclick="askQuickQuestion('How secure is it?')">
                                🔒 Security
                            </button>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="flex gap-3">
                        <input 
                            id="userMsg" 
                            type="text" 
                            placeholder="Ask me anything about NovaCloud..." 
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                            onkeypress="if(event.key === 'Enter') sendMessage()"
                        />
                        <button 
                            id="sendBtn" 
                            onclick="sendMessage()"
                            class="px-6 py-3 bg-gradient-to-r from-cyan-600 to-purple-600 text-white rounded-xl font-semibold hover:opacity-90 transition-opacity flex items-center gap-2"
                        >
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column - Help Resources -->
            <div class="space-y-6">
                <!-- About NovaCloud -->
                <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                    <h3 class="font-bold text-xl mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-cyan-600"></i> About NovaCloud
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 bg-cyan-50 rounded-lg">
                            <i class="fas fa-user-graduate text-cyan-600"></i>
                            <div>
                                <p class="font-semibold">Creator</p>
                                <p class="text-sm text-gray-600">Abraham Mekonnen</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-purple-50 rounded-lg">
                            <i class="fas fa-university text-purple-600"></i>
                            <div>
                                <p class="font-semibold">Institution</p>
                                <p class="text-sm text-gray-600">Ambo University</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg">
                            <i class="fas fa-book text-blue-600"></i>
                            <div>
                                <p class="font-semibold">Course</p>
                                <p class="text-sm text-gray-600">Internet Programming II</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Guide -->
                <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                    <h3 class="font-bold text-xl mb-4 flex items-center gap-2">
                        <i class="fas fa-graduation-cap text-purple-600"></i> Getting Started
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-cyan-100 rounded-full flex items-center justify-center">
                                <span class="text-cyan-600 font-bold">1</span>
                            </div>
                            <span>Register for a free account</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-cyan-100 rounded-full flex items-center justify-center">
                                <span class="text-cyan-600 font-bold">2</span>
                            </div>
                            <span>Upload files via dashboard</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-cyan-100 rounded-full flex items-center justify-center">
                                <span class="text-cyan-600 font-bold">3</span>
                            </div>
                            <span>Share files with secure links</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-cyan-100 rounded-full flex items-center justify-center">
                                <span class="text-cyan-600 font-bold">4</span>
                            </div>
                            <span>Upgrade for more storage</span>
                        </li>
                    </ul>
                </div>

                <!-- Contact Support -->
                <div class="bg-gradient-to-br from-cyan-50 to-purple-50 rounded-2xl shadow-lg p-6 card-hover border border-cyan-100">
                    <h3 class="font-bold text-xl mb-4 flex items-center gap-2">
                        <i class="fas fa-headset text-green-600"></i> Need More Help?
                    </h3>
                    <p class="text-gray-700 mb-4 text-sm">If you need additional assistance:</p>
                    <div class="space-y-3">
                        <?php if ($isLoggedIn): ?>
                            <a href="dashboard.php" class="flex items-center gap-3 text-cyan-600 hover:text-cyan-700 p-2 hover:bg-white rounded-lg transition-colors">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Go to Dashboard</span>
                            </a>
                        <?php endif; ?>
                        <a href="about.php" class="flex items-center gap-3 text-cyan-600 hover:text-cyan-700 p-2 hover:bg-white rounded-lg transition-colors">
                            <i class="fas fa-info-circle"></i>
                            <span>About NovaCloud</span>
                        </a>
                        <div class="p-2">
                            <p class="text-sm font-semibold mb-1">Email Support</p>
                            <p class="text-xs text-gray-600">support@novacloud.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-12 border-t border-gray-200 mt-12">
        <div class="container mx-auto px-6 text-center">
            <p class="text-gray-600">
                © 2024 NovaCloud. Created by Abraham Mekonnen for Ambo University Internet Programming II Project.
            </p>
            <p class="text-sm text-gray-500 mt-2">
                AI Assistant powered by Google Gemini API
            </p>
        </div>
    </footer>

    <script>
        // Chat functionality
        function appendMessage(who, text, isTyping = false) {
            const box = document.getElementById('chatBox');
            const el = document.createElement('div');
            
            if (isTyping) {
                el.className = 'message ai-message';
                el.innerHTML = `
                    <div class="font-semibold text-cyan-600 mb-1">NovaCloud AI</div>
                    <div class="typing-indicator">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                `;
            } else {
                el.className = who === 'user' ? 'message user-message' : 'message ai-message';
                const sender = who === 'user' ? 'You' : 'NovaCloud AI';
                el.innerHTML = `
                    <div class="${who === 'user' ? 'text-white' : 'text-cyan-600'} font-semibold mb-1">${sender}</div>
                    <div>${text.replace(/\n/g, '<br>')}</div>
                `;
            }
            
            box.appendChild(el);
            box.scrollTop = box.scrollHeight;
        }

        function askQuickQuestion(question) {
            document.getElementById('userMsg').value = question;
            sendMessage();
        }

        async function sendMessage() {
            const input = document.getElementById('userMsg');
            const message = input.value.trim();
            
            if (!message) return;
            
            // Add user message
            appendMessage('user', message);
            input.value = '';
            
            // Show typing indicator
            appendMessage('ai', '', true);
            
            try {
                // Send to ai-chat.php endpoint
                const response = await fetch('api/ai-chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        // Pass your API key (though the server has it hardcoded)
                        api_key: "AIzaSyDMkGbQrnQbIYGAWwR7sXfLUa_n1gbk6DA",
                        prompt: message
                    })
                });
                
                const data = await response.json();
                
                // Remove typing indicator
                const box = document.getElementById('chatBox');
                box.removeChild(box.lastChild);
                
                if (data.success) {
                    appendMessage('ai', data.reply);
                } else {
                    // Use fallback response if available
                    if (data.fallback_reply) {
                        appendMessage('ai', data.fallback_reply);
                    } else {
                        appendMessage('ai', 'Error: ' + (data.error || 'Please try again.'));
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                const box = document.getElementById('chatBox');
                box.removeChild(box.lastChild);
                // Fallback to local response
                appendMessage('ai', getLocalResponse(message));
            }
        }

        // Local fallback responses
        function getLocalResponse(message) {
            const lowerMsg = message.toLowerCase();
            
            if (lowerMsg.includes('who') && lowerMsg.includes('create')) {
                return "👋 I'm NovaCloud AI! NovaCloud was created by **Abraham Mekonnen**, a Information Technology student at **Ambo University** in Ethiopia. This is an Internet Programming II project that demonstrates modern web development with cloud storage functionality.";
            }
            
            if (lowerMsg.includes('ambo') || lowerMsg.includes('university')) {
                return "🏛️ **Ambo University** is where NovaCloud was developed as part of the Internet Programming II curriculum. The project showcases practical application of web technologies like PHP, MySQL, JavaScript, and cloud architecture.";
            }
            
            if (lowerMsg.includes('upload')) {
                return "📤 **To upload files to NovaCloud:**\n\n1. **Login** to your account\n2. Go to the **Dashboard**\n3. Click the **Upload** button\n4. Select files from your device\n5. Files are **encrypted automatically** with AES-256\n\nMaximum file size: Free=100MB, Premium=2GB, Business=10GB";
            }
            
            if (lowerMsg.includes('share')) {
                return "🔗 **To share files on NovaCloud:**\n\n1. Go to **My Files** in your Dashboard\n2. Click on the file\n3. Click **Share** button\n4. Choose: Shareable link or email invite\n5. Set permissions and expiration date\n\nAll shared files remain encrypted for security.";
            }
            
            if (lowerMsg.includes('free') || lowerMsg.includes('price')) {
                return "💰 **NovaCloud Plans:**\n\n**FREE**: 5GB storage, basic features\n**PREMIUM**: $4.99/month, 100GB, advanced sharing\n**BUSINESS**: $14.99/month, 1TB, team features\n\nUpgrade anytime from your Dashboard!";
            }
            
            if (lowerMsg.includes('login') || lowerMsg.includes('sign in')) {
                return "🔑 **To login to NovaCloud:**\n\n1. Click **Sign In** button\n2. Enter username/email and password\n3. Click login\n4. Access your dashboard\n\nNew user? Click **Get Started** to register for free.";
            }
            
            if (lowerMsg.includes('what') && lowerMsg.includes('novacloud')) {
                return "🌩️ **NovaCloud** is a secure cloud storage platform created for Ambo University's Internet Programming II project. It allows users to store, share, and manage files online with enterprise-grade security and user-friendly interface.";
            }
            
            if (lowerMsg.includes('feature')) {
                return "✨ **NovaCloud Features:**\n\n• Secure file storage (AES-256)\n• File sharing with secure links\n• User-friendly dashboard\n• Cross-platform web access\n• Real-time file sync\n• Admin controls\n• Multi-language support";
            }
            
            if (lowerMsg.includes('secure') || lowerMsg.includes('safe')) {
                return "🛡️ **NovaCloud Security:**\n\n• AES-256 encryption for all files\n• SSL/TLS for data transfer\n• Secure user authentication\n• Regular security updates\n• GDPR compliance\n• Your data privacy is protected";
            }
            
            return "Hello! I'm **NovaCloud AI**, your assistant for the NovaCloud cloud storage platform. \n\nI can help you with:\n• Account setup and management\n• File uploading and sharing\n• Understanding storage plans\n• Technical guidance\n• Security information\n\nPlease ask me specific questions about using NovaCloud!";
        }

        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggle = document.querySelector('.mobile-toggle');
            const navMenu = document.querySelector('.nav-menu');
            
            if (mobileToggle) {
                mobileToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    mobileToggle.classList.toggle('active');
                });
            }
            
            // Add enter key support for chat
            document.getElementById('userMsg').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') sendMessage();
            });
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.nav-menu') && !event.target.closest('.mobile-toggle')) {
                    navMenu.classList.remove('active');
                    mobileToggle.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
<?php include 'includes/footer.php'; ?>
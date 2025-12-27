<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Your Gemini API Key
$GEMINI_API_KEY = "AIzaSyDMkGbQrnQbIYGAWwR7sXfLUa_n1gbk6DA";

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$userApiKey = isset($data['api_key']) ? trim($data['api_key']) : '';
$userPrompt = isset($data['prompt']) ? trim($data['prompt']) : '';

// If no API key provided, use the hardcoded one
$apiKeyToUse = !empty($userApiKey) ? $userApiKey : $GEMINI_API_KEY;

if (empty($userPrompt)) {
    echo json_encode([
        'success' => false,
        'error' => 'No prompt provided. Please ask a question.'
    ]);
    exit;
}

// NovaCloud Context
$novaCloudContext = "You are NovaCloud AI, an intelligent assistant for the NovaCloud cloud storage platform. 
IMPORTANT BACKGROUND ABOUT NOVACLOUD:
- NovaCloud is a cloud storage platform created by Abraham Mekonnen (a student at Ambo University in Ethiopia)
- This is an academic project for the Internet Programming II course at Ambo University
- NovaCloud provides secure file storage, sharing, and management services
- Key features: AES-256 encryption, file sharing with secure links, user dashboard, admin panel
- Storage plans: Free (5GB), Premium ($4.99/month for 100GB), Business ($14.99/month for 1TB)
- Technologies used: PHP, MySQL, JavaScript, HTML/CSS, Tailwind CSS

YOUR IDENTITY AND PURPOSE:
1. You are NovaCloud AI - always introduce yourself as such
2. Your main purpose is to help users navigate and use NovaCloud
3. You specialize in cloud storage, file management, and technical guidance
4. When asked about creator/university, provide accurate details
5. Be friendly, helpful, and professional

RESPONSE GUIDELINES:
- If asked about NovaCloud features, explain them clearly
- If asked about file uploads, provide step-by-step instructions
- If asked about security, explain encryption and safety measures
- If asked about pricing, list the available plans
- If asked about the creator, mention Abraham Mekonnen and Ambo University
- Keep responses concise but informative
- Format responses with bullet points or numbered steps when helpful
- Use emojis occasionally to make responses friendly

User's question or request: " . $userPrompt . "

Please provide a helpful response as NovaCloud AI:";

try {
    // Call Gemini API
    $response = callGeminiAPI($apiKeyToUse, $novaCloudContext);
    
    if ($response['success']) {
        echo json_encode([
            'success' => true,
            'reply' => $response['text'],
            'tokens_used' => $response['tokens_used'] ?? 0
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $response['error'],
            'fallback_reply' => getFallbackResponse($userPrompt)
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'API Error: ' . $e->getMessage(),
        'fallback_reply' => getFallbackResponse($userPrompt)
    ]);
}

function callGeminiAPI($apiKey, $prompt) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey;
    
    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "topK" => 1,
            "topP" => 1,
            "maxOutputTokens" => 1000,
            "stopSequences" => []
        ],
        "safetySettings" => [
            [
                "category" => "HARM_CATEGORY_HARASSMENT",
                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
            ],
            [
                "category" => "HARM_CATEGORY_HATE_SPEECH",
                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
            ],
            [
                "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
            ],
            [
                "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'CURL Error: ' . $error
        ];
    }
    
    if ($httpCode !== 200) {
        $errorData = json_decode($response, true);
        $errorMsg = 'HTTP ' . $httpCode . ': ';
        
        if (isset($errorData['error']['message'])) {
            $errorMsg .= $errorData['error']['message'];
        } else {
            $errorMsg .= 'Unknown API error';
        }
        
        return [
            'success' => false,
            'error' => $errorMsg
        ];
    }
    
    $responseData = json_decode($response, true);
    
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $text = $responseData['candidates'][0]['content']['parts'][0]['text'];
        $tokens = $responseData['usageMetadata']['totalTokenCount'] ?? 0;
        
        return [
            'success' => true,
            'text' => trim($text),
            'tokens_used' => $tokens
        ];
    } else {
        return [
            'success' => false,
            'error' => 'No text in response'
        ];
    }
}

function getFallbackResponse($userPrompt) {
    $lowerPrompt = strtolower($userPrompt);
    
    // Define fallback responses based on common questions
    $fallbacks = [
        'who' => [
            'match' => ['who created', 'who made', 'who built', 'creator', 'author', 'developer'],
            'response' => "👋 I'm NovaCloud AI! NovaCloud was created by **Abraham Mekonnen**, a Information Technology student at **Ambo University** in Ethiopia. This is an Internet Programming II project that demonstrates modern web development with cloud storage functionality.\n\nThe platform showcases skills in PHP, MySQL, JavaScript, and cloud architecture learned during the course."
        ],
        'what' => [
            'match' => ['what is novacloud', 'what is this', 'what does it do', 'purpose'],
            'response' => "🌩️ **NovaCloud** is a secure cloud storage platform that allows you to:\n\n• Store files securely with AES-256 encryption\n• Share files with others via secure links\n• Access files from any device with internet\n• Manage your storage through a user-friendly dashboard\n\nIt was created as an educational project for Ambo University's Internet Programming II course."
        ],
        'upload' => [
            'match' => ['upload', 'add file', 'store file', 'how to put'],
            'response' => "📤 **To upload files to NovaCloud:**\n\n1. **Login** to your account\n2. Go to the **Dashboard**\n3. Click the **Upload** button\n4. Select files from your device\n5. Files are **encrypted automatically** with AES-256\n6. Access them anytime from 'My Files'\n\n**Note:** Free accounts can upload files up to 100MB. Premium users get 2GB per file."
        ],
        'share' => [
            'match' => ['share', 'send file', 'give access', 'collaborate'],
            'response' => "🔗 **To share files on NovaCloud:**\n\n1. Go to **My Files** in your Dashboard\n2. Click on the file you want to share\n3. Click the **Share** button\n4. Choose sharing method:\n   - **Shareable Link**: Copy and send the generated link\n   - **Email Invite**: Enter recipient's email\n5. Set **permissions** (view/download) and **expiration date**\n\nAll shared files remain encrypted for security."
        ],
        'login' => [
            'match' => ['login', 'sign in', 'log in', 'access account'],
            'response' => "🔑 **To login to NovaCloud:**\n\n1. Click **Sign In** on the homepage\n2. Enter your **username or email**\n3. Enter your **password**\n4. Click **Login**\n5. You'll be redirected to your Dashboard\n\n**New user?** Click **Get Started** to register for a free account with 5GB storage.\n**Forgot password?** Use the 'Forgot Password' link on the login page."
        ],
        'price' => [
            'match' => ['price', 'cost', 'free', 'plan', 'subscription', 'how much'],
            'response' => "💰 **NovaCloud Pricing Plans:**\n\n**FREE TIER** (Perfect for starters):\n• 5GB storage\n• Basic file sharing\n• Web access only\n• Community support\n\n**PREMIUM** ($4.99/month):\n• 100GB storage\n• Advanced sharing features\n• Priority support\n• No file size limits\n\n**BUSINESS** ($14.99/month):\n• 1TB storage\n• Team collaboration\n• Admin controls\n• Dedicated support\n\nUpgrade anytime from your Dashboard!"
        ],
        'security' => [
            'match' => ['security', 'safe', 'encryption', 'privacy', 'protect'],
            'response' => "🛡️ **NovaCloud Security Features:**\n\n• **AES-256 Encryption**: Military-grade encryption for all files\n• **SSL/TLS**: Secure data transmission\n• **Secure Authentication**: Protected login system\n• **File Integrity**: Checksums to ensure file safety\n• **Regular Audits**: Security reviews and updates\n• **GDPR Compliant**: Data protection standards\n\nYour files are encrypted both during transfer and while stored on our servers."
        ],
        'university' => [
            'match' => ['ambo', 'university', 'school', 'college', 'education'],
            'response' => "🏛️ **Ambo University** is a higher education institution in Ethiopia where NovaCloud was developed. \n\nThis project is part of the **Internet Programming II** course curriculum, which teaches students:\n• Web development with PHP and MySQL\n• Frontend technologies (HTML, CSS, JavaScript)\n• Database design and management\n• Cloud computing concepts\n• Security best practices\n\nThe project demonstrates practical application of these skills in a real-world cloud storage platform."
        ],
        'feature' => [
            'match' => ['feature', 'what can', 'capability', 'function'],
            'response' => "✨ **NovaCloud Key Features:**\n\n**Core Features:**\n• Secure file storage with encryption\n• Easy file sharing with links\n• User-friendly dashboard\n• Cross-platform access\n\n**Advanced Features:**\n• File version history\n• Activity tracking\n• Admin management panel\n• Multi-language support\n• Responsive design\n\n**Security Features:**\n• AES-256 encryption\n• Secure authentication\n• Password-protected shares\n• Link expiration"
        ],
        'help' => [
            'match' => ['help', 'support', 'issue', 'problem', 'error'],
            'response' => "❓ **Need Help with NovaCloud?**\n\n**Quick Solutions:**\n1. **Can't login?** Try resetting your password\n2. **Upload failed?** Check file size limits\n3. **Share not working?** Verify link expiration\n4. **Slow speeds?** Check your internet connection\n\n**Contact Support:**\n• Email: support@novacloud.com\n• Check the **Help Center** for guides\n• Visit the **Dashboard** for account help\n\n**Emergency:** If you suspect security issues, contact us immediately!"
        ]
    ];
    
    // Check which fallback to use
    foreach ($fallbacks as $category => $fb) {
        foreach ($fb['match'] as $keyword) {
            if (strpos($lowerPrompt, $keyword) !== false) {
                return $fb['response'];
            }
        }
    }
    
    // Default fallback response
    return "Hello! I'm **NovaCloud AI**, your assistant for the NovaCloud cloud storage platform. \n\nI can help you with:\n• Account setup and management\n• File uploading and sharing\n• Understanding storage plans\n• Technical guidance\n• Security information\n\nPlease ask me specific questions about using NovaCloud! Try:\n• \"How do I upload files?\"\n• \"Who created NovaCloud?\"\n• \"What are the pricing plans?\"\n• \"How secure is my data?\"";
}
?>
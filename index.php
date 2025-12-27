<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

$session = new SessionManager();
$functions = new NovaCloudFunctions();

$isLoggedIn = $session->isLoggedIn();

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-key="site_name">NovaCloud - Secure Cloud Storage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>

/* Reset and Base Styles */
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
    overflow-x: hidden;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Navigation Styles */
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

/* Hero Section */
.hero-section {
    min-height: 100vh;
    padding: 140px 20px 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.hero-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.hero-content {
    color: white;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 10px 20px;
    border-radius: 50px;
    margin-bottom: 30px;
    font-size: 0.9rem;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 20px;
}

.gradient-text {
    background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-description {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 40px;
    max-width: 500px;
}

.hero-stats {
    display: flex;
    gap: 40px;
    margin-bottom: 40px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-item i {
    font-size: 2.5rem;
    opacity: 0.8;
}

.stat-item h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-item p {
    font-size: 0.9rem;
    opacity: 0.8;
}

.hero-actions {
    display: flex;
    gap: 20px;
    margin-bottom: 40px;
}

.btn-hero {
    padding: 16px 32px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.btn-primary {
    background: white;
    color: #4f46e5;
    border: none;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.btn-secondary {
    background: transparent;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: white;
}

.btn-success {
    background: #10b981;
    color: white;
    border: none;
}

.btn-success:hover {
    background: #059669;
    transform: translateY(-3px);
}

.trusted-by p {
    margin-bottom: 15px;
    opacity: 0.8;
}

.trusted-logos {
    display: flex;
    gap: 30px;
    font-size: 2rem;
    opacity: 0.7;
}

.hero-visual {
    position: relative;
}

.dashboard-preview {
    position: relative;
    z-index: 2;
}

.dashboard-window {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 40px 80px rgba(0, 0, 0, 0.3);
    transform: perspective(1000px) rotateY(-10deg);
}

.window-header {
    background: #1e293b;
    color: white;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.window-controls {
    display: flex;
    gap: 8px;
}

.control {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.control.red { background: #ef4444; }
.control.yellow { background: #f59e0b; }
.control.green { background: #10b981; }

.window-content {
    height: 300px;
    background: #0f172a;
    position: relative;
    overflow: hidden;
}

.floating-card {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    color: white;
    animation: float 6s ease-in-out infinite;
}

.floating-card i {
    font-size: 2rem;
}

.file-card-1 {
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.file-card-2 {
    top: 60%;
    right: 15%;
    animation-delay: 1s;
}

.file-card-3 {
    top: 40%;
    left: 50%;
    animation-delay: 2s;
}

.folder-card {
    bottom: 20%;
    left: 30%;
    animation-delay: 3s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
    }
}

.hero-background {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1;
}

.bg-circle {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
}

.circle-1 {
    width: 400px;
    height: 400px;
    top: -200px;
    right: -100px;
}

.circle-2 {
    width: 300px;
    height: 300px;
    bottom: -150px;
    left: -100px;
}

.circle-3 {
    width: 200px;
    height: 200px;
    top: 50%;
    right: 30%;
}

.scroll-indicator {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    color: white;
    opacity: 0.8;
}

.mouse {
    width: 26px;
    height: 40px;
    border: 2px solid white;
    border-radius: 20px;
    margin: 10px auto;
    position: relative;
}

.wheel {
    width: 4px;
    height: 8px;
    background: white;
    border-radius: 2px;
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    animation: scroll 2s ease infinite;
}

@keyframes scroll {
    0%, 100% {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
    50% {
        transform: translateX(-50%) translateY(10px);
        opacity: 0.5;
    }
}

/* Section Common Styles */
.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.section-subtitle {
    color: #6b7280;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

/* Features Section */
.features-section {
    padding: 100px 20px;
    background: white;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
}

.feature-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 40px 30px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.feature-icon {
    position: relative;
    margin-bottom: 25px;
}

.icon-wrapper {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    position: relative;
    z-index: 2;
}

.icon-bg {
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 25px;
    opacity: 0.1;
}

.feature-card h3 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: #1f2937;
}

.feature-card p {
    color: #6b7280;
    margin-bottom: 20px;
    line-height: 1.7;
}

.feature-badge {
    display: inline-block;
    padding: 6px 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.feature-stats {
    color: #10b981;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.platform-icons {
    display: flex;
    gap: 15px;
    font-size: 1.5rem;
    color: #6b7280;
    margin-top: 10px;
}

/* About Section */
.about-section {
    padding: 100px 20px;
    background: #f8fafc;
}

.about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.about-description {
    color: #6b7280;
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 40px;
}

.about-features {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-bottom: 40px;
}

.about-feature {
    display: flex;
    align-items: center;
    gap: 20px;
}

.about-feature i {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.about-feature h4 {
    font-size: 1.2rem;
    margin-bottom: 5px;
    color: #1f2937;
}

.about-feature p {
    color: #6b7280;
    font-size: 0.95rem;
}

.btn-about {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-about:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.about-visual {
    position: relative;
    height: 400px;
}

.world-map {
    position: relative;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%);
    border-radius: 20px;
    overflow: hidden;
}

.location-pin {
    position: absolute;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.8rem;
    font-weight: 600;
    animation: pulse 2s infinite;
}

.location-pin::after {
    content: '';
    position: absolute;
    width: 60px;
    height: 60px;
    border: 2px solid rgba(102, 126, 234, 0.3);
    border-radius: 50%;
    animation: ripple 2s infinite;
}

.pin-1 {
    top: 30%;
    left: 20%;
}

.pin-2 {
    top: 40%;
    left: 50%;
}

.pin-3 {
    top: 60%;
    left: 70%;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

@keyframes ripple {
    0% {
        transform: scale(0.8);
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

/* Pricing Section */
.pricing-section {
    padding: 100px 20px;
    background: white;
}

.billing-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
}

.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #e5e7eb;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.discount-badge {
    background: #10b981;
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    margin-left: 5px;
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-top: 50px;
}

.pricing-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
    position: relative;
}

.pricing-card.popular {
    border-color: #667eea;
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15);
}

.popular-badge {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pricing-header {
    text-align: center;
    margin-bottom: 40px;
}

.pricing-header h3 {
    font-size: 1.8rem;
    margin-bottom: 20px;
    color: #1f2937;
}

.price {
    display: flex;
    align-items: baseline;
    justify-content: center;
    margin-bottom: 10px;
}

.currency {
    font-size: 1.5rem;
    font-weight: 600;
    color: #6b7280;
}

.amount {
    font-size: 3.5rem;
    font-weight: 800;
    color: #1f2937;
    margin: 0 5px;
}

.period {
    color: #6b7280;
    font-size: 1rem;
}

.price-description {
    color: #6b7280;
    font-size: 0.95rem;
}

.pricing-features {
    list-style: none;
    margin-bottom: 40px;
}

.pricing-features li {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
    color: #6b7280;
}

.pricing-features li i {
    color: #10b981;
    font-size: 1.1rem;
}

.pricing-features li i.fa-times {
    color: #ef4444;
}

.btn-pricing {
    display: block;
    width: 100%;
    padding: 16px;
    text-align: center;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid #e5e7eb;
    color: #4b5563;
}

.btn-pricing:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.btn-popular {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white !important;
    border: none !important;
}

.btn-popular:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.pricing-footer {
    text-align: center;
    margin-top: 50px;
    padding-top: 30px;
    border-top: 1px solid #e5e7eb;
    color: #6b7280;
}

.pricing-footer i {
    color: #10b981;
    margin-right: 10px;
}

/* Testimonials Section */
.testimonials-section {
    padding: 100px 20px;
    background: #f8fafc;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
}

.testimonial-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.stars {
    color: #fbbf24;
    font-size: 1.2rem;
    margin-bottom: 20px;
}

.testimonial-text {
    color: #4b5563;
    font-style: italic;
    line-height: 1.7;
    margin-bottom: 30px;
    font-size: 1.05rem;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 20px;
}

.author-avatar {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
}

.author-info h4 {
    font-size: 1.1rem;
    margin-bottom: 5px;
    color: #1f2937;
}

.author-info p {
    color: #6b7280;
    font-size: 0.9rem;
}

/* FAQ Section */
.faq-section {
    padding: 100px 20px;
    background: white;
}

.faq-grid {
    max-width: 800px;
    margin: 0 auto;
}

.faq-item {
    border-bottom: 1px solid #e5e7eb;
    padding: 25px 0;
    transition: all 0.3s ease;
}

.faq-item.active {
    padding-bottom: 40px;
}

.faq-question {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.faq-question:hover h3 {
    color: #4f46e5;
}

.faq-question h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #1f2937;
    transition: all 0.3s ease;
    flex: 1;
}

.faq-question i {
    color: #6b7280;
    transition: transform 0.3s ease;
    font-size: 1.2rem;
}

.faq-item.active .faq-question i {
    transform: rotate(180deg);
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    color: #6b7280;
    line-height: 1.7;
    margin-top: 0;
}

.faq-item.active .faq-answer {
    max-height: 200px;
    margin-top: 20px;
}

.faq-cta {
    text-align: center;
    margin-top: 60px;
}

.faq-cta p {
    font-size: 1.2rem;
    margin-bottom: 20px;
    color: #4b5563;
}

.btn-faq {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-faq:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

/* CTA Section */
.cta-section {
    padding: 100px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.cta-description {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto 40px;
}

.cta-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-bottom: 40px;
}

.btn-cta-primary, .btn-cta-secondary {
    padding: 16px 32px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.btn-cta-primary {
    background: white;
    color: #4f46e5;
    border: none;
}

.btn-cta-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.btn-cta-secondary {
    background: transparent;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-cta-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: white;
}

.cta-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap;
}

.stat {
    display: flex;
    align-items: center;
    gap: 10px;
    opacity: 0.9;
}

.stat i {
    font-size: 1.2rem;
}

/* Footer */
.footer-section {
    background: #0f172a;
    color: white;
    padding: 80px 20px 40px;
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 40px;
    margin-bottom: 60px;
}

.footer-brand {
    grid-column: span 2;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: white;
}

.footer-tagline {
    opacity: 0.7;
    margin-bottom: 30px;
    line-height: 1.6;
}

.footer-social {
    display: flex;
    gap: 15px;
}

.social-link {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: translateY(-3px);
}

.footer-col h4 {
    font-size: 1.1rem;
    margin-bottom: 25px;
    color: white;
    font-weight: 600;
}

.footer-col ul {
    list-style: none;
}

.footer-col ul li {
    margin-bottom: 15px;
}

.footer-col ul li a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.footer-col ul li a:hover {
    color: white;
    padding-left: 5px;
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.footer-copyright {
    opacity: 0.7;
    font-size: 0.9rem;
}

.footer-links {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.footer-links a:hover {
    color: white;
}

.separator {
    opacity: 0.3;
}

.language-selector-small select {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    outline: none;
}

.language-selector-small select option {
    background: #0f172a;
    color: white;
}

/* Back to Top Button */
.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: all 0.3s ease;
    z-index: 999;
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.back-to-top:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .hero-title {
        font-size: 2.8rem;
    }
    
    .features-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .testimonials-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .footer-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .footer-brand {
        grid-column: span 3;
    }
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
    
    .hero-container {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .hero-stats {
        justify-content: center;
    }
    
    .hero-actions {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .about-grid {
        grid-template-columns: 1fr;
    }
    
    .pricing-grid {
        grid-template-columns: 1fr;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .features-grid,
    .testimonials-grid {
        grid-template-columns: 1fr;
    }
    
    .cta-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .footer-grid {
        grid-template-columns: 1fr;
    }
    
    .footer-brand {
        grid-column: span 1;
    }
    
    .footer-bottom {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
}

@media (max-width: 480px) {
    .hero-title {
        font-size: 2.2rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 20px;
    }
    
    .cta-stats {
        flex-direction: column;
        gap: 20px;
    }
}

    </style>


</head>
<body>
    <!-- Modern Navigation -->
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
                <span class="logo-text" data-key="site_name">NovaCloud</span>
            </div>
            
            <div class="nav-menu">
                <div class="nav-links">
                    <a href="#home" class="nav-link active" data-key="home">Home</a>
                    
                    <a href="about.php" class="nav-link" data-key="about">About</a>
                    <a href="help.php" class="nav-link" data-key="help">Help</a>
                    
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

    <!-- Enhanced Hero Section -->
    <section class="hero-section" id="home">
        <div class="hero-container">
            <div class="hero-content animate__animated animate__fadeInLeft">
                <div class="hero-badge" data-key="hero_badge">
                    <i class="fas fa-star"></i>
                    <span>Trusted by 10,000+ Businesses</span>
                </div>
                
                <h1 class="hero-title" data-key="home_title">
                    <span class="gradient-text">Secure Cloud Storage</span> for Modern Teams
                </h1>
                
                <p class="hero-description" data-key="home_description">
                    Store, share, and collaborate on files with enterprise-grade security, lightning-fast sync, and seamless team collaboration.
                </p>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <h3>256-bit</h3>
                            <p data-key="encryption">Encryption</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-bolt"></i>
                        <div>
                            <h3 data-key="instant_sync">Instant</h3>
                            <p data-key="sync">Sync</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-globe"></i>
                        <div>
                            <h3>99.9%</h3>
                            <p data-key="global_uptime">Global Uptime</p>
                        </div>
                    </div>
                </div>
                
                <div class="hero-actions">
                    <?php if (!$isLoggedIn): ?>
                        <a href="register.php" class="btn-primary btn-hero" data-key="register_button">
                            <i class="fas fa-rocket"></i> Start Free Trial
                        </a>
                        <a href="#features" class="btn-secondary btn-hero" data-key="learn_more">
                            <i class="fas fa-play-circle"></i> Watch Demo
                        </a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn-primary btn-hero" data-key="dashboard">
                            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                        </a>
                        <a href="#" onclick="document.getElementById('uploadTrigger').click()" 
                           class="btn-success btn-hero" data-key="upload_files">
                            <i class="fas fa-cloud-upload-alt"></i> Upload Files
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="trusted-by">
                    <p data-key="trusted_by">Trusted by teams at:</p>
                    <div class="trusted-logos">
                        <i class="fas fa-building"></i>
                        <i class="fas fa-university"></i>
                        <i class="fas fa-graduation-cap"></i>
                        <i class="fas fa-briefcase"></i>
                    </div>
                </div>
            </div>
            
            <div class="hero-visual animate__animated animate__fadeInRight">
                <div class="dashboard-preview">
                    <div class="dashboard-window">
                        <div class="window-header">
                            <div class="window-controls">
                                <span class="control red"></span>
                                <span class="control yellow"></span>
                                <span class="control green"></span>
                            </div>
                            <span data-key="dashboard_preview">Dashboard Preview</span>
                        </div>
                        <div class="window-content">
                            <!-- Floating file cards -->
                            <div class="floating-card file-card-1">
                                <i class="fas fa-file-pdf"></i>
                                <span>Annual_Report.pdf</span>
                                <div class="file-size">2.4 MB</div>
                            </div>
                            <div class="floating-card file-card-2">
                                <i class="fas fa-file-image"></i>
                                <span>Team_Photo.jpg</span>
                                <div class="file-size">5.1 MB</div>
                            </div>
                            <div class="floating-card file-card-3">
                                <i class="fas fa-file-video"></i>
                                <span>Presentation.mp4</span>
                                <div class="file-size">48.2 MB</div>
                            </div>
                            <div class="floating-card folder-card">
                                <i class="fas fa-folder"></i>
                                <span>Projects</span>
                                <div class="file-count">24 files</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Animated background elements -->
                <div class="hero-background">
                    <div class="bg-circle circle-1"></div>
                    <div class="bg-circle circle-2"></div>
                    <div class="bg-circle circle-3"></div>
                </div>
            </div>
        </div>
        
        <!-- Scroll indicator -->
        <div class="scroll-indicator">
            <span data-key="scroll_to_explore">Scroll to explore</span>
            <div class="mouse">
                <div class="wheel"></div>
            </div>
        </div>
    </section>

    <!-- Features Section with Animation -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" data-key="features_title">Powerful Features for Your Workflow</h2>
                <p class="section-subtitle" data-key="features_subtitle">
                    Everything you need to store, sync, and share files securely
                </p>
            </div>
            
            <div class="features-grid">
                <!-- Feature 1 -->
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="icon-bg"></div>
                    </div>
                    <h3 data-key="security">Military-Grade Security</h3>
                    <p data-key="security_desc">
                        End-to-end encryption with zero-knowledge architecture. Your data stays private, even from us.
                    </p>
                    <div class="feature-badge" data-key="enterprise_grade">Enterprise Grade</div>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="icon-bg"></div>
                    </div>
                    <h3 data-key="real_time_sync">Real-Time Sync</h3>
                    <p data-key="sync_desc">
                        Changes sync instantly across all your devices. Work seamlessly anywhere, anytime.
                    </p>
                    <div class="feature-stats">
                        <span><i class="fas fa-bolt"></i> < 1s sync</span>
                    </div>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="icon-bg"></div>
                    </div>
                    <h3 data-key="team_collaboration">Team Collaboration</h3>
                    <p data-key="collab_desc">
                        Share files, set permissions, and collaborate in real-time with your entire team.
                    </p>
                    <div class="feature-badge" data-key="new_feature">New</div>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="icon-bg"></div>
                    </div>
                    <h3 data-key="cross_platform">Cross-Platform</h3>
                    <p data-key="cross_platform_desc">
                        Native apps for Windows, Mac, iOS, Android, and web. Access files from any device.
                    </p>
                    <div class="platform-icons">
                        <i class="fab fa-windows"></i>
                        <i class="fab fa-apple"></i>
                        <i class="fab fa-android"></i>
                        <i class="fab fa-linux"></i>
                    </div>
                </div>
                
                <!-- Feature 5 -->
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-infinity"></i>
                        </div>
                        <div class="icon-bg"></div>
                    </div>
                    <h3 data-key="unlimited_versions">Unlimited Versions</h3>
                    <p data-key="versions_desc">
                        Track every change with unlimited file versioning. Restore any previous version with one click.
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="icon-bg"></div>
                    </div>
                    <h3 data-key="analytics">Advanced Analytics</h3>
                    <p data-key="analytics_desc">
                        Get insights into file usage, storage trends, and team collaboration patterns.
                    </p>
                    <div class="feature-badge" data-key="pro_feature">Pro Feature</div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-content animate-on-scroll">
                    <h2 class="section-title" data-key="about_title">Why Businesses Choose NovaCloud</h2>
                    <p class="about-description" data-key="about_description">
                        We're on a mission to make secure cloud storage accessible to everyone. With data centers across 3 continents and 24/7 support, we ensure your data is always safe and available.
                    </p>
                    
                    <div class="about-features">
                        <div class="about-feature">
                            <i class="fas fa-server"></i>
                            <div>
                                <h4 data-key="global_infrastructure">Global Infrastructure</h4>
                                <p data-key="global_infra_desc">Data centers in US, EU, and Asia for low latency</p>
                            </div>
                        </div>
                        <div class="about-feature">
                            <i class="fas fa-headset"></i>
                            <div>
                                <h4 data-key="support_24_7">24/7 Support</h4>
                                <p data-key="support_desc">Round-the-clock support via chat, email, and phone</p>
                            </div>
                        </div>
                        <div class="about-feature">
                            <i class="fas fa-certificate"></i>
                            <div>
                                <h4 data-key="compliance">Compliance</h4>
                                <p data-key="compliance_desc">GDPR, HIPAA, SOC2 Type II certified</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="#pricing" class="btn-about" data-key="see_pricing">
                        See Pricing Plans <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="about-visual animate-on-scroll">
                    <div class="world-map">
                        <div class="location-pin pin-1">
                            <span>Ethiopia</span>
                        </div>
                        <div class="location-pin pin-2">
                            <span>A.A</span>
                        </div>
                        <div class="location-pin pin-3">
                            <span>Ambo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section (Enhanced) -->
    <section class="pricing-section" id="pricing">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" data-key="pricing_title">Simple, Transparent Pricing</h2>
                <p class="section-subtitle" data-key="pricing_subtitle">
                    No hidden fees. Cancel anytime. All plans include 14-day free trial.
                </p>
                <div class="billing-toggle">
                    <span data-key="monthly">Monthly</span>
                    <label class="switch">
                        <input type="checkbox" id="billingToggle">
                        <span class="slider"></span>
                    </label>
                    <span data-key="yearly">Yearly <span class="discount-badge" data-key="save_20">Save 20%</span></span>
                </div>
            </div>
            
            <div class="pricing-grid">
                <!-- Free Plan -->
                <div class="pricing-card animate-on-scroll">
                    <div class="pricing-header">
                        <h3 data-key="free">Free</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount">0</span>
                            <span class="period" data-key="month">/month</span>
                        </div>
                        <p class="price-description" data-key="for_individuals">For individuals</p>
                    </div>
                    
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> <span data-key="storage_5gb">5GB Storage</span></li>
                        <li><i class="fas fa-check"></i> <span data-key="basic_sharing">Basic file sharing</span></li>
                        <li><i class="fas fa-check"></i> <span data-key="file_preview">File preview</span></li>
                        <li><i class="fas fa-times"></i> <span data-key="team_collab">Team collaboration</span></li>
                        <li><i class="fas fa-times"></i> <span data-key="version_history">Version history</span></li>
                    </ul>
                    
                    <a href="register.php" class="btn-pricing" data-key="get_started_free">
                        Get Started Free
                    </a>
                </div>
                
                <!-- Pro Plan -->
                <div class="pricing-card popular animate-on-scroll">
                    <div class="popular-badge" data-key="most_popular">
                        <i class="fas fa-crown"></i> Most Popular
                    </div>
                    <div class="pricing-header">
                        <h3 data-key="pro">Pro</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount">9</span>
                            <span class="period" data-key="month">/month</span>
                        </div>
                        <p class="price-description" data-key="for_teams">For teams & professionals</p>
                    </div>
                    
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> <span data-key="storage_100gb">100GB Storage</span></li>
                        <li><i class="fas fa-check"></i> <span data-key="advanced_sharing">Advanced sharing</span></li>
                        <li><i class="fas fa-check"></i> <span data-key="file_versioning">File versioning</span></li>
                        <li><i class="fas fa-check"></i> <span data-key="password_protect">Password protection</span></li>
                        <li><i class="fas fa-check"></i> <span data-key="priority_support">Priority support</span></li>
                    </ul>
                    
                    <a href="register.php?plan=pro" class="btn-pricing btn-popular" data-key="start_free_trial">
                        Start Free Trial <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <!-- Enterprise Plan -->
                <div class="pricing-card animate-on-scroll">
                    <div class="pricing-header">
                        <h3 data-key="enterprise">Enterprise</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount">29</span>
                            <span class="period" data-key="month">/month</span>
                        </div>
                        <p class="price-description" data-key="for_business">For large businesses</p>
                    </div>
                    
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> <span data-key="storage_unlimited">Unlimited storage</span></li>
                        <li><i class="fas fa-check"></i> <span data-key="team_collab">Team collaboration</span></li>
                        <li><i class="fas fa-check"></i> <span data-key="admin_controls">Admin controls</span></li>
                        <li><i class="fas fa-check"></i> <span data-key="custom_branding">Custom branding</span></li>
                        <li><i class="fas fa-check"></i> <span data-key="dedicated_support">Dedicated support</span></li>
                    </ul>
                    
                    <a href="contact.php" class="btn-pricing" data-key="contact_sales">
                        Contact Sales <i class="fas fa-headset"></i>
                    </a>
                </div>
            </div>
            
            <div class="pricing-footer">
                <p data-key="all_plans_include">
                    <i class="fas fa-check-circle"></i> All plans include: 256-bit encryption, mobile apps, web access, and 99.9% uptime SLA
                </p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section" id="testimonials">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" data-key="testimonials_title">Loved by Teams Worldwide</h2>
                <p class="section-subtitle" data-key="testimonials_subtitle">
                    See what our customers say about their experience
                </p>
            </div>
            
            <div class="testimonials-grid">
                <div class="testimonial-card animate-on-scroll">
                    <div class="testimonial-content">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text" data-key="testimonial_1">
                            "NovaCloud transformed how our remote team collaborates. The security features give us peace of mind."
                        </p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="author-info">
                            <h4 data-key="author_1_name">Alex Johnson</h4>
                            <p data-key="author_1_role">CTO at TechCorp</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card animate-on-scroll">
                    <div class="testimonial-content">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="testimonial-text" data-key="testimonial_2">
                            "The sync speed is incredible. Our team across 5 countries can now work together seamlessly."
                        </p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="author-info">
                            <h4 data-key="author_2_name">Sarah Chen</h4>
                            <p data-key="author_2_role">Research Director, University</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card animate-on-scroll">
                    <div class="testimonial-content">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text" data-key="testimonial_3">
                            "We switched from three different tools to NovaCloud. The simplicity and power are unmatched."
                        </p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="author-info">
                            <h4 data-key="author_3_name">Dr. Michael Rodriguez</h4>
                            <p data-key="author_3_role">Healthcare System Admin</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section" id="faq">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" data-key="faq_title">Frequently Asked Questions</h2>
                <p class="section-subtitle" data-key="faq_subtitle">
                    Find answers to common questions about NovaCloud
                </p>
            </div>
            
            <div class="faq-grid">
                <div class="faq-item animate-on-scroll">
                    <div class="faq-question">
                        <h3 data-key="faq_1_q">Is my data secure with NovaCloud?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p data-key="faq_1_a">
                            Yes! We use 256-bit end-to-end encryption with zero-knowledge architecture. Your files are encrypted before they leave your device, and only you hold the keys.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item animate-on-scroll">
                    <div class="faq-question">
                        <h3 data-key="faq_2_q">Can I access my files offline?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p data-key="faq_2_a">
                            Absolutely! Our desktop and mobile apps allow you to mark files for offline access. Any changes sync automatically when you're back online.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item animate-on-scroll">
                    <div class="faq-question">
                        <h3 data-key="faq_3_q">How many devices can I use?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p data-key="faq_3_a">
                            All plans support unlimited devices. You can access your files from as many computers, phones, and tablets as you need.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item animate-on-scroll">
                    <div class="faq-question">
                        <h3 data-key="faq_4_q">What happens if I cancel?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p data-key="faq_4_a">
                            You can cancel anytime. After cancellation, you can still access and download your files for 30 days. Your data is never deleted immediately.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-cta">
                <p data-key="faq_more_questions">Still have questions?</p>
                <a href="contact.php" class="btn-faq" data-key="contact_support">
                    Contact Support <i class="fas fa-headset"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content animate-on-scroll">
                <h2 class="cta-title" data-key="cta_title">Ready to secure your files?</h2>
                <p class="cta-description" data-key="cta_description">
                    Join 10,000+ teams who trust NovaCloud with their data. Start your free 14-day trial today.
                </p>
                
                <div class="cta-actions">
                    <?php if (!$isLoggedIn): ?>
                        <a href="register.php" class="btn-cta-primary" data-key="start_free_trial">
                            <i class="fas fa-rocket"></i> Start Free Trial
                        </a>
                        <a href="#features" class="btn-cta-secondary" data-key="schedule_demo">
                            <i class="fas fa-calendar"></i> Schedule a Demo
                        </a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn-cta-primary" data-key="go_to_dashboard">
                            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                        </a>
                        <a href="upgrade.php" class="btn-cta-secondary" data-key="upgrade_plan">
                            <i class="fas fa-crown"></i> Upgrade Plan
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="cta-stats">
                    <div class="stat">
                        <i class="fas fa-clock"></i>
                        <span data-key="setup_time">Setup in 5 minutes</span>
                    </div>
                    <div class="stat">
                        <i class="fas fa-credit-card"></i>
                        <span data-key="no_credit_card">No credit card required</span>
                    </div>
                    <div class="stat">
                        <i class="fas fa-shield-alt"></i>
                        <span data-key="cancel_anytime">Cancel anytime</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Footer -->
    <footer class="footer-section">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col footer-brand">
                    <div class="footer-logo">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span data-key="site_name">NovaCloud</span>
                    </div>
                    <p class="footer-tagline" data-key="footer_tagline">
                        Secure cloud storage for individuals, teams, and enterprises.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4 data-key="product">Product</h4>
                    <ul>
                        <li><a href="#features" data-key="features">Features</a></li>
                        <li><a href="#pricing" data-key="pricing">Pricing</a></li>
                        <li><a href="#" data-key="security">Security</a></li>
                        <li><a href="#" data-key="api_docs">API & Docs</a></li>
                        <li><a href="#" data-key="download_apps">Download Apps</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4 data-key="company">Company</h4>
                    <ul>
                        <li><a href="#about" data-key="about">About</a></li>
                        <li><a href="#" data-key="blog">Blog</a></li>
                        <li><a href="#" data-key="careers">Careers</a></li>
                        <li><a href="#" data-key="press">Press</a></li>
                        <li><a href="#" data-key="contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4 data-key="support">Support</h4>
                    <ul>
                        <li><a href="#faq" data-key="faq">FAQ</a></li>
                        <li><a href="#" data-key="help_center">Help Center</a></li>
                        <li><a href="#" data-key="community">Community</a></li>
                        <li><a href="#" data-key="status">System Status</a></li>
                        <li><a href="#" data-key="contact_support">Contact Support</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4 data-key="legal">Legal</h4>
                    <ul>
                        <li><a href="#" data-key="privacy_policy">Privacy Policy</a></li>
                        <li><a href="#" data-key="terms_of_service">Terms of Service</a></li>
                        <li><a href="#" data-key="cookie_policy">Cookie Policy</a></li>
                        <li><a href="#" data-key="gdpr">GDPR</a></li>
                        <li><a href="#" data-key="compliance">Compliance</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p data-key="copyright">&copy; <?php echo date('Y'); ?> NovaCloud. All rights reserved.</p>
                </div>
                
                <div class="footer-links">
                    <a href="#" data-key="sitemap">Sitemap</a>
                    <span class="separator">•</span>
                    <a href="#" data-key="cookie_settings">Cookie Settings</a>
                    <span class="separator">•</span>
                    <div class="language-selector-small">
                        <select id="footerLanguageSelect">
                            <option value="en">English</option>
                            <option value="am">አማርኛ</option>
                            <option value="om">Afaan Oromoo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Hidden Upload Trigger -->
    <input type="file" id="uploadTrigger" style="display: none;" multiple>

    <!-- React Components Container -->
    <div id="react-components"></div>

    <!-- Scripts -->
    <script src="assets/js/language-switcher.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Scroll Reveal Animations
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize ScrollReveal
            const sr = ScrollReveal({
                origin: 'bottom',
                distance: '60px',
                duration: 1000,
                delay: 200,
                easing: 'cubic-bezier(0.5, 0, 0, 1)',
                reset: false
            });
            
            // Configure animations
            sr.reveal('.animate-on-scroll', {
                interval: 200
            });
            
            sr.reveal('.hero-content', {
                origin: 'left',
                distance: '100px'
            });
            
            sr.reveal('.hero-visual', {
                origin: 'right',
                distance: '100px'
            });
            
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
            
            mobileToggle.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                mobileToggle.classList.toggle('active');
            });
            
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
                        navMenu.classList.remove('active');
                        mobileToggle.classList.remove('active');
                    }
                });
            });
            
            // FAQ accordion
            document.querySelectorAll('.faq-question').forEach(question => {
                question.addEventListener('click', () => {
                    const item = question.parentElement;
                    item.classList.toggle('active');
                });
            });
            
            // Back to top button
            const backToTop = document.querySelector('.back-to-top');
            
            window.addEventListener('scroll', () => {
                if (window.scrollY > 500) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
            });
            
            backToTop.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Billing toggle
            const billingToggle = document.getElementById('billingToggle');
            const monthlyPrices = document.querySelectorAll('.price .amount');
            const yearlyPrices = ['0', '7', '23']; // Yearly prices
            
            billingToggle.addEventListener('change', function() {
                monthlyPrices.forEach((price, index) => {
                    if (this.checked) {
                        price.textContent = yearlyPrices[index];
                    } else {
                        const originalPrices = ['0', '9', '29'];
                        price.textContent = originalPrices[index];
                    }
                });
            });
        });
    </script>
    
    <script type="text/babel">
        // React Components
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
<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
ini_set('session.use_strict_mode', 1);
session_start();
require_once 'lib/auth.php';
require_once 'lib/functions.php';
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid request. CSRF token mismatch.');
    }
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // jika tidak ada pilihan role maka role akan diisi dengan role: admin
    $role = $_POST['role'] ?? 'mahasiswa';
    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Validate password strength
        $passwordErrors = validatePassword($password, false); // ganti menjadi: false agar bebas membuat password
        if (!empty($passwordErrors)) {
            $error = implode('', $passwordErrors);
        } else {
            if (registerUser($username, $password, $role)) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Username already exists or registration failed.";
            }
        }
    }
}
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DURALUX Resto Jepang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-navy: #2c3e50;
            --secondary-navy: #34495e;
            --dark-navy: #1a252f;
            --light-navy: #3d5568;
            --accent-blue: #3498db;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #ecf0f1;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Decorative Background Pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }
        
        /* Floating geometric shapes */
        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            top: -200px;
            right: -200px;
            animation: float 25s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(-40px, 40px) rotate(120deg); }
            66% { transform: translate(40px, -20px) rotate(240deg); }
        }
        
        .register-container {
            position: relative;
            z-index: 1;
            padding: 20px;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            border: none;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--primary-navy) 0%, var(--secondary-navy) 100%);
            padding: 45px 35px 35px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background pattern */
        .register-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 10px,
                    rgba(255, 255, 255, 0.03) 10px,
                    rgba(255, 255, 255, 0.03) 20px
                );
            animation: slide 20s linear infinite;
        }
        
        @keyframes slide {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .register-header::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 75px;
            height: 75px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }
        
        .logo-container {
            position: relative;
            z-index: 3;
            margin-bottom: 20px;
        }
        
        .logo-icon {
            width: 90px;
            height: 90px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 3;
            border: 4px solid rgba(255, 255, 255, 0.2);
        }
        
        .logo-icon i {
            font-size: 45px;
            color: var(--primary-navy);
        }
        
        .brand-name {
            color: white;
            font-size: 34px;
            font-weight: 700;
            margin: 0 0 5px 0;
            font-family: 'Poppins', sans-serif;
            text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.3);
            letter-spacing: 3px;
            position: relative;
            z-index: 3;
        }
        
        .app-title {
            color: rgba(255, 255, 255, 0.95);
            font-size: 16px;
            font-weight: 500;
            margin: 0;
            font-family: 'Noto Sans JP', sans-serif;
            position: relative;
            z-index: 3;
        }
        
        .register-body {
            padding: 55px 45px 45px;
            background: white;
        }
        
        .welcome-text {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .welcome-text h4 {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 24px;
        }
        
        .welcome-text p {
            color: var(--text-light);
            font-size: 14px;
            margin: 0;
        }
        
        .form-floating {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-floating > .form-control,
        .form-floating > .form-select {
            border: 2px solid #e8ecef;
            border-radius: 14px;
            padding: 1.1rem 1.1rem 1.1rem 3.5rem;
            height: 60px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            font-size: 15px;
        }
        
        .form-floating > .form-select {
            padding: 1.1rem 1.1rem 1.1rem 3.5rem;
        }
        
        .form-floating > .form-control:focus,
        .form-floating > .form-select:focus {
            border-color: var(--primary-navy);
            box-shadow: 0 0 0 0.25rem rgba(44, 62, 80, 0.12);
            background: white;
        }
        
        .form-floating > label {
            padding-left: 3.5rem;
            color: #95a5a6;
            font-size: 14px;
        }
        
        .input-icon {
            position: absolute;
            left: 24px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
            z-index: 5;
            transition: all 0.3s ease;
            font-size: 18px;
        }
        
        .form-floating > .form-control:focus ~ .input-icon,
        .form-floating > .form-select:focus ~ .input-icon {
            color: var(--primary-navy);
            transform: translateY(-50%) scale(1.15);
        }
        
        .password-hint {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 8px;
            padding-left: 5px;
        }
        
        .password-hint i {
            margin-right: 5px;
        }
        
        .btn-register {
            background: linear-gradient(135deg, var(--primary-navy) 0%, var(--secondary-navy) 100%);
            border: none;
            border-radius: 14px;
            padding: 17px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(44, 62, 80, 0.35);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-register:hover::before {
            left: 100%;
        }
        
        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(44, 62, 80, 0.45);
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
        }
        
        .btn-register:active {
            transform: translateY(-1px);
        }
        
        .divider {
            text-align: center;
            margin: 28px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, #dfe6e9, transparent);
        }
        
        .divider span {
            background: white;
            padding: 0 20px;
            color: #95a5a6;
            font-size: 13px;
            position: relative;
            z-index: 1;
            font-weight: 500;
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .login-link a {
            color: var(--primary-navy);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 15px;
        }
        
        .login-link a:hover {
            color: var(--secondary-navy);
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 25px;
            padding: 16px 20px;
            animation: slideDown 0.4s ease;
        }
        
        .alert-danger {
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.2);
        }
        
        .alert-success {
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.2);
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .footer-text {
            text-align: center;
            margin-top: 40px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.4);
            font-weight: 300;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .register-body {
                padding: 55px 30px 40px;
            }
            
            .brand-name {
                font-size: 28px;
                letter-spacing: 2px;
            }
            
            .app-title {
                font-size: 14px;
            }
            
            .register-header {
                padding: 40px 25px 35px;
            }
            
            .logo-icon {
                width: 80px;
                height: 80px;
            }
            
            .logo-icon i {
                font-size: 40px;
            }
            
            .welcome-text h4 {
                font-size: 20px;
            }
        }
        
        /* Loading animation */
        .btn-register.loading {
            pointer-events: none;
        }
        
        .btn-register.loading::after {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            top: 50%;
            right: 28px;
            margin-top: -9px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spinner 0.6s linear infinite;
        }
        
        @keyframes spinner {
            to {transform: rotate(360deg);}
        }
        
        /* Focus ring custom */
        .form-control:focus,
        .form-select:focus {
            outline: none;
        }
        
        /* Custom select arrow */
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
        }
    </style>
</head>
<body>
    <div class="container register-container d-flex justify-content-center align-items-center min-vh-100">
        <div class="register-card">
            <!-- Header -->
            <div class="register-header">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                </div>
                <h1 class="brand-name">NEOSUSHI</h1>
                <p class="app-title">🍱 Aplikasi Kasir Restoran Jepang Berbasis Web</p>
            </div>
            
            <!-- Body -->
            <div class="register-body">
                <div class="welcome-text">
                    <h4>Buat Akun Baru</h4>
                    <p>Daftar untuk mulai menggunakan sistem</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-circle me-3"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="fas fa-check-circle me-3"></i>
                        <div><?= htmlspecialchars($success) ?></div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
                    
                    <div class="form-floating position-relative">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="username" class="form-control" id="username" placeholder="Username" required autofocus>
                        <label for="username">Username</label>
                    </div>
                    
                    <div class="form-floating position-relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>
                    <div class="password-hint">
                        <i class="fas fa-info-circle"></i>
                        Must be 8+ chars, with uppercase, lowercase, and number.
                    </div>
                    
                    <div class="form-floating position-relative">
                        <i class="fas fa-user-tag input-icon"></i>
                        <select name="role" class="form-select" id="role">
                            <option value="admin">Admin</option>
                            <option value="dekan">Kasir</option>
                        </select>
                        <label for="role">Role</label>
                    </div>
                    
                    <button type="submit" class="btn btn-register" id="registerBtn">
                        <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                    </button>
                    
                    <div class="divider">
                        <span>atau</span>
                    </div>
                    
                    <div class="login-link">
                        <p class="mb-0 text-muted">Sudah punya akun? <a href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Login di Sini</a></p>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="footer-text">
            <p class="mb-0">© 2026 NEOSUSHI
                 Restoran Jepang. Semua hak dilindungi.</p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission animation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('registerBtn');
            btn.classList.add('loading');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Memproses Registrasi...';
        });
        
        // Input focus animation
        document.querySelectorAll('.form-control, .form-select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
        
        // Auto-hide alert after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'slideUp 0.4s ease';
                setTimeout(() => alert.remove(), 400);
            }, 5000);
        });
        
        // Add subtle hover effect to form fields
        document.querySelectorAll('.form-floating').forEach(field => {
            field.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.01)';
            });
            field.addEventListener('mouseleave', function() {
                if (!this.querySelector('.form-control:focus') && !this.querySelector('.form-select:focus')) {
                    this.style.transform = 'scale(1)';
                }
            });
        });
    </script>
    
    <style>
        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-15px);
            }
        }
    </style>
</body>
</html>
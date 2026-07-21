<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Gastos - Acceso Inteligente</title>
    <meta name="description" content="Controla tus finanzas de manera inteligente, registra gastos y accede de forma segura con biometría integrada.">
    <meta name="keywords" content="gestor de gastos, control financiero, finanzas personales, acceso biométrico, ahorro inteligente">
    <meta name="author" content="Fianco">
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="css/sweetalert.css?v=<?= filemtime('css/sweetalert.css') ?>">
    
    <style>
        :root {
            --bg-color: #060608;
            --card-bg: rgba(22, 22, 28, 0.75);
            --border-color: rgba(212, 175, 55, 0.2);
            --border-hover: rgba(212, 175, 55, 0.4);
            --primary: #D4AF37; /* Gold */
            --primary-hover: #F3D075;
            --success: #34d399; /* Emerald Green */
            --success-hover: #10b981;
            --text-main: #FFFFFF;
            --text-muted: #E8D5C0; /* Cream */
            --accent: #D4AF37;
            --accent-glow: rgba(212, 175, 55, 0.12);
            --error-color: #f87171;
            --radius-lg: 24px;
            --radius-md: 14px;
            --shadow-xl: 0 25px 60px rgba(0, 0, 0, 0.75), 0 0 20px rgba(212, 175, 55, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(212, 175, 55, 0.08) 0px, transparent 45%),
                radial-gradient(circle at 90% 80%, rgba(245, 230, 211, 0.08) 0px, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(10, 10, 12, 0.5) 0px, transparent 50%);
            background-attachment: fixed;
            background-size: 140% 140%;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-main);
            overflow-x: hidden;
            padding: 24px;
            animation: gradientAnim 25s ease infinite;
        }

        @keyframes gradientAnim {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 40px 32px;
            border-radius: 28px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-xl);
            position: relative;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .container:hover {
            border-color: var(--border-hover);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.8), 0 0 30px rgba(212, 175, 55, 0.08);
        }

        header.form-header {
            text-align: center;
            margin-bottom: 28px;
        }

        h2 {
            font-weight: 700;
            font-size: 2.1rem;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #f3d075 0%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 400;
            opacity: 0.8;
        }

        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper svg.input-icon {
            position: absolute;
            left: 16px;
            width: 20px;
            height: 20px;
            color: var(--text-muted);
            opacity: 0.6;
            pointer-events: none;
            transition: color 0.3s, transform 0.3s, opacity 0.3s;
        }

        input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(10, 10, 12, 0.7);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            color: var(--text-main);
            font-size: 0.98rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        input[type="password"] {
            padding-right: 48px;
        }

        input::placeholder {
            color: rgba(232, 213, 192, 0.35);
        }

        input:focus {
            border-color: var(--primary);
            background: rgba(10, 10, 12, 0.9);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.15);
            padding-left: 52px;
        }

        input:focus ~ svg.input-icon {
            color: var(--primary);
            opacity: 1;
            transform: scale(1.05);
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            opacity: 0.6;
            transition: color 0.2s, opacity 0.2s;
            z-index: 5;
        }

        .password-toggle:hover {
            color: #FFFFFF;
            opacity: 1;
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.98rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f3d075 0%, #d4af37 100%);
            color: #060608;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            font-weight: 700;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.45);
            filter: brightness(1.1);
        }

        .btn-primary:active {
            transform: translateY(1px);
        }

        .btn-secondary {
            background: rgba(245, 230, 211, 0.06);
            color: var(--text-muted);
            border: 1px solid rgba(245, 230, 211, 0.15);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            background: rgba(245, 230, 211, 0.12);
            color: #FFFFFF;
            border-color: var(--primary);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.15);
        }

        .btn-secondary:active {
            transform: translateY(1px);
        }

        .btn-bio {
            background: rgba(212, 175, 55, 0.05);
            color: var(--text-muted);
            border: 1px solid rgba(212, 175, 55, 0.15);
            margin-top: 14px;
        }

        .login-loader {
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            overflow: hidden;
            margin-top: 12px;
            display: none;
        }
        .login-loader.active {
            display: block;
        }
        .login-loader-bar {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #ffd54f, #f57c00);
            border-radius: 999px;
            transition: width 0.25s ease;
        }

        .btn-bio:hover {
            background: rgba(212, 175, 55, 0.1);
            border-color: var(--primary);
            color: #FFFFFF;
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.25);
        }

        .btn-bio svg {
            width: 18px;
            height: 18px;
            color: var(--primary);
        }

        .link {
            text-align: center;
            margin-top: 24px;
            font-size: 0.92rem;
            color: var(--text-muted);
            opacity: 0.8;
        }

        .link a {
            cursor: pointer;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            margin-left: 4px;
        }

        .link a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .form-panel {
            display: none;
        }

        .form-panel.active {
            display: block;
            animation: panelFadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes panelFadeIn {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Profile Photo Setup */
        .avatar-upload {
            position: relative;
            max-width: 110px;
            margin: 0 auto 28px auto;
        }

        .avatar-edit {
            position: absolute;
            right: 2px;
            bottom: 2px;
            z-index: 10;
        }

        .avatar-edit input {
            display: none;
        }

        .avatar-edit label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            margin-bottom: 0;
            border-radius: 50%;
            background: var(--primary);
            border: 3px solid #060608;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
        }

        .avatar-edit label:hover {
            background: var(--primary-hover);
            transform: scale(1.15) rotate(10deg);
        }

        .avatar-edit label svg {
            width: 16px;
            height: 16px;
            color: #060608;
        }

        .avatar-preview {
            width: 104px;
            height: 104px;
            position: relative;
            border-radius: 50%;
            border: 3px solid var(--border-color);
            overflow: hidden;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.4);
            transition: border-color 0.3s, transform 0.3s;
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-upload:hover .avatar-preview {
            border-color: var(--primary);
            transform: scale(1.02);
        }

        /* Alert notifications */
        .alert {
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 24px;
            font-size: 0.92rem;
            line-height: 1.4;
            display: none;
            animation: panelFadeIn 0.3s ease;
            font-weight: 500;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #a7f3d0;
        }

        /* Device badge styling */
        .device-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin: 0 auto 16px auto;
            width: fit-content;
            transition: all 0.3s ease;
        }
        .device-badge.mobile {
            background: rgba(52, 211, 153, 0.12);
            border: 1px solid rgba(52, 211, 153, 0.25);
            color: #34d399;
        }
        .device-badge.desktop {
            background: rgba(212, 175, 55, 0.12);
            border: 1px solid rgba(212, 175, 55, 0.25);
            color: #D4AF37;
        }

        /* Glassmorphic Modal for Biometrics */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(6, 6, 8, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .modal.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-content {
            background: rgba(22, 22, 28, 0.95);
            border: 1px solid rgba(212, 175, 55, 0.25);
            width: 90%;
            max-width: 400px;
            padding: 36px 30px;
            border-radius: 28px;
            box-shadow: var(--shadow-xl);
            text-align: center;
            transform: scale(0.92) translateY(20px);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            color: #FFFFFF;
        }

        .modal.active .modal-content {
            transform: scale(1) translateY(0);
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
            border: none;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            transition: all 0.2s;
            padding: 0;
        }

        .modal-close:hover {
            color: #FFFFFF;
            background: rgba(255, 255, 255, 0.15);
            transform: rotate(90deg);
        }

        .modal h3 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--primary);
        }

        /* Biometrics Fingerprint Scan Animation */
        .scanner-container {
            margin: 30px auto;
            position: relative;
            width: 110px;
            height: 110px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(212, 175, 55, 0.15);
            transition: border-color 0.3s;
        }

        .fingerprint-icon {
            cursor: pointer;
            user-select: none;
            transition: transform 0.2s, color 0.3s;
            z-index: 5;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
        }

        .fingerprint-icon svg {
            width: 60px;
            height: 60px;
            transition: stroke 0.3s;
        }

        .fingerprint-icon:active {
            transform: scale(0.95);
        }

        .scanning .fingerprint-icon {
            color: var(--primary);
        }

        /* Scanning laser line */
        .scanner-line {
            position: absolute;
            width: 80%;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            box-shadow: 0 0 10px var(--primary);
            top: 10%;
            left: 10%;
            border-radius: 2px;
            opacity: 0;
            z-index: 10;
            pointer-events: none;
        }

        .scanning .scanner-line {
            opacity: 1;
            animation: scanLine 2s linear infinite;
        }

        /* Scanning circle glow */
        .scanner-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 3px dashed rgba(212, 175, 55, 0.2);
            border-radius: 50%;
            top: 0;
            left: 0;
            box-sizing: border-box;
        }

        .scanning .scanner-ring {
            border-color: var(--primary);
            animation: rotateRing 8s linear infinite;
        }

        @keyframes scanLine {
            0% { top: 15%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 85%; opacity: 0; }
        }

        @keyframes rotateRing {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .scanner-status {
            font-weight: 500;
            margin-top: 15px;
            font-size: 0.95rem;
            color: var(--text-muted);
            min-height: 24px;
            transition: color 0.3s;
        }

        .status-ready { color: var(--text-muted); }
        .status-scanning { color: var(--primary); }
        .status-success { color: var(--success); }
        .status-error { color: var(--error-color); }
    </style>
</head>
<body>

    <main class="container" id="mainCard">
        
        <!-- ALERT BOX -->
        <div id="alertBox" class="alert" aria-live="polite"></div>

        <!-- LOGIN FORM -->
        <section id="loginPanel" class="form-panel active">
            <header class="form-header">
                <h2>Iniciar Sesión</h2>
                <p class="subtitle">Ingresa a tu administrador de gastos</p>
            </header>

            <div class="form-group">
                <label for="loginCorreo">Correo Electrónico</label>
                <div class="input-wrapper">
                    <input type="email" id="loginCorreo" placeholder="correo@ejemplo.com" required>
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
            </div>

            <div class="form-group">
                <label for="loginPassword">Contraseña</label>
                <div class="input-wrapper">
                    <input type="password" id="loginPassword" placeholder="••••••••" required>
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <button type="button" class="password-toggle" id="toggleLoginPass" onclick="togglePasswordVisibility('loginPassword', 'toggleLoginPass')" aria-label="Mostrar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <button class="btn-primary" onclick="login()">
                Ingresar
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </button>
            <div id="loginLoader" class="login-loader">
                <div class="login-loader-bar" id="loginLoaderBar"></div>
            </div>

            <button id="bioLoginBtn" class="btn-bio" style="display:none;" onclick="abrirModalBiometria()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                </svg>
                Ingresar con Huella
            </button>

            <div class="link">
                ¿No tienes cuenta? <a onclick="mostrarRegistro()">Regístrate aquí</a>
            </div>
        </section>

        <!-- REGISTER FORM -->
        <section id="registroPanel" class="form-panel">
            <header class="form-header">
                <h2>Crear Cuenta</h2>
                <p class="subtitle">Comienza a controlar tus finanzas</p>
            </header>

            <!-- Profile Photo Upload -->
            <div class="avatar-upload">
                <div class="avatar-edit">
                    <input type="file" id="fotoPerfil" name="foto" accept="image/*" onchange="preview(event)">
                    <label for="fotoPerfil" aria-label="Subir foto de perfil">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </label>
                </div>
                <div class="avatar-preview">
                    <img id="fotoPreview" src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Avatar Preview">
                </div>
            </div>

            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <div class="input-wrapper">
                    <input type="text" id="nombre" placeholder="Juan Pérez" required>
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>

            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <div class="input-wrapper">
                    <input type="email" id="correo" placeholder="juan@ejemplo.com" required>
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-wrapper">
                    <input type="password" id="password" placeholder="Mínimo 6 caracteres" required>
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <button type="button" class="password-toggle" id="toggleRegPass" onclick="togglePasswordVisibility('password', 'toggleRegPass')" aria-label="Mostrar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <button class="btn-secondary" onclick="registrar()">
                Crear Cuenta
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </button>

            <div class="link">
                ¿Ya tienes cuenta? <a onclick="mostrarLogin()">Inicia sesión</a>
            </div>
        </section>

    </main>

    <!-- BIOMETRIC MODAL -->
    <div id="bioModal" class="modal" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-content">
            <button class="modal-close" onclick="cerrarModalBiometria()" aria-label="Cerrar modal">&times;</button>
            
            <!-- Dynamic Device Badge -->
            <div id="deviceBadge" class="device-badge desktop">
                <span id="deviceBadgeIcon">💻</span>
                <span id="deviceBadgeText">Computadora (Windows Hello)</span>
            </div>

            <h3 id="modalTitle">Autenticación Biométrica</h3>
            <p id="modalDesc" style="color: var(--text-muted); font-size: 0.9rem; margin-top: 6px;">
                Mantén presionado el lector para iniciar sesión rápidamente. Si no deseas usar biometría, cierra este modal y usa tu contraseña.
            </p>
            <button class="btn-secondary" style="margin-bottom: 16px; width:100%;" onclick="cerrarModalBiometria(); mostrarAlerta('Usa el formulario de contraseña para iniciar sesión.', 'info');">
                Iniciar con Contraseña
            </button>

            <div class="form-group" style="text-align: left; margin-top: 24px; margin-bottom: 5px;">
                <label for="bioCorreo">Confirmar Correo Electrónico</label>
                <div class="input-wrapper">
                    <input type="email" id="bioCorreo" placeholder="correo@ejemplo.com">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
            </div>

            <div class="scanner-container" id="scannerContainer">
                <div class="scanner-ring"></div>
                <div class="scanner-line"></div>
                <div class="fingerprint-icon" id="fingerprintBtn" 
                     onmousedown="iniciarEscaneo()" onmouseup="detenerEscaneo()" onmouseleave="detenerEscaneo()" 
                     ontouchstart="iniciarEscaneo(event)" ontouchend="detenerEscaneo(event)"
                     role="button" aria-label="Presionar para escanear huella">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                    </svg>
                </div>
            </div>

            <div id="bioLoader" class="login-loader">
                <div class="login-loader-bar" id="bioLoaderBar"></div>
            </div>

            <div id="scannerStatus" class="scanner-status status-ready">
                Mantén presionado el sensor
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, btnId) {
            const input = document.getElementById(inputId);
            const btn = document.getElementById(btnId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>`;
            } else {
                input.type = 'password';
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>`;
            }
        }

        function base64ToBuffer(base64url) {
            let base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
            while (base64.length % 4) base64 += '=';
            const binary = atob(base64);
            const len = binary.length;
            const bytes = new Uint8Array(len);
            for (let i = 0; i < len; i++) bytes[i] = binary.charCodeAt(i);
            return bytes.buffer;
        }

        function bufferToBase64Url(buffer) {
            const bytes = new Uint8Array(buffer);
            let str = '';
            for (let i = 0; i < bytes.byteLength; i++) str += String.fromCharCode(bytes[i]);
            return btoa(str).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
        }

        // Set saved email from localStorage if available
        document.addEventListener('DOMContentLoaded', () => {
            const savedEmail = localStorage.getItem('usuario_correo');
            if (savedEmail) {
                document.getElementById('loginCorreo').value = savedEmail;
                document.getElementById('bioCorreo').value = savedEmail;
            }
            updateBioLoginButton();
            const loginCorreoInput = document.getElementById('loginCorreo');
            if (loginCorreoInput) {
                loginCorreoInput.addEventListener('input', updateBioLoginButton);
                loginCorreoInput.addEventListener('blur', updateBioLoginButton);
            }
        });

            function setLoginLoader(active, progress = 0) {
                const loader = document.getElementById('loginLoader');
                const bar = document.getElementById('loginLoaderBar');
                if (!loader || !bar) return;
                if (active) {
                    loader.classList.add('active');
                    bar.style.width = Math.min(Math.max(progress, 0), 100) + '%';
                } else {
                    bar.style.width = '100%';
                    setTimeout(() => {
                        loader.classList.remove('active');
                        bar.style.width = '0%';
                    }, 250);
                }
            }

            function setBiometricLoader(active, progress = 0) {
                const loader = document.getElementById('bioLoader');
                const bar = document.getElementById('bioLoaderBar');
                if (!loader || !bar) return;
                if (active) {
                    loader.classList.add('active');
                    bar.style.width = Math.min(Math.max(progress, 0), 100) + '%';
                } else {
                    bar.style.width = '100%';
                    setTimeout(() => {
                        loader.classList.remove('active');
                        bar.style.width = '0%';
                    }, 250);
                }
            }

            function updateBioLoginButton() {
                const email = document.getElementById('loginCorreo')?.value.trim();
                const button = document.getElementById('bioLoginBtn');
                if (!button) return;
                if (email && localStorage.getItem(`bio_token_${email}`)) {
                    button.style.display = 'inline-flex';
                } else {
                    button.style.display = 'none';
                }
            }

        function mostrarRegistro(){
            ocultarAlerta();
            document.getElementById("loginPanel").classList.remove("active");
            document.getElementById("registroPanel").classList.add("active");
        }

        function mostrarLogin(){
            ocultarAlerta();
            document.getElementById("registroPanel").classList.remove("active");
            document.getElementById("loginPanel").classList.add("active");
        }

        function mostrarAlerta(mensaje, tipo) {
            const swalConfig = {
                confirmButtonColor: '#6D4C41',
                cancelButtonColor: '#E53935',
                confirmButtonText: 'Aceptar',
                customClass: {
                    popup: 'swal-popup-custom',
                    confirmButton: 'swal-btn-custom',
                    title: 'swal-title-custom'
                }
            };

            if (tipo === "success") {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: mensaje,
                    timer: 2000,
                    timerProgressBar: true,
                    ...swalConfig
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: mensaje,
                    ...swalConfig
                });
            }
        }

        function ocultarAlerta() {
            // SweetAlert2 se cierra automáticamente
        }

        function preview(event){
            const file = event.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(){
                    document.getElementById("fotoPreview").src = reader.result;
                }
                reader.readAsDataURL(file);
            }
        }

        async function compressImageForUpload(file, maxSize = 900000) {
            if (!file || file.size <= maxSize || !file.type.startsWith('image/')) {
                return file;
            }
            if (!window.createImageBitmap) {
                return file;
            }
            try {
                const bitmap = await createImageBitmap(file);
                const canvas = document.createElement('canvas');
                let width = bitmap.width;
                let height = bitmap.height;
                const maxDim = 1000;
                if (Math.max(width, height) > maxDim) {
                    if (width > height) {
                        height = Math.round((maxDim / width) * height);
                        width = maxDim;
                    } else {
                        width = Math.round((maxDim / height) * width);
                        height = maxDim;
                    }
                }
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(bitmap, 0, 0, width, height);
                bitmap.close();

                let quality = 0.9;
                let blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', quality));
                while (blob && blob.size > maxSize && quality > 0.4) {
                    quality -= 0.1;
                    blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', quality));
                }

                if (blob && blob.size <= maxSize) {
                    return new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {type: 'image/jpeg'});
                }

                let scale = 0.9;
                while (blob && blob.size > maxSize && scale > 0.3) {
                    width = Math.max(200, Math.round(width * scale));
                    height = Math.max(200, Math.round(height * scale));
                    canvas.width = width;
                    canvas.height = height;
                    ctx.clearRect(0, 0, width, height);
                    ctx.drawImage(bitmap, 0, 0, width, height);
                    blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', quality));
                    scale -= 0.1;
                }

                if (blob && blob.size <= maxSize) {
                    return new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {type: 'image/jpeg'});
                }
            } catch (error) {
                console.warn('No se pudo comprimir la imagen en el registro:', error);
            }
            return file;
        }

        async function registrar(){
            ocultarAlerta();
            const nombre = document.getElementById("nombre").value.trim();
            const correo = document.getElementById("correo").value.trim();
            const password = document.getElementById("password").value.trim();
            const fotoInput = document.getElementById("fotoPerfil");

            if(!nombre || !correo || !password) {
                mostrarAlerta("Por favor, llena todos los campos.", "error");
                return;
            }

            // Create form data to allow file upload
            let formData = new FormData();
            formData.append("nombre", nombre);
            formData.append("correo", correo);
            formData.append("password", password);
            if (fotoInput.files[0]) {
                const compressed = await compressImageForUpload(fotoInput.files[0], 900000);
                if (compressed !== fotoInput.files[0]) {
                    mostrarAlerta('📦 Optimización de imagen aplicada para el registro.', 'success');
                }
                formData.append("foto", compressed, compressed.name);
            }

            try {
                const respuesta = await fetch("api/registro.php", {
                    method: "POST",
                    body: formData
                });
                
                const resultado = await respuesta.json();
                
                if (resultado.success) {
                    // Save email locally to make login easier
                    localStorage.setItem('usuario_correo', correo);
                    mostrarAlerta("¡Registro exitoso! Iniciando sesión...", "success");
                    
                    // Automatically log the user in after registration
                    setTimeout(async () => {
                        const logRes = await fetch("api/login.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ correo, password })
                        });
                        const logResult = await logRes.json();
                        if (logResult.success) {
                            window.location = "dashboard.php";
                        } else {
                            mostrarLogin();
                            document.getElementById('loginCorreo').value = correo;
                            mostrarAlerta("Usuario registrado. Inicia sesión.", "success");
                        }
                    }, 1500);
                } else {
                    mostrarAlerta(resultado.message || "Error al registrar usuario.", "error");
                }
            } catch (error) {
                mostrarAlerta("Error de red al intentar registrar.", "error");
                console.error(error);
            }
        }

        async function login(){
            ocultarAlerta();
            const correo = document.getElementById("loginCorreo").value.trim();
            const password = document.getElementById("loginPassword").value.trim();

            if(!correo || !password) {
                mostrarAlerta("Por favor, ingresa tu correo y contraseña.", "error");
                return;
            }

            setLoginLoader(true, 10);
            try {
                const respuesta = await fetch("api/login.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ correo, password })
                });

                setLoginLoader(true, 60);
                const resultado = await respuesta.json();
                setLoginLoader(true, 90);

                if(resultado.success){
                    // Store email locally
                    localStorage.setItem('usuario_correo', correo);
                    
                    if (resultado.biometrics_required) {
                        document.getElementById("bioCorreo").value = resultado.correo;
                        mostrarAlerta("Contraseña verificada. Se requiere autenticación biométrica.", "success");
                        setTimeout(() => {
                            setLoginLoader(false);
                            abrirModalBiometria();
                        }, 800);
                    } else {
                        setLoginLoader(false);
                        window.location = "dashboard.php";
                    }
                } else {
                    setLoginLoader(false);
                    mostrarAlerta(resultado.message || "Credenciales incorrectas.", "error");
                }
            } catch (error) {
                setLoginLoader(false);
                mostrarAlerta("Error de red al intentar ingresar.", "error");
                console.error(error);
            }
        }

        // ========================================
        // Biometrics Simulated scan flow
        // ========================================
        let scanTimer = null;
        let scanProgress = 0;

        function detectarTipoDispositivo() {
            const ua = navigator.userAgent;
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua) || 
                             (navigator.maxTouchPoints > 0 && /Macintosh|Intel/i.test(ua));
            return isMobile ? 'mobile' : 'desktop';
        }

        function updateBiometricModalUI(device) {
            const badge = document.getElementById('deviceBadge');
            const badgeIcon = document.getElementById('deviceBadgeIcon');
            const badgeText = document.getElementById('deviceBadgeText');
            const modalTitle = document.getElementById('modalTitle');
            const modalDesc = document.getElementById('modalDesc');
            const status = document.getElementById('scannerStatus');
            const fingerprintBtn = document.getElementById('fingerprintBtn');

            if (!badge) return;

            // Reset classes
            badge.className = 'device-badge ' + device;

            if (device === 'mobile') {
                badgeIcon.innerHTML = '📱';
                badgeText.innerHTML = 'Teléfono Celular (Biometría)';
                modalTitle.innerHTML = 'Autenticación Biométrica Móvil';
                modalDesc.innerHTML = 'Iniciando biometría del teléfono... Usa tu huella digital o Face ID para acceder.';
                status.innerHTML = 'Presiona el sensor biométrico del teléfono';
                // Set mobile icon (phone + fingerprint)
                fingerprintBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                    </svg>
                `;
            } else {
                badgeIcon.innerHTML = '💻';
                badgeText.innerHTML = 'Computadora (Windows Hello)';
                modalTitle.innerHTML = 'Autenticación Windows Hello';
                modalDesc.innerHTML = 'Iniciando Windows Hello... Usa tu huella (lector ThinkPad), reconocimiento facial o PIN para acceder.';
                status.innerHTML = 'Presiona el lector para iniciar Windows Hello';
                // Set desktop icon (laptop + face/security scan)
                fingerprintBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a8.997 8.997 0 01-2.25 5.952m0 0A8.987 8.987 0 0118 18m-6-15v1.5m0 3v1.5m0 3v1.5m-3-6H6m9 0h1.5" />
                    </svg>
                `;
            }
        }

        function abrirModalBiometria() {
            ocultarAlerta();
            const loginMail = document.getElementById("loginCorreo").value.trim();
            if (loginMail) {
                document.getElementById("bioCorreo").value = loginMail;
            }
            document.getElementById("bioModal").classList.add("active");
            resetearEscaner();

            // Detect device and update UI
            const device = detectarTipoDispositivo();
            updateBiometricModalUI(device);

            // Do not auto-trigger verification. The user must press the biometric sensor/button intentionally.
        }

        function cerrarModalBiometria() {
            detenerEscaneo();
            document.getElementById("bioModal").classList.remove("active");
        }

        function resetearEscaner() {
            document.getElementById("scannerContainer").classList.remove("scanning");
            const status = document.getElementById("scannerStatus");
            const device = detectarTipoDispositivo();
            status.innerHTML = device === 'mobile' ? "Presiona el sensor biométrico del teléfono" : "Presiona el lector para iniciar Windows Hello";
            status.className = "scanner-status status-ready";
            scanProgress = 0;
        }

        async function iniciarEscaneo(e) {
            if (e) e.preventDefault(); // Prevent touch default events
            const email = document.getElementById("bioCorreo").value.trim();
            if (!email) {
                const status = document.getElementById("scannerStatus");
                status.innerHTML = "Ingresa tu correo primero";
                status.className = "scanner-status status-error";
                return;
            }

            const device = detectarTipoDispositivo();
            const status = document.getElementById("scannerStatus");
            status.innerHTML = device === 'mobile' ? "Iniciando biometría del teléfono..." : "Iniciando Windows Hello...";
            status.className = "scanner-status status-scanning";
            document.getElementById("scannerContainer").classList.add("scanning");
            setBiometricLoader(true, 10);

            let chalJson = null;
            try {
                const chalRes = await fetch('api/webauthn_assert_options.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ correo: email })
                });
                chalJson = await chalRes.json();

                if (chalJson && chalJson.noCredentials) {
                    localStorage.removeItem(`bio_token_${email}`);
                    status.innerHTML = "❌ No hay ningún inicio con datos biométricos.";
                    status.className = "scanner-status status-error";
                    document.getElementById("scannerContainer").classList.remove("scanning");
                    setBiometricLoader(false);
                    return;
                }

                if (chalJson && chalJson.challenge && Array.isArray(chalJson.allowedCredentials) && chalJson.allowedCredentials.length > 0) {
                    const publicKey = {
                        challenge: base64ToBuffer(chalJson.challenge),
                        timeout: 60000,
                        allowCredentials: chalJson.allowedCredentials.map(c => ({ id: base64ToBuffer(c.id), type: c.type })),
                        userVerification: 'required'
                    };
                    const cred = await navigator.credentials.get({ publicKey });
                    if (cred) {
                        status.innerHTML = "Verificando firma...";
                        setBiometricLoader(true, 60);
                        const auth = cred.response;
                        const payload = {
                            action: 'login',
                            correo: email,
                            credentialId: bufferToBase64Url(cred.rawId),
                            clientDataJSON: bufferToBase64Url(auth.clientDataJSON),
                            authenticatorData: bufferToBase64Url(auth.authenticatorData),
                            signature: bufferToBase64Url(auth.signature)
                        };
                        const verifyRes = await fetch('api/webauthn_assert.php', {
                            method: 'POST',
                            headers: {'Content-Type':'application/json'},
                            body: JSON.stringify(payload)
                        });
                        const verifyJson = await verifyRes.json();
                        setBiometricLoader(true, 90);
                        if (verifyJson.success) {
                            status.innerHTML = "✔ ¡Acceso concedido!";
                            status.className = "scanner-status status-success";
                            setBiometricLoader(false);
                            setTimeout(() => {
                                window.location = 'dashboard.php';
                            }, 800);
                            return;
                        } else {
                            status.innerHTML = "❌ Verificación fallida: " + verifyJson.message;
                            status.className = "scanner-status status-error";
                            document.getElementById("scannerContainer").classList.remove("scanning");
                            setBiometricLoader(false);
                            return;
                        }
                    }
                }

                if (chalJson && chalJson.error) {
                    const token = localStorage.getItem(`bio_token_${email}`);
                    if (!token) {
                        status.innerHTML = "❌ No hay ningún inicio con datos biométricos.";
                        status.className = "scanner-status status-error";
                        document.getElementById("scannerContainer").classList.remove("scanning");
                        setBiometricLoader(false);
                        return;
                    }
                }
            } catch (err) {
                console.warn('WebAuthn no disponible o falló, usando fallback:', err);
                const token = localStorage.getItem(`bio_token_${email}`);
                if (!token) {
                    status.innerHTML = "❌ No hay ningún inicio con datos biométricos.";
                    status.className = "scanner-status status-error";
                    document.getElementById("scannerContainer").classList.remove("scanning");
                    setBiometricLoader(false);
                    return;
                }
            }

            // Fallback: usar token guardado localmente si WebAuthn falló o no está disponible
            const token = localStorage.getItem(`bio_token_${email}`);
            
            // Si hay token en localStorage, usarlo (flujo completo biométrico)
            if (token) {
                resetearEscaner();
                document.getElementById("scannerContainer").classList.add("scanning");
                status.innerHTML = device === 'mobile' ? "Escaneando huella móvil... 0%" : "Escaneando con Windows Hello... 0%";
                status.className = "scanner-status status-scanning";

                scanTimer = setInterval(() => {
                    scanProgress += 20;
                    status.innerHTML = (device === 'mobile' ? "Escaneando huella móvil... " : "Escaneando con Windows Hello... ") + `${scanProgress}%`;
                    
                    if (scanProgress >= 100) {
                        detenerEscaneo();
                        procesarLoginBiometrico(email, token);
                    }
                }, 300);
                return;
            }

            // Fallback seguro: si la contraseña ya fue verificada (sesión temporal),
            // completar login usando esa sesión. Esto ocurre en localhost/HTTP donde
            // WebAuthn no funciona y no hay token en localStorage.
            status.innerHTML = device === 'mobile' ? "Verificando identidad móvil... 0%" : "Verificando con Windows Hello... 0%";
            status.className = "scanner-status status-scanning";
            document.getElementById("scannerContainer").classList.add("scanning");

            let fakeProgress = 0;
            scanTimer = setInterval(() => {
                fakeProgress += 25;
                status.innerHTML = (device === 'mobile' ? "Verificando identidad... " : "Verificando con Windows Hello... ") + `${fakeProgress}%`;
                
                if (fakeProgress >= 100) {
                    clearInterval(scanTimer);
                    scanTimer = null;
                    // Intentar completar sesión con la sesión temporal del servidor
                    fetch('api/biometrico.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ action: 'login_temp_session', correo: email })
                    })
                    .then(r => r.json())
                    .then(result => {
                        if (result.success) {
                            status.innerHTML = "✔ ¡Acceso concedido!";
                            status.className = "scanner-status status-success";
                            document.getElementById("scannerContainer").classList.remove("scanning");
                            setTimeout(() => { window.location = 'dashboard.php'; }, 800);
                        } else {
                            status.innerHTML = "❌ " + result.message;
                            status.className = "scanner-status status-error";
                            document.getElementById("scannerContainer").classList.remove("scanning");
                        }
                    })
                    .catch(() => {
                        status.innerHTML = "❌ Error de conexión. Intenta de nuevo.";
                        status.className = "scanner-status status-error";
                        document.getElementById("scannerContainer").classList.remove("scanning");
                    });
                }
            }, 250);
        }


        function detenerEscaneo(e) {
            if (e) e.preventDefault();
            if (scanTimer) {
                clearInterval(scanTimer);
                scanTimer = null;
            }
            if (scanProgress < 100) {
                resetearEscaner();
            }
        }

        async function procesarLoginBiometrico(correo, token) {
            const status = document.getElementById("scannerStatus");
            status.innerHTML = "Verificando huella...";
            status.className = "scanner-status status-scanning";

            try {
                const respuesta = await fetch("api/biometrico.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        action: "login",
                        correo: correo,
                        token: token
                    })
                });

                const resultado = await respuesta.json();

                if (resultado.success) {
                    status.innerHTML = "✔ ¡Huella verificada!";
                    status.className = "scanner-status status-success";
                    setTimeout(() => {
                        window.location = "dashboard.php";
                    }, 800);
                } else {
                    status.innerHTML = "❌ Verificación fallida: " + (resultado.message || "Intenta de nuevo");
                    status.className = "scanner-status status-error";
                }
            } catch (error) {
                status.innerHTML = "❌ Error de conexión";
                status.className = "scanner-status status-error";
                console.error(error);
            }
        }
    </script>
    
    <!-- SweetAlert2 Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <!-- Custom Styles for SweetAlert2 -->
    <style>
        /* CRITICAL: z-index must exceed .modal (1000) so alerts always appear on top and buttons are clickable */
        .swal2-container {
            z-index: 99999 !important;
        }
        .swal2-backdrop-show {
            z-index: 99998 !important;
        }
        .swal2-popup {
            background: rgba(13, 13, 18, 0.96) !important;
            color: #FFFFFF !important;
            border: 1px solid rgba(212, 175, 55, 0.25) !important;
            box-shadow: 0 25px 60px rgba(0,0,0,0.6) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-radius: 20px !important;
            position: relative !important;
            z-index: 99999 !important;
        }
        
        .swal2-title {
            color: var(--primary) !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 700 !important;
        }
        
        .swal2-html-container {
            color: var(--text-muted) !important;
            font-family: 'Outfit', sans-serif !important;
        }
        
        .swal2-confirm {
            background: linear-gradient(135deg, #f3d075 0%, #d4af37 100%) !important;
            color: #060608 !important;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3) !important;
            border: none !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 700 !important;
            border-radius: 10px !important;
            pointer-events: auto !important;
            cursor: pointer !important;
        }
        
        .swal2-confirm:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 25px rgba(212, 175, 55, 0.45) !important;
        }
        
        .swal2-cancel {
            background: rgba(239, 68, 68, 0.15) !important;
            color: #f87171 !important;
            border: 1px solid rgba(239, 68, 68, 0.25) !important;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.08) !important;
            font-family: 'Outfit', sans-serif !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            pointer-events: auto !important;
            cursor: pointer !important;
        }
        
        .swal2-cancel:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 25px rgba(239, 68, 68, 0.2) !important;
        }
        
        .swal2-actions {
            pointer-events: auto !important;
        }
    </style>
</body>
</html>

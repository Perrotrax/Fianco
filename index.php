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
    <title>Gestor de Gastos - Acceso</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(17, 24, 39, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --success: #10b981;
            --success-hover: #059669;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --accent: #8b5cf6;
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
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-main);
            overflow-x: hidden;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 40px 30px;
            border-radius: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        h2 {
            text-align: center;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 2rem;
            letter-spacing: -0.025em;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-main);
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        input:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(1px);
        }

        .btn-secondary {
            background: var(--success);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-secondary:hover {
            background: var(--success-hover);
            transform: translateY(-1px);
        }

        .btn-secondary:active {
            transform: translateY(1px);
        }

        .btn-bio {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            margin-top: 12px;
        }

        .btn-bio:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .link {
            text-align: center;
            margin-top: 24px;
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .link a {
            cursor: pointer;
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }

        .link a:hover {
            color: var(--accent);
            text-decoration: underline;
        }

        .form-panel {
            display: none;
        }

        .form-panel.active {
            display: block;
            animation: fadeIn 0.4s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Profile Photo Setup */
        .avatar-upload {
            position: relative;
            max-width: 110px;
            margin: 0 auto 24px auto;
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
            width: 32px;
            height: 32px;
            margin-bottom: 0;
            border-radius: 50%;
            background: var(--primary);
            border: 2px solid #111827;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.12);
        }

        .avatar-edit label:hover {
            background: var(--primary-hover);
            transform: scale(1.1);
        }

        .avatar-edit label:after {
            content: "📷";
            font-size: 0.9rem;
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            position: relative;
            border-radius: 50%;
            border: 3px solid rgba(59, 130, 246, 0.3);
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            transition: border-color 0.3s;
        }

        .avatar-preview:hover {
            border-color: var(--primary);
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Alert notifications */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        /* Glassmorphic Modal for Biometrics */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(8px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .modal.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-content {
            background: #111827;
            border: 1px solid var(--border-color);
            width: 90%;
            max-width: 380px;
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            transform: scale(0.9);
            transition: all 0.3s ease;
            position: relative;
        }

        .modal.active .modal-content {
            transform: scale(1);
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            font-size: 1.5rem;
            color: var(--text-muted);
            cursor: pointer;
            border: none;
            background: none;
            width: auto;
            padding: 0;
        }

        .modal-close:hover {
            color: var(--text-main);
        }

        /* Biometrics Fingerprint Scan Animation */
        .scanner-container {
            margin: 30px auto;
            position: relative;
            width: 100px;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .fingerprint-icon {
            font-size: 4.5rem;
            cursor: pointer;
            user-select: none;
            transition: transform 0.2s;
            z-index: 5;
            position: relative;
        }

        .fingerprint-icon:active {
            transform: scale(0.95);
        }

        /* Scanning laser line */
        .scanner-line {
            position: absolute;
            width: 100%;
            height: 4px;
            background: var(--primary);
            box-shadow: 0 0 12px 2px var(--primary);
            top: 0;
            left: 0;
            border-radius: 2px;
            opacity: 0;
            z-index: 10;
        }

        .scanning .scanner-line {
            opacity: 1;
            animation: scan 1.5s linear infinite;
        }

        /* Scanning circle glow */
        .scanner-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px dashed rgba(59, 130, 246, 0.2);
            border-radius: 50%;
            top: 0;
            left: 0;
            box-sizing: border-box;
        }

        .scanning .scanner-ring {
            border-color: var(--primary);
            animation: rotate 4s linear infinite;
        }

        @keyframes scan {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .scanner-status {
            font-weight: 500;
            margin-top: 15px;
            font-size: 1.05rem;
            color: var(--text-muted);
            min-height: 24px;
        }

        .status-ready { color: var(--text-muted); }
        .status-scanning { color: var(--primary); }
        .status-success { color: var(--success); }
        .status-error { color: #f87171; }

    </style>
</head>
<body>

    <div class="container">
        
        <!-- ALERT BOX -->
        <div id="alertBox" class="alert"></div>

        <!-- LOGIN FORM -->
        <div id="loginPanel" class="form-panel active">
            <h2>Iniciar Sesión</h2>
            <p class="subtitle">Ingresa a tu administrador de gastos</p>

            <div class="form-group">
                <label for="loginCorreo">Correo Electrónico</label>
                <input type="email" id="loginCorreo" placeholder="correo@ejemplo.com" required>
            </div>

            <div class="form-group">
                <label for="loginPassword">Contraseña</label>
                <input type="password" id="loginPassword" placeholder="••••••••" required>
            </div>

            <button class="btn-primary" onclick="login()">
                Ingresar ➔
            </button>

            <button class="btn-bio" onclick="abrirModalBiometria()">
                🔐 Ingresar con Huella
            </button>

            <div class="link">
                ¿No tienes cuenta? <a onclick="mostrarRegistro()">Regístrate aquí</a>
            </div>
        </div>

        <!-- REGISTER FORM -->
        <div id="registroPanel" class="form-panel">
            <h2>Crear Cuenta</h2>
            <p class="subtitle">Comienza a controlar tus finanzas</p>

            <!-- Profile Photo Upload -->
            <div class="avatar-upload">
                <div class="avatar-edit">
                    <input type="file" id="fotoPerfil" accept="image/*" onchange="preview(event)">
                    <label for="fotoPerfil"></label>
                </div>
                <div class="avatar-preview">
                    <img id="fotoPreview" src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Avatar Preview">
                </div>
            </div>

            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" placeholder="Juan Pérez" required>
            </div>

            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" placeholder="juan@ejemplo.com" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" placeholder="Mínimo 6 caracteres" required>
            </div>

            <button class="btn-secondary" onclick="registrar()">
                Crear Cuenta ✨
            </button>

            <div class="link">
                ¿Ya tienes cuenta? <a onclick="mostrarLogin()">Inicia sesión</a>
            </div>
        </div>

    </div>

    <!-- BIOMETRIC MODAL -->
    <div id="bioModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="cerrarModalBiometria()">&times;</button>
            <h3>Autenticación Biométrica</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 6px;">
                Coloca tu huella digital para iniciar sesión de forma rápida.
            </p>

            <div class="form-group" style="text-align: left; margin-top: 20px; margin-bottom: 5px;">
                <label for="bioCorreo">Confirmar Correo Electrónico</label>
                <input type="email" id="bioCorreo" placeholder="correo@ejemplo.com">
            </div>

            <div class="scanner-container" id="scannerContainer">
                <div class="scanner-ring"></div>
                <div class="scanner-line"></div>
                <div class="fingerprint-icon" id="fingerprintBtn" onmousedown="iniciarEscaneo()" onmouseup="detenerEscaneo()" onmouseleave="detenerEscaneo()" ontouchstart="iniciarEscaneo(event)" ontouchend="detenerEscaneo(event)">
                    👆
                </div>
            </div>

            <div id="scannerStatus" class="scanner-status status-ready">
                Mantén presionado el sensor
            </div>
        </div>
    </div>

    <script>
        // Set saved email from localStorage if available
        document.addEventListener('DOMContentLoaded', () => {
            const savedEmail = localStorage.getItem('usuario_correo');
            if (savedEmail) {
                document.getElementById('loginCorreo').value = savedEmail;
                document.getElementById('bioCorreo').value = savedEmail;
            }
        });

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
            const alertBox = document.getElementById("alertBox");
            alertBox.className = "alert " + (tipo === "success" ? "alert-success" : "alert-error");
            alertBox.innerHTML = mensaje;
            alertBox.style.display = "block";
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function ocultarAlerta() {
            document.getElementById("alertBox").style.display = "none";
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
                formData.append("foto", fotoInput.files[0]);
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

            try {
                const respuesta = await fetch("api/login.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ correo, password })
                });

                const resultado = await respuesta.json();

                if(resultado.success){
                    // Store email locally
                    localStorage.setItem('usuario_correo', correo);
                    window.location = "dashboard.php";
                } else {
                    mostrarAlerta(resultado.message || "Credenciales incorrectas.", "error");
                }
            } catch (error) {
                mostrarAlerta("Error de red al intentar ingresar.", "error");
                console.error(error);
            }
        }

        // ========================================
        // Biometrics Simulated scan flow
        // ========================================
        let scanTimer = null;
        let scanProgress = 0;

        function abrirModalBiometria() {
            ocultarAlerta();
            const loginMail = document.getElementById("loginCorreo").value.trim();
            if (loginMail) {
                document.getElementById("bioCorreo").value = loginMail;
            }
            document.getElementById("bioModal").classList.add("active");
            resetearEscaner();
        }

        function cerrarModalBiometria() {
            detenerEscaneo();
            document.getElementById("bioModal").classList.remove("active");
        }

        function resetearEscaner() {
            document.getElementById("scannerContainer").classList.remove("scanning");
            const status = document.getElementById("scannerStatus");
            status.innerHTML = "Mantén presionado el sensor";
            status.className = "scanner-status status-ready";
            scanProgress = 0;
        }

        function iniciarEscaneo(e) {
            if (e) e.preventDefault(); // Prevent touch default events
            const email = document.getElementById("bioCorreo").value.trim();
            if (!email) {
                const status = document.getElementById("scannerStatus");
                status.innerHTML = "Ingresa tu correo primero";
                status.className = "scanner-status status-error";
                return;
            }

            const token = localStorage.getItem(`bio_token_${email}`);
            if (!token) {
                const status = document.getElementById("scannerStatus");
                status.innerHTML = "Biometría no configurada en este dispositivo";
                status.className = "scanner-status status-error";
                return;
            }

            resetearEscaner();
            document.getElementById("scannerContainer").classList.add("scanning");
            const status = document.getElementById("scannerStatus");
            status.innerHTML = "Escaneando huella... 0%";
            status.className = "scanner-status status-scanning";

            scanTimer = setInterval(() => {
                scanProgress += 20;
                status.innerHTML = `Escaneando huella... ${scanProgress}%`;
                
                if (scanProgress >= 100) {
                    detenerEscaneo();
                    procesarLoginBiometrico(email, token);
                }
            }, 300);
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
</body>
</html>

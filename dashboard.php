<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/api/conexion.php';

$userId = $_SESSION['id_usuario'];

// Fetch latest user details from DB
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: logout.php");
    exit;
}

$fotoPath = 'uploads/perfiles/' . $user['foto_perfil'];
if ($user['foto_perfil'] === 'default.png' || !file_exists($fotoPath)) {
    $fotoSrc = 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
} else {
    $fotoSrc = $fotoPath;
}

// Define default monthly budget
$presupuestoMensual = 15000.00;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Gastos - Dashboard</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0f19;
            --sidebar-bg: #111827;
            --card-bg: rgba(17, 24, 39, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
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
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.08) 0px, transparent 40%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.08) 0px, transparent 40%);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text-main);
            display: flex;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 10;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 40px;
            padding-left: 10px;
        }

        .logo span {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .profile-img-container {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--primary);
            flex-shrink: 0;
        }

        .profile-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            overflow: hidden;
        }

        .profile-name {
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .profile-email {
            font-size: 0.8rem;
            color: var(--text-muted);
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .menu-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .menu-item.active a, .menu-item a:hover {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.05);
        }

        .menu-item.active a {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
            border-left: 3px solid var(--primary);
            border-radius: 0 12px 12px 0;
            padding-left: 13px;
        }

        .logout-btn {
            margin-top: auto;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }

        .logout-btn a {
            color: var(--danger);
        }

        .logout-btn a:hover {
            background: rgba(239, 68, 68, 0.05);
            color: #f87171;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            padding: 40px;
            max-width: 1200px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .header-title p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 4px;
        }

        /* Dashboard Overview Grid */
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .stat-card.balance::before { background: var(--primary); }
        .stat-card.budget::before { background: var(--success); }
        .stat-card.spent::before { background: var(--danger); }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .stat-val {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .stat-sub {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* Two Column Layout */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 1.3fr 1.7fr;
            gap: 30px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Cards Panel container */
        .panel {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .panel-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        input, select {
            width: 100%;
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-main);
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s;
        }

        input:focus, select:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        button.btn-add {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }

        button.btn-add:hover {
            background: var(--primary-hover);
        }

        /* Transactions list */
        .transactions-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 480px;
            overflow-y: auto;
            padding-right: 5px;
        }

        /* Custom scrollbar */
        .transactions-list::-webkit-scrollbar {
            width: 6px;
        }
        .transactions-list::-webkit-scrollbar-track {
            background: transparent;
        }
        .transactions-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .transaction-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            transition: all 0.3s ease;
        }

        .transaction-item:hover {
            background: rgba(255, 255, 255, 0.04);
            transform: translateX(2px);
        }

        .trans-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .trans-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
        }

        .trans-details {
            display: flex;
            flex-direction: column;
        }

        .trans-desc {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .trans-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .trans-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .trans-monto {
            font-weight: 700;
            color: var(--danger);
            font-size: 1.05rem;
        }

        .btn-delete {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn-delete:hover {
            color: var(--danger);
            background: rgba(239, 68, 68, 0.1);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Biometrics Settings View CSS */
        .section-view {
            display: none;
        }

        .section-view.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Toggle switch */
        .switch-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            padding: 20px;
            border-radius: 16px;
            margin-top: 15px;
        }

        .switch-label-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .switch-title {
            font-weight: 600;
            font-size: 1rem;
        }

        .switch-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            max-width: 450px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
            flex-shrink: 0;
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
            background-color: rgba(255, 255, 255, 0.1);
            transition: .3s;
            border-radius: 34px;
            border: 1px solid var(--border-color);
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary);
        }

        input:focus + .slider {
            box-shadow: 0 0 1px var(--primary);
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        /* Status Banner for notifications */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #1f2937;
            border: 1px solid var(--border-color);
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 2000;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-success { border-left: 4px solid var(--success); }
        .toast-error { border-left: 4px solid var(--danger); }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo">
            💰 <span>Gestor Gastos</span>
        </div>

        <div class="profile-card">
            <div class="profile-img-container">
                <img src="<?= $fotoSrc ?>" alt="Foto Perfil">
            </div>
            <div class="profile-info">
                <div class="profile-name"><?= htmlspecialchars($user['nombre']) ?></div>
                <div class="profile-email"><?= htmlspecialchars($user['correo']) ?></div>
            </div>
        </div>

        <ul class="menu-list">
            <li class="menu-item active" id="menu-resumen" onclick="switchTab('resumen')">
                <a>📊 Resumen</a>
            </li>
            <li class="menu-item" id="menu-seguridad" onclick="switchTab('seguridad')">
                <a>🔐 Seguridad</a>
            </li>
            <li class="menu-item logout-btn">
                <a href="logout.php">➔ Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- MAIN PANEL -->
    <div class="main-content">
        <header>
            <div class="header-title">
                <h1>Hola, <?= htmlspecialchars(explode(' ', $user['nombre'])[0]) ?> 👋</h1>
                <p id="current-date">Cargando fecha...</p>
            </div>
        </header>

        <!-- VIEW 1: RESUMEN (EXPENSE MANAGER) -->
        <div id="view-resumen" class="section-view active">
            <!-- Stat Cards -->
            <div class="overview-grid">
                <div class="stat-card balance">
                    <span class="stat-label">Saldo Disponible</span>
                    <span class="stat-val" id="display-saldo">$0.00</span>
                    <span class="stat-sub">Calculado en base a tus gastos</span>
                </div>
                <div class="stat-card budget">
                    <span class="stat-label">Presupuesto Mensual</span>
                    <span class="stat-val">$<?= number_format($presupuestoMensual, 2) ?></span>
                    <span class="stat-sub">Límite de consumo sugerido</span>
                </div>
                <div class="stat-card spent">
                    <span class="stat-label">Total Gastado</span>
                    <span class="stat-val" id="display-total-gastado">$0.00</span>
                    <span class="stat-sub" id="display-total-count">0 movimientos registrados</span>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="dashboard-layout">
                <!-- Left: Add Expense Form -->
                <div class="panel">
                    <div class="panel-title">✍ Agregar Nuevo Gasto</div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <input type="text" id="descripcion" placeholder="Ej. Almuerzo, Uber, Netflix" required>
                    </div>

                    <div class="form-group">
                        <label for="monto">Monto ($)</label>
                        <input type="number" id="monto" step="0.01" min="0.01" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label for="categoria">Categoría</label>
                        <select id="categoria">
                            <option value="Comida">🍔 Comida</option>
                            <option value="Transporte">🚗 Transporte</option>
                            <option value="Entretenimiento">🎬 Entretenimiento</option>
                            <option value="Servicios">💡 Servicios</option>
                            <option value="Hogar">🏠 Hogar</option>
                            <option value="Otros">📦 Otros</option>
                        </select>
                    </div>

                    <button class="btn-add" onclick="agregarGasto()">
                        Registrar Gasto ➔
                    </button>
                </div>

                <!-- Right: Recent Transactions -->
                <div class="panel">
                    <div class="panel-title">⏰ Historial de Movimientos</div>
                    <div class="transactions-list" id="transacciones-contenedor">
                        <!-- Transacciones cargadas dinámicamente -->
                        <div class="empty-state">No hay gastos registrados todavía.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW 2: SEGURIDAD (BIOMETRICS TOGGLE) -->
        <div id="view-seguridad" class="section-view">
            <div class="panel">
                <div class="panel-title">🔐 Ajustes de Seguridad</div>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.5;">
                    Configura opciones avanzadas para proteger y agilizar el acceso a tu cuenta en esta plataforma.
                </p>

                <div class="switch-container">
                    <div class="switch-label-group">
                        <span class="switch-title">Habilitar Ingreso con Huella</span>
                        <span class="switch-desc">
                            Permite iniciar sesión de forma rápida escaneando tu huella dactilar (simulada por token local) en este navegador web sin necesidad de escribir tu contraseña.
                        </span>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="biometricToggle" onchange="toggleBiometria(this)">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div id="toast" class="toast">
        <span id="toast-icon">✔</span>
        <span id="toast-message">Mensaje de notificación</span>
    </div>

    <script>
        const userEmail = "<?= $user['correo'] ?>";
        const totalBudget = <?= $presupuestoMensual ?>;
        
        // Load Date
        document.addEventListener('DOMContentLoaded', () => {
            const dateElement = document.getElementById("current-date");
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const today = new Date();
            dateElement.innerHTML = today.toLocaleDateString('es-ES', options);
            
            // Check biometric configuration on load
            checkBiometricToggle();

            // Load gastos
            cargarGastos();
        });

        // Tab Switcher
        function switchTab(tab) {
            document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.section-view').forEach(el => el.classList.remove('active'));
            
            document.getElementById(`menu-${tab}`).classList.add('active');
            document.getElementById(`view-${tab}`).classList.add('active');
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            const msg = document.getElementById('toast-message');
            
            toast.className = 'toast show ' + (type === 'success' ? 'toast-success' : 'toast-error');
            icon.innerHTML = type === 'success' ? '✔' : '❌';
            msg.innerHTML = message;
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Load expenses from database
        async function cargarGastos() {
            try {
                const response = await fetch("api/get_gastos.php");
                const res = await response.json();
                
                if (res.success) {
                    actualizarGastosUI(res.gastos, res.total_gastado);
                } else {
                    console.error("Error cargando gastos:", res.message);
                }
            } catch (e) {
                console.error("Error de conexión al cargar gastos", e);
            }
        }

        // Map Category to Emoji
        function getCategoriaEmoji(cat) {
            const mapping = {
                'Comida': '🍔',
                'Transporte': '🚗',
                'Entretenimiento': '🎬',
                'Servicios': '💡',
                'Hogar': '🏠',
                'Otros': '📦'
            };
            return mapping[cat] || '💰';
        }

        // Render UI with expenses
        function actualizarGastosUI(gastos, totalGastado) {
            const container = document.getElementById('transacciones-contenedor');
            const totalDisplay = document.getElementById('display-total-gastado');
            const saldoDisplay = document.getElementById('display-saldo');
            const countDisplay = document.getElementById('display-total-count');
            
            // Format displays
            totalDisplay.innerHTML = `$${totalGastado.toFixed(2)}`;
            const saldo = totalBudget - totalGastado;
            saldoDisplay.innerHTML = `$${saldo.toFixed(2)}`;
            
            if (saldo < 0) {
                saldoDisplay.style.color = '#ef4444'; // Red if negative
            } else {
                saldoDisplay.style.color = ''; // Default text
            }
            
            countDisplay.innerHTML = `${gastos.length} ${gastos.length === 1 ? 'movimiento registrado' : 'movimientos registrados'}`;

            if (gastos.length === 0) {
                container.innerHTML = `<div class="empty-state">No hay gastos registrados todavía.</div>`;
                return;
            }

            let html = '';
            gastos.forEach(g => {
                const emoji = getCategoriaEmoji(g.categoria);
                const fechaFormat = new Date(g.fecha).toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                html += `
                <div class="transaction-item" id="gasto-row-${g.id_gasto}">
                    <div class="trans-left">
                        <div class="trans-icon">${emoji}</div>
                        <div class="trans-details">
                            <span class="trans-desc">${escapeHtml(g.descripcion)}</span>
                            <span class="trans-meta">${g.categoria} • ${fechaFormat}</span>
                        </div>
                    </div>
                    <div class="trans-right">
                        <span class="trans-monto">-$${parseFloat(g.monto).toFixed(2)}</span>
                        <button class="btn-delete" onclick="eliminarGasto(${g.id_gasto})" title="Eliminar gasto">
                            🗑️
                        </button>
                    </div>
                </div>
                `;
            });
            container.innerHTML = html;
        }

        // Add expense
        async function agregarGasto() {
            const descInput = document.getElementById('descripcion');
            const montoInput = document.getElementById('monto');
            const catSelect = document.getElementById('categoria');
            
            const descripcion = descInput.value.trim();
            const monto = parseFloat(montoInput.value);
            const categoria = catSelect.value;
            
            if (!descripcion || isNaN(monto) || monto <= 0) {
                showToast("Por favor, ingresa una descripción y un monto válido.", "error");
                return;
            }
            
            try {
                const response = await fetch("api/add_gasto.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ descripcion, monto, categoria })
                });
                
                const res = await response.json();
                if (res.success) {
                    showToast("Gasto registrado exitosamente.");
                    descInput.value = '';
                    montoInput.value = '';
                    cargarGastos();
                } else {
                    showToast(res.message || "Error al agregar gasto.", "error");
                }
            } catch (e) {
                showToast("Error de conexión.", "error");
            }
        }

        // Delete expense
        async function eliminarGasto(idGasto) {
            if (!confirm("¿Estás seguro de que deseas eliminar este gasto?")) return;
            
            try {
                const response = await fetch("api/delete_gasto.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id_gasto: idGasto })
                });
                
                const res = await response.json();
                if (res.success) {
                    showToast("Gasto eliminado correctamente.");
                    
                    // Animate row removal
                    const row = document.getElementById(`gasto-row-${idGasto}`);
                    if (row) {
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            cargarGastos();
                        }, 300);
                    } else {
                        cargarGastos();
                    }
                } else {
                    showToast(res.message || "Error al eliminar gasto.", "error");
                }
            } catch (e) {
                showToast("Error de conexión.", "error");
            }
        }

        // Escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // ========================================
        // Biometrics Management
        // ========================================
        function checkBiometricToggle() {
            const toggle = document.getElementById('biometricToggle');
            const token = localStorage.getItem(`bio_token_${userEmail}`);
            const isDbBiometricEnabled = <?= $user['biometrico'] == 1 ? 'true' : 'false' ?>;
            
            // Enable if both local token and DB state match
            toggle.checked = (token && isDbBiometricEnabled);
        }

        async function toggleBiometria(checkbox) {
            const enable = checkbox.checked;
            
            try {
                const response = await fetch("api/biometrico.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        action: "register",
                        enable: enable
                    })
                });
                
                const res = await response.json();
                
                if (res.success) {
                    if (enable) {
                        // Save token locally
                        localStorage.setItem(`bio_token_${userEmail}`, res.token);
                        showToast("Autenticación biométrica habilitada en este dispositivo.");
                    } else {
                        // Remove local token
                        localStorage.removeItem(`bio_token_${userEmail}`);
                        showToast("Autenticación biométrica deshabilitada.");
                    }
                } else {
                    checkbox.checked = !enable; // revert checkbox
                    showToast(res.message || "Error al configurar biometría.", "error");
                }
            } catch (e) {
                checkbox.checked = !enable; // revert checkbox
                showToast("Error de conexión al configurar biometría.", "error");
            }
        }
    </script>
</body>
</html>
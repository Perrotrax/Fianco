<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/api/conexion.php';

$userId = $_SESSION['id_usuario'];

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: logout.php");
    exit;
}

if (!empty($user['foto_perfil'])) {
    $mime = 'image/png';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($user['foto_perfil']);
    }
    $fotoSrc = 'data:' . $mime . ';base64,' . base64_encode($user['foto_perfil']);
} else {
    $fotoSrc = 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
}

$presupuestoMensual = isset($user['presupuesto']) ? floatval($user['presupuesto']) : 0.00;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Gastos - Dashboard Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime('css/style.css') ?>">
    <link rel="stylesheet" href="css/sweetalert.css?v=<?= filemtime('css/sweetalert.css') ?>">
    
    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        /* ==========================================================================
           TICKELIA PREMIUM 2026 - COMPLEMENTOS DE DISEÑO DE LUJO
           ========================================================================== */

        /* SweetAlert2 - Dark Gold Theme */
        /* CRITICAL: z-index must exceed .modal-overlay (2000) so alerts are always on top and clickable */
        .swal2-container {
            z-index: 99999 !important;
        }
        .swal2-backdrop-show {
            z-index: 99998 !important;
        }
        .swal2-popup {
            background: rgba(13, 13, 18, 0.96) !important;
            color: var(--text-primary) !important;
            border: 1px solid rgba(212, 175, 55, 0.25) !important;
            box-shadow: var(--shadow-xl) !important;
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
            color: var(--text-secondary) !important;
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
            /* FIX: evita que button { width:100%; display:flex } rompa el SweetAlert */
            width: auto !important;
            display: inline-flex !important;
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
            /* FIX: evita que button { width:100%; display:flex } rompa el SweetAlert */
            width: auto !important;
            display: inline-flex !important;
        }
        
        .swal2-actions {
            pointer-events: auto !important;
        }

        /* Sidebar active overrides to align with structure */
        .menu-item.active a {
            background-color: rgba(212, 175, 55, 0.12) !important;
            color: var(--primary) !important;
            border-left: 4px solid var(--primary) !important;
            font-weight: 600;
        }

        /* Dev Mode Switch in Header */
        .dev-switch-wrapper {
            display: flex; 
            align-items: center; 
            gap: 8px;
            background: rgba(255, 255, 255, 0.05); 
            padding: 6px 14px; 
            border-radius: 20px;
            font-size: 0.82rem; 
            font-weight: 600; 
            color: var(--text-secondary);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
        }
        
        .switch { 
            position: relative; 
            display: inline-block; 
            width: 36px; 
            height: 20px; 
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
            transition: .4s; 
            border-radius: 34px; 
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .slider:before { 
            position: absolute; 
            content: ""; 
            height: 12px; 
            width: 12px; 
            left: 3px; 
            bottom: 3px; 
            background-color: #a89968; 
            transition: .4s; 
            border-radius: 50%; 
        }
        
        input:checked + .slider { 
            background-color: var(--primary); 
            border-color: rgba(212, 175, 55, 0.3);
        }
        
        input:checked + .slider:before { 
            transform: translateX(16px); 
            background-color: #060608; 
        }

        /* Dev Logs and panels */
        .dev-code-panel {
            background: rgba(10, 10, 14, 0.85); 
            border: 1px solid rgba(255, 255, 255, 0.05); 
            border-radius: 12px; 
            padding: 18px;
            font-family: 'Fira Code', monospace; 
            font-size: 0.82rem; 
            color: #d4cfb4; 
            overflow-x: auto;
            max-height: 250px;
            box-shadow: inset 0 4px 15px rgba(0, 0, 0, 0.7);
        }
        
        .dev-meta { 
            font-size: 0.75rem; 
            color: var(--text-muted); 
            margin-bottom: 8px; 
            font-weight: 600; 
            letter-spacing: 0.08em; 
            text-transform: uppercase; 
        }

        /* Premium Toasts */
        .toast {
            position: fixed; 
            bottom: 30px; 
            right: 30px; 
            background: rgba(13, 13, 18, 0.95); 
            padding: 14px 24px; 
            border-radius: 12px;
            box-shadow: var(--shadow-xl); 
            border-left: 4px solid var(--primary);
            transform: translateY(100px); 
            opacity: 0; 
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); 
            z-index: 9999; 
            font-weight: 600;
            color: var(--text-primary);
            border-top: 1px solid rgba(255, 255, 255, 0.04);
            border-right: 1px solid rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(12px);
        }
        
        .toast.show { 
            transform: translateY(0); 
            opacity: 1; 
        }

        /* Floating Action Camera for Mobile */
        .fab-camera {
            display: none;
            position: fixed; 
            bottom: 30px; 
            right: 30px;
            width: 60px; 
            height: 60px; 
            background: linear-gradient(135deg, #f3d075 0%, #d4af37 100%); 
            color: #060608;
            border-radius: 50%; 
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.35);
            align-items: center; 
            justify-content: center; 
            font-size: 1.6rem;
            z-index: 1000; 
            cursor: pointer; 
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .fab-camera:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 12px 35px rgba(212, 175, 55, 0.45); 
        }
        
        .fab-camera:active { 
            transform: translateY(0) scale(0.95); 
        }

        /* Modern Glassmorphic Modals */
        .modal-overlay {
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%;
            background: rgba(0, 0, 0, 0.7); 
            backdrop-filter: blur(8px); 
            -webkit-backdrop-filter: blur(8px);
            z-index: 2000; 
            align-items: center; 
            justify-content: center;
        }
        
        .modal-overlay .modal-content {
            background: rgba(22, 22, 28, 0.95); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
            width: 90%; 
            max-width: 450px; 
            border-radius: 20px;
            padding: 30px; 
            box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 20px rgba(212, 175, 55, 0.05);
            animation: modalIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            color: var(--text-primary);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        @keyframes modalIn { 
            from { opacity: 0; transform: translateY(20px) scale(0.96); } 
            to { opacity: 1; transform: translateY(0) scale(1); } 
        }

        /* Empty state placeholders */
        .placeholder-box {
            background: rgba(212, 175, 55, 0.02); 
            border: 2px dashed rgba(212, 175, 55, 0.15);
            border-radius: 16px; 
            padding: 45px 20px; 
            text-align: center; 
            color: var(--text-muted);
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .placeholder-box:hover { 
            border-color: rgba(212, 175, 55, 0.35); 
        }
        
        .placeholder-box span { 
            font-size: 2.2rem; 
            display: block; 
            margin-bottom: 12px; 
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3)); 
        }

        /* RESPONSIVE LAYOUT TABLES */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; }
            .hamburger { display: block; }
            .summary-grid { grid-template-columns: 1fr; gap: 16px; }
            .add-form-row { display: none; }
            .fab-camera { display: flex; }
            
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 15px; padding: 12px; background: rgba(255,255,255,0.02); }
            td { border: none; border-bottom: 1px solid rgba(255, 255, 255, 0.05); position: relative; padding-left: 45%; text-align: right; }
            td:last-child { border-bottom: 0; }
            td:before { 
                position: absolute; 
                top: 15px; 
                left: 15px; 
                width: 40%; 
                white-space: nowrap; 
                font-weight: 600; 
                text-align: left; 
                color: var(--text-muted); 
                font-size: 0.72rem; 
                text-transform: uppercase; 
                letter-spacing: 0.05em; 
            }
            
            td:nth-of-type(1):before { content: "Estado"; }
            td:nth-of-type(2):before { content: "Fecha"; }
            td:nth-of-type(3):before { content: "Descripción"; }
            td:nth-of-type(4):before { content: "Categoría"; }
            td:nth-of-type(5):before { content: "Importe"; }
            td:nth-of-type(6):before { content: "Acción"; }
        }
    </style>
</head>

<body>
    <!-- CAMERA INPUT HIDDEN -->
    <input type="file" id="cameraInput" accept="image/*" capture="environment" style="display:none;" onchange="handleTicketPhoto(event)">

    <!-- TICKET MODAL -->
    <div id="ticketModal" class="modal-overlay">
        <div class="modal-content">
            <h3 style="margin-bottom:20px; font-size:1.4rem;">🧾 Registrar Ticket</h3>
            <img id="ticketPreview" src="" style="width:100%; max-height:180px; object-fit:cover; border-radius:12px; margin-bottom:20px; display:none; border:1px solid var(--border);">
            
            <div id="ocrLoader" style="display:none; text-align:center; padding: 20px; color:var(--text-muted);">
                <div style="font-size:2rem; margin-bottom:10px; animation: spin 1s infinite linear;">⚙️</div>
                Analizando datos del ticket...
            </div>
            
            <div id="ticketFormInputs" style="display:none;">
                <div class="form-group">
                    <label>Descripción Extraída</label>
                    <input type="text" id="ticketDesc" placeholder="Ej. Almuerzo de trabajo">
                </div>
                <div class="form-group">
                    <label>Importe Extraído ($)</label>
                    <input type="number" id="ticketMonto" step="0.01" placeholder="Monto detectado...">
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <select id="ticketCategoria">
                        <option value="Comida">Comida</option>
                        <option value="Transporte">Transporte</option>
                        <option value="Servicios">Servicios</option>
                        <option value="Hogar">Alojamiento/Hogar</option>
                        <option value="Otros">Otros</option>
                    </select>
                </div>
            </div>
            
            <div style="display:flex; gap:10px; margin-top:25px;">
                <button class="btn-cancel" onclick="cerrarModalTicket()">Cancelar</button>
                <button class="btn-add" id="btnGuardarTicket" style="flex:1; display:none;" onclick="guardarTicketFromModal()">Guardar Gasto</button>
            </div>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            Fianco<span style="color:rgba(255,255,255,0.5);font-size:0.5em;">PRO</span>
        </div>
        <div class="menu-list-container">
            

            <ul class="menu-list">
                
                <li class="menu-item active" id="menu-resumen"><a onclick="switchTab('resumen')">🏠 Inicio</a></li>
                <li class="menu-item" id="menu-wallet"><a onclick="switchTab('wallet')">🪙 Wallet</a></li>
                <li class="menu-item" id="menu-anticipos"><a onclick="switchTab('anticipos')">💸 Anticipos</a></li>
                <li class="menu-item" id="menu-aprobacion"><a onclick="switchTab('aprobacion')">📋 Aprobación <span id="sidebar-aprobaciones-badge" class="sidebar-badge" style="display:none;">0</span></a></li>
                <li class="menu-item" id="menu-consultas"><a onclick="switchTab('consultas')">🔎 Consultas</a></li>
                <li class="menu-item" id="menu-estadisticas"><a onclick="switchTab('estadisticas')">📊 Estadísticas</a></li>
                
                
                <li class="menu-item" id="menu-Limitess"><a onclick="switchTab('Limitess')">🗃️ Limitess</a></li>
                <li class="menu-item" id="menu-proyectos"><a onclick="switchTab('proyectos')">📁 Proyectos/Viajes</a></li>
                
                <li class="menu-item" id="menu-seguridad" style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1); padding-top:10px;">
                    <a onclick="switchTab('seguridad')">🔐 Seguridad</a>
                </li>
                <li class="menu-item" id="menu-perfil">
                    <a onclick="switchTab('perfil')">👤 Mi Perfil</a>
                </li>
                <li class="menu-item"><a href="logout.php" style="color: #ef5350;">🚪 Cerrar Sesión</a></li>
            </ul>
        </div>
      
    </div>
    

    <!-- MAIN -->
    <div class="main-content">
        <!-- HEADER -->
        <header class="top-header">
            <div class="header-left">
                <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                <div style="font-weight: 600; font-size: 1.1rem;" class="normal-only" id="header-title-text">Gestión de Gastos</div>
                <div style="font-weight: 600; font-size: 1.1rem; font-family: monospace;" class="dev-only">/api/v1/dashboard</div>
            </div>
            <div class="header-right">
                <button class="btn-add normal-only" style="height: 36px; font-size: 0.85rem;" onclick="document.getElementById('cameraInput').click()">📸 Escanear Ticket</button>
                <div class="dev-switch-wrapper">
                    <span>Modo Dev</span>
                    <label class="switch">
                        <input type="checkbox" id="devToggle" onchange="toggleDevMode(this)">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </header>

        <!-- VIEW 1: RESUMEN (HOME) -->
        <div class="content-area tab-view" id="view-resumen">
            <!-- TOP SECTION: Balance Card (Left) & Accounts Grid (Right) -->
            <div class="row g-4 mb-4">
                <!-- Left: Balance Card -->
                <div class="col-md-6">
                    <div class="balance-card h-100">
                        <div class="balance-title">Saldo Wallet Pro</div>
                        <div class="balance-amount" id="display-saldo">$0.00</div>
                        
                        <div class="balance-distribution">
                            <div class="distribution-row">
                                <div class="distribution-meta">
                                    <span>🪙 Saldo en Wallet</span>
                                    <span id="distribution-wallet-val">$0.00</span>
                                </div>
                                <div class="distribution-bar-bg">
                                    <div class="distribution-bar" id="distribution-wallet-bar" style="width: 0%; background: #D4AF37;"></div>
                                </div>
                            </div>
                            
                            <div class="distribution-row">
                                <div class="distribution-meta">
                                    <span>💵 Límite Presupuestal</span>
                                    <span id="distribution-budget-val">$<?= number_format($presupuestoMensual, 2) ?></span>
                                </div>
                                <div class="distribution-bar-bg">
                                    <div class="distribution-bar" id="distribution-budget-bar" style="width: 100%; background: #F5E6D3;"></div>
                                </div>
                            </div>
                            
                            <div class="distribution-row">
                                <div class="distribution-meta">
                                    <span>📉 Gastos Aprobados</span>
                                    <span id="distribution-spent-val">$0.00</span>
                                </div>
                                <div class="distribution-bar-bg">
                                    <div class="distribution-bar" id="distribution-spent-bar" style="width: 0%; background: #E53935;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right: Accounts Grid -->
                <div class="col-md-6">
                    <div class="row g-3 h-100 align-content-between">
                        <div class="col-6">
                            <div class="accounts-card card-variant-gold h-100">
                                <span class="accounts-card-label">🪙 Billetera</span>
                                <div class="accounts-card-val" id="grid-wallet-val">$0.00</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="accounts-card card-variant-cream h-100">
                                <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                                    <span class="accounts-card-label">💵 Presupuesto</span>
                                    <button onclick="editarPresupuesto()" style="background:none;border:none;cursor:pointer;font-size:0.9rem;color:var(--text-muted);padding:0;margin-top:-2px;">✎</button>
                                </div>
                                <div class="accounts-card-val" id="grid-budget-val">$<?= number_format($presupuestoMensual, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="accounts-card card-variant-black h-100">
                                <span class="accounts-card-label">📉 Total Gastado</span>
                                <div class="accounts-card-val" id="grid-spent-val">$0.00</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="accounts-card card-variant-gold h-100">
                                <span class="accounts-card-label">📁 Proyectos & Viajes</span>
                                <div class="accounts-card-val" id="grid-extras-val" style="font-size:0.85rem; font-weight:600; line-height:1.3; margin-top:8px;">
                                    0 Proyectos<br>0 Viajes
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI MINI CARDS -->
            <div class="row g-3 mb-4" id="kpi-grid">
                <div class="col-6 col-lg-3">
                    <div class="panel h-100" style="padding:15px; text-align:center;">
                        <div style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; margin-bottom:6px;">% Presupuesto Usado</div>
                        <div id="kpi-pct-presupuesto" style="font-size:1.5rem; font-weight:700; color:var(--text-main);">—</div>
                        <div id="kpi-pct-bar-container" class="progress-container" style="margin-top:8px;">
                            <div id="kpi-pct-bar" class="progress-bar" style="width:0%; background:var(--badge-green);"></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="panel h-100" style="padding:15px; text-align:center;">
                        <div style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; margin-bottom:6px;">Gasto Diario Promedio</div>
                        <div id="kpi-diario" style="font-size:1.5rem; font-weight:700; color:var(--text-main);">—</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="panel h-100" style="padding:15px; text-align:center;">
                        <div style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; margin-bottom:6px;">Días Restantes</div>
                        <div id="kpi-dias" style="font-size:1.5rem; font-weight:700; color:var(--badge-blue);">—</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="panel h-100" style="padding:15px; text-align:center;">
                        <div style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; margin-bottom:6px;">Proyección Fin de Mes</div>
                        <div id="kpi-proyeccion" style="font-size:1.5rem; font-weight:700; color:var(--text-main);">—</div>
                    </div>
                </div>
            </div>

            <!-- ALERTA DE PRESUPUESTO -->
            <div id="budget-alert-box" style="display:none; background: rgba(239,83,80,0.1); border:1px solid rgba(239,83,80,0.3); border-radius:10px; padding:12px 20px; align-items:center; gap:12px;" class="mb-4">
                <span style="font-size:1.5rem;">⚠️</span>
                <div>
                    <strong style="color:#C62828;">Presupuesto excedido</strong>
                    <div id="budget-alert-msg" style="font-size:0.85rem; color:var(--text-muted);"></div>
                </div>
            </div>

            <!-- CHARTS -->
            <div class="row g-4 mb-4">
                <div class="col-md-8">
                    <div class="panel h-100">
                        <div class="panel-title">
                            <span>Evolución de Gasto (7 Días)</span>
                            <span class="dev-only" style="font-size:0.75rem; font-family:monospace; color:var(--dev-accent);">[Render: Chart.js Line]</span>
                        </div>
                        <div style="position: relative; height: 250px; width: 100%;">
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel h-100">
                        <div class="panel-title">
                            <span>Gasto por Categoría</span>
                            <span class="dev-only" style="font-size:0.75rem; font-family:monospace; color:var(--dev-accent);">[Render: Chart.js Donut]</span>
                        </div>
                        <div class="chart-overlay-container">
                            <canvas id="donutChart"></canvas>
                            <div class="chart-center-value">
                                <span class="chart-center-title">Gasto Total</span>
                                <span class="chart-center-amount" id="chart-center-spent-amount">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-container">
                <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 20px; background: rgba(13, 13, 18, 0.95); border-bottom: 2px solid var(--primary);">
                    <span style="font-weight:600; font-size:1rem; color: var(--text-primary);">Transacciones recientes</span>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="text" id="buscarGasto" placeholder="Buscar..." oninput="filtrarTablaLocal()" style="padding:6px 12px; background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 8px; font-size: 0.85rem; width: 150px; color: var(--text-primary);">
                        <span style="font-size:0.8rem; color:var(--text-muted);" id="tabla-count"></span>
                    </div>
                </div>
                <table id="gastosTable">
                    <thead>
                        <tr>
                            <th class="dev-only">ID</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Proyecto / Viaje</th>
                            <th>Pago</th>
                            <th>Subtipo</th>
                            <th style="text-align:right;">Importe</th>
                            <th style="text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="transacciones-tbody">
                        <tr><td colspan="9" style="text-align:center;">Cargando datos...</td></tr>
                    </tbody>
                </table>
                <!-- PAGINACION -->
                <div id="paginacion-container" style="display:flex; justify-content:center; align-items:center; gap:8px; padding:15px; border-top:1px solid var(--border);"></div>
            </div>

            <!-- DEV MODE LOGS -->
            <div class="panel dev-block">
                <div class="panel-title">🛠️ Terminal / API Logs</div>
                <div class="dev-code-panel">
                    <div class="dev-meta" id="dev-meta-info">// Esperando peticiones...</div>
                    <pre id="dev-json-output">{}</pre>
                </div>
            </div>
        </div>

        <!-- VIEW WALLET -->
        <div class="content-area tab-view" id="view-wallet" style="display:none; flex-direction:column; gap:30px;">
            <div class="grid-two-cols">
                <div class="panel">
                    <div class="panel-title">🪙 Mi Billetera Digital (Wallet)</div>
                    <div class="wallet-card">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <div class="wallet-balance-label">Saldo Disponible</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #FFE082;">WALLET PRO</div>
                        </div>
                        <div class="wallet-balance-val" id="wallet-balance-card-val">$0.00</div>
                        <div class="wallet-chip"></div>
                        <div class="wallet-number">**** **** **** <?= str_pad($userId, 4, '0', STR_PAD_LEFT) ?></div>
                        <div class="wallet-owner">
                            <div>
                                <span style="font-size:0.75rem; text-transform:uppercase; opacity:0.8; display:block;">Titular</span>
                                <strong><?= htmlspecialchars($user['nombre']) ?></strong>
                            </div>
                            <div style="font-size:0.85rem; opacity:0.8;">VENCE: 12/30</div>
                        </div>
                    </div>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:20px;">
                        <div>
                            <h4 style="margin-bottom:10px; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase;">Cargar Saldo</h4>
                            <div style="display:flex; gap:10px;">
                                <input type="number" id="walletRecargaMonto" step="0.01" placeholder="0.00">
                                <button class="btn-add" onclick="walletAction('deposit')" style="height:42px;">Cargar</button>
                            </div>
                        </div>
                        <div>
                            <h4 style="margin-bottom:10px; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase;">Retirar Saldo</h4>
                            <div style="display:flex; gap:10px;">
                                <input type="number" id="walletRetiroMonto" step="0.01" placeholder="0.00">
                                <button class="btn-cancel" onclick="walletAction('withdraw')" style="height:42px; border-color:var(--primary); color:var(--primary);">Retirar</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="panel">
                    <div class="panel-title">🕒 Historial de la Billetera</div>
                    <div class="table-container" style="max-height:380px; overflow-y:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th style="text-align:right;">Monto</th>
                                </tr>
                            </thead>
                            <tbody id="wallet-tx-tbody">
                                <tr><td colspan="3" style="text-align:center;">Cargando transacciones...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW ANTICIPOS -->
        <div class="content-area tab-view" id="view-anticipos" style="display:none; flex-direction:column; gap:30px;">
            <div class="grid-two-cols">
                <div class="panel">
                    <div class="panel-title">💸 Solicitar Anticipo</div>
                    <div class="form-group">
                        <label>Monto del Anticipo ($)</label>
                        <input type="number" id="anticipoMonto" step="0.01" placeholder="0.00">
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label>Motivo / Justificación</label>
                        <input type="text" id="anticipoMotivo" placeholder="Ej. Viáticos para viaje a CDMX">
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label>Asociar a Viaje (Opcional)</label>
                        <select id="anticipoViaje">
                            <option value="">General (Ninguno)</option>
                        </select>
                    </div>
                    <button class="btn-add" onclick="solicitarAnticipo()" style="margin-top:20px; width:100%;">Enviar Solicitud</button>
                    <p style="margin-top:15px; font-size:0.8rem; color:var(--text-muted);">* Al aprobarse un anticipo, el saldo se transferirá automáticamente a tu Wallet.</p>
                </div>
                
                <div class="panel">
                    <div class="panel-title">📋 Solicitudes de Anticipos</div>
                    <div class="table-container" style="max-height:380px; overflow-y:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Motivo / Viaje</th>
                                    <th>Estado</th>
                                    <th style="text-align:right;">Importe</th>
                                </tr>
                            </thead>
                            <tbody id="anticipos-tbody">
                                <tr><td colspan="4" style="text-align:center;">Cargando anticipos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW APROBACION -->
        <div class="content-area tab-view" id="view-aprobacion" style="display:none; flex-direction:column; gap:30px;">
            <div class="panel">
                <div class="panel-title">📋 Flujo de Aprobación Corporativa</div>
                <p style="margin-bottom:20px; font-size:0.95rem; color:var(--text-muted);">Como administrador/supervisor, aprueba o rechaza los gastos y anticipos pendientes.</p>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Concepto</th>
                                <th>Fecha Registro</th>
                                <th style="text-align:right;">Monto</th>
                                <th style="text-align:center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="aprobaciones-tbody">
                            <tr><td colspan="5" style="text-align:center;">No hay solicitudes pendientes de aprobación</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- VIEW CONSULTAS -->
        <div class="content-area tab-view" id="view-consultas" style="display:none; flex-direction:column; gap:30px;">
            <div class="panel">
                <div class="panel-title">🔎 Filtros Avanzados</div>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:15px;">
                    <div class="form-group">
                        <label>Subtipo / Categoría</label>
                        <select id="consultasCategoria">
                            <option value="">Todas</option>
                            <option value="Comida">Comida</option>
                            <option value="Transporte">Transporte</option>
                            <option value="Entretenimiento">Entretenimiento</option>
                            <option value="Servicios">Servicios</option>
                            <option value="Hogar">Alojamiento/Hogar</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Proyecto</label>
                        <select id="consultasProyecto">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Viaje</label>
                        <select id="consultasViaje">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Método de Pago</label>
                        <select id="consultasMetodoPago">
                            <option value="">Todos</option>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Wallet">Wallet</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha Inicio</label>
                        <input type="date" id="consultasFechaInicio">
                    </div>
                    <div class="form-group">
                        <label>Fecha Fin</label>
                        <input type="date" id="consultasFechaFin">
                    </div>
                </div>
                <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                    <button class="btn-cancel" onclick="limpiarFiltrosConsultas()">Limpiar</button>
                    <button class="btn-add" onclick="filtrarConsultas()">Aplicar Filtro</button>
                </div>
            </div>
            
            <div class="panel">
                <div class="panel-title">
                    <span>Resultados de Búsqueda</span>
                    <div>
                        <button class="btn-cancel" onclick="exportarConsultas('json')" style="height:32px; padding:0 12px; font-size:0.8rem; border-color:var(--primary); color:var(--primary);">JSON</button>
                        <button class="btn-add" onclick="exportarConsultas('csv')" style="height:32px; padding:0 12px; font-size:0.8rem;">CSV</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Proyecto / Viaje</th>
                                <th>Pago</th>
                                <th>Estado</th>
                                <th style="text-align:right;">Monto</th>
                            </tr>
                        </thead>
                        <tbody id="consultas-tbody">
                            <tr><td colspan="7" style="text-align:center; color:var(--text-muted);">Realiza una consulta para ver resultados</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- VIEW ESTADISTICAS -->
        <div class="content-area tab-view" id="view-estadisticas" style="display:none; flex-direction:column; gap:30px;">
            <div class="grid-two-cols">
                <div class="panel">
                    <div class="panel-title">📊 Comparativa: Límite Mensual vs Gastado Real</div>
                    <div style="position:relative; height:280px; width:100%;">
                        <canvas id="statsBarChart"></canvas>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-title">📈 Historial de Saldo en Wallet</div>
                    <div style="position:relative; height:280px; width:100%;">
                        <canvas id="statsLineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW LimitesS -->
        <div class="content-area tab-view" id="view-Limitess" style="display:none; flex-direction:column; gap:30px;">
            <div class="grid-two-cols">
                <div class="panel">
                    <div class="panel-title">🗃️ Límites Presupuestarios por Categoría</div>
                    <p style="margin-bottom:20px; font-size:0.95rem; color:var(--text-muted);">Configura topes máximos mensuales para las categorías de gastos. Deja el límite en $0.00 para removerlo.</p>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Categoría</th>
                                    <th>Límite ($)</th>
                                    <th>% Usado</th>
                                    <th style="text-align:center;">Configurar</th>
                                </tr>
                            </thead>
                            <tbody id="Limitess-tbody">
                                <tr><td colspan="4" style="text-align:center;">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-title">🏢 Proveedores Frecuentes</div>
                    <div class="form-group">
                        <label>Nombre del Proveedor *</label>
                        <input type="text" id="provNombre" placeholder="Ej. OXXO, Walmart, Gasolinera X">
                    </div>
                    <div class="form-group" style="margin-top:10px;">
                        <label>RFC (Opcional)</label>
                        <input type="text" id="provRfc" placeholder="Ej. XAXX010101000">
                    </div>
                    <div class="form-group" style="margin-top:10px;">
                        <label>Categoría</label>
                        <select id="provCategoria">
                            <option value="">Sin categoría</option>
                            <option>Comida</option>
                            <option>Transporte</option>
                            <option>Servicios</option>
                            <option>Hogar</option>
                            <option>Entretenimiento</option>
                            <option>Otros</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-top:10px;">
                        <label>Contacto / Teléfono</label>
                        <input type="text" id="provContacto" placeholder="Ej. 55 1234 5678">
                    </div>
                    <button class="btn-add" onclick="agregarProveedor()" style="margin-top:15px; width:100%;">Agregar Proveedor</button>
                    <hr style="border:0; border-top:1px solid var(--border); margin:20px 0;">
                    <div id="proveedores-lista" style="max-height:220px; overflow-y:auto;">
                        <p style="text-align:center; color:var(--text-muted); font-size:0.9rem;">Cargando proveedores...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW PROYECTOS -->
        <div class="content-area tab-view" id="view-proyectos" style="display:none; flex-direction:column; gap:30px;">
            <div class="grid-two-cols">
                <div class="panel">
                    <div class="panel-title">📁 Nuevo Proyecto / Centro de Costos</div>
                    <div class="form-group">
                        <label>Código del Proyecto</label>
                        <input type="text" id="proyectoCodigo" placeholder="Ej. PRY-2026-01">
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label>Nombre del Proyecto</label>
                        <input type="text" id="proyectoNombre" placeholder="Ej. Desarrollo App Android">
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label>Presupuesto Asignado ($)</label>
                        <input type="number" id="proyectoPresupuesto" step="0.01" placeholder="0.00">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:15px;">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" id="proyectoFechaInicio">
                        </div>
                        <div class="form-group">
                            <label>Fecha Fin</label>
                            <input type="date" id="proyectoFechaFin">
                        </div>
                    </div>
                    <button class="btn-add" onclick="crearProyecto()" style="margin-top:20px; width:100%;">Guardar Proyecto</button>
                </div>
                
                 <div class="panel">
                    <div class="panel-title">🧭 Nuevo Viaje de Negocios</div>
                    <div class="form-group">
                        <label>Destino</label>
                        <input type="text" id="viajeDestino" placeholder="Ej. Monterrey, México">
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label>Fecha de Inicio</label>
                        <input type="date" id="viajeFechaInicio">
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label>Fecha de Fin</label>
                        <input type="date" id="viajeFechaFin">
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label>Presupuesto Viáticos ($)</label>
                        <input type="number" id="viajePresupuesto" step="0.01" placeholder="0.00">
                    </div>
                    <button class="btn-add" onclick="crearViaje()" style="margin-top:20px; width:100%;">Registrar Viaje</button>
                </div>
                
                <div class="panel">
                    <div class="panel-title">🧭 Viajes Registrados</div>
                    <div class="table-container" style="max-height:380px; overflow-y:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Destino</th>
                                    <th>Fechas</th>
                                    <th>Presupuesto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="viajes-tbody">
                                <tr><td colspan="4" style="text-align:center;">Cargando viajes...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW VIAJES -->
        <div class="content-area tab-view" id="view-viajes" style="display:none; flex-direction:column; gap:30px;">
            <div class="grid-two-cols">
               
            </div>
        </div>

        <!-- VIEW SECURITY -->
        <div class="content-area tab-view" id="view-seguridad" style="display:none;">
            <div class="panel" style="max-width: 600px; margin: 0 auto; width:100%;">
                <div class="panel-title" id="biometricPanelTitle">🔐 Ajustes de Biometría (WebAuthn)</div>
                <p id="biometricPanelDesc" style="margin-bottom: 20px; font-size: 0.95rem; color: var(--text-muted);">
                    Activa esta opción para permitir el inicio de sesión seguro usando el hardware de tu dispositivo (lector de huellas o reconocimiento facial).
                </p>
                <div style="display:flex; justify-content:space-between; align-items:center; background: rgba(255, 255, 255, 0.03); padding:20px; border-radius:10px; border: 1px solid rgba(255, 255, 255, 0.05);">
                    <div>
                        <div id="biometricLabelTitle" style="color:var(--text-primary); font-weight:600;">Usar Biometría (Huella / Face ID)</div>
                        <div id="biometricLabelSubtitle" style="color:var(--text-muted); font-size:0.8rem; margin-top:4px;">Al activar, podrás ingresar con tu huella desde la pantalla de login.</div>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="biometricToggle" onchange="toggleBiometria(this)">
                        <span class="slider"></span>
                    </label>
                </div>
                <div id="biometric-status-box" style="margin-top:15px; padding:12px 15px; background: rgba(22, 163, 74, 0.1); border: 1px solid rgba(22, 163, 74, 0.2); border-radius:8px; display:none;">
                    <p id="biometricStatusText" style="color:#4ade80; font-size:0.9rem; font-weight:500;">✅ Biometría registrada. Puedes usar huella en el login.</p>
                </div>
                <div class="dev-block" style="margin-top:20px;">
                    <div class="dev-code-panel">
                        <div class="dev-meta">// Estado WebAuthn / LocalStorage</div>
                        <pre id="dev-bio-status"></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW PERFIL -->
        <div class="content-area tab-view" id="view-perfil" style="display:none; flex-direction:column; gap:30px;">
            <div class="grid-two-cols">
                <div class="panel">
                    <div class="panel-title">👤 Datos Personales</div>
                    
                    <!-- Foto de Perfil -->
                    <div style="text-align:center; margin-bottom:25px;">
                        <div style="position:relative; display:inline-block;">
                            <img id="perfil-foto-preview" src="<?= $fotoSrc ?>" style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); box-shadow:0 4px 12px rgba(109,76,65,0.25);">
                            <label for="perfilFotoInput" style="position:absolute; bottom:0; right:0; background:var(--primary); color:white; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1rem; border:2px solid white;">📷</label>
                            <input type="file" id="perfilFotoInput" accept="image/*" style="display:none;" onchange="actualizarFotoPerfil(event)">
                        </div>
                        <p style="font-size:0.75rem; color:var(--text-muted); margin-top:8px;">Haz clic en el icono para cambiar tu foto</p>
                    </div>

                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" id="perfilNombre" value="<?= htmlspecialchars($user['nombre']) ?>">
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label>Correo Electrónico</label>
                        <input type="email" id="perfilCorreo" value="<?= htmlspecialchars($user['correo']) ?>">
                    </div>
                    <button class="btn-add" onclick="guardarDatosPerfil()" style="margin-top:20px; width:100%;">💾 Guardar Cambios</button>
                </div>

                <div class="panel">
                    <div class="panel-title">🔑 Cambiar Contraseña</div>
                    <div class="form-group">
                        <label>Contraseña Actual</label>
                        <input type="password" id="perfilPassActual" placeholder="••••••••">
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label>Nueva Contraseña</label>
                        <input type="password" id="perfilPassNueva" placeholder="Mínimo 6 caracteres">
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label>Confirmar Contraseña</label>
                        <input type="password" id="perfilPassConfirmar" placeholder="Repite la nueva contraseña">
                    </div>
                    <button class="btn-add" onclick="cambiarPassword()" style="margin-top:20px; width:100%; background:var(--sidebar-hover);">🔒 Actualizar Contraseña</button>

                    <hr style="border:0; border-top:1px solid var(--border); margin:25px 0;">

                    <div class="panel-title" style="margin-bottom:15px;">📊 Estadísticas de Cuenta</div>
                    <div id="perfil-stats" style="display:flex; flex-direction:column; gap:10px;">
                        <div style="display:flex; justify-content:space-between; padding:10px 15px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius:8px;">
                            <span style="color:var(--text-muted);">Gastos Registrados</span>
                            <strong id="perfil-stat-gastos">—</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:10px 15px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius:8px;">
                            <span style="color:var(--text-muted);">Proyectos Activos</span>
                            <strong id="perfil-stat-proyectos">—</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:10px 15px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius:8px;">
                            <span style="color:var(--text-muted);">Viajes Registrados</span>
                            <strong id="perfil-stat-viajes">—</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:10px 15px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius:8px;">
                            <span style="color:var(--text-muted);">Saldo en Wallet</span>
                            <strong id="perfil-stat-wallet">—</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- EDIT GASTO MODAL -->
    <div id="editGastoModal" class="modal-overlay">
        <div class="modal-content" style="max-width:520px;">
            <h3 style="margin-bottom:20px; font-size:1.3rem;">✏️ Editar Gasto</h3>
            <input type="hidden" id="editGastoId">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Descripción</label>
                    <input type="text" id="editDesc" placeholder="Descripción del gasto">
                </div>
                <div class="form-group">
                    <label>Importe ($)</label>
                    <input type="number" id="editMonto" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <select id="editCategoria">
                        <option>Comida</option><option>Transporte</option>
                        <option>Entretenimiento</option><option>Servicios</option>
                        <option>Hogar</option><option>Otros</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="editEstado">
                        <option>Pendiente</option><option>Aprobado</option><option>Rechazado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Método de Pago</label>
                    <select id="editMetodo">
                        <option>Efectivo</option><option>Tarjeta</option><option>Wallet</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Proyecto</label>
                    <select id="editProyecto"><option value="">General</option></select>
                </div>
                <div class="form-group">
                    <label>Viaje</label>
                    <select id="editViaje"><option value="">General</option></select>
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button class="btn-cancel" onclick="cerrarEditModal()">Cancelar</button>
                <button class="btn-add" onclick="guardarEdicionGasto()" style="flex:1;">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <!-- MODAL REGISTRAR GASTO -->
    <div id="gastoModal" class="modal-overlay">
        <div class="modal-content" style="max-width:400px; padding:20px; border: 1px solid rgba(212, 175, 55, 0.25);">
            <div style="border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 8px;">
                <h3 style="margin: 0; font-size:1.05rem; color: var(--primary);">📝 Registrar Gasto</h3>
                <button type="button" onclick="cerrarGastoModal()" style="background: none; border: none; color: #FFF; font-size: 1.1rem; cursor: pointer; padding:0;">✕</button>
            </div>
            <div style="color: var(--text-primary);">
                <div class="mb-2 text-center">
                    <button type="button" class="btn-add w-100" style="height: 36px; font-size:0.82rem;" onclick="document.getElementById('cameraInput').click(); cerrarGastoModal();">
                        📸 Escanear Ticket / Factura
                    </button>
                    <div style="font-size:0.72rem; color:var(--text-muted); margin-top:4px;">O introduce los datos manualmente abajo</div>
                </div>
                <div class="form-group mb-2" style="margin-bottom:8px;">
                    <label style="font-size:0.72rem; margin-bottom:3px; display:block;">Descripción</label>
                    <input type="text" id="descripcion" placeholder="Ej. Restaurante, Gasolina" style="padding:7px 10px; font-size:0.85rem;">
                </div>
                <div class="form-group mb-2" style="margin-bottom:8px;">
                    <label style="font-size:0.72rem; margin-bottom:3px; display:block;">Importe ($)</label>
                    <input type="number" id="monto" step="0.01" placeholder="0.00" style="padding:7px 10px; font-size:0.85rem;">
                </div>
                <div class="form-group mb-2" style="margin-bottom:8px;">
                    <label style="font-size:0.72rem; margin-bottom:3px; display:block;">Categoría</label>
                    <select id="categoria" style="padding:7px 10px; font-size:0.85rem;">
                        <option value="Comida">Comida</option>
                        <option value="Transporte">Transporte</option>
                        <option value="Entretenimiento">Entretenimiento</option>
                        <option value="Servicios">Servicios</option>
                        <option value="Hogar">Alojamiento/Hogar</option>
                        <option value="Otros">Varios</option>
                    </select>
                </div>
                <div class="form-group mb-2" style="margin-bottom:8px;">
                    <label style="font-size:0.72rem; margin-bottom:3px; display:block;">Proyecto</label>
                    <select id="gastoProyecto" style="padding:7px 10px; font-size:0.85rem;">
                        <option value="">General (Ninguno)</option>
                    </select>
                </div>
                <div class="form-group mb-2" style="margin-bottom:8px;">
                    <label style="font-size:0.72rem; margin-bottom:3px; display:block;">Viaje</label>
                    <select id="gastoViaje" style="padding:7px 10px; font-size:0.85rem;">
                        <option value="">General (Ninguno)</option>
                    </select>
                </div>
                <div class="form-group mb-2" style="margin-bottom:4px;">
                    <label style="font-size:0.72rem; margin-bottom:3px; display:block;">Pago</label>
                    <select id="gastoMetodoPago" style="padding:7px 10px; font-size:0.85rem;">
                        <option value="Efectivo">💵 Efectivo</option>
                        <option value="Tarjeta">💳 Tarjeta</option>
                        <option value="Wallet">🪙 Wallet</option>
                    </select>
                </div>
            </div>
            <div style="border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; gap: 8px; margin-top: 14px; padding-top: 12px;">
                <button type="button" class="btn-cancel" onclick="cerrarGastoModal()" style="flex: 1; height:38px; font-size:0.82rem;">Cancelar</button>
                <button type="button" class="btn-add" onclick="agregarGasto()" style="flex: 1; height: 38px; font-size:0.82rem;">Registrar Gasto</button>
            </div>
        </div>
    </div>


    <!-- FAB QUICK REGISTER -->
    <button class="fab-plus" onclick="abrirGastoModal()">+</button>

    <!-- BOTTOM NAV MOBILE -->
    <div class="bottom-nav">
        <a class="bottom-nav-item active" id="btn-nav-resumen" onclick="switchTab('resumen')">
            <span class="nav-icon">🏠</span>
            <span>Inicio</span>
        </a>
        <a class="bottom-nav-item" id="btn-nav-wallet" onclick="switchTab('wallet')">
            <span class="nav-icon">🪙</span>
            <span>Wallet</span>
        </a>
        <a class="bottom-nav-item" id="btn-nav-proyectos" onclick="switchTab('proyectos')">
            <span class="nav-icon">📁</span>
            <span>Proyectos</span>
        </a>
        <a class="bottom-nav-item" id="btn-nav-seguridad" onclick="switchTab('seguridad')">
            <span class="nav-icon">🔐</span>
            <span>Seguridad</span>
        </a>
        <a class="bottom-nav-item" id="btn-nav-perfil" onclick="switchTab('perfil')">
            <span class="nav-icon">👤</span>
            <span>Perfil</span>
        </a>
    </div>

    <!-- TOAST -->
    <div id="toast" class="toast">Mensaje</div>

    <script>
        const userEmail = "<?= $user['correo'] ?>";
        const userName  = "<?= htmlspecialchars($user['nombre']) ?>";
        let totalBudget = <?= $presupuestoMensual ?>;
        let walletBalance = 0.00;
        let gastosData = [];
        let gastosDataFiltrados = [];
        let proyectosData = [];
        let viajesData = [];
        let anticiposData = [];
        let facturasData = [];
        let liquidacionesData = [];
        let walletTxData = [];
        let categoriasCustomData = [];
        let aprobacionesData = [];
        let estadisticasData = {};
        let proveedoresData = [];
        
        // Paginacion
        let paginaActual = 1;
        const POR_PAGINA = 10;
        
        let lineChart = null;
        let donutChart = null;
        let statsBarChart = null;
        let statsLineChart = null;
        let statsTop5Chart = null;

        document.addEventListener('DOMContentLoaded', () => {
            checkBiometricToggle();
            cargarGastos();
            cargarSidebarYErpDatos();
            cargarEstadisticasAvanzadas();
        });

        // Tab Switcher
        function switchTab(tab) {
            document.querySelectorAll('.tab-view').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.bottom-nav-item').forEach(el => el.classList.remove('active'));
            
            const view = document.getElementById(`view-${tab}`);
            if (view) view.style.display = 'flex';
            
            const menu = document.getElementById(`menu-${tab}`);
            if (menu) menu.classList.add('active');
            
            const bottomNavBtn = document.getElementById(`btn-nav-${tab}`);
            if (bottomNavBtn) bottomNavBtn.classList.add('active');
            
            const titles = {
                'resumen': 'Gestión de Gastos', 'wallet': 'Mi Billetera (Wallet)', 'anticipos': 'Anticipos', 'aprobacion': 'Aprobaciones',
                'consultas': 'Consultas', 'estadisticas': 'Estadísticas', 'facturacion': 'Facturación',
                'liquidaciones': 'Liquidaciones', 'Limitess': 'Limitess', 'proyectos': 'Proyectos',
                'viajes': 'Viajes', 'seguridad': 'Seguridad', 'perfil': 'Mi Perfil'
            };
            document.getElementById('header-title-text').innerText = titles[tab] || 'Gestor Gastos';
            
            if (tab === 'estadisticas') setTimeout(renderEstadisticas, 100);
            if (tab === 'perfil') actualizarPerfilStats();
            if (tab === 'Limitess') cargarProveedores();
            
            const sidebar = document.getElementById('sidebar');
            if(window.innerWidth <= 768) sidebar.classList.remove('open');
        }

        // Camera Logic
        function handleTicketPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('ticketPreview').src = e.target.result;
                    document.getElementById('ticketPreview').style.display = 'block';
                    document.getElementById('ticketModal').style.display = 'flex';
                    
                    document.getElementById('ocrLoader').style.display = 'block';
                    document.getElementById('ticketFormInputs').style.display = 'none';
                    document.getElementById('btnGuardarTicket').style.display = 'none';
                    
                    // Simulate OCR Delay
                    setTimeout(() => {
                        document.getElementById('ocrLoader').style.display = 'none';
                        document.getElementById('ticketFormInputs').style.display = 'block';
                        document.getElementById('btnGuardarTicket').style.display = 'block';
                        
                        document.getElementById('ticketDesc').value = "Ticket Escaneado";
                        document.getElementById('ticketMonto').value = (Math.random() * 50 + 10).toFixed(2);
                        showToast("✅ Importe detectado automáticamente");
                    }, 1800);
                }
                reader.readAsDataURL(file);
            }
        }

        function cerrarModalTicket() {
            document.getElementById('ticketModal').style.display = 'none';
            document.getElementById('cameraInput').value = "";
        }

        async function guardarTicketFromModal() {
            const desc = document.getElementById('ticketDesc').value.trim();
            const monto = parseFloat(document.getElementById('ticketMonto').value);
            const cat = document.getElementById('ticketCategoria').value;
            if (!desc || isNaN(monto)) return showToast("Faltan datos del ticket");

            if (!verificarLimitePresupuesto(cat, monto)) return;

            const reqTime = performance.now();
            const response = await fetch("api/add_gasto.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({descripcion: desc, monto: monto, categoria: cat, metodo_pago: 'Efectivo', estado: 'Pendiente'})
            });
            const res = await response.json();
            if (res.success) {
                cerrarModalTicket();
                cargarGastos();
                cargarSidebarYErpDatos();
                switchTab('resumen');
                showToast("Ticket registrado en revisión");
                actualizarDevLogs(res, performance.now() - reqTime, "POST /api/add_gasto.php (Ticket)");
            } else {
                showToast(res.message || "Error al guardar el ticket");
            }
        }

        // Dev Mode Toggle
        function toggleDevMode(checkbox) {
            if (checkbox.checked) {
                document.body.classList.add('dev-mode');
                showToast("Modo Desarrollador Activado");
            } else {
                document.body.classList.remove('dev-mode');
                showToast("Modo Normal Activado");
            }
            actualizarDevLogs();
            actualizarBiometriaLog();
        }

        function showToast(msg) {
            const t = document.getElementById('toast');
            t.innerText = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        // API Fetch principal
        async function cargarGastos() {
            const startTime = performance.now();
            try {
                const response = await fetch("api/get_gastos.php");
                const res = await response.json();
                const endTime = performance.now();
                
                if (res.success) {
                    gastosData = res.gastos;
                    gastosDataFiltrados = gastosData;
                    actualizarUI(res.total_gastado);
                    actualizarDevLogs(res, endTime - startTime);
                }
            } catch (e) {
                console.error("Error", e);
            }
        }

        async function cargarEstadisticasAvanzadas() {
            try {
                const res = await fetch("api/get_estadisticas_avanzadas.php");
                const json = await res.json();
                if (json.success !== false) {
                    estadisticasData = json;
                    actualizarKPIs(json);
                }
            } catch(e) { console.error('KPIs error', e); }
        }

        function actualizarKPIs(d) {
            // % presupuesto
            const pct = d.pct_presupuesto || 0;
            const pctEl = document.getElementById('kpi-pct-presupuesto');
            const pctBar = document.getElementById('kpi-pct-bar');
            if (pctEl) {
                pctEl.innerText = pct + '%';
                const color = pct >= 100 ? '#E53935' : pct >= 80 ? '#FF8F00' : '#66BB6A';
                pctEl.style.color = color;
                if (pctBar) { pctBar.style.width = Math.min(pct,100)+'%'; pctBar.style.background = color; }
            }
            // diario
            const dEl = document.getElementById('kpi-diario');
            if (dEl) dEl.innerText = '$' + (d.gasto_diario_promedio||0).toFixed(2);
            // dias restantes
            const diasEl = document.getElementById('kpi-dias');
            if (diasEl) diasEl.innerText = d.dias_restantes + ' días';
            // proyeccion
            const proyEl = document.getElementById('kpi-proyeccion');
            if (proyEl) proyEl.innerText = '$' + (d.proyeccion_fin_mes||0).toFixed(2);
            
            // Alerta presupuesto
            const alertBox = document.getElementById('budget-alert-box');
            const alertMsg = document.getElementById('budget-alert-msg');
            if (alertBox && pct >= 90) {
                alertBox.style.display = 'flex';
                const exceso = (d.gasto_mes_actual - d.presupuesto).toFixed(2);
                alertMsg.innerText = pct >= 100
                    ? `Has excedido tu presupuesto por $${exceso}. Gasto actual: $${(d.gasto_mes_actual||0).toFixed(2)}`
                    : `Estás al ${pct}% de tu presupuesto mensual de $${(d.presupuesto||0).toFixed(2)}`;
            } else if (alertBox) {
                alertBox.style.display = 'none';
            }
        }

        // Cargar datos ERP
        async function cargarSidebarYErpDatos() {
            try {
                const res = await fetch("api/get_sidebar_data.php");
                const json = await res.json();
                if (json.success) {
                    walletBalance = parseFloat(json.wallet_balance);
                    proyectosData = json.proyectos;
                    viajesData = json.viajes;
                    anticiposData = json.anticipos;
                    facturasData = json.facturas;
                    liquidacionesData = json.liquidaciones;
                    walletTxData = json.wallet_transactions;
                    categoriasCustomData = json.categorias_custom;
                    aprobacionesData = json.aprobaciones_pendientes;

                    // Actualizar balances
                    const dispSaldo = document.getElementById('display-saldo');
                    if (dispSaldo) dispSaldo.innerText = '$' + walletBalance.toFixed(2);
                    const walletBalCard = document.getElementById('wallet-balance-card-val');
                    if (walletBalCard) walletBalCard.innerText = '$' + walletBalance.toFixed(2);

                    // Actualizar proyectos y viajes en la rejilla Fintech
                    const extrasEl = document.getElementById('grid-extras-val');
                    if (extrasEl) {
                        extrasEl.innerHTML = `${proyectosData.length} Proyectos<br>${viajesData.length} Viajes`;
                    }

                    // Poblar desplegables
                    poblarSelects();

                    // Renderizar cada vista
                    renderWallet();
                    renderAnticipos();
                    renderAprobaciones();
                    renderProyectos();
                    renderViajes();
                    renderFacturas();
                    renderLiquidaciones();
                    renderLimitess();
                }
            } catch (e) {
                console.error("Error cargando ERP datos", e);
            }
        }

        function poblarSelects() {
            // Selectores del formulario de gastos principal
            const pSel = document.getElementById('gastoProyecto');
            const vSel = document.getElementById('gastoViaje');
            
            // Selectores de consultas
            const cpSel = document.getElementById('consultasProyecto');
            const cvSel = document.getElementById('consultasViaje');

            // Selector de anticipos
            const avSel = document.getElementById('anticipoViaje');
            
            // Selector de facturas
            const fgSel = document.getElementById('facturaGastoId');

            // Selector de liquidaciones
            const lvSel = document.getElementById('liquidacionViaje');

            // Resetear selects si existen en el DOM
            if (pSel) pSel.innerHTML = '<option value="">General (Ninguno)</option>';
            if (vSel) vSel.innerHTML = '<option value="">General (Ninguno)</option>';
            if (cpSel) cpSel.innerHTML = '<option value="">Todos</option>';
            if (cvSel) cvSel.innerHTML = '<option value="">Todos</option>';
            if (avSel) avSel.innerHTML = '<option value="">General (Ninguno)</option>';
            if (fgSel) fgSel.innerHTML = '<option value="">General (No vincular)</option>';
            if (lvSel) lvSel.innerHTML = '<option value="">Selecciona un viaje...</option>';

            proyectosData.forEach(p => {
                if (pSel) pSel.innerHTML += `<option value="${p.id_proyecto}">${p.codigo} - ${p.nombre}</option>`;
                if (cpSel) cpSel.innerHTML += `<option value="${p.id_proyecto}">${p.nombre}</option>`;
            });

            viajesData.forEach(v => {
                if (vSel) vSel.innerHTML += `<option value="${v.id_viaje}">${v.destino}</option>`;
                if (cvSel) cvSel.innerHTML += `<option value="${v.id_viaje}">${v.destino}</option>`;
                if (avSel) avSel.innerHTML += `<option value="${v.id_viaje}">${v.destino}</option>`;
                if (lvSel) lvSel.innerHTML += `<option value="${v.id_viaje}">${v.destino} (${v.estado})</option>`;
            });

            if (fgSel) {
                gastosData.forEach(g => {
                    // Solo vincular gastos sin XML o factura adjunta
                    if (!g.xml_invoice) {
                        fgSel.innerHTML += `<option value="${g.id_gasto}">${g.descripcion} - $${g.monto.toFixed(2)}</option>`;
                    }
                });
            }
        }

        function renderWallet() {
            const tbody = document.getElementById('wallet-tx-tbody');
            if (!tbody) return;
            if (walletTxData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="3" style="text-align:center; color:var(--text-muted);">No hay transacciones registradas</td></tr>`;
                return;
            }
            tbody.innerHTML = '';
            walletTxData.forEach(tx => {
                const color = tx.tipo === 'deposito' ? '#2E7D32' : '#C62828';
                const sign = tx.tipo === 'deposito' ? '+' : '-';
                tbody.innerHTML += `
                    <tr>
                        <td>${tx.fecha}</td>
                        <td>${tx.descripcion}</td>
                        <td style="text-align:right; font-weight:600; color:${color};">${sign}$${tx.monto.toFixed(2)}</td>
                    </tr>
                `;
            });
        }

        async function walletAction(action) {
            const inputId = action === 'deposit' ? 'walletRecargaMonto' : 'walletRetiroMonto';
            const inputEl = document.getElementById(inputId);
            if (!inputEl) return;
            const val = parseFloat(inputEl.value);
            if (isNaN(val) || val <= 0) return showToast("Monto inválido");

            const res = await fetch("api/wallet_action.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({action: action, monto: val})
            });
            const json = await res.json();
            if (json.success) {
                showToast(json.message);
                inputEl.value = '';
                cargarSidebarYErpDatos();
                cargarGastos();
            } else {
                showToast(json.message);
            }
        }

        function renderAnticipos() {
            const tbody = document.getElementById('anticipos-tbody');
            if (!tbody) return;
            if (anticiposData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No hay anticipos registrados</td></tr>`;
                return;
            }
            tbody.innerHTML = '';
            anticiposData.forEach(a => {
                let badgeClass = 'badge-pendiente';
                if (a.estado === 'Aprobado') badgeClass = 'badge-aprobado';
                else if (a.estado === 'Rechazado') badgeClass = 'badge-rechazado';
                
                tbody.innerHTML += `
                    <tr>
                        <td>${a.fecha_creacion.split(' ')[0]}</td>
                        <td><strong>${a.motivo}</strong><br><span style="font-size:0.75rem; color:var(--text-muted);">Viaje: ${a.destino}</span></td>
                        <td><span class="badge-estado ${badgeClass}">${a.estado}</span></td>
                        <td style="text-align:right; font-weight:600;">$${a.monto.toFixed(2)}</td>
                    </tr>
                `;
            });
        }

        async function solicitarAnticipo() {
            const mEl = document.getElementById('anticipoMonto');
            const motEl = document.getElementById('anticipoMotivo');
            const vEl = document.getElementById('anticipoViaje');
            if (!mEl || !motEl || !vEl) return;
            const monto = parseFloat(mEl.value);
            const motivo = motEl.value.trim();
            const id_viaje = vEl.value;

            if (isNaN(monto) || monto <= 0 || !motivo) {
                return showToast("Ingresa monto y motivo válido");
            }

            const res = await fetch("api/add_anticipo.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({monto: monto, motivo: motivo, id_viaje: id_viaje})
            });
            const json = await res.json();
            if (json.success) {
                showToast("Solicitud de anticipo registrada. Pendiente de aprobación.");
                mEl.value = '';
                motEl.value = '';
                vEl.value = '';
                cargarSidebarYErpDatos();
            } else {
                showToast(json.message);
            }
        }

        function renderAprobaciones() {
            const tbody = document.getElementById('aprobaciones-tbody');
            const badge = document.getElementById('sidebar-aprobaciones-badge');
            if (!tbody) return;
            
            if (aprobacionesData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--text-muted);">No hay solicitudes pendientes de aprobación</td></tr>`;
                if (badge) badge.style.display = 'none';
                return;
            }
            
            if (badge) {
                badge.innerText = aprobacionesData.length;
                badge.style.display = 'inline-block';
            }
            
            tbody.innerHTML = '';
            aprobacionesData.forEach(item => {
                const tipoTxt = item.tipo === 'gasto' ? '🧾 Gasto' : '💸 Anticipo';
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${tipoTxt}</strong></td>
                        <td>${item.titulo}</td>
                        <td>${item.fecha}</td>
                        <td style="text-align:right; font-weight:600;">$${item.monto.toFixed(2)}</td>
                        <td style="text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <button class="btn-approve" onclick="procesarAprobacion(${item.id}, '${item.tipo}', 'aprobar')">✓ Aprobar</button>
                                <button class="btn-reject" onclick="procesarAprobacion(${item.id}, '${item.tipo}', 'rechazar')">✕ Rechazar</button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        async function procesarAprobacion(id, tipo, accion) {
            const res = await fetch("api/approve_item.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({id: id, tipo: tipo, accion: accion})
            });
            const json = await res.json();
            if (json.success) {
                showToast(json.message);
                cargarSidebarYErpDatos();
                cargarGastos();
            } else {
                showToast(json.message);
            }
        }

        function renderProyectos() {
            const tbody = document.getElementById('proyectos-tbody');
            if (!tbody) return;
            if (proyectosData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="3" style="text-align:center;">No hay proyectos activos</td></tr>`;
                return;
            }
            tbody.innerHTML = '';
            proyectosData.forEach(p => {
                const pct = p.presupuesto > 0 ? Math.min((p.gastado / p.presupuesto) * 100, 100) : 0;
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${p.codigo}</strong><br><span style="font-size:0.8rem; color:var(--text-muted);">${p.nombre}</span></td>
                        <td>Límite: $${p.presupuesto.toFixed(2)}<br><span style="color:#C62828;">Gastado: $${p.gastado.toFixed(2)}</span></td>
                        <td>
                            <div style="font-size:0.75rem; text-align:right; font-weight:600;">${pct.toFixed(0)}%</div>
                            <div class="progress-container"><div class="progress-bar" style="width:${pct}%;"></div></div>
                        </td>
                    </tr>
                `;
            });
        }

        async function crearProyecto() {
            const codEl = document.getElementById('proyectoCodigo');
            const nomEl = document.getElementById('proyectoNombre');
            const preEl = document.getElementById('proyectoPresupuesto');
            if (!codEl || !nomEl || !preEl) return;
            const cod = codEl.value.trim();
            const nom = nomEl.value.trim();
            const pre = parseFloat(preEl.value);
            const fi  = document.getElementById('proyectoFechaInicio') ? document.getElementById('proyectoFechaInicio').value : '';
            const ff  = document.getElementById('proyectoFechaFin') ? document.getElementById('proyectoFechaFin').value : '';

            if (!cod || !nom || isNaN(pre) || pre <= 0) {
                return showToast("Ingresa datos de proyecto válidos");
            }

            const res = await fetch("api/add_proyecto.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({codigo: cod, nombre: nom, presupuesto: pre, fecha_inicio: fi, fecha_fin: ff})
            });
            const json = await res.json();
            if (json.success) {
                showToast("Proyecto registrado");
                codEl.value = '';
                nomEl.value = '';
                preEl.value = '';
                if (document.getElementById('proyectoFechaInicio')) document.getElementById('proyectoFechaInicio').value = '';
                if (document.getElementById('proyectoFechaFin')) document.getElementById('proyectoFechaFin').value = '';
                cargarSidebarYErpDatos();
            }
        }

        function renderViajes() {
            const tbody = document.getElementById('viajes-tbody');
            if (!tbody) return;
            if (viajesData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No hay viajes registrados</td></tr>`;
                return;
            }
            tbody.innerHTML = '';
            viajesData.forEach(v => {
                let badgeClass = 'badge-pendiente';
                if (v.estado === 'En curso') badgeClass = 'badge-aprobado';
                else if (v.estado === 'Terminado') badgeClass = 'badge-rechazado';
                
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${v.destino}</strong></td>
                        <td style="font-size:0.85rem;">Inicio: ${v.fecha_inicio}<br>Fin: ${v.fecha_fin}</td>
                        <td style="font-weight:600;">$${v.presupuesto.toFixed(2)}</td>
                        <td><span class="badge-estado ${badgeClass}">${v.estado}</span></td>
                    </tr>
                `;
            });
        }

        async function crearViaje() {
            const destEl = document.getElementById('viajeDestino');
            const fiEl = document.getElementById('viajeFechaInicio');
            const ffEl = document.getElementById('viajeFechaFin');
            const preEl = document.getElementById('viajePresupuesto');
            if (!destEl || !fiEl || !ffEl || !preEl) return;
            const dest = destEl.value.trim();
            const finicio = fiEl.value;
            const ffin = ffEl.value;
            const pre = parseFloat(preEl.value);

            if (!dest || !finicio || !ffin || isNaN(pre) || pre <= 0) {
                return showToast("Todos los campos son obligatorios");
            }

            const res = await fetch("api/add_viaje.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({destino: dest, fecha_inicio: finicio, fecha_fin: ffin, presupuesto: pre, estado: 'Planificado'})
            });
            const json = await res.json();
            if (json.success) {
                showToast("Viaje creado correctamente");
                destEl.value = '';
                fiEl.value = '';
                ffEl.value = '';
                preEl.value = '';
                cargarSidebarYErpDatos();
            }
        }

        function renderFacturas() {
            const tbody = document.getElementById('facturas-tbody');
            if (!tbody) return;
            if (facturasData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No hay facturas registradas</td></tr>`;
                return;
            }
            tbody.innerHTML = '';
            facturasData.forEach(f => {
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${f.folio}</strong></td>
                        <td style="font-size:0.85rem;">Emisor: ${f.emisor}<br>Receptor: ${f.receptor}</td>
                        <td style="font-size:0.85rem; color:var(--text-muted);">${f.gasto_descripcion}</td>
                        <td style="text-align:right; font-weight:600;">$${f.monto.toFixed(2)}<br><span style="font-size:0.75rem; color:#4CAF50; font-weight:normal;">IVA: $${f.iva.toFixed(2)}</span></td>
                    </tr>
                `;
            });
        }

        function autogenerarXML() {
            const randFol = Math.floor(Math.random() * 900000) + 100000;
            const randTot = (Math.random() * 1500 + 100).toFixed(2);
            const sub = (randTot / 1.16).toFixed(2);
            const iva = (randTot - sub).toFixed(2);
            
            const xml = `<?xml version="1.0" encoding="utf-8"?>
<cfdi:Comprobante Version="4.0" Serie="CFDI" Folio="${randFol}" Fecha="${new Date().toISOString().slice(0,19)}" SubTotal="${sub}" Total="${randTot}" Moneda="MXN">
  <cfdi:Emisor Rfc="COFE901010CC1" Nombre="CAFETERIA COFFEE AND CREAM S.A. DE C.V." RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="PEVR901010XYZ" Nombre="JUAN PEREZ" UsoCFDI="G03"/>
  <cfdi:Impuestos TotalImpuestosTrasladados="${iva}">
    <cfdi:Traslados>
      <cfdi:Traslado Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="${iva}"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>
</cfdi:Comprobante>`;

            document.getElementById('xmlContent').value = xml;
            showToast("XML CFDI 4.0 Autogenerado");
        }

        function procesarXML() {
            const xml = document.getElementById('xmlContent').value.trim();
            if (!xml) return showToast("Inserta contenido XML");

            const folioM = xml.match(/Folio="([^"]+)"/);
            const totalM = xml.match(/Total="([^"]+)"/);
            const subM = xml.match(/SubTotal="([^"]+)"/);
            const emisorNombreM = xml.match(/Emisor[^>]+Nombre="([^"]+)"/);
            const receptorNombreM = xml.match(/Receptor[^>]+Nombre="([^"]+)"/);

            if (!totalM) return showToast("CFDI Inválido: Falta el campo Total");

            const folio = folioM ? folioM[1] : "F-" + Math.floor(Math.random()*1000);
            const total = parseFloat(totalM[1]);
            const sub = subM ? parseFloat(subM[1]) : (total / 1.16);
            const iva = (total - sub);
            
            let emisor = emisorNombreM ? emisorNombreM[1] : "Proveedor CFDI";
            let receptor = receptorNombreM ? receptorNombreM[1] : userName;

            document.getElementById('facturaFolio').value = folio;
            document.getElementById('facturaFecha').value = new Date().toISOString().split('T')[0];
            document.getElementById('facturaEmisor').value = emisor;
            document.getElementById('facturaReceptor').value = receptor;
            document.getElementById('facturaMonto').value = total.toFixed(2);
            document.getElementById('facturaIva').value = iva.toFixed(2);

            showToast("✅ Campos XML extraídos");
        }

        async function guardarFactura() {
            const folio = document.getElementById('facturaFolio').value;
            const fecha = document.getElementById('facturaFecha').value;
            const emisor = document.getElementById('facturaEmisor').value;
            const receptor = document.getElementById('facturaReceptor').value;
            const monto = parseFloat(document.getElementById('facturaMonto').value);
            const iva = parseFloat(document.getElementById('facturaIva').value);
            const id_gasto = document.getElementById('facturaGastoId').value;

            if (!folio || !emisor || isNaN(monto) || monto <= 0) {
                return showToast("Procesa primero un XML válido");
            }

            const res = await fetch("api/add_factura.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({folio: folio, fecha_emision: fecha, emisor: emisor, receptor: receptor, monto: monto, iva: iva, id_gasto: id_gasto})
            });
            const json = await res.json();
            if (json.success) {
                showToast("Factura adjuntada y guardada");
                document.getElementById('xmlContent').value = '';
                document.getElementById('facturaFolio').value = '';
                document.getElementById('facturaEmisor').value = '';
                document.getElementById('facturaReceptor').value = '';
                document.getElementById('facturaMonto').value = '';
                document.getElementById('facturaIva').value = '';
                cargarSidebarYErpDatos();
                cargarGastos();
            }
        }

        async function calcularLiquidacionViaje() {
            const id_viaje = document.getElementById('liquidacionViaje').value;
            const container = document.getElementById('liquidacionCalculos');
            
            if (!id_viaje) {
                container.style.display = 'none';
                return;
            }

            // Calcular localmente a partir de gastosData y anticiposData
            let totalGasto = 0;
            gastosData.forEach(g => {
                if (g.id_viaje == id_viaje && g.estado === 'Aprobado') totalGasto += g.monto;
            });

            let totalAnticipo = 0;
            anticiposData.forEach(a => {
                if (a.id_viaje == id_viaje && a.estado === 'Aprobado') totalAnticipo += a.monto;
            });

            const neto = totalGasto - totalAnticipo;

            document.getElementById('liqTotalGastado').innerText = '$' + totalGasto.toFixed(2);
            document.getElementById('liqTotalAnticipos').innerText = '$' + totalAnticipo.toFixed(2);
            
            const netoEl = document.getElementById('liqNeto');
            netoEl.innerText = (neto >= 0 ? '+' : '') + '$' + neto.toFixed(2);
            netoEl.style.color = neto >= 0 ? '#2E7D32' : '#C62828';

            const msgEl = document.getElementById('liqResultadoMsg');
            if (neto > 0) {
                msgEl.innerText = "Empresa reembolsa al empleado: $" + neto.toFixed(2);
                msgEl.style.color = '#2E7D32';
            } else if (neto < 0) {
                msgEl.innerText = "Empleado debe retornar a la empresa: $" + Math.abs(neto).toFixed(2);
                msgEl.style.color = '#C62828';
            } else {
                msgEl.innerText = "Saldos parejos. No se requieren transferencias.";
                msgEl.style.color = 'var(--text-main)';
            }

            container.style.display = 'block';
        }

        async function crearLiquidacion() {
            const id_viaje = document.getElementById('liquidacionViaje').value;
            const nom = document.getElementById('liquidacionNombre').value.trim();

            if (!id_viaje || !nom) return showToast("Selecciona un viaje y escribe un concepto");

            const res = await fetch("api/add_liquidacion.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({id_viaje: id_viaje, nombre: nom})
            });
            const json = await res.json();
            if (json.success) {
                showToast("Liquidación procesada correctamente");
                document.getElementById('liquidacionNombre').value = '';
                document.getElementById('liquidacionViaje').value = '';
                document.getElementById('liquidacionCalculos').style.display = 'none';
                cargarSidebarYErpDatos();
            }
        }

        function renderLiquidaciones() {
            const tbody = document.getElementById('liquidaciones-tbody');
            if (!tbody) return;
            if (liquidacionesData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No hay liquidaciones cerradas</td></tr>`;
                return;
            }
            tbody.innerHTML = '';
            liquidacionesData.forEach(l => {
                const color = l.resultado >= 0 ? '#2E7D32' : '#C62828';
                const resText = l.resultado >= 0 ? "A favor: $" + l.resultado.toFixed(2) : "Retornar: $" + Math.abs(l.resultado).toFixed(2);
                tbody.innerHTML += `
                    <tr>
                        <td>${l.fecha_creacion.split(' ')[0]}</td>
                        <td><strong>${l.nombre}</strong><br><span style="font-size:0.8rem; color:var(--text-muted);">Viaje: ${l.destino}</span></td>
                        <td style="font-size:0.85rem;">Gastado: $${l.monto_total.toFixed(2)}<br>Anticipos: $${l.monto_anticipos.toFixed(2)}</td>
                        <td style="text-align:right; font-weight:600; color:${color};">${resText}</td>
                    </tr>
                `;
            });
        }

        function renderLimitess() {
            const tbody = document.getElementById('Limitess-tbody');
            if (!tbody) return;
            tbody.innerHTML = '';
            
            const categories = ['Comida', 'Transporte', 'Entretenimiento', 'Servicios', 'Hogar', 'Otros'];
            const limits = {};
            
            categoriasCustomData.forEach(c => {
                limits[c.nombre] = c.limite_mensual;
            });

            // Calcular gasto real por categoria (mes actual)
            const catSpent = {};
            gastosData.forEach(g => {
                const thisMonth = new Date().toISOString().slice(0,7);
                if (g.fecha && g.fecha.slice(0,7) === thisMonth && g.estado !== 'Rechazado') {
                    catSpent[g.categoria] = (catSpent[g.categoria] || 0) + g.monto;
                }
            });

            categories.forEach(cat => {
                const limit = limits[cat] || 0.00;
                const spent = catSpent[cat] || 0;
                const limitTxt = limit > 0 ? '$' + limit.toFixed(2) : 'Sin límite';
                const pct = limit > 0 ? Math.min((spent / limit) * 100, 100) : 0;
                const pctColor = pct >= 100 ? '#C62828' : pct >= 80 ? '#E65100' : 'var(--primary)';
                
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${cat}</strong></td>
                        <td id="limit-val-${cat}">${limitTxt}</td>
                        <td>
                            ${limit > 0 ? `
                                <div style="font-size:0.75rem; font-weight:600; color:${pctColor};">${pct.toFixed(0)}% ($${spent.toFixed(2)})</div>
                                <div class="progress-container" style="margin-top:4px;"><div class="progress-bar" style="width:${pct}%; background:${pctColor};"></div></div>
                            ` : `<span style="color:var(--text-muted); font-size:0.8rem;">Sin límite fijado</span>`}
                        </td>
                        <td style="text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <input type="number" id="limit-input-${cat}" step="10" placeholder="${limit > 0 ? limit : '0.00'}" style="width:80px; padding:4px 8px; font-size:0.85rem; border-radius:6px; border:1px solid var(--border);">
                                <button class="btn-add" onclick="actualizarLimiteLimitess('${cat}')" style="height:28px; font-size:0.8rem; padding:0 10px;">Fijar</button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        async function cargarProveedores() {
            try {
                const res = await fetch('api/proveedor.php', {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({action: 'list'})
                });
                const json = await res.json();
                if (json.success) {
                    proveedoresData = json.proveedores;
                    renderProveedores();
                }
            } catch(e) { console.error('Error cargando proveedores', e); }
        }

        function renderProveedores() {
            const el = document.getElementById('proveedores-lista');
            if (!el) return;
            if (proveedoresData.length === 0) {
                el.innerHTML = '<p style="text-align:center; color:var(--text-muted); font-size:0.9rem;">No hay proveedores registrados</p>';
                return;
            }
            el.innerHTML = '';
            proveedoresData.forEach(p => {
                el.innerHTML += `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background: rgba(255,255,255,0.03); border-radius:8px; margin-bottom:8px; border: 1px solid rgba(255,255,255,0.05);">
                        <div>
                            <strong style="font-size:0.9rem;">${p.nombre}</strong>
                            <div style="font-size:0.75rem; color:var(--text-muted);">${p.rfc || ''} ${p.categoria ? '&bull; ' + p.categoria : ''} ${p.contacto ? '&bull; ' + p.contacto : ''}</div>
                        </div>
                        <button class="btn-icon" onclick="eliminarProveedor(${p.id_proveedor})" title="Eliminar">🗑</button>
                    </div>
                `;
            });
        }

        async function agregarProveedor() {
            const nombre = document.getElementById('provNombre').value.trim();
            const rfc = document.getElementById('provRfc').value.trim();
            const cat = document.getElementById('provCategoria').value;
            const contacto = document.getElementById('provContacto').value.trim();
            if (!nombre) return showToast('El nombre del proveedor es requerido');
            const res = await fetch('api/proveedor.php', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({action:'add', nombre, rfc, categoria:cat, contacto})
            });
            const json = await res.json();
            if (json.success) {
                showToast('✅ Proveedor agregado');
                document.getElementById('provNombre').value = '';
                document.getElementById('provRfc').value = '';
                document.getElementById('provContacto').value = '';
                cargarProveedores();
            } else { showToast(json.message); }
        }

        async function eliminarProveedor(id) {
            if (!confirm('¿Eliminar este proveedor?')) return;
            const res = await fetch('api/proveedor.php', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({action:'delete', id})
            });
            const json = await res.json();
            if (json.success) { showToast('Proveedor eliminado'); cargarProveedores(); }
        }

        async function actualizarLimiteLimitess(cat) {
            const val = parseFloat(document.getElementById(`limit-input-${cat}`).value);
            if (isNaN(val) || val < 0) return showToast("Límite no válido");

            const res = await fetch("api/update_categoria_limite.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({nombre: cat, limite_mensual: val})
            });
            const json = await res.json();
            if (json.success) {
                showToast("Límite de categoría actualizado");
                document.getElementById(`limit-input-${cat}`).value = '';
                cargarSidebarYErpDatos();
            }
        }

        function renderEstadisticas() {
            const ctxBar = document.getElementById('statsBarChart')?.getContext('2d');
            if (ctxBar) {
                const limits = {};
                categoriasCustomData.forEach(c => { limits[c.nombre] = c.limite_mensual; });
                
                const catSpent = {};
                gastosData.forEach(g => {
                    if (g.estado === 'Aprobado') {
                        catSpent[g.categoria] = (catSpent[g.categoria] || 0) + g.monto;
                    }
                });

                const labels = Object.keys(catSpent);
                const spentData = Object.values(catSpent);
                const budgetData = labels.map(l => limits[l] || 0);

                if (statsBarChart) statsBarChart.destroy();
                statsBarChart = new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'Consumo Real ($)', data: spentData, backgroundColor: '#D4AF37', borderRadius: 6 },
                            { label: 'Límite Máximo ($)', data: budgetData, backgroundColor: '#F5E6D3', borderRadius: 6 }
                        ]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            legend: { 
                                position: 'top',
                                labels: {
                                    color: '#E8D5C0',
                                    font: { family: 'Outfit' }
                                }
                            } 
                        },
                        scales: {
                            y: {
                                grid: { color: 'rgba(255,255,255,0.08)' },
                                ticks: { color: '#E8D5C0', font: { family: 'Outfit' } }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#E8D5C0', font: { family: 'Outfit' } }
                            }
                        }
                    }
                });
            }

            const ctxLine = document.getElementById('statsLineChart')?.getContext('2d');
            if (ctxLine) {
                const reversedTxs = [...walletTxData].reverse();
                let runningBalance = 0;
                const historyData = [];
                const historyLabels = [];
                
                reversedTxs.forEach(tx => {
                    if (tx.tipo === 'deposito') runningBalance += tx.monto;
                    else runningBalance -= tx.monto;
                    historyData.push(runningBalance);
                    historyLabels.push(tx.fecha.split(' ')[0].substring(5));
                });
                
                if (historyData.length === 0) {
                    historyData.push(walletBalance);
                    historyLabels.push('Hoy');
                }

                if (statsLineChart) statsLineChart.destroy();
                statsLineChart = new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: historyLabels,
                        datasets: [{
                            label: 'Saldo Billetera ($)',
                            data: historyData,
                            borderColor: '#D4AF37',
                            backgroundColor: 'rgba(212, 175, 55, 0.1)',
                            fill: true, tension: 0.3
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#E8D5C0',
                                    font: { family: 'Outfit' }
                                }
                            }
                        },
                        scales: {
                            y: {
                                grid: { color: 'rgba(255,255,255,0.08)' },
                                ticks: { color: '#E8D5C0', font: { family: 'Outfit' } }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#E8D5C0', font: { family: 'Outfit' } }
                            }
                        }
                    }
                });
            }
        }

        // Consultas
        function filtrarConsultas() {
            const cat = document.getElementById('consultasCategoria').value;
            const proy = document.getElementById('consultasProyecto').value;
            const viaje = document.getElementById('consultasViaje').value;
            const pago = document.getElementById('consultasMetodoPago').value;
            const finicio = document.getElementById('consultasFechaInicio').value;
            const ffin = document.getElementById('consultasFechaFin').value;

            let filtrados = gastosData;

            if (cat) filtrados = filtrados.filter(g => g.categoria === cat);
            if (proy) filtrados = filtrados.filter(g => g.id_proyecto == proy);
            if (viaje) filtrados = filtrados.filter(g => g.id_viaje == viaje);
            if (pago) filtrados = filtrados.filter(g => g.metodo_pago === pago);
            if (finicio) filtrados = filtrados.filter(g => g.fecha.split(' ')[0] >= finicio);
            if (ffin) filtrados = filtrados.filter(g => g.fecha.split(' ')[0] <= ffin);

            const tbody = document.getElementById('consultas-tbody');
            if (filtrados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No hay resultados con estos filtros</td></tr>`;
                return;
            }

            tbody.innerHTML = '';
            filtrados.forEach(g => {
                let badgeClass = 'badge-pendiente';
                if (g.estado === 'Aprobado') badgeClass = 'badge-aprobado';
                else if (g.estado === 'Rechazado') badgeClass = 'badge-rechazado';

                tbody.innerHTML += `
                    <tr>
                        <td>${g.fecha.split(' ')[0]}</td>
                        <td>${g.descripcion}</td>
                        <td>${g.categoria}</td>
                        <td>Proy: ${g.proyecto_nombre}<br>Viaje: ${g.viaje_destino}</td>
                        <td>${g.metodo_pago}</td>
                        <td><span class="badge-estado ${badgeClass}">${g.estado}</span></td>
                        <td style="text-align:right; font-weight:600;">$${g.monto.toFixed(2)}</td>
                    </tr>
                `;
            });
            showToast("Búsqueda finalizada");
        }

        function limpiarFiltrosConsultas() {
            document.getElementById('consultasCategoria').value = '';
            document.getElementById('consultasProyecto').value = '';
            document.getElementById('consultasViaje').value = '';
            document.getElementById('consultasMetodoPago').value = '';
            document.getElementById('consultasFechaInicio').value = '';
            document.getElementById('consultasFechaFin').value = '';
            document.getElementById('consultas-tbody').innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--text-muted);">Realiza una consulta para ver resultados</td></tr>`;
        }

        function exportarConsultas(format) {
            const cat = document.getElementById('consultasCategoria').value;
            const proy = document.getElementById('consultasProyecto').value;
            const viaje = document.getElementById('consultasViaje').value;
            const pago = document.getElementById('consultasMetodoPago').value;
            const finicio = document.getElementById('consultasFechaInicio').value;
            const ffin = document.getElementById('consultasFechaFin').value;

            let filtrados = gastosData;

            if (cat) filtrados = filtrados.filter(g => g.categoria === cat);
            if (proy) filtrados = filtrados.filter(g => g.id_proyecto == proy);
            if (viaje) filtrados = filtrados.filter(g => g.id_viaje == viaje);
            if (pago) filtrados = filtrados.filter(g => g.metodo_pago === pago);
            if (finicio) filtrados = filtrados.filter(g => g.fecha.split(' ')[0] >= finicio);
            if (ffin) filtrados = filtrados.filter(g => g.fecha.split(' ')[0] <= ffin);

            if (filtrados.length === 0) return showToast("No hay datos para exportar");

            if (format === 'json') {
                const blob = new Blob([JSON.stringify(filtrados, null, 2)], {type : 'application/json'});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `consulta_gastos_${new Date().toISOString().split('T')[0]}.json`;
                a.click();
            } else if (format === 'csv') {
                let csv = 'Fecha,Descripcion,Monto,Categoria,Estado,MetodoPago,Proyecto,Viaje\n';
                filtrados.forEach(g => {
                    csv += `"${g.fecha}","${g.descripcion}",${g.monto},"${g.categoria}","${g.estado}","${g.metodo_pago}","${g.proyecto_nombre}","${g.viaje_destino}"\n`;
                });
                const blob = new Blob([csv], {type : 'text/csv;charset=utf-8;'});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `consulta_gastos_${new Date().toISOString().split('T')[0]}.csv`;
                a.click();
            }
        }

        // Actualizar UI del Resumen (Inicio)
        function actualizarUI(totalGastado) {
            const spentFloat = parseFloat(totalGastado) || 0;
            
            // Update display values in home layout
            const displaySaldo = document.getElementById('display-saldo');
            if (displaySaldo) displaySaldo.innerText = '$' + walletBalance.toFixed(2);
            
            // Progress bars on balance-card
            const walletValEl = document.getElementById('distribution-wallet-val');
            if (walletValEl) walletValEl.innerText = '$' + walletBalance.toFixed(2);
            const budgetValEl = document.getElementById('distribution-budget-val');
            if (budgetValEl) budgetValEl.innerText = '$' + totalBudget.toFixed(2);
            const spentValEl = document.getElementById('distribution-spent-val');
            if (spentValEl) spentValEl.innerText = '$' + spentFloat.toFixed(2);
            
            const walletPct = totalBudget > 0 ? Math.min((walletBalance / totalBudget) * 100, 100) : 0;
            const spentPct = totalBudget > 0 ? Math.min((spentFloat / totalBudget) * 100, 100) : 0;
            
            const walletBar = document.getElementById('distribution-wallet-bar');
            if (walletBar) walletBar.style.width = walletPct.toFixed(0) + '%';
            
            const budgetBar = document.getElementById('distribution-budget-bar');
            if (budgetBar) budgetBar.style.width = '100%';
            
            const spentBar = document.getElementById('distribution-spent-bar');
            if (spentBar) spentBar.style.width = spentPct.toFixed(0) + '%';
            
            // Update quick grid card values
            const gridWalletVal = document.getElementById('grid-wallet-val');
            if (gridWalletVal) gridWalletVal.innerText = '$' + walletBalance.toFixed(2);
            const gridBudgetVal = document.getElementById('grid-budget-val');
            if (gridBudgetVal) gridBudgetVal.innerText = '$' + totalBudget.toFixed(2);
            const gridSpentVal = document.getElementById('grid-spent-val');
            if (gridSpentVal) gridSpentVal.innerText = '$' + spentFloat.toFixed(2);
            
            // Donut chart centered spent amount
            const centerSpentEl = document.getElementById('chart-center-spent-amount');
            if (centerSpentEl) centerSpentEl.innerText = '$' + spentFloat.toFixed(2);
            
            // Resetear filtros y paginacion
            gastosDataFiltrados = gastosData;
            paginaActual = 1;
            renderTabla();
            dibujarGraficas();
        }

        // Busqueda local
        function filtrarTablaLocal() {
            const q = document.getElementById('buscarGasto').value.toLowerCase();
            gastosDataFiltrados = q ? gastosData.filter(g =>
                g.descripcion.toLowerCase().includes(q) ||
                g.categoria.toLowerCase().includes(q) ||
                (g.proyecto_nombre && g.proyecto_nombre.toLowerCase().includes(q))
            ) : gastosData;
            paginaActual = 1;
            renderTabla();
        }

        // Render paginado
        function renderTabla() {
            const total = gastosDataFiltrados.length;
            const totalPaginas = Math.ceil(total / POR_PAGINA) || 1;
            const inicio = (paginaActual - 1) * POR_PAGINA;
            const pagina = gastosDataFiltrados.slice(inicio, inicio + POR_PAGINA);
            
            const countEl = document.getElementById('tabla-count');
            if (countEl) countEl.innerText = `${total} registros`;

            const tbody = document.getElementById('transacciones-tbody');
            if (total === 0) {
                tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; color:var(--text-muted); padding:30px;">No hay gastos que coincidan</td></tr>`;
                document.getElementById('paginacion-container').innerHTML = '';
                return;
            }

            tbody.innerHTML = '';
            pagina.forEach(g => {
                let badgeClass = 'badge-pendiente';
                if (g.estado === 'Aprobado') badgeClass = 'badge-aprobado';
                else if (g.estado === 'Rechazado') badgeClass = 'badge-rechazado';
                
                tbody.innerHTML += `
                    <tr>
                        <td class="dev-only" style="font-family:monospace; font-size:0.75rem;">${g.id_gasto}</td>
                        <td><span class="badge-estado ${badgeClass}">${g.estado}</span></td>
                        <td>${g.fecha.split(' ')[0].substring(5)}</td>
                        <td><strong>${g.descripcion}</strong></td>
                        <td style="font-size:0.85rem;">Pr: ${g.proyecto_nombre || 'General'}<br>Vi: ${g.viaje_destino || 'General'}</td>
                        <td style="font-size:0.85rem;">${g.metodo_pago === 'Wallet' ? '🪙 Wallet' : (g.metodo_pago === 'Tarjeta' ? '💳 Tarjeta' : '💵 Efct.')}</td>
                        <td><span style="font-size:0.85rem; padding:4px 8px; border-radius:6px; background:rgba(255,255,255,0.05); color:var(--text-secondary); border: 1px solid rgba(255,255,255,0.04);">${g.categoria}</span></td>
                        <td style="text-align:right; font-weight:700;">$${g.monto.toFixed(2)}</td>
                        <td style="text-align:center;">
                            <button class="btn-icon" onclick="abrirEditModal(${g.id_gasto})" title="Editar">✏️</button>
                            <button class="btn-icon" onclick="eliminarGasto(${g.id_gasto})" title="Eliminar">🗑</button>
                        </td>
                    </tr>
                `;
            });

            // Paginacion
            const pagEl = document.getElementById('paginacion-container');
            if (!pagEl) return;
            if (totalPaginas <= 1) { pagEl.innerHTML = ''; return; }
            
            let html = '';
            const btnStyle = (active) => `style="padding:6px 12px; border-radius:6px; border:1px solid rgba(255,255,255,0.08); background:${active ? 'var(--primary)' : 'rgba(255,255,255,0.05)'}; color:${active ? '#060608' : 'var(--text-primary)'}; cursor:pointer; font-weight:600; transition: var(--transition);"`;
            if (paginaActual > 1) html += `<button ${btnStyle(false)} onclick="irPagina(${paginaActual-1})">&#8249;</button>`;
            for (let p = 1; p <= totalPaginas; p++) {
                html += `<button ${btnStyle(p===paginaActual)} onclick="irPagina(${p})">${p}</button>`;
            }
            if (paginaActual < totalPaginas) html += `<button ${btnStyle(false)} onclick="irPagina(${paginaActual+1})">&#8250;</button>`;
            pagEl.innerHTML = html;
        }

        function irPagina(p) { paginaActual = p; renderTabla(); }

        // Modal edicion
        function abrirEditModal(id) {
            const g = gastosData.find(x => x.id_gasto === id);
            if (!g) return showToast('Gasto no encontrado');
            document.getElementById('editGastoId').value = g.id_gasto;
            document.getElementById('editDesc').value = g.descripcion;
            document.getElementById('editMonto').value = g.monto;
            document.getElementById('editCategoria').value = g.categoria;
            document.getElementById('editEstado').value = g.estado;
            document.getElementById('editMetodo').value = g.metodo_pago || 'Efectivo';
            // Poblar proyectos y viajes del modal
            const ep = document.getElementById('editProyecto');
            const ev = document.getElementById('editViaje');
            ep.innerHTML = '<option value="">General</option>';
            ev.innerHTML = '<option value="">General</option>';
            proyectosData.forEach(p => ep.innerHTML += `<option value="${p.id_proyecto}"${g.id_proyecto==p.id_proyecto?' selected':''}>${p.codigo} - ${p.nombre}</option>`);
            viajesData.forEach(v => ev.innerHTML += `<option value="${v.id_viaje}"${g.id_viaje==v.id_viaje?' selected':''}>${v.destino}</option>`);
            document.getElementById('editGastoModal').style.display = 'flex';
        }

        function cerrarEditModal() {
            document.getElementById('editGastoModal').style.display = 'none';
        }

        function abrirGastoModal() {
            const el = document.getElementById('gastoModal');
            if (el) el.style.display = 'flex';
        }

        function cerrarGastoModal() {
            const el = document.getElementById('gastoModal');
            if (el) el.style.display = 'none';
        }

        function verificarLimitePresupuesto(categoria, nuevoMonto, excludeGastoId = null) {
            const catLimit = categoriasCustomData.find(c => c.nombre === categoria);
            if (catLimit && parseFloat(catLimit.limite_mensual) > 0) {
                const limit = parseFloat(catLimit.limite_mensual);
                let spent = 0;
                const thisMonth = new Date().toISOString().slice(0, 7);
                
                gastosData.forEach(g => {
                    if (g.id_gasto !== excludeGastoId && g.categoria === categoria && g.fecha && g.fecha.slice(0, 7) === thisMonth && g.estado !== 'Rechazado') {
                        spent += parseFloat(g.monto);
                    }
                });

                if (spent + nuevoMonto > limit) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Has excedido el presupuesto',
                        text: 'El monto excede el presupuesto límite establecido para la categoría ' + categoria + '.',
                        confirmButtonColor: '#d4af37'
                    });
                    return false;
                }
            }
            return true;
        }

        async function guardarEdicionGasto() {
            const id = parseInt(document.getElementById('editGastoId').value);
            const desc = document.getElementById('editDesc').value.trim();
            const monto = parseFloat(document.getElementById('editMonto').value);
            const cat = document.getElementById('editCategoria').value;
            const estado = document.getElementById('editEstado').value;
            const metodo = document.getElementById('editMetodo').value;
            const proy = document.getElementById('editProyecto').value;
            const viaje = document.getElementById('editViaje').value;
            
            if (!desc || isNaN(monto) || monto <= 0) return showToast('Completa los campos correctamente');

            if (!verificarLimitePresupuesto(cat, monto, id)) return;

            const res = await fetch('api/update_gasto.php', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id, descripcion:desc, monto, categoria:cat, estado, metodo_pago:metodo, id_proyecto:proy||null, id_viaje:viaje||null})
            });
            const json = await res.json();
            if (json.success) {
                cerrarEditModal();
                cargarGastos(); cargarSidebarYErpDatos(); cargarEstadisticasAvanzadas();
                showToast('✅ Gasto actualizado correctamente');
            } else {
                showToast(json.message || 'Error al actualizar');
            }
        }

        async function agregarGasto() {
            const desc = document.getElementById('descripcion').value.trim();
            const val = parseFloat(document.getElementById('monto').value);
            const cat = document.getElementById('categoria').value;
            const proy = document.getElementById('gastoProyecto').value;
            const viaje = document.getElementById('gastoViaje').value;
            const pago = document.getElementById('gastoMetodoPago').value;

            if(!desc || isNaN(val) || val <= 0) return showToast("Por favor, llena los campos correctamente.");

            if (!verificarLimitePresupuesto(cat, val)) return;

            const reqTime = performance.now();
            const response = await fetch("api/add_gasto.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({descripcion: desc, monto: val, categoria: cat, id_proyecto: proy, id_viaje: viaje, metodo_pago: pago, estado: 'Pendiente'})
            });
            const res = await response.json();
            if (res.success) {
                document.getElementById('descripcion').value = '';
                document.getElementById('monto').value = '';
                
                // Cerrar modal de gastos
                cerrarGastoModal();

                cargarGastos();
                cargarSidebarYErpDatos();
                cargarEstadisticasAvanzadas();
                showToast("✅ Gasto registrado y enviado a aprobación");
                actualizarDevLogs(res, performance.now() - reqTime, "POST /api/add_gasto.php");
            } else {
                showToast(res.message || "Error al agregar gasto");
            }
        }

        async function eliminarGasto(id) {
            if(!confirm("¿Estás seguro de eliminar este gasto?")) return;
            const reqTime = performance.now();
            const response = await fetch("api/delete_gasto.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({id: id})
            });
            const res = await response.json();
            if (res.success) {
                cargarGastos();
                cargarSidebarYErpDatos();
                showToast("Gasto eliminado correctamente");
                actualizarDevLogs(res, performance.now() - reqTime, "POST /api/delete_gasto.php");
            }
        }

        async function editarPresupuesto() {
            const nuevo = prompt("Ingresa el nuevo presupuesto mensual:", totalBudget);
            if(nuevo === null) return;
            const val = parseFloat(nuevo);
            if (isNaN(val) || val < 0) return alert("Presupuesto inválido");

            const reqTime = performance.now();
            const response = await fetch("api/update_presupuesto.php", {
                method: "POST", headers: {"Content-Type":"application/json"},
                body: JSON.stringify({presupuesto: val})
            });
            const res = await response.json();
            if (res.success) {
                totalBudget = val;
                document.getElementById('display-presupuesto').innerText = '$' + val.toFixed(2);
                showToast("Presupuesto mensual actualizado");
                actualizarDevLogs(res, performance.now() - reqTime, "POST /api/update_presupuesto.php");
            }
        }

        function dibujarGraficas() {
            const catTotals = {};
            const dateTotals = {};
            
            for(let i=6; i>=0; i--) {
                const d = new Date(); d.setDate(d.getDate() - i);
                dateTotals[d.toISOString().split('T')[0]] = 0;
            }

            // Agrupar solo gastos aprobados o pendientes en el análisis (no rechazados)
            gastosData.forEach(g => {
                if (g.estado !== 'Rechazado') {
                    catTotals[g.categoria] = (catTotals[g.categoria] || 0) + g.monto;
                    const dateKey = g.fecha.split(' ')[0];
                    if(dateTotals[dateKey] !== undefined) dateTotals[dateKey] += g.monto;
                }
            });

            const colors = {
                'Comida': '#00BCD4', 'Transporte': '#FFCA28', 'Entretenimiento': '#ab47bc',
                'Servicios': '#66BB6A', 'Hogar': '#ef5350', 'Otros': '#8D6E63'
            };

            if(donutChart) donutChart.destroy();
            donutChart = new Chart(document.getElementById('donutChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(catTotals),
                    datasets: [{
                        data: Object.values(catTotals),
                        backgroundColor: Object.keys(catTotals).map(k => colors[k] || '#ccc'),
                        borderWidth: 0
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    cutout: '75%',
                    plugins: { 
                        legend: { 
                            position: 'bottom',
                            labels: {
                                color: '#F5E6D3',
                                font: {
                                    family: 'Outfit',
                                    size: 11
                                }
                            }
                        } 
                    } 
                }
            });

            if(lineChart) lineChart.destroy();
            lineChart = new Chart(document.getElementById('lineChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: Object.keys(dateTotals).map(d => d.substring(5)),
                    datasets: [{
                        label: 'Gasto Diario',
                        data: Object.values(dateTotals),
                        borderColor: '#D4AF37',
                        backgroundColor: 'rgba(212, 175, 55, 0.1)',
                        fill: true, tension: 0.4, borderWidth: 2, pointRadius: 4
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            grid: { 
                                color: 'rgba(255, 255, 255, 0.08)' 
                            },
                            ticks: {
                                color: '#E8D5C0',
                                font: { family: 'Outfit' }
                            }
                        }, 
                        x: { 
                            grid: { 
                                display: false 
                            },
                            ticks: {
                                color: '#E8D5C0',
                                font: { family: 'Outfit' }
                            }
                        } 
                    },
                    plugins: { 
                        legend: { 
                            display: false 
                        } 
                    }
                }
            });
        }

        // Dev Logs
        let lastRawJson = {};
        function actualizarDevLogs(json = lastRawJson, timeMs = 0, route = "GET /api/get_gastos.php") {
            lastRawJson = json;
            const meta = document.getElementById('dev-meta-info');
            const output = document.getElementById('dev-json-output');
            if (meta && output) {
                meta.innerText = `[${new Date().toISOString()}] ${route} | 🟢 200 OK | ${timeMs.toFixed(2)}ms`;
                output.innerText = JSON.stringify(json, null, 2);
            }
        }

        // Biometrics
        function checkBiometricToggle() {
            const toggle = document.getElementById('biometricToggle');
            if (!toggle) return;
            const token = localStorage.getItem(`bio_token_${userEmail}`);
            const isDb = <?= $user['biometrico'] == 1 ? 'true' : 'false' ?>;
            toggle.checked = (token && isDb);
            
            // Update status box
            const statusBox = document.getElementById('biometric-status-box');
            if (statusBox) statusBox.style.display = toggle.checked ? 'block' : 'none';

            // Device detection and texts customization
            const ua = navigator.userAgent;
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua) || 
                             (navigator.maxTouchPoints > 0 && /Macintosh|Intel/i.test(ua));
                             
            const panelTitle = document.getElementById('biometricPanelTitle');
            const panelDesc = document.getElementById('biometricPanelDesc');
            const labelTitle = document.getElementById('biometricLabelTitle');
            const labelSubtitle = document.getElementById('biometricLabelSubtitle');
            const statusText = document.getElementById('biometricStatusText');
            
            if (isMobile) {
                if (panelTitle) panelTitle.innerHTML = "🔐 Ajustes de Biometría Móvil";
                if (panelDesc) panelDesc.innerHTML = "Activa esta opción para permitir el inicio de sesión seguro usando la biometría de tu teléfono (huella digital o reconocimiento facial Face ID).";
                if (labelTitle) labelTitle.innerHTML = "Usar Biometría Celular (Huella / Face ID)";
                if (labelSubtitle) labelSubtitle.innerHTML = "Al activar, podrás ingresar usando los sensores biométricos de tu teléfono.";
                if (statusText) statusText.innerHTML = "✅ Biometría móvil registrada. Puedes usar tu huella/Face ID en el login.";
            } else {
                if (panelTitle) panelTitle.innerHTML = "🔐 Ajustes de Windows Hello";
                if (panelDesc) panelDesc.innerHTML = "Activa esta opción para permitir el inicio de sesión seguro usando Windows Hello (huella ThinkPad, reconocimiento facial o código PIN de tu computadora).";
                if (labelTitle) labelTitle.innerHTML = "Usar Windows Hello (Huella / Rostro / PIN)";
                if (labelSubtitle) labelSubtitle.innerHTML = "Al activar, podrás ingresar usando Windows Hello en esta computadora.";
                if (statusText) statusText.innerHTML = "✅ Windows Hello registrado. Puedes iniciar sesión usando tu huella/PIN en esta computadora.";
            }

            actualizarBiometriaLog();
        }

        async function toggleBiometria(cb) {
            const enable = cb.checked;
            try {
                if (enable && window.PublicKeyCredential && navigator.credentials) {
                    try {
                        const optsRes = await fetch('api/webauthn_register_options.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ correo: userEmail }) });
                        const opts = await optsRes.json();
                        if (opts && opts.publicKey) {
                            opts.publicKey.challenge = base64ToBuffer(opts.publicKey.challenge);
                            if (opts.publicKey.user && opts.publicKey.user.id) opts.publicKey.user.id = base64ToBuffer(opts.publicKey.user.id);
                            const cred = await navigator.credentials.create({ publicKey: opts.publicKey });
                            if (cred) {
                                const rawIdB64 = bufferToBase64Url(cred.rawId);

                                try {
                                    const attBuf = cred.response.attestationObject;
                                    const attObj = decodeCBOR(attBuf);
                                    const authData = attObj.authData;

                                    const authView = new DataView(authData.buffer, authData.byteOffset || 0, authData.byteLength || authData.length);
                                    let offset = 0;
                                    offset += 32;
                                    offset += 1;
                                    offset += 4;

                                    const flags = authData[32];
                                    if ((flags & 0x40) !== 0) {
                                        offset += 16;
                                        const credIdLen = authView.getUint16(offset);
                                        offset += 2;
                                        offset += credIdLen;

                                        const coseStart = offset;
                                        const coseBuf = authData.slice(coseStart);
                                        const cose = decodeCBOR(coseBuf.buffer || coseBuf);
                                        const jwk = coseToJwk(cose);

                                        await fetch('api/webauthn_register.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ correo: userEmail, credentialId: rawIdB64, publicKeyJwk: jwk }) });
                                    } else {
                                        await fetch('api/webauthn_register.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ correo: userEmail, credentialId: rawIdB64 }) });
                                    }
                                } catch (err) {
                                    console.warn('No se pudo extraer publicKey, registrando solo id:', err);
                                    await fetch('api/webauthn_register.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ correo: userEmail, credentialId: rawIdB64 }) });
                                }
                            }
                        }
                    } catch (err) {
                        console.warn('Registro WebAuthn falló:', err);
                    }
                }

                const res = await fetch("api/biometrico.php", {
                    method: "POST", headers: {"Content-Type":"application/json"},
                    body: JSON.stringify({action: "register", enable: enable})
                });
                const json = await res.json();
                if (json.success) {
                    enable ? localStorage.setItem(`bio_token_${userEmail}`, json.token) : localStorage.removeItem(`bio_token_${userEmail}`);
                    showToast(enable ? "Biometría habilitada" : "Biometría deshabilitada");
                } else {
                    cb.checked = !enable;
                }
            } catch(e) { cb.checked = !enable; }
            actualizarBiometriaLog();
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

        function decodeCBOR(input) {
            const bytes = input instanceof ArrayBuffer ? new Uint8Array(input) : new Uint8Array(input);
            let offset = 0;

            function readUint(addInfo) {
                if (addInfo < 24) return addInfo;
                if (addInfo === 24) return bytes[offset++];
                if (addInfo === 25) { const v = (bytes[offset]<<8) | bytes[offset+1]; offset+=2; return v; }
                if (addInfo === 26) { const v = (bytes[offset]<<24)|(bytes[offset+1]<<16)|(bytes[offset+2]<<8)|bytes[offset+3]; offset+=4; return v; }
                if (addInfo === 27) { let v=0; for(let i=0;i<8;i++){v=(v<<8)|bytes[offset+i];} offset+=8; return v; }
                return null;
            }

            function parseItem() {
                const initial = bytes[offset++];
                const major = initial >> 5;
                const add = initial & 0x1f;
                if (major === 0) return readUint(add);
                if (major === 1) return -1 - readUint(add);
                if (major === 2) {
                    const len = readUint(add);
                    const b = bytes.slice(offset, offset+len); offset += len; return b;
                }
                if (major === 3) {
                    const len = readUint(add);
                    const s = new TextDecoder().decode(bytes.slice(offset, offset+len)); offset += len; return s;
                }
                if (major === 4) {
                    const len = readUint(add);
                    const arr = []; for (let i=0;i<len;i++) arr.push(parseItem()); return arr;
                }
                if (major === 5) {
                    const len = readUint(add);
                    const obj = {};
                    for (let i=0;i<len;i++) {
                        const key = parseItem();
                        const val = parseItem();
                        obj[key] = val;
                    }
                    return obj;
                }
                if (major === 6) {
                    const tag = readUint(add);
                    const item = parseItem(); return item;
                }
                if (major === 7) {
                    if (add === 25) { const v = (bytes[offset]<<8)|(bytes[offset+1]); offset+=2; return v; }
                    if (add === 26) { const v = (bytes[offset]<<24)|(bytes[offset+1]<<16)|(bytes[offset+2]<<8)|bytes[offset+3]; offset+=4; return v; }
                    if (add === 27) { let v=0; for(let i=0;i<8;i++){v=(v<<8)|bytes[offset+i];} offset+=8; return v; }
                    return null;
                }
                return null;
            }

            return parseItem();
        }

        function coseToJwk(cose) {
            const kty = cose[1];
            if (kty === 2) {
                const crv = cose[-1];
                const x = cose[-2];
                const y = cose[-3];
                const crvName = (crv === 1) ? 'P-256' : (crv === 2 ? 'P-384' : 'P-521');
                return { kty: 'EC', crv: crvName, x: bufferToBase64Url(x), y: bufferToBase64Url(y) };
            }
            return null;
        }

        function actualizarBiometriaLog() {
            const info = {
                localStorageToken: localStorage.getItem(`bio_token_${userEmail}`) || 'null',
                dbStatus: <?= $user['biometrico'] == 1 ? '1' : '0' ?>,
                userAgent: navigator.userAgent
            };
            const el = document.getElementById('dev-bio-status');
            if (el) el.innerText = JSON.stringify(info, null, 2);
        }

        // ===== PERFIL FUNCTIONS =====
        async function guardarDatosPerfil() {
            const nombre = document.getElementById('perfilNombre').value.trim();
            const correo = document.getElementById('perfilCorreo').value.trim();
            if (!nombre || !correo) return showToast('Nombre y correo son requeridos');
            const res = await fetch('api/update_perfil.php', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({action: 'update_datos', nombre, correo})
            });
            const json = await res.json();
            showToast(json.success ? '✅ Datos actualizados correctamente' : json.message);
        }

        async function cambiarPassword() {
            const actual = document.getElementById('perfilPassActual').value;
            const nueva = document.getElementById('perfilPassNueva').value;
            const confirmar = document.getElementById('perfilPassConfirmar').value;
            if (!actual || !nueva || !confirmar) return showToast('Todos los campos son requeridos');
            const res = await fetch('api/update_perfil.php', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({action: 'update_password', actual, nueva, confirmar})
            });
            const json = await res.json();
            if (json.success) {
                showToast('✅ Contraseña actualizada');
                document.getElementById('perfilPassActual').value = '';
                document.getElementById('perfilPassNueva').value = '';
                document.getElementById('perfilPassConfirmar').value = '';
            } else {
                showToast(json.message || 'Error al actualizar');
            }
        }

        async function actualizarFotoPerfil(event) {
            const file = event.target.files[0];
            if (!file) return;
            // Preview
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('perfil-foto-preview').src = e.target.result;
                // Also update sidebar photo
                const sidebarPhoto = document.querySelector('.profile-mini img');
                if (sidebarPhoto) sidebarPhoto.src = e.target.result;
            };
            reader.readAsDataURL(file);
            
            // Upload
            const fd = new FormData();
            fd.append('foto', file);
            fd.append('action', 'update_foto');
            const res = await fetch('api/update_perfil.php?action=update_foto', {method:'POST', body: fd});
            const json = await res.json();
            showToast(json.success ? '✅ Foto de perfil actualizada' : json.message || 'Error al subir foto');
        }

        function actualizarPerfilStats() {
            const gastoEl = document.getElementById('perfil-stat-gastos');
            const proyEl  = document.getElementById('perfil-stat-proyectos');
            const viajesEl = document.getElementById('perfil-stat-viajes');
            const walletEl = document.getElementById('perfil-stat-wallet');
            if (gastoEl)  gastoEl.innerText  = gastosData.length + ' gastos';
            if (proyEl)   proyEl.innerText   = proyectosData.length + ' proyectos';
            if (viajesEl) viajesEl.innerText = viajesData.length + ' viajes';
            if (walletEl) walletEl.innerText = '$' + walletBalance.toFixed(2);
        }

        // AUTO-UPDATE VIAJE STATUS based on dates
        function autoUpdateViajeStatus(v) {
            if (!v.fecha_inicio || !v.fecha_fin) return v.estado;
            const hoy = new Date().toISOString().split('T')[0];
            if (hoy < v.fecha_inicio) return 'Planificado';
            if (hoy > v.fecha_fin) return 'Terminado';
            return 'En curso';
        }

        // Override renderViajes to use auto status
        const _originalRenderViajes = renderViajes;
        function renderViajes() {
            const tbody = document.getElementById('viajes-tbody');
            if (viajesData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No hay viajes registrados</td></tr>`;
                return;
            }
            tbody.innerHTML = '';
            viajesData.forEach(v => {
                const estado = autoUpdateViajeStatus(v);
                let badgeClass = 'badge-pendiente';
                if (estado === 'En curso') badgeClass = 'badge-aprobado';
                else if (estado === 'Terminado') badgeClass = 'badge-rechazado';
                
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${v.destino}</strong></td>
                        <td style="font-size:0.85rem;">Inicio: ${v.fecha_inicio}<br>Fin: ${v.fecha_fin}</td>
                        <td style="font-weight:600;">$${v.presupuesto.toFixed(2)}</td>
                        <td><span class="badge-estado ${badgeClass}">${estado}</span></td>
                    </tr>
                `;
            });
        }

        // checkBiometricToggle has been unified above

        // ===== ESTADISTICAS AVANZADAS =====
        function renderEstadisticas() {
            const ctxBar = document.getElementById('statsBarChart')?.getContext('2d');
            if (ctxBar) {
                const limits = {};
                categoriasCustomData.forEach(c => { limits[c.nombre] = c.limite_mensual; });
                
                const catSpent = {};
                gastosData.forEach(g => {
                    if (g.estado === 'Aprobado') {
                        catSpent[g.categoria] = (catSpent[g.categoria] || 0) + g.monto;
                    }
                });

                const labels = Object.keys(catSpent);
                const spentData = Object.values(catSpent);
                const budgetData = labels.map(l => limits[l] || 0);

                if (statsBarChart) statsBarChart.destroy();
                statsBarChart = new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'Consumo Real ($)', data: spentData, backgroundColor: '#6D4C41', borderRadius: 6 },
                            { label: 'Límite Máximo ($)', data: budgetData, backgroundColor: '#d7ccc8', borderRadius: 6 }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                });
            }

            const ctxLine = document.getElementById('statsLineChart')?.getContext('2d');
            if (ctxLine) {
                const reversedTxs = [...walletTxData].reverse();
                let runningBalance = 0;
                const historyData = [];
                const historyLabels = [];
                
                reversedTxs.forEach(tx => {
                    if (tx.tipo === 'deposito') runningBalance += tx.monto;
                    else runningBalance -= tx.monto;
                    historyData.push(runningBalance);
                    historyLabels.push(tx.fecha.split(' ')[0].substring(5));
                });
                
                if (historyData.length === 0) {
                    historyData.push(walletBalance);
                    historyLabels.push('Hoy');
                }

                if (statsLineChart) statsLineChart.destroy();
                statsLineChart = new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: historyLabels,
                        datasets: [{
                            label: 'Saldo Billetera ($)',
                            data: historyData,
                            borderColor: '#8D6E63',
                            backgroundColor: 'rgba(141, 110, 99, 0.1)',
                            fill: true, tension: 0.3
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // Render Top 5 from estadisticasData
            if (estadisticasData.top5_gastos) {
                renderTop5(estadisticasData.top5_gastos);
            }
            
            // Render KPI comparativa
            if (estadisticasData.gasto_mes_actual !== undefined) {
                renderKPIComparativa(estadisticasData);
            }
        }

        function renderTop5(top5) {
            const cont = document.getElementById('top5-container');
            if (!cont) return;
            if (top5.length === 0) { cont.innerHTML = '<p style="color:var(--text-muted); text-align:center;">Sin datos del mes</p>'; return; }
            const maxVal = top5[0].monto;
            cont.innerHTML = top5.map((g, i) => {
                const pct = maxVal > 0 ? (g.monto / maxVal * 100).toFixed(0) : 0;
                return `
                    <div style="margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                            <span style="font-size:0.85rem; font-weight:600;">${i+1}. ${g.descripcion}</span>
                            <strong style="color:var(--primary);">$${g.monto.toFixed(2)}</strong>
                        </div>
                        <div class="progress-container"><div class="progress-bar" style="width:${pct}%;"></div></div>
                        <div style="font-size:0.72rem; color:var(--text-muted); margin-top:2px;">${g.categoria} — ${g.fecha ? g.fecha.split(' ')[0] : ''}</div>
                    </div>
                `;
            }).join('');
        }

        function renderKPIComparativa(d) {
            const el = document.getElementById('kpi-comparativa-container');
            if (!el) return;
            const diff = d.gasto_mes_actual - d.gasto_mes_anterior;
            const diffPct = d.gasto_mes_anterior > 0 ? Math.abs((diff / d.gasto_mes_anterior) * 100).toFixed(1) : 0;
            const trending = diff > 0 ? '↑' : (diff < 0 ? '↓' : '→');
            const color = diff > 0 ? '#C62828' : '#2E7D32';
            el.innerHTML = `
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px; text-align:center;">
                    <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius:10px; padding:15px;">
                        <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:6px;">Mes Actual</div>
                        <div style="font-size:1.4rem; font-weight:700; color:var(--text-primary);">$${(d.gasto_mes_actual||0).toFixed(2)}</div>
                    </div>
                    <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius:10px; padding:15px;">
                        <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:6px;">Mes Anterior</div>
                        <div style="font-size:1.4rem; font-weight:700; color:var(--text-muted);">$${(d.gasto_mes_anterior||0).toFixed(2)}</div>
                    </div>
                    <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius:10px; padding:15px;">
                        <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:6px;">Variación</div>
                        <div style="font-size:1.4rem; font-weight:700; color:${color};">${trending} ${diffPct}%</div>
                    </div>
                </div>
            `;
        }
    </script>
</body>
</html>
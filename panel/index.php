<?php
require_once __DIR__ . '/includes/init.php';

$pages = ['dashboard', 'users', 'gastos', 'analytics', 'settings'];
$page = isset($_GET['page']) && in_array($_GET['page'], $pages, true)
    ? $_GET['page']
    : 'dashboard';

$pageTitles = [
    'dashboard' => 'Dashboard',
    'users'     => 'Usuarios',
    'gastos'    => 'Gastos',
    'analytics' => 'Analíticas',
    'settings'  => 'Configuración',
];

$activeTitle = $pageTitles[$page];
$userInitials = $panelData['currentUser']['initials'];
$userName = htmlspecialchars($currentUser['nombre']);
$userEmail = htmlspecialchars($currentUser['correo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestor de Gastos — <?= htmlspecialchars($activeTitle) ?></title>
  <meta name="description" content="Panel de administración del Gestor de Gastos — usuarios, gastos y biometría.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="css/sweetalert.css">
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="mobile-overlay" id="mobile-overlay" onclick="closeSidebar()"></div>

<div id="app">

  <aside id="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-inner">
        <div class="sidebar-logo-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
        </div>
        <div>
          <p class="sidebar-logo-title">Gestor de Gastos</p>
          <p class="sidebar-logo-sub">Panel administrativo</p>
        </div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <p class="nav-section-label">Principal</p>

      <a href="?page=dashboard" class="nav-item <?= $page === 'dashboard' ? 'active' : '' ?>" id="nav-dashboard">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
          <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
        <span class="nav-label">Dashboard</span>
      </a>

      <a href="?page=users" class="nav-item <?= $page === 'users' ? 'active' : '' ?>" id="nav-users">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <span class="nav-label">Usuarios</span>
      </a>

      <p class="nav-section-label">Gestión</p>

      <a href="?page=gastos" class="nav-item <?= $page === 'gastos' ? 'active' : '' ?>" id="nav-gastos">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
          <line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
        <span class="nav-label">Gastos</span>
      </a>

      <a href="?page=analytics" class="nav-item <?= $page === 'analytics' ? 'active' : '' ?>" id="nav-analytics">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
          <line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
        </svg>
        <span class="nav-label">Analíticas</span>
      </a>

      <a href="?page=settings" class="nav-item <?= $page === 'settings' ? 'active' : '' ?>" id="nav-settings">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="3"/>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06
                   a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09
                   A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83
                   l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09
                   A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83
                   l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09
                   a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83
                   l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09
                   a1.65 1.65 0 0 0-1.51 1z"/>
        </svg>
        <span class="nav-label">Configuración</span>
      </a>

      <a href="../dashboard.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span class="nav-label">Mi dashboard</span>
      </a>
    </nav>

    <div class="sidebar-user">
      <div class="sidebar-user-card">
        <div class="avatar"><?= htmlspecialchars($userInitials) ?></div>
        <div>
          <p class="sidebar-user-name"><?= $userName ?></p>
          <p class="sidebar-user-email"><?= $userEmail ?></p>
        </div>
      </div>
      <a href="../logout.php" class="sidebar-logout">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>Cerrar sesión</span>
      </a>
    </div>
  </aside>

  <div id="main">
    <header id="header">
      <button class="btn-hamburger btn-icon" onclick="openSidebar()" id="hamburger-btn" aria-label="Menú">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="12" x2="21" y2="12"/>
          <line x1="3" y1="6"  x2="21" y2="6"/>
          <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>

      <span id="header-title"><?= htmlspecialchars($activeTitle) ?></span>

      <div class="header-search">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" placeholder="Buscar..." id="global-search" aria-label="Buscar">
      </div>

      <div class="avatar" style="cursor:pointer" title="<?= $userName ?>"><?= htmlspecialchars($userInitials) ?></div>
    </header>

    <main id="content">
      <?php
        $file = __DIR__ . '/pages/' . $page . '.php';
        if (file_exists($file)) {
            include $file;
        } else {
            echo '<p style="color:var(--text-dim)">Página no encontrada.</p>';
        }
      ?>
    </main>
  </div>
</div>

<script>
window.PANEL_DATA = <?= json_encode($panelData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
window.PANEL_USER_EMAIL = <?= json_encode($currentUser['correo']) ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="js/app.js"></script>
<?php
$jsFile = 'js/' . $page . '.js';
if (file_exists(__DIR__ . '/' . $jsFile)):
?>
<script src="<?= $jsFile ?>"></script>
<?php endif; ?>

</body>
</html>

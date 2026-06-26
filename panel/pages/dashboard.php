<!-- ── Dashboard ─────────────────────────────────────────── -->
<div class="dashboard-grid" id="dashboard-root">

  <div class="card card-p col-3 stat-card">
    <div class="stat-card-top">
      <p class="stat-label">Total usuarios</p>
      <div class="stat-icon" style="background:rgba(108,99,255,0.12)">
        <svg style="color:#6c63ff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
      </div>
    </div>
    <p class="stat-value" id="stat-total">0</p>
    <div class="stat-change up">
      <span>Registrados en el sistema</span>
    </div>
  </div>

  <div class="card card-p col-3 stat-card">
    <div class="stat-card-top">
      <p class="stat-label">Total gastos</p>
      <div class="stat-icon" style="background:rgba(52,211,153,0.12)">
        <svg style="color:#34d399" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
          <line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
      </div>
    </div>
    <p class="stat-value" id="stat-gastos">0</p>
    <div class="stat-change up">
      <span>Movimientos registrados</span>
    </div>
  </div>

  <div class="card card-p col-3 stat-card">
    <div class="stat-card-top">
      <p class="stat-label">Monto total</p>
      <div class="stat-icon" style="background:rgba(245,158,11,0.12)">
        <svg style="color:#f59e0b" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="1" x2="12" y2="23"/>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
      </div>
    </div>
    <p class="stat-value" id="stat-monto">$0.00</p>
    <div class="stat-change up">
      <span>Suma de todos los gastos</span>
    </div>
  </div>

  <div class="card card-p col-3 stat-card">
    <div class="stat-card-top">
      <p class="stat-label">Biometría activa</p>
      <div class="stat-icon" style="background:rgba(34,211,238,0.12)">
        <svg style="color:#22d3ee" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          <polyline points="9 12 11 14 15 10"/>
        </svg>
      </div>
    </div>
    <p class="stat-value" id="stat-biometric">0</p>
    <div class="stat-change up">
      <span>Usuarios con huella habilitada</span>
    </div>
  </div>

  <div class="card card-p col-8">
    <div class="chart-header">
      <div>
        <p class="chart-title">Métricas de gastos</p>
        <p class="chart-sub">Montos y cantidad de movimientos del año actual</p>
      </div>
      <div class="tab-pills">
        <button class="tab-pill active" id="tab-amounts" onclick="switchChart('amounts')">Montos</button>
        <button class="tab-pill" id="tab-counts" onclick="switchChart('counts')">Cantidad</button>
      </div>
    </div>
    <canvas id="main-chart" height="220"></canvas>
  </div>

  <div class="card card-p col-4">
    <p class="chart-title mb-1">Categorías</p>
    <p class="chart-sub mb-3">Distribución por categoría</p>
    <canvas id="pie-chart" height="140"></canvas>
    <div class="pie-legend" id="pie-legend"></div>
  </div>

  <div class="card card-p col-6">
    <div class="flex-between mb-4">
      <div>
        <p class="chart-title">Usuarios recientes</p>
        <p class="chart-sub">Últimos registros</p>
      </div>
      <a href="?page=users" class="btn-view-all">
        Ver todos
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>
        </svg>
      </a>
    </div>
    <div id="recent-users-list"></div>
  </div>

  <div class="card card-p col-6">
    <div class="flex-between mb-4">
      <div>
        <p class="chart-title">Gastos recientes</p>
        <p class="chart-sub">Últimos movimientos del sistema</p>
      </div>
      <a href="?page=gastos" class="btn-view-all">
        Ver todos
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>
        </svg>
      </a>
    </div>
    <div id="recent-gastos-list"></div>
  </div>

</div>

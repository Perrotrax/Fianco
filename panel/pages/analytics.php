<!-- ── Analíticas ─────────────────────────────────────────── -->

<div class="kpi-grid">
  <div class="kpi-card">
    <p class="kpi-label">Gasto promedio</p>
    <p class="kpi-value" id="kpi-promedio">$0.00</p>
    <p class="kpi-sub">Por movimiento registrado</p>
  </div>
  <div class="kpi-card">
    <p class="kpi-label">Categoría principal</p>
    <p class="kpi-value" id="kpi-categoria">—</p>
    <p class="kpi-sub" id="kpi-categoria-sub">Mayor monto acumulado</p>
  </div>
  <div class="kpi-card">
    <p class="kpi-label">Usuarios activos</p>
    <p class="kpi-value" id="kpi-usuarios">0</p>
    <p class="kpi-sub">Con al menos un gasto</p>
  </div>
  <div class="kpi-card">
    <p class="kpi-label">Biometría habilitada</p>
    <p class="kpi-value" id="kpi-biometric">0</p>
    <p class="kpi-sub" id="kpi-biometric-sub">0% del total de usuarios</p>
  </div>
</div>

<div class="charts-2col">
  <div class="card card-p">
    <p class="chart-title mb-1">Gastos mensuales</p>
    <p class="chart-sub mb-3">Montos registrados por mes (año actual)</p>
    <canvas id="analytics-bar-chart" height="220"></canvas>
  </div>
  <div class="card card-p">
    <p class="chart-title mb-1">Registros de usuarios</p>
    <p class="chart-sub mb-3">Nuevos usuarios por mes (año actual)</p>
    <canvas id="analytics-line-chart" height="220"></canvas>
  </div>
</div>

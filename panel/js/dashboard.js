/**
 * dashboard.js — Dashboard conectado a base.sql (usuarios + gastos)
 */

const panelData = window.PANEL_DATA || {};
const charts = panelData.charts || {};
const stats = panelData.stats || {};
const users = panelData.users || [];
const gastos = panelData.gastos || [];

Chart.defaults.color = '#7070a0';
Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
Chart.defaults.font.size = 11;

const gridColor = 'rgba(255,255,255,0.04)';
const tooltipPlugin = {
  backgroundColor: '#18181f',
  borderColor: 'rgba(255,255,255,0.07)',
  borderWidth: 1,
  titleColor: '#e8e8f0',
  bodyColor: '#a8a8c0',
  padding: 10,
  cornerRadius: 10,
};

let mainChartInst = null;
let currentChartTab = 'amounts';

function formatMoney(value) {
  return '$' + Number(value || 0).toFixed(2);
}

function buildAmountsChart(ctx) {
  return new Chart(ctx, {
    type: 'line',
    data: {
      labels: charts.months || [],
      datasets: [{
        label: 'Montos',
        data: charts.monthlyAmounts || [],
        borderColor: '#6c63ff',
        backgroundColor: 'rgba(108,99,255,0.15)',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointRadius: 0,
        pointHoverRadius: 4,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        tooltip: {
          ...tooltipPlugin,
          callbacks: {
            label: (ctx) => ` ${formatMoney(ctx.parsed.y)}`,
          },
        },
        legend: { display: false },
      },
      scales: {
        x: { grid: { color: gridColor }, border: { display: false } },
        y: {
          grid: { color: gridColor },
          border: { display: false },
          ticks: { callback: (v) => '$' + v },
        },
      },
    },
  });
}

function buildCountsChart(ctx) {
  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels: charts.months || [],
      datasets: [{
        label: 'Cantidad',
        data: charts.monthlyCounts || [],
        backgroundColor: '#22d3ee',
        borderRadius: 4,
        barPercentage: 0.6,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { tooltip: tooltipPlugin, legend: { display: false } },
      scales: {
        x: { grid: { display: false }, border: { display: false } },
        y: { grid: { color: gridColor }, border: { display: false } },
      },
    },
  });
}

function switchChart(tab) {
  if (tab === currentChartTab) return;
  currentChartTab = tab;

  document.getElementById('tab-amounts').classList.toggle('active', tab === 'amounts');
  document.getElementById('tab-counts').classList.toggle('active', tab === 'counts');

  if (mainChartInst) mainChartInst.destroy();

  const ctx = document.getElementById('main-chart').getContext('2d');
  mainChartInst = tab === 'amounts' ? buildAmountsChart(ctx) : buildCountsChart(ctx);
}

const PIE_COLORS = ['#6c63ff', '#22d3ee', '#34d399', '#f59e0b', '#f472b6', '#ef4444'];

function buildPieChart(categories) {
  const ctx = document.getElementById('pie-chart');
  if (!ctx || !categories.length) return;

  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: categories.map((c) => c.name),
      datasets: [{
        data: categories.map((c) => c.total),
        backgroundColor: PIE_COLORS,
        borderColor: '#111118',
        borderWidth: 3,
        hoverOffset: 4,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: { tooltip: tooltipPlugin, legend: { display: false } },
    },
  });

  const legend = document.getElementById('pie-legend');
  legend.innerHTML = categories.map((r, i) => `
    <div class="pie-legend-item">
      <div class="pie-legend-dot" style="background:${PIE_COLORS[i % PIE_COLORS.length]}"></div>
      <span class="pie-legend-name">${r.name}</span>
      <span class="pie-legend-val">${formatMoney(r.total)}</span>
    </div>
  `).join('');
}

function renderRecentUsers() {
  const list = document.getElementById('recent-users-list');
  if (!list) return;

  const recent = users.slice(0, 6);
  if (!recent.length) {
    list.innerHTML = '<p class="muted">No hay usuarios registrados.</p>';
    return;
  }

  list.innerHTML = recent.map((u) => `
    <div class="user-list-item">
      <div class="avatar" style="background:${u.avatarColor}22;color:${u.avatarColor}">${getInitials(u.name)}</div>
      <div class="user-info">
        <p class="user-name">${u.name}</p>
        <p class="user-email">${u.email}</p>
      </div>
      <span class="badge-role" style="background:${u.biometric ? 'rgba(52,211,153,0.12)' : 'rgba(112,112,160,0.1)'};color:${u.biometric ? '#34d399' : '#7070a0'}">
        ${u.biometric ? 'Biometría ON' : 'Biometría OFF'}
      </span>
    </div>
  `).join('');
}

function renderRecentGastos() {
  const list = document.getElementById('recent-gastos-list');
  if (!list) return;

  const recent = gastos.slice(0, 6);
  if (!recent.length) {
    list.innerHTML = '<p class="muted">No hay gastos registrados.</p>';
    return;
  }

  list.innerHTML = recent.map((g) => `
    <div class="user-list-item">
      <div class="avatar" style="background:rgba(245,158,11,0.12);color:#f59e0b">$</div>
      <div class="user-info">
        <p class="user-name">${g.description}</p>
        <p class="user-email">${g.userName} · ${g.category}</p>
      </div>
      <span class="badge-role" style="background:rgba(108,99,255,0.12);color:#a89fff">${formatMoney(g.amount)}</span>
    </div>
  `).join('');
}

function updateStats() {
  const setEl = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  };

  setEl('stat-total', stats.totalUsuarios || 0);
  setEl('stat-gastos', stats.totalGastos || 0);
  setEl('stat-monto', formatMoney(stats.montoTotal));
  setEl('stat-biometric', stats.biometricosActivos || 0);
}

(function initDashboard() {
  updateStats();
  renderRecentUsers();
  renderRecentGastos();

  const mainCtx = document.getElementById('main-chart');
  if (mainCtx) mainChartInst = buildAmountsChart(mainCtx.getContext('2d'));

  buildPieChart(charts.categories || []);
})();

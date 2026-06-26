/**
 * analytics.js — Analíticas de gastos y usuarios (base.sql)
 */

const panelData = window.PANEL_DATA || {};
const charts = panelData.charts || {};
const stats = panelData.stats || {};
const gastos = panelData.gastos || [];

Chart.defaults.color = '#7070a0';
Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
Chart.defaults.font.size = 11;

const tooltipCfg = {
  backgroundColor: '#18181f',
  borderColor: 'rgba(255,255,255,0.07)',
  borderWidth: 1,
  titleColor: '#e8e8f0',
  bodyColor: '#a8a8c0',
  padding: 10,
  cornerRadius: 10,
};
const gridColor = 'rgba(255,255,255,0.04)';

function formatMoney(value) {
  return '$' + Number(value || 0).toFixed(2);
}

function updateKpis() {
  const avg = stats.totalGastos > 0 ? stats.montoTotal / stats.totalGastos : 0;
  const topCategory = (charts.categories || [])[0];
  const activeUsers = new Set(gastos.map((g) => g.userId)).size;
  const bioPercent = stats.totalUsuarios > 0
    ? Math.round((stats.biometricosActivos / stats.totalUsuarios) * 100)
    : 0;

  const setText = (id, text) => {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  };

  setText('kpi-promedio', formatMoney(avg));
  setText('kpi-categoria', topCategory ? topCategory.name : '—');
  setText('kpi-categoria-sub', topCategory ? formatMoney(topCategory.total) + ' acumulados' : 'Sin datos');
  setText('kpi-usuarios', String(activeUsers));
  setText('kpi-biometric', String(stats.biometricosActivos || 0));
  setText('kpi-biometric-sub', `${bioPercent}% del total de usuarios`);
}

(function initAnalytics() {
  updateKpis();

  const barCtx = document.getElementById('analytics-bar-chart');
  if (barCtx) {
    new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: charts.months || [],
        datasets: [{
          label: 'Gastos',
          data: charts.monthlyAmounts || [],
          backgroundColor: '#6c63ff',
          borderRadius: 4,
          barPercentage: 0.7,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          tooltip: {
            ...tooltipCfg,
            callbacks: { label: (ctx) => ` ${formatMoney(ctx.parsed.y)}` },
          },
          legend: { labels: { color: '#7070a0', boxWidth: 10, padding: 14 } },
        },
        scales: {
          x: { grid: { display: false }, border: { display: false } },
          y: {
            grid: { color: gridColor },
            border: { display: false },
            ticks: { callback: (v) => '$' + v },
          },
        },
      },
    });
  }

  const lineCtx = document.getElementById('analytics-line-chart');
  if (lineCtx) {
    new Chart(lineCtx, {
      type: 'line',
      data: {
        labels: charts.months || [],
        datasets: [{
          label: 'Usuarios',
          data: charts.registrations || [],
          borderColor: '#22d3ee',
          backgroundColor: 'rgba(34,211,238,0.10)',
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
        plugins: { tooltip: tooltipCfg, legend: { display: false } },
        scales: {
          x: { grid: { color: gridColor }, border: { display: false } },
          y: { grid: { color: gridColor }, border: { display: false } },
        },
      },
    });
  }
})();

/**
 * gastos.js — Listado de gastos desde base.sql
 */

let gastos = (window.PANEL_DATA?.gastos || []).slice();
let filtered = [];
let page = 1;
const PAGE_SIZE = 10;

function formatMoney(value) {
  return '$' + Number(value || 0).toFixed(2);
}

function filterGastos() {
  const q = (document.getElementById('gasto-search')?.value || '').toLowerCase();
  const category = document.getElementById('category-filter')?.value || '';

  filtered = gastos.filter((g) => {
    const matchSearch =
      g.description.toLowerCase().includes(q) ||
      g.userName.toLowerCase().includes(q) ||
      g.category.toLowerCase().includes(q);
    const matchCategory = !category || g.category === category;
    return matchSearch && matchCategory;
  });

  page = 1;
  renderTable();
}

function renderTable() {
  const tbody = document.getElementById('gastos-tbody');
  if (!tbody) return;

  const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  if (page > totalPages) page = totalPages;
  const paginated = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);
  const totalAmount = filtered.reduce((sum, g) => sum + g.amount, 0);

  const totalLabel = document.getElementById('gastos-total-label');
  if (totalLabel) totalLabel.textContent = `Total filtrado: ${formatMoney(totalAmount)}`;

  if (!paginated.length) {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:64px;color:var(--text-dimmer)">
      No se encontraron gastos</td></tr>`;
  } else {
    tbody.innerHTML = paginated.map((g) => `
      <tr>
        <td><strong>${g.description}</strong></td>
        <td>${g.userName}</td>
        <td><span class="badge-role" style="background:rgba(108,99,255,0.12);color:#a89fff">${g.category}</span></td>
        <td>${formatMoney(g.amount)}</td>
        <td class="muted">${g.dateLabel}</td>
      </tr>
    `).join('');
  }

  renderPagination(totalPages);
}

function renderPagination(totalPages) {
  const info = document.getElementById('gastos-pagination-info');
  const btns = document.getElementById('gastos-pagination-btns');
  if (!info || !btns) return;

  const start = filtered.length === 0 ? 0 : (page - 1) * PAGE_SIZE + 1;
  const end = Math.min(page * PAGE_SIZE, filtered.length);
  info.textContent = `Mostrando ${start}–${end} de ${filtered.length} gastos`;

  const chevL = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="15 18 9 12 15 6"/>
  </svg>`;
  const chevR = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="9 18 15 12 9 6"/>
  </svg>`;

  let html = `<button class="page-btn" ${page === 1 ? 'disabled' : ''} onclick="goGastosPage(${page - 1})">${chevL}</button>`;
  for (let p = 1; p <= totalPages; p++) {
    html += `<button class="page-btn ${p === page ? 'active' : ''}" onclick="goGastosPage(${p})">${p}</button>`;
  }
  html += `<button class="page-btn" ${page === totalPages ? 'disabled' : ''} onclick="goGastosPage(${page + 1})">${chevR}</button>`;
  btns.innerHTML = html;
}

function goGastosPage(p) { page = p; renderTable(); }

(function initGastos() {
  filtered = gastos.slice();
  renderTable();
})();

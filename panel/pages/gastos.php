<!-- ── Gastos ──────────────────────────────────────────────── -->
<div class="table-wrap">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <div class="search-box">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="gasto-search" placeholder="Buscar gastos..."
               oninput="filterGastos()" aria-label="Buscar gastos">
      </div>

      <select class="filter-select" id="category-filter" onchange="filterGastos()" aria-label="Filtrar por categoría">
        <option value="">Todas las categorías</option>
        <option>Comida</option>
        <option>Transporte</option>
        <option>Entretenimiento</option>
        <option>Servicios</option>
        <option>Hogar</option>
        <option>Otros</option>
      </select>
    </div>

    <div class="toolbar-right">
      <span class="selected-count" id="gastos-total-label">Total: $0.00</span>
    </div>
  </div>

  <div style="overflow-x:auto">
    <table>
      <thead>
        <tr>
          <th>Descripción</th>
          <th>Usuario</th>
          <th>Categoría</th>
          <th>Monto</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody id="gastos-tbody"></tbody>
    </table>
  </div>

  <div class="pagination">
    <p class="pagination-info" id="gastos-pagination-info"></p>
    <div class="pagination-btns" id="gastos-pagination-btns"></div>
  </div>
</div>

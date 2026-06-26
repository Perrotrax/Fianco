<!-- ── Usuarios ────────────────────────────────────────────── -->

<div class="modal-backdrop hidden" id="user-modal-backdrop">
  <div class="modal" id="user-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-header">
      <div class="modal-header-left">
        <div class="modal-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
        <div>
          <h2 class="modal-title" id="modal-title">Nuevo usuario</h2>
          <p class="modal-sub" id="modal-sub">Completa los datos para registrar</p>
        </div>
      </div>
      <button class="modal-close" onclick="closeUserModal()" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <div class="modal-body">
      <form id="user-form" onsubmit="handleUserSave(event)" novalidate>
        <input type="hidden" id="form-user-id">

        <div class="form-group">
          <label class="form-label" for="form-name">Nombre completo</label>
          <input class="form-input" id="form-name" type="text" placeholder="Ej: María García" autocomplete="off">
          <p class="form-error" id="err-name" style="display:none"></p>
        </div>

        <div class="form-group">
          <label class="form-label" for="form-email">Correo electrónico</label>
          <input class="form-input" id="form-email" type="email" placeholder="maria@empresa.com" autocomplete="off">
          <p class="form-error" id="err-email" style="display:none"></p>
        </div>

        <div class="form-group" id="password-group">
          <label class="form-label" for="form-password">Contraseña</label>
          <input class="form-input" id="form-password" type="password" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
          <p class="form-error" id="err-password" style="display:none"></p>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick="closeUserModal()">Cancelar</button>
          <button type="submit" class="btn-save" id="modal-save-btn">Agregar usuario</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="table-wrap">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <div class="search-box">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="user-search" placeholder="Buscar usuarios..."
               oninput="filterUsers()" aria-label="Buscar usuarios">
      </div>

      <select class="filter-select" id="bio-filter" onchange="filterUsers()" aria-label="Filtrar por biometría">
        <option value="">Todos</option>
        <option value="1">Con biometría</option>
        <option value="0">Sin biometría</option>
      </select>
    </div>

    <div class="toolbar-right">
      <button class="btn-icon-sm" title="Actualizar" onclick="reloadUsers()" id="refresh-btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="23 4 23 10 17 10"/>
          <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
        </svg>
      </button>
      <button class="btn-primary" onclick="openUserModal(null)" id="add-user-btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Agregar usuario
      </button>
    </div>
  </div>

  <div style="overflow-x:auto">
    <table>
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Biometría</th>
          <th>Registrado</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="user-tbody"></tbody>
    </table>
  </div>

  <div class="pagination">
    <p class="pagination-info" id="pagination-info"></p>
    <div class="pagination-btns" id="pagination-btns"></div>
  </div>
</div>

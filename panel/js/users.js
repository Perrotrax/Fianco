/**
 * users.js — Gestión de usuarios conectada a base.sql
 */

let users = (window.PANEL_DATA?.users || []).slice();
let filtered = [];
let page = 1;
const PAGE_SIZE = 8;
let editingUserId = null;

const svgEdit = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
</svg>`;

const svgTrash = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <polyline points="3 6 5 6 21 6"/>
  <path d="M19 6l-1 14H6L5 6"/>
  <path d="M10 11v6"/><path d="M14 11v6"/>
  <path d="M9 6V4h6v2"/>
</svg>`;

const svgFingerprint = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4"/>
  <path d="M14 13.12c0 2.38 0 6.38-1 8.88"/>
  <path d="M17.29 21.02c.12-.6.43-2.3.5-3.02"/>
  <path d="M2 12a10 10 0 0 1 10-10"/>
  <path d="M2 16h.01"/>
  <path d="M21.8 16c.2-2 .131-5.354 0-6"/>
  <path d="M5 19.5c.9-.9 2.2-1.5 3.5-1.5"/>
  <path d="M9 3.34c1.1.8 2.2 1.2 3 1.2"/>
</svg>`;

const svgMore = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>
</svg>`;

async function apiUsers(payload) {
  const response = await fetch('../api/panel/usuarios.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  return response.json();
}

async function reloadUsers() {
  const response = await fetch('../api/panel/usuarios.php');
  const res = await response.json();
  if (res.success) {
    users = (res.users || []).map((u, index) => ({
      ...u,
      avatarColor: randomAvatarColor(),
      id: String(u.id),
    }));
    filterUsers();
  }
}

function filterUsers() {
  const q = (document.getElementById('user-search')?.value || '').toLowerCase();
  const bio = document.getElementById('bio-filter')?.value || '';

  filtered = users.filter((u) => {
    const matchSearch = u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
    const matchBio = bio === '' || String(u.biometric ? 1 : 0) === bio;
    return matchSearch && matchBio;
  });

  page = 1;
  renderTable();
}

function renderTable() {
  const tbody = document.getElementById('user-tbody');
  if (!tbody) return;

  const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  if (page > totalPages) page = totalPages;
  const paginated = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);

  if (!paginated.length) {
    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;padding:64px;color:var(--text-dimmer)">
      No se encontraron usuarios</td></tr>`;
  } else {
    tbody.innerHTML = paginated.map((u) => {
      const bioStyle = u.biometric
        ? { bg: 'rgba(52,211,153,0.1)', color: '#34d399', label: 'Activa' }
        : { bg: 'rgba(112,112,160,0.1)', color: '#7070a0', label: 'Inactiva' };

      return `
        <tr data-id="${u.id}">
          <td>
            <div class="user-cell">
              <div class="avatar" style="background:${u.avatarColor}22;color:${u.avatarColor}">
                ${getInitials(u.name)}
              </div>
              <div>
                <p class="user-cell-name">${u.name}</p>
                <p class="user-cell-email">${u.email}</p>
              </div>
            </div>
          </td>
          <td>
            <span class="badge" style="background:${bioStyle.bg};color:${bioStyle.color}">
              <span class="badge-dot" style="background:${bioStyle.color}"></span>
              ${bioStyle.label}
            </span>
          </td>
          <td class="muted">${u.joined}</td>
          <td>
            <div class="ctx-menu-wrap" style="display:flex;justify-content:flex-end">
              <button class="ctx-btn" onclick="toggleCtxMenu(event,'ctx-${u.id}')" aria-label="Más opciones">${svgMore}</button>
              <div class="ctx-menu" id="ctx-${u.id}">
                <button class="ctx-menu-item" onclick="openUserModal('${u.id}')">${svgEdit} Editar usuario</button>
                ${u.biometric ? `<button class="ctx-menu-item" onclick="resetBiometric('${u.id}')">${svgFingerprint} Restablecer biometría</button>` : ''}
                <div class="ctx-divider"></div>
                <button class="ctx-menu-item danger" onclick="deleteUser('${u.id}')">${svgTrash} Eliminar usuario</button>
              </div>
            </div>
          </td>
        </tr>`;
    }).join('');
  }

  renderPagination(totalPages);
}

function renderPagination(totalPages) {
  const info = document.getElementById('pagination-info');
  const btns = document.getElementById('pagination-btns');
  if (!info || !btns) return;

  const start = filtered.length === 0 ? 0 : (page - 1) * PAGE_SIZE + 1;
  const end = Math.min(page * PAGE_SIZE, filtered.length);
  info.textContent = `Mostrando ${start}–${end} de ${filtered.length} usuarios`;

  const chevL = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="15 18 9 12 15 6"/>
  </svg>`;
  const chevR = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="9 18 15 12 9 6"/>
  </svg>`;

  let html = `<button class="page-btn" ${page === 1 ? 'disabled' : ''} onclick="goPage(${page - 1})">${chevL}</button>`;
  for (let p = 1; p <= totalPages; p++) {
    html += `<button class="page-btn ${p === page ? 'active' : ''}" onclick="goPage(${p})">${p}</button>`;
  }
  html += `<button class="page-btn" ${page === totalPages ? 'disabled' : ''} onclick="goPage(${page + 1})">${chevR}</button>`;
  btns.innerHTML = html;
}

function goPage(p) { page = p; renderTable(); }

async function deleteUser(id) {
  if (!confirm('¿Eliminar este usuario y todos sus gastos?')) return;
  const res = await apiUsers({ action: 'delete', id });
  if (res.success) {
    users = users.filter((u) => u.id !== id);
    filterUsers();
  } else {
    alert(res.message || 'No se pudo eliminar.');
  }
}

async function resetBiometric(id) {
  if (!confirm('¿Restablecer la biometría de este usuario?')) return;
  const res = await apiUsers({ action: 'reset_biometric', id });
  if (res.success) {
    users = users.map((u) => (u.id === id ? { ...u, biometric: false } : u));
    filterUsers();
  } else {
    alert(res.message || 'No se pudo restablecer.');
  }
}

function toggleCtxMenu(e, menuId) {
  e.stopPropagation();
  document.querySelectorAll('.ctx-menu.open').forEach((m) => {
    if (m.id !== menuId) m.classList.remove('open');
  });
  document.getElementById(menuId)?.classList.toggle('open');
}

function openUserModal(userId) {
  document.querySelectorAll('.ctx-menu.open').forEach((m) => m.classList.remove('open'));

  editingUserId = userId;
  const user = userId ? users.find((u) => u.id === userId) : null;

  document.getElementById('modal-title').textContent = user ? 'Editar usuario' : 'Nuevo usuario';
  document.getElementById('modal-sub').textContent = user ? 'Actualiza los datos del usuario' : 'Completa los datos para registrar';
  document.getElementById('modal-save-btn').textContent = user ? 'Guardar cambios' : 'Agregar usuario';
  document.getElementById('form-user-id').value = user ? user.id : '';
  document.getElementById('form-name').value = user ? user.name : '';
  document.getElementById('form-email').value = user ? user.email : '';
  document.getElementById('form-password').value = '';

  const passwordGroup = document.getElementById('password-group');
  passwordGroup.style.display = user ? 'none' : 'block';

  clearFormErrors();
  document.getElementById('user-modal-backdrop').classList.remove('hidden');
  document.getElementById('form-name').focus();
}

function closeUserModal() {
  document.getElementById('user-modal-backdrop').classList.add('hidden');
  editingUserId = null;
  clearFormErrors();
}

document.getElementById('user-modal-backdrop')?.addEventListener('click', function (e) {
  if (e.target === this) closeUserModal();
});

function clearFormErrors() {
  ['name', 'email', 'password'].forEach((field) => {
    const input = document.getElementById(`form-${field}`);
    const err = document.getElementById(`err-${field}`);
    if (input) input.classList.remove('error');
    if (err) err.style.display = 'none';
  });
}

function showFieldError(field, msg) {
  const input = document.getElementById(`form-${field}`);
  const err = document.getElementById(`err-${field}`);
  if (input) input.classList.add('error');
  if (err) { err.textContent = msg; err.style.display = 'block'; }
}

async function handleUserSave(e) {
  e.preventDefault();
  clearFormErrors();

  const name = document.getElementById('form-name').value.trim();
  const email = document.getElementById('form-email').value.trim();
  const password = document.getElementById('form-password').value.trim();
  const userId = document.getElementById('form-user-id').value;
  let valid = true;

  if (!name) { showFieldError('name', 'El nombre es requerido'); valid = false; }
  if (!email) { showFieldError('email', 'El email es requerido'); valid = false; }
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showFieldError('email', 'Email no válido'); valid = false; }
  if (!userId && password.length < 6) { showFieldError('password', 'Mínimo 6 caracteres'); valid = false; }
  if (!valid) return;

  const payload = userId
    ? { action: 'update', id: userId, nombre: name, correo: email }
    : { action: 'create', nombre: name, correo: email, password };

  const res = await apiUsers(payload);
  if (!res.success) {
    alert(res.message || 'Error al guardar.');
    return;
  }

  if (userId) {
    users = users.map((u) => (u.id === userId ? { ...u, name, email } : u));
  } else {
    users.unshift({
      id: res.id,
      name,
      email,
      biometric: false,
      joined: formatDate(new Date()),
      avatarColor: randomAvatarColor(),
    });
  }

  closeUserModal();
  filterUsers();
}

(function initUsers() {
  filtered = users.slice();
  renderTable();
})();

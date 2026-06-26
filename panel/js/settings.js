/**
 * settings.js — Perfil, seguridad y biometría (base.sql)
 */

const panelData = window.PANEL_DATA || {};
const currentUser = panelData.currentUser || {};
const userEmail = window.PANEL_USER_EMAIL || currentUser.email || '';

function switchSection(btn) {
  const sectionId = btn.dataset.section;

  document.querySelectorAll('.settings-nav-item').forEach((b) => {
    b.classList.toggle('active', b === btn);
  });

  document.querySelectorAll('.settings-section').forEach((s) => {
    s.classList.toggle('active', s.id === `section-${sectionId}`);
  });

  if (sectionId === 'security') {
    refreshSecurityStatus();
  }
}

function showBioFeedback(message, isError = false) {
  const el = document.getElementById('bio-feedback');
  if (!el) return;
  el.style.display = 'block';
  el.style.color = isError ? '#ff4d6d' : '#34d399';
  el.textContent = message;
}

function checkBiometricToggle() {
  const toggle = document.getElementById('biometricToggle');
  if (!toggle) return;

  const token = localStorage.getItem(`bio_token_${userEmail}`);
  toggle.checked = Boolean(token && currentUser.biometric);
}

function refreshSecurityStatus() {
  const token = localStorage.getItem(`bio_token_${userEmail}`);
  const dbEnabled = Boolean(currentUser.biometric);
  const localEnabled = Boolean(token);

  const setBadge = (id, active, activeLabel, inactiveLabel) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = active ? activeLabel : inactiveLabel;
    el.style.background = active ? 'rgba(52,211,153,0.1)' : 'rgba(112,112,160,0.1)';
    el.style.color = active ? '#34d399' : '#7070a0';
  };

  const dbStatus = document.getElementById('bio-db-status');
  const localStatus = document.getElementById('bio-local-status');
  if (dbStatus) dbStatus.textContent = dbEnabled ? 'Campo biometrico = 1 en la base de datos' : 'Campo biometrico = 0';
  if (localStatus) localStatus.textContent = localEnabled ? 'Token presente en localStorage' : 'Sin token local';

  setBadge('bio-db-badge', dbEnabled, 'Activa', 'Inactiva');
  setBadge('bio-local-badge', localEnabled, 'Presente', 'Ausente');
}

async function toggleBiometria(checkbox) {
  const enable = checkbox.checked;

  try {
    const response = await fetch('../api/biometrico.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'register', enable }),
    });

    const res = await response.json();

    if (res.success) {
      currentUser.biometric = enable;
      if (enable) {
        localStorage.setItem(`bio_token_${userEmail}`, res.token);
        showBioFeedback('Biometría activada. Token guardado en localStorage y base de datos.');
      } else {
        localStorage.removeItem(`bio_token_${userEmail}`);
        showBioFeedback('Biometría desactivada correctamente.');
      }
      refreshSecurityStatus();
    } else {
      checkbox.checked = !enable;
      showBioFeedback(res.message || 'Error al configurar biometría.', true);
    }
  } catch (e) {
    checkbox.checked = !enable;
    showBioFeedback('Error de conexión al configurar biometría.', true);
  }
}

(function initSettings() {
  const setValue = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.value = value || '';
  };

  setValue('profile-name', currentUser.name);
  setValue('profile-email', currentUser.email);
  setValue('profile-joined', currentUser.joined);

  checkBiometricToggle();
  refreshSecurityStatus();
})();

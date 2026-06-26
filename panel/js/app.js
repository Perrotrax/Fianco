/**
 * app.js — Lógica global del panel AdminHub
 * Sidebar móvil, utilidades compartidas y SweetAlert2
 */

// ========================================
// Configuración de SweetAlert2
// ========================================
const swalConfig = {
    confirmButtonColor: '#6366f1',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Sí, continuar',
    cancelButtonText: 'Cancelar',
    customClass: {
        popup: 'swal-popup-custom',
        confirmButton: 'swal-btn-confirm',
        cancelButton: 'swal-btn-cancel',
        title: 'swal-title-custom',
        htmlContainer: 'swal-content-custom'
    }
};

// ========================================
// Funciones de Alerta Mejoradas
// ========================================
function showSuccess(title, message = '', timer = 1500) {
    Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        timer: timer,
        timerProgressBar: true,
        showConfirmButton: timer ? false : true,
        ...swalConfig
    });
}

function showError(title, message = '') {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        ...swalConfig
    });
}

function showWarning(title, message = '') {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        ...swalConfig
    });
}

function showInfo(title, message = '') {
    Swal.fire({
        icon: 'info',
        title: title,
        text: message,
        ...swalConfig
    });
}

function confirmAction(title, message, callback) {
    Swal.fire({
        icon: 'question',
        title: title,
        text: message,
        showCancelButton: true,
        ...swalConfig
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}

// ========================================
// Sidebar móvil
// ========================================
function openSidebar() {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('mobile-overlay').classList.add('open');
}

function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('mobile-overlay').classList.remove('open');
}

/* ── Cerrar menús contextuales al hacer click fuera ──────── */
document.addEventListener('click', function (e) {
  if (!e.target.closest('.ctx-menu-wrap')) {
    document.querySelectorAll('.ctx-menu.open').forEach(m => m.classList.remove('open'));
  }
});

// ========================================
// Utilidades de color de avatar
// ========================================
const AVATAR_COLORS = [
  '#6366f1','#f472b6','#14b8a6','#f59e0b',
  '#3b82f6','#10b981','#8b5cf6','#ef4444'
];

function getInitials(name) {
  return name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase();
}

function randomAvatarColor() {
  return AVATAR_COLORS[Math.floor(Math.random() * AVATAR_COLORS.length)];
}

// ========================================
// Formato de fecha
// ========================================
function formatDate(date) {
  return date.toLocaleDateString('es-MX', { day: 'numeric', month: 'short', year: 'numeric' });
}

// ========================================
// Funciones de Validación
// ========================================
function validarEmail(email) {
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regexEmail.test(email);
}

function validarPassword(password) {
    return password && password.length >= 6;
}

// ========================================
// Manejo de API con SweetAlerts
// ========================================
async function fetchWithAlert(url, options = {}, showLoadingAlert = true) {
    try {
        if (showLoadingAlert) {
            Swal.fire({
                title: 'Cargando...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        const response = await fetch(url, options);
        const data = await response.json();

        Swal.close();

        if (!response.ok && !data.success) {
            showError('Error', data.message || 'Ocurrió un error al procesar la solicitud');
            return null;
        }

        return data;
    } catch (error) {
        Swal.close();
        showError('Error de Conexión', 'No se pudo conectar con el servidor');
        console.error(error);
        return null;
    }
}
